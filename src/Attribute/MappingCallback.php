<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class MappingCallback
{
    /**
     * @param string  $callback - You got access to the `${{var}}` property with the value,
     *                          You can do antythig with it, just type php as string.
     *                          Remember that:
     *                          - you can use `$this` to access to the MapperService.
     *                          - $data and $output variables are used and You should not use them.
     * @param int     $priority - higher priority callbacks will be executed first
     * @param mixed[] $options  - any options required but custom actions
     */
    public function __construct(
        public readonly string $callback,
        public readonly int $priority = 0,
        public readonly bool $isValueNotFoundCallback = false,
        public readonly array $options = [],
    ) {
    }
}
