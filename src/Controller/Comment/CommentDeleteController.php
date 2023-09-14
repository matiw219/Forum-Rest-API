<?php

namespace App\Controller\Comment;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentDeleteController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[Route('/comments/{id}', name: 'delete_comment', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        return $this->commentService->remove($request, $id);
    }
}
