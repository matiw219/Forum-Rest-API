<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
    ) {
    }

    #[Route('/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        return $this->registrationService->register($request->getContent())->toJson();
    }
}
