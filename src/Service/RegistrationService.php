<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RegistrationDto;
use App\Factory\CreateUserFactory;
use App\Response\AbstractResponse;
use App\Response\CustomResponse;
use App\Response\ErrorResponse;
use App\Validation\RegistrationValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly RegistrationValidator $registrationValidator,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function register($content): AbstractResponse
    {
        $data = json_decode($content, true);

        if (empty($data['email'])) {
            return new ErrorResponse('Email is required', 400);
        }

        if (empty($data['username'])) {
            return new ErrorResponse('Username is required', 400);
        }

        if (empty($data['password'])) {
            return new ErrorResponse('Password is required', 400);
        }

        $registrationDto = $this->serializer->deserialize($content, RegistrationDto::class, 'json');
        $this->registrationValidator->validate($registrationDto);

        if ($this->registrationValidator->hasErrors()) {
            return new ErrorResponse(
                $this->registrationValidator->getErrors()[0],
                $this->registrationValidator->getCode()
            );
        }

        $user = CreateUserFactory::create($registrationDto, $this->passwordHasher);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new CustomResponse([
            'user' => [
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'numberPhone' => $user->getNumberPhone(),
                'country' => $user->getCountry(),
                'state' => $user->getState(),
                'createdAt' => $user->getCreatedAt()
            ]
        ]);
    }
}
