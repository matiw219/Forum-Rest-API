<?php

namespace App\Controller\Comment;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommentGetSingleController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[Route('/comments/{id}', name: 'get_comment', methods: ['GET'])]
    public function getSingle(int $id): JsonResponse
    {
        return $this->commentService->get($id);
    }
}
