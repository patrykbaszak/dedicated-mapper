<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Accessor
{
    /**
     * @param string|null $setter setter method name - must be public and accept one argument
     * @example 'setKey' - method name (accepted only for objects with `setKey` method)
     * 
     * @param string|null $getter getter method name - must be public and require no arguments
     * @example 'getKey' - method name (accepted only for objects with `getKey` method)
     */
    public function __construct(
        public readonly ?string $setter = null,
        public readonly ?string $getter = null,
        public readonly array $options = [],
    ) {
        if ($this->setter === null && $this->getter === null) {
            throw new \InvalidArgumentException('You must provide at least one of setter or getter');
        }
    }
}
