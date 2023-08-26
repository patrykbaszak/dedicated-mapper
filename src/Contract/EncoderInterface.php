<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

interface EncoderInterface
{
    /**
     * Function encodes data to string
     *
     * @param array|object $data
     * @param string $format Supported formats: `json`
     * 
     * @return string|null `null` if encoding failed
     */
    public function encode(array|object $data, string $format): ?string;

    /**
     * Function decodes data from string
     * 
     * @param string $data
     * @param string $format Supported formats: `json`
     * @param string $type Supported types: `array`, `object`
     * 
     * @return null|array|object `null` if decoding failed
     */
    public function decode(string $data, string $format, string $type = 'array'): null|array|object;
}
