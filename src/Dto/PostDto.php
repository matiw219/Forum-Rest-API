<?php

declare(strict_types=1);

namespace App\Dto;

class PostDto
{
    public function __construct(
        private readonly string $title,
        private readonly string $content
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
