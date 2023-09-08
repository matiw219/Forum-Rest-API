<?php

namespace App\Controller\Auth;

use App\Dto\RegistrationDto;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterController extends AbstractController
{

    public function __construct(
        private RegistrationService $registrationService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $registrationDto = $this->serializer->deserialize($request->getContent(), RegistrationDto::class, 'json');
        return $this->registrationService->registerPost($registrationDto);
    }
}
