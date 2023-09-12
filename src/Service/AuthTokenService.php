<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AuthToken;
use App\Entity\User;
use App\Factory\AuthTokenFactory;
use App\Repository\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class AuthTokenService
{
    public function __construct(
        private AuthTokenRepository $tokenRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function loggedInAs(string $token = null): ?User
    {
        if (!$token) {
            return null;
        }

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
}
