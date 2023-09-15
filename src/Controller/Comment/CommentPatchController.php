<?php

declare(strict_types=1);

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
        $userToken = $request->headers->get('Authorization');
        $content = $request->getContent();

        return $this->commentService->patch($userToken, $content)->toJson();
    }
}
