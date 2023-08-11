<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Attribute;

use PBaszak\DedicatedMapperBundle\Expression\Assets\Setter;

/**
 * Part of the mapping process.
 * Use is if You got class like DateTime or ArrayObject but Your own.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class SimpleObject
{
    /**
     * @param string              $staticConstructor      name of static method to create object,
     *                                                    if `null` then constructor will be used
     * @param array<string,mixed> $namedArguments         arguments passed to constructor or static method
     * @param string|null         $deconstructor          name of method to deconstruct object which will be used when mapping
     *                                                    back to anything else than class object. Like `format` for DateTime
     * @param array<string,mixed> $deconstructorArguments arguments passed to deconstructor
     * @param mixed[]             $options                any options required but custom actions
     */
    public function __construct(
        public readonly ?string $staticConstructor = null,
        public readonly ?string $nameOfArgument = null,
        public readonly array $namedArguments = [],
        public readonly ?string $deconstructor = null,
        public readonly array $deconstructorArguments = [],
        public readonly array $options = [],
    ) {
    }

    /**
     * @param class-string $class
     */
    public function getConstructorExpression(
        string $class, 
        bool $isCollectionItem = false,
    ): string {
        $constructor = $this->staticConstructor
            ? sprintf('%s::%s(%s)', $class, $this->staticConstructor, '%s')
            : sprintf('new %s(%s)', $class, '%s');

        $constructorArguments = $this->nameOfArgument
            ? sprintf('\'%s\' => %s', $this->nameOfArgument, '%s')
            : '%s';

        if ($this->nameOfArgument) {
            foreach ($this->namedArguments as $name => $value) {
                $constructorArguments = sprintf(
                    '%s, \'%s\' => %s',
                    $constructorArguments,
                    $name,
                    var_export($value, true),
                );
            }
        }

        return sprintf(
            '($x = %s) instanceof %s ? $x : %s',
            $isCollectionItem ? '{{getterAssignment:item}}' : '{{getterAssignment:basic}}',
            $class,
            sprintf(
                $constructor,
                '%s' === $constructorArguments ? '$x' : sprintf('...[%s]', $constructorArguments)
            )
        );
    }

    public function getDeconstructorExpression(): string
    {
        if (!$this->deconstructor) {
            return '';
        }

        if ($this->deconstructorArguments) {
            return sprintf(
                '->%s(...%s)',
                $this->deconstructor,
                var_export($this->deconstructorArguments, true),
            );
        }

        return sprintf('->%s()', $this->deconstructor);
    }
}
