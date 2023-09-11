<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RegistrationDto;
use App\Factory\CreateUserFactory;
use App\Repository\UserRepository;
use App\Validation\RegistrationValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function register(RegistrationDto $registrationDto) : JsonResponse
    {
        $validator = new RegistrationValidator($this->validator, $registrationDto, $this->userRepository);
        if ($validator->hasErrors()) {
            return new JsonResponse(['error' => $validator->getErrors()], $validator->getCode());
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