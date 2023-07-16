<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Func;

use PBaszak\MessengerMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Modificator\Symfony\SymfonyValidator;
use PBaszak\MessengerMapperBundle\Tests\assets\Dummy;
use PBaszak\MessengerMapperBundle\ValidatedMapperService;
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
