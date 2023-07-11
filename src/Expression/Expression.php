<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Expression
{
    /** @param Modifier[] $modifiers */
    public function __construct(
        public Getter $getter,
        public Setter $setter,
        public Statement $statement,
        public array $modifiers,
        public bool $throwException = false,
        public bool $isNullable = false,
        public bool $hasDefaultValue = false,
        public mixed $defaultValue = null,
    ) {
    }

    public function toString(string $sourceVariableName, string $targetVariableName): string
    {
        $getter = $this->getter->toString($sourceVariableName);

        if ($this->hasDefaultValue && 'null' !== strtolower($defaultValue = var_export($this->defaultValue, true))) {
            $getter .= ' ?? '.$defaultValue;
        }

        if ($this->isNullable) {
            $getter .= ' ?? null';
        }

        $modifiers = implode("\n", array_map(
            fn (Modifier $modifier) => $modifier->toString('var'),
            $this->modifiers
        ));

        if ($this->throwException && '' === $modifiers) {
            return $this->setter->toString(
                $targetVariableName,
                $getter,
            );
        } elseif ($this->throwException && $modifiers) {
            return sprintf(
                "\$var = %s;\n".
                    "%s\n",
                $getter,
                $modifiers."\n".
                    $this->setter->toString(
                        $targetVariableName,
                        '$var',
                    )
            );
        } else {
            return $this->statement->toString(
                $sourceVariableName,
                'var',
                $this->getter->toString($sourceVariableName),
                ($modifiers ? $modifiers."\n" : '').
                $this->setter->toString(
                    $targetVariableName,
                    '$var',
                )
            );
        }
    }
}
