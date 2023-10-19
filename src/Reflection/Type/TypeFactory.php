<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use PBaszak\DedicatedMapper\Reflection\ReflectionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Type as PhpDocumentorType;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionClass;
use ReflectionProperty;
use ReflectionType;
use Reflector;
use RuntimeException;

class TypeFactory
{
    protected ReflectionFactory $reflectionFactory;

    public const AVAILABLE_TYPES_BY_PRIORITY = [
        SimpleObjectType::class,
        CollectionType::class,
        ClassType::class,
        CompoundType::class,
        Type::class,
    ];

    public function __construct(ReflectionFactory $reflectionFactory = null)
    {
        $this->reflectionFactory = $reflectionFactory ?? new ReflectionFactory();
    }

    public function createFromReflection(Reflector $reflection, null|PropertyReflection|TypeInterface $parent = null): TypeInterface
    {
        if ($reflection instanceof ReflectionClass) {
            return $this->createFromString($reflection->getName(), $reflection, $parent);
        }
        if ($reflection instanceof ReflectionProperty) {
            if ($docComment = $reflection->getDocComment()) {
                $phpDocType = $this->createFromDocComment($docComment, $reflection, $parent);
            }
            if ($typehint = $reflection->getType()) {
                $reflectionType = $this->createFromString((string) $typehint, $reflection, $parent);
            }

            if (!$docComment && !$typehint) {
                $phpDocType = $this->createFromString('mixed', $reflection, $parent);
            }

            return $this->combineTypes(...array_filter([$phpDocType ?? null, $reflectionType ?? null]));
        }
        if ($reflection instanceof ReflectionType) {
            return $this->createFromString((string) $reflection, $reflection, $parent);
        }

        throw new RuntimeException('Unsupported reflection type.');
    }

    public function createFromDocComment(string $docComment, Reflector $reflection, null|PropertyReflection|TypeInterface $parent = null): TypeInterface
    {
        /** @var Var_[] $varTags */
        $varTags = DocBlockFactory::createInstance()->create(
            $docComment,
            (new ContextFactory)->createFromReflector($reflection)
        )->getTagsByName('var');
        /** @var null|PhpDocumentorType $phpDocumentatorType */
        $phpDocumentatorType = $varTags[0]->getType() ?? null;

        if (null === $phpDocumentatorType) {
            throw new RuntimeException('Property has no type. Docblock `@var` or typehint is required.');
        }

        return $this->createFromString((string) $phpDocumentatorType, $reflection, $parent);
    }

    /**
     * @param string|class-string $type
     * @param null|\ReflectionClass|ReflectionProperty|ReflectionType $reflection for Context to resolve namespaces
     */
    public function createFromString(string $type, ?Reflector $reflection = null, null|PropertyReflection|TypeInterface $parent = null): TypeInterface
    {
        $reflection = class_exists($type, false) ? null : $reflection;
        $resolver = new TypeResolver(new FqsenResolver());

        $phpDocumentatorType = $resolver->resolve($type, $reflection ? (new ContextFactory)->createFromReflector($reflection) : null);
        
        $instance = $this->createFromPhpDocumentatorType($phpDocumentatorType, $parent);

        if ($reflection) {
            $ref = new ReflectionClass(Type::class);
            $ref->getProperty('reflectionType')->setValue(
                $instance,
                match (get_class($reflection)) {
                    ReflectionProperty::class => $reflection->getType(),
                    ReflectionType::class => $reflection,
                    default => null,
                } 
            );
        }


        return $instance;
    }

    protected function createFromPhpDocumentatorType(PhpDocumentorType $type, null|PropertyReflection|TypeInterface $parent = null): TypeInterface
    {
        $ref = new \ReflectionClass(Type::class);
        /** @var Type $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('parent')->setValue($instance, $parent);
        
        $types = [];
        if ($type instanceof Nullable || $type instanceof Null_) {
            $types[] = 'null';
            $ref->getProperty('nullable')->setValue($instance, true);
            $type = $type->getActualType();
        }
        if ($type instanceof Compound) {
            $types = array_merge($types, array_map(fn (PhpDocumentorType $t) => (string) $t, $type->getIterator()->getArrayCopy()));
            $ref->getProperty('union')->setValue($instance, true);
        } elseif ($type instanceof Intersection) {
            $types = array_merge($types, array_map(fn (PhpDocumentorType $t) => (string) $t, $type->getIterator()->getArrayCopy()));
            $ref->getProperty('intersection')->setValue($instance, true);
        } elseif ($type instanceof AbstractList) {
            if (method_exists($type, 'getFqsen')) {
                $fqsen = $type->{'getFqsen'}();
                if ($fqsen && class_exists((string) $fqsen, false)) {
                    $ref->getProperty('class')->setValue($instance, true);
                }
            }
            $types[] = isset($fqsen) && null !== $fqsen ? (string) $fqsen : 'array';
            $ref->getProperty('collection')->setValue($instance, true);
            $ref->getProperty('innerType')->setValue($instance, $this->createFromPhpDocumentatorType($type->getValueType(), $instance));
        } else {
            $t = (string) $type;
            if (class_exists($t, false)) {
                $ref->getProperty('class')->setValue($instance, true);
            }
            $types[] = $t;
        }

        $types = array_unique($types);
        $ref->getProperty('types')->setValue($instance, $types);

        return $this->resolveTypeInterface($instance);
    }

    protected function combineTypes(TypeInterface ...$types): TypeInterface
    {
        $types = array_filter($types);
        $types = array_map(fn(TypeInterface $type): Type => method_exists($type, 'getType') ? $type->{'getType'}() : $type, $types);
        $ref = new \ReflectionClass(Type::class);
        /** @var Type $instance */
        $instance = $ref->newInstanceWithoutConstructor();

        foreach ($types as $type) {
            $ref->getProperty('parent')->setValue($instance, $type->getParent());
            $instanceTypes = $ref->getProperty('types')->isInitialized($instance) ? $ref->getProperty('types')->getValue($instance) : [];
            $ref->getProperty('types')->setValue($instance, array_merge($instanceTypes, $type->getTypes()));
            $instanceInnerType = $ref->getProperty('innerType')->getValue($instance);
            if (null === $instanceInnerType) {
                $ref->getProperty('innerType')->setValue($instance, $type->getInnerType());
            } elseif (null !== $type->getInnerType()) {
                $ref->getProperty('innerType')->setValue($instance, $this->combineTypes($instanceInnerType, $type->getInnerType()));
            }
            $ref->getProperty('nullable')->setValue($instance, $type->isNullable() || $ref->getProperty('nullable')->getValue($instance));
            $ref->getProperty('union')->setValue($instance, $type->isUnion() || $ref->getProperty('union')->getValue($instance));
            $ref->getProperty('intersection')->setValue($instance, $type->isIntersection() || $ref->getProperty('intersection')->getValue($instance));
            $ref->getProperty('collection')->setValue($instance, $type->isCollection() || $ref->getProperty('collection')->getValue($instance));
            $ref->getProperty('class')->setValue($instance, $type->isClass() || $ref->getProperty('class')->getValue($instance));
            $ref->getProperty('simpleObject')->setValue($instance, $type->isSimpleObject() || $ref->getProperty('simpleObject')->getValue($instance));
            $ref->getProperty('phpDocumentorReflectionType')->setValue($instance, $type->getPhpDocumentorReflectionType() ?? $ref->getProperty('phpDocumentorReflectionType')->getValue($instance));
            $ref->getProperty('reflectionType')->setValue($instance, $type->getReflectionType() ?? $ref->getProperty('reflectionType')->getValue($instance));
        }
        
        return $this->resolveTypeInterface($instance);
    }

    protected function resolveTypeInterface(Type $type): TypeInterface
    {
        foreach (self::AVAILABLE_TYPES_BY_PRIORITY as $typeClass) {
            if ($typeClass::supports($type)) {
                return $typeClass::create($type);   
            }
        }
    }
}
