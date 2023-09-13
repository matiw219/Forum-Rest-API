<?php

namespace App\Controller\Post;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PostGetSingleController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/posts/{id}', name: 'get_post', methods: ['GET'])]
    public function getSingle(int $id): JsonResponse
    {
        return $this->postService->get($id);
    }
}
