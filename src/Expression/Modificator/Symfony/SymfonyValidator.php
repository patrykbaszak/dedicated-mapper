<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Modificator\Symfony;

use PBaszak\DedicatedMapper\Attribute\MappingCallback;
use PBaszak\DedicatedMapper\Contract\ModificatorInterface;
use PBaszak\DedicatedMapper\Expression\Assets\Expression;
use PBaszak\DedicatedMapper\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class SymfonyValidator.
 * Requires symfony/validator package.
 * Requires access to ValidatorInterface $this->validator from the mapper function.
 * Requires access to empty array $this->validationErrors from the mapper function.
 *
 * Supported attributes:
 * - Constraint() - only for properties
 *
 * Not supported (yet) attributes:
 * - Constraint() - in class scope
 * - HasNamedArguments()
 */
class SymfonyValidator implements ModificatorInterface
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
    }

    public function modifyPropertyExpression(Property $sourceProperty, Property $targetProperty, Expression $expression): void
    {
        $expression->pathVariable = 'path';
        $name = $sourceProperty->options['name'] ?? $sourceProperty->originName;

        $constraints = array_filter(
            array_map(
                fn (\ReflectionAttribute $attr) => is_subclass_of($attr->getName(), Constraint::class) ?
                    (object) ['name' => $attr->getName(), 'arguments' => $attr->getArguments()]
                    : null,
                $sourceProperty->getAttributes()
            )
        );

        $notFoundConstraints = [];
        foreach ($constraints as $index => $constraint) {
            if (in_array('groups', array_map(fn (\ReflectionParameter $param) => $param->name, (new \ReflectionMethod($constraint->name, '__construct'))->getParameters()))) {
                $constraint->arguments['groups'] = array_merge($constraint->arguments['groups'] ?? [], array_filter(['Default', $sourceProperty->getParent()?->blueprint?->reflection->getShortName()]));
                $constraint->arguments['groups'] = array_unique($constraint->arguments['groups']);
            }

            if (NotBlank::class === $constraint->name) {
                unset($constraints[$index]);
                $notFoundConstraints[] = $constraint;
            }
            if (Valid::class === $constraint->name) {
                unset($constraints[$index]);
            }
        }

        if (!empty($constraints)) {
            $sourceProperty->callbacks[] = new MappingCallback(
                $this->createConstraintCallback($name, $constraints),
                0
            );
        }
        if (!empty($notFoundConstraints)) {
            $notBlank = new ($notFoundConstraints[0]->name)(...$notFoundConstraints[0]->arguments);
            $sourceProperty->callbacks[] = new MappingCallback(
                "\$this->validationErrors[\"{\${{pathName}}}.{$name}\"] = new Symfony\Component\Validator\ConstraintViolationList([new \Symfony\Component\Validator\ConstraintViolation(\n".
                    "'{$notBlank->message}', '{$notBlank->message}', ['{{ value }}' => null], null, \"{\${{pathName}}}.{$name}\", null, null, ".
                    "\Symfony\Component\Validator\Constraints\NotBlank::IS_BLANK_ERROR, new \Symfony\Component\Validator\Constraints\NotBlank(...".var_export($notFoundConstraints[0]->arguments, true)."), null\n".
                    ')]);',
                0,
                true
            );
        }
    }

    public function modifyBlueprintExpression(Blueprint $sourceBlueprint, Blueprint $targetBlueprint, FunctionExpression $expression): void
    {
        $expression->pathVariable = 'path';
    }

    /**
     * @param array<object{name: string, arguments: array}> $constraints
     */
    private function createConstraintCallback(string $propertyName, array $constraints): string
    {
        $constraintsExpression = '';
        foreach ($constraints as $constraint) {
            $constraintsExpression .= "\tnew {$constraint->name}(...".var_export($constraint->arguments, true)."),\n";
        }
        $groupsExpression = implode(', ', $this->groups);
        $callback = "if (0 < (\$e = \$this->validator->validate(\${{var}}, [{$constraintsExpression}], [{$groupsExpression}]))->count()) {\n"
            ."\t\$this->validationErrors[\"{\${{pathName}}}.{$propertyName}\"] = \$e;\n"
            ."}\n";

        return $callback;
    }
}
