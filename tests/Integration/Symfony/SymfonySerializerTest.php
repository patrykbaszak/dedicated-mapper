<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Integration\Symfony;

use PBaszak\DedicatedMapper\Expression\Builder\AnonymousObjectExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\FunctionExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\ExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Modificator\Symfony\SymfonySerializer;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

/** @group integration */
class SymfonySerializerTest extends TestCase
{
    /** @test */
    public function shouldMapOnlyPropertiesWithGroups(): void
    {
        $data = require __DIR__.'/../../assets/DummyByFormats.php';
        unset(
            $data[AnonymousObjectExpressionBuilder::class]->description,
            $data[AnonymousObjectExpressionBuilder::class]->_embedded->items
        );

        $source = $data[ArrayExpressionBuilder::class];
        $expectedTarget = $data[AnonymousObjectExpressionBuilder::class];

        $target = (new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ArrayExpressionBuilder(),
            new AnonymousObjectExpressionBuilder(),
            new FunctionExpressionBuilder(),
        ))->applyModificators([new SymfonySerializer(['test'])])
            ->build(true)
            ->getMapper()
            ->map($source);

        $this->assertEquals($expectedTarget, $target);
    }

    /** @test */
    public function shouldMapOnlyPropertiesWhichAreNotIgnored(): void
    {
        $data = require __DIR__.'/../../assets/DummyByFormats.php';
        unset(
            $data[AnonymousObjectExpressionBuilder::class]->_embedded->items[0]->name,
            $data[AnonymousObjectExpressionBuilder::class]->_embedded->items[1]->name
        );

        $source = $data[ArrayExpressionBuilder::class];
        $expectedTarget = $data[AnonymousObjectExpressionBuilder::class];

        $target = (new ExpressionBuilder(
            Blueprint::create(Dummy::class, false),
            new ArrayExpressionBuilder(),
            new AnonymousObjectExpressionBuilder(),
            new FunctionExpressionBuilder(),
        ))->applyModificators([new SymfonySerializer()])
            ->build(true)
            ->getMapper()
            ->map($source);

        $this->assertEquals($expectedTarget, $target);
    }

    /** @test */
    public function shouldUseSerializedNameForSerializedData(): void
    {
        $id = Uuid::v4()->toRfc4122();
        $data = [
            ArrayExpressionBuilder::class => [
                'test' => $id,
            ],
            AnonymousObjectExpressionBuilder::class => (object) [
                'test' => $id,
            ],
            ReflectionClassExpressionBuilder::class => new SymfonySerializerTestedClass($id),
        ];

        for ($i = 0; $i < count($data) - 1; ++$i) {
            for ($j = $i; $j < count($data); ++$j) {
                $blueprint = Blueprint::create(SymfonySerializerTestedClass::class);
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
                    false
                ))->applyModificators([new SymfonySerializer()])->build(true)->getMapper();

                $source = $data[$sourceClass];
                $expectedTarget = $data[$targetClass];

                $target = $mapper($source);
                self::assertEquals($expectedTarget, $target);
            }
        }
    }
}

class SymfonySerializerTestedClass
{
    public function __construct(
        #[SerializedName('test')]
        public string $id,
    ) {
    }
}
