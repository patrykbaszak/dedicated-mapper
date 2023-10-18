<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Attribute\SimpleObject;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use PBaszak\DedicatedMapper\Reflection\ReflectionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type as PhpDocumentorType;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;

class TypeFactory
{
    protected ReflectionFactory $reflectionFactory;

    public const AVAILABLE_TYPES_BY_PRIORITY = [
        CollectionType::class,
        SimpleObjectType::class,
        ClassType::class,
        Type::class,
    ];

    public function __construct(ReflectionFactory $reflectionFactory = null)
    {
        $this->reflectionFactory = $reflectionFactory ?? new ReflectionFactory();
    }

    public function createClassType(\ReflectionClass $reflection, null|PropertyReflection|TypeInterface $parent): ClassType
    {
        $ref = new \ReflectionClass(ClassType::class);
        /** @var ClassType $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('reflection')->setValue($instance, $this->reflectionFactory->createClassReflection($reflection->getName(), $instance));
        $ref->getProperty('parent')->setValue($instance, $parent);

        ClassType::storeClassType($instance);

        return $instance;
    }
    
    public function createType(PropertyReflection $propertyInstanceWithoutType, int $depth = 0, ?Type $parentInstance = null): TypeInterface
    {
        if (null === ($ref = $propertyInstanceWithoutType->getReflection())) {
            throw new \Exception('Reflection is null.');
        }

        $type = 0 === $depth ? $ref->getType() : null;
        $phpDocumentatorType = null;
        if (false !== ($docComment = $ref->getDocComment())) {
            /** @var Var_[] $varTags */
            $varTags = DocBlockFactory::createInstance()->create(
                $docComment,
                (new ContextFactory)->createFromReflector($ref)
            )->getTagsByName('var');

            /** @var null|PhpDocumentorType $phpDocumentatorType */
            $phpDocumentatorType = $varTags[0]->getType() ?? null;
            if ($phpDocumentatorType && $depth > 0) {
                for ($i = 0; $i > $depth; $i++) {
                    /** @var AbstractList $phpDocumentatorType It means that we are in collection type */
                    $phpDocumentatorType = $phpDocumentatorType->getValueType();
                }
            }
        }

        if (null === $type && null === $phpDocumentatorType) {
            throw new RuntimeException('Property has no type. Docblock `@var` or typehint is required.');
        }

        $ref = new \ReflectionClass(Type::class);
        /** @var Type $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('reflectionType')->setValue($instance, $type);
        $ref->getProperty('phpDocumentorReflectionType')->setValue($instance, $phpDocumentatorType);
        $ref->getProperty('parent')->setValue($instance, $parentInstance ?? $propertyInstanceWithoutType);

        $types = [];
        if (null !== $type) {
            if ($type->allowsNull()) {
                $types[] = 'null';
                $ref->getProperty('nullable')->setValue($instance, true);
            }
            if ($type instanceof ReflectionNamedType) {
                $types[] = $type->getName();
                if (class_exists($type->getName(), false)) {
                    $ref->getProperty('class')->setValue($instance, true);
                }
            }
            if ($type instanceof ReflectionUnionType) {
                $types = array_merge($types, array_map(fn (ReflectionNamedType $t) => $t->getName(), $type->getTypes()));
                $ref->getProperty('union')->setValue($instance, true);
            }
            if ($type instanceof ReflectionIntersectionType) {
                $types = array_merge($types, array_map(fn (ReflectionNamedType $t) => $t->getName(), $type->getTypes()));
                $ref->getProperty('intersection')->setValue($instance, true);
            }
        }
        if (null !== $phpDocumentatorType) {
            if ($phpDocumentatorType instanceof Nullable || $phpDocumentatorType instanceof Null_) {
                $types[] = 'null';
                $ref->getProperty('nullable')->setValue($instance, true);
                $phpDocumentatorType = $phpDocumentatorType->getActualType();
            }
            if ($phpDocumentatorType instanceof Compound) {
                $types = array_merge($types, array_map(fn (PhpDocumentorType $t) => (string) $t, $phpDocumentatorType->getIterator()->getArrayCopy()));
                $ref->getProperty('union')->setValue($instance, true);
            } elseif ($phpDocumentatorType instanceof Intersection) {
                $types = array_merge($types, array_map(fn (PhpDocumentorType $t) => (string) $t, $phpDocumentatorType->getIterator()->getArrayCopy()));
                $ref->getProperty('intersection')->setValue($instance, true);
            } elseif ($phpDocumentatorType instanceof AbstractList) {
                if (method_exists($phpDocumentatorType, 'getFqsen')) {
                    $fqsen = $phpDocumentatorType->getFqsen();
                    if ($fqsen && class_exists((string) $fqsen, false)) {
                        $ref->getProperty('class')->setValue($instance, true);
                    }
                }
                $types[] = $fqsen ? (string) $fqsen : 'array';
                $ref->getProperty('collection')->setValue($instance, true);
                $ref->getProperty('innerTypes')->setValue($instance, $this->createType($propertyInstanceWithoutType, $depth + 1, $instance));
            } else {
                $t = (string) $phpDocumentatorType;
                if (class_exists($t, false)) {
                    $types[] = $t;
                    $ref->getProperty('class')->setValue($instance, true);
                }
            }
        }

        $types = array_unique($types);
        $ref->getProperty('types')->setValue($instance, $types);

        foreach (self::AVAILABLE_TYPES_BY_PRIORITY as $typeClass) {
            if ($typeClass::supports($instance, $depth)) {
                $instance = $typeClass::create($instance);
                break;
            }
        }

        return $instance;
    }
}
