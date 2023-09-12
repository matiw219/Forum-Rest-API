<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RegistrationDto;
use App\Factory\CreateUserFactory;
use App\Validation\RegistrationValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private RegistrationValidator $registrationValidator,
        private SerializerInterface $serializer,
    ) {
    }

    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email'])) {
            return new JsonResponse(['error' => 'Type your email'], 400);
        }

        if (empty($data['username'])) {
            return new JsonResponse(['error' => 'Type your username'], 400);
        }

        if (empty($data['password'])) {
            return new JsonResponse(['error' => 'Type your password'], 400);
        }

        $registrationDto = $this->serializer->deserialize($request->getContent(), RegistrationDto::class, 'json');
        $this->registrationValidator->validate($registrationDto);

        if ($this->registrationValidator->hasErrors()) {
            return new JsonResponse(
                ['error' => $this->registrationValidator->getErrors()],
                $this->registrationValidator->getCode()
            );
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
