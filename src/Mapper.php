<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use PBaszak\MessengerMapperBundle\Contract\GetMapper;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Mapper
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $cachedMessageBus,
        private ValidatorInterface $validator,
    ) {
        $this->messageBus = $cachedMessageBus;
    }

    public function map(
        mixed $data,
        mixed $from,
        mixed $to,
        ?string $fromType = null,
        ?string $toType = null,
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): mixed {
        $getMapper = new GetMapper($from, $to, $fromType, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
        $mapper = $this->handle($getMapper);

        return $getMapper->map($mapper, $data, $this->validator);
    }

    /**
     * @param mixed[]           $data
     * @param class-string      $toClass
     * @param 'array'|'map{%s}' $fromType - %s - map separator for nested array/object
     */
    public function fromArrayToClassObject(
        array $data,
        string $toClass,
        string $fromType = 'array',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): object {
        return $this->map($data, 'array', $toClass, $fromType, null, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param mixed[]            $data
     * @param class-string|null  $classTemplate
     * @param 'array'|'map{%s}'  $fromType      - %s - map separator for nested array/object
     * @param 'object'|'map{%s}' $toType        - %s - map separator for nested array/object
     */
    public function fromArrayToAnonymousObject(
        array $data,
        ?string $classTemplate = null,
        string $fromType = 'array',
        string $toType = 'object',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): object {
        if (!$classTemplate && 'array' === $fromType) {
            return (object) $data;
        }

        return (object) $this->map($data, 'array', $classTemplate ?? 'object', $fromType, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param mixed[]           $data
     * @param class-string|null $classTemplate
     * @param 'array'|'map{%s}' $fromType      - %s - map separator for nested array/object
     * @param 'array'|'map{%s}' $toType        - %s - map separator for nested array/object
     *
     * @return mixed[]
     */
    public function fromArrayToArray(
        array $data,
        ?string $classTemplate = null,
        string $fromType = 'array',
        string $toType = 'array',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): array {
        return $this->map($data, 'array', $classTemplate ?? 'array', $fromType, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param class-string       $class
     * @param 'object'|'map{%s}' $fromType - %s - map separator for nested array/object
     */
    public function fromAnonymousObjectToClassObject(
        object $data,
        string $class,
        string $fromType = 'object',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): object {
        return $this->map($data, 'object', $class, $fromType, null, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param class-string|null  $classTemplate
     * @param 'object'|'map{%s}' $fromType      - %s - map separator for nested array/object
     * @param 'object'|'map{%s}' $toType        - %s - map separator for nested array/object
     */
    public function fromAnonymousObjectToAnonymousObject(
        object $data,
        ?string $classTemplate = null,
        string $fromType = 'object',
        string $toType = 'object',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): object {
        if (!$classTemplate && 'object' === $fromType) {
            return $data;
        }

        return (object) $this->map($data, 'object', $classTemplate ?? 'object', $fromType, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param class-string|null  $classTemplate
     * @param 'object'|'map{%s}' $fromType      - %s - map separator for nested array/object
     * @param 'array'|'map{%s}'  $toType        - %s - map separator for nested array/object
     *
     * @return mixed[]
     */
    public function fromAnonymousObjectToArray(
        object $data,
        ?string $classTemplate = null,
        string $fromType = 'object',
        string $toType = 'array',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): array {
        return $this->map($data, 'object', $classTemplate ?? 'array', $fromType, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param class-string $class output
     */
    public function fromClassObjectToClassObject(
        object $data,
        string $class,
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): object {
        return $this->map($data, get_class($data), $class, null, null, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param class-string|null  $classTemplate
     * @param 'object'|'map{%s}' $toType        - %s - map separator for nested array/object
     */
    public function fromClassObjectToAnonymousObject(
        object $data,
        ?string $classTemplate = null,
        string $toType = 'object',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): object {
        return (object) $this->map($data, get_class($data), $classTemplate ?? 'object', null, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }

    /**
     * @param class-string|null $classTemplate
     * @param 'array'|'map{%s}' $toType        - %s - map separator for nested array/object
     *
     * @return mixed[]
     */
    public function fromClassObjectToArray(
        object $data,
        ?string $classTemplate = null,
        string $toType = 'array',
        bool $useValidator = false,
        ?array $validatorGroups = null,
        bool $useSerializer = false,
        ?array $serializerGroups = null,
    ): array {
        return $this->map($data, get_class($data), $classTemplate ?? 'array', null, $toType, $useValidator, $validatorGroups, $useSerializer, $serializerGroups);
    }
}
