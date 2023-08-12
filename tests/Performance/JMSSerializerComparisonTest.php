<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Performance;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\Serializer;
use PBaszak\DedicatedMapper\Contract\MapperServiceInterface;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleDataForJMSSerializer
{
    public string $name;
}

/** @group performance */
class JMSSerializerComparisonTest extends KernelTestCase
{
    private array $dummy;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->dummy = json_decode(file_get_contents(__DIR__.'/../assets/dummy.json'), true);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     */
    public function checkSameMappingResultsBeforeComparison(): void
    {
        $serializer = self::getContainer()->get(ArrayTransformerInterface::class);
        $mapper = self::getContainer()->get(MapperServiceInterface::class);
        $serializerDummyObject = $serializer->fromArray($this->dummy, Dummy::class);
        $mapperDummyObject = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

        $this->assertEquals($serializerDummyObject, $mapperDummyObject);
    }

    /**
     * @test
     */
    public function jmsSerializerBuildAndUseComparisonTest(): void
    {
        $time = (object) [
            'jmsSerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $timeStart = microtime(true);
            $serializer = self::getContainer()->get(ArrayTransformerInterface::class);
            $output = $serializer->fromArray($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->jmsSerializer[] = $timeEnd - $timeStart;

            self::ensureKernelShutdown();
            self::bootKernel();
            $timeStart = microtime(true);
            $mapper = self::getContainer()->get(MapperServiceInterface::class);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
            $timeEnd = microtime(true);
            $time->mapper[] = $timeEnd - $timeStart;

            (new \ReflectionProperty($mapper, 'mappers'))->setValue($mapper, []);
        }

        $comparison = (object) [
            'jmsSerializer' => (object) [
                'avg' => array_sum($time->jmsSerializer) / count($time->jmsSerializer),
                'min' => min($time->jmsSerializer),
                'max' => max($time->jmsSerializer),
            ],
            'mapper' => (object) [
                'avg' => array_sum($time->mapper) / count($time->mapper),
                'min' => min($time->mapper),
                'max' => max($time->mapper),
            ],
        ];

        /* Mapper is from 5 to 30 times faster than JMS Serializer */
        $this->assertLessThan($comparison->jmsSerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->jmsSerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->jmsSerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mBuild and use comparison test:\033[0m".PHP_EOL,
        "\033[0;34mJMS Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->jmsSerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->jmsSerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->jmsSerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }

    /**
     * @test
     */
    public function jmsSerializerUseComparisonTest(): void
    {
        $time = (object) [
            'jmsSerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $serializer = self::getContainer()->get(ArrayTransformerInterface::class);
            $timeStart = microtime(true);
            $output = $serializer->fromArray($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->jmsSerializer[] = $timeEnd - $timeStart;

            self::ensureKernelShutdown();
            self::bootKernel();
            $mapper = self::getContainer()->get(MapperServiceInterface::class);
            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
            $timeEnd = microtime(true);
            $time->mapper[] = $timeEnd - $timeStart;

            (new \ReflectionProperty($mapper, 'mappers'))->setValue($mapper, []);
        }

        $comparison = (object) [
            'jmsSerializer' => (object) [
                'avg' => array_sum($time->jmsSerializer) / count($time->jmsSerializer),
                'min' => min($time->jmsSerializer),
                'max' => max($time->jmsSerializer),
            ],
            'mapper' => (object) [
                'avg' => array_sum($time->mapper) / count($time->mapper),
                'min' => min($time->mapper),
                'max' => max($time->mapper),
            ],
        ];

        /* Mapper is from 5 to 30 times faster than JMS Serializer */
        $this->assertLessThan($comparison->jmsSerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->jmsSerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->jmsSerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mJust use one time comparison test:\033[0m".PHP_EOL,
        "\033[0;34mJMS Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->jmsSerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->jmsSerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->jmsSerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }

    /**
     * @test
     */
    public function jmsSerializerSecondUseComparisonTest(): void
    {
        $time = (object) [
            'jmsSerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $serializer = self::getContainer()->get(ArrayTransformerInterface::class);
            $mapper = self::getContainer()->get(MapperServiceInterface::class);
            $serializer->fromArray($this->dummy, Dummy::class);
            $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

            $timeStart = microtime(true);
            $output = $serializer->fromArray($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->jmsSerializer[] = $timeEnd - $timeStart;

            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
            $timeEnd = microtime(true);
            $time->mapper[] = $timeEnd - $timeStart;
        }

        $comparison = (object) [
            'jmsSerializer' => (object) [
                'avg' => array_sum($time->jmsSerializer) / count($time->jmsSerializer),
                'min' => min($time->jmsSerializer),
                'max' => max($time->jmsSerializer),
            ],
            'mapper' => (object) [
                'avg' => array_sum($time->mapper) / count($time->mapper),
                'min' => min($time->mapper),
                'max' => max($time->mapper),
            ],
        ];

        /* Mapper is from 5 to 30 times faster than JMS Serializer */
        $this->assertLessThan($comparison->jmsSerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->jmsSerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->jmsSerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mSecond use comparison test (same data):\033[0m".PHP_EOL,
        "\033[0;34mJMS Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->jmsSerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->jmsSerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->jmsSerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }

    /**
     * @test
     */
    public function jmsSerializerSecondUseComparisonButWithDifferentBlueprintsTest(): void
    {
        $time = (object) [
            'jmsSerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $serializer = self::getContainer()->get(ArrayTransformerInterface::class);
            $mapper = self::getContainer()->get(MapperServiceInterface::class);
            $serializer->fromArray(['name' => 'test'], SimpleDataForJMSSerializer::class);
            $mapper->map(['name' => 'test'], SimpleDataForJMSSerializer::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

            $timeStart = microtime(true);
            $output = $serializer->fromArray($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->jmsSerializer[] = $timeEnd - $timeStart;

            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
            $timeEnd = microtime(true);
            $time->mapper[] = $timeEnd - $timeStart;
        }

        $comparison = (object) [
            'jmsSerializer' => (object) [
                'avg' => array_sum($time->jmsSerializer) / count($time->jmsSerializer),
                'min' => min($time->jmsSerializer),
                'max' => max($time->jmsSerializer),
            ],
            'mapper' => (object) [
                'avg' => array_sum($time->mapper) / count($time->mapper),
                'min' => min($time->mapper),
                'max' => max($time->mapper),
            ],
        ];

        /* Mapper is from 5 to 30 times faster than JMS Serializer */
        $this->assertLessThan($comparison->jmsSerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->jmsSerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->jmsSerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mSecond use comparison test (different data):\033[0m".PHP_EOL,
        "\033[0;34mJMS Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->jmsSerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->jmsSerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->jmsSerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }
}
