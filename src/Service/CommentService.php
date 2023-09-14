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

    public function findById(int $id): ?Comment
    {
        return $this->commentRepository->find($id);
    }
}
