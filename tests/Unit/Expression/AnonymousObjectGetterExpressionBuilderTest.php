<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Unit\Expression;

use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Expression;
use PBaszak\DedicatedMapperBundle\Expression\Builder\AnonymousObjectExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PHPUnit\Framework\TestCase;

class AnonymousObjectGetterExpressionBuilderTestedClass
{
    /**
     * isSimpleObject
     * !hasDefaultValue.
     *
     * @var string[]
     */
    public \ArrayObject $test;

    /**
     * isSimpleObject
     * hasDefaultValue.
     */
    public ?\ArrayObject $test2 = null;

    /**
     * !isSimpleObject
     * hasDefaultValue.
     */
    public string $test3 = 'default';

    /**
     * !isSimpleObject
     * !hasDefaultValue.
     */
    public string $test4;
}

/** @group unit */
class AnonymousObjectGetterExpressionBuilderTest extends TestCase
{
    private AnonymousObjectExpressionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new AnonymousObjectExpressionBuilder();
    }

    protected function getExpression(
        bool $isSimpleObject,
        bool $throwExceptionOnMissingRequiredValue,
        bool $hasDefaultValue,
        bool $hasCallback,
        bool $hasNotFoundCallbacks,
    ): string {
        if ($isSimpleObject) {
            $propertyName = $hasDefaultValue ? 'test2' : 'test';
        } else {
            $propertyName = $hasDefaultValue ? 'test3' : 'test4';
        }

        $callbacks = $hasCallback ? [
            $isSimpleObject
                ? new MappingCallback("\${{var}}->append('appended');\n")
                : new MappingCallback("\${{var}} = strtoupper(\${{var}});\n"),
        ] : [];
        $callbacks = $hasNotFoundCallbacks ? array_merge($callbacks, [new MappingCallback("throw new Exception('Not found.');\n", 0, true)]) : $callbacks;

        if ($hasDefaultValue && $hasCallback && $isSimpleObject) {
            $callbacks[] = new MappingCallback("if (!\${{var}}) {\n\t\${{var}} = new ArrayObject(['a', 'b', 'c']);\n}\n", 1, false);
        }

        $blueprint = Blueprint::create(AnonymousObjectGetterExpressionBuilderTestedClass::class, false);

        return "\$output = (object) [];\n".(new Expression(
            $this->builder->getGetter($blueprint->getProperty($propertyName)),
            $this->builder->getSetter($blueprint->getProperty($propertyName)),
            null,
            [],
            $callbacks,
            false,
            $throwExceptionOnMissingRequiredValue,
            'data',
            'output',
            'var',
            null
        ))->build(
            $blueprint->getProperty($propertyName),
            $blueprint->getProperty($propertyName)
        )->toString();
    }

    /** @return bool[] */
    protected function explodeKey(string $key): array
    {
        return array_map(fn (string $value) => boolval(intval($value)), str_split($key));
    }

    protected function assertIsOutputAsigned(string $key): void
    {
        $key = $this->explodeKey($key);

        if ($key[0]) {
            $propertyName = $key[2] ? 'test2' : 'test';
        } else {
            $propertyName = $key[2] ? 'test3' : 'test4';
        }

        $data = (object) [
            'test' => new \ArrayObject(['a', 'b', 'c']),
            'test2' => new \ArrayObject(['a', 'b', 'c']),
            'test3' => 'test',
            'test4' => 'test',
        ];
        $expression = $this->getExpression(...$key);

        eval($expression);
        if (!isset($output)) {
            throw new \Exception('Output is not set.');
        }

        if ($key[3]) {
            $data = (object) [
                'test' => new \ArrayObject(['a', 'b', 'c', 'appended']),
                'test2' => new \ArrayObject(['a', 'b', 'c', 'appended']),
                'test3' => 'TEST',
                'test4' => 'TEST',
            ];
        }

        $this->assertEquals($data->$propertyName, $output->$propertyName);
    }

    protected function assertIsOutputNotAsigned(string $key): void
    {
        $key = $this->explodeKey($key);
        
        if ($key[1]) {
            throw new \LogicException('Cannot test not asigned output on property with throw exception on missing required value.');
        }
        if ($key[2]) {
            throw new \LogicException('Cannot test not asigned output on property with default value.');
        }
        if ($key[4]) {
            throw new \LogicException('Cannot test not asigned output on property with not found callback.');
        }

        $data = (object) [];
        $expression = $this->getExpression(...$key);
        eval($expression);

        if (isset($output)) {
            $this->assertEmpty((array) $output);
        } else {
            $this->assertFalse(isset($output));
        }
    }

    protected function assertIsSimpleObject(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[0]) {
            throw new \LogicException('Cannot test simple object on not simple object.');
        }

        $data = $key[2] ? (object) [
            'test2' => ['a', 'b', 'c'],
        ] : (object) [
            'test' => ['a', 'b', 'c'],
        ];
        $expression = $this->getExpression(...$key);

        eval($expression);
        if (!isset($output)) {
            throw new \Exception('Output is not set.');
        }

        $this->assertInstanceOf(\ArrayObject::class, $key[2] ? $output->test2 : $output->test);
    }

    protected function assertThrowsExceptionOnMissingRequiredValue(string $key): void
    {
        $key = $this->explodeKey($key);

        if ($key[2]) {
            throw new \LogicException('Cannot test missing required value on property with default value.');
        }
        if ($key[4]) {
            throw new \LogicException('Cannot test missing required value on property with not found callback.');
        }

        $data = (object) [];
        $expression = $this->getExpression(...$key);

        $this->expectException(\Error::class);
        @eval($expression);
    }

    protected function assertHasDefaultValue(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[2]) {
            throw new \LogicException('Cannot test default value on property without default value.');
        }

        $data = (object) [];
        $expression = $this->getExpression(...$key);

        eval($expression);
        if (!isset($output)) {
            throw new \Exception('Output is not set.');
        }

        if ($key[0]) {
            if ($key[3]) {
                $this->assertEquals(new \ArrayObject(['a', 'b', 'c', 'appended']), $output->test2);
            } else {
                $this->assertEquals(null, $output->test2);
            }
        } else {
            if ($key[3]) {
                $this->assertEquals('DEFAULT', $output->test3);
            } else {
                $this->assertEquals('default', $output->test3);
            }
        }
    }

    protected function assertHasCallback(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[3]) {
            throw new \LogicException('Cannot test callback on property without callback.');
        }

        if ($key[0]) {
            $propertyName = $key[2] ? 'test2' : 'test';
        } else {
            $propertyName = $key[2] ? 'test3' : 'test4';
        }

        $data = $key[0] ? (object) [
            $propertyName => [
                'a', 'b', 'c',
            ],
        ] : (object) [
            $propertyName => 'test',
        ];
        $expression = $this->getExpression(...$key);

        eval($expression);
        if (!isset($output)) {
            throw new \Exception('Output is not set.');
        }

        $output = $output->$propertyName;

        if ($key[0]) {
            $this->assertInstanceOf(\ArrayObject::class, $output);
            $this->assertEquals(['a', 'b', 'c', 'appended'], $output->getArrayCopy());
        } else {
            $this->assertSame('TEST', $output);
        }
    }

    protected function assertHasNotFoundCallback(string $key): void
    {
        $key = $this->explodeKey($key);

        if ($key[2]) {
            throw new \LogicException('Cannot test not found callback on property with default value.');
        }

        if (!$key[4]) {
            throw new \LogicException('Cannot test not found callback on property without not found callback.');
        }

        $data = (object) [];
        $expression = $this->getExpression(...$key);

        try {
            eval($expression);
        } catch (\Exception $e) {
            $this->assertSame('Not found.', $e->getMessage());
        }
    }

    /** @test */
    public function testGetter00000(): void
    {
        $key = '00000';
        $this->assertIsOutputAsigned($key);
    }

    /** @test */
    public function testGetter00001(): void
    {
        $key = '00001';
        $this->assertIsOutputAsigned($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter00010(): void
    {
        $key = '00010';
        $this->assertHasCallback($key);
        $this->assertIsOutputNotAsigned($key);
        $this->assertIsOutputAsigned($key);
    }

    /** @test */
    public function testGetter00011(): void
    {
        $key = '00011';
        $this->assertIsOutputAsigned($key);
        $this->assertHasCallback($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter00100(): void
    {
        $key = '00100';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter00101(): void
    {
        $key = '00101';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter00110(): void
    {
        $key = '00110';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter00111(): void
    {
        $key = '00111';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter01000(): void
    {
        $key = '01000';
        $this->assertIsOutputAsigned($key);
        $this->assertThrowsExceptionOnMissingRequiredValue($key);
    }

    /** @test */
    public function testGetter01001(): void
    {
        $key = '01001';
        $this->assertIsOutputAsigned($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter01010(): void
    {
        $key = '01010';
        $this->assertIsOutputAsigned($key);
        $this->assertThrowsExceptionOnMissingRequiredValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter01011(): void
    {
        $key = '01011';
        $this->assertIsOutputAsigned($key);
        $this->assertHasCallback($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter01100(): void
    {
        $key = '01100';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter01101(): void
    {
        $key = '01101';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter01110(): void
    {
        $key = '01110';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter01111(): void
    {
        $key = '01111';
        $this->assertIsOutputAsigned($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter10000(): void
    {
        $key = '10000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsOutputNotAsigned($key);
        $this->assertIsSimpleObject($key);
    }

    /** @test */
    public function testGetter10001(): void
    {
        $key = '10001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter10010(): void
    {
        $key = '10010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsOutputNotAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter10011(): void
    {
        $key = '10011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasCallback($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter10100(): void
    {
        $key = '10100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter10101(): void
    {
        $key = '10101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter10110(): void
    {
        $key = '10110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter10111(): void
    {
        $key = '10111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter11000(): void
    {
        $key = '11000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertThrowsExceptionOnMissingRequiredValue($key);
    }

    /** @test */
    public function testGetter11001(): void
    {
        $key = '11001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter11010(): void
    {
        $key = '11010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertThrowsExceptionOnMissingRequiredValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter11011(): void
    {
        $key = '11011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasCallback($key);
        $this->assertHasNotFoundCallback($key);
    }

    /** @test */
    public function testGetter11100(): void
    {
        $key = '11100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter11101(): void
    {
        $key = '11101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
    }

    /** @test */
    public function testGetter11110(): void
    {
        $key = '11110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }

    /** @test */
    public function testGetter11111(): void
    {
        $key = '11111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsSimpleObject($key);
        $this->assertHasDefaultValue($key);
        $this->assertHasCallback($key);
    }
}
