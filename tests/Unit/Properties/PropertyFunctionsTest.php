<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Unit\Properties;

use PBaszak\DedicatedMapperBundle\Properties\Property;
use PHPUnit\Framework\TestCase;

class PropertyFunctionsNestedTester
{
    /**
     * PROPERTY.
     */
    public ?string $test;

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
     * SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION.
     *
     * @var \ArrayObject<\DateTime>
     */
    public \ArrayObject|null $test2;

    /**
     * SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION.
     *
     * @var \DateTime[]
     */
    public \ArrayObject $test3;
}

class PropertyFunctionsTester
{
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
     * COLLECTION.
     *
     * @var PropertyFunctionsNestedTester[]
     */
    public array $test1;

    /**
     * COLLECTION.
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
}

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

        /* COLLECTION */
        $this->assertEquals(
            Property::COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test1'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test2'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECT_COLLECTION */
        $this->assertEquals(
            Property::SIMPLE_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test3'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsTester::class),
                'test4'
            )->getPropertyType()
        );

        /* SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION */
        $this->assertEquals(
            Property::SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test2'
            )->getPropertyType()
        );
        $this->assertEquals(
            Property::SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION,
            Property::create(
                new \ReflectionClass(PropertyFunctionsNestedTester::class),
                'test3'
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
    }
}
