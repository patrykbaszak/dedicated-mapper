<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Func\Attribute;

use PBaszak\DedicatedMapper\Attribute\InitialValueCallback;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\FunctionExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\ExpressionBuilder;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PHPUnit\Framework\TestCase;

class InitialValueCallbackTester
{
    #[InitialValueCallback('new \DateTime(\'2023-01-01 00:00:00\')')]
    public \DateTime $test;
}

class InitialValueCallbackUseSourceTester
{
    #[InitialValueCallback('new \DateTime(\'2023-01-01 00:00:00\')', true)]
    public \DateTime $test;
}

class InitialValueCallbackNullableTester
{
    #[InitialValueCallback('new \DateTime(\'2023-01-01 00:00:00\')')]
    public ?\DateTime $test = null;
}

class InitialValueCallbackNullableUseSourceTester
{
    #[InitialValueCallback('new \DateTime(\'2023-01-01 00:00:00\')', true)]
    public ?\DateTime $test = null;
}

/** @group func */
class InitialValueCallbackTest extends TestCase
{
    /** @test */
    public function shouldAddSpecialTimeToOutput(): void
    {
        $data = [];
        foreach ([true, false] as $throwExceptionOnMissing) {
            foreach ([true, false] as $allowNullable) {
                foreach ([true, false] as $useSource) {
                    $output = $this->map($data, $throwExceptionOnMissing, $allowNullable, $useSource);
                    $this->assertEquals(new \DateTime('2023-01-01 00:00:00'), $output->test);
                }
            }
        }
    }

    /** @test */
    public function shouldNotUseIncomingTimeForOutput(): void
    {
        $data = [
            'test' => new \DateTime('2021-01-01 00:00:00'),
        ];
        foreach ([true, false] as $throwExceptionOnMissing) {
            foreach ([true, false] as $allowNullable) {
                $output = $this->map($data, $throwExceptionOnMissing, $allowNullable, false);
                $this->assertEquals(new \DateTime('2023-01-01 00:00:00'), $output->test);
            }
        }
    }

    /** @test */
    public function shouldUseIncomingTimeForOutput(): void
    {
        $data = [
            'test' => new \DateTime('2021-01-01 00:00:00'),
        ];
        foreach ([true, false] as $throwExceptionOnMissing) {
            foreach ([true, false] as $allowNullable) {
                $output = $this->map($data, $throwExceptionOnMissing, $allowNullable, true);
                $this->assertEquals(new \DateTime('2021-01-01 00:00:00'), $output->test);
            }
        }
    }

    private function map(array $data, bool $throwExceptionOnMissing, bool $allowNullable, bool $useSource): InitialValueCallbackNullableUseSourceTester|InitialValueCallbackNullableTester|InitialValueCallbackUseSourceTester|InitialValueCallbackTester
    {
        return (new ExpressionBuilder(
            Blueprint::create(
                $allowNullable
                ? ($useSource ? InitialValueCallbackNullableUseSourceTester::class : InitialValueCallbackNullableTester::class)
                : ($useSource ? InitialValueCallbackUseSourceTester::class : InitialValueCallbackTester::class)
            ),
            new ArrayExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
            new FunctionExpressionBuilder(),
            false
        ))->build($throwExceptionOnMissing)->getMapper()->map($data);
    }
}
