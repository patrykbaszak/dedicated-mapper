<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerCacheBundle\Attribute\Cache;
use PBaszak\MessengerCacheBundle\Contract\Required\Cacheable;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Cache(pool: 'messenger_mapper')]
class GetMapper implements Cacheable
{
    public const MAPPER_TEMPLATE = 'return function (mixed $data): mixed {%s};';
    public const MAPPER_TEMPLATE_WITH_VALIDATOR = 'return function (mixed $data) use ($validator): mixed {%s};';

    /**
     * @param class-string|'array'|'object'   $from     associative array or object or class name
     * @param class-string|'array'|'object'   $to       associative array or object or class name
     * @param 'array'|'object'|'map{%s}'|null $fromType type of data which will be delivered
     *                                                  to mapper, %s - separator for nested array/object
     * @param 'array'|'object'|'map{%s}'|null $toType   type of data which mapper must return,
     *                                                  %s - separator for nested array/object
     *
     * @example map{.} - map from array/object or to array/object which nested keys are separated by dot
     * <code>
     * # map
     * $from = [
     *   'key' => 'value',
     *   'key.nested' => ['value']
     * ]
     * $to
     * </code>
     */
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly ?string $fromType = null,
        public readonly ?string $toType = null,
        public readonly bool $useValidator = false,
        public readonly ?array $validatorGroups = null,
        public readonly bool $useSerializer = false,
        public readonly ?array $serializerGroups = null,
    ) {
    }

    public function map(string $mapper, mixed $data, ?ValidatorInterface $validator = null): mixed
    {
        $mapper = eval($mapper);

        return $mapper($data);
    }
}
