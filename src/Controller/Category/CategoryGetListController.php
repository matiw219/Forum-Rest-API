<?php

declare(strict_types=1);

namespace App\Controller\Category;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryGetListController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {
    }

    #[Route('/categories', name: 'category_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->categoryService->getAll($request);
    }
}
