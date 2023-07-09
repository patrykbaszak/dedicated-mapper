<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\MapperServiceInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Builder\DefaultExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\ExpressionBuilder;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

class MapperService implements MapperServiceInterface
{
    /** @var array<string,callable> */
    private static array $mappers = [];

    public function __construct(
        private string $directory,
    ) {
    }

    /**
     * @param class-string           $blueprint
     * @param ModificatorInterface[] $modificators
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = null,
        LoopInterface $loopBuilder = null,
        bool $throwException = false,
        bool $isCollection = false,
        array $modificators = [],
        string $group = null
    ): mixed {
        $mapperId = hash(in_array('xxh3', hash_algos()) ? 'xxh3' : 'crc32', var_export(array_slice(func_get_args(), 1), true));
        $function = self::$mappers[$mapperId] ??= $this->getFunction(
            $mapperId,
            $blueprint,
            $getterBuilder,
            $setterBuilder,
            $functionBuilder,
            $loopBuilder,
            $throwException,
            $isCollection,
            $modificators,
            $group
        );

        return $function($data);
    }

    /**
     * @param class-string           $blueprint
     * @param ModificatorInterface[] $modificators
     */
    private function getFunction(
        string $mapperId,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = null,
        LoopInterface $loopBuilder = null,
        bool $throwException = false,
        bool $isCollection = false,
        array $modificators = [],
        string $group = null
    ): callable {
        if ($function = @include $fileName = $this->directory.$mapperId.'.php') {
            return $function;
        }

        $blueprint = Blueprint::create($blueprint, $isCollection);

        $expressionBuilder = new ExpressionBuilder(
            $blueprint,
            $getterBuilder,
            $setterBuilder,
            $functionBuilder ?? new DefaultExpressionBuilder(),
            $loopBuilder ?? new DefaultExpressionBuilder(),
            $group,
        );

        $mapper = sprintf(
            "<?php\n\ndeclare(strict_types=1);\n\n%s",
            $expressionBuilder
                ->applyModificators($modificators)
                ->createExpression($throwException)
                ->getMapper()
                ->toString()
        );

        if (!file_exists($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        if (!file_put_contents($fileName, $mapper)) {
            throw new \RuntimeException('Unable to create mapper file.');
        }

        return include $fileName;
    }
}
