<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Func\Expression;

use PBaszak\DedicatedMapper\Expression\Assets\Expression;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\FunctionExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\ExpressionBuilder;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
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
    /** @test */
    public function testSimpleExpression(): void
    {
        $property = Blueprint::create(ExpressionTestedClass::class, false)->getProperty('test2');
        $expression = (new Expression(
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        ))->build($property, $property)->toString();

        $output = null;
        $data = ['test2' => 'test2'];
        eval($expression);

        self::assertSame('test2', $output['test2']);
    }

    /** @test */
    public function testSimpleObjectExpression(): void
    {
        $property = Blueprint::create(ExpressionTestedClass::class, false)->getProperty('test');
        $expression = (new Expression(
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        ))->build($property, $property)->toString();

        $output = null;
        $data = ['test' => ['test2']];
        eval($expression);

        self::assertNotSame($data, $output);
        self::assertInstanceOf(\ArrayObject::class, $output['test']);
        self::assertSame($data['test'], $output['test']->getArrayCopy());
    }

    /** @test */
    public function testDummyBlueprintBetweenAllFormats(): void
    {
        $data = require __DIR__.'/../../assets/DummyByFormats.php';

        foreach ([true, false] as $isCollection) {
            foreach ([true, false] as $throwException) {
                for ($i = 0; $i < count($data) - 1; ++$i) {
                    for ($j = $i; $j < count($data); ++$j) {
                        $blueprint = Blueprint::create(Dummy::class);
                        $sourceClass = array_keys($data)[$i];
                        $targetClass = array_keys($data)[$j];

                        if (!class_exists($sourceClass) || !class_exists($targetClass)) {
                            throw new \RuntimeException("One of classes doesn't exists: {$sourceClass}, {$targetClass}");
                        }

                        $mapper = (new ExpressionBuilder(
                            $blueprint,
                            new $sourceClass(),
                            new $targetClass(),
                            new FunctionExpressionBuilder(),
                            $isCollection
                        ))->build($throwException)->getMapper();

                        $source = $isCollection ? array_fill(0, 5, $data[$sourceClass]) : $data[$sourceClass];
                        $expectedTarget = $isCollection ? array_fill(0, 5, $data[$targetClass]) : $data[$targetClass];

                        $target = $mapper($source);
                        self::assertEquals($expectedTarget, $target);
                    }
                }
            }
        }
    }
}
