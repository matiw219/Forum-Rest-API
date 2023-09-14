<?php

namespace App\Controller\Post\Comment;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostPostCommentController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[Route('/posts/{id}/comments', name: 'post_comment', methods: ['POST'])]
    public function index(Request $request, int $id): JsonResponse
    {
        return $this->commentService->post($request, $id);
    }
}
