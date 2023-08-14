<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Integration\Symfony;

use PBaszak\DedicatedMapper\Contract\MapperServiceInterface;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Modificator\Symfony\SymfonyValidator;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/** @group integration */
class SymfonyValidatorTest extends KernelTestCase
{
    private MapperServiceInterface $mapperService;
    private array $dummy;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mapperService = self::getContainer()->get('pbaszak.dedicated_mapper.validated');
        $this->dummy = json_decode(file_get_contents(__DIR__.'/../../assets/dummy.json'), true);
    }

    /** @test */
    public function shouldThrowValidationException(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\ValidationFailedException::class);
        $this->mapperService->map(
            $this->dummy,
            Dummy::class,
            new ArrayExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
            null,
            false,
            false,
            [new SymfonyValidator()]
        );
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function shouldReturnCorrectExceptionPaths(): void
    {
        try {
            $this->mapperService->map(
                $this->dummy,
                Dummy::class,
                new ArrayExpressionBuilder(),
                new ReflectionClassExpressionBuilder(),
                null,
                false,
                false,
                [new SymfonyValidator()]
            );
        } catch (ValidationFailedException $e) {
            $this->assertEquals('_embedded.items.0.currency', $e->getViolations()->get(0)->getPropertyPath());
        }
    }
}
