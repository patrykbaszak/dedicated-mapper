<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Modificator\Symfony;

use PBaszak\DedicatedMapper\Contract\ModificatorInterface;
use PBaszak\DedicatedMapper\Expression\Assets\Expression;
use PBaszak\DedicatedMapper\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class SymfonySerializer.
 *
 * Supported attributes:
 * - @Serializer\Groups()
 * - @Serializer\Ignore()
 * - @Serializer\SerializedName()
 *
 * Not supported (yet) attributes:
 * - @Serializer\Context() ~ have the highest priority to be implemented
 * - @Serializer\DiscriminatorMap()
 * - @Serializer\MaxDepth() ~ have the highest priority to be implemented
 * - @Serializer\SerializedPath()
 */
class SymfonySerializer implements ModificatorInterface
{
    /**
     * @param string[] $groups
     */
    public function __construct(
        private array $groups = [],
    ) {
    }

    public function init(Blueprint $blueprint): void
    {
        $this->applyGroupsAttribute($blueprint);
        $this->applyIgnoreAttribute($blueprint);
    }

    public function modifyPropertyExpression(Property $sourceProperty, Property $targetProperty, Expression $expression): void
    {
        $this->applySerializedNameAttribute($sourceProperty, $targetProperty);
    }

    public function modifyBlueprintExpression(Blueprint $sourceBlueprint, Blueprint $targetBlueprint, FunctionExpression $expression): void
    {
    }

    /**
     * Groups are supported only for init method().
     */
    private function applyGroupsAttribute(Blueprint $blueprint): void
    {
        if (empty($this->groups)) {
            return;
        }

        $isPropertyInGroup = function (Property $property) {
            if (!empty($attr = $property->getAttributes(Serializer\Groups::class))) {
                /** @var Serializer\Groups $attr */
                $attr = $attr[0]->newInstance();
                $groups = $attr->getGroups();

                return !empty(array_intersect($this->groups, $groups));
            }

            return false;
        };

        foreach ($blueprint->properties as $property) {
            if ($isPropertyInGroup($property)) {
                continue;
            }

            $blueprint->deleteProperty($property->originName);
        }

        foreach ($blueprint->getAllProperties() as $property) {
            if ($isPropertyInGroup($property)) {
                continue;
            }

            $property->delete();
        }
    }

    /**
     * Ignore is supported only for init method().
     */
    private function applyIgnoreAttribute(Blueprint $blueprint): void
    {
        $isPropertyIgnored = function (Property $property) {
            return !empty($property->getAttributes(Serializer\Ignore::class));
        };

        foreach ($blueprint->properties as $property) {
            if ($isPropertyIgnored($property)) {
                $blueprint->deleteProperty($property->originName);
            }
        }

        foreach ($blueprint->getAllProperties() as $property) {
            if ($isPropertyIgnored($property)) {
                $property->delete();
            }
        }
    }

    /**
     * SerializedName is supported only for modifyPropertyExpression.
     */
    private function applySerializedNameAttribute(Property $sourceProperty, Property $targetProperty): void
    {
        foreach ([$sourceProperty, $targetProperty] as $property) {
            if (!empty($attr = $property->getAttributes(Serializer\SerializedName::class))) {
                /** @var Serializer\SerializedName $attr */
                $attr = $attr[0]->newInstance();
                $property->options['name'] = $attr->getSerializedName();
            }
        }
    }
}
