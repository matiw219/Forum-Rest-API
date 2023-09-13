<?php

namespace App\Controller\Category\Post;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryPostPostController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/categories/{id}/posts', name: 'post_post', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        return $this->postService->post($request);
    }
}
