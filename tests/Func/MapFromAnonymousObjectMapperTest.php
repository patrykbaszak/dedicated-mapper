<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerCacheBundle\Contract\Replaceable\MessengerCacheManagerInterface;
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
        /** @var MessengerCacheManagerInterface $cacheManager */
        $cacheManager = self::getContainer()->get(MessengerCacheManagerInterface::class);
        $cacheManager->clear(pool: 'messenger_mapper');
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
            $nestedOriginArray = $object->someTargetedProperty instanceof SimpleDataSet ? $object->someTargetedProperty->toArray() : (array) $object->someTargetedProperty;
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
            $nestedOriginArray = $object->someTargetedProperty instanceof SimpleDataSet ? $object->someTargetedProperty->toArray() : (array) $object->someTargetedProperty;
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
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $nestedObject->targetProperty);
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
            $nestedOriginArray = $object->someTargetedProperty instanceof SimpleDataSet ? $object->someTargetedProperty->toArray() : (array) $object->someTargetedProperty;
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
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $nestedObject['targetProperty']);
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

            $nestedOriginArray = $object->someTargetedProperty instanceof SimpleDataSet ? $object->someTargetedProperty->toArray() : (array) $object->someTargetedProperty;
            self::assertEquals($nestedOriginArray['text'], $mappedObject['simpleDataSet.text']);
            self::assertEquals($nestedOriginArray['number'], $mappedObject['simpleDataSet.number']);
            self::assertEquals($nestedOriginArray['bool'], $mappedObject['simpleDataSet.bool']);
            self::assertEquals($nestedOriginArray['nullable'], $mappedObject['simpleDataSet.nullable']);
            self::assertEquals($nestedOriginArray['nullableInt'], $mappedObject['simpleDataSet.nullableInt']);
            self::assertEquals($nestedOriginArray['nullableBool'], $mappedObject['simpleDataSet.nullableBool']);
            self::assertEquals($nestedOriginArray['nullableFloat'], $mappedObject['simpleDataSet.nullableFloat']);
            self::assertEquals($nestedOriginArray['nullableArray'], $mappedObject['simpleDataSet.nullableArray']);
            self::assertEquals($nestedOriginArray['nullableObject'], $mappedObject['simpleDataSet.nullableObject']);
            self::assertEquals($nestedOriginArray['nullableDateTime'], $mappedObject['simpleDataSet.nullableDateTime']);
            self::assertEquals($nestedOriginArray['dateTime'], $mappedObject['simpleDataSet.dateTime']);
            self::assertEquals($nestedOriginArray['someTargetedProperty'] ?? $nestedOriginArray['targetProperty'], $mappedObject['simpleDataSet.targetProperty']);
        }
    }
}
