<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AuthToken;
use App\Entity\User;

class AuthTokenFactory
{
    public static function create(User $user): ?AuthToken
    {
        $token = new AuthToken();
        $token->setUser($user);
        try {
            $token->setHash(bin2hex(random_bytes(32)));
        } catch (\Exception $e) {
            die('Random cannot be found');
        }
        $token->setCreatedAt(new \DateTimeImmutable());

        $expiresAt = new \DateTimeImmutable();
        $expiresAt = $expiresAt->modify('+1 hour');
        $token->setExpiresAt($expiresAt);

        return $token;
    }

    public static function patchToken(AuthToken $token): AuthToken
    {
        try {
            $token->setHash(bin2hex(random_bytes(32)));
        } catch (\Exception $e) {
            die('Random cannot be found');
        }
        $token->setCreatedAt(new \DateTimeImmutable());
        $expiresAt = new \DateTimeImmutable();
        $expiresAt = $expiresAt->modify('+1 hour');
        $token->setExpiresAt($expiresAt);

        return $token;
    }
}
