<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Service\CategoryService;
use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostGetListController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/posts', name: 'post_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->postService->getAll($request);
    }
}
