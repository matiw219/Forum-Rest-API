<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostDeleteController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService
    ) {
    }

    #[Route('/posts/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        return $this->postService->remove($userToken, $id)->toJson();
    }
}
