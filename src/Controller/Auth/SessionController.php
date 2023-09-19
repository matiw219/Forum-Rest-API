<?php

namespace App\Controller\Auth;

use App\Service\AuthTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    public function __construct(
        private readonly AuthTokenService $authTokenService
    ) {
    }

    #[Route('/auth/session', name: 'app_auth_session', methods: 'POST')]
    public function check(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['user'] ?? null;
        $hash = $data['hash'] ?? null;

        return $this->authTokenService->session($id, $hash)->toJson();
    }
}
