<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use LogicException;
use PBaszak\DedicatedMapper\Reflection\ReflectionFactory;
use PBaszak\DedicatedMapper\Reflection\Type\ClassType;
use PBaszak\DedicatedMapper\Reflection\Type\TypeFactory;

class Config
{
    /**
     * @var array<string, ClassType>
     */
    protected array $classes;

    /**
     * @param class-string $className
     */
    public function __construct(protected string $className)
    {
        if (!class_exists($this->className, false)) {
            throw new \InvalidArgumentException(
                sprintf('Class %s does not exist', $this->className)
            );
        }
    }

    public function reflect(): self
    {
        ClassType::$classTypes = [];
        (new TypeFactory())->createFromString($this->className);
        $this->classes = ClassType::$classTypes;

        return $this;
    }

    public function getMainClassType(): ClassType
    {
        return $this->classes[$this->className];
    }

    public function export(): array
    {
        if (!isset($this->classes)) {
            throw new LogicException('Class reflection is not set. You need to call reflect() method first.');
        }

        return [
            'main' => $this->className,
            'classes' => array_map(fn (ClassType $class) => $class->toArray(), $this->classes),
        ];
    }

    public static function import(array $data): self
    {
        $config = new self($data['className']);

        return $config;
    }
}
