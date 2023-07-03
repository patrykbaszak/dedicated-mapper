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
        return new Getter(
            sprintf(
                '$%s[\'%s\']',
                Getter::SOURCE_VARIABLE_NAME,
                $property->originName
            )
        );
    }
    
    public function createSimpleObjectGetter(Property $property): Getter
    {
        return $this->createGetter($property);
    }

    public function createSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                '$%s[\'%s\'] = %s;',
                Setter::TARGET_VARIABLE_NAME,
                $property->originName,
                Setter::GETTER_EXPRESSION
            )
        );
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                '$%s[\'%s\'] = new %s(%s);',
                Setter::TARGET_VARIABLE_NAME,
                $property->originName,
                $property->getClassType(),
                Setter::GETTER_EXPRESSION
            )
        );
    }
}
