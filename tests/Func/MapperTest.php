<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerMapperBundle\Attribute as Mapper;
use PBaszak\MessengerMapperBundle\Contract\GetMapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\HandleTrait;

/** @group func */
class MapperTest extends KernelTestCase
{
    use HandleTrait;

    public function setUp(): void
    {
        $this->messageBus = self::getContainer()->get('messenger.bus.default');
    }

    /** @test */
    public function shouldMapClassToClass(): void
    {
        $command = new GetMapper(
            SomeClassWithConstructorAndPublicReadOnlyProperties::class,
            SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class,
        );
        $mapper = $this->handle($command);

        $object1 = new SomeClassWithConstructorAndPublicReadOnlyProperties(
            1.0,
            'test',
            'test2',
            'test3',
            2,
            true,
        );

        $object2 = $command->map($mapper, $object1);

        self::assertInstanceOf(SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class, $object2);
        self::assertEquals(1.0, $object2->test0);
        self::assertEquals('test', $object2->test1);
        self::assertEquals('test2', $object2->test2);
        self::assertEquals('test3', $object2->test3);
        self::assertEquals(2, $object2->test4);
        self::assertEquals(true, $object2->test8);
    }

    /** @test */
    public function shouldMapArrayToClass(): void
    {
        $command = new GetMapper(
            SomeClassWithConstructorAndPublicReadOnlyProperties::class,
            SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class,
            'array'
        );
        $mapper = $this->handle($command);

        $object1 = [
            'test0' => 1.0,
            'test1' => 'test',
            'test2' => 'test2',
            'test3' => 'test3',
            'test4' => 2,
            'test5' => true,
        ];

        $object2 = $command->map($mapper, $object1);

        self::assertInstanceOf(SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class, $object2);
        self::assertEquals(1.0, $object2->test0);
        self::assertEquals('test', $object2->test1);
        self::assertEquals('test2', $object2->test2);
        self::assertEquals('test3', $object2->test3);
        self::assertEquals(2, $object2->test4);
        self::assertEquals(true, $object2->test8);
    }

    /** @test */
    public function shouldMapObjectToClass(): void
    {
        $command = new GetMapper(
            SomeClassWithConstructorAndPublicReadOnlyProperties::class,
            SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class,
            'object'
        );
        $mapper = $this->handle($command);

        $object1 = (object) [
            'test0' => 1.0,
            'test1' => 'test',
            'test2' => 'test2',
            'test3' => 'test3',
            'test4' => 2,
            'test5' => true,
        ];

        $object2 = $command->map($mapper, $object1);

        self::assertInstanceOf(SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class, $object2);
        self::assertEquals(1.0, $object2->test0);
        self::assertEquals('test', $object2->test1);
        self::assertEquals('test2', $object2->test2);
        self::assertEquals('test3', $object2->test3);
        self::assertEquals(2, $object2->test4);
        self::assertEquals(true, $object2->test8);
    }

    /** @test */
    public function shouldMapClassToArray(): void
    {
        $command = new GetMapper(
            SomeClassWithConstructorAndPublicReadOnlyProperties::class,
            SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class,
            null,
            'array'
        );
        $mapper = $this->handle($command);

        $object1 = new SomeClassWithConstructorAndPublicReadOnlyProperties(
            1.0,
            'test',
            'test2',
            'test3',
            2,
            true,
        );

        $object2 = $command->map($mapper, $object1);

        self::assertIsArray($object2);
        self::assertEquals(1.0, $object2['test0']);
        self::assertEquals('test', $object2['test1']);
        self::assertEquals('test2', $object2['test2']);
        self::assertEquals('test3', $object2['test3']);
        self::assertEquals(2, $object2['test4']);
        self::assertEquals(true, $object2['test8']);
    }

    /** @test */
    public function shouldMapObjectToArray(): void
    {
        $command = new GetMapper(
            SomeClassWithConstructorAndPublicReadOnlyProperties::class,
            SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class,
            'object',
            'array'
        );
        $mapper = $this->handle($command);

        $object1 = (object) [
            'test0' => 1.0,
            'test1' => 'test',
            'test2' => 'test2',
            'test3' => 'test3',
            'test4' => 2,
            'test5' => true,
        ];

        $object2 = $command->map($mapper, $object1);

        self::assertIsArray($object2);
        self::assertEquals(1.0, $object2['test0']);
        self::assertEquals('test', $object2['test1']);
        self::assertEquals('test2', $object2['test2']);
        self::assertEquals('test3', $object2['test3']);
        self::assertEquals(2, $object2['test4']);
        self::assertEquals(true, $object2['test8']);
    }

    /** @test */
    public function shouldMapArrayToArray(): void
    {
        $command = new GetMapper(
            SomeClassWithConstructorAndPublicReadOnlyProperties::class,
            SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2::class,
            'array',
            'array'
        );
        $mapper = $this->handle($command);

        $object1 = [
            'test0' => 1.0,
            'test1' => 'test',
            'test2' => 'test2',
            'test3' => 'test3',
            'test4' => 2,
            'test5' => true,
        ];

        $object2 = $command->map($mapper, $object1);

        self::assertIsArray($object2);
        self::assertEquals(1.0, $object2['test0']);
        self::assertEquals('test', $object2['test1']);
        self::assertEquals('test2', $object2['test2']);
        self::assertEquals('test3', $object2['test3']);
        self::assertEquals(2, $object2['test4']);
        self::assertEquals(true, $object2['test8']);
    }
}

class SomeClassWithConstructorAndPublicReadOnlyProperties
{
    public function __construct(
        public readonly float $test0,
        public readonly mixed $test1,
        public readonly string $test2,
        public readonly ?string $test3 = null,
        public readonly ?int $test4 = null,
        public readonly bool $test5 = false,
    ) {}
}

class SomeClassWithConstructorAndPublicReadOnlyPropertiesNo2
{
    public function __construct(
        public readonly float $test0,
        public readonly mixed $test1,
        public readonly string $test2,
        public readonly ?string $test3 = null,
        public readonly ?int $test4 = null,
        #[Mapper\TargetProperty('test5')]
        public readonly bool $test8 = false,
    ) {}
}

class SomeClassWithConstructorAndPublicReadOnlyNestedProperties
{
    public function __construct(
        public readonly float $test0,
        public readonly mixed $test1,
        public readonly SomeClassWithConstructorAndPublicReadOnlyProperties $test2,
    ) {}
}

class SomeClassWithConstructorAndPrivateProperties
{
    public function __construct(
        private float $test0,
        private mixed $test1,
        private string $test2,
        private ?string $test3 = null,
        private ?int $test4 = null,
        private bool $test5 = false,
    ) {}

    public function getTest0(): float
    {
        return $this->test0;
    }

    public function getTest1(): mixed
    {
        return $this->test1;
    }

    public function getTest2(): string
    {
        return $this->test2;
    }

    public function getTest3(): ?string
    {
        return $this->test3;
    }

    public function getTest4(): ?int
    {
        return $this->test4;
    }

    public function getTest5(): bool
    {
        return $this->test5;
    }

    public function setTest0(float $test0): void
    {
        $this->test0 = $test0;
    }

    public function setTest1(mixed $test1): void
    {
        $this->test1 = $test1;
    }

    public function setTest2(string $test2): void
    {
        $this->test2 = $test2;
    }

    public function setTest3(?string $test3): void
    {
        $this->test3 = $test3;
    }

    public function setTest4(?int $test4): void
    {
        $this->test4 = $test4;
    }

    public function setTest5(bool $test5): void
    {
        $this->test5 = $test5;
    }
}

class SomeClassWithCustomAccessor
{
    #[Mapper\Accessor(setter: 'setSomeTest', getter: 'getCustomTest')]
    private float $test0;

    public function getCustomTest(): float
    {
        return $this->test0;
    }

    public function setSomeTest(float $test0): void
    {
        $this->test0 = $test0;
    }
}

class SomeClassWithDeepNestedPropertiesAndWithoutGetters
{
    public function __construct(
        public readonly float $test0,
        public readonly mixed $test1,
        private ?SomeClassWithConstructorAndPublicReadOnlyProperties $test2,
        private ?SomeClassWithConstructorAndPrivateProperties $test3 = null,
        private ?SomeClassWithCustomAccessor $test4 = null,
    ) {}
}
