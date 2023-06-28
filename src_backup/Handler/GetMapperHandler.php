<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Handler;

use PBaszak\MessengerMapperBundle\Attribute\Accessor;
use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;
use PBaszak\MessengerMapperBundle\Contract\GetMapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Constraint as PropertiesConstraint;
use PBaszak\MessengerMapperBundle\DTO\Properties\Mapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Serializer;
use PBaszak\MessengerMapperBundle\DTO\Properties\Validator;
use PBaszak\MessengerMapperBundle\DTO\Property;
use PBaszak\MessengerMapperBundle\Utils\GetClassIfClassType;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\SerializedPath;
use Symfony\Component\Validator\Constraint;

#[AsMessageHandler()]
class GetMapperHandler
{
    use GetClassIfClassType;

    private ?string $fromMapSeparator = null;
    private ?string $toMapSeparator = null;
    private bool $useSerializer;
    private bool $useValidator;
    private string $fromOrigin;
    private string $toOrigin;

    public function __invoke(GetMapper $query): string
    {
        $this->useSerializer = $query->useSerializer;
        $this->useValidator = $query->useValidator;
        $this->validateInput($query);
        $this->setOrigin($query->from, $query->fromType, $query->to, $query->toType);

        $sourceProperties = class_exists($query->from, false) ? $this->extractProperties($query->from, $this->fromOrigin, $query->serializerGroups, null) : [];
        $targetProperties = class_exists($query->to, false) ? $this->extractProperties($query->to, $this->toOrigin, $query->serializerGroups, null) : [];

        do {
            [$beforeSourceCount, $beforeTargetCount] = [$this->countMirrors($sourceProperties), $this->countMirrors($targetProperties)];
            $this->matchProperties($sourceProperties, $targetProperties);
            [$afterSourceCount, $afterTargetCount] = [$this->countMirrors($sourceProperties), $this->countMirrors($targetProperties)];
        } while ($beforeSourceCount !== $afterSourceCount || $beforeTargetCount !== $afterTargetCount);

        $properties = $this->reduceProperties($targetProperties);

        return $this->buildExpression($properties);
    }

    /**
     * @param Property[] $properties
     * @param mixed[]    $validatorGroups
     */
    private function buildExpression(array $properties, array $validatorGroups = []): string
    {
        if (empty($properties)) {
            throw new \LogicException('Any properties found to mapping in the root data template.');
        }

        $expression = [];
        if ($this->useValidator) {
            $expression[] = '$errors = [];';
        }
        if (in_array($this->toOrigin, [Property::ORIGIN_ARRAY, Property::ORIGIN_MAP])) {
            $expression[] = '$mapped = [];';
            foreach ($properties as $property) {
                $expression[] = $property->getPropertyExpression('mapped', $validatorGroups, $this->toMapSeparator, $this->fromMapSeparator);
            }
        } elseif (in_array($this->toOrigin, [Property::ORIGIN_OBJECT, Property::ORIGIN_MAP_OBJECT])) {
            $expression[] = '$mapped = (object) [];';
            foreach ($properties as $property) {
                $expression[] = $property->getPropertyExpression('mapped', $validatorGroups, $this->toMapSeparator, $this->fromMapSeparator);
            }
        } else {
            $constructorArguments = [];
            $expression[] = '$constructorArguments = [];';
            foreach ($properties as $property) {
                if (null !== $property->reflectionParameter) {
                    $constructorArguments[] = $property->getName();
                    $expression[] = $property->getPropertyExpression(
                        'constructorArguments',
                        $validatorGroups,
                        null,
                        $this->fromMapSeparator,
                        'array'
                    );
                }
            }
            $expression[] = sprintf('$mapped = new %s(...$constructorArguments);', $property->originClass);
            foreach ($properties as $property) {
                if (!in_array($property->getName(), $constructorArguments)) {
                    $expression[] = $property->getPropertyExpression('mapped', $validatorGroups, $this->toMapSeparator, $this->fromMapSeparator);
                }
            }
        }
        if ($this->useValidator) {
            $expression[] = 'if (!empty($errors)) {
                $constraintViolationList = new Symfony\Component\Validator\ConstraintViolationList();
                foreach ($errors as $path => $errorList) {
                    foreach (array_values($errorList) as $constraintViolation) {
                        $constraintViolationList->add(
                            new Symfony\Component\Validator\ConstraintViolation(
                                $constraintViolation->getMessage(),
                                $constraintViolation->getMessageTemplate(),
                                $constraintViolation->getParameters(),
                                $constraintViolation->getRoot(),
                                $path,
                                $constraintViolation->getInvalidValue(),
                                $constraintViolation->getPlural(),
                                $constraintViolation->getCode(),
                                $constraintViolation->getConstraint(),
                                $constraintViolation->getCause(),
                            );
                        );
                    }
                }

                throw new Symfony\Component\Validator\Exception\ValidationFailedException(null, $constraintViolationList);
            }';

            $expression[] = 'return $mapped;';

            return sprintf(GetMapper::MAPPER_TEMPLATE_WITH_VALIDATOR, implode('', $expression));
        }

        $expression[] = 'return $mapped;';

        return sprintf(GetMapper::MAPPER_TEMPLATE, implode('', $expression));
    }

    private function validateInput(GetMapper $query): void
    {
        foreach ([$query->from, $query->to] as $argument) {
            switch ($argument) {
                case 'array':
                case 'object':
                    break;
                default:
                    if (!class_exists($argument, false)) {
                        throw new \InvalidArgumentException(sprintf('Class %s does not exist.', $argument));
                    }
            }
        }

        foreach (['from' => $query->fromType, 'to' => $query->toType] as $key => $argument) {
            switch ($argument) {
                case null:
                case 'map':
                case 'array':
                case 'object':
                    continue 2;
            }

            /** @var string $argument */
            if (preg_match('/^map\{(?<separator>.+)\}$/', $argument, $matches)) {
                $this->{$key.'MapSeparator'} = $matches['separator'];
                continue;
            }

            throw new \InvalidArgumentException(sprintf('Invalid %sType argument. Allowed: `null`, `array`, `object`, `map{<separator>}`.', $key));
        }
    }

    /**
     * @param Property[] $properties
     *
     * @return Property[]
     */
    private function reduceProperties(array $properties): array
    {
        foreach ($properties as $index => $property) {
            if ($property->parent) {
                unset($properties[$index]);
            }
        }

        return $properties;
    }

    /**
     * @param Property[] $properties
     *
     * @return int number of properties with mirror properties
     */
    private function countMirrors(array $properties): int
    {
        $count = 0;
        foreach ($properties as $property) {
            if ($property->hasMirrorProperty()) {
                ++$count;
            }
        }

        return $count;
    }

    private function setOrigin(string $from, ?string $fromType, string $to, ?string $toType): void
    {
        $this->fromOrigin = $this->calculateOrigin($from, $fromType);
        $this->toOrigin = $this->calculateOrigin($to, $toType);
    }

    private function calculateOrigin(string $value, ?string $type): string
    {
        if ('array' === $value) {
            if (null === $type || Property::ORIGIN_ARRAY === $type) {
                return Property::ORIGIN_ARRAY;
            } elseif (null !== $type && str_starts_with($type, Property::ORIGIN_MAP)) {
                return Property::ORIGIN_MAP;
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid %sType argument. Allowed: `null`, `array`, `map{<separator>}`.', $type));
            }
        } elseif ('object' === $value) {
            if (null === $type || Property::ORIGIN_OBJECT === $type) {
                return Property::ORIGIN_OBJECT;
            } elseif (null !== $type && str_starts_with($type, Property::ORIGIN_MAP)) {
                return Property::ORIGIN_MAP_OBJECT;
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid %sType argument. Allowed: `null`, `object`, `map{<separator>}`.', $type));
            }
        } else { // class-string
            if (null !== $type && str_starts_with($type, Property::ORIGIN_MAP)) {
                return Property::ORIGIN_MAP;
            } elseif (null !== $type) {
                return $type;
            } else {
                return Property::ORIGIN_CLASS_OBJECT;
            }
        }
    }

    /**
     * @param Property[] $source
     * @param Property[] $destination
     */
    private function matchProperties(array &$source, array &$destination): void
    {
        foreach ($source as $property) {
            if ($property->hasMirrorProperty()) {
                continue;
            }

            $this->doMatchProperties($property, $destination);
        }

        foreach ($destination as $property) {
            if ($property->hasMirrorProperty()) {
                continue;
            }

            $this->doMatchProperties($property, $source);
        }
    }

    /**
     * @param Property[] $mirrors
     */
    private function doMatchProperties(Property $property, array &$mirrors): void
    {
        $name = $property->getName();
        foreach ($mirrors as $mirror) {
            $mirrorOrigin ??= $mirror->origin;
            if ($mirror->hasMirrorProperty()) {
                continue;
            }

            if ($name === $mirror->getName()) {
                $property->setMirrorProperty($mirror);

                return;
            }
        }

        if ($property->parent && !$property->parent->hasMirrorProperty()) {
            return;
        }

        $mirrorOrigin ??= ($this->fromOrigin === $property->origin ? $this->toOrigin : $this->fromOrigin);

        $property->setMirrorProperty(
            new Property(
                $property->getMirrorName(),
                $property->type,
                $property->parent ? $property->parent->getMirrorProperty() : null,
                $property->originClass,
                $property->isCollection(),
                $mirrorOrigin,
                $property->reflection,
                $property->reflectionParameter,
                null,
                null,
                null
            )
        );
    }

    /**
     * @param class-string  $class
     * @param string[]|null $serializerGroups
     *
     * @return Property[]
     */
    private function extractProperties(string $class, string $origin, ?array $serializerGroups = [], ?Property $parent = null): array
    {
        $reflectionClass = new \ReflectionClass($class);
        /** @var \ReflectionParameter[] $constructorParameters */
        $constructorParameters = $reflectionClass->getConstructor()?->getParameters() ?? [];
        /** @var \ReflectionProperty[] $properties */
        $properties = $reflectionClass->getProperties();

        $output = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $parameter = null;
            foreach ($constructorParameters as $constructorParameter) {
                if ($constructorParameter->getName() === $name) {
                    $parameter = $constructorParameter;
                }
            }

            $type = $parameter?->getType() ?? $property->getType();
            $currentProperty = new Property(
                $name,
                $type ? (string) $type : null,
                $parent,
                $class,
                $this->isCollection($type),
                $origin,
                $property,
                $parameter ?? null,
                new Mapper(
                    empty($attrAccessor = $property->getAttributes(Accessor::class)) ? null : $attrAccessor[0]->newInstance(),
                    empty($attrTargetProperty = $property->getAttributes(TargetProperty::class)) ? null : $attrTargetProperty[0]->newInstance(),
                    array_filter(array_map(fn (\ReflectionAttribute $attrMappingCallback): ?MappingCallback => is_subclass_of($attrMappingCallback->getName(), MappingCallback::class) || MappingCallback::class === $attrMappingCallback->getName() ? $attrMappingCallback->newInstance() : null, $property->getAttributes()))
                ),
                $this->useSerializer ? new Serializer(
                    empty($attrGroups = $property->getAttributes(Groups::class)) ? null : $attrGroups[0]->newInstance(),
                    empty($attrIgnore = $property->getAttributes(Ignore::class)) ? null : $attrIgnore[0]->newInstance(),
                    empty($attrMaxDepth = $property->getAttributes(MaxDepth::class)) ? null : $attrMaxDepth[0]->newInstance(),
                    empty($attrSerializedName = $property->getAttributes(SerializedName::class)) ? null : $attrSerializedName[0]->newInstance(),
                    empty($attrSerializedPath = $property->getAttributes(SerializedPath::class)) ? null : $attrSerializedPath[0]->newInstance(),
                ) : null,
                $this->useValidator ? new Validator(
                    array_filter(array_map(fn (\ReflectionAttribute $attrConstraint): ?PropertiesConstraint => is_subclass_of($attrConstraint->getName(), Constraint::class) ? new PropertiesConstraint($attrConstraint->getName(), $attrConstraint->getArguments()) : null, $property->getAttributes()))
                ) : null
            );

            if ($useSerializerGroups = is_array($serializerGroups) && $currentProperty->serializer?->groups) {
                $groups = $currentProperty->serializer?->groups?->getGroups();
            }
            if ($currentProperty->serializer?->ignore || ($useSerializerGroups && empty(array_intersect($serializerGroups, $groups ?? [])))) {
                continue;
            }

            $output[] = $currentProperty;
            if ($currentProperty->isCollection) {
                $collectionItemType = $this->getCollectionItemType($property);
                if ($collectionItemType && class_exists($collectionItemType->valueType, false)) {
                    $currentProperty->addChild(
                        $collectionItemProperty = new Property(
                            $collectionItemType->keyType,
                            $collectionItemType->valueType,
                            $currentProperty,
                            null,
                            false,
                            $origin,
                            null,
                            null,
                            null,
                            null,
                            null
                        )
                    );
                    $output[] = $collectionItemProperty;
                    $output = array_merge($output, $this->extractProperties($collectionItemType->valueType, $origin, $serializerGroups, $collectionItemProperty));
                }
            } elseif ($classType = $this->getClassIfClassType($type)) {
                $output = array_merge($output, $this->extractProperties($classType, $origin, $serializerGroups, $currentProperty));
            }
        }

        return $output;
    }

    private function isCollection(?\ReflectionType $type): bool
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

    private function getCollectionItemType(?\ReflectionProperty $property): ?CollectionItemType
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
}

class CollectionItemType
{
    public function __construct(
        public readonly string $keyType,
        public readonly string $valueType,
    ) {
    }
}