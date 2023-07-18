<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Unit\Expression;

use PBaszak\DedicatedMapperBundle\Expression\Assets\Expression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Getter;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Setter;
use PBaszak\DedicatedMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PHPUnit\Framework\TestCase;

class ArrayExpressionBuilderTestedClass
{
    /** @var string[] */
    public \ArrayObject $test;
    public string $test2;
}

/** @group unit */
class ArrayExpressionBuilderTest extends TestCase
{
    private const PLACEHOLDERS = [
        Expression::VAR_VARIABLE => 'var',
        Getter::SOURCE_VARIABLE_NAME => 'data',
        Getter::SETTER_EXPRESSION => '$output = {{getter}};',
        Getter::DEFAULT_VALUE_EXPRESSION => '\'default\'',
        Getter::SIMPLE_OBJECT_EXPRESSION => '($x = {{getter}}) instanceof ArrayObject ? $x : new ArrayObject($x)',
        Getter::CALLBACKS_EXPRESSION => '${{var}} = \'changed\';',
        Getter::VALUE_NOT_FOUND_EXPRESSIONS => 'throw new \Exception(\'Value not found\');',
    ];

    private ArrayExpressionBuilder $builder;
    private Blueprint $blueprint;

    protected function setUp(): void
    {
        $this->builder = new ArrayExpressionBuilder();
        $this->blueprint = Blueprint::create(ArrayExpressionBuilderTestedClass::class, false);
    }

    protected function getExpressions(string $property): array
    {
        $getter = $this->builder->getGetter(
            $this->blueprint->getProperty('test'),
        );

        return (new \ReflectionProperty($getter, 'expressions'))->getValue($getter);
    }

    protected function getExpression(string $property, string $key): string
    {
        $expr = $this->getExpressions($property)[$key];

        $hasVarVariable = false !== strpos($expr, Expression::VAR_VARIABLE);
        $hasSetter = false !== strpos($expr, Getter::SETTER_EXPRESSION);

        do {
            $expr = str_replace(
                [
                    Getter::SOURCE_VARIABLE_NAME,
                    Getter::SETTER_EXPRESSION,
                    Getter::DEFAULT_VALUE_EXPRESSION,
                    Getter::SIMPLE_OBJECT_EXPRESSION,
                    Getter::CALLBACKS_EXPRESSION,
                    Getter::VALUE_NOT_FOUND_EXPRESSIONS,
                ],
                [
                    self::PLACEHOLDERS[Getter::SOURCE_VARIABLE_NAME],
                    self::PLACEHOLDERS[Getter::SETTER_EXPRESSION],
                    self::PLACEHOLDERS[Getter::DEFAULT_VALUE_EXPRESSION],
                    self::PLACEHOLDERS[Getter::SIMPLE_OBJECT_EXPRESSION],
                    self::PLACEHOLDERS[Getter::CALLBACKS_EXPRESSION],
                    self::PLACEHOLDERS[Getter::VALUE_NOT_FOUND_EXPRESSIONS],
                ],
                $expr
            );

            $expr = str_replace(
                [
                    Setter::GETTER_EXPRESSION,
                    Expression::VAR_VARIABLE,
                ],
                [
                    $hasVarVariable ? '${{var}}' : $this->builder->getGetter(
                        $this->blueprint->getProperty('test'),
                    )->getSimpleGetter(),
                    self::PLACEHOLDERS[Expression::VAR_VARIABLE],
                ],
                $expr
            );
        } while (false !== strpos($expr, '{{'));

        return $hasSetter ? $expr : '$output = '.$expr.';';
    }

    protected function assertOutputHasValue(string $property, mixed $value, string $key): void
    {
        $data = [
            $property => $value,
        ];
        $output = null;
        eval($this->getExpression($property, $key));
        $this->assertTrue(isset($output));
        $this->assertEquals($value, $output);
    }

    protected function assertOutputIsNotSet(string $property, mixed $value, string $key): void
    {
        $data = [];
        $output = null;
        eval($this->getExpression($property, $key));
        $this->assertFalse(isset($output));
    }

    protected function assertExpectedException(string $property, mixed $value, string $key): void
    {
        $data = [];

        $this->expectException(\Exception::class);
        eval($this->getExpression($property, $key));
    }

    protected function assertNotException(string $property, mixed $value, string $key): void
    {
        $data = [];
        eval($this->getExpression($property, $key));
        $this->assertTrue(isset($output));
    }

    protected function assertOutputHasChangedValue(string $property, mixed $value, string $key): void
    {
        $data = [
            $property => $value,
        ];
        $output = null;
        eval($this->getExpression($property, $key));
        $this->assertTrue(isset($output));
        $this->assertEquals('changed', $output);
    }

    protected function assertOutputHasDefaultValue(string $property, mixed $value, string $key): void
    {
        $data = [];
        $output = null;
        eval($this->getExpression($property, $key));
        $this->assertTrue(isset($output));
        $this->assertEquals('default', $output);
    }

    /** @test */
    public function getterBasicTest(): void
    {
        $this->assertEquals(
            '${{source}}[\'test\']',
            $this->getExpressions('test')['basic'],
        );
    }

    /** @test */
    public function getterTestNo00000(): void
    {
        $this->assertOutputHasValue('test', 'test', '00000');
        $this->assertOutputIsNotSet('test', 'test', '00000');
    }

    /** @test */
    public function getterTestNo00001(): void
    {
        $this->assertOutputHasValue('test', 'test', '00001');
        $this->assertExpectedException('test', 'test', '00001');
    }

    /** @test */
    public function getterTestNo00010(): void
    {
        $this->assertOutputHasChangedValue('test', 'test', '00010');
        $this->assertOutputIsNotSet('test', 'test', '00010');
    }

    /** @test */
    public function getterTestNo00011(): void
    {
        $this->assertOutputHasChangedValue('test', 'test', '00011');
        $this->assertExpectedException('test', 'test', '00011');
    }

    /** @test */
    public function getterTestNo00100(): void
    {
        $this->assertOutputHasValue('test', 'test', '00100');
        $this->assertOutputHasDefaultValue('test', 'test', '00100');
    }

    /** @test */
    public function getterTestNo00101(): void
    {
        $this->assertOutputHasValue('test', 'test', '00101');
        $this->assertOutputHasDefaultValue('test', 'test', '00101');
    }

    /** @test */
    public function getterTestNo00110(): void
    {
        $this->assertOutputHasChangedValue('test', 'test', '00110');
        $this->assertNotException('test', 'test', '00110');
    }

    /** @test */
    public function getterTestNo00111(): void
    {
        $this->assertOutputHasChangedValue('test', 'test', '00111');
        $this->assertNotException('test', 'test', '00111');
    }
}
