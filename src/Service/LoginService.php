<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use App\Validation\LoginValidator;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class LoginService
{
    public function __construct(
        private UserRepository $userRepository,
        private AuthTokenService $tokenService,
        private LoggerInterface $logger,
        private LoginValidator $loginValidator,
        private SerializerInterface $serializer
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['user'])) {
            return new JsonResponse(['error' => 'Please enter your username or email'], 400);
        }

        if (empty($data['password'])) {
            return new JsonResponse(['error' => 'Please enter your password'], 400);
        }

        $user = null;

        try {
            $user = $this->userRepository->findUserByUsernameOrEmail($data['user']);
        } catch (NonUniqueResultException $exception) {
            $this->logger->error('An error occurred while searching for the user: ' . $exception->getMessage());
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }

        $loginDto = $this->serializer->deserialize($request->getContent(), LoginDto::class, 'json');
        $this->loginValidator->setUser($user);
        $this->loginValidator->validate($loginDto);

        if ($this->loginValidator->hasErrors()) {
            return new JsonResponse(['error' => $this->loginValidator->getErrors()], $this->loginValidator->getCode());
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
