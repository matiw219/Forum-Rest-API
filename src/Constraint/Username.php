<?php

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Username extends Constraint
{

    public string $message = 'The username should consist only of letters and numbers, should be from 6 to 30 characters long and should start with a letter';
    public string $regex = '/^[a-zA-Z][a-zA-Z0-9_]{5,29}$/';

}