<?php

namespace App\Service;

use App\Entity\Comment;
use App\Paginator\Paginator;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CommentService
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly Paginator $paginator
    ) {
    }

    public function getAll(Request $request): JsonResponse
    {
        $page = $request->get('page');

        if ($page == null) {
            $comments = $this->getAllComments();
            $commentsCount = count($comments);

            return new JsonResponse([
                'info' => [
                    'page' => -1,
                    'maxResults' => $commentsCount,
                    'results' => $commentsCount
                ],
                'comments' => $this->formatComments($comments)
            ], 200);
        }


        $maxResults = (int) $request->get('maxResults', Paginator::DEFAULT_MAX_RESULTS);
        $comments = $this->getComments($page, $maxResults);

        if (0 === count($comments)) {
            return new JsonResponse([
                'error' => 'Page not found'
            ], 404);
        }

        return new JsonResponse([
            'info' => [
                'page' => (int) $page,
                'maxResults' => $maxResults,
                'results' => count($comments)
            ],
            'comments' => $this->formatComments($comments)
        ], 200);
    }

    private function getComments(int $page = 1, int $maxResults = Paginator::DEFAULT_MAX_RESULTS): array
    {
        if ($page == (-1)) {
            return $this->getAllComments();
        }

        $this->paginator->setEntity(Comment::class);
        $this->paginator->setPage($page);
        $this->paginator->setMaxResults($maxResults);

        return $this->paginator->getResults();
    }

    public function getAllComments(): ?array
    {
        return $this->commentRepository->findAll();
    }

    public function formatComment(Comment $comment): array
    {
        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'post' => $comment->getPost()->getId(),
            'createdBy' => ($comment->getCreatedBy()?->getId()),
            'createdAt' => $comment->getCreatedAt()
        ];
    }

    public function formatComments(array $comments): array
    {
        $formattedComments = [];

        foreach ($comments as $comment) {
            $formattedComments[] = $this->formatComment($comment);
        }

        return $formattedComments;
    }

    public function findById(int $id): ?Comment
    {
        return $this->commentRepository->find($id);
    }
}
