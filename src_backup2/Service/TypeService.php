<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

class TypeService implements TypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function calculateType(string $value, ?string $type): int
    {
        /* @var ?string $type */
        switch (true) {
            case 'array' === $type:
                return self::ARRAY;
            case 'object' === $type:
                return self::OBJECT;
            case (is_string($type) && preg_match('/^map\{.*\}$/', $type)) ? true : false:
                if ('object' === $value) {
                    return self::MAP_OBJECT;
                } else {
                    return self::MAP;
                }
                // no break
            default:
                switch (true) {
                    case class_exists($value):
                        return self::CLASS_OBJECT;
                    case 'array' === $value:
                        return self::ARRAY;
                    case 'object' === $value:
                        return self::OBJECT;
                }
        }

        throw new \InvalidArgumentException(sprintf('Invalid input/output type: %s or input/output template %s.', $type, $value));
    }
}
