<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;

/**
 * @property string $id
 * @property string $name
 * @property string $mirror
 */
class Options
{
    protected ArrayObject $options;

    public function __construct(mixed ...$options)
    {
        if (!empty(array_filter(array_keys($options), fn (int|string $key) => is_int($key)))) {
            throw new \InvalidArgumentException('Options must be associative array.');
        }

        $this->options = new ArrayObject($options);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->options[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->options[$name]);
    }

    public function toArray(): array
    {
        return $this->options->getArrayCopy();
    }

    public function merge(Options $options): Options
    {
        return new Options(
            ...array_merge($this->toArray(), $options->toArray())
        );
    }
}
