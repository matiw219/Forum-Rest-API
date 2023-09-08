<?php

namespace App\Service;

use App\Entity\AuthToken;
use App\Entity\User;
use App\Repository\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class AuthTokenService
{

    public function __construct(
        private AuthTokenRepository $tokenRepository,
        private EntityManagerInterface $entityManager,
    ){
    }

    public function loggedInAs(?string $token = null) : ?User
    {
        if (!$token) {
            return null;
        }

        $authToken = $this->tokenRepository->findOneBy(['token' => $token]);
        if (!$authToken) {
            return null;
        }

        $now = new \DateTimeImmutable();
        if ($authToken->getExpiresAt() < $now) {
            $this->deleteOldToken($authToken->getUser());
            return null;
        }

        if ($authToken->getToken() === $token) {
            return $authToken->getUser();
        }

        return null;
    }

    public function createNewToken(User $user) : AuthToken
    {
        if ($user->getAuthToken()) {
            $this->patchToken($user->getAuthToken());

            return $user->getAuthToken();
        }

        $token = $this->makeToken($user);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    private function makeToken(User $user) : AuthToken
    {
        $token = new AuthToken();
        $token->setUser($user);
        $token->setToken(bin2hex(random_bytes(32)));
        $token->setCreatedAt(new \DateTimeImmutable());
        
        $expiresAt = new \DateTimeImmutable();
        $expiresAt = $expiresAt->modify('+1 hour');
        $token->setExpiresAt($expiresAt);
        
        return $token;
    }

    public function patchToken(AuthToken $token) : void {
        $token->setToken(bin2hex(random_bytes(32)));
        $token->setCreatedAt(new \DateTimeImmutable());

        $expiresAt = new \DateTimeImmutable();
        $expiresAt = $expiresAt->modify('+1 hour');
        $token->setExpiresAt($expiresAt);

        $this->entityManager->flush();
    }
    
    private function deleteOldToken(User $user) : void
    {
        $token = $user->getAuthToken();

        if ($token) {
            $this->entityManager->remove($token);
            $this->entityManager->flush();
        }
    }

}