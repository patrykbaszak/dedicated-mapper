<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Func\Expression;

use PBaszak\DedicatedMapper\Expression\Assets\Expression;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExpressionTestedClass
{
    /** @var string[] */
    public \ArrayObject $test;
    public string $test2;
}

/** @group func */
class ExpressionTest extends KernelTestCase
{
    private Blueprint $blueprint;

    protected function setUp(): void
    {
        $this->blueprint = Blueprint::create(ExpressionTestedClass::class, false);
    }

    /** @test */
    public function testSimpleExpression(): void
    {
        $property = $this->blueprint->getProperty('test2');
        $expression = (new Expression(
            (new ArrayExpressionBuilder())->getGetter($property),
            (new ArrayExpressionBuilder())->getSetter($property),
        ))->build($property, $property)->toString();

        $output = null;
        $data = ['test2' => 'test2'];
        eval($expression);

        self::assertSame('test2', $output['test2']);
    }

    /** @test */
    public function testSimpleObjectExpression(): void
    {
        $property = $this->blueprint->getProperty('test');
        $expression = (new Expression(
            (new ArrayExpressionBuilder())->getGetter($property),
            (new ArrayExpressionBuilder())->getSetter($property),
        ))->build($property, $property)->toString();

        $output = null;
        $data = ['test' => ['test2']];
        eval($expression);

        self::assertNotSame($data, $output);
        self::assertInstanceOf(\ArrayObject::class, $output['test']);
        self::assertSame($data['test'], $output['test']->getArrayCopy());
    }
}
