<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Performance;

use PBaszak\DedicatedMapper\Contract\MapperServiceInterface;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SimpleData
{
    public string $name;
}

/** @group performance */
class SymfonySerializerComparisonTest extends KernelTestCase
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
        system('rm -rf -- '.escapeshellarg(__DIR__.'/../../var/mapper'), $retval);
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $mapper = self::getContainer()->get(MapperServiceInterface::class);
        $serializerDummyObject = $serializer->denormalize($this->dummy, Dummy::class);
        $mapperDummyObject = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

        $this->assertEquals($serializerDummyObject, $mapperDummyObject);
    }

    /**
     * @test
     */
    public function symfonySerializerBuildAndUseComparisonTest(): void
    {
        $time = (object) [
            'symfonySerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $timeStart = microtime(true);
            $serializer = self::getContainer()->get(SerializerInterface::class);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;

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

        /* Mapper is from 5 to 30 times faster than Symfony Serializer */
        $this->assertLessThan($comparison->symfonySerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->symfonySerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->symfonySerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mBuild and use comparison test:\033[0m".PHP_EOL,
        "\033[0;34mSymfony Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->symfonySerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->symfonySerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->symfonySerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }

    /**
     * @test
     */
    public function symfonySerializerUseComparisonTest(): void
    {
        $time = (object) [
            'symfonySerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $serializer = self::getContainer()->get(SerializerInterface::class);
            $timeStart = microtime(true);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;

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

        /* Mapper is from 5 to 30 times faster than Symfony Serializer */
        $this->assertLessThan($comparison->symfonySerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->symfonySerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->symfonySerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mJust use one time comparison test:\033[0m".PHP_EOL,
        "\033[0;34mSymfony Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->symfonySerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->symfonySerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->symfonySerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }

    /**
     * @test
     */
    public function symfonySerializerSecondUseComparisonTest(): void
    {
        $time = (object) [
            'symfonySerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $serializer = self::getContainer()->get(SerializerInterface::class);
            $mapper = self::getContainer()->get(MapperServiceInterface::class);
            $serializer->denormalize($this->dummy, Dummy::class);
            $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

            $timeStart = microtime(true);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;

            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
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

        /* Mapper is from 5 to 30 times faster than Symfony Serializer */
        $this->assertLessThan($comparison->symfonySerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->symfonySerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->symfonySerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mSecond use comparison test (same data):\033[0m".PHP_EOL,
        "\033[0;34mSymfony Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->symfonySerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->symfonySerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->symfonySerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }

    /**
     * @test
     */
    public function symfonySerializerSecondUseComparisonButWithDifferentBlueprintsTest(): void
    {
        $time = (object) [
            'symfonySerializer' => [],
            'mapper' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            self::ensureKernelShutdown();
            self::bootKernel();
            $serializer = self::getContainer()->get(SerializerInterface::class);
            $mapper = self::getContainer()->get(MapperServiceInterface::class);
            $serializer->denormalize(['name' => 'test'], SimpleData::class);
            $mapper->map(['name' => 'test'], SimpleData::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());

            $timeStart = microtime(true);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;

            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder());
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

        /* Mapper is from 5 to 30 times faster than Symfony Serializer */
        $this->assertLessThan($comparison->symfonySerializer->avg, $comparison->mapper->avg);
        $this->assertLessThan($comparison->symfonySerializer->min, $comparison->mapper->min);
        $this->assertLessThan($comparison->symfonySerializer->max, $comparison->mapper->max);

        /* report */
        echo PHP_EOL."\033[1;34mSecond use comparison test (different data):\033[0m".PHP_EOL,
        "\033[0;34mSymfony Serializer:\033[0m".PHP_EOL,
        'avg: '.($savg = $comparison->symfonySerializer->avg).' s'.PHP_EOL,
        'min: '.($smin = $comparison->symfonySerializer->min).' s'.PHP_EOL,
        'max: '.($smax = $comparison->symfonySerializer->max).' s'.PHP_EOL,
        "\033[0;34mMapper:\033[0m".PHP_EOL,
        'avg: '.($mavg = $comparison->mapper->avg).' s ('.round($savg / $mavg, 2).' times faster)'.PHP_EOL,
        'min: '.($mmin = $comparison->mapper->min).' s ('.round($smin / $mmin, 2).' times faster)'.PHP_EOL,
        'max: '.($mmax = $comparison->mapper->max).' s ('.round($smax / $mmax, 2).' times faster)'.PHP_EOL,
        PHP_EOL;
    }
}
