<?php

declare(strict_types=1);

namespace App\Controller\Category;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryPatchController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {
    }

    #[Route('/categories', name: 'patch_category', methods: ['PATCH'])]
    public function patch(Request $request): JsonResponse
    {
        return $this->categoryService->patch($request);
    }
}
