<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Assets;

use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;

class SimpleDataSet
{
    public string $text;
    private int $number;
    public bool $bool;
    private ?string $nullable;
    public ?int $nullableInt;
    private ?bool $nullableBool;
    public ?float $nullableFloat;
    private ?array $nullableArray;
    public ?object $nullableObject;
    private ?\DateTime $nullableDateTime;
    public \DateTime $dateTime;
    #[TargetProperty('someTargetedProperty')]
    public string $targetProperty;

    public function __construct(
        string $text,
        int $number,
        bool $bool,
        ?string $nullable,
        ?int $nullableInt,
        ?bool $nullableBool,
        ?float $nullableFloat,
        ?array $nullableArray,
        ?object $nullableObject,
        ?\DateTime $nullableDateTime,
        \DateTime $dateTime,
        string $targetProperty,
    ) {
        $this->text = $text;
        $this->number = $number;
        $this->bool = $bool;
        $this->nullable = $nullable;
        $this->nullableInt = $nullableInt;
        $this->nullableBool = $nullableBool;
        $this->nullableFloat = $nullableFloat;
        $this->nullableArray = $nullableArray;
        $this->nullableObject = $nullableObject;
        $this->nullableDateTime = $nullableDateTime;
        $this->dateTime = $dateTime;
        $this->targetProperty = $targetProperty;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getNullable(): ?string
    {
        return $this->nullable;
    }

    public function getNullableInt(): ?int
    {
        return $this->nullableInt;
    }

    public function getNullableBool(): ?bool
    {
        return $this->nullableBool;
    }

    public function getNullableFloat(): ?float
    {
        return $this->nullableFloat;
    }

    public function getNullableArray(): ?array
    {
        return $this->nullableArray;
    }

    public function getNullableObject(): ?object
    {
        return $this->nullableObject;
    }

    public function getNullableDateTime(): ?\DateTime
    {
        return $this->nullableDateTime;
    }
}

return [
    'class' => SimpleDataSet::class,
    'objects' => [
        new SimpleDataSet(
            'text',
            1,
            true,
            'nullable',
            2,
            false,
            3.14,
            ['array'],
            new \stdClass(),
            new \DateTime(),
            new \DateTime(),
            'test'
        ),
        new SimpleDataSet(
            'text',
            1,
            true,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            new \DateTime(),
            'test2'
        ),
    ],
    'anonymousObjects' => [
        (object) [
            'text' => 'text',
            'number' => 1,
            'bool' => true,
            'nullable' => 'nullable',
            'nullableInt' => 2,
            'nullableBool' => false,
            'nullableFloat' => 3.14,
            'nullableArray' => ['array'],
            'nullableObject' => new \stdClass(),
            'nullableDateTime' => new \DateTime(),
            'dateTime' => new \DateTime(),
            'someTargetedProperty' => 'test',
        ],
        (object) [
            'text' => 'text',
            'number' => 1,
            'bool' => true,
            'nullable' => null,
            'nullableInt' => null,
            'nullableBool' => null,
            'nullableFloat' => null,
            'nullableArray' => null,
            'nullableObject' => null,
            'nullableDateTime' => null,
            'dateTime' => new \DateTime(),
            'someTargetedProperty' => 'test2',
        ],
    ],
    'arrays' => [
        [
            'text' => 'text',
            'number' => 1,
            'bool' => true,
            'nullable' => 'nullable',
            'nullableInt' => 2,
            'nullableBool' => false,
            'nullableFloat' => 3.14,
            'nullableArray' => ['array'],
            'nullableObject' => new \stdClass(),
            'nullableDateTime' => new \DateTime(),
            'dateTime' => new \DateTime(),
            'someTargetedProperty' => 'test',
        ],
        [
            'text' => 'text',
            'number' => 1,
            'bool' => true,
            'nullable' => null,
            'nullableInt' => null,
            'nullableBool' => null,
            'nullableFloat' => null,
            'nullableArray' => null,
            'nullableObject' => null,
            'nullableDateTime' => null,
            'dateTime' => new \DateTime(),
            'someTargetedProperty' => 'test2',
        ]
    ],
    'maps' => [
        'map{.}' => [
            'text' => 'text',
            'number' => 1,
            'bool' => true,
            'nullable' => 'nullable',
            'nullableInt' => 2,
            'nullableBool' => false,
            'nullableFloat' => 3.14,
            'nullableArray' => ['array'],
            'nullableObject' => new \stdClass(),
            'nullableDateTime' => new \DateTime(),
            'dateTime' => new \DateTime(),
            'someTargetedProperty' => 'test',
        ],
        'map{_}' => [
            'text' => 'text',
            'number' => 1,
            'bool' => true,
            'nullable' => null,
            'nullableInt' => null,
            'nullableBool' => null,
            'nullableFloat' => null,
            'nullableArray' => null,
            'nullableObject' => null,
            'nullableDateTime' => null,
            'dateTime' => new \DateTime(),
            'someTargetedProperty' => 'test2',
        ]
    ]
];
