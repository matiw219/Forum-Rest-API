<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\Post;
use App\Paginator\Paginator;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryService
{
    public function __construct(
        private readonly Paginator $paginator,
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    public function getAll(Request $request): JsonResponse
    {
        $page = $request->get('page');

        if ($page == null) {
            $categories = $this->getAllCategories();
            $categoriesCount = count($categories);

            return new JsonResponse([
                'info' => [
                    'page' => -1,
                    'maxResults' => $categoriesCount,
                    'results' => $categoriesCount
                ],
                'categories' => $this->formatCategories($this->getAllCategories())
            ], 200);
        }

        $maxResults = (int) $request->get('maxResults', Paginator::DEFAULT_MAX_RESULTS);
        $categories = $this->getCategories($page, $maxResults);

        if (0 === count($categories)) {
            return new JsonResponse([
                'error' => 'Page not found'
            ], 404);
        }

        return new JsonResponse([
            'info' => [
                'page' => $page,
                'maxResults' => $maxResults,
                'results' => count($categories)
            ],
            'categories' => $this->formatCategories($categories)
        ], 200);
    }

    public function get(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return new JsonResponse([
                'error' => 'Category not found'
            ], 404);
        }

        return new JsonResponse([
            'category' => $this->formatCategory($category)
        ]);
    }

    private function getCategories(int $page = 1, int $maxResults = Paginator::DEFAULT_MAX_RESULTS): array
    {
        if ($page == (-1)) {
            return $this->getAllCategories();
        }

        $this->paginator->setEntity(Category::class);
        $this->paginator->setPage($page);
        $this->paginator->setMaxResults($maxResults);

        return $this->paginator->getResults();
    }

    private function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function formatCategory(Category $category): array
    {
        $children = [];
        $posts = [];

        /** @var Category $child */
        foreach ($category->getChildren() as $child) {
            $children[] = $child->getId();
        }

        /** @var Post $post */
        foreach ($category->getPosts() as $post) {
            $posts[] = $post->getId();
        }

        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'createdAt' => $category->getCreatedAt(),
            'createdBy' => ($category->getCreatedBy()?->getId()),
            'parent_id' => ($category->getParent()?->getId()),
            'children' => $children,
            'posts' => $posts
        ];
    }

    private function formatCategories(array $categories): array
    {
        $formattedCategories = [];

        /** @var Category $category */
        foreach ($categories as $category) {
            $formattedCategories[] = $this->formatCategory($category);
        }

        return $formattedCategories;
    }
}
