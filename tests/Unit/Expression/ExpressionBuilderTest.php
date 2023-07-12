<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Unit\Expression;

use PBaszak\MessengerMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Builder\DefaultExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\ExpressionBuilder;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Tests\assets\Dummy;
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
    public \DateTime $time;

    /** @var SimpleClass[] */
    public \ArrayObject $properties;
}

/** @group unit */
class ExpressionBuilderTest extends TestCase
{
    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function shouldCreateSimpleExpressionForOneProperty(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleClass::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $this->assertEquals(
            'return function (array $data): array {
                $output = [];
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
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $this->assertEquals(
            [
                'property' => 'test',
            ],
            $mapper(['property' => 'test'])
        );
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function shouldCreateSimpleExpressionForOneCollectionProperty(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleCollectionClass::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();
        $function = eval($mapper->toString());

        $this->assertEqualsIgnoringCase(
            'return function (array $data): array {
                $output = [];
$var_78c4d00f = function (array $data, string $path = \'\'): array {
                $output = [];
$output[\'property\'] = $data[\'property\'];

                return $output;
            };
$var_16ff5206 = [];
            foreach ($data[\'properties\'] as $index => $var_cfd9110b) {
                $var_16ff5206[] = $var_78c4d00f($var_cfd9110b, implode(\'.\', array_filter([$path ?? null, $index], fn ($p) => null !== $p)));
            }
            $output[\'properties\'] = $var_16ff5206;

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
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $collection = [
            'properties' => [
                ['property' => 'test'],
                ['property' => 'test2'],
            ],
        ];

        $this->assertEquals(
            $collection,
            $mapper($collection)
        );
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function shouldCreateSimpleExpressionForOneCollectionPropertyAndSimpleObject(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(SimpleClassWithSimpleObject::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();
        $function = eval($mapper->toString());

        $this->assertEqualsIgnoringCase(
            'return function (array $data): array {
                $output = [];
$output[\'time\'] = ($x = $data[\'time\']) instanceof DateTime ? $x : new DateTime($x);
$var_78c4d00f = function (array $data, string $path = \'\'): array {
                $output = [];
$output[\'property\'] = $data[\'property\'];

                return $output;
            };
$var_16ff5206 = [];
            foreach ($data[\'properties\'] as $index => $var_cfd9110b) {
                $var_16ff5206[] = $var_78c4d00f($var_cfd9110b, implode(\'.\', array_filter([$path ?? null, $index], fn ($p) => null !== $p)));
            }
            $output[\'properties\'] = new ArrayObject($var_16ff5206);

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
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $collection = [
            'time' => (new \DateTime())->format(\DateTime::ATOM),
            'properties' => [
                ['property' => 'test'],
                ['property' => 'test2'],
            ],
        ];

        $this->assertEquals(
            [
                'time' => new \DateTime($collection['time']),
                'properties' => new \ArrayObject($collection['properties']),
            ],
            $mapper($collection)
        );
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function shouldCreateExpressionForDummyBlueprint(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $function = eval($mapper->toString());

        $this->assertEqualsIgnoringCase(
            'return function (array $data): array {
                $output = [];
$output[\'id\'] = $data[\'id\'];
$output[\'name\'] = $data[\'name\'];
$output[\'description\'] = $data[\'description\'];
$var_6e52c0f5 = function (array $data, string $path = \'\'): array {
                $output = [];
$output[\'page\'] = $data[\'page\'];
$output[\'pageSize\'] = $data[\'pageSize\'];
$output[\'total\'] = $data[\'total\'];
$var_ccdcc381 = function (array $data, string $path = \'\'): array {
                $output = [];
$output[\'id\'] = $data[\'id\'];
$output[\'name\'] = $data[\'name\'];
$output[\'description\'] = $data[\'description\'];
$output[\'price\'] = $data[\'price\'];
$output[\'currency\'] = $data[\'currency\'];
$output[\'quantity\'] = $data[\'quantity\'];
$output[\'type\'] = $data[\'type\'];
$output[\'category\'] = $data[\'category\'];
$output[\'vat\'] = $data[\'vat\'];
$var_5626685c = function (array $data, string $path = \'\'): array {
                $output = [];
$output[\'test\'] = $data[\'test\'];
$output[\'test2\'] = $data[\'test2\'];

                return $output;
            };
$output[\'metadata\'] = $var_5626685c($data[\'metadata\'], implode(\'.\', array_filter([$path ?? null, \'metadata\'], fn ($p) => null !== $p)));
$output[\'created_at\'] = ($x = $data[\'created_at\']) instanceof DateTime ? $x : new DateTime($x);
$output[\'updated_at\'] = ($x = $data[\'updated_at\']) instanceof DateTime ? $x : new DateTime($x);
$output[\'availableActions\'] = $data[\'availableActions\'];

                return $output;
            };
$var_a2e74188 = [];
            foreach ($data[\'items\'] as $index => $var_15fa808c) {
                $var_a2e74188[] = $var_ccdcc381($var_15fa808c, implode(\'.\', array_filter([$path ?? null, $index], fn ($p) => null !== $p)));
            }
            $output[\'items\'] = $var_a2e74188;

                return $output;
            };
$output[\'_embedded\'] = $var_6e52c0f5($data[\'_embedded\'], implode(\'.\', array_filter([$path ?? null, \'_embedded\'], fn ($p) => null !== $p)));

                return $output;
            };',
            $mapper->toString()
        );
    }

    /** @test */
    public function shouldMapDummyObject(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ArrayExpressionBuilder(),
            new ArrayExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $dummy = json_decode(file_get_contents(__DIR__.'/../../assets/dummy.json'), true);
        $mappedDummy = $mapper($dummy);
        foreach ($dummy['_embedded']['items'] as &$item) {
            $item['created_at'] = (new \DateTime($item['created_at']));
            $item['updated_at'] = (new \DateTime($item['updated_at']));
        }

        $this->assertEquals(
            $dummy,
            $mappedDummy
        );
    }
}
