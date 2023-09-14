<?php

namespace App\Controller\Post;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostPatchController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/posts', name: 'patch_post', methods: ['PATCH'])]
    public function patch(Request $request): JsonResponse
    {
        return $this->postService->patch($request);
    }
}
