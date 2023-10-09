<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use PBaszak\DedicatedMapper\Contract\EncoderInterface;

class Encoder implements EncoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encode(array|object $data, string $format): ?string
    {
        return match ($format) {
            'json' => json_encode($data) ?: null,
            default => throw new \InvalidArgumentException('Unsupported format. Allowed formats: `json`.'),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data, string $format, string $type = 'array'): null|array|object
    {
        return match ($format) {
            'json' => match ($type) {
                'array' => json_decode($data, true) ?: null,
                'object' => json_decode($data) ?: null,
                default => throw new \InvalidArgumentException('Unsupported type. Allowed types: `array`, `object`.'),
            },
            default => throw new \InvalidArgumentException('Unsupported format. Allowed formats: `json`.'),
        };
    }

}
