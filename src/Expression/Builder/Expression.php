<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use InvalidArgumentException;

class Expression
{
    public const AUXILIARY_EXPRESSIONS = [
        'if' => 'if ({%s}) {$this->expression .= %s}',
        'if_else' => 'if ({%s}) {$this->expression .= "%s"} else {$this->expression .= "%s"}',
    ];

    public string $expression = '';

    public array $expressionFullTemplate = [
        self::AUXILIARY_EXPRESSIONS['if'] => [
            '$this->checkIfSourceValueIsNotEmpty',
            [
                "if ({{existsStatement}}) {\n",
                self::AUXILIARY_EXPRESSIONS['if'] => [
                    '$this->hasFunctions',
                    [
                        '{{functionDeclarations}}',
                    ]
                ],
                self::AUXILIARY_EXPRESSIONS['if'] => [
                    '$this->isCollectionStorage',
                    [
                        '{{targetIteratorInitialAssignment}}',
                        "foreach ({{sourceIteratorAssignment}} as \${{index}} => \${{item}}) {\n",
                        self::AUXILIARY_EXPRESSIONS['if'] => [
                            '$this->discriminator',
                            [
                            ]
                        ],
                    ]
                ],
            ]
        ],
        '{{existsStatement}}' => [
            '{{functionDeclarations}}' => [
                '${{functionName}}' => '{{function}}',
                '${{itemFunctionName}}' => '{{itemFunction}}',
            ],
            '{{loop}}' => [
                '{{outputCollectionStorageDeclaration}}',
                '{{loopBody}}' => [
                    '{{itemExpression}}',
                ],
                '{{outputCollectionStorageAssignment}}'
            ],
            '{{expression}}' => [
                '{{switch}}' => [
                    '{{switchCase}}' => [
                        '{{functionCall}}',
                        '{{break}}'
                    ]
                ],
                '{{initiator}}',
                '{{callbacks}}',
                '{{finalAssignment}}'
            ]
        ],
        '{{else}}' => [
            '{{notFoundCallbacks}}',
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
        public readonly ?string $discriminator = null,
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
        if (!empty($this->discriminatorMap) && empty($this->discriminator)) {
            throw new InvalidArgumentException('Discriminator cannot be empty if discriminator map is not empty');
        }
        if (empty($this->discriminatorMap) && !empty($this->discriminator)) {
            throw new InvalidArgumentException('Discriminator map cannot be empty if discriminator is not empty');
        }
        if (!empty($this->discriminatorMap) && !empty($this->discriminator)) {
            foreach ($this->discriminatorMap as $discriminatorMap) {
                if (!is_string($discriminatorMap)) {
                    throw new InvalidArgumentException('Discriminator map must be a string');
                }
                if (!class_exists($discriminatorMap, false)) {
                    throw new InvalidArgumentException('Discriminator map must be a class');
                }
            }
        }

        if (empty($this->discriminatorMap) && empty($this->discriminator)) {
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
