<?php

declare(strict_types=1);

namespace App\Paginator;

use Doctrine\ORM\EntityManagerInterface;

class Paginator
{
    final public const DEFAULT_MAX_RESULTS = 10;

    private string $entity;
    private int $page;
    private int $maxResults;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->page = 1;
        $this->maxResults = Paginator::DEFAULT_MAX_RESULTS;
    }

    public function getResults(): array
    {
        $this->page = max(1, $this->page);

        $firstResult = ($this->page - 1) * $this->maxResults;
        $qb = $this->entityManager->getRepository($this->entity)->createQueryBuilder('e');

        return $qb->setFirstResult($firstResult)
            ->setMaxResults($this->maxResults)
            ->getQuery()
            ->getResult();
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
