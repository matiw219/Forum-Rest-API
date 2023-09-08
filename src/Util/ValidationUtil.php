<?php

namespace App\Util;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationUtil
{

    public static function getErrors(ConstraintViolationListInterface $constraintViolationList) : array {
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