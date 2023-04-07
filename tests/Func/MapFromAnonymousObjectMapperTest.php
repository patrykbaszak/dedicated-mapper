<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Tests\Assets\NestedDataSet;
use PBaszak\MessengerMapperBundle\Tests\Assets\SimpleDataSet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @group func */
class MapFromAnonymousObjectMapperTest extends KernelTestCase
{
    private Mapper $mapper;
    private array $dataSet;
    private array $nestedDataSet;

    protected function setUp(): void
    {
        $this->mapper = self::getContainer()->get(Mapper::class);
        $this->dataSet = SimpleDataSet::getDataSet();
        $this->nestedDataSet = NestedDataSet::getDataSet();
    }

    /** @test */
    public function shouldMapSimpleDataSetfromAnonymousObjectToClassObject(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['anonymousObjects'];

        foreach ($objects as $object) {
            /** @var SimpleDataSet $mappedObject */
            $mappedObject = $this->mapper->fromAnonymousObjectToClassObject($object, $class);

            self::assertInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->number, $mappedObject->getNumber());
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->nullable, $mappedObject->getNullable());
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->nullableBool, $mappedObject->getNullableBool());
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->nullableArray, $mappedObject->getNullableArray());
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->nullableDateTime, $mappedObject->getNullableDateTime());
            self::assertEquals($object->dateTime, $mappedObject->dateTime);
            self::assertEquals($object->someTargetedProperty, $mappedObject->targetProperty);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetfromAnonymousObjectToAnonymousObject(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['anonymousObjects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromAnonymousObjectToAnonymousObject($object, $class);

            self::assertNotInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->number, $mappedObject->number);
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->nullable, $mappedObject->nullable);
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->nullableBool, $mappedObject->nullableBool);
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->nullableArray, $mappedObject->nullableArray);
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->nullableDateTime, $mappedObject->nullableDateTime);
            self::assertEquals($object->dateTime, $mappedObject->dateTime);
            self::assertEquals($object->someTargetedProperty, $mappedObject->targetProperty);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetfromAnonymousObjectToArray(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['anonymousObjects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromAnonymousObjectToArray($object, $class);

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->number, $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->nullable, $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->nullableBool, $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->nullableArray, $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->nullableDateTime, $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);
            self::assertEquals($object->someTargetedProperty, $mappedObject['targetProperty']);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetfromAnonymousObjectToMap(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['anonymousObjects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromAnonymousObjectToArray($object, $class, 'object', 'map{.}');

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->number, $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->nullable, $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->nullableBool, $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->nullableArray, $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->nullableDateTime, $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);
            self::assertEquals($object->someTargetedProperty, $mappedObject['targetProperty']);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromAnonymousObjectToClassObject(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['anonymousObjects'];

        foreach ($objects as $object) {
            /** @var NestedDataSet $mappedObject */
            $mappedObject = $this->mapper->fromAnonymousObjectToClassObject($object, $class);

            self::assertInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->number, $mappedObject->getNumber());
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->nullable, $mappedObject->getNullable());
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->nullableBool, $mappedObject->isTrue());
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->nullableArray, $mappedObject->getNullableArray());
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->nullableDateTime, $mappedObject->getNullableDateTime());
            self::assertEquals($object->dateTime, $mappedObject->dateTime);

            $nestedObject = $mappedObject->simpleDataSet;
            self::assertEquals($object->someTargetedProperty->text, $nestedObject->text);
            self::assertEquals($object->someTargetedProperty->number, $nestedObject->getNumber());
            self::assertEquals($object->someTargetedProperty->bool, $nestedObject->bool);
            self::assertEquals($object->someTargetedProperty->nullable, $nestedObject->getNullable());
            self::assertEquals($object->someTargetedProperty->nullableInt, $nestedObject->nullableInt);
            self::assertEquals($object->someTargetedProperty->nullableBool, $nestedObject->getNullableBool());
            self::assertEquals($object->someTargetedProperty->nullableFloat, $nestedObject->nullableFloat);
            self::assertEquals($object->someTargetedProperty->nullableArray, $nestedObject->getNullableArray());
            self::assertEquals($object->someTargetedProperty->nullableObject, $nestedObject->nullableObject);
            self::assertEquals($object->someTargetedProperty->nullableDateTime, $nestedObject->getNullableDateTime());
            self::assertEquals($object->someTargetedProperty->dateTime, $nestedObject->dateTime);
            self::assertEquals($object->someTargetedProperty->someTargetedProperty, $nestedObject->targetProperty);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromAnonymousObjectToAnonymousObject(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['anonymousObjects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromAnonymousObjectToAnonymousObject($object, $class);

            self::assertNotInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->number, $mappedObject->number);
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->nullable, $mappedObject->nullable);
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->nullableBool, $mappedObject->nullableBool);
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->nullableArray, $mappedObject->nullableArray);
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->nullableDateTime, $mappedObject->nullableDateTime);
            self::assertEquals($object->dateTime, $mappedObject->dateTime);

            $nestedObject = $mappedObject->simpleDataSet;
            self::assertEquals($object->someTargetedProperty->text, $$nestedObject->text);
            self::assertEquals($object->someTargetedProperty->number, $$nestedObject->number);
            self::assertEquals($object->someTargetedProperty->bool, $$nestedObject->bool);
            self::assertEquals($object->someTargetedProperty->nullable, $$nestedObject->nullable);
            self::assertEquals($object->someTargetedProperty->nullableInt, $$nestedObject->nullableInt);
            self::assertEquals($object->someTargetedProperty->nullableBool, $$nestedObject->nullableBool);
            self::assertEquals($object->someTargetedProperty->nullableFloat, $$nestedObject->nullableFloat);
            self::assertEquals($object->someTargetedProperty->nullableArray, $$nestedObject->nullableArray);
            self::assertEquals($object->someTargetedProperty->nullableObject, $$nestedObject->nullableObject);
            self::assertEquals($object->someTargetedProperty->nullableDateTime, $$nestedObject->nullableDateTime);
            self::assertEquals($object->someTargetedProperty->dateTime, $$nestedObject->dateTime);
            self::assertEquals($object->someTargetedProperty->someTargetedProperty, $nestedObject->targetProperty);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromAnonymousObjectToArray(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['anonymousObjects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromAnonymousObjectToArray($object, $class);

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->number, $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->nullable, $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->nullableBool, $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->nullableArray, $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->nullableDateTime, $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);

            $nestedObject = $mappedObject['simpleDataSet'];
            self::assertEquals($object->someTargetedProperty->text, $nestedObject['text']);
            self::assertEquals($object->someTargetedProperty->number, $nestedObject['number']);
            self::assertEquals($object->someTargetedProperty->bool, $nestedObject['bool']);
            self::assertEquals($object->someTargetedProperty->nullable, $nestedObject['nullable']);
            self::assertEquals($object->someTargetedProperty->nullableInt, $nestedObject['nullableInt']);
            self::assertEquals($object->someTargetedProperty->nullableBool, $nestedObject['nullableBool']);
            self::assertEquals($object->someTargetedProperty->nullableFloat, $nestedObject['nullableFloat']);
            self::assertEquals($object->someTargetedProperty->nullableArray, $nestedObject['nullableArray']);
            self::assertEquals($object->someTargetedProperty->nullableObject, $nestedObject['nullableObject']);
            self::assertEquals($object->someTargetedProperty->nullableDateTime, $nestedObject['nullableDateTime']);
            self::assertEquals($object->someTargetedProperty->dateTime, $nestedObject['dateTime']);
            self::assertEquals($object->someTargetedProperty->someTargetedProperty, $nestedObject['targetProperty']);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromAnonymousObjectToMap(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['anonymousObjects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromAnonymousObjectToArray($object, $class, 'object', 'map{.}');

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->number, $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->nullable, $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->nullableBool, $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->nullableArray, $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->nullableDateTime, $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);

            self::assertEquals($object->someTargetedProperty->text, $mappedObject['simpleDataSet.text']);
            self::assertEquals($object->someTargetedProperty->number, $mappedObject['simpleDataSet.number']);
            self::assertEquals($object->someTargetedProperty->bool, $mappedObject['simpleDataSet.bool']);
            self::assertEquals($object->someTargetedProperty->nullable, $mappedObject['simpleDataSet.nullable']);
            self::assertEquals($object->someTargetedProperty->nullableInt, $mappedObject['simpleDataSet.nullableInt']);
            self::assertEquals($object->someTargetedProperty->nullableBool, $mappedObject['simpleDataSet.nullableBool']);
            self::assertEquals($object->someTargetedProperty->nullableFloat, $mappedObject['simpleDataSet.nullableFloat']);
            self::assertEquals($object->someTargetedProperty->nullableArray, $mappedObject['simpleDataSet.nullableArray']);
            self::assertEquals($object->someTargetedProperty->nullableObject, $mappedObject['simpleDataSet.nullableObject']);
            self::assertEquals($object->someTargetedProperty->nullableDateTime, $mappedObject['simpleDataSet.nullableDateTime']);
            self::assertEquals($object->someTargetedProperty->dateTime, $mappedObject['simpleDataSet.dateTime']);
            self::assertEquals($object->someTargetedProperty->someTargetedProperty, $mappedObject['simpleDataSet.targetProperty']);
        }
    }
}
