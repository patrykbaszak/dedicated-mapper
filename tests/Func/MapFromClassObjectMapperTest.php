<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Tests\Assets\NestedDataSet;
use PBaszak\MessengerMapperBundle\Tests\Assets\SimpleDataSet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @group func */
class MapfromClassObjectMapperTest extends KernelTestCase
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
    public function shouldMapSimpleDataSetfromClassObjectToClassObject(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['objects'];

        foreach ($objects as $object) {
            /** @var SimpleDataSet $mappedObject */
            $mappedObject = $this->mapper->fromClassObjectToClassObject($object, $class);

            self::assertInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->getNumber(), $mappedObject->getNumber());
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->getNullable(), $mappedObject->getNullable());
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->getNullableBool(), $mappedObject->getNullableBool());
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->getNullableArray(), $mappedObject->getNullableArray());
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->getNullableDateTime(), $mappedObject->getNullableDateTime());
            self::assertEquals($object->dateTime, $mappedObject->dateTime);
            self::assertEquals($object->targetProperty, $mappedObject->targetProperty);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetfromClassObjectToAnonymousObject(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToAnonymousObject($object, $class);

            self::assertNotInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->getNumber(), $mappedObject->number);
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->getNullable(), $mappedObject->nullable);
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->getNullableBool(), $mappedObject->nullableBool);
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->getNullableArray(), $mappedObject->nullableArray);
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->getNullableDateTime(), $mappedObject->nullableDateTime);
            self::assertEquals($object->dateTime, $mappedObject->dateTime);
            self::assertEquals($object->targetProperty, $mappedObject->someTargetedProperty);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetfromClassObjectToArray(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToArray($object, $class);

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->getNumber(), $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->getNullable(), $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->getNullableBool(), $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->getNullableArray(), $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->getNullableDateTime(), $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);
            self::assertEquals($object->targetProperty, $mappedObject['someTargetedProperty']);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetfromClassObjectToMap(): void
    {
        $class = $this->dataSet['class'];
        $objects = $this->dataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToArray($object, $class, 'map{.}');

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->getNumber(), $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->getNullable(), $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->getNullableBool(), $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->getNullableArray(), $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->getNullableDateTime(), $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);
            self::assertEquals($object->targetProperty, $mappedObject['someTargetedProperty']);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromClassObjectToClassObject(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['objects'];

        foreach ($objects as $object) {
            /** @var NestedDataSet $mappedObject */
            $mappedObject = $this->mapper->fromClassObjectToClassObject($object, $class);

            self::assertInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->getNumber(), $mappedObject->getNumber());
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->getNullable(), $mappedObject->getNullable());
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->isTrue(), $mappedObject->isTrue());
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->getNullableArray(), $mappedObject->getNullableArray());
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->getNullableDateTime(), $mappedObject->getNullableDateTime());
            self::assertEquals($object->dateTime, $mappedObject->dateTime);

            $nestedObject = $mappedObject->simpleDataSet;
            $nestedOriginArray = $object->simpleDataSet instanceof SimpleDataSet ? $object->simpleDataSet->toArray() : (array) $object->simpleDataSet;
            self::assertEquals($nestedOriginArray['text'], $nestedObject->text);
            self::assertEquals($nestedOriginArray['number'], $nestedObject->getNumber());
            self::assertEquals($nestedOriginArray['bool'], $nestedObject->bool);
            self::assertEquals($nestedOriginArray['nullable'], $nestedObject->getNullable());
            self::assertEquals($nestedOriginArray['nullableInt'], $nestedObject->nullableInt);
            self::assertEquals($nestedOriginArray['nullableBool'], $nestedObject->getNullableBool());
            self::assertEquals($nestedOriginArray['nullableFloat'], $nestedObject->nullableFloat);
            self::assertEquals($nestedOriginArray['nullableArray'], $nestedObject->getNullableArray());
            self::assertEquals($nestedOriginArray['nullableObject'], $nestedObject->nullableObject);
            self::assertEquals($nestedOriginArray['nullableDateTime'], $nestedObject->getNullableDateTime());
            self::assertEquals($nestedOriginArray['dateTime'], $nestedObject->dateTime);
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $nestedObject->targetProperty);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromClassObjectToAnonymousObject(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToAnonymousObject($object, $class);

            self::assertNotInstanceOf($class, $mappedObject);
            self::assertEquals($object->text, $mappedObject->text);
            self::assertEquals($object->getNumber(), $mappedObject->number);
            self::assertEquals($object->bool, $mappedObject->bool);
            self::assertEquals($object->getNullable(), $mappedObject->nullable);
            self::assertEquals($object->nullableInt, $mappedObject->nullableInt);
            self::assertEquals($object->isTrue(), $mappedObject->nullableBool);
            self::assertEquals($object->nullableFloat, $mappedObject->nullableFloat);
            self::assertEquals($object->getNullableArray(), $mappedObject->nullableArray);
            self::assertEquals($object->nullableObject, $mappedObject->nullableObject);
            self::assertEquals($object->getNullableDateTime(), $mappedObject->nullableDateTime);
            self::assertEquals($object->dateTime, $mappedObject->dateTime);

            $nestedObject = $mappedObject->someTargetedProperty;
            $nestedOriginArray = $object->simpleDataSet instanceof SimpleDataSet ? $object->simpleDataSet->toArray() : (array) $object->simpleDataSet;
            self::assertEquals($nestedOriginArray['text'], $nestedObject->text);
            self::assertEquals($nestedOriginArray['number'], $nestedObject->number);
            self::assertEquals($nestedOriginArray['bool'], $nestedObject->bool);
            self::assertEquals($nestedOriginArray['nullable'], $nestedObject->nullable);
            self::assertEquals($nestedOriginArray['nullableInt'], $nestedObject->nullableInt);
            self::assertEquals($nestedOriginArray['nullableBool'], $nestedObject->nullableBool);
            self::assertEquals($nestedOriginArray['nullableFloat'], $nestedObject->nullableFloat);
            self::assertEquals($nestedOriginArray['nullableArray'], $nestedObject->nullableArray);
            self::assertEquals($nestedOriginArray['nullableObject'], $nestedObject->nullableObject);
            self::assertEquals($nestedOriginArray['nullableDateTime'], $nestedObject->nullableDateTime);
            self::assertEquals($nestedOriginArray['dateTime'], $nestedObject->dateTime);
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $nestedObject->someTargetedProperty);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromClassObjectToArray(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToArray($object, $class);

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->getNumber(), $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->getNullable(), $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->isTrue(), $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->getNullableArray(), $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->getNullableDateTime(), $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);

            $nestedObject = $mappedObject['someTargetedProperty'];
            $nestedOriginArray = $object->simpleDataSet instanceof SimpleDataSet ? $object->simpleDataSet->toArray() : (array) $object->simpleDataSet;
            self::assertEquals($nestedOriginArray['text'], $nestedObject['text']);
            self::assertEquals($nestedOriginArray['number'], $nestedObject['number']);
            self::assertEquals($nestedOriginArray['bool'], $nestedObject['bool']);
            self::assertEquals($nestedOriginArray['nullable'], $nestedObject['nullable']);
            self::assertEquals($nestedOriginArray['nullableInt'], $nestedObject['nullableInt']);
            self::assertEquals($nestedOriginArray['nullableBool'], $nestedObject['nullableBool']);
            self::assertEquals($nestedOriginArray['nullableFloat'], $nestedObject['nullableFloat']);
            self::assertEquals($nestedOriginArray['nullableArray'], $nestedObject['nullableArray']);
            self::assertEquals($nestedOriginArray['nullableObject'], $nestedObject['nullableObject']);
            self::assertEquals($nestedOriginArray['nullableDateTime'], $nestedObject['nullableDateTime']);
            self::assertEquals($nestedOriginArray['dateTime'], $nestedObject['dateTime']);
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $nestedObject['someTargetedProperty']);
        }
    }

    /** @test */
    public function shouldMapNestedDataSetfromClassObjectToMap(): void
    {
        $class = $this->nestedDataSet['class'];
        $objects = $this->nestedDataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToArray($object, $class, 'map{.}');

            self::assertIsArray($mappedObject);
            self::assertEquals($object->text, $mappedObject['text']);
            self::assertEquals($object->getNumber(), $mappedObject['number']);
            self::assertEquals($object->bool, $mappedObject['bool']);
            self::assertEquals($object->getNullable(), $mappedObject['nullable']);
            self::assertEquals($object->nullableInt, $mappedObject['nullableInt']);
            self::assertEquals($object->isTrue(), $mappedObject['nullableBool']);
            self::assertEquals($object->nullableFloat, $mappedObject['nullableFloat']);
            self::assertEquals($object->getNullableArray(), $mappedObject['nullableArray']);
            self::assertEquals($object->nullableObject, $mappedObject['nullableObject']);
            self::assertEquals($object->getNullableDateTime(), $mappedObject['nullableDateTime']);
            self::assertEquals($object->dateTime, $mappedObject['dateTime']);

            $nestedOriginArray = $object->simpleDataSet instanceof SimpleDataSet ? $object->simpleDataSet->toArray() : (array) $object->simpleDataSet;
            self::assertEquals($nestedOriginArray['text'], $mappedObject['someTargetedProperty.text']);
            self::assertEquals($nestedOriginArray['number'], $mappedObject['someTargetedProperty.number']);
            self::assertEquals($nestedOriginArray['bool'], $mappedObject['someTargetedProperty.bool']);
            self::assertEquals($nestedOriginArray['nullable'], $mappedObject['someTargetedProperty.nullable']);
            self::assertEquals($nestedOriginArray['nullableInt'], $mappedObject['someTargetedProperty.nullableInt']);
            self::assertEquals($nestedOriginArray['nullableBool'], $mappedObject['someTargetedProperty.nullableBool']);
            self::assertEquals($nestedOriginArray['nullableFloat'], $mappedObject['someTargetedProperty.nullableFloat']);
            self::assertEquals($nestedOriginArray['nullableArray'], $mappedObject['someTargetedProperty.nullableArray']);
            self::assertEquals($nestedOriginArray['nullableObject'], $mappedObject['someTargetedProperty.nullableObject']);
            self::assertEquals($nestedOriginArray['nullableDateTime'], $mappedObject['someTargetedProperty.nullableDateTime']);
            self::assertEquals($nestedOriginArray['dateTime'], $mappedObject['someTargetedProperty.dateTime']);
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $mappedObject['someTargetedProperty.someTargetedProperty']);
        }
    }
}
