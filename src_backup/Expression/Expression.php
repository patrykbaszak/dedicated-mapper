<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression;

class Expression
{
    /** @param string[] $modifiers */
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

        $modifiers = implode("\n", $this->modifiers);

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
            // case when we have no modifiers and we don't want to throw exception
            // should be possible to set variable without $var variable as a middleman
            // } elseif (!$this->throwException && '' === $modifiers) {
            //     return $this->statement->toString(
            //         $sourceVariableName,
            //         'var',
            //         $this->getter->toString($sourceVariableName),
            //         ($modifiers ? $modifiers."\n" : '').
            //         $this->setter->toString(
            //             $targetVariableName,
            //             '$var',
            //         )
            //     );
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
