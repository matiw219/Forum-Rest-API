<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Array\BetterArray;
use App\Entity\AuthToken;
use App\Entity\User;
use App\Factory\AuthTokenFactory;
use App\Repository\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthTokenService
{
    public function __construct(
        private AuthTokenRepository $tokenRepository,
        private EntityManagerInterface $entityManager,
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

    public function responseNotLoggedIn(?User $user): ?JsonResponse
    {
        if (!$user) {
            return new JsonResponse([
                'error' => 'You`re not logged in'
            ], 401);
        }
        return null;
    }

    public function responseNotAdmin(User $user): ?JsonResponse
    {
        if (!BetterArray::fromArray($user->getRoles())->contains('ROLE_ADMIN')) {
            return new JsonResponse([
                'error' => 'No permission'
            ], 403);
        }

        return null;
    }

    public function responseNotLoggedNotAdmin(?User $user): ?JsonResponse
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
