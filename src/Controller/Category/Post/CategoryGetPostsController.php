<?php

declare(strict_types=1);

namespace App\Controller\Category\Post;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CategoryGetPostsController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/categories/{id}/posts', name: 'get_category_posts', methods: ['GET'])]
    public function posts(int $id): JsonResponse
    {
        return $this->postService->getCategoryPosts($id)->toJson();
    }
}
