<?php

declare(strict_types=1);

namespace App\Controller\Category;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {
    }

    #[Route('/categories/{id}', name: 'app_get_category', methods: ['GET'])]
    public function index(int $id): JsonResponse
    {
        return $this->categoryService->get($id);
    }
}
