<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use App\Validation\LoginValidator;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginService
{
    public function __construct(
        private UserRepository $userRepository,
        private AuthTokenService $tokenService,
        private LoggerInterface $logger,
        private LoginValidator $loginValidator
    ){
    }

    public function login(LoginDto $loginDto) : JsonResponse
    {
        $this->loginValidator->validate($loginDto);
        if ($this->loginValidator->hasErrors()) {
            return new JsonResponse(['error' => $this->loginValidator->getErrors()], $this->loginValidator->getCode());
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