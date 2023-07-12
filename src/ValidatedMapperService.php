<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\MapperServiceInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
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
     *
     * @warning This method will throw ValidationFailedException if any validation error occurs.
     * If You change `throwException` flag into `false`, all properties without default value will
     * be optional and will not be validated if they are not present in input data.
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = null,
        LoopInterface $loopBuilder = null,
        bool $throwException = true,
        bool $isCollection = false,
        array $modificators = [],
        string $group = null
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
