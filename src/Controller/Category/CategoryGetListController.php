<?php

declare(strict_types=1);

namespace App\Controller\Category;

use App\Paginator\Paginator;
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

    #[Route('/categories', name: 'get_categories', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->get('page');
        $maxResults = (int) $request->get('maxResults', Paginator::DEFAULT_MAX_RESULTS);

        return $this->categoryService->getAll($page, $maxResults)->toJson();
    }
}
