<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use PBaszak\DedicatedMapper\Attribute\TargetProperty;
use PBaszak\DedicatedMapper\Contract\GetterInterface;
use PBaszak\DedicatedMapper\Contract\SetterInterface;
use PBaszak\DedicatedMapper\Expression\Getter;
use PBaszak\DedicatedMapper\Expression\InitialExpression;
use PBaszak\DedicatedMapper\Expression\Modificator\ModificatorInterface;
use PBaszak\DedicatedMapper\Expression\Setter;
use PBaszak\DedicatedMapper\Expression\Statement;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;
use Symfony\Component\Uid\Uuid;

class SecondBlueprintExpressionBuilderDecorator implements SetterInterface, GetterInterface
{
    private const OPTIONS_KEY = 'mirror_resource.id';

    /** @var array<string,Blueprint|Property> */
    private array $mirrors = [];
    private bool $isInitialized = false;

    /**
     * @param Blueprint|class-string $blueprint class-string is preferred because it's faster
     */
    public function __construct(
        private SetterInterface|GetterInterface $expressionBuilder,
        private string|Blueprint $blueprint,
    ) {
    }

    public function getPropertyName(Property $property): string
    {
        $property = $this->getProperty($property);

        return $this->expressionBuilder->getPropertyName($property);
    }

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function applyModificators(Blueprint $blueprint, GetterInterface $getterBuilder, SetterInterface $setterBuilder, string $group = null, array $modificators): void
    {
        $this->isInitialized || $this->init($blueprint);
        $blueprint = $this->getBlueprint($blueprint);

        foreach ($modificators as $modificator) {
            $modificator->init($blueprint, $getterBuilder, $setterBuilder, $group);
        }
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        $this->isInitialized || $this->init($blueprint);
        $blueprint = $this->getBlueprint($blueprint);

        return $this->expressionBuilder->getSetterInitialExpression($blueprint, $initialExpressionId);
    }

    public function createSetter(Property $property): Setter
    {
        $property = $this->getProperty($property);

        return $this->expressionBuilder->createSetter($property);
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        $property = $this->getProperty($property);

        return $this->expressionBuilder->createSimpleObjectSetter($property);
    }

    public function isPropertyNullable(Property $property): bool
    {
        $property = $this->getProperty($property);

        return $this->isPropertyNullable($property);
    }

    public function hasPropertyDefaultValue(Property $property): bool
    {
        $property = $this->getProperty($property);

        return $this->hasPropertyDefaultValue($property);
    }

    public function getPropertyDefaultValue(Property $property): mixed
    {
        $property = $this->getProperty($property);

        return $this->getPropertyDefaultValue($property);
    }

    public function getMappingCallbacks(Property $property): array
    {
        $property = $this->getProperty($property);

        return $this->getMappingCallbacks($property);
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        $this->isInitialized || $this->init($blueprint);
        $blueprint = $this->getBlueprint($blueprint);

        return $this->expressionBuilder->getGetterInitialExpression($blueprint, $initialExpressionId);
    }

    public function createGetter(Property $property): Getter
    {
        $property = $this->getProperty($property);

        return $this->expressionBuilder->createGetter($property);
    }

    public function createSimpleObjectGetter(Property $property): Getter
    {
        $property = $this->getProperty($property);

        return $this->expressionBuilder->createSimpleObjectGetter($property);
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        $blueprint = $this->getBlueprint($blueprint);

        return $this->expressionBuilder->getSourceType($blueprint);
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        $blueprint = $this->getBlueprint($blueprint);

        return $this->expressionBuilder->getOutputType($blueprint);
    }

    public function getIssetStatement(Property $property, bool $hasDefaultValue): Statement
    {
        $property = $this->getProperty($property);

        return $this->expressionBuilder->getIssetStatement($property, $hasDefaultValue);
    }

    private function init(Blueprint $originBlueprint): void
    {
        $this->blueprint instanceof Blueprint || $this->blueprint = Blueprint::create($this->blueprint);
        $this->matchBlueprints($originBlueprint, $this->blueprint);
        $this->isInitialized = true;
    }

    private function matchBlueprints(Blueprint $originBlueprint, Blueprint $blueprint): void
    {
        $originBlueprint->options[self::OPTIONS_KEY] = $mirrorId = Uuid::v4()->toRfc4122();
        $this->mirrors[$mirrorId] = $blueprint;

        foreach ($originBlueprint->properties as $property) {
            $this->matchProperties($property, $blueprint);
        }
    }

    private function matchProperties(Property $originProperty, Blueprint $mirroredBlueprint): void
    {
        /** @var TargetProperty|null */
        $targetPropertyAttr = !empty($a = $originProperty->reflection->getAttributes(TargetProperty::class)) ? $a[0]->newInstance() : null;
        /** @var Property */
        $mirroredProperty = $mirroredBlueprint->getProperty($targetPropertyAttr?->name ?? $originProperty->originName, true);

        $originProperty->options[self::OPTIONS_KEY] = $mirrorId = Uuid::v4()->toRfc4122();
        $this->mirrors[$mirrorId] = $mirroredProperty;

        if ($originProperty->blueprint) {
            if (null === $mirroredProperty->blueprint) {
                /*
                 * There is one case when mirrored property blueprint is null while origin property blueprint is not.
                 * The mirrored property is a simple object, so it doesn't have a blueprint but origin property has or vice versa.
                 */
                throw new \LogicException('Mirrored property blueprint is null while origin property blueprint is not, what is not supported (maybe) yet.');
            }
            $this->matchBlueprints($originProperty->blueprint, $mirroredProperty->blueprint);
        }
    }

    private function getBlueprint(Blueprint $blueprint): Blueprint
    {
        /** @var Blueprint */
        $blueprint = $this->mirrors[$blueprint->options[self::OPTIONS_KEY]];

        return $blueprint;
    }

    private function getProperty(Property $property): Property
    {
        /** @var Property */
        $property = $this->mirrors[$property->options[self::OPTIONS_KEY]];

        return $property;
    }
}
