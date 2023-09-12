<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\RegistrationDto;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserFactory
{
    public static function create(RegistrationDto $registrationDto, UserPasswordHasherInterface $passwordHasher): ?User
    {
        $user = new User();
        $user->setEmail($registrationDto->getEmail());
        $user->setUsername($registrationDto->getUsername());
        $user->setPassword($passwordHasher->hashPassword($user, $registrationDto->getPassword()));

        if ($registrationDto->getRoles() != null) {
            $user->setRoles($registrationDto->getRoles());
        }

        $user->setNumberPhone($registrationDto->getNumberPhone());
        $user->setCountry($registrationDto->getCountry());
        $user->setState($registrationDto->getState());
        $user->setCreatedAt(new \DateTimeImmutable());

        return $user;
    }
}
