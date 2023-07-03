<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use PBaszak\MessengerCacheBundle\Attribute\Cache;
use PBaszak\MessengerCacheBundle\Contract\Required\Cacheable;
use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\DefaultExpressionBuilder;

#[Cache(pool: MessengerMapperBundle::ALIAS)]
class GetMapper implements Cacheable
{
    public function __construct(
        public readonly string $blueprint,
        public readonly GetterInterface $getterBuilder,
        public readonly SetterInterface $setterBuilder,
        public readonly FunctionInterface $functionBuilder = new DefaultExpressionBuilder(),
        public readonly LoopInterface $loopBuilder = new DefaultExpressionBuilder(),
        public readonly bool $isCollection = false,
    ) {}
}
