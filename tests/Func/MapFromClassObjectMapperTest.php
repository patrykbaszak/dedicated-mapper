<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Tests\Assets\CollectionDataSet;
use PBaszak\MessengerMapperBundle\Tests\Assets\NestedDataSet;
use PBaszak\MessengerMapperBundle\Tests\Assets\SimpleDataSet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @group func */
class MapfromClassObjectMapperTest extends KernelTestCase
{
    private Mapper $mapper;
    private array $dataSet;
    private array $nestedDataSet;
    private array $collectionDataSet;

    protected function setUp(): void
    {
        $this->mapper = self::getContainer()->get(Mapper::class);
        $this->dataSet = SimpleDataSet::getDataSet();
        $this->nestedDataSet = NestedDataSet::getDataSet();
        $this->collectionDataSet = CollectionDataSet::getDataSet();
    }

    private function assertEqualsSimpleDataSetClassObjectToClassObject(SimpleDataSet $expected, SimpleDataSet $actual): void
    {
        self::assertEquals($expected->text, $actual->text);
        self::assertEquals($expected->getNumber(), $actual->getNumber());
        self::assertEquals($expected->bool, $actual->bool);
        self::assertEquals($expected->getNullable(), $actual->getNullable());
        self::assertEquals($expected->nullableInt, $actual->nullableInt);
        self::assertEquals($expected->getNullableBool(), $actual->getNullableBool());
        self::assertEquals($expected->nullableFloat, $actual->nullableFloat);
        self::assertEquals($expected->getNullableArray(), $actual->getNullableArray());
        self::assertEquals($expected->nullableObject, $actual->nullableObject);
        self::assertEquals($expected->getNullableDateTime(), $actual->getNullableDateTime());
        self::assertEquals($expected->dateTime, $actual->dateTime);
        self::assertEquals($expected->targetProperty, $actual->targetProperty);
    }

    private function assertEqualsSimpleDataSetClassObjectToAnonymousObject(SimpleDataSet $expected, object $actual): void
    {
        self::assertEquals($expected->text, $actual->text);
        self::assertEquals($expected->getNumber(), $actual->number);
        self::assertEquals($expected->bool, $actual->bool);
        self::assertEquals($expected->getNullable(), $actual->nullable);
        self::assertEquals($expected->nullableInt, $actual->nullableInt);
        self::assertEquals($expected->getNullableBool(), $actual->nullableBool);
        self::assertEquals($expected->nullableFloat, $actual->nullableFloat);
        self::assertEquals($expected->getNullableArray(), $actual->nullableArray);
        self::assertEquals($expected->nullableObject, $actual->nullableObject);
        self::assertEquals($expected->getNullableDateTime(), $actual->nullableDateTime);
        self::assertEquals($expected->dateTime, $actual->dateTime);
        self::assertEquals($expected->targetProperty, $actual->someTargetedProperty);
    }

    private function assertEqualsSimpleDataSetClassObjectToArray(SimpleDataSet $expected, array $actual): void
    {
        self::assertEquals($expected->text, $actual['text']);
        self::assertEquals($expected->getNumber(), $actual['number']);
        self::assertEquals($expected->bool, $actual['bool']);
        self::assertEquals($expected->getNullable(), $actual['nullable']);
        self::assertEquals($expected->nullableInt, $actual['nullableInt']);
        self::assertEquals($expected->getNullableBool(), $actual['nullableBool']);
        self::assertEquals($expected->nullableFloat, $actual['nullableFloat']);
        self::assertEquals($expected->getNullableArray(), $actual['nullableArray']);
        self::assertEquals($expected->nullableObject, $actual['nullableObject']);
        self::assertEquals($expected->getNullableDateTime(), $actual['nullableDateTime']);
        self::assertEquals($expected->dateTime, $actual['dateTime']);
        self::assertEquals($expected->targetProperty, $actual['someTargetedProperty']);
    }

    private function assertEqualsSimpleDataSetClassObjectToMap(SimpleDataSet $expected, array $actual, string $prefix = ''): void
    {
        self::assertEquals($expected->text, $actual[$prefix . 'text']);
        self::assertEquals($expected->getNumber(), $actual[$prefix . 'number']);
        self::assertEquals($expected->bool, $actual[$prefix . 'bool']);
        self::assertEquals($expected->getNullable(), $actual[$prefix . 'nullable']);
        self::assertEquals($expected->nullableInt, $actual[$prefix . 'nullableInt']);
        self::assertEquals($expected->getNullableBool(), $actual[$prefix . 'nullableBool']);
        self::assertEquals($expected->nullableFloat, $actual[$prefix . 'nullableFloat']);
        self::assertEquals($expected->getNullableArray(), $actual[$prefix . 'nullableArray']);
        self::assertEquals($expected->nullableObject, $actual[$prefix . 'nullableObject']);
        self::assertEquals($expected->getNullableDateTime(), $actual[$prefix . 'nullableDateTime']);
        self::assertEquals($expected->dateTime, $actual[$prefix . 'dateTime']);
        self::assertEquals($expected->targetProperty, $actual[$prefix . 'someTargetedProperty']);
    }

    private function assertEqualsNestedDataSetClassObjectToClassObject(NestedDataSet $expected, NestedDataSet $actual): void
    {
        self::assertEquals($expected->text, $actual->text);
        self::assertEquals($expected->getNumber(), $actual->getNumber());
        self::assertEquals($expected->bool, $actual->bool);
        self::assertEquals($expected->getNullable(), $actual->getNullable());
        self::assertEquals($expected->nullableInt, $actual->nullableInt);
        self::assertEquals($expected->isTrue(), $actual->isTrue());
        self::assertEquals($expected->nullableFloat, $actual->nullableFloat);
        self::assertEquals($expected->getNullableArray(), $actual->getNullableArray());
        self::assertEquals($expected->nullableObject, $actual->nullableObject);
        self::assertEquals($expected->getNullableDateTime(), $actual->getNullableDateTime());
        self::assertEquals($expected->dateTime, $actual->dateTime);
        $nestedObject = $actual->simpleDataSet;
        if ($expected->simpleDataSet instanceof SimpleDataSet) {
            $originNestedObject = $expected->simpleDataSet;
        } else {
            $originNestedArray = (array) $expected->simpleDataSet;
            $originNestedArray['targetProperty'] = $originNestedArray['someTargetedProperty'] ?? $originNestedArray['targetProperty'];
            unset($originNestedArray['someTargetedProperty']);
            $originNestedObject = new SimpleDataSet(...$originNestedArray);
        }
        $this->assertEqualsSimpleDataSetClassObjectToClassObject($originNestedObject, $nestedObject);
    }

    private function assertEqualsNestedDataSetClassObjectToAnonymousObject(NestedDataSet $expected, object $actual): void
    {
        self::assertEquals($expected->text, $actual->text);
        self::assertEquals($expected->getNumber(), $actual->number);
        self::assertEquals($expected->bool, $actual->bool);
        self::assertEquals($expected->getNullable(), $actual->nullable);
        self::assertEquals($expected->nullableInt, $actual->nullableInt);
        self::assertEquals($expected->isTrue(), $actual->nullableBool);
        self::assertEquals($expected->nullableFloat, $actual->nullableFloat);
        self::assertEquals($expected->getNullableArray(), $actual->nullableArray);
        self::assertEquals($expected->nullableObject, $actual->nullableObject);
        self::assertEquals($expected->getNullableDateTime(), $actual->nullableDateTime);
        self::assertEquals($expected->dateTime, $actual->dateTime);
        $nestedObject = $actual->someTargetedProperty;
        self::assertNotInstanceOf(SimpleDataSet::class, $nestedObject);
        if ($expected->simpleDataSet instanceof SimpleDataSet) {
            $originNestedObject = $expected->simpleDataSet;
        } else {
            $originNestedArray = (array) $expected->simpleDataSet;
            $originNestedArray['targetProperty'] = $originNestedArray['someTargetedProperty'] ?? $originNestedArray['targetProperty'];
            unset($originNestedArray['someTargetedProperty']);
            $originNestedObject = new SimpleDataSet(...$originNestedArray);
        }
        $this->assertEqualsSimpleDataSetClassObjectToAnonymousObject($originNestedObject, $nestedObject);
    }

    private function assertEqualsNestedDataSetClassObjectToArray(NestedDataSet $expected, array $actual): void
    {
        self::assertEquals($expected->text, $actual['text']);
        self::assertEquals($expected->getNumber(), $actual['number']);
        self::assertEquals($expected->bool, $actual['bool']);
        self::assertEquals($expected->getNullable(), $actual['nullable']);
        self::assertEquals($expected->nullableInt, $actual['nullableInt']);
        self::assertEquals($expected->isTrue(), $actual['nullableBool']);
        self::assertEquals($expected->nullableFloat, $actual['nullableFloat']);
        self::assertEquals($expected->getNullableArray(), $actual['nullableArray']);
        self::assertEquals($expected->nullableObject, $actual['nullableObject']);
        self::assertEquals($expected->getNullableDateTime(), $actual['nullableDateTime']);
        self::assertEquals($expected->dateTime, $actual['dateTime']);
        $nestedObject = $actual['someTargetedProperty'];
        self::assertIsArray($nestedObject);
        if ($expected->simpleDataSet instanceof SimpleDataSet) {
            $originNestedObject = $expected->simpleDataSet;
        } else {
            $originNestedArray = (array) $expected->simpleDataSet;
            $originNestedArray['targetProperty'] = $originNestedArray['someTargetedProperty'] ?? $originNestedArray['targetProperty'];
            unset($originNestedArray['someTargetedProperty']);
            $originNestedObject = new SimpleDataSet(...$originNestedArray);
        }
        $this->assertEqualsSimpleDataSetClassObjectToArray($originNestedObject, $nestedObject);
    }

    private function assertEqualsNestedDataSetClassObjectToMap(NestedDataSet $expected, array $actual, string $separator = '.'): void
    {
        self::assertEquals($expected->text, $actual['text']);
        self::assertEquals($expected->getNumber(), $actual['number']);
        self::assertEquals($expected->bool, $actual['bool']);
        self::assertEquals($expected->getNullable(), $actual['nullable']);
        self::assertEquals($expected->nullableInt, $actual['nullableInt']);
        self::assertEquals($expected->isTrue(), $actual['nullableBool']);
        self::assertEquals($expected->nullableFloat, $actual['nullableFloat']);
        self::assertEquals($expected->getNullableArray(), $actual['nullableArray']);
        self::assertEquals($expected->nullableObject, $actual['nullableObject']);
        self::assertEquals($expected->getNullableDateTime(), $actual['nullableDateTime']);
        self::assertEquals($expected->dateTime, $actual['dateTime']);

        if ($expected->simpleDataSet instanceof SimpleDataSet) {
            $originNestedObject = $expected->simpleDataSet;
        } else {
            $originNestedArray = (array) $expected->simpleDataSet;
            $originNestedArray['targetProperty'] = $originNestedArray['someTargetedProperty'] ?? $originNestedArray['targetProperty'];
            unset($originNestedArray['someTargetedProperty']);
            $originNestedObject = new SimpleDataSet(...$originNestedArray);
        }

        $prefix = 'someTargetedProperty' . $separator;
        $this->assertEqualsSimpleDataSetClassObjectToMap(
            $originNestedObject,
            array_filter($actual, fn (string $k) => str_starts_with($k, $prefix), ARRAY_FILTER_USE_KEY),
            $prefix
        );
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
            $this->assertEqualsSimpleDataSetClassObjectToClassObject($object, $mappedObject);
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
            $this->assertEqualsSimpleDataSetClassObjectToAnonymousObject($object, $mappedObject);
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
            $this->assertEqualsSimpleDataSetClassObjectToArray($object, $mappedObject);
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
            $this->assertEqualsSimpleDataSetClassObjectToMap($object, $mappedObject);
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
            $this->assertEqualsNestedDataSetClassObjectToClassObject($object, $mappedObject);
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
            $this->assertEqualsNestedDataSetClassObjectToAnonymousObject($object, $mappedObject);
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
            $this->assertEqualsNestedDataSetClassObjectToArray($object, $mappedObject);
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
            $this->assertEqualsNestedDataSetClassObjectToMap($object, $mappedObject, '.');
        }
    }

    /** @test */
    public function shouldMapCollectionDataSetfromClassObjectToClassObject(): void
    {
        $class = $this->collectionDataSet['class'];
        $objects = $this->collectionDataSet['objects'];

        foreach ($objects as $object) {
            /** @var CollectionDataSet $mappedObject */
            $mappedObject = $this->mapper->fromClassObjectToClassObject($object, $class);

            self::assertInstanceOf($class, $mappedObject);
            self::assertNotEmpty($mappedObject->simpleDataSets);
            foreach ($mappedObject->simpleDataSets as $index => $simpleDataSet) {
                $origin = $object->simpleDataSets[$index];
                self::assertInstanceOf(SimpleDataSet::class, $simpleDataSet);
                $this->assertEqualsSimpleDataSetClassObjectToClassObject($origin, $simpleDataSet);
            }

            self::assertEmpty($mappedObject->emptySimpleDataSets);

            self::assertNotEmpty($mappedObject->simpleDataSetsWithNulls);
            foreach ($mappedObject->simpleDataSetsWithNulls as $index => $null) {
                self::assertNull($null);
            }

            self::assertNotEmpty($mappedObject->nestedDataSets);
            foreach ($mappedObject->nestedDataSets as $index => $nestedDataSet) {
                $origin = $object->nestedDataSets[$index];
                self::assertInstanceOf(NestedDataSet::class, $nestedDataSet);
                $this->assertEqualsNestedDataSetClassObjectToClassObject($origin, $nestedDataSet);
            }
        }
    }

    /** @test */
    public function shouldMapCollectionDataSetfromClassObjectToAnonymousObject(): void
    {
        $class = $this->collectionDataSet['class'];
        $objects = $this->collectionDataSet['objects'];

        foreach ($objects as $object) {
            $mappedObject = $this->mapper->fromClassObjectToAnonymousObject($object, $class);

            self::assertNotInstanceOf($class, $mappedObject);

            self::assertNotEmpty($mappedObject->simpleDataSets);
            foreach ($mappedObject->simpleDataSets as $index => $simpleDataSet) {
                $origin = $object->simpleDataSets[$index];
                self::assertNotInstanceOf(SimpleDataSet::class, $simpleDataSet);
                $this->assertEqualsSimpleDataSetClassObjectToAnonymousObject($origin, $simpleDataSet);
            }

            self::assertEmpty($mappedObject->emptySimpleDataSets);

            self::assertNotEmpty($mappedObject->simpleDataSetsWithNulls);
            foreach ($mappedObject->simpleDataSetsWithNulls as $index => $null) {
                self::assertNull($null);
            }

            self::assertNotEmpty($mappedObject->nestedDataSets);
            foreach ($mappedObject->nestedDataSets as $index => $nestedDataSet) {
                $origin = $object->nestedDataSets[$index];
                self::assertNotInstanceOf(NestedDataSet::class, $nestedDataSet);
                $this->assertEqualsNestedDataSetClassObjectToAnonymousObject($origin, $nestedDataSet);
            }
        }
    }
}
