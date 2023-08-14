<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Tests\Performance;

use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Modificator\Symfony\SymfonyValidator;
use PBaszak\DedicatedMapper\Tests\assets\Dummy;
use PBaszak\DedicatedMapper\ValidatedMapperService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SimpleDataWithValidation
{
    public string $name;
}

/** @group performance */
class SymfonySerializerWithValidationComparisonTest extends KernelTestCase
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
    public function checkSameMappingAndValidationResultsBeforeComparison(): void
    {
        /** @var Serializer $serializer */
        $serializer = self::getContainer()->get('serializer');
        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        /** @var ValidatedMapperService $mapper */
        $mapper = self::getContainer()->get('pbaszak.dedicated_mapper.validated');

        $serializerDummyObject = $serializer->denormalize($this->dummy, Dummy::class);
        $validationResults = $validator->validate($serializerDummyObject);
        $mapperDummyObject = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
        $mapperValidationResults = $mapper->getLastValidationResult();

        $this->assertEquals($serializerDummyObject, $mapperDummyObject);
        $this->assertEquals($validationResults, $mapperValidationResults);
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
            /** @var Serializer $serializer */
            $serializer = self::getContainer()->get('serializer');
            /** @var ValidatorInterface $validator */
            $validator = self::getContainer()->get('validator');
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $validationResults = $validator->validate($output);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;
            unset($output, $validationResults);

            self::ensureKernelShutdown();
            self::bootKernel();
            $timeStart = microtime(true);
            /** @var ValidatedMapperService $mapper */
            $mapper = self::getContainer()->get('pbaszak.dedicated_mapper.validated');
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
            $validationResults = $mapper->getLastValidationResult();
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
        echo PHP_EOL."\033[1;34mBuild, use and validation comparison test:\033[0m".PHP_EOL,
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
            /** @var Serializer $serializer */
            $serializer = self::getContainer()->get('serializer');
            /** @var ValidatorInterface $validator */
            $validator = self::getContainer()->get('validator');
            $timeStart = microtime(true);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $validationResults = $validator->validate($output);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;
            unset($output, $validationResults);

            self::ensureKernelShutdown();
            self::bootKernel();
            /** @var ValidatedMapperService $mapper */
            $mapper = self::getContainer()->get('pbaszak.dedicated_mapper.validated');
            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
            $validationResults = $mapper->getLastValidationResult();
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
        echo PHP_EOL."\033[1;34mJust use and validation one time comparison test:\033[0m".PHP_EOL,
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
            /** @var Serializer $serializer */
            $serializer = self::getContainer()->get('serializer');
            /** @var ValidatorInterface $validator */
            $validator = self::getContainer()->get('validator');
            /** @var ValidatedMapperService $mapper */
            $mapper = self::getContainer()->get('pbaszak.dedicated_mapper.validated');
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $validationResults = $validator->validate($output);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
            $validationResults = $mapper->getLastValidationResult();
            unset($output, $validationResults);

            $timeStart = microtime(true);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $validationResults = $validator->validate($output);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;
            unset($output, $validationResults);

            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
            $validationResults = $mapper->getLastValidationResult();
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
        echo PHP_EOL."\033[1;34mSecond use and validation comparison test (same data):\033[0m".PHP_EOL,
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
            /** @var Serializer $serializer */
            $serializer = self::getContainer()->get('serializer');
            /** @var ValidatorInterface $validator */
            $validator = self::getContainer()->get('validator');
            /** @var ValidatedMapperService $mapper */
            $mapper = self::getContainer()->get('pbaszak.dedicated_mapper.validated');

            $output = $serializer->denormalize(['name' => 'test'], SimpleDataWithValidation::class);
            $validationResults = $validator->validate($output);
            $mapper->map(['name' => 'test'], SimpleDataWithValidation::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
            $validationResults = $mapper->getLastValidationResult();
            unset($output, $validationResults);

            $timeStart = microtime(true);
            $output = $serializer->denormalize($this->dummy, Dummy::class);
            $validationResults = $validator->validate($output);
            $timeEnd = microtime(true);
            $time->symfonySerializer[] = $timeEnd - $timeStart;
            unset($output, $validationResults);

            $timeStart = microtime(true);
            $output = $mapper->map($this->dummy, Dummy::class, new ArrayExpressionBuilder(), new ReflectionClassExpressionBuilder(), modificators: [new SymfonyValidator()], throwValidationFailedException: false);
            $validationResults = $mapper->getLastValidationResult();
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
        echo PHP_EOL."\033[1;34mSecond use and validation comparison test (different data):\033[0m".PHP_EOL,
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
