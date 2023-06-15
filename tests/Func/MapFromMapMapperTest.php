<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerCacheBundle\Contract\Replaceable\MessengerCacheManagerInterface;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Tests\Assets\SimpleDataSet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @group func */
class MapFromMapMapperTest extends KernelTestCase
{
    private Mapper $mapper;
    private array $dataSet;

    protected function setUp(): void
    {
        /** @var MessengerCacheManagerInterface $cacheManager */
        $cacheManager = self::getContainer()->get(MessengerCacheManagerInterface::class);
        $cacheManager->clear(pool: 'messenger_mapper');
        $this->mapper = self::getContainer()->get(Mapper::class);
        $this->dataSet = SimpleDataSet::getDataSet();
    }

    /** @test */
    public function shouldMapSimpleDataSetFromArrayToClassObject(): void
    {
        $class = $this->dataSet['class'];
        $maps = $this->dataSet['maps'];

        foreach ($maps as $array) {
            /** @var SimpleDataSet $object */
            $object = $this->mapper->fromArrayToClassObject($array, $class);

            self::assertInstanceOf($class, $object);
            self::assertEquals($array['text'], $object->text);
            self::assertEquals($array['number'], $object->getNumber());
            self::assertEquals($array['bool'], $object->bool);
            self::assertEquals($array['nullable'], $object->getNullable());
            self::assertEquals($array['nullableInt'], $object->nullableInt);
            self::assertEquals($array['nullableBool'], $object->getNullableBool());
            self::assertEquals($array['nullableFloat'], $object->nullableFloat);
            self::assertEquals($array['nullableArray'], $object->getNullableArray());
            self::assertEquals($array['nullableObject'], $object->nullableObject);
            self::assertEquals($array['nullableDateTime'], $object->getNullableDateTime());
            self::assertEquals($array['dateTime'], $object->dateTime);
            self::assertEquals($array['someTargetedProperty'], $object->targetProperty);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetFromArrayToAnonymousObject(): void
    {
        $class = $this->dataSet['class'];
        $maps = $this->dataSet['maps'];

        foreach ($maps as $array) {
            $object = $this->mapper->fromArrayToAnonymousObject($array, $class);

            self::assertNotInstanceOf($class, $object);
            self::assertEquals($array['text'], $object->text);
            self::assertEquals($array['number'], $object->number);
            self::assertEquals($array['bool'], $object->bool);
            self::assertEquals($array['nullable'], $object->nullable);
            self::assertEquals($array['nullableInt'], $object->nullableInt);
            self::assertEquals($array['nullableBool'], $object->nullableBool);
            self::assertEquals($array['nullableFloat'], $object->nullableFloat);
            self::assertEquals($array['nullableArray'], $object->nullableArray);
            self::assertEquals($array['nullableObject'], $object->nullableObject);
            self::assertEquals($array['nullableDateTime'], $object->nullableDateTime);
            self::assertEquals($array['dateTime'], $object->dateTime);
            self::assertEquals($array['someTargetedProperty'], $object->targetProperty);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetFromArrayToArray(): void
    {
        $class = $this->dataSet['class'];
        $maps = $this->dataSet['maps'];

        foreach ($maps as $array) {
            $object = $this->mapper->fromArrayToArray($array, $class);

            self::assertIsArray($object);
            self::assertEquals($array['text'], $object['text']);
            self::assertEquals($array['number'], $object['number']);
            self::assertEquals($array['bool'], $object['bool']);
            self::assertEquals($array['nullable'], $object['nullable']);
            self::assertEquals($array['nullableInt'], $object['nullableInt']);
            self::assertEquals($array['nullableBool'], $object['nullableBool']);
            self::assertEquals($array['nullableFloat'], $object['nullableFloat']);
            self::assertEquals($array['nullableArray'], $object['nullableArray']);
            self::assertEquals($array['nullableObject'], $object['nullableObject']);
            self::assertEquals($array['nullableDateTime'], $object['nullableDateTime']);
            self::assertEquals($array['dateTime'], $object['dateTime']);
            self::assertEquals($array['someTargetedProperty'], $object['targetProperty']);
        }
    }

    /** @test */
    public function shouldMapSimpleDataSetFromArrayToMap(): void
    {
        $class = $this->dataSet['class'];
        $maps = $this->dataSet['maps'];

        foreach ($maps as $array) {
            $object = $this->mapper->fromArrayToArray($array, $class, 'array', 'map{.}');

            self::assertIsArray($object);
            self::assertEquals($array['text'], $object['text']);
            self::assertEquals($array['number'], $object['number']);
            self::assertEquals($array['bool'], $object['bool']);
            self::assertEquals($array['nullable'], $object['nullable']);
            self::assertEquals($array['nullableInt'], $object['nullableInt']);
            self::assertEquals($array['nullableBool'], $object['nullableBool']);
            self::assertEquals($array['nullableFloat'], $object['nullableFloat']);
            self::assertEquals($array['nullableArray'], $object['nullableArray']);
            self::assertEquals($array['nullableObject'], $object['nullableObject']);
            self::assertEquals($array['nullableDateTime'], $object['nullableDateTime']);
            self::assertEquals($array['dateTime'], $object['dateTime']);
            self::assertEquals($array['someTargetedProperty'], $object['targetProperty']);
        }
    }
}
