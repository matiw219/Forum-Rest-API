<?php

declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidator
{
    /*** @var array<int, string> $errors */
    private array $errors = [];
    private int $code = 0;

    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function hasErrors(): bool
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

    abstract public function validate(object $object): void;
}
