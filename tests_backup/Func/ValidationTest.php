<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Func;

use PBaszak\DedicatedMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Expression\Modificator\Symfony\SymfonyValidator;
use PBaszak\DedicatedMapperBundle\Tests\assets\Dummy;
use PBaszak\DedicatedMapperBundle\ValidatedMapperService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @group func */
class ValidationTest extends KernelTestCase
{
    private array $dummy;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->dummy = json_decode(file_get_contents(__DIR__.'/../assets/dummy.json'), true);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function test(): void
    {
        $mapper = self::getContainer()->get(ValidatedMapperService::class);

        $this->expectException(\Symfony\Component\Validator\Exception\ValidationFailedException::class);
        $mapperDummyObject = $mapper->map(
            $this->dummy,
            Dummy::class,
            new ArrayExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
            modificators: [
                new SymfonyValidator(),
            ]
        );

        $this->assertInstanceOf(Dummy::class, $mapperDummyObject);
    }
}
