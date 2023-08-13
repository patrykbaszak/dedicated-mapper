<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Unit\Properties;

use PBaszak\DedicatedMapper\Attribute\ApplyToCollectionItems;
use PBaszak\DedicatedMapper\Attribute\SimpleObject;
use PBaszak\DedicatedMapper\Properties\Property;
use PHPUnit\Framework\TestCase;

/** @group unit */
class PropertyFunctionsTest extends TestCase
{
    /** @test */
    public function testIsNullable(): void
    {
        $this->assertTrue(
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test'
            )->isNullable()
        );
        $this->assertTrue(
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test0'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test1'
            )->isNullable()
        );
        $this->assertTrue(
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test2'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test3'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test'
            )->isNullable()
        );
        $this->assertTrue(
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test0'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test1'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test2'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test3'
            )->isNullable()
        );
        $this->assertFalse(
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test4'
            )->isNullable()
        );
    }

    /** @test */
    public function testGetPropertyType(): void
    {
        /* PROPERTY */
        $this->assertEquals(
            Property::PROPERTY,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test'
            )->getPropertyType()
        );

        /* CLASS_OBJECT */
        $this->assertEquals(
            Property::CLASS_OBJECT,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::CLASS_OBJECT,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test0'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECT */
        $this->assertEquals(
            Property::SIMPLE_OBJECT,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test0'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'simpleObject'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'simpleObject'
            )->getPropertyType()
        );

        /* PROPERTIES_COLLECTION */
        $this->assertEquals(
            Property::PROPERTIES_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'testStrings'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::PROPERTIES_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'testStrings2'
            )->getPropertyType()
        );

        /* CLASS_OBJECTS_COLLECTION */
        $this->assertEquals(
            Property::CLASS_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test1'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::CLASS_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test2'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECTS_COLLECTION */
        $this->assertEquals(
            Property::SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test1'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'simpleObjects'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'simpleObjectsCollection'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECT_COLLECTION */
        $this->assertEquals(
            Property::SIMPLE_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'testInts'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'testInts2'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECT_CLASS_OBJECT_COLLECTION */
        $this->assertEquals(
            Property::SIMPLE_OBJECT_CLASS_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test3'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_CLASS_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test4'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION */
        $this->assertEquals(
            Property::SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test2'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test3'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'simpleObjects'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'simpleObjectsNo1'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'simpleObjectsNo2'
            )->getPropertyType()
        );
    }
}

#[SimpleObject()]
class PropertyFunctionsSimpleObject
{
    public function __construct(
        public string $test,
    ) {
    }
}

class PropertyFunctionsNestedTester
{
    /**
     * PROPERTY.
     */
    public ?string $test;

    /**
     * PROPERTIES_COLLECTION.
     *
     * @var string[]
     */
    public array $testStrings;

    /**
     * PROPERTIES_COLLECTION.
     *
     * @var array<string>
     */
    public array $testStrings2;

    /**
     * SIMPLE_OBJECT_COLLECTION.
     *
     * @var int[]
     */
    public \ArrayObject $testInts;

    /**
     * SIMPLE_OBJECT_COLLECTION.
     *
     * @var array<int>
     */
    public \ArrayObject $testInts2;

    /**
     * SIMPLE_OBJECT.
     */
    public PropertyFunctionsSimpleObject $simpleObject;

    /**
     * SIMPLE_OBJECTS_COLLECTION.
     *
     * @var PropertyFunctionsSimpleObject[]
     */
    public array $simpleObjects;

    /**
     * SIMPLE_OBJECT.
     */
    public null|\DateTime $test0;

    /**
     * SIMPLE_OBJECTS_COLLECTION.
     *
     * @var \DateTime[]
     */
    public array $test1;

    /**
     * SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION.
     *
     * @var \ArrayObject<\DateTime>
     */
    public \ArrayObject|null $test2;

    /**
     * SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION.
     *
     * @var \DateTime[]
     */
    public \ArrayObject $test3;
}

class PropertyFunctionsTester
{
    /**
     * SIMPLE_OBJECT.
     */
    #[SimpleObject()]
    public PropertyFunctionsNestedTester $simpleObject;

    /**
     * CLASS_OBJECT.
     */
    public PropertyFunctionsNestedTester $test;

    /**
     * CLASS_OBJECT.
     *
     * @var PropertyFunctionsNestedTester|null
     */
    public $test0;

    /**
     * CLASS_OBJECTS_COLLECTION.
     *
     * @var PropertyFunctionsNestedTester[]
     */
    public array $test1;

    /**
     * CLASS_OBJECTS_COLLECTION.
     *
     * @var array<PropertyFunctionsNestedTester>
     */
    public array $test2;

    /**
     * SIMPLE_OBJECT_COLLECTION.
     *
     * @var \ArrayObject<PropertyFunctionsNestedTester>
     */
    public \ArrayObject $test3;

    /**
     * SIMPLE_OBJECT_COLLECTION.
     *
     * @var PropertyFunctionsNestedTester[]
     */
    public \ArrayObject $test4;

    /**
     * SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION.
     *
     * @var PropertyFunctionsNestedTester[]
     */
    #[ApplyToCollectionItems(
        [
            new SimpleObject(),
        ]
    )]
    public \ArrayObject $simpleObjects;

    /**
     * SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION.
     *
     * @var \DateTime[]
     */
    public PropertyFunctionsSimpleObject $simpleObjectsNo1;

    /**
     * SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION.
     *
     * @var PropertyFunctionsNestedTester[]
     */
    #[SimpleObject()]
    #[ApplyToCollectionItems(
        [
            new SimpleObject(),
        ]
    )]
    public PropertyFunctionsNestedTester $simpleObjectsNo2;

    /**
     * SIMPLE_OBJECTS_COLLECTION.
     *
     * @var PropertyFunctionsNestedTester[]
     */
    #[ApplyToCollectionItems(
        [
            new SimpleObject(),
        ]
    )]
    public array $simpleObjectsCollection;
}
