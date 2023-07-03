<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Unit\Expression;

use ArrayObject;
use DateTime;
use PBaszak\MessengerMapperBundle\Expression\ArrayExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\ExpressionBuilder;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PHPUnit\Framework\TestCase;

class SimpleClass
{
    public string $property;
}

class SimpleCollectionClass
{
    /** @var SimpleClass[] */
    public array $properties;
}

class SimpleClassWithSimpleObject
{
    public DateTime $time;
    
    /** @var SimpleClass[] */
    public ArrayObject $properties;
}

/** @group unit */
class ExpressionBuilderTest extends TestCase
{
    /** 
     * @test 
     * @runInSeparateProcess
     */
    public function shouldCreateSimpleExpressionForOneProperty(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleClass::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        );

        $expressionBuilder->createExpression();
        $mapper = $expressionBuilder->getMapper();

        $this->assertEquals(
            'return function (mixed $data)  {
                $output[\'property\'] = $data[\'property\'];
                
                return $output;
            };',
            $mapper->toString()
        );
    }

    /** @test */
    public function shouldMapSimpleClass(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleClass::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        );

        $expressionBuilder->createExpression();
        $mapper = $expressionBuilder->getMapper();

        $this->assertEquals(
            [
                'property' => 'test',
            ],
            $mapper(['property' => 'test'])
        );
    }

    /** 
     * @test
     * @runInSeparateProcess
     */
    public function shouldCreateSimpleExpressionForOneCollectionProperty(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleCollectionClass::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        );

        $expressionBuilder->createExpression();
        $mapper = $expressionBuilder->getMapper();

        $this->assertEqualsIgnoringCase(
            'return function (mixed $data)  {
                $var_c9bd5b35=function (mixed $data)  {
                $output[\'property\'] = $data[\'property\'];
                
                return $output;
            };$var_109b1838 = [];
            foreach ($data[\'properties\'] as $var_a786d93c) {
                $var_109b1838[] = $var_c9bd5b35($var_a786d93c);
            }
            $output[\'properties\'] = $var_109b1838;
                
                return $output;
            };',
            $mapper->toString()
        );
    }

    /** @test */
    public function shouldMapSimpleCollectionClass(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleCollectionClass::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        );

        $expressionBuilder->createExpression();
        $mapper = $expressionBuilder->getMapper();

        $collection = [
            'properties' => [
                ['property' => 'test'],
                ['property' => 'test2'],
            ]
        ];

        $this->assertEquals(
            $collection,
            $mapper($collection)
        );
    }

    /** 
     * @test
     * @runInSeparateProcess
     */
    public function shouldCreateSimpleExpressionForOneCollectionPropertyAndSimpleObject(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleClassWithSimpleObject::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        );

        $expressionBuilder->createExpression();
        $mapper = $expressionBuilder->getMapper();

        $this->assertEqualsIgnoringCase(
            'return function (mixed $data)  {
                $output[\'time\'] = new DateTime($data[\'time\']);$var_3626cd7f=function (mixed $data)  {
                $output[\'property\'] = $data[\'property\'];
                
                return $output;
            };$var_ef008e72 = [];
            foreach ($data[\'properties\'] as $var_581d4f76) {
                $var_ef008e72[] = $var_3626cd7f($var_581d4f76);
            }
            $output[\'properties\'] = new ArrayObject($var_ef008e72);
                
                return $output;
            };',
            $mapper->toString()
        );
    } 

    /** @test */
    public function shouldMapSimpleClassWithSimpleObject(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleClassWithSimpleObject::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
        );

        $expressionBuilder->createExpression();
        $mapper = $expressionBuilder->getMapper();

        $collection = [
            'time' => (new DateTime())->format(DateTime::ATOM),
            'properties' => [
                ['property' => 'test'],
                ['property' => 'test2'],
            ]
        ];

        $this->assertEquals(
            [
                'time' => new DateTime($collection['time']),
                'properties' => new ArrayObject($collection['properties']),
            ],
            $mapper($collection)
        );
    }
}
