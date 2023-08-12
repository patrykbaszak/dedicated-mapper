<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Unit\Properties;

use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;
use PBaszak\DedicatedMapperBundle\Tests\assets\Dummy;
use PHPUnit\Framework\TestCase;

/** @group unit */
class TreeBuildingTest extends TestCase
{
    private const BLUEPRINT_CLASS = Dummy::class;

    /** @test */
    public function buildTest(): void
    {
        $blueprint = Blueprint::create(self::BLUEPRINT_CLASS, false);

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals(self::BLUEPRINT_CLASS, $blueprint->reflection->getName());
        $this->assertCount(4, $blueprint->properties);
        $this->assertSame(false, $blueprint->isCollection);
        foreach ($blueprint->properties as $property) {
            $this->assertInstanceOf(Property::class, $property);
        }
        /** @var Property $embedded */
        $embedded = $blueprint->properties['_embedded'];
        $this->assertInstanceOf(Blueprint::class, $embedded->blueprint);
        $this->assertCount(4, $embedded->blueprint->properties);
        $items = $embedded->getChildren()['items'];
        $this->assertInstanceOf(Blueprint::class, $items->blueprint);
        $this->assertCount(13, $items->blueprint->properties);
        $this->assertSame(true, $items->blueprint->isCollection);
    }
}
