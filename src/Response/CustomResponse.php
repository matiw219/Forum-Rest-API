<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class CustomResponse extends AbstractResponse
{
    public function __construct(
        private readonly array $data,
        int $code = 200
    ) {
        parent::__construct($code);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toJson(): JsonResponse
    {
        return new JsonResponse($this->getData(), $this->getCode());
    }
}
