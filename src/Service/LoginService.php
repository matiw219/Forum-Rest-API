<?php

namespace App\Service;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginService
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthTokenService $tokenService
    ){
    }

    public function login(LoginDto $loginDto) : JsonResponse
    {
        if (!$loginDto->getUser() || !$loginDto->getPassword()) {
            return new JsonResponse(['error' => 'The submitted inquiry does not contain all the required data'], 404);
        }

        $user = $this->userRepository->findUserByUsernameOrEmail($loginDto->getUser());

        if (!$user | !$this->passwordHasher->isPasswordValid($user, $loginDto->getPassword())) {
            return new JsonResponse(['error' => 'The login or password provided is incorrect'], 404);
        }

        $token = $this->tokenService->createNewToken($user);

        return new JsonResponse(
            [
                'token' => $token->getToken(),
                'user' => $token->getUser()->getUsername(),
                'createdAt' => $token->getCreatedAt(),
                'expiresAt' => $token->getExpiresAt()
            ]
        );
    }
}