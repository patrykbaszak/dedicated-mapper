<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use ReflectionIntersectionType;
use ReflectionUnionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type as PhpDocumentorReflectionType;
use phpDocumentor\Reflection\Types\Array_;
use ReflectionClass;
use ReflectionType;

trait Type
{
    use Reflection;

    protected bool $isCollection = false;

    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    public function getTypes(): Types
    {
        $types = new Types();

        $types->property = $this->reflection->getType();
        $types->propertyDocBlock = $this->getPropertyTypeFromVarDocBlock();
        $types->constructorParameter = $this->constructorParameter?->getType();
        $types->constructorParameterDocBlock = $this->getParameterTypeFromParamDocBlock();
        $types->process($this->reflection);
        
        return $types;
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
    public ?ReflectionType $property;
    public ?PhpDocumentorReflectionType $propertyDocBlock;
    public ?ReflectionType $constructorParameter;
    public ?PhpDocumentorReflectionType $constructorParameterDocBlock;

    /** @var string[] */
    public array $types = [];
    /** @var string[] only if isCollection is `true` */
    public array $innerTypes = [];

    public function process(ReflectionProperty $property): void
    {
        $this->processReflectionType($this->property);
        $this->processPhpDocumentorReflectionType($this->propertyDocBlock, $property->getDeclaringClass());
        $this->processReflectionType($this->constructorParameter);
        $this->processPhpDocumentorReflectionType($this->constructorParameterDocBlock, $property->getDeclaringClass());
    }

    private function processReflectionType(?ReflectionType $reflection): void
    {
        if (null === $reflection) {
            return;
        }

        if ($reflection instanceof ReflectionNamedType) {
            $this->addType($reflection->getName());
        }

        if ($reflection instanceof ReflectionUnionType || $reflection instanceof ReflectionIntersectionType) {
            $types = $reflection->getTypes();
            foreach ($types as $type) {
                $this->processReflectionType($type);
            }
        }
    }

    private function processPhpDocumentorReflectionType(?PhpDocumentorReflectionType $reflection, ReflectionClass $classReflection): void
    {
        if (null === $reflection) {
            return;
        }

        if ($reflection instanceof Array_) {
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
                file($classReflection->getFileName() ?: '')
            ));

            foreach ($imports as $import) {
                if (class_exists($import, false)) {
                    $this->innerTypes[] = $import;
                    $this->addType((string) $reflection->getKeyType());

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
