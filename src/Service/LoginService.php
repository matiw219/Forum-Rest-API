<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use App\Validation\LoginValidator;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginService
{
    public function __construct(
        private UserRepository $userRepository,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthTokenService $tokenService,
        private LoggerInterface $logger
    ){
    }

    public function login(LoginDto $loginDto) : JsonResponse
    {
        $validator = new LoginValidator($this->validator, $loginDto, $this->userRepository, $this->logger, $this->passwordHasher);
        if ($validator->hasErrors()) {
            return new JsonResponse(['error' => $validator->getErrors()], $validator->getCode());
        }

        $user = null;
        try {
            $user = $this->userRepository->findUserByUsernameOrEmail($loginDto->getUser());
        } catch (NonUniqueResultException $exception) {
            $this->logger->error('An error occurred while searching for the user: ' . $exception->getMessage());
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }
        $token = $this->tokenService->createNewToken($user);

        return new JsonResponse(
            [
                'token' => $token->getHash(),
                'user' => $token->getUser()->getUsername(),
                'createdAt' => $token->getCreatedAt(),
                'expiresAt' => $token->getExpiresAt()
            ]
        );
    }
}