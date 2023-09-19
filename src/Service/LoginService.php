<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use App\Response\AbstractResponse;
use App\Response\CustomResponse;
use App\Response\ErrorResponse;
use App\Validation\LoginValidator;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class LoginService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthTokenService $tokenService,
        private readonly LoggerInterface $logger,
        private readonly LoginValidator $loginValidator,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function login($content): AbstractResponse
    {
        $data = json_decode($content, true);

        if (empty($data['user'])) {
            return new ErrorResponse('User is required', 400);
        }

        if (empty($data['password'])) {
            return new ErrorResponse('Password is required', 400);
        }

        $user = null;

        try {
            $user = $this->userRepository->findUserByUsernameOrEmail($data['user']);
        } catch (NonUniqueResultException $exception) {
            $this->logger->error('An error occurred while searching for the user: ' . $exception->getMessage());

            return new ErrorResponse('An unexpected error occurred', 500);
        }

        $loginDto = $this->serializer->deserialize($content, LoginDto::class, 'json');
        $this->loginValidator->setUser($user);
        $this->loginValidator->validate($loginDto);

        if ($this->loginValidator->hasErrors()) {
            return new ErrorResponse($this->loginValidator->getErrors()[0], $this->loginValidator->getCode());
        }

        $token = $this->tokenService->createNewToken($user);

        return new CustomResponse([
            'token' => $token->getHash(),
            'user_id' => $token->getUser()->getId(),
            'user' => $token->getUser()->getUsername(),
            'createdAt' => $token->getCreatedAt(),
            'expiresAt' => $token->getExpiresAt()
        ], 200);
    }
}
