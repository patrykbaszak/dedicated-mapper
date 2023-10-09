<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use PBaszak\DedicatedMapper\Expression\AbstractBuilder;
use PBaszak\DedicatedMapper\Expression\GetterBuilderInterface;
use PBaszak\DedicatedMapper\Expression\SetterBuilderInterface;

/**
 * Expression Builder for any class - using constructors, public properties and methods.
 */
class ClassObjectExpressionBuilder extends AbstractBuilder implements GetterBuilderInterface, SetterBuilderInterface
{
    
}
