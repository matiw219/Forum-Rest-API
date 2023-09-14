<?php

namespace App\Controller\Comment;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentGetListController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[Route('/comments', name: 'comment_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->commentService->getAll($request);
    }
}
