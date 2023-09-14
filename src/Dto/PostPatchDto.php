<?php

namespace App\Dto;

class PostPatchDto
{
    public function __construct(
        private readonly int $id,
        private readonly ?int $createdBy,
        private readonly ?int $category,
        private readonly ?string $title,
        private readonly ?string $content,
        private readonly ?int $views
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getCategory(): ?int
    {
        return $this->category;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

}
