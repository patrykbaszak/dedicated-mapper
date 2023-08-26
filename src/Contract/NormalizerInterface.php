<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Context;

interface NormalizerInterface
{
    /**
     * Function normalize any class object to array or anonymous object
     *
     * @param object $data
     * @param Context|null $context
     * @param string $type Supported types: `array`, `object`
     * 
     * @return array|object
     */
    public function normalize(
        object $data, 
        string $type = 'array',
        ?Context $context = null, 
    ): array|object;
    
    /**
     * Function denormalize data to class object
     *
     * @param object|array $data
     * @param class-string $blueprint
     * @param Context|null $context
     * 
     * @return object[]|object of type `class_object` 
     */
    public function denormalize(
        object|array $data, 
        string $blueprint,
        ?Context $context = null,
    ): array|object;
}
