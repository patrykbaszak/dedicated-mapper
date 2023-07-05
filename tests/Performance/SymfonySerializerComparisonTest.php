<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Tests\Performance;

use PBaszak\MessengerMapperBundle\Contract\MapperServiceInterface;
use PBaszak\MessengerMapperBundle\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\MapperService;
use PBaszak\MessengerMapperBundle\Tests\assets\Dummy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/** @group performance */
class SymfonySerializerComparisonTest extends KernelTestCase
{
    private Serializer $serializer;
    private MapperService $mapper;

    private array $dummy;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mapper = self::getContainer()->get(MapperServiceInterface::class);

        $this->dummy = json_decode(file_get_contents(__DIR__.'/../assets/dummy.json'), true);
    }

    /** @test */
    public function symfonySerializerComparisonTest(): void
    {
        $serializerDummyObject = $this->serializer->denormalize($this->dummy, Dummy::class);
        $mapperDummyObject = $this->mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

        $this->assertEquals($serializerDummyObject, $mapperDummyObject);

        $time = (object) ['symfonySerializer' => [], 'mapper' => []];
        for ($i = 0; $i < 1000; ++$i) {
            $timeStart = microtime(true);
            $output = $this->serializer->denormalize($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;

            $timeStart = microtime(true);
            $output = $this->mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
            $timeEnd = microtime(true);
            $time->mapper[] = $timeEnd - $timeStart;
        }

        $comparison = (object) [
            'symfonySerializer' => (object) [
                    'avg' => array_sum($time->symfonySerializer) / count($time->symfonySerializer),
                    'min' => min($time->symfonySerializer),
                    'max' => max($time->symfonySerializer),
                ],
            'mapper' => (object) [
                    'avg' => array_sum($time->mapper) / count($time->mapper),
                    'min' => min($time->mapper),
                    'max' => max($time->mapper),
                ],
        ];

        /* Mapper is from 15 to 30 times faster than Symfony Serializer */
        $this->assertLessThan($comparison->symfonySerializer->avg / 25, $comparison->mapper->avg);
        $this->assertLessThan($comparison->symfonySerializer->min / 25, $comparison->mapper->min);
        $this->assertLessThan($comparison->symfonySerializer->max / 15, $comparison->mapper->max);
    }
}
