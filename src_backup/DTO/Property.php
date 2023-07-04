<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO;

use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\DTO\Properties\Constraint;
use PBaszak\MessengerMapperBundle\DTO\Properties\Mapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Serializer;
use PBaszak\MessengerMapperBundle\DTO\Properties\Validator;
use PBaszak\MessengerMapperBundle\Utils\GetClassIfClassType;

class Property
{
    use GetClassIfClassType;

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

    public function getPath(string $separator = '.', bool $forceRootVariable = false): string
    {
        if (null === $this->parent || true === $this->parent->isCollection || $forceRootVariable) {
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

    public function getGetterExpression(string $variableName, string $mapSeparator = null, string $forcedOrigin = null, bool $forceRootVariable = false): string
    {
        try {
            $defaultValue = $this->reflection?->getDefaultValue();
        } catch (\ReflectionException) {
        }

        if (isset($defaultValue)) {
            return $this->isNullable()
                ? sprintf('%s ?? %s ?? null', $this->doGetGetterExpression($variableName, $mapSeparator, $forcedOrigin, $forceRootVariable), var_export($defaultValue, true))
                : sprintf('%s ?? %s', $this->doGetGetterExpression($variableName, $mapSeparator, $forcedOrigin, $forceRootVariable), var_export($defaultValue, true));
        }

        return $this->isNullable()
            ? sprintf('%s ?? null', $this->doGetGetterExpression($variableName, $mapSeparator, $forcedOrigin, $forceRootVariable))
            : $this->doGetGetterExpression($variableName, $mapSeparator, $forcedOrigin, $forceRootVariable);
    }

    private function doGetGetterExpression(string $variableName, string $mapSeparator = null, string $forcedOrigin = null, bool $forceRootVariable = false): string
    {
        if (self::ORIGIN_ARRAY === ($forcedOrigin ?? $this->origin)) {
            return sprintf('$%s[\'%s\']', $variableName, $this->getPath('\'][\'', $forceRootVariable));
        }
        if (self::ORIGIN_OBJECT === ($forcedOrigin ?? $this->origin)) {
            return sprintf('$%s->%s', $variableName, $this->getPath('->', $forceRootVariable));
        }
        if (self::ORIGIN_MAP === ($forcedOrigin ?? $this->origin)) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s[\'%s\']', $variableName, $this->getPath($mapSeparator, $forceRootVariable));
        }
        if (self::ORIGIN_MAP_OBJECT === ($forcedOrigin ?? $this->origin)) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s->%s', $variableName, $this->getPath($mapSeparator, $forceRootVariable));
        }
        if (self::ORIGIN_CLASS_OBJECT === ($forcedOrigin ?? $this->origin)) {
            if (null === $this->originClass) {
                throw new \InvalidArgumentException('Origin class is required for class object.');
            }

            $getterMethods = array_filter(
                [
                    $this->mapper?->accessor?->getter,
                    $this->getMirrorProperty()?->mapper?->accessor?->getter,
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
                if ($forcedOrigin) {
                    return sprintf('$%s->%s ?? $%s->%s', $variableName, $this->name, $variableName, $this->mirror->name);
                }

                return sprintf('$%s->%s', $variableName, $this->name);
            }

            return sprintf('$%s->%s()', $variableName, reset($getterMethods));
        }

        throw new \InvalidArgumentException(sprintf('Invalid origin: %s. Allowed: %s.', $forcedOrigin ?? $this->origin, implode(', ', self::ORIGINS)));
    }

    /**
     * @param string[]|null $validatorGroups
     */
    public function getPropertyExpression(string $variableName, array $validatorGroups = null, string $setterSeparator = null, string $getterSeparator = null, string $forcedOrigin = null, bool $forceRootVariable = false, string $sourceVariableName = 'data'): string
    {
        if ($this->isCollection() && $this->hasChildren()) {
            if (count($this->children) > 1) {
                throw new \LogicException('Collection with more than one child is not supported.');
            }
            $child = reset($this->children);
            $childPropertyInputVariableName = sprintf('%sInput', $this->getName());
            $expression = [sprintf('$%s = %s;', $childPropertyInputVariableName, $this->getMirrorProperty()->getGetterExpression($sourceVariableName, $getterSeparator, $forcedOrigin, $forceRootVariable))];
            $expression[] = sprintf('if (is_array($%s)) {', $childPropertyInputVariableName);
            $expression[] = sprintf('$%s = [];', $this->getName());
            $expression[] = sprintf('foreach ($%s as $%s) {', $childPropertyInputVariableName, $childVariableName = sprintf('%sItem', $this->getName()));
            $childPropertyVariableName = sprintf('%sProperty', $childVariableName);
            $expression[] = $child->getPropertyExpression(
                $childPropertyVariableName,
                $validatorGroups,
                $setterSeparator,
                $getterSeparator,
                $forcedOrigin,
                true,
                $childVariableName
            );
            $expression[] = sprintf('$%s[] = $%s;', $this->getName(), $childPropertyVariableName);
            $expression[] = '}';
            $expression[] = '} else {';
            if ($this->isNullable()) {
                $expression[] = sprintf('$%s = null;', $this->getName());
            } else {
                $expression[] = sprintf('throw new \\InvalidArgumentException(\'%s is not an array.\');', $this->getName());
            }
            $expression[] = '}';

            return implode('', $expression).
                $this->getSetterExpression(
                    '$'.$this->getName(),
                    $variableName,
                    $setterSeparator,
                    $validatorGroups,
                ).
                sprintf('unset($%s, $%s);', $this->getName(), $childPropertyVariableName);
        }

        /* If it is an class object */
        if ($this->hasChildren()) {
            if (self::ORIGIN_CLASS_OBJECT === $this->origin) {
                if (null === ($class = $this->getClassIfClassType($this->reflectionParameter?->getType() ?? $this->reflection?->getType()))) {
                    throw new \LogicException('Unable to get class for property '.$this->name);
                }
                $constructorArguments = [];
                $constructorVariableName = sprintf('%sConstructorParameters', $this->name);
                $expression = [sprintf('if (!($%s = %s) instanceof %s) {', $this->getName(), $this->getMirrorProperty()->getGetterExpression($sourceVariableName, $getterSeparator), $class)];
                $expression[] = sprintf('$%s = [];', $constructorVariableName);
                foreach ($this->children as $child) {
                    if (null !== $child->reflectionParameter) {
                        $constructorArguments[] = $child->getName();
                        $expression[] = $child->getPropertyExpression(
                            $constructorVariableName,
                            $validatorGroups,
                            null,
                            $getterSeparator,
                            'array',
                            true,
                            $sourceVariableName
                        );
                    }
                }
                $expression[] = sprintf('$%s = new %s(...$%s);', $this->getName(), $class, $constructorVariableName);
                foreach ($this->children as $child) {
                    if (!in_array($child->getName(), $constructorArguments, true)) {
                        $expression[] = $child->getPropertyExpression(
                            $this->getName(),
                            $validatorGroups,
                            $setterSeparator,
                            $getterSeparator,
                            null,
                            true,
                            $sourceVariableName
                        );
                    }
                }
                $expression[] = '}';

                return implode('', $expression).$this->getSetterExpression(
                    '$'.$this->getName(),
                    $variableName,
                    $setterSeparator,
                    $validatorGroups,
                    $forcedOrigin,
                    $forceRootVariable
                );
            } else {
                $expression = [
                    $forceRootVariable ?
                        sprintf(
                            '$%s = %s;',
                            $variableName,
                            in_array(
                                $this->origin,
                                [self::ORIGIN_ARRAY, self::ORIGIN_MAP],
                                true
                            ) ?
                                '[]' : '(object)[]'
                        ) :
                        $this->getSetterExpression(
                            in_array($this->origin, [self::ORIGIN_ARRAY, self::ORIGIN_MAP], true) ? '[]' : '(object)[]',
                            $variableName,
                            $setterSeparator,
                            $validatorGroups,
                            $forcedOrigin,
                            false
                        ),
                ];
                if (null !== ($class = $this->getClassIfClassType(
                    $this->getMirrorProperty()->reflectionParameter?->getType() ??
                        $this->getMirrorProperty()->reflection?->getType() ??
                        $this->reflectionParameter?->getType() ??
                        $this->reflection?->getType()
                )) && self::ORIGIN_CLASS_OBJECT !== $this->getMirrorProperty()?->origin) {
                    $expression[] = sprintf('if (($%s = %s) instanceof %s) {', $this->getName(), $this->getMirrorProperty()->getGetterExpression($sourceVariableName, $getterSeparator), $class);
                    foreach ($this->children as $child) {
                        $expression[] = $child->getSetterExpression(
                            $child->getMirrorProperty()->getGetterExpression($this->getName(), $getterSeparator, self::ORIGIN_CLASS_OBJECT, true),
                            $variableName,
                            $setterSeparator,
                            $validatorGroups,
                            $forcedOrigin,
                            false
                        );
                    }
                    $expression[] = '} else {';
                }
                foreach ($this->children as $child) {
                    if ($child->hasChildren()) {
                        $childVariableName = sprintf('%s%s', $variableName, ucfirst($child->getName()));
                        $childSourceVariableName = sprintf('%s%s', $sourceVariableName, ucfirst($child->getName()));
                        $expression[] = sprintf('$%s = %s;', $childSourceVariableName, $child->getMirrorProperty()->getGetterExpression($sourceVariableName, $getterSeparator));
                        $expression[] = $child->getPropertyExpression(
                            $childVariableName,
                            $validatorGroups,
                            $setterSeparator,
                            $getterSeparator,
                            $forcedOrigin,
                            $forceRootVariable,
                            $childSourceVariableName
                        );
                        $expression[] = $child->getSetterExpression(
                            '$'.$childVariableName,
                            $variableName,
                            $setterSeparator,
                            $validatorGroups,
                            $forcedOrigin,
                            true
                        );
                        continue;
                    }
                    $expression[] = $child->getPropertyExpression(
                        $variableName,
                        $validatorGroups,
                        $setterSeparator,
                        $getterSeparator,
                        $forcedOrigin,
                        $forceRootVariable,
                        $sourceVariableName
                    );
                }
                if (null !== $class && self::ORIGIN_CLASS_OBJECT !== $this->getMirrorProperty()?->origin) {
                    $expression[] = '}';
                }

                return implode('', $expression);
            }
        }

        return $this->getSetterExpression(
            $this->getMirrorProperty()->getGetterExpression($sourceVariableName, $getterSeparator),
            $variableName,
            $setterSeparator,
            $validatorGroups,
            $forcedOrigin,
            $forceRootVariable
        );
    }

    /**
     * @param string[]|null $validatorGroups
     */
    public function getSetterExpression(string $getterExpression, string $variableName, string $mapSeparator = null, array $validatorGroups = null, string $forcedOrigin = null, bool $forceRootVariable = false): string
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
                    ? sprintf('%s ?? null', $this->doGetSetterExpression('$var', $variableName, $mapSeparator, $forcedOrigin, $forceRootVariable))
                    : $this->doGetSetterExpression('$var', $variableName, $mapSeparator, $forcedOrigin, $forceRootVariable)
            );
        }
        try {
            $defaultValue = $this->reflection?->getDefaultValue();
        } catch (\ReflectionException) {
        }

        if (isset($defaultValue)) {
            return $this->isNullable()
                ? sprintf('%s ?? %s ?? null', $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin, $forceRootVariable), var_export($defaultValue, true))
                : sprintf('%s ?? %s', $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin, $forceRootVariable), var_export($defaultValue, true));
        }

        return ($this->isNullable()
            ? sprintf('%s ?? null', $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin, $forceRootVariable))
            : $this->doGetSetterExpression($getterExpression, $variableName, $mapSeparator, $forcedOrigin, $forceRootVariable)).';';
    }

    private function doGetSetterExpression(string $getterExpression, string $variableName, string $mapSeparator = null, string $forcedOrigin = null, bool $forceRootVariable = false): string
    {
        $getterExpression = $this->decorateGetterExpressionWithCallbacks(self::AS_DESTINATION, $getterExpression);
        if (self::ORIGIN_ARRAY === ($forcedOrigin ?? $this->origin)) {
            return sprintf('$%s[\'%s\'] = %s', $variableName, $this->getPath('\'][\'', $forceRootVariable), $getterExpression);
        }
        if (self::ORIGIN_OBJECT === ($forcedOrigin ?? $this->origin)) {
            return sprintf('$%s->%s = %s', $variableName, $this->getPath('->', $forceRootVariable), $getterExpression);
        }
        if (self::ORIGIN_MAP === ($forcedOrigin ?? $this->origin)) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s[\'%s\'] = %s', $variableName, $this->getPath($mapSeparator, $forceRootVariable), $getterExpression);
        }
        if (self::ORIGIN_MAP_OBJECT === ($forcedOrigin ?? $this->origin)) {
            if (null === $mapSeparator) {
                throw new \InvalidArgumentException('Map separator is required for map.');
            }

            return sprintf('$%s->%s = %s', $variableName, $this->getPath($mapSeparator, $forceRootVariable), $getterExpression);
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

    private function decorateGetterExpressionWithCallbacks(int $as, string $getterExpression, string $variableName = null): string
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
