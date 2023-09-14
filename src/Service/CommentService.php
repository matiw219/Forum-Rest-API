<?php

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;

class CommentService
{
    public function __construct(
        private readonly CommentRepository $commentRepository
    ) {
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
