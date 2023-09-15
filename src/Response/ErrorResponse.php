<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorResponse extends AbstractResponse
{
    public function __construct(
        private readonly string $error,
        int $code = 400
    ) {
        parent::__construct($code);
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function toJson(): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'code' => $this->getCode(),
                'message' => $this->getError()
            ]
        ], $this->getCode());
    }
}
