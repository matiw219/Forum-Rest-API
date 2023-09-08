<?php

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!preg_match($constraint->regex, $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}