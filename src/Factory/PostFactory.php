<?php

namespace App\Factory;

use App\Dto\PostDto;
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
}
