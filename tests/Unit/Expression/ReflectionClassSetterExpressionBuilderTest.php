<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Unit\Expression;

use DateTime;
use PBaszak\DedicatedMapper\Attribute\ApplyToCollectionItems;
use PBaszak\DedicatedMapper\Attribute\MappingCallback;
use PBaszak\DedicatedMapper\Attribute\SimpleObject;
use PBaszak\DedicatedMapper\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapper\Expression\Builder\FunctionExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\ExpressionBuilder;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ReflectionClassSetterExpressionTester
{
    public string $test;
    public bool $test2;

    #[SimpleObject(deconstructor: 'format', deconstructorArguments: ['Y-m-d'])]
    public ?\DateTime $test3 = null;

    public \DateTime $test4;

    /**
     * @var \DateTime[]
     */
    #[ApplyToCollectionItems(
        [new SimpleObject(deconstructor: 'format', deconstructorArguments: ['Y-m-d'])]
    )]
    public \ArrayObject $test5;
}

class NestedReflectionClassSetterExpressionTester
{
    public string $test;
    public bool $test2;
    public ReflectionClassSetterExpressionTester $test0;

    /** @var ReflectionClassSetterExpressionTester[] */
    public ?\ArrayObject $test4;

    /** @var \DateTime[] */
    public array $test5 = [];

    /** @var ReflectionClassSetterExpressionTester[] */
    public array $test6 = [];
}

/** @group unit */
class ReflectionClassSetterExpressionBuilderTest extends TestCase
{
    protected function getExpression(
        bool $isCollection,
        bool $hasFunction,
        bool $hasPathUsed,
        bool $isSimpleObject,
        bool $hasSimpleObjectDeconstructor,
        bool $isVarVariableUsed,
        string $class,
        string $property
    ): string {
        if ($hasFunction && $isSimpleObject) {
            throw new \LogicException('Function cannot be used with simple object.');
        }

        if ($isCollection && (!$hasFunction && !$isSimpleObject)) {
            throw new \LogicException('Collection can be used only with function or simple object.');
        }

        if ($hasFunction && !in_array($property, ['test0', 'test4', 'test6'])) {
            throw new \LogicException('Property must be test0, test4 or test6.');
        }

        if ($isSimpleObject && !in_array($property, ['test3', 'test4', 'test5'])) {
            throw new \LogicException('Property must be test3, test4 or test5.');
        }

        if ($hasSimpleObjectDeconstructor && !in_array($property, ['test3', 'test5'])) {
            throw new \LogicException('Property must be test3 or test5.');
        }

        $blueprint = Blueprint::create($class, $isCollection, null);
        $sourceProperty = $blueprint->getProperty($property);
        $targetProperty = clone $sourceProperty;

        $expressionBuilder = (new ExpressionBuilder(
            $blueprint,
            new ReflectionClassExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
            new FunctionExpressionBuilder()
        ));
        FunctionExpression::$createdExpressions = [];
        $reflection = new \ReflectionClass($expressionBuilder);
        $reflection->getProperty('throwExceptionOnMissingProperty')->setValue($expressionBuilder, false);
        $reflection->getMethod('matchBlueprints')->invokeArgs(
            $expressionBuilder,
            [
                $reflection->getProperty('blueprint')->getValue($expressionBuilder),
                $reflection->getProperty('source')->getValue($expressionBuilder),
                $reflection->getProperty('target')->getValue($expressionBuilder),
            ]
        );

        if ($isVarVariableUsed) {
            $callbacks = array_merge($callbacks ?? [], [new MappingCallback("\n")]);
        }

        $initialExpression = str_replace('{{target}}', 'output', (new ReflectionClassExpressionBuilder())->getSetterInitialExpression($blueprint, Uuid::v4()->toRfc4122())->toString());
        if ($hasPathUsed) {
            $initialExpression = "\$path = 'root';\n".$initialExpression;
        }

        $function = $hasFunction ? $reflection->getMethod('newFunctionExpression')->invokeArgs(
            $expressionBuilder,
            [
                $sourceProperty->blueprint,
                $sourceProperty->blueprint,
                $targetProperty->blueprint,
            ]
        ) : null;

        if ($function) {
            if ($hasPathUsed) {
                $function->pathVariable = 'path';
            }
        }

        return ($initialExpression ?? '').$reflection->getMethod('newPropertyExpression')->invokeArgs(
            $expressionBuilder,
            [
                $sourceProperty,
                $targetProperty,
                $function,
                $hasFunction ? 'function' : null,
                $callbacks ?? [],
            ]
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

        if (!$key[0] && !$key[1] && !$key[3] && !$key[4]) {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test';
            $data = new $class();
            $data->$property = 'test';
        } elseif (!$key[1] && ($key[3] || $key[4])) {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test3';
            $data = new $class();
            $data->$property = new \DateTime('2021-01-01');
        } elseif ($key[1] && !$key[0]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test0';
            $data = new $class();
            $data->$property = new ReflectionClassSetterExpressionTester();
            $data->$property->test = 'test';
            $data->$property->test2 = true;
            $data->$property->test3 = new \DateTime('2021-01-01');
        } elseif ($key[0]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test6';
            $d = new ReflectionClassSetterExpressionTester();
            $d->test = 'test';
            $d->test2 = true;
            $d->test3 = new \DateTime('2021-01-01');
            $data = new $class();
            $data->$property = [$d, clone $d];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertEquals($data->$property, $output->$property);
    }

    protected function assertIsAsignedCollection(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[0]) {
            throw new \LogicException('Collection is not used.');
        }

        if ($key[4]) {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test5';
            $data = new $class();
            $data->$property = new \ArrayObject([
                new \DateTime('2021-01-01'),
                new \DateTime('2022-01-01'),
                new \DateTime('2023-01-01'),
            ]);
        } elseif ($key[3]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test5';
            $data = new $class();
            $data->$property = [
                new \DateTime('2021-01-01'),
                new \DateTime('2022-01-01'),
                new \DateTime('2023-01-01'),
            ];
        } else {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test6';
            $d = new ReflectionClassSetterExpressionTester();
            $d->test = 'test';
            $d->test2 = true;
            $d->test3 = new \DateTime('2021-01-01');  // Converted string to DateTime object
            $data = new $class();
            $data->$property = [$d, clone $d];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertEquals($data->$property, $output->$property);
    }

    protected function assertIsAsignedFunction(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[1]) {
            throw new \LogicException('Function is not used.');
        }

        if (!$key[0]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test0';
            $data = new $class();
            $data->$property = new ReflectionClassSetterExpressionTester();
            $data->$property->test = 'test';
            $data->$property->test2 = true;
            $data->$property->test3 = new \DateTime('2021-01-01');
        } else {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test6';
            $d = new ReflectionClassSetterExpressionTester();
            $d->test = 'test';
            $d->test2 = true;
            $d->test3 = new \DateTime('2021-01-01');
            $data = new $class();
            $data->$property = [$d];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertTrue(isset($function));
        $this->assertTrue(isset($output->$property));
    }

    protected function assertIsAsignedPath(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[2]) {
            throw new \LogicException('Path is not used.');
        }

        if (!$key[0] && !$key[1]) {
            $class = ReflectionClassSetterExpressionTester::class;
            $data = new $class();
            if ($key[3] || $key[4]) {
                $property = 'test3';
                $data->$property = new \DateTime('2021-01-01');
            } else {
                $property = 'test';
                $data->$property = 'test';
            }
        } elseif ($key[1] && !$key[0]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test0';
            $nestedData = new ReflectionClassSetterExpressionTester();
            $nestedData->test = 'test';
            $nestedData->test2 = true;
            $nestedData->test3 = new \DateTime('2021-01-01');
            $data = new $class();
            $data->$property = $nestedData;
        } elseif ($key[0] && $key[3]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test5';
            $data = new $class();
            $data->$property = [
                new \DateTime('2021-01-01'),
                new \DateTime('2022-01-01'),
                new \DateTime('2023-01-01'),
            ];
        } elseif ($key[0]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test6';
            $nestedData = new ReflectionClassSetterExpressionTester();
            $nestedData->test = 'test';
            $nestedData->test2 = true;
            $nestedData->test3 = new \DateTime('2021-01-01');
            $data = new $class();
            $data->$property = [$nestedData];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        $this->assertTrue(isset($path));
    }

    protected function assertIsAsignedSimpleObject(string $key): void
    {
        $key = $this->explodeKey($key);

        if ($key[1]) {
            throw new \LogicException('Function cannot be used for simple object.');
        }

        if (!$key[3]) {
            throw new \LogicException('Simple object is not used.');
        }

        if ($key[0] && !$key[4]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test4';
            $data1 = new ReflectionClassSetterExpressionTester();
            $data1->test = 'test';
            $data1->test2 = true;
            $data1->test3 = new \DateTime('2021-01-01 15:30:00');

            $data2 = new ReflectionClassSetterExpressionTester();
            $data2->test = 'test2';
            $data2->test2 = false;
            $data2->test3 = new \DateTime('2021-01-01 15:30:00');

            $data = new $class();
            $data->$property = new \ArrayObject([$data1, $data2]);

            $instanceOf = \ArrayObject::class;
        } elseif (!$key[0] && !$key[4]) {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test4';
            $data = new $class();
            $data->$property = new \DateTime('2021-01-01 15:30:00');

            $instanceOf = \DateTime::class;
        } elseif ($key[4]) {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test3';
            $data = new $class();
            $data->$property = new \DateTime('2021-01-01');

            $instanceOf = \DateTime::class;
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        if ('string' !== $instanceOf) {
            $this->assertInstanceOf($instanceOf, $output->$property);
        } else {
            $this->assertTrue(is_string($output->$property));
            $this->assertEquals($data->$property, $output->$property);
        }
    }

    protected function assertIsAssignedVarVariable(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[5]) {
            throw new \LogicException('Var variable is not used.');
        }

        if ($key[1]) {
            $class = NestedReflectionClassSetterExpressionTester::class;
            $property = 'test0';
            $nestedData = new ReflectionClassSetterExpressionTester();
            $nestedData->test = 'test';
            $nestedData->test2 = true;
            $nestedData->test3 = new \DateTime('2021-01-01');
            $data = new $class();
            $data->$property = $nestedData;
        } elseif ($key[3] || $key[4]) {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test3';
            $data = new $class();
            $data->$property = new \DateTime('2021-01-01');
        } else {
            $class = ReflectionClassSetterExpressionTester::class;
            $property = 'test';
            $data = new $class();
            $data->$property = 'test';
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        $this->assertTrue(isset($var));
    }

    /** @test */
    public function testSetter000000(): void
    {
        $key = '000000';
        $this->assertIsOutputAsigned($key);
    }

    /** @test */
    public function testSetter000001(): void
    {
        $key = '000001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter000010(): void
    {
        $key = '000010';
        $this->assertIsOutputAsigned($key);
    }

    /** @test */
    public function testSetter000011(): void
    {
        $key = '000011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter000100(): void
    {
        $key = '000100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter000101(): void
    {
        $key = '000101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter000110(): void
    {
        $key = '000110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter000111(): void
    {
        $key = '000111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);

        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter001000(): void
    {
        $key = '001000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testSetter001001(): void
    {
        $key = '001001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter001010(): void
    {
        $key = '001010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testSetter001011(): void
    {
        $key = '001011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter001100(): void
    {
        $key = '001100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter001101(): void
    {
        $key = '001101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter001110(): void
    {
        $key = '001110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter001111(): void
    {
        $key = '001111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);

        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter010000(): void
    {
        $key = '010000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
    }

    /** @test */
    public function testSetter010001(): void
    {
        $key = '010001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter011000(): void
    {
        $key = '011000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testSetter011001(): void
    {
        $key = '011001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter100100(): void
    {
        $key = '100100';
        // $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter100101(): void
    {
        $key = '100101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter100110(): void
    {
        $key = '100110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter100111(): void
    {
        $key = '100111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);

        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter101100(): void
    {
        $key = '101100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter101101(): void
    {
        $key = '101101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter101110(): void
    {
        $key = '101110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testSetter101111(): void
    {
        $key = '101111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);

        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testSetter110000(): void
    {
        $key = '110000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
    }

    /** @test */
    public function testSetter110001(): void
    {
        $key = '110001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
    }

    /** @test */
    public function testSetter111000(): void
    {
        $key = '111000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testSetter111001(): void
    {
        $key = '111001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
    }
}
