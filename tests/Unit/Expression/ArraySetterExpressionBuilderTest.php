<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Unit\Expression;

use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Attribute\SimpleObject;
use PBaszak\DedicatedMapperBundle\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Expression\Builder\FunctionExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Expression\ExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PHPUnit\Framework\TestCase;

class ArraySetterExpressionTester
{
    public string $test;
    public bool $test2;

    #[SimpleObject(deconstructor: 'format', deconstructorArguments: ['Y-m-d'])]
    public ?\DateTime $test3 = null;

    public \DateTime $test4;
}

class NestedArraySetterExpressionTester
{
    public string $test;
    public bool $test2;
    public ArraySetterExpressionTester $test0;

    /** @var ArraySetterExpressionTester[] */
    public ?\ArrayObject $test4;

    /** @var \DateTime[] */
    public array $test5 = [];

    /** @var ArraySetterExpressionTester[] */
    public array $test6 = [];
}

/** @group unit */
class ArraySetterExpressionBuilderTest extends TestCase
{
    private ArrayExpressionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ArrayExpressionBuilder();
    }

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

        if ($hasSimpleObjectDeconstructor && !in_array($property, ['test3'])) {
            throw new \LogicException('Property must be test3.');
        }

        $blueprint = Blueprint::create($class, $isCollection, null);
        $sourceProperty = $blueprint->getProperty($property);
        $targetProperty = clone $sourceProperty;

        $expressionBuilder = (new ExpressionBuilder(
            $blueprint,
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
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

        if ($hasPathUsed) {
            $initialExpression = "\$path = 'root';\n";
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
                $isCollection,
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
            $class = ArraySetterExpressionTester::class;
            $property = 'test';
            $data = [
                $property => 'test',
            ];
        } elseif (!$key[1] && ($key[3] || $key[4])) {
            $class = ArraySetterExpressionTester::class;
            $property = 'test3';
            $data = [
                $property => '2021-01-01',
            ];
        } elseif ($key[1] && !$key[0]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test0';
            $data = [
                $property => [
                    'test' => 'test',
                    'test2' => true,
                    'test3' => '2021-01-01',
                ],
            ];
        } elseif ($key[0]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test6';
            $data = [
                $property => [
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01',
                    ],
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01',
                    ],
                ],
            ];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertEquals($data[$property], $output[$property]);
    }

    protected function assertIsAsignedCollection(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[0]) {
            throw new \LogicException('Collection is not used.');
        }

        if ($key[3]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test5';
            $data = [
                $property => [
                    '2021-01-01',
                    '2022-01-01',
                    '2023-01-01',
                ],
            ];
        } else {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test6';
            $data = [
                $property => [
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01',
                    ],
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01',
                    ],
                ],
            ];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertEquals($data[$property], $output[$property]);
    }

    protected function assertIsAsignedFunction(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[1]) {
            throw new \LogicException('Function is not used.');
        }

        if (!$key[0]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test0';
            $data = [
                $property => [
                    'test' => 'test',
                    'test2' => true,
                    'test3' => '2021-01-01',
                ],
            ];
        } else {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test6';
            $data = [
                $property => [
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01',
                    ],
                ],
            ];
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertTrue(isset($function));
        $this->assertTrue(isset($output[$property]));
    }

    protected function assertIsAsignedPath(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[2]) {
            throw new \LogicException('Path is not used.');
        }

        if (!$key[0] && !$key[1]) {
            $class = ArraySetterExpressionTester::class;
            if ($key[3] || $key[4]) {
                $property = 'test3';
                $data = [
                    $property => '2021-01-01',
                ];
            } else {
                $property = 'test';
                $data = [
                    $property => 'test',
                ];
            }
        } elseif ($key[1] && !$key[0]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test0';
            $data = [
                $property => [
                    'test' => 'test',
                    'test2' => true,
                    'test3' => '2021-01-01',
                ],
            ];
        } elseif ($key[0]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test6';
            $data = [
                $property => [
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01',
                    ],
                ],
            ];
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
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test4';
            $data = [
                $property => [
                    [
                        'test' => 'test',
                        'test2' => true,
                        'test3' => '2021-01-01 15:30:00',
                    ],
                    [
                        'test' => 'test2',
                        'test2' => false,
                        'test3' => '2021-01-01 15:30:00',
                    ],
                ],
            ];
            $instanceOf = \ArrayObject::class;
        } elseif (!$key[0] && !$key[4]) {
            $class = ArraySetterExpressionTester::class;
            $property = 'test4';
            $data = [
                $property => '2021-01-01 15:30:00',
            ];
            $instanceOf = \DateTime::class;
        } elseif ($key[4]) {
            $class = ArraySetterExpressionTester::class;
            $property = 'test3';
            $data = [
                $property => '2021-01-01',
            ];
            $instanceOf = 'string';
        }

        $args = array_merge($key, [$class, $property]);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        if ('string' !== $instanceOf) {
            $this->assertInstanceOf($instanceOf, $output[$property]);
        } else {
            $this->assertTrue(is_string($output[$property]));
            $this->assertEquals($data[$property], $output[$property]);
        }
    }

    protected function assertIsAsignedSimpleObjectDeconstructor(string $key): void
    {
        $key = $this->explodeKey($key);

        if ($key[1]) {
            throw new \LogicException('Function cannot be used for simple object.');
        }

        if (!$key[3]) {
            throw new \LogicException('Simple object is not used.');
        }

        if (!$key[4]) {
            throw new \LogicException('Simple object deconstructor is not used.');
        }

        $data = [
            'test3' => '2021-01-01 15:30:00',
        ];

        $args = array_merge($key, [ArraySetterExpressionTester::class, 'test3']);
        $expression = $this->getExpression(...$args);
        eval($expression);

        if (!isset($output)) {
            throw new \LogicException('Output is not set.');
        }

        $this->assertEquals('2021-01-01', $output['test3']);
    }

    protected function assertIsAssignedVarVariable(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[5]) {
            throw new \LogicException('Var variable is not used.');
        }

        if ($key[1]) {
            $class = NestedArraySetterExpressionTester::class;
            $property = 'test0';
            $data = [
                $property => [
                    'test' => 'test',
                    'test2' => true,
                    'test3' => '2021-01-01',
                ],
            ];
        } elseif ($key[3] || $key[4]) {
            $class = ArraySetterExpressionTester::class;
            $property = 'test3';
            $data = [
                $property => '2021-01-01',
            ];
        } else {
            $class = ArraySetterExpressionTester::class;
            $property = 'test';
            $data = [
                $property => 'test',
            ];
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
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testSetter000111(): void
    {
        $key = '000111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
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
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testSetter001111(): void
    {
        $key = '001111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
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
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testSetter100111(): void
    {
        $key = '100111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
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
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testSetter101111(): void
    {
        $key = '101111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
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
