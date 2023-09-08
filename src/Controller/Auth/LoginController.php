<?php

namespace App\Controller\Auth;

use App\Dto\LoginDto;
use App\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LoginController extends AbstractController
{

    public function __construct(
        private LoginService $loginService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $registrationDto = $this->serializer->deserialize($request->getContent(), LoginDto::class, 'json');
        return $this->loginService->login($registrationDto);
    }
}
