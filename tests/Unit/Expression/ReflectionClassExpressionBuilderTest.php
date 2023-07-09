<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Builder\DefaultExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Tests\assets\Dummy;
use PHPUnit\Framework\TestCase;

/** @group unit */
class ReflectionClassExpressionBuilderTest extends TestCase
{
    /** @test */
    public function shouldMapArrayIntoClassObject(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ArrayExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $dummy = json_decode(file_get_contents(__DIR__.'/../../assets/dummy.json'), true);
        $mappedDummy = $mapper($dummy);

        $this->assertInstanceOf(Dummy::class, $mappedDummy);
        $this->assertEquals($dummy['id'], $mappedDummy->id);
        $this->assertEquals($dummy['name'], $mappedDummy->name);
        $this->assertEquals($dummy['description'], $mappedDummy->description);
        $this->assertEquals($dummy['_embedded']['page'], $mappedDummy->_embedded->page);
        $this->assertEquals($dummy['_embedded']['pageSize'], $mappedDummy->_embedded->pageSize);
        $this->assertEquals($dummy['_embedded']['total'], $mappedDummy->_embedded->total);

        $this->assertEquals($dummy['_embedded']['items'][0]['id'], $mappedDummy->_embedded->items[0]->id);
        $this->assertEquals($dummy['_embedded']['items'][0]['name'], $mappedDummy->_embedded->items[0]->name);
        $this->assertEquals($dummy['_embedded']['items'][0]['description'], $mappedDummy->_embedded->items[0]->description);
        $this->assertEquals($dummy['_embedded']['items'][0]['price'], $mappedDummy->_embedded->items[0]->price);
        $this->assertEquals($dummy['_embedded']['items'][0]['currency'], $mappedDummy->_embedded->items[0]->currency);
        $this->assertEquals($dummy['_embedded']['items'][0]['quantity'], $mappedDummy->_embedded->items[0]->quantity);
        $this->assertEquals($dummy['_embedded']['items'][0]['type'], $mappedDummy->_embedded->items[0]->type);
        $this->assertEquals($dummy['_embedded']['items'][0]['category'], $mappedDummy->_embedded->items[0]->category);
        $this->assertEquals($dummy['_embedded']['items'][0]['vat'], $mappedDummy->_embedded->items[0]->vat);
        $this->assertEquals($dummy['_embedded']['items'][0]['metadata']['test'], $mappedDummy->_embedded->items[0]->metadata->test);
        $this->assertEquals($dummy['_embedded']['items'][0]['metadata']['test2'], $mappedDummy->_embedded->items[0]->metadata->test2);
        $this->assertEquals((new \DateTime($dummy['_embedded']['items'][0]['created_at']))->format('Y-m-d H:i:s'), $mappedDummy->_embedded->items[0]->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals((new \DateTime($dummy['_embedded']['items'][0]['updated_at']))->format('Y-m-d H:i:s'), $mappedDummy->_embedded->items[0]->updated_at->format('Y-m-d H:i:s'));
        $this->assertEquals($dummy['_embedded']['items'][0]['availableActions'], $mappedDummy->_embedded->items[0]->availableActions);

        $this->assertEquals($dummy['_embedded']['items'][1]['id'], $mappedDummy->_embedded->items[1]->id);
        $this->assertEquals($dummy['_embedded']['items'][1]['name'], $mappedDummy->_embedded->items[1]->name);
        $this->assertEquals($dummy['_embedded']['items'][1]['description'], $mappedDummy->_embedded->items[1]->description);
        $this->assertEquals($dummy['_embedded']['items'][1]['price'], $mappedDummy->_embedded->items[1]->price);
        $this->assertEquals($dummy['_embedded']['items'][1]['currency'], $mappedDummy->_embedded->items[1]->currency);
        $this->assertEquals($dummy['_embedded']['items'][1]['quantity'], $mappedDummy->_embedded->items[1]->quantity);
        $this->assertEquals($dummy['_embedded']['items'][1]['type'], $mappedDummy->_embedded->items[1]->type);
        $this->assertEquals($dummy['_embedded']['items'][1]['category'], $mappedDummy->_embedded->items[1]->category);
        $this->assertEquals($dummy['_embedded']['items'][1]['vat'], $mappedDummy->_embedded->items[1]->vat);
        $this->assertEquals($dummy['_embedded']['items'][1]['metadata']['test'], $mappedDummy->_embedded->items[1]->metadata->test);
        $this->assertEquals($dummy['_embedded']['items'][1]['metadata']['test2'], $mappedDummy->_embedded->items[1]->metadata->test2);
        $this->assertEquals((new \DateTime($dummy['_embedded']['items'][1]['created_at']))->format('Y-m-d H:i:s'), $mappedDummy->_embedded->items[1]->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals((new \DateTime($dummy['_embedded']['items'][1]['updated_at']))->format('Y-m-d H:i:s'), $mappedDummy->_embedded->items[1]->updated_at->format('Y-m-d H:i:s'));
        $this->assertEquals($dummy['_embedded']['items'][1]['availableActions'], $mappedDummy->_embedded->items[1]->availableActions);

        $this->assertArrayNotHasKey(2, $mappedDummy->_embedded->items);
    }

    /** @test */
    public function shouldClassObjectIntoArray(): void
    {
        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ArrayExpressionBuilder(),
            new ReflectionClassExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $dummy = json_decode(file_get_contents(__DIR__.'/../../assets/dummy.json'), true);
        $mappedDummy = $mapper($dummy);

        $expressionBuilder = new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ReflectionClassExpressionBuilder(),
            new ArrayExpressionBuilder(),
            new DefaultExpressionBuilder(),
            new DefaultExpressionBuilder(),
        );

        $mapper = $expressionBuilder->createExpression(true)->getMapper();

        $arrayDummy = $mapper($mappedDummy);

        $this->assertIsArray($arrayDummy);

        foreach ($arrayDummy['_embedded']['items'] as &$item) {
            $item['created_at'] = $item['created_at']->format(\DateTime::ATOM);
            $item['updated_at'] = $item['updated_at']->format(\DateTime::ATOM);
        }

        $this->assertEquals($dummy, $arrayDummy);
    }
}
