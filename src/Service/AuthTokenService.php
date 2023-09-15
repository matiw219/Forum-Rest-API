<?php

declare(strict_types=1);

namespace App\Service;

use App\Array\BetterArray;
use App\Entity\AuthToken;
use App\Entity\User;
use App\Factory\AuthTokenFactory;
use App\Repository\AuthTokenRepository;
use App\Response\AbstractResponse;
use App\Response\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;

class AuthTokenService
{
    public function __construct(
        private readonly AuthTokenRepository $tokenRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function loggedInAs(?string $token = null): ?User
    {
        if (!$token) {
            return null;
        }

        if (!str_starts_with($token, 'Bearer ')) {
            return null;
        }

        $token = trim($token, 'Bearer ');

        $authToken = $this->tokenRepository->findOneBy(['hash' => $token]);
        if (!$authToken) {
            return null;
        }

        $now = new \DateTimeImmutable();
        if ($authToken->getExpiresAt() < $now) {
            $this->deleteOldToken($authToken->getUser());
            return null;
        }

        if ($authToken->getHash() === $token) {
            return $authToken->getUser();
        }

        return null;
    }

    public function createNewToken(User $user): AuthToken
    {
        if ($user->getAuthToken()) {
            $this->patchToken($user->getAuthToken());

            return $user->getAuthToken();
        }

        $token = AuthTokenFactory::create($user);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function patchToken(AuthToken $token): void
    {
        AuthTokenFactory::patchToken($token);

        $this->entityManager->flush();
    }

    private function deleteOldToken(User $user): void
    {
        $token = $user->getAuthToken();

        if ($token) {
            $this->entityManager->remove($token);
            $this->entityManager->flush();
        }
    }

    public function responseNotLoggedIn(?User $user): ?AbstractResponse
    {
        if (!$user) {
            return new ErrorResponse('You`re not logged in', 401);
        }
        return null;
    }

    public function responseNotAdmin(User $user): ?AbstractResponse
    {
        if (!BetterArray::fromArray($user->getRoles())->contains('ROLE_ADMIN')) {
            return new ErrorResponse('No permission', 403);
        }

        return null;
    }

    public function responseNotLoggedNotAdmin(?User $user): ?AbstractResponse
    {
        $responseNotLoggedIn = $this->responseNotLoggedIn($user);

        if ($responseNotLoggedIn) {
            return $responseNotLoggedIn;
        }

        $responseNotAdmin = $this->responseNotAdmin($user);

        if ($responseNotAdmin) {
            return $responseNotAdmin;
        }

        return null;
    }
}
