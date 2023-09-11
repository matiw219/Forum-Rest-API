<?php

declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidator
{
    private ?ValidatorInterface $validator = null;
    private array $errors = [];
    private int $code = 0;

    public function __construct(ValidatorInterface $validator, object $object)
    {
        $this->validator = $validator;
        $this->validate($object);
    }

    public function getErrors() : array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function hasErrors() : bool
    {
        return count($this->errors) > 0;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    public function getValidator(): ?ValidatorInterface
    {
        return $this->validator;
    }

    public abstract function validate(object $object) : void;
}