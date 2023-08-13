<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Unit\Utils;

use PBaszak\DedicatedMapper\Utils\NativeSimpleObject;
use PHPUnit\Framework\TestCase;

/** @group unit */
class NativeSimpleObjectTest extends TestCase
{
    /** @test */
    public function testDateTimeConstructor(): void
    {
        $result = NativeSimpleObject::DateTimeConstructor();
        $this->assertInstanceOf(\DateTime::class, $result);

        $result2 = NativeSimpleObject::DateTimeConstructor('2022-01-01');
        $this->assertEquals('2022-01-01', $result2->format('Y-m-d'));

        $obj = (object) [
            'date' => '2022-01-02',
            'timezone' => 'Europe/Warsaw',
        ];
        $result3 = NativeSimpleObject::DateTimeConstructor($obj);
        $this->assertEquals('2022-01-02', $result3->format('Y-m-d'));
        $this->assertEquals('Europe/Warsaw', $result3->getTimezone()->getName());

        $arr = [
            'date' => '2022-01-03',
            'timezone' => 'America/New_York',
        ];
        $result4 = NativeSimpleObject::DateTimeConstructor($arr);
        $this->assertEquals('2022-01-03', $result4->format('Y-m-d'));
        $this->assertEquals('America/New_York', $result4->getTimezone()->getName());
    }

    /** @test */
    public function testDateTimeZoneConstructor(): void
    {
        // Test dla wartości null
        $result = NativeSimpleObject::DateTimeZoneConstructor();
        $this->assertInstanceOf(\DateTimeZone::class, $result);
        $this->assertEquals((new \DateTime())->getTimezone()->getName(), $result->getName());

        // Test dla stringa
        $result2 = NativeSimpleObject::DateTimeZoneConstructor('Europe/Warsaw');
        $this->assertEquals('Europe/Warsaw', $result2->getName());

        // Test dla StdClass
        $obj = (object) ['timezone' => 'Europe/Warsaw'];
        $result3 = NativeSimpleObject::DateTimeZoneConstructor($obj);
        $this->assertEquals('Europe/Warsaw', $result3->getName());

        // Test dla tablicy
        $arr = ['timezone' => 'America/New_York'];
        $result4 = NativeSimpleObject::DateTimeZoneConstructor($arr);
        $this->assertEquals('America/New_York', $result4->getName());

        // Test dla StdClass z wartością null
        $obj2 = (object) ['timezone' => null];
        $result5 = NativeSimpleObject::DateTimeZoneConstructor($obj2);
        $this->assertInstanceOf(\DateTimeZone::class, $result5);
        $this->assertEquals((new \DateTime())->getTimezone()->getName(), $result5->getName());

        // Test dla nieprawidłowego obiektu/tablicy
        $invalidObj = (object) ['wrong_key' => 'some_value'];
        $this->expectException(\InvalidArgumentException::class);
        NativeSimpleObject::DateTimeZoneConstructor($invalidObj);
    }

    /** @test */
    public function testDateIntervalConstructor()
    {
        $this->expectException(\ArgumentCountError::class);
        NativeSimpleObject::DateIntervalConstructor(null);

        $interval = NativeSimpleObject::DateIntervalConstructor('P1Y2M3DT4H5M6S');
        $this->assertEquals(1, $interval->y);
        $this->assertEquals(2, $interval->m);
        $this->assertEquals(3, $interval->d);
        $this->assertEquals(4, $interval->h);
        $this->assertEquals(5, $interval->i);
        $this->assertEquals(6, $interval->s);

        $interval2 = NativeSimpleObject::DateIntervalConstructor('2 days');
        $this->assertEquals(2, $interval2->d);

        $obj = (object) [
            'y' => 1,
            'm' => 2,
            'd' => 3,
            'h' => 4,
            'i' => 5,
            's' => 6,
            'from_string' => false,
        ];
        $interval3 = NativeSimpleObject::DateIntervalConstructor($obj);
        $this->assertEquals(1, $interval3->y);
        $this->assertEquals(2, $interval3->m);
        $this->assertEquals(3, $interval3->d);
        $this->assertEquals(4, $interval3->h);
        $this->assertEquals(5, $interval3->i);
        $this->assertEquals(6, $interval3->s);

        $arr = [
            'date_string' => '2 days',
            'from_string' => true,
        ];
        $interval4 = NativeSimpleObject::DateIntervalConstructor($arr);
        $this->assertEquals(2, $interval4->d);

        $this->expectException(\InvalidArgumentException::class);
        $badArr = ['bad_key' => 'bad_value'];
        NativeSimpleObject::DateIntervalConstructor($badArr);
    }
}
