<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use PBaszak\DedicatedMapper\Reflection\ClassReflection;
use PBaszak\DedicatedMapper\Reflection\ReflectionFactory;

class Config
{
    /** @var array<string> */
    protected array $usedFiles = [];
    protected ClassReflection $classReflection;

    /**
     * @param class-string $className
     */
    public function __construct(private string $className)
    {
        if (!class_exists($this->className, false)) {
            throw new \InvalidArgumentException(
                sprintf('Class %s does not exist', $this->className)
            );
        }
    }

    public function reflect(): self
    {
        $this->classReflection = (new ReflectionFactory())->createClassReflection($this->className, null);

        return $this;
    }

    public function getClassReflection(): ClassReflection
    {
        return $this->classReflection;
    }

    public function export(): array
    {
        return [
            'className' => $this->className,
            'classReflection' => $this->classReflection,
            'usedFiles' => $this->usedFiles,
        ];
    }

    public static function import(array $data): self
    {
        $config = new self($data['className']);
        $config->classReflection = $data['classReflection'];
        $config->usedFiles = $data['usedFiles'];

        return $config;
    }
}
