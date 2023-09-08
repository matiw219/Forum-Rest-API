<?php

namespace App\Service;

use App\Dto\RegistrationDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Util\ValidationUtil;
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
        if (!$registrationDto->getEmail() || !$registrationDto->getUsername() || !$registrationDto->getPassword()) {
            return new JsonResponse(['error' => 'The submitted inquiry does not contain all the required data'], 404);
        }

        $emailViolations = ValidationUtil::getErrors($this->validator->validateProperty($registrationDto, 'email'));
        if (!empty($emailViolations)) {
            return new JsonResponse(['errors' => $emailViolations], 404);
        }

        $usernameViolations = ValidationUtil::getErrors($this->validator->validateProperty($registrationDto, 'username'));
        if (!empty($usernameViolations)) {
            return new JsonResponse(['errors' => $usernameViolations], 404);
        }

        $passwordViolations = ValidationUtil::getErrors($this->validator->validateProperty($registrationDto, 'password'));
        if (!empty($passwordViolations)) {
            return new JsonResponse(['errors' => $passwordViolations], 404);
        }

        $user = $this->userRepository->findOneBy(['email' => $registrationDto->getEmail()]);
        if ($user) {
            return new JsonResponse(['error' => 'A user with this email address already exists'], 404);
        }

        $user = $this->userRepository->findOneBy(['username' => $registrationDto->getUsername()]);
        if ($user) {
            return new JsonResponse(['error' => 'A user with this username already exists'], 404);
        }

        $user = $this->makeUser($registrationDto);
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

    public function makeUser(RegistrationDto $registrationDto) : User
    {
        $user = new User();
        $user->setEmail($registrationDto->getEmail());
        $user->setUsername($registrationDto->getUsername());
        $user->setPassword($this->passwordHasher->hashPassword($user, $registrationDto->getPassword()));
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