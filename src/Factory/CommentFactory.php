<?php

namespace App\Factory;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;

class CommentFactory
{
    public static function create(string $content, User $user, Post $post): Comment
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setPost($post);
        $comment->setCreatedBy($user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        return $comment;
    }
}
