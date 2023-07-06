<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\MapperServiceInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Builder\DefaultExpressionBuilder;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class MapperService implements MapperServiceInterface
{
    use HandleTrait;

    /** @var array<string,callable> */
    private static array $mappers = [];

    public function __construct(
        private string $directory,
        MessageBusInterface $messageBus
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * @param class-string $blueprint
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = new DefaultExpressionBuilder(),
        LoopInterface $loopBuilder = new DefaultExpressionBuilder(),
        bool $isCollection = false
    ): mixed {
        $mapperId = hash(in_array('xxh3', hash_algos()) ? 'xxh3' : 'crc32', var_export(array_slice(func_get_args(), 1), true));
        $function = self::$mappers[$mapperId] ??= $this->getFunction($mapperId, $blueprint, $getterBuilder, $setterBuilder, $functionBuilder, $loopBuilder, $isCollection);

        return $function($data);
    }

    /**
     * @param class-string $blueprint
     */
    private function getFunction(
        string $mapperId,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = new DefaultExpressionBuilder(),
        LoopInterface $loopBuilder = new DefaultExpressionBuilder(),
        bool $isCollection = false
    ): callable {
        $function = @include $this->directory.$mapperId.'.php';

        if ($function) {
            return $function;
        }
        $mapper = sprintf(
            "<?php\n\ndeclare(strict_types=1);\n\n%s",
            $this->handle(
                new GetMapper($blueprint, $getterBuilder, $setterBuilder, $functionBuilder, $loopBuilder, $isCollection)
            )->toString());

        if (!@file_put_contents($this->directory.$mapperId.'.php', $mapper)) {
            mkdir($this->directory, 0777, true);
            if (!@file_put_contents($this->directory.$mapperId.'.php', $mapper)) {
                throw new \RuntimeException('Unable to create mapper file.');
            }
        }

        return @include $this->directory.$mapperId.'.php';
    }
}
