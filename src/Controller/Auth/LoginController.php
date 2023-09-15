<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly LoginService $loginService,
    ) {
    }

    #[Route('/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return $this->loginService->login($request->getContent())->toJson();
    }
}
