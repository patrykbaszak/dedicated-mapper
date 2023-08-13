<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class InitialValueCallback
{
    /**
     * @param string  $callback - Just type php as string and init value of Your property.
     *                          example: `new DateTime()` - without `;` at the end and remember that.
     * @param mixed[] $options  - any options required but custom actions
     */
    public function __construct(
        public readonly string $callback,
        public readonly bool $useSourceInsteadIfExists = false,
        public readonly array $options = [],
    ) {
    }
}
