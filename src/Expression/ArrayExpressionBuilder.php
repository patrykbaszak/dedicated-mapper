<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Modificator\PBaszakMessengerMapper;
use PBaszak\MessengerMapperBundle\Properties\Property;

class ArrayExpressionBuilder implements GetterInterface, SetterInterface
{
    private const PARENT_EXPRESSION = '[\'%s\']';
    private const PARENT_PLACEHOLDER = '{{parents}}';
    private const GETTER_EXPRESSION = '$' . Getter::SOURCE_VARIABLE_NAME . self::PARENT_PLACEHOLDER . '[\'%s\']';
    private const SETTER_EXPRESSION = '$' . Setter::TARGET_VARIABLE_NAME . self::PARENT_PLACEHOLDER . '[\'%s\'] = ' . Setter::GETTER_EXPRESSION;

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function __construct(
        public array $getterModificators = [
            new PBaszakMessengerMapper()
        ],
        public array $setterModificators = [
            new PBaszakMessengerMapper()
        ]
    ) {}

    public function createGetter(Property $property): Getter
    {

    }

    public function createSetter(Property $property): Setter
    {
        
    }
}
