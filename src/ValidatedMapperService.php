<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use PBaszak\DedicatedMapper\Contract\FunctionInterface;
use PBaszak\DedicatedMapper\Contract\GetterInterface;
use PBaszak\DedicatedMapper\Contract\MapperServiceInterface;
use PBaszak\DedicatedMapper\Contract\SetterInterface;
use PBaszak\DedicatedMapper\Expression\Builder\AbstractBuilder;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatedMapperService extends MapperService implements MapperServiceInterface
{
    /**
     * @var ConstraintViolationList[]
     */
    protected array $validationErrors = [];

    public function __construct(
        string $directory,
        protected ValidatorInterface $validator
    ) {
        parent::__construct($directory);
    }

    /**
     * @throws ValidationFailedException
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface&AbstractBuilder $getterBuilder,
        SetterInterface&AbstractBuilder $setterBuilder,
        FunctionInterface $functionBuilder = null,
        bool $throwExceptionOnMissingProperty = false,
        bool $isCollection = false,
        array $modificators = [],
        array $groups = null
    ): mixed {
        $output = parent::map(...func_get_args());

        if (!empty($this->validationErrors)) {
            $violations = new ConstraintViolationList();

            foreach ($this->validationErrors as $path => $errorList) {
                foreach ($errorList->getIterator() as $e) {
                    $violations->add(
                        new ConstraintViolation(
                            $e->getMessage(),
                            $e->getMessageTemplate(),
                            $e->getParameters(),
                            $e->getRoot(),
                            $path,
                            $e->getInvalidValue(),
                            $e->getPlural(),
                            $e->getCode(),
                            $e->getConstraint(),
                            $e->getCause(),
                        )
                    );
                }
            }

            throw new ValidationFailedException(null, $violations);
        }

        return $output;
    }
}
