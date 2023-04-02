<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Assets;

use PBaszak\MessengerMapperBundle\Attribute\Accessor;
use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;

class NestedDataSet
{
    public string $text;
    private int $number;
    public bool $bool;
    private ?string $nullable;
    public ?int $nullableInt;
    #[Accessor('setBool', 'isTrue')]
    private ?bool $nullableBool;
    public ?float $nullableFloat;
    private ?array $nullableArray;
    public ?object $nullableObject;
    private ?\DateTime $nullableDateTime;
    public \DateTime $dateTime;
    #[TargetProperty('someTargetedProperty')]
    public SimpleDataSet $simpleDataSet;

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
        SimpleDataSet $simpleDataSet,
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
        $this->simpleDataSet = $simpleDataSet;
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

    public function isTrue(): ?bool
    {
        return $this->nullableBool;
    }

    public function setBool(?bool $bool): void
    {
        $this->nullableBool = $bool;
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

    public static function getDataSet(): array
    {

        return [
            'class' => NestedDataSet::class,
            'objects' => [
                new NestedDataSet(
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
                    )
                ),
                new NestedDataSet(
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
                    )
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
                    'someTargetedProperty' => (object) [
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
                    'someTargetedProperty' => new SimpleDataSet(
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
                    'someTargetedProperty' => [
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
                    'someTargetedProperty' => new SimpleDataSet(
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
                    )
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
                    'nullableDateTime' => null,
                    'dateTime' => new \DateTime(),
                    'someTargetedProperty.text' => 'text',
                    'someTargetedProperty.number' => 1,
                    'someTargetedProperty.bool' => true,
                    'someTargetedProperty.nullable' => 'nullable',
                    'someTargetedProperty.nullableInt' => 2,
                    'someTargetedProperty.nullableBool' => false,
                    'someTargetedProperty.nullableFloat' => 3.14,
                    'someTargetedProperty.nullableArray' => ['array'],
                    'someTargetedProperty.nullableObject' => new \stdClass(),
                    'someTargetedProperty.nullableDateTime' => new \DateTime(),
                    'someTargetedProperty.dateTime' => '2022-01-01T00:10:00+00:00',
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
                    'dateTime' => '2022-01-01T00:00:00+00:00',
                    'someTargetedProperty_text' => 'text',
                    'someTargetedProperty_number' => 1,
                    'someTargetedProperty_bool' => true,
                    'someTargetedProperty_nullable' => null,
                    'someTargetedProperty_nullableInt' => null,
                    'someTargetedProperty_nullableBool' => null,
                    'someTargetedProperty_nullableFloat' => null,
                    'someTargetedProperty_nullableArray' => null,
                    'someTargetedProperty_nullableObject' => null,
                    'someTargetedProperty_nullableDateTime' => null,
                    'someTargetedProperty_dateTime' => '2021-01-01T00:00:00+00:00',
                ]
            ]
        ];
    }
}
