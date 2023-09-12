<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\LoginDto;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginValidator extends AbstractValidator
{
    private User $user;

    public function __construct(
        ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct($validator);
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function validate(object $object): void
    {
        if (!($object instanceof LoginDto)) {
            return;
        }

        if (!$this->user | !$this->passwordHasher->isPasswordValid($this->user, $object->getPassword())) {
            $this->addError('The login or password provided is incorrect');
            $this->setCode(401);
        }
    }
}
