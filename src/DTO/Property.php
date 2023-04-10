<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO;

use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\DTO\Properties\Constraint;
use PBaszak\MessengerMapperBundle\DTO\Properties\Mapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Serializer;
use PBaszak\MessengerMapperBundle\DTO\Properties\Validator;

class Property
{
    public const AS_SOURCE = 1;
    public const AS_DESTINATION = 2;

    public const ORIGIN_ARRAY = 'array';
    public const ORIGIN_MAP = 'map';
    public const ORIGIN_MAP_OBJECT = 'map_object'; // anonymous object of map
    public const ORIGIN_OBJECT = 'object'; // anonymous object
    public const ORIGIN_CLASS_OBJECT = 'class_object'; // object of class

    private const ORIGINS = [
        self::ORIGIN_ARRAY,
        self::ORIGIN_MAP,
        self::ORIGIN_MAP_OBJECT,
        self::ORIGIN_OBJECT,
        self::ORIGIN_CLASS_OBJECT,
    ];

    private self $mirror;
    /** @var self[] */
    private array $children = [];

    public function __construct(
        public string $name,
        public ?string $type = null,
        public ?self $parent = null,
        public ?string $originClass = null,
        public bool $isCollection = false,
        public string $origin = self::ORIGIN_ARRAY,
        public null|\ReflectionProperty $reflection = null,
        public null|\ReflectionParameter $reflectionParameter = null,
        public null|Mapper $mapper = null,
        public null|Serializer $serializer = null,
        public null|Validator $validator = null,
    ) {
        if (!in_array($origin, self::ORIGINS)) {
            throw new \InvalidArgumentException(sprintf('Invalid origin: %s. Allowed: %s.', $origin, implode(', ', self::ORIGINS)));
        }

        if ($parent) {
            $parent->addChild($this);
        }
    }

    public function addChild(self $property): void
    {
        $this->children[$property->name] = $property;
    }

    public function hasChild(string $name): bool
    {
        return isset($this->children[$name]);
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function getMirrorProperty(): self
    {
        return $this->mirror;
    }

    public function hasMirrorProperty(): bool
    {
        return isset($this->mirror);
    }

    public function setMirrorProperty(self $property): void
    {
        if (!isset($this->mirror)) {
            $this->mirror = $property;
            $this->mirror->setMirrorProperty($this);
        }
    }

    public function getName(): string
    {
        if (self::ORIGIN_CLASS_OBJECT === $this->origin || !isset($this->mirror)) {
            return $this->name;
        }

        return $this->mirror->serializer?->serializedName?->getSerializedName()
            ?? $this->mirror->mapper?->targetProperty?->name
            ?? $this->name;
    }

    public function getMirrorName(): string
    {
        if ($this->hasMirrorProperty()) {
            return $this->mirror->getName();
        }

        return $this->serializer?->serializedName?->getSerializedName()
            ?? $this->mapper?->targetProperty?->name
            ?? $this->name;
    }

    public function getPath(string $separator = '.'): string
    {
        if (null === $this->parent || true === $this->parent->isCollection) {
            return $this->getName();
        }

        return sprintf('%s%s%s%s', $this->serializer?->serializedPath?->getSerializedPath(), $this->parent->getPath($separator), $separator, $this->getName());
    }

    public function isPublic(): bool
    {
        return (bool) $this->reflection?->isPublic();
    }

    public function isIgnored(): bool
    {
        return (bool) $this->mirror->serializer?->ignore;
    }

    public function isNullable(): bool
    {
        if (null !== $this->type && (str_starts_with($this->type, '?') || 'mixed' === $this->type || false !== strpos($this->type, 'null'))) {
            return true;
        }

        if (null !== $this->reflectionParameter) {
            return $this->reflectionParameter->allowsNull();
        }

        return (bool) $this->reflection?->getType()?->allowsNull();
    }

    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    public function isInGroup(string $group): bool
    {
        if (null === $this->mirror->serializer?->groups) {
            return false;
        }

        return in_array($group, $this->mirror->serializer->groups->getGroups());
    }

    /** @return MappingCallback[] */
    public function getSelfMappingCallbacks(int $activateOnMapping): array
    {
        $callbacks = [];

        if (null !== $this->mapper) {
            foreach ($this->mapper->mappingCallbacks as $callback) {
                if ($callback->activateOnMapping & $activateOnMapping) {
                    $callbacks[] = $callback;
                }
            }
        }

        return $callbacks;
    }

    public function getGetterExpression(string $variableName, ?string $mapSeparator = null): string
    {
        try {
            $defaultValue = $this->reflection?->getDefaultValue();
        } catch (\ReflectionException) {
        }

        if (isset($defaultValue)) {
            return $this->isNullable()
                ? sprintf('%s ?? %s ?? null', $this->doGetGetterExpression($variableName, $mapSeparator), var_export($defaultValue, true))
                : sprintf('%s ?? %s', $this->doGetGetterExpression($variableName, $mapSeparator), var_export($defaultValue, true));
        }

        return $this->isNullable()
            ? sprintf('%s ?? null', $this->doGetGetterExpression($variableName, $mapSeparator))
            : $this->doGetGetterExpression($variableName, $mapSeparator);
    }

    private function doGetGetterExpression(string $variableName, ?string $mapSeparator = null): string
    {
        if (self::ORIGIN_ARRAY === $this->origin) {
            return sprintf('$%s[\'%s\']', $variableName, $this->getPath('\'][\''));
        }
        if (self::ORIGIN_OBJECT === $this->origin) {
            return sprintf('$%s->%s', $variableName, $this->getPath('->'));
        }
        if (self::ORIGIN_MAP === $this->origin) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s[\'%s\']', $variableName, $this->getPath($mapSeparator));
        }
        if (self::ORIGIN_MAP_OBJECT === $this->origin) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s->%s', $variableName, $this->getPath($mapSeparator));
        }
        if (self::ORIGIN_CLASS_OBJECT === $this->origin) {
            if (null === $this->originClass) {
                throw new \InvalidArgumentException('Origin class is required for class object.');
            }

            $getterMethods = array_filter(
                [
                    $this->mapper?->accessor?->getter,
                    'get'.ucfirst($this->name),
                    'is'.ucfirst($this->name),
                    $this->name,
                ],
                fn ($method) => $method && method_exists($this->originClass, $method)
            );

            if (($isEmpty = empty($getterMethods)) && !$this->isPublic()) {
                throw new \InvalidArgumentException(sprintf('Getter method not found for property %s in class %s and property is not public.', $this->name, $this->originClass));
            }

            if ($isEmpty) {
                return sprintf('$%s->%s', $variableName, $this->name);
            }

            return sprintf('$%s->%s()', $variableName, reset($getterMethods));
        }

        throw new \InvalidArgumentException(sprintf('Invalid origin: %s. Allowed: %s.', $this->origin, implode(', ', self::ORIGINS)));
    }

    /**
     * @param string[]|null $validatorGroups
     */
    public function getPropertyExpression(string $variableName, ?array $validatorGroups = null, ?string $setterSeparator = null, ?string $getterSeparator = null): string
    {
        if ($this->isCollection() && $this->hasChildren()) {
            return '';
        }

        if ($this->hasChildren()) {
            return '';
        }

        $getterExpression = $this->getMirrorProperty()->getGetterExpression('data', $getterSeparator);

        return $this->getSetterExpression($getterExpression, $variableName, $setterSeparator, $validatorGroups);
    }

    /**
     * @param string[]|null $validatorGroups
     */
    public function getSetterExpression(string $getterExpression, string $variableName, ?string $mapSeparator = null, ?array $validatorGroups = null, ?string $forcedOrigin = null): string
    {
        if (!empty($this->validator->constraints)) {
            $getterExpression = sprintf(
                'if (!empty($validationErrors = $validator->validate(($var = %s), [%s], %s)) ($errors[%s] = $validationErrors]);%s;',
                $getterExpression,
                implode(
                    ',',
                    array_map(
                        fn (Constraint $constraint) => sprintf('new %s(...%s)', $constraint->className, var_export($constraint->arguments, true)),
                        $this->validator->constraints
                    )
                ),
                $validatorGroups ? var_export($validatorGroups, true) : 'null',
                $this->getPath('.'),
                $this->isNullable()
                    ? sprintf('%s ?? null', $this->doGetSetterExpression('$var', $variableName, $mapSeparator, $forcedOrigin))
                    : $this->doGetSetterExpression('$var', $variableName, $mapSeparator, $forcedOrigin)
            );
        }
        try {
            $defaultValue = $this->reflection?->getDefaultValue();
        } catch (\ReflectionException) {
        }

        if (isset($defaultValue)) {
            return $this->isNullable()
                ? sprintf('%s ?? %s ?? null', $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin), var_export($defaultValue, true))
                : sprintf('%s ?? %s', $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin), var_export($defaultValue, true));
        }

        return ($this->isNullable()
            ? sprintf('%s ?? null', $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin))
            : $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin)).';';
    }

    private function doGetSetterExpression(string $getterExpression, string $variableName, ?string $mapSeparator = null, ?string $forcedOrigin = null): string
    {
        $getterExpression = $this->decorateGetterExpressionWithCallbacks(self::AS_DESTINATION, $getterExpression);
        if (self::ORIGIN_ARRAY === ($forcedOrigin ?? $this->origin)) {
            return sprintf('$%s[\'%s\'] = %s', $variableName, $this->getPath('\'][\''), $getterExpression);
        }
        if (self::ORIGIN_OBJECT === ($forcedOrigin ?? $this->origin)) {
            return sprintf('$%s->%s = %s', $variableName, $this->getPath('->'), $getterExpression);
        }
        if (self::ORIGIN_MAP === ($forcedOrigin ?? $this->origin)) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s[\'%s\'] = %s', $variableName, $this->getPath($mapSeparator), $getterExpression);
        }
        if (self::ORIGIN_MAP_OBJECT === ($forcedOrigin ?? $this->origin)) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s->%s = %s', $variableName, $this->getPath($mapSeparator), $getterExpression);
        }
        if (self::ORIGIN_CLASS_OBJECT === ($forcedOrigin ?? $this->origin)) {
            if (null === $this->originClass) {
                throw new \InvalidArgumentException('Origin class is required for class object.');
            }

            $setterMethods = array_filter(
                [
                    $this->mapper?->accessor?->setter,
                    'set'.ucfirst($this->name),
                    $this->name,
                ],
                fn ($method) => $method && method_exists($this->originClass, $method)
            );

            if (($isEmpty = empty($setterMethods)) && null === $this->reflectionParameter && !$this->isPublic()) {
                throw new \InvalidArgumentException(sprintf('Setter method not found for property %s in class %s and property is not public.', $this->name, $this->originClass));
            }

            if ($isEmpty) {
                return sprintf('$%s->%s = %s', $variableName, $this->name, $getterExpression);
            }

            return sprintf('$%s->%s(%s)', $variableName, reset($setterMethods), $getterExpression);
        }

        throw new \InvalidArgumentException(sprintf('Invalid origin: %s. Allowed: %s.', $this->origin, implode(', ', self::ORIGINS)));
    }

    private function decorateGetterExpressionWithCallbacks(int $as, string $getterExpression, ?string $variableName = null): string
    {
        $mirrorAs = $as ^ (self::AS_SOURCE | self::AS_DESTINATION);
        if (empty($mirrorCallbacks = $this->mirror->getSelfMappingCallbacks($mirrorAs)) && empty($selfCallbacks = $this->getSelfMappingCallbacks($as))) {
            return $getterExpression;
        }

        $callbacks = array_merge($selfCallbacks ?? [], $mirrorCallbacks);

        foreach ($this->sortCallbacks($callbacks) as $callback) {
            if (false !== strpos($callback->callback, '%s')) {
                $getterExpression = sprintf($callback->callback, $getterExpression);
            } elseif (false !== strpos($callback->callback, '::')) {
                $getterExpression = sprintf('%s(%s)', $callback->callback, $getterExpression);
            } elseif (null !== $variableName) {
                $getterExpression = sprintf('$%s->%s(%s)', $variableName, $callback->callback, $getterExpression);
            } else {
                throw new \LogicException('Unsupported callback.');
            }
        }

        return $getterExpression;
    }

    /**
     * @param MappingCallback[] $callbacks
     *
     * @return MappingCallback[]
     */
    private function sortCallbacks(array $callbacks): array
    {
        usort($callbacks, function (MappingCallback $a, MappingCallback $b) {
            return $a->priority <=> $b->priority;
        });

        return $callbacks;
    }
}
