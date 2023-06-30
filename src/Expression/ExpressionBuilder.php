<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

class ExpressionBuilder
{
    protected Mapper $mapper;
    protected static int $seed = 0;

    public function __construct(
        protected Blueprint $blueprint,
        protected GetterInterface $getterBuilder,
        protected SetterInterface $setterBuilder,
        protected string $originVariableName = 'data',
        protected string $targetVariableName = 'output',
    ) {}

    public function createExpression(): self
    {
        foreach ($this->blueprint->properties as $property) {
            if ($property->blueprint) {
                $mapper = (new self(
                    $property->blueprint,
                    $this->getterBuilder,
                    $this->setterBuilder,
                    sha1((string) self::$seed++ . $property->name . $this->originVariableName, false),
                    sha1((string) self::$seed++ . $property->name . $this->targetVariableName, false),
                ))->createExpression()->getMapper();
            }
        }
    }

    public function getMapper(): Mapper
    {
        return $this->mapper;
    }
}
