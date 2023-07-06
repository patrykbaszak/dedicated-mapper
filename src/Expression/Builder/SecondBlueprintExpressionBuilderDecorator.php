<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;
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
