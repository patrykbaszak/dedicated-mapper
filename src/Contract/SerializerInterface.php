<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Context;

interface SerializerInterface
{
    /**
     * Function serialize data to string
     *
     * @param array|object $data
     * @param string $format Supported formats: `json`
     * @param Context|null $context
     * 
     * @return string
     */
    public function serialize(
        array|object $data, 
        string $format, 
        ?Context $context = null
    ): string;
    
    /**
     * Function deserialize data from string
     *
     * @param string $data
     * @param class-string $blueprint
     * @param string $format Supported formats: `json`
     * @param string $type Supported types: `class_object`, `array`, `object`
     * @param Context|null $context
     * 
     * @return array|object[]|object
     */
    public function deserialize(
        string $data, 
        string $blueprint, 
        string $format = 'json',
        string $type = 'class_object',
        ?Context $context = null
    ): array|object;
}