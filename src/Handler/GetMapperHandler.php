<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Handler;

use PBaszak\MessengerMapperBundle\Contract\GetMapper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class GetMapperHandler
{
    private ?string $fromMapSeparator = null;
    private ?string $toMapSeparator = null;

    public function __invoke(GetMapper $query): string
    {
        $this->validateInput($query);
        $expressions = $this->createExpressions(
            $query->from,
            $query->to,
            $query->fromType,
            $query->toType,
            $query->useValidator
        );
        
        if ($query->useValidator) {
            return sprintf(GetMapper::MAPPER_TEMPLATE_WITH_VALIDATOR, '', implode('', $expressions));
        }
        return sprintf(GetMapper::MAPPER_TEMPLATE, implode('', $expressions));
    }

    private function validateInput(GetMapper $query): void
    {
        foreach ([$query->from, $query->to] as $argument) {
            switch ($argument) {
                case 'array':
                case 'object':
                    break;
                default:
                    if (!class_exists($argument)) {
                        throw new \InvalidArgumentException(sprintf('Class %s does not exist.', $argument));
                    }
            }
        }

        foreach (['from' => $query->fromType, 'to' => $query->toType] as $key => $argument) {
            switch ($argument) {
                case null:
                case 'map':
                case 'array':
                case 'object':
                    continue 2;
            }

            /** @var string $argument */
            if (preg_match('/^map\{(?<separator>.+)\}$/', $argument, $matches)) {
                $this->{$key . 'MapSeparator'} = $matches['separator'];
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid %sType argument. Allowed: `null`, `array`, `object`, `map`, `map{<separator>}`.',
                    $key
                )
            );
        }
    }

    private function createExpressions(mixed $from, mixed $to, ?string $fromType = null, ?string $toType = null, bool $useValidator = false): array
    {
        return [];
    }
}
