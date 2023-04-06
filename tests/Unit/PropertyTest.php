<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Unit;

use PBaszak\MessengerMapperBundle\Attribute\Accessor;
use PBaszak\MessengerMapperBundle\DTO\Property;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class TestClass
{
    public string $property1;
    #[Accessor(getter: 'getTest')]
    public string $property2;
    private string $test;

    public function getTest(): string
    {
        return $this->property2;
    }

    public function setTest(string $test): void
    {
        $this->test = $test;
    }
}

/** @group unit */
class PropertyTest extends TestCase
{
    public function testConstructor(): void
    {
        $property = new Property(
            name: 'test',
            type: 'string',
            originClass: 'TestClass',
            isCollection: false,
            origin: Property::ORIGIN_ARRAY
        );

        $this->assertInstanceOf(Property::class, $property);
    }

    public function testInvalidOrigin(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Property(
            name: 'test',
            type: 'string',
            originClass: 'TestClass',
            isCollection: false,
            origin: 'invalid_origin'
        );
    }

    public function testGetMirrorProperty(): void
    {
        $property1 = new Property(
            name: 'property1',
            type: 'string',
            origin: Property::ORIGIN_OBJECT
        );

        $property2 = new Property(
            name: 'property2',
            type: 'string',
            origin: Property::ORIGIN_OBJECT
        );

        $property1->setMirrorProperty($property2);

        $this->assertSame($property2, $property1->getMirrorProperty());
        $this->assertSame($property1, $property2->getMirrorProperty());
    }

    public function testGetName(): void
    {
        $property = new Property(
            name: 'test',
            type: 'string',
            originClass: 'TestClass',
            isCollection: false,
            origin: Property::ORIGIN_ARRAY
        );

        $this->assertEquals('test', $property->getName());
    }

    public function testGetPath(): void
    {
        $parentProperty = new Property(
            name: 'parent',
            type: 'array',
            origin: Property::ORIGIN_ARRAY
        );

        $childProperty = new Property(
            name: 'child',
            type: 'string',
            parent: $parentProperty,
            origin: Property::ORIGIN_ARRAY
        );

        $this->assertEquals('parent.child', $childProperty->getPath());
    }

    public function testIsPublic(): void
    {
        $reflectionProperty = new ReflectionProperty(Property::class, 'name');
        $property = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_CLASS_OBJECT,
            reflection: $reflectionProperty
        );

        $this->assertTrue($property->isPublic());
    }

    public function testIsIgnored(): void
    {
        $property1 = new Property(name: 'property1', origin: Property::ORIGIN_ARRAY);
        $property2 = new Property(name: 'property2', origin: Property::ORIGIN_ARRAY);

        $property1->setMirrorProperty($property2);

        $this->assertFalse($property1->isIgnored());
    }

    public function testIsNullable(): void
    {
        $reflectionProperty = new ReflectionProperty(Property::class, 'name');
        $property = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_CLASS_OBJECT,
            reflection: $reflectionProperty
        );

        $this->assertFalse($property->isNullable());
    }

    public function testIsCollection(): void
    {
        $property = new Property(
            name: 'test',
            type: 'array',
            origin: Property::ORIGIN_ARRAY,
            isCollection: true
        );

        $this->assertTrue($property->isCollection());
    }

    public function testIsInGroup(): void
    {
        $property1 = new Property(name: 'property1', origin: Property::ORIGIN_ARRAY);
        $property2 = new Property(name: 'property2', origin: Property::ORIGIN_ARRAY);

        $property1->setMirrorProperty($property2);

        $this->assertFalse($property1->isInGroup('test_group'));
    }

    public function testGetGetterExpression(): void
    {
        // Test for ORIGIN_ARRAY
        $propertyArray = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_ARRAY
        );

        $this->assertEquals('$variableName[\'test\']', $propertyArray->getGetterExpression('variableName'));

        // Test for ORIGIN_OBJECT
        $propertyObject = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_OBJECT
        );

        $this->assertEquals('$variableName->test', $propertyObject->getGetterExpression('variableName'));

        // Test for ORIGIN_MAP
        $propertyMap = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_MAP
        );

        $this->assertEquals('$variableName[\'test\']', $propertyMap->getGetterExpression('variableName', '.'));

        // Test for ORIGIN_MAP_OBJECT
        $propertyMapObject = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_MAP_OBJECT
        );

        $this->assertEquals('$variableName->test', $propertyMapObject->getGetterExpression('variableName', '.'));

        // Test for ORIGIN_CLASS_OBJECT
        $propertyClassObject = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_CLASS_OBJECT,
            originClass: TestClass::class
        );

        // Assuming there's a getter method in TestClass for 'test' property.
        $this->assertEquals('$variableName->getTest()', $propertyClassObject->getGetterExpression('variableName'));
    }

    public function testGetSetterExpression(): void
    {
        // Test for ORIGIN_ARRAY
        $propertyArray = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_ARRAY
        );

        $this->assertEquals('$variableName[\'test\'] = $getterExpression;', $propertyArray->getSetterExpression('$getterExpression', 'variableName'));

        // Test for ORIGIN_OBJECT
        $propertyObject = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_OBJECT
        );

        $this->assertEquals('$variableName->test = $getterExpression;', $propertyObject->getSetterExpression('$getterExpression', 'variableName'));

        // Test for ORIGIN_MAP
        $propertyMap = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_MAP
        );

        $this->assertEquals('$variableName[\'test\'] = $getterExpression;', $propertyMap->getSetterExpression('$getterExpression', 'variableName', '.'));

        // Test for ORIGIN_MAP_OBJECT
        $propertyMapObject = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_MAP_OBJECT
        );

        $this->assertEquals('$variableName->test = $getterExpression;', $propertyMapObject->getSetterExpression('$getterExpression', 'variableName', '.'));

        // Test for ORIGIN_CLASS_OBJECT
        $propertyClassObject = new Property(
            name: 'test',
            type: 'string',
            origin: Property::ORIGIN_CLASS_OBJECT,
            originClass: TestClass::class
        );

        // Assuming there's a setter method in TestClass for 'test' property.
        $this->assertEquals('$variableName->setTest($getterExpression);', $propertyClassObject->getSetterExpression('$getterExpression', 'variableName'));
    }
}
