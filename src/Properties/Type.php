<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Properties;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type as PhpDocumentorReflectionType;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;

trait Type
{
    use Reflection;

    protected Types $types;

    /** @return class-string */
    public function getClassType(bool $asCollectionItem = false): string
    {
        if ($asCollectionItem) {
            foreach ($this->getTypes()->innerTypes as $type) {
                if (class_exists($type, false)) {
                    return $type;
                }
            }

            throw new \RuntimeException(sprintf('Item class not found. Property: %s.', $this->reflection->getName()));
        }

        foreach ($this->getTypes()->types as $type) {
            if (class_exists($type, false)) {
                return $type;
            }
        }

        throw new \RuntimeException(sprintf('Class not found. Property: %s.', $this->reflection->getName()));
    }

    public function isNullable(): bool
    {
        if ($type = $this->reflection->getType()) {
            return $type->allowsNull();
        }

        foreach ($this->getTypes()->types as $type) {
            if ('null' === $type) {
                return true;
            }
        }

        return false;
    }

    public function hasDefaultValue(): bool
    {
        if (!$this->reflection->hasDefaultValue()) {
            return $this->reflection->isPromoted() && $this->constructorParameter?->isDefaultValueAvailable();
        }

        return true;
    }

    public function getDefaultValue(): mixed
    {
        if (!$this->reflection->hasDefaultValue()) {
            if (!$this->reflection->isPromoted() || !$this->constructorParameter?->isDefaultValueAvailable()) {
                return null;
            }

            return $this->constructorParameter->getDefaultValue();
        }

        return $this->reflection->getDefaultValue();
    }

    public function getTypes(): Types
    {
        if (isset($this->types)) {
            return $this->types;
        }

        $types = new Types();

        $types->property = $this->reflection->getType();
        $types->propertyDocBlock = $this->getPropertyTypeFromVarDocBlock();
        $types->constructorParameter = $this->constructorParameter?->getType();
        $types->constructorParameterDocBlock = $this->getParameterTypeFromParamDocBlock();
        $types->process($this->reflection);

        return $this->types = $types;
    }

    private function getPropertyTypeFromVarDocBlock(): ?PhpDocumentorReflectionType
    {
        $docBlock = $this->reflection->getDocComment();
        if (false === $docBlock) {
            return null;
        }

        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = $docBlockFactory->create($docBlock);

        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');
        if (empty($varTags)) {
            return null;
        }

        return $varTags[0]->getType();
    }

    private function getParameterTypeFromParamDocBlock(): ?PhpDocumentorReflectionType
    {
        if (!$this->constructorParameter) {
            return null;
        }

        $constructor = $this->constructorParameter->getDeclaringFunction();
        $docBlock = $constructor->getDocComment();
        if (false === $docBlock) {
            return null;
        }

        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = $docBlockFactory->create($docBlock);

        /** @var Param[] $paramTags */
        $paramTags = $docBlock->getTagsByName('param');
        if (empty($paramTags)) {
            return null;
        }

        $paramTag = array_filter(
            $paramTags,
            fn (Param $paramTag) => $paramTag->getVariableName() === $this->constructorParameter->getName()
        );

        if (empty($paramTag)) {
            return null;
        }

        return $paramTag[0]->getType();
    }
}

class Types
{
    public ?\ReflectionType $property;
    public ?PhpDocumentorReflectionType $propertyDocBlock;
    public ?\ReflectionType $constructorParameter;
    public ?PhpDocumentorReflectionType $constructorParameterDocBlock;

    /** @var string[] */
    public array $types = [];
    /** @var string[] only if isCollection is `true` */
    public array $innerTypes = [];

    public function process(\ReflectionProperty $property): void
    {
        $this->processReflectionType($this->property);
        $this->processPhpDocumentorReflectionType($this->propertyDocBlock, $property->getDeclaringClass());
        $this->processReflectionType($this->constructorParameter);
        $this->processPhpDocumentorReflectionType($this->constructorParameterDocBlock, $property->getDeclaringClass());
    }

    private function processReflectionType(?\ReflectionType $reflection): void
    {
        if (null === $reflection) {
            return;
        }

        if ($reflection instanceof \ReflectionNamedType) {
            if ($reflection->allowsNull()) {
                $this->addType('null');
            }
            $this->addType($reflection->getName());
        }

        if ($reflection instanceof \ReflectionUnionType || $reflection instanceof \ReflectionIntersectionType) {
            $types = $reflection->getTypes();
            foreach ($types as $type) {
                $this->processReflectionType($type);
            }
        }
    }

    private function processPhpDocumentorReflectionType(?PhpDocumentorReflectionType $reflection, \ReflectionClass $classReflection): void
    {
        if (null === $reflection) {
            return;
        }

        if ($reflection instanceof Compound) {
            $types = $reflection->getIterator();
            foreach ($types as $type) {
                $this->processPhpDocumentorReflectionType($type, $classReflection);
            }

            return;
        }

        if ($reflection instanceof Array_ || $reflection instanceof Collection) {
            $itemType = $reflection->getValueType();

            $itemClass = $itemType->__toString();
            if (class_exists($itemClass, false)) {
                $this->innerTypes[] = $itemClass;
                $this->addType((string) $reflection->getKeyType());

                return;
            }

            if (class_exists($class = $classReflection->getNamespaceName().'\\'.ltrim($itemClass, '\\'), false)) {
                $this->innerTypes[] = $class;
                $this->addType((string) $reflection->getKeyType());

                return;
            }

            /** @var class-string[] $imports */
            $imports = array_filter(array_map(
                fn (string $line) => str_starts_with($line, 'use') ?
                    (false !== strpos($line, ltrim($itemClass, '\\')) ?
                        sscanf($line, 'use %s;') :
                        null
                    ) :
                    null,
                file($classReflection->getFileName() ?: '') ?: []
            ));

            foreach ($imports as $import) {
                if (class_exists($import, false)) {
                    $this->innerTypes[] = $import;
                    $this->addType((string) $reflection->getKeyType());

                    return;
                }
            }

            // class not exists! It's simple type
            $this->innerTypes[] = (string) $itemType;
            $this->addType((string) $reflection->getKeyType());

            return;
        }

        if ($reflection instanceof Object_) {
            $class = $reflection->__toString();
            if (class_exists($class, false)) {
                $this->types[] = $class;
                $this->addType((string) $class);

                return;
            }

            if (class_exists($class = $classReflection->getNamespaceName().'\\'.ltrim($class, '\\'), false)) {
                $this->types[] = $class;
                $this->addType($class);

                return;
            }

            $imports = array_filter(array_map(
                fn (string $line) => str_starts_with($line, 'use') ?
                (false !== strpos($line, ltrim($class, '\\')) ?
                sscanf($line, 'use %s;') :
                null
                ) :
                null,
                file($classReflection->getFileName() ?: '') ?: []
            ));

            /** @var class-string[] $imports */
            foreach ($imports as $import) {
                if (class_exists($import, false)) {
                    $this->types[] = $import;
                    $this->addType($import);

                    return;
                }
            }
        }

        $this->addType((string) $reflection);
    }

    private function addType(string $type): void
    {
        if ('?' === $type[0]) {
            if (strlen($type) > 1) {
                $type = substr($type, 1);
                $this->addType('null');
            } else {
                $type = 'null';
            }
        }

        if (!in_array($type, $this->types)) {
            $this->types[] = $type;
        }
    }
}
