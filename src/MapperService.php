<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle;

use PBaszak\DedicatedMapperBundle\Contract\FunctionInterface;
use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\MapperServiceInterface;
use PBaszak\DedicatedMapperBundle\Contract\ModificatorInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Builder\AbstractBuilder;
use PBaszak\DedicatedMapperBundle\Expression\Builder\FunctionExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Expression\ExpressionBuilder;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;

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
        GetterInterface&AbstractBuilder $getterBuilder,
        SetterInterface&AbstractBuilder $setterBuilder,
        FunctionInterface $functionBuilder = null,
        bool $throwExceptionOnMissingProperty = false,
        bool $isCollection = false,
        array $modificators = [],
        array $groups = null
    ): mixed {
        $mapperId = hash(in_array('xxh3', hash_algos()) ? 'xxh3' : 'crc32', var_export(array_slice(func_get_args(), 1), true));
        $function = self::$mappers[$mapperId] ??= $this->getFunction(
            $mapperId,
            $blueprint,
            $getterBuilder,
            $setterBuilder,
            $functionBuilder,
            $throwExceptionOnMissingProperty,
            $isCollection,
            $modificators,
            $groups
        );

        return $function($data);
    }

    /**
     * @param class-string           $blueprint
     * @param ModificatorInterface[] $modificators
     * @param array<string>|null     $groups
     */
    private function getFunction(
        string $mapperId,
        string $blueprint,
        GetterInterface&AbstractBuilder $getterBuilder,
        SetterInterface&AbstractBuilder $setterBuilder,
        FunctionInterface $functionBuilder = null,
        bool $throwException = false,
        bool $isCollection = false,
        array $modificators = [],
        array $groups = null
    ): callable {
        if ($function = @include $fileName = $this->directory.$mapperId.'.php') {
            return $function;
        }

        $blueprint = Blueprint::create($blueprint, $isCollection);

        $expressionBuilder = new ExpressionBuilder(
            $blueprint,
            $getterBuilder,
            $setterBuilder,
            $functionBuilder ?? new FunctionExpressionBuilder(),
            $groups,
        );

        $mapper = sprintf(
            "<?php\n\ndeclare(strict_types=1);\n\n%s",
            $expressionBuilder
                ->applyModificators($modificators)
                ->build($throwException)
                ->getMapper()
                ->toString()
        );

        if (!file_exists($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        if (!file_put_contents($fileName, $mapper)) {
            throw new \RuntimeException('Unable to create mapper file.');
        }

        @shell_exec("vendor/bin/php-cs-fixer fix $fileName --rules=@Symfony --using-cache=no --quiet");

        return include $fileName;
    }
}
