<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression;

use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Attribute\TargetProperty;
use PBaszak\DedicatedMapperBundle\Contract\FunctionInterface;
use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\ModificatorInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Expression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapperBundle\Expression\Builder\AbstractBuilder;
use PBaszak\DedicatedMapperBundle\Mapper;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;
use Symfony\Component\Uid\Uuid;

class ExpressionBuilder
{
    protected Mapper $mapper;

    /**
     * @var ModificatorInterface[]
     */
    protected array $modificators = [];
    protected Blueprint $source;
    protected Blueprint $target;
    protected bool $throwExceptionOnMissingProperty = false;

    /**
     * @param array<string>|null $groups
     */
    public function __construct(
        protected Blueprint $blueprint,
        protected AbstractBuilder&GetterInterface $getterBuilder,
        protected AbstractBuilder&SetterInterface $setterBuilder,
        protected FunctionInterface $functionBuilder,
        protected ?array $groups = null,
    ) {
        $this->source = $getterBuilder->getBlueprint($blueprint->isCollection) ?? clone $blueprint;
        $this->target = $setterBuilder->getBlueprint($blueprint->isCollection) ?? clone $blueprint;
    }

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function applyModificators(array $modificators = []): self
    {
        $this->modificators = $modificators;

        foreach ($this->modificators as $modificator) {
            $modificator->init($this->blueprint, $this->groups);
        }

        return $this;
    }

    public function build(bool $throwExceptionOnMissingProperty = false): self
    {
        $this->throwExceptionOnMissingProperty = $throwExceptionOnMissingProperty;
        $this->matchBlueprints($this->blueprint, $this->source, $this->target);

        $this->mapper = new Mapper(
            sprintf(
                'return %s;',
                $this->newFunctionExpression(
                    $this->blueprint,
                    $this->source,
                    $this->target
                )->toString()
            )
        );

        return $this;
    }

    public function getMapper(): Mapper
    {
        return $this->mapper;
    }

    /** @param MappingCallback[] $callbacks */
    protected function newPropertyExpression(
        Property $source,
        Property $target,
        FunctionExpression $function = null,
        string $functionVar = null,
        array $callbacks = [] // it is required by tests, do not recommend to use it
    ): Expression {
        $expression = new Expression(
            $this->getterBuilder->getGetter($source),
            $this->setterBuilder->getSetter($target),
            $function,
            $this->modificators,
            $callbacks,
            [],
            $this->throwExceptionOnMissingProperty,
            functionVar: $functionVar,
        );

        return $expression->build($source, $target);
    }

    protected function newFunctionExpression(
        Blueprint $origin,
        Blueprint $source,
        Blueprint $target
    ): FunctionExpression {
        $expression = new FunctionExpression(
            $this->functionBuilder->getFunction(),
            $this->modificators,
            sourceType: $this->getterBuilder->getSourceType($source),
            targetType: $this->setterBuilder->getTargetType($target),
        );

        $functionId = Uuid::v4()->toRfc4122();
        /* Getters first */
        $expression->addInitialExpression($this->getterBuilder->getGetterInitialExpression($source, $functionId));
        $expression->addFinalExpression($this->getterBuilder->getGetterFinalExpression($source, $functionId));

        $expression->addInitialExpression($this->setterBuilder->getSetterInitialExpression($target, $functionId));
        $expression->addFinalExpression($this->setterBuilder->getSetterFinalExpression($target, $functionId));

        foreach ($origin->properties as $property) {
            $expression->addExpression(
                (new Expression(
                    $this->getterBuilder->getGetter($this->getProperty($property, 'source')),
                    $this->setterBuilder->getSetter($this->getProperty($property, 'target')),
                    $property->blueprint ? $this->newFunctionExpression(
                        $property->blueprint,
                        $this->getBlueprint($property->blueprint, 'source'),
                        $this->getBlueprint($property->blueprint, 'target')
                    ) : null,
                    $this->modificators,
                    [], // this Expression Builder does not include own callbacks
                    [], // this Expression Builder does not include own collection items callbacks
                    $this->throwExceptionOnMissingProperty,
                    functionVar: $property->blueprint ? $this->createUniqueVariableName($property->blueprint, 'func_') : null,
                ))->build(
                    $this->getProperty($property, 'source'),
                    $this->getProperty($property, 'target')
                )
            );
        }

        return $expression->build($source, $target);
    }

    /** @var string[] */
    private static array $usedVariableNames = [];
    private static int $seed = 0;

    private function createUniqueVariableName(Blueprint $blueprint, string $prefix = 'var_'): string
    {
        do {
            $variableName = hash('crc32', $blueprint->reflection->getName().self::$seed++, false);
        } while (in_array($variableName, self::$usedVariableNames));

        self::$usedVariableNames[] = $variableName = $prefix.$variableName;

        return $variableName;
    }

    private const OPTIONS_KEY = 'mirror_resource.id';
    /** @var array<string,array<string,Blueprint|Property>> */
    private array $mirrors = [];

    private function matchBlueprints(Blueprint $originBlueprint, Blueprint $source, Blueprint $target): void
    {
        $originBlueprint->options[self::OPTIONS_KEY] = $mirrorId = Uuid::v4()->toRfc4122();
        $this->mirrors[$mirrorId] = ['source' => $source, 'target' => $target];

        foreach ($originBlueprint->properties as $property) {
            $this->matchProperties($property, $source, $target);
        }
    }

    private function matchProperties(Property $originProperty, Blueprint $source, Blueprint $target): void
    {
        /** @var TargetProperty|null */
        $targetPropertyAttr = !empty($a = $originProperty->reflection->getAttributes(TargetProperty::class)) ? $a[0]->newInstance() : null;
        /** @var Property */
        $sourceProperty = $source->getProperty($targetPropertyAttr?->name ?? $originProperty->originName, true);
        /** @var Property */
        $targetProperty = $target->getProperty($targetPropertyAttr?->name ?? $originProperty->originName, true);

        $originProperty->options[self::OPTIONS_KEY] = $mirrorId = Uuid::v4()->toRfc4122();
        $this->mirrors[$mirrorId] = ['source' => $sourceProperty, 'target' => $targetProperty];

        if ($originProperty->blueprint) {
            if (null === $sourceProperty->blueprint || null === $targetProperty->blueprint) {
                /*
                 * There is one case when mirrored property blueprint is null while origin property blueprint is not.
                 * The mirrored property is a simple object, so it doesn't have a blueprint but origin property has or vice versa.
                 */
                throw new \LogicException('Mirrored property blueprint is null while origin property blueprint is not, what is not supported (maybe) yet.');
            }
            $this->matchBlueprints($originProperty->blueprint, $sourceProperty->blueprint, $targetProperty->blueprint);
        }
    }

    private function getBlueprint(Blueprint $blueprint, string $origin = 'source'): Blueprint
    {
        /** @var Blueprint */
        $blueprint = $this->mirrors[$blueprint->options[self::OPTIONS_KEY]][$origin];

        return $blueprint;
    }

    private function getProperty(Property $property, string $origin = 'source'): Property
    {
        /** @var Property */
        $property = $this->mirrors[$property->options[self::OPTIONS_KEY]][$origin];

        return $property;
    }
}
