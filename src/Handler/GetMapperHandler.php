<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Handler;

use PBaszak\MessengerMapperBundle\Attribute\Accessor;
use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;
use PBaszak\MessengerMapperBundle\Contract\GetMapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Mapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Serializer;
use PBaszak\MessengerMapperBundle\DTO\Properties\Validator;
use PBaszak\MessengerMapperBundle\DTO\Property;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use ReflectionAttribute;
use ReflectionProperty;
use ReflectionType;
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

        if (class_exists($query->from)) {
            $sourceProperties = $this->extractProperties($query->from, $this->fromOrigin, null);
        }

        if (class_exists($query->to)) {
            $targetProperties = $this->extractProperties($query->to, $this->toOrigin, null);
        }

        return '';
    }

    private function validateInput(GetMapper $query): void
    {
        foreach ([$query->from, $query->to] as $argument) {
            switch ($argument) {
                case 'array':
                case 'object':
                    break;
                default:
                    if (!class_exists($argument)) {
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
                $this->{$key . 'MapSeparator'} = $matches['separator'];
                continue;
            }

            throw new \InvalidArgumentException(sprintf('Invalid %sType argument. Allowed: `null`, `array`, `object`, `map{<separator>}`.', $key));
        }
    }

    private function setOrigin(string $from, ?string $fromType, string $to, ?string $toType): void
    {
        $this->fromOrigin = $this->calculateOrigin($from, $fromType);
        $this->toOrigin = $this->calculateOrigin($to, $toType);
    }

    private function calculateOrigin(string $value, ?string $type): string
    {
        if ($value === 'array') {
            if ($type === null || $type === Property::ORIGIN_ARRAY) {
                return Property::ORIGIN_ARRAY;
            } elseif ($type !== null && str_starts_with($type, Property::ORIGIN_MAP)) {
                return Property::ORIGIN_MAP;
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid %sType argument. Allowed: `null`, `array`, `map{<separator>}`.', $type));
            }
        } elseif ($value === 'object') {
            if ($type === null || $type === Property::ORIGIN_OBJECT) {
                return Property::ORIGIN_OBJECT;
            } elseif ($type !== null && str_starts_with($type, Property::ORIGIN_MAP)) {
                return Property::ORIGIN_MAP_OBJECT;
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid %sType argument. Allowed: `null`, `object`, `map{<separator>}`.', $type));
            }
        } else { // class-string
            if ($type === Property::ORIGIN_OBJECT) {
                return Property::ORIGIN_OBJECT;
            } elseif ($type !== null && str_starts_with($type, Property::ORIGIN_MAP)) {
                return Property::ORIGIN_MAP;
            } else {
                return Property::ORIGIN_CLASS_OBJECT;
            }
        }
    }

    private function extractProperties(string $class, string $origin, ?Property $parent = null): array
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
                $parameter ?? $property ?? null,
                new Mapper(
                    ($property->getAttributes(Accessor::class))[0]?->newInstance() ?? null,
                    ($property->getAttributes(TargetProperty::class))[0]?->newInstance() ?? null,
                    array_filter(array_map(fn (ReflectionAttribute $attr) => is_subclass_of($attr->getName(), MappingCallback::class) ? $attr->newInstance() : null, ($property->getAttributes())))
                ),
                new Serializer(
                    ($property->getAttributes(Groups::class))[0]?->newInstance() ?? null,
                    ($property->getAttributes(Ignore::class))[0]?->newInstance() ?? null,
                    ($property->getAttributes(MaxDepth::class))[0]?->newInstance() ?? null,
                    ($property->getAttributes(SerializedName::class))[0]?->newInstance() ?? null,
                    ($property->getAttributes(SerializedPath::class))[0]?->newInstance() ?? null,
                ),
                new Validator(
                    array_filter(array_map(fn (ReflectionAttribute $attr) => is_subclass_of($attr->getName(), Constraint::class) ? $attr->newInstance() : null, ($property->getAttributes())))
                )
            );
            $output[] = $currentProperty;

            if ($currentProperty->isCollection) {
                $classType = $this->getCollectionItemType($property);
                if ($classType && class_exists($classType)) {
                    $output = array_merge($output, $this->extractProperties($classType, $origin, $currentProperty));
                }
            } elseif (($classType = $this->getClassIfClassType($type))) {
                $output = array_merge($output, $this->extractProperties($classType, $origin, $currentProperty));
            }
        }

        return $output;
    }

    private function isCollection(?ReflectionType $type): bool
    {
        if ($type === null) {
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

    private function getClassIfClassType(?ReflectionType $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            if (class_exists($typeName)) {
                return $typeName;
            }
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            $typeList = $type->getTypes();
            foreach ($typeList as $innerType) {
                $class = $this->getClassIfClassType($innerType);
                if ($class !== null) {
                    return $class;
                }
            }
        }

        return null;
    }

    private function getCollectionItemType(?ReflectionProperty $property): ?string
    {
        if ($property === null) {
            return null;
        }

        $docComment = $property->getDocComment();
        if ($docComment === false) {
            return null;
        }

        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = $docBlockFactory->create($docComment);
        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');

        if (count($varTags) === 0) {
            return null;
        }

        $type = $varTags[0]->getType();
        if ($type instanceof Array_) {
            $itemType = $type->getValueType();
            return $itemType->__toString();
        }

        return null;
    }
}
