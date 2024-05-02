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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatedMapperService extends MapperService implements MapperServiceInterface
{
    /**
     * @var constraintViolationList[]
     *                                Used to collect validation errors during mapping
     */
    protected array $validationErrors = [];

    private ?ConstraintViolationListInterface $validationResult = null;

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
        ?FunctionInterface $functionBuilder = null,
        bool $throwExceptionOnMissingProperty = false,
        bool $isCollection = false,
        array $modificators = [],
        bool $throwValidationFailedException = true,
    ): mixed {
        $output = parent::map(...func_get_args());

        if (!empty($this->validationErrors)) {
            $violations = new ConstraintViolationList();

            foreach ($this->validationErrors as $path => $errorList) {
                /** @var ConstraintViolation $e */
                foreach ($errorList->getIterator() as $e) {
                    $violations->add(
                        new ConstraintViolation(
                            $e->getMessage(),
                            $e->getMessageTemplate(),
                            $e->getParameters(),
                            $output,
                            ltrim($path, '.'),
                            $e->getInvalidValue(),
                            $e->getPlural(),
                            $e->getCode(),
                            $e->getConstraint(),
                            $e->getCause(),
                        )
                    );
                }
            }

            $this->validationResult = $violations;

            if ($throwValidationFailedException) {
                throw new ValidationFailedException(sprintf('Source data failed validation during mapping from `%s` to `%s`.', $getterBuilder->getSourceType($blueprint), $setterBuilder->getTargetType($blueprint)), $violations);
            }
        }

        return $output;
    }

    public function getLastValidationResult(): ?ConstraintViolationListInterface
    {
        return $this->validationResult;
    }
}
