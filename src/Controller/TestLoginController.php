<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ONLY FOR CHECK
 */
class TestLoginController extends AbstractController
{
    public function __construct(
        private AuthTokenService $tokenService
    ) {
    }

    #[Route('/check/{token}', name: 'app_check', methods: ['GET'])]
    public function index(string $token): JsonResponse
    {
        $user = $this->tokenService->loggedInAs($token);

        if (!$user) {
            return new JsonResponse(['You`re not logged in'], 404);
        }

        return new JsonResponse(['You`re logged in']);
    }
}
