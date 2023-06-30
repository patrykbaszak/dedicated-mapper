<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use PBaszak\MessengerCacheBundle\Attribute\Cache;
use PBaszak\MessengerCacheBundle\Contract\Required\Cacheable;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;

#[Cache(pool: MessengerMapperBundle::ALIAS)]
class GetMapper implements Cacheable
{
    public function __construct(
        public readonly string $blueprint,
        public readonly GetterInterface $getterBuilder,
        public readonly SetterInterface $setterBuilder,
        public readonly bool $isCollection = false,
    ) {}
}
