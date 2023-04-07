<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class MappingCallback
{
    public const FROM_SOURCE = 1;
    public const TO_DESTINATION = 2;

    /**
     * @example 'doSth' - method name (accepted only for objects with `doSth` method)
     * @example 'PBaszak\MessengerMapperBundle\Contract\GetMapper::map' - static method
     * @example '($var = %s) === null ? null : (string) $var' - expression (only one %s placeholder is allowed)
     *
     * @param int $priority - higher priority callbacks will be executed first
     */
    public function __construct(
        public readonly string $callback,
        public readonly int $priority = 0,
        public readonly int $activateOnMapping = self::FROM_SOURCE | self::TO_DESTINATION,
        public readonly array $options = [],
    ) {
    }
}
