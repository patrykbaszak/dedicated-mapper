<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use InvalidArgumentException;
use PBaszak\DedicatedMapper\Utils\HasNotFilledPlaceholdersTrait;

class ExpressionTemplate
{
    use HasNotFilledPlaceholdersTrait;

    public array $expressionFullTemplate = [
        '{{functionDeclarations}}' => [
            '$this->hasFunctions' => '{{functionDeclarations}}',
            '$this->nestedExpression?->hasFunctions' => '{{itemFunctionDeclarations}}'
        ],
        '{{loop}}' => [
            '$this->isCollectionStorage' => [
                '{{targetIteratorInitialAssignment}}',
                "foreach ({{sourceIteratorAssignment}} as \${{index}} => \${{item}}) {\n{{itemExpression}}}\n",
                '{{targetIteratorFinalAssignment}}'
            ]
        ],
        '{{switch}}' => [
            '$this->hasDiscriminator' => '{{switchCase}}',
        ],
        '{{itemExpression}}' => [
            '$this->hasDiscriminator' => '{{switch}}',
            '$this->hasOwnInitiator && $this->isInitiatorUsedSource' => '{{initiator}}',
            '$this->hasCallbacks' => '{{callbacks}}',
            '{{finalAssignment}}'
        ],
        '{{expression}}' => [
            '$this->hasDiscriminator' => '{{switch}}',
            '$this->hasOwnInitiator' => '{{initiator}}',
            '$this->hasCallbacks' => '{{callbacks}}',
            '{{finalAssignment}}'
        ],
        '{{notFoundExpression}}' => [
            '$this->hasNotFoundCallbacks' => '{{notFoundCallbacks}}',
        ],
        '{{init}}' => [
            '$this->checkIfSourceValueIsNotEmpty' => [
                '$this->hasDefaultValue || $this->hasNotFoundCallbacks' => "if ({{existsStatement}}) {\n{{functionDeclarations}}{{loop}}{{expression}}} else {\n{{notFoundExpression}}}\n",
                '(!$this->hasDefaultValue && !$this->hasNotFoundCallbacks) || ($this->hasOwnInitiator && $this->isInitiatorUsedSource)' => "if ({{existsStatement}}) {\n{{functionDeclarations}}{{loop}}{{expression}}}\n",
            ],
            '!$this->checkIfSourceValueIsNotEmpty' => [
                '$this->hasNotFoundCallbacks && !$this->hasDefaultValue' => "if (!{{existsStatement}}) {\n{{notFoundCallbacks}}}\n{{functionDeclarations}}{{loop}}{{expression}}",
                '!$this->hasDefaultValue && !$this->hasNotFoundCallbacks' => "{{functionDeclarations}}{{loop}}{{expression}}",
            ]
        ]
    ];

    public function __construct(
        // basic info
        public readonly string $id,
        public readonly int $propertyType,
        public readonly array $types,
        public readonly bool $hasCallbacks = false,
        // if property is not found
        public readonly bool $checkIfSourceValueIsNotEmpty = false,
        public readonly bool $hasNotFoundCallbacks = false,
        public readonly bool $hasDefaultValue = false,
        // if property has more than one class type
        public readonly bool $hasDiscriminator = false,
        public readonly array $discriminatorMap = [],
        // if property is a collection
        public readonly bool $isCollectionStorage = false,
        public readonly bool $isCollectionItem = false,
        public readonly ?self $nestedExpression = null,
        // if property is a class type
        public readonly bool $hasFunctions = false,
        public readonly bool $isPathVariableUsed = false,
        public readonly bool $passNonNestedSourceVariable = false,
        // if property has own initiator
        public readonly bool $hasOwnInitiator = false, // like new \DateTime()
        public readonly bool $isInitiatorUsedSource = false, // like new \DateTime($source)
    ) {
        $this->validateBasicInfo();
        $this->validateIsNotFound();
        $this->validateHasMoreThanOneClassType();
        $this->validateIsCollection();
        $this->validateIsClassType();
        $this->validateHasOwnInitiator();
    }

    public function __toString(): string
    {
        $expression = '{{init}}';
        $args = $this->expressionFullTemplate;
        $getValue = function (array $expr) use (&$getValue) {
            $value = '';
            foreach ($expr as $key => $value) {
                if (is_int($key)) {
                    $value .= is_string($value) ? $value : $getValue($value);
                }
                if (is_string($key)) {
                    if (eval($key)) {
                        $value .= is_string($value) ? $value : $getValue($value);
                    }
                }
                if (is_array($value)) {
                    $value .= $getValue($value);
                }
                return $value;
            }
        };

        do {
            foreach ($args as $placeholder => $expr) {
                if (is_string($expr)) {
                    $expression = str_replace($placeholder, $expr, $expression);
                } else {
                    $value = $getValue($expr);
                    $expression = str_replace($placeholder, $value, $expression);
                }
            }
            str_replace(array_keys($args), array_values($args), $expression);
        } while ($this->hasNotFilledPlaceholders(array_keys($args), $expression));

        return $expression;
    }

    private function validateBasicInfo(): void
    {
        if (empty($this->id)) {
            throw new InvalidArgumentException('Id cannot be empty');
        }
        if ($this->propertyType > 15) {
            throw new InvalidArgumentException('Property type cannot be greater than 15');
        }
        if (empty($this->types)) {
            throw new InvalidArgumentException('Types cannot be empty');
        }
        foreach ($this->types as $type) {
            if (!is_string($type)) {
                throw new InvalidArgumentException('Types must be a string');
            }
            if (!in_array($type, ['null', 'string', 'int', 'float', 'bool', 'array', 'object', 'true', 'false']) && !class_exists($type, false)) {
                throw new InvalidArgumentException('Wrong type.');
            }
            if (class_exists($type, false) && !$this->hasFunctions) {
                throw new InvalidArgumentException('Type cannot be a class if property has no functions');
            }
        }
    }

    private function validateIsNotFound(): void
    {
        if ($this->hasNotFoundCallbacks && !$this->checkIfSourceValueIsNotEmpty) {
            throw new InvalidArgumentException('Cannot have not found callbacks if check if source value is not empty is false');
        }
        if ($this->hasNotFoundCallbacks && $this->hasDefaultValue) {
            throw new InvalidArgumentException('Cannot have not found callbacks if has default value is true');
        }
    }

    private function validateHasMoreThanOneClassType(): void
    {
        if (!empty($this->discriminatorMap) && $this->hasDiscriminator) {
            throw new InvalidArgumentException('Discriminator cannot be empty if discriminator map is not empty');
        }
        if (empty($this->discriminatorMap) && !$this->hasDiscriminator) {
            throw new InvalidArgumentException('Discriminator map cannot be empty if discriminator is not empty');
        }
        if (!empty($this->discriminatorMap) && !$this->hasDiscriminator) {
            foreach ($this->discriminatorMap as $discriminatorMap) {
                if (!is_string($discriminatorMap)) {
                    throw new InvalidArgumentException('Discriminator map must be a string');
                }
                if (!class_exists($discriminatorMap, false)) {
                    throw new InvalidArgumentException('Discriminator map must be a class');
                }
            }
        }

        if (empty($this->discriminatorMap) && $this->hasDiscriminator) {
            return;
        }

        foreach ($this->types as $type) {
            if ('null' !== $type && !class_exists($type, false)) {
                throw new InvalidArgumentException('Type must be a class if property has more than one class type. Null is allowed.');
            }
        }
    }

    private function validateIsCollection(): void
    {
        if ($this->isCollectionItem && $this->isCollectionStorage) {
            throw new InvalidArgumentException('Colection item cannot be a collection storage. Collection in collection are not available.');
        }
        if ($this->isCollectionItem && $this->hasDefaultValue) {
            throw new InvalidArgumentException('Collection item cannot have default value.');
        }
        if ($this->isCollectionItem && $this->hasNotFoundCallbacks) {
            throw new InvalidArgumentException('Collection item cannot have not found callbacks.');
        }
        if ($this->isCollectionItem && $this->checkIfSourceValueIsNotEmpty) {
            throw new InvalidArgumentException('Collection item cannot check if source value is not empty.');
        }
        if ($this->isCollectionItem && ($this->hasOwnInitiator && !$this->isInitiatorUsedSource)) {
            throw new InvalidArgumentException('Collection item cannot have own initiator which does not use source.');
        }
        if ($this->isCollectionStorage && !$this->nestedExpression) {
            throw new InvalidArgumentException('Collection storage must have nested expression.');
        }
        if ($this->isCollectionStorage && !(bool) ($this->propertyType & 4)) {
            throw new InvalidArgumentException('PropertyType is not collection.');
        }
    }

    private function validateIsClassType(): void
    {
        if ($this->hasFunctions) {
            foreach ($this->types as $type) {
                if ('null' !== $type && !class_exists($type, false)) {
                    throw new InvalidArgumentException('Type must be a class if property has more than one class type. Null is allowed.');
                }
            }
        }
    }

    private function validateHasOwnInitiator(): void
    {
        if ($this->hasOwnInitiator && !$this->isInitiatorUsedSource && $this->checkIfSourceValueIsNotEmpty) {
            throw new InvalidArgumentException('Own initiator cannot be used if check if source value is not empty is true.');
        }
        if ($this->hasOwnInitiator && $this->hasFunctions) {
            throw new InvalidArgumentException('Own initiator cannot be used if property has functions.');
        }
    }
}
