<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RegistrationDto;
use App\Factory\CreateUserFactory;
use App\Validation\RegistrationValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private RegistrationValidator $registrationValidator
    ) {
    }

    public function register(RegistrationDto $registrationDto) : JsonResponse
    {
        $this->registrationValidator->validate($registrationDto);
        if ($this->registrationValidator->hasErrors()) {
            return new JsonResponse(['error' => $this->registrationValidator->getErrors()], $this->registrationValidator->getCode());
        }

        $user = CreateUserFactory::create($registrationDto, $this->passwordHasher);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'numberPhone' => $user->getNumberPhone(),
                'country' => $user->getCountry(),
                'state' => $user->getState(),
                'createdAt' => $user->getCreatedAt()
            ]
        ]);
    }
}