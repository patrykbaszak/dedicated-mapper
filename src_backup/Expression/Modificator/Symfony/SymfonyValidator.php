<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Modificator\Symfony;

use PBaszak\DedicatedMapper\Attribute\MappingCallback;
use PBaszak\DedicatedMapper\Contract\GetterInterface;
use PBaszak\DedicatedMapper\Contract\SetterInterface;
use PBaszak\DedicatedMapper\Expression\Modificator\ModificatorInterface;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use Symfony\Component\Validator\Constraint;

class SymfonyValidator implements ModificatorInterface
{
    private GetterInterface $getterBuilder;
    private SetterInterface $setterBuilder;

    public function init(Blueprint $blueprint, GetterInterface $getterBuilder, SetterInterface $setterBuilder, string $group = null): void
    {
        $this->getterBuilder = $getterBuilder;
        $this->setterBuilder = $setterBuilder;

        if (!class_exists(Constraint::class)) {
            throw new \RuntimeException('Symfony validator is not installed.');
        }

        $this->applyValidationCallbacks($blueprint, $group);
    }

    private function applyValidationCallbacks(Blueprint $blueprint, string $group = null): void
    {
        foreach ($blueprint->properties as &$property) {
            if ($property->blueprint) {
                $this->applyValidationCallbacks($property->blueprint, $group);
            }

            $constraints = array_filter(
                array_map(
                    fn (\ReflectionAttribute $attr) => is_subclass_of($attr->getName(), Constraint::class) ?
                        (object) ['name' => $attr->getName(), 'arguments' => $attr->getArguments()]
                        : null,
                    $property->reflection->getAttributes()
                )
            );

            if (!empty($constraints)) {
                $callback = 'if (0 < ($e = $this->validator->validate($var, [';
                foreach ($constraints as $constraint) {
                    $callback .= "\tnew {$constraint->name}(...".var_export($constraint->arguments, true)."),\n";
                }
                $callback .= ']';
                if ($group) {
                    $callback .= ", [{$group}]";
                }
                $callback .= "))->count())\n";
                $callback .= sprintf(
                    "\t\$this->validationErrors[%s] = \$e;\n",
                    'isset($path) ? "{$path}.'.$this->getterBuilder->getPropertyName($property).'" : \''.$this->getterBuilder->getPropertyName($property).'\''
                );
                $property->callbacks[] = new MappingCallback($callback);
            }
        }
    }
}
