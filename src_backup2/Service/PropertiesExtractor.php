<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

use PBaszak\MessengerMapperBundle\Attribute\Accessor;
use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;
use PBaszak\MessengerMapperBundle\DTO\Properties\Constraint as PropertiesConstraint;
use PBaszak\MessengerMapperBundle\DTO\Properties\Mapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Serializer;
use PBaszak\MessengerMapperBundle\DTO\Properties\Validator;
use PBaszak\MessengerMapperBundle\DTO\Property;
use PBaszak\MessengerMapperBundle\Utils\GetAttributesTrait;
use PBaszak\MessengerMapperBundle\Utils\GetClassIfClassType;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\SerializedPath;
use Symfony\Component\Validator\Constraint;

class PropertiesExtractor implements PropertiesExtractorInterface
{
    use GetAttributesTrait;
    use GetClassIfClassType;

    /**
     * {@inheritdoc}
     */
    public function extractProperties(int $origin, string $class, ?array $serializerGroups, ?array $validatorGroups, ?Property $parent = null): array
    {
        $reflectionClass = new \ReflectionClass($class);
        $output = [];
        if (!$parent) {
            $root = new Property(
                $origin,
                Property::SOURCE === $origin ? 'data' : 'mapped',
                $class,
                false,
                false,
                null,
                null,
                null,
                null,
                null,
                $this->getMapperClassAttributes($reflectionClass),
                null,
                null,
            );

            $output[] = $root;
        }

        /** @var \ReflectionProperty[] $properties */
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $name = $property->getName();
            $parameter = $this->getMatchingConstructorParameter($property);
            if ($this->isCollection($type = $parameter?->getType() ?? $property->getType())) {
                $collectionItemType = $this->getCollectionItemType($property)->valueType;
            }
            $isCollectionItem = (bool) $collectionItemType ?? false;
            $isNullable = $type?->allowsNull() ?? false;

            $output[] = $newProperty = new Property(
                $origin,
                $name,
                $collectionItemType ?? $this->getClassIfClassType($type) ?? $type?->getName() ?? 'mixed',
                $isNullable,
                $isCollectionItem,
                $type,
                $parent ?? $root ?? null,
                $reflectionClass,
                $property,
                $parameter,
                $this->getMapperAttributes($property),
                is_array($serializerGroups) ? $this->getSerializerAttributes($property) : null,
                is_array($validatorGroups) ? $this->getValidatorAttributes($property, $validatorGroups) : null,
            );

            if ($propertyClass = $this->getClassIfClassType($type)) {
                $childProperties = $this->extractProperties($origin, $propertyClass, $serializerGroups, $validatorGroups, $newProperty);
                foreach ($childProperties as $childProperty) {
                    $newProperty->addChild($childProperty);
                    $output[] = $childProperty;
                }
            }
        }

        return $output;
    }

    private function getMatchingConstructorParameter(\ReflectionProperty $property): ?\ReflectionParameter
    {
        $constructor = $property->getDeclaringClass()->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->getName() === $property->getName()) {
                    return $parameter;
                }
            }
        }

        return null;
    }

    private function isCollection(\ReflectionType $type): bool
    {
        if (null === $type) {
            return false;
        }

        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            $collectionTypes = [
                'array',
                'ArrayObject',
                'Iterator',
                'Traversable',
            ];

            return in_array($typeName, $collectionTypes, true);
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            $typeList = $type->getTypes();
            foreach ($typeList as $innerType) {
                if ($this->isCollection($innerType)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getCollectionItemType(\ReflectionProperty $property): ?CollectionItemType
    {
        if (null === $property) {
            return null;
        }

        $docComment = $property->getDocComment();
        if (false === $docComment) {
            return null;
        }

        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = $docBlockFactory->create($docComment);
        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');

        if (0 === count($varTags)) {
            return null;
        }

        $type = $varTags[0]->getType();
        if ($type instanceof Array_) {
            $itemType = $type->getValueType();

            $itemClass = $itemType->__toString();
            if (class_exists($itemClass, false)) {
                return new CollectionItemType($type->getKeyType()->__toString(), $itemClass);
            }

            if (class_exists($class = $property->getDeclaringClass()->getNamespaceName().'\\'.ltrim($itemClass, '\\'), false)) {
                return new CollectionItemType($type->getKeyType()->__toString(), $class);
            }

            /** @var class-string[] $imports */
            $imports = array_filter(array_map(
                fn (string $line) => str_starts_with($line, 'use') ?
                    (false !== strpos($line, ltrim($itemClass, '\\')) ?
                        sscanf($line, 'use %s;') :
                        null
                    ) :
                    null,
                file($property->getDeclaringClass()->getFileName() ?: '')
            ));

            foreach ($imports as $import) {
                if (class_exists($import, false)) {
                    return new CollectionItemType($type->getKeyType()->__toString(), $import);
                }
            }
        }

        return null;
    }

    private function getMapperClassAttributes(\ReflectionClass $class): ?Mapper
    {
        $targetProperty = $class->getAttributes(TargetProperty::class)[0]?->newInstance() ?? null;
        if (!$targetProperty) {
            return null;
        }

        return new Mapper(targetProperty: $targetProperty);
    }

    private function getMapperAttributes(\ReflectionProperty $property): Mapper
    {
        return new Mapper(
            $this->getAttributeInstance($property, Accessor::class),
            $this->getAttributeInstance($property, TargetProperty::class),
            $this->getAttributesInstances($property, MappingCallback::class)
        );
    }

    private function getSerializerAttributes(\ReflectionProperty $property): Serializer
    {
        return new Serializer(
            $this->getAttributeInstance($property, Groups::class),
            $this->getAttributeInstance($property, Ignore::class),
            $this->getAttributeInstance($property, MaxDepth::class),
            $this->getAttributeInstance($property, SerializedName::class),
            $this->getAttributeInstance($property, SerializedPath::class),
        );
    }

    /** @param string[] $validatorGroups */
    private function getValidatorAttributes(\ReflectionProperty $property, ?array $validatorGroups): ?Validator
    {
        $constraintAttrs = array_filter($property->getAttributes(), fn (\ReflectionAttribute $attr) => is_subclass_of($attr, Constraint::class));

        if (0 === count($constraintAttrs)) {
            return null;
        }

        $constraints = array_map(
            fn (\ReflectionAttribute $attr) => new PropertiesConstraint($attr->getName(), $attr->getArguments()),
            $constraintAttrs
        );

        if (0 === count($constraints)) {
            return null;
        }

        if (is_array($validatorGroups)) {
            foreach ($constraints as $index => $constraint) {
                $groups = array_merge($constraint->arguments['groups'] ?? [], $constraint->arguments['options']['groups'] ?? []);
                if (0 === count($groups)) {
                    continue;
                }

                if (0 === count($groups = array_intersect($groups, $validatorGroups))) {
                    unset($constraints[$index]);
                }
            }
        }

        return new Validator($constraints);
    }
}

class CollectionItemType
{
    public function __construct(
        public readonly string $keyType,
        public readonly string $valueType,
    ) {
    }
}
