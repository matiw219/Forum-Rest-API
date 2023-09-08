<?php

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Password extends Constraint
{

    public string $message = 'Your password must consist of at least one uppercase letter, one lowercase letter, a number and a special character. The password should have from 8 to 30 characters in total.';
    public string $regex = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$%^&+=!]).{8,30}$/';

}