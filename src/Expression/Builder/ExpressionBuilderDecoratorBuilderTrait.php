<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Contract\AbstractExpressionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Expression\Modificator\Mapper\NullablePropertyModificator;

trait ExpressionBuilderDecoratorBuilderTrait
{
    protected function decoratesBuilder(AbstractExpressionInterface $builder): AbstractExpressionInterface
    {
        $modificators = $this->sortByPriority(
            array_merge(
                array_filter([
                    $builder instanceof SetterInterface || $builder instanceof GetterInterface ? new NullablePropertyModificator() : null,
                ]),
                $builder->getModificators()
            )
        );

        foreach ($modificators as $modificator) {
            $modificator->setBuilder($builder);
            $builder = $modificator;
        }

        return $builder;
    }

    /**
     * @param ModificatorInterface[]
     *
     * @return ModificatorInterface[]
     */
    private function sortByPriority(array $modificators): array
    {
        usort(
            $modificators,
            fn (ModificatorInterface $a, ModificatorInterface $b) => $a->getPriority() <=> $b->getPriority()
        );

        return $modificators;
    }
}
