<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Assets;

class CollectionDataSet
{
    public function __construct(
        /** @var SimpleDataSet[] $simpleDataSets */
        public readonly array $simpleDataSets,
        /** @var SimpleDataSet[] $emptySimpleDataSets */
        public readonly array $emptySimpleDataSets = [],
        /** @var SimpleDataSet[] $simpleDataSetsWithNulls */
        public readonly array $simpleDataSetsWithNulls = [],
        /** @var NestedDataSet[] $nestedDataSets */
        public readonly array $nestedDataSets = [],
    ) {
    }

    public function getDataSet(): array
    {
        return [
            'class' => __CLASS__,
            'objects' => [
                new self(
                    [
                        new SimpleDataSet(
                            'text1',
                            1,
                            true,
                            'nullable1',
                            1,
                            true,
                            1.1,
                            ['array1'],
                            new \stdClass(),
                            new \DateTime('2021-01-01'),
                            new \DateTime('2021-01-01'),
                            'target1',
                        ),
                        new SimpleDataSet(
                            'text2',
                            2,
                            false,
                            'nullable2',
                            2,
                            false,
                            2.2,
                            ['array2'],
                            new \stdClass(),
                            new \DateTime('2021-01-02'),
                            new \DateTime('2021-01-02'),
                            'target2',
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
                        )
                    ],
                    [],
                    [
                        null,
                        null,
                    ],
                    [
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
                        )
                    ]
                )
            ],
            'anonymousObjects' => [
                (object) [
                    'simpleDataSets' => [
                        (object) [
                            'text' => 'text1',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => 'nullable1',
                            'nullableInt' => 1,
                            'nullableBool' => true,
                            'nullableFloat' => 1.1,
                            'nullableArray' => ['array1'],
                            'nullableObject' => new \stdClass(),
                            'nullableDateTime' => new \DateTime('2021-01-01'),
                            'createdAt' => new \DateTime('2021-01-01'),
                            'target' => 'target1',
                        ],
                        (object) [
                            'text' => 'text2',
                            'int' => 2,
                            'bool' => false,
                            'nullableText' => 'nullable2',
                            'nullableInt' => 2,
                            'nullableBool' => false,
                            'nullableFloat' => 2.2,
                            'nullableArray' => ['array2'],
                            'nullableObject' => new \stdClass(),
                            'nullableDateTime' => new \DateTime('2021-01-02'),
                            'createdAt' => new \DateTime('2021-01-02'),
                            'target' => 'target2',
                        ],
                        (object) [
                            'text' => 'text',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => null,
                            'nullableInt' => null,
                            'nullableBool' => null,
                            'nullableFloat' => null,
                            'nullableArray' => null,
                            'nullableObject' => null,
                            'nullableDateTime' => null,
                            'createdAt' => new \DateTime(),
                            'target' => 'test2',
                        ]
                    ],
                    'emptySimpleDataSets' => [],
                    'simpleDataSetsWithNulls' => [
                        null,
                        null,
                    ],
                    'nestedDataSets' => [
                        (object) [
                            'text' => 'text',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => 'nullable',
                            'nullableInt' => 2,
                            'nullableBool' => false,
                            'nullableFloat' => 3.14,
                            'nullableArray' => ['array'],
                            'nullableObject' => new \stdClass(),
                            'nullableDateTime' => new \DateTime(),
                            'createdAt' => new \DateTime(),
                            'simpleDataSet' => (object) [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => 'nullable',
                                'nullableInt' => 2,
                                'nullableBool' => false,
                                'nullableFloat' => 3.14,
                                'nullableArray' => ['array'],
                                'nullableObject' => new \stdClass(),
                                'nullableDateTime' => new \DateTime(),
                                'createdAt' => new \DateTime(),
                                'target' => 'test',
                            ]
                        ],
                        (object) [
                            'text' => 'text',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => null,
                            'nullableInt' => null,
                            'nullableBool' => null,
                            'nullableFloat' => null,
                            'nullableArray' => null,
                            'nullableObject' => null,
                            'nullableDateTime' => null,
                            'createdAt' => new \DateTime(),
                            'simpleDataSet' => (object) [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => null,
                                'nullableInt' => null,
                                'nullableBool' => null,
                                'nullableFloat' => null,
                                'nullableArray' => null,
                                'nullableObject' => null,
                                'nullableDateTime' => null,
                                'createdAt' => new \DateTime(),
                                'target' => 'test2',
                            ]
                        ]
                    ]
                ],
            ],
            'arrays' => [
                [
                    'simpleDataSets' => [
                        [
                            'text' => 'text1',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => 'nullable1',
                            'nullableInt' => 1,
                            'nullableBool' => true,
                            'nullableFloat' => 1.1,
                            'nullableArray' => ['array1'],
                            'nullableObject' => new \stdClass(),
                            'nullableDateTime' => new \DateTime('2021-01-01'),
                            'createdAt' => new \DateTime('2021-01-01'),
                            'target' => 'target1',
                        ],
                        [
                            'text' => 'text2',
                            'int' => 2,
                            'bool' => false,
                            'nullableText' => 'nullable2',
                            'nullableInt' => 2,
                            'nullableBool' => false,
                            'nullableFloat' => 2.2,
                            'nullableArray' => ['array2'],
                            'nullableObject' => new \stdClass(),
                            'nullableDateTime' => new \DateTime('2021-01-02'),
                            'createdAt' => new \DateTime('2021-01-02'),
                            'target' => 'target2',
                        ],
                        [
                            'text' => 'text',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => null,
                            'nullableInt' => null,
                            'nullableBool' => null,
                            'nullableFloat' => null,
                            'nullableArray' => null,
                            'nullableObject' => null,
                            'nullableDateTime' => null,
                            'createdAt' => new \DateTime(),
                            'target' => 'test2',
                        ]
                    ],
                    'emptySimpleDataSets' => [],
                    'simpleDataSetsWithNulls' => [
                        null,
                        null,
                    ],
                    'nestedDataSets' => [
                        [
                            'text' => 'text',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => 'nullable',
                            'nullableInt' => 2,
                            'nullableBool' => false,
                            'nullableFloat' => 3.14,
                            'nullableArray' => ['array'],
                            'nullableObject' => new \stdClass(),
                            'nullableDateTime' => new \DateTime(),
                            'createdAt' => new \DateTime(),
                            'simpleDataSet' => [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => 'nullable',
                                'nullableInt' => 2,
                                'nullableBool' => false,
                                'nullableFloat' => 3.14,
                                'nullableArray' => ['array'],
                                'nullableObject' => new \stdClass(),
                                'nullableDateTime' => new \DateTime(),
                                'createdAt' => new \DateTime(),
                                'target' => 'test',
                            ]
                        ],
                        [
                            'text' => 'text',
                            'int' => 1,
                            'bool' => true,
                            'nullableText' => null,
                            'nullableInt' => null,
                            'nullableBool' => null,
                            'nullableFloat' => null,
                            'nullableArray' => null,
                            'nullableObject' => null,
                            'nullableDateTime' => null,
                            'createdAt' => new \DateTime(),
                            'simpleDataSet' => [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => null,
                                'nullableInt' => null,
                                'nullableBool' => null,
                                'nullableFloat' => null,
                                'nullableArray' => null,
                                'nullableObject' => null,
                                'nullableDateTime' => null,
                                'createdAt' => new \DateTime(),
                                'target' => 'test2',
                            ]
                        ]
                    ]
                ],
            ],
            'maps' => [
                'map{_}' => [
                    [
                        'simpleDataSets' => [
                            [
                                'text' => 'text1',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => 'nullable1',
                                'nullableInt' => 1,
                                'nullableBool' => true,
                                'nullableFloat' => 1.1,
                                'nullableArray' => ['array1'],
                                'nullableObject' => new \stdClass(),
                                'nullableDateTime' => new \DateTime('2021-01-01'),
                                'createdAt' => new \DateTime('2021-01-01'),
                                'target' => 'target1',
                            ],
                            [
                                'text' => 'text2',
                                'int' => 2,
                                'bool' => false,
                                'nullableText' => 'nullable2',
                                'nullableInt' => 2,
                                'nullableBool' => false,
                                'nullableFloat' => 2.2,
                                'nullableArray' => ['array2'],
                                'nullableObject' => new \stdClass(),
                                'nullableDateTime' => new \DateTime('2021-01-02'),
                                'createdAt' => new \DateTime('2021-01-02'),
                                'target' => 'target2',
                            ],
                            [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => null,
                                'nullableInt' => null,
                                'nullableBool' => null,
                                'nullableFloat' => null,
                                'nullableArray' => null,
                                'nullableObject' => null,
                                'nullableDateTime' => null,
                                'createdAt' => new \DateTime(),
                                'target' => 'test2',
                            ]
                        ],
                        'emptySimpleDataSets' => [],
                        'simpleDataSetsWithNulls' => [
                            null,
                            null,
                        ],
                        'nestedDataSets' => [
                            [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => 'nullable',
                                'nullableInt' => 2,
                                'nullableBool' => false,
                                'nullableFloat' => 3.14,
                                'nullableArray' => ['array'],
                                'nullableObject' => new \stdClass(),
                                'nullableDateTime' => new \DateTime(),
                                'createdAt' => new \DateTime(),
                                'simpleDataSet_text' => 'text',
                                'simpleDataSet_int' => 1,
                                'simpleDataSet_bool' => true,
                                'simpleDataSet_nullableText' => 'nullable',
                                'simpleDataSet_nullableInt' => 2,
                                'simpleDataSet_nullableBool' => false,
                                'simpleDataSet_nullableFloat' => 3.14,
                                'simpleDataSet_nullableArray' => ['array'],
                                'simpleDataSet_nullableObject' => new \stdClass(),
                                'simpleDataSet_nullableDateTime' => new \DateTime(),
                                'simpleDataSet_createdAt' => new \DateTime(),
                                'simpleDataSet_target' => 'test',
                            ],
                            [
                                'text' => 'text',
                                'int' => 1,
                                'bool' => true,
                                'nullableText' => null,
                                'nullableInt' => null,
                                'nullableBool' => null,
                                'nullableFloat' => null,
                                'nullableArray' => null,
                                'nullableObject' => null,
                                'nullableDateTime' => null,
                                'createdAt' => new \DateTime(),
                                'simpleDataSet_text' => 'text',
                                'simpleDataSet_int' => 1,
                                'simpleDataSet_bool' => true,
                                'simpleDataSet_nullableText' => null,
                                'simpleDataSet_nullableInt' => null,
                                'simpleDataSet_nullableBool' => null,
                                'simpleDataSet_nullableFloat' => null,
                                'simpleDataSet_nullableArray' => null,
                                'simpleDataSet_nullableObject' => null,
                                'simpleDataSet_nullableDateTime' => null,
                                'simpleDataSet_createdAt' => new \DateTime(),
                                'simpleDataSet_target' => 'test2',
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}
