<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\E2e;

use PBaszak\DedicatedMapper\Expression\Builder\AnonymousObjectExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\MapperService;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('e2e')]
class MapperServiceTest extends TestCase
{
    #[Test]
    public function shouldReturnCorrectMappedDummyObject(): void
    {
        $mapper = new MapperService(dirname(__DIR__, 2).'/var/mapper/');
        $dummyJson = file_get_contents(dirname(__DIR__).'/assets/dummy.json');
        $expectedResults = require dirname(__DIR__).'/assets/DummyByFormats.php';

        $dummy = $mapper->map(
            json_decode($dummyJson),
            Dummy::class,
            new AnonymousObjectExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
        );

        $this->assertEquals($expectedResults[ReflectionClassExpressionBuilder::class], $dummy);
    }
}
