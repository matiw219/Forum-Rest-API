<?php

declare(strict_types=1);

namespace App\Controller\Post\Comment;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PostGetCommentsController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[Route('/posts/{id}/comments', name: 'get_post_comments', methods: ['GET'])]
    public function comments(int $id): JsonResponse
    {
        return $this->commentService->getPostComments($id);
    }
}
