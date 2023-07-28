<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Tests\Unit\Expression;

use ArrayObject;
use DateTime;
use LogicException;
use PBaszak\DedicatedMapperBundle\Attribute\SimpleObject;
use PBaszak\DedicatedMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PHPUnit\Framework\TestCase;

class ArraySetterExpressionTester
{
    public string $test;
    public bool $test2;

    #[SimpleObject(deconstructor: 'format', deconstructorArguments: ['Y-m-d'])]
    public ?DateTime $test3 = null;
}

class NestedArraySetterExpressionTester
{
    public string $test;
    public bool $test2;
    public ArraySetterExpressionTester $test3;

    /** @var ArraySetterExpressionTester[] */
    public ?ArrayObject $test4 = null;

    /** @var DateTime[] */
    public array $test5 = [];
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
    ): string {
        return '';
    }

    /** @return bool[] */
    protected function explodeKey(string $key): array
    {
        return array_map(fn (string $value) => boolval(intval($value)), str_split($key));
    }

    protected function assertIsOutputAsigned(string $key): void
    {
        $key = $this->explodeKey($key);
    }

    protected function assertIsAsignedCollection(string $key): void
    {
        $key = $this->explodeKey($key);

        $data = [
            [
                'test' => 'test',
                'test2' => true,
            ],
            [
                'test' => 'test2',
                'test2' => false,
            ]
        ];
    }

    protected function assertIsAsignedFunction(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[1]) {
            throw new LogicException('Function is not used.');
        }

        $data = [
            'test' => 'test',
            'test2' => true,
        ];
    }

    protected function assertIsAsignedPath(string $key): void
    {
        $key = $this->explodeKey($key);

        $data = [
            'test' => 'test',
            'test2' => true,
            'test3' => [
                'test' => 'test',
                'test2' => true,
            ]
        ];
    }

    protected function assertIsAsignedSimpleObject(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[3]) {
            throw new LogicException('Simple object is not used.');
        }

        $data = [
            'test' => 'test',
            'test2' => true,
            'test4' => [
                [
                    'test' => 'test',
                    'test2' => true,
                    'test3' => '2021-01-01 15:30:00',
                ],
                [
                    'test' => 'test2',
                    'test2' => false,
                    'test3' => '2021-01-01 15:30:00',
                ]
            ]
        ];


        $expression = $this->getExpression(...$key);
        eval($expression);

        if (!isset($output)) {
            throw new LogicException('Output is not set.');
        }

        $this->assertInstanceOf(ArrayObject::class, $output['test4']);
    }

    protected function assertIsAsignedSimpleObjectDeconstructor(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[3]) {
            throw new LogicException('Simple object is not used.');
        }

        if (!$key[4]) {
            throw new LogicException('Simple object deconstructor is not used.');
        }

        $data = [
            'test' => 'test',
            'test2' => true,
            'test3' => '2021-01-01 15:30:00',
        ];

        $expression = $this->getExpression(...$key);
        eval($expression);

        if (!isset($output)) {
            throw new LogicException('Output is not set.');
        }

        $this->assertEquals('2021-01-01', $output['test3']);
    }

    protected function assertIsAssignedVarVariable(string $key): void
    {
        $key = $this->explodeKey($key);

        if (!$key[5]) {
            throw new LogicException('Var variable is not used.');
        }

        $data = [
            'test' => 'test',
            'test2' => true,
            'test3' => [
                'test' => 'test',
                'test2' => true,
            ]
        ];

        $expression = $this->getExpression(...$key);
        eval($expression);

        $this->assertTrue(isset($var));
    }

    /** @test */
    public function testGetter000000(): void
    {
        $key = '000000';
        $this->assertIsOutputAsigned($key);
    }

    /** @test */
    public function testGetter000001(): void
    {
        $key = '000001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
    }

    /** @test */
    public function testGetter000010(): void
    {
        $key = '000010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
    }

    /** @test */
    public function testGetter000011(): void
    {
        $key = '000011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
    }

    /** @test */
    public function testGetter000100(): void
    {
        $key = '000100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testGetter000101(): void
    {
        $key = '000101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testGetter000110(): void
    {
        $key = '000110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testGetter000111(): void
    {
        $key = '000111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
    }

    /** @test */
    public function testGetter001000(): void
    {
        $key = '001000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001001(): void
    {
        $key = '001001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001010(): void
    {
        $key = '001010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001011(): void
    {
        $key = '001011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001100(): void
    {
        $key = '001100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001101(): void
    {
        $key = '001101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001110(): void
    {
        $key = '001110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter001111(): void
    {
        $key = '001111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
    }

    /** @test */
    public function testGetter010000(): void
    {
        $key = '010000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010001(): void
    {
        $key = '010001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010010(): void
    {
        $key = '010010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010011(): void
    {
        $key = '010011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010100(): void
    {
        $key = '010100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010101(): void
    {
        $key = '010101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010110(): void
    {
        $key = '010110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter010111(): void
    {
        $key = '010111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011000(): void
    {
        $key = '011000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011001(): void
    {
        $key = '011001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011010(): void
    {
        $key = '011010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011011(): void
    {
        $key = '011011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011100(): void
    {
        $key = '011100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011101(): void
    {
        $key = '011101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011110(): void
    {
        $key = '011110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter011111(): void
    {
        $key = '011111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
    }

    /** @test */
    public function testGetter100000(): void
    {
        $key = '100000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100001(): void
    {
        $key = '100001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100010(): void
    {
        $key = '100010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100011(): void
    {
        $key = '100011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100100(): void
    {
        $key = '100100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100101(): void
    {
        $key = '100101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100110(): void
    {
        $key = '100110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter100111(): void
    {
        $key = '100111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101000(): void
    {
        $key = '101000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101001(): void
    {
        $key = '101001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101010(): void
    {
        $key = '101010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101011(): void
    {
        $key = '101011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101100(): void
    {
        $key = '101100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101101(): void
    {
        $key = '101101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101110(): void
    {
        $key = '101110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter101111(): void
    {
        $key = '101111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110000(): void
    {
        $key = '110000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110001(): void
    {
        $key = '110001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110010(): void
    {
        $key = '110010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110011(): void
    {
        $key = '110011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110100(): void
    {
        $key = '110100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110101(): void
    {
        $key = '110101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110110(): void
    {
        $key = '110110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter110111(): void
    {
        $key = '110111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111000(): void
    {
        $key = '111000';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111001(): void
    {
        $key = '111001';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111010(): void
    {
        $key = '111010';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111011(): void
    {
        $key = '111011';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111100(): void
    {
        $key = '111100';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111101(): void
    {
        $key = '111101';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111110(): void
    {
        $key = '111110';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }

    /** @test */
    public function testGetter111111(): void
    {
        $key = '111111';
        $this->assertIsOutputAsigned($key);
        $this->assertIsAsignedCollection($key);
        $this->assertIsAsignedFunction($key);
        $this->assertIsAsignedPath($key);
        $this->assertIsAsignedSimpleObject($key);
        $this->assertIsAsignedSimpleObjectDeconstructor($key);
        $this->assertIsAssignedVarVariable($key);
    }
}
