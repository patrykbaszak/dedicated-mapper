<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Handler;

use PBaszak\MessengerMapperBundle\Expression\ExpressionBuilder;
use PBaszak\MessengerMapperBundle\GetMapper;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

class GetMapperHandler
{
    public function __invoke(GetMapper $query): Mapper
    {
        $tree = Blueprint::create($query->blueprint, $query->isCollection);

        $expressionBuilder = new ExpressionBuilder(
            $tree,
            $query->getterBuilder,
            $query->setterBuilder,
        );

        return $expressionBuilder->createExpression()->getMapper();
    }
}
