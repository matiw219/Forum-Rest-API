<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractResponse
{
    public function __construct(
        private readonly int $code = 200
    ) {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    abstract public function toJson(): JsonResponse;
}
