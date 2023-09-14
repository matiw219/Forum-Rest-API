<?php

namespace App\Controller\Post;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PostDeleteController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/posts/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return $this->postService->remove($id);
    }
}
