<?php

declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationHandler
{
    public static function getErrors(ConstraintViolationListInterface $constraintViolationList): array
    {
        if (count($constraintViolationList) == 0) {
            return [];
        }

        $result = [];

        foreach ($constraintViolationList as $error) {
            $result[] = $error->getMessage();
        }

        return $result;
    }
}
