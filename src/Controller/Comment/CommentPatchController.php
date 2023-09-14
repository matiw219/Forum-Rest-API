<?php

namespace App\Controller\Comment;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentPatchController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    #[Route('/comments', name: 'patch_comment', methods: ['PATCH'])]
    public function patch(Request $request): JsonResponse
    {
        return $this->commentService->patch($request);
    }
}
