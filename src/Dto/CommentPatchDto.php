<?php

declare(strict_types=1);

namespace App\Dto;

class CommentPatchDto
{
    public function __construct(
        private readonly int $id,
        private readonly ?string $content,
        private readonly ?int $createdBy,
        private readonly ?int $post
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getPost(): ?int
    {
        return $this->post;
    }
}
