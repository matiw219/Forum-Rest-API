<?php

namespace App\Factory;

use App\Dto\PostDto;
use App\Dto\PostPatchDto;
use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;

class PostFactory
{
    public static function create(PostDto $postDto, User $user, Category $category): Post
    {
        $post = new Post();
        $post->setTitle($postDto->getTitle());
        $post->setContent($postDto->getContent());
        $post->setViews(0);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setCreatedBy($user);
        $post->setCategory($category);

        return $post;
    }

    public static function patchPost(Post $post, PostPatchDto $postPatchDto): void
    {
        if ($postPatchDto->getTitle()) {
            $post->setTitle($postPatchDto->getTitle());
        }

        if ($postPatchDto->getContent()) {
            $post->setContent($postPatchDto->getContent());
        }

        if ($postPatchDto->getViews()) {
            $post->setViews($postPatchDto->getViews());
        }
    }
}
