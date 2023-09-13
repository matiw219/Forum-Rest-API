<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Paginator\Paginator;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryService
{
    public function __construct(
        private readonly Paginator $paginator,
        private readonly CategoryRepository $categoryRepository,
        private readonly AuthTokenService $authTokenService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService $userService
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

    public function post(Request $request): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return new JsonResponse([
                'error' => 'Enter a category name'
            ], 400);
        }

        $name = $data['name'];
        $parentId = 0;

        if (!empty($data['parent'])) {
            $parentId = (int) $data['parent'];
        }

        $category = $this->categoryRepository->findOneBy(['name' => $name]);

        if ($category) {
            return new JsonResponse([
                'error' => 'Category with this name already exists'
            ], 302);
        }

        $parent = null;

        if ($parentId !== 0) {
            $parent = $this->categoryRepository->find($parentId);

            if (!$parent) {
                return new JsonResponse([
                    'error' => 'Typed parent does not exists'
                ], 404);
            }

            if ($parent->getParent()) {
                return new JsonResponse([
                    'error' => 'The given category is already a child of another parent'
                ], 302);
            }
        }

        $category = CategoryFactory::create($name, $parent, $user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new JsonResponse([
            'category' => $this->formatCategory($category)
        ], 201);
    }

    public function patch(Request $request): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['id'])) {
            return new JsonResponse([
                'error' => 'Enter a category id'
            ], 400);
        }

        $category = $this->categoryRepository->find($data['id']);

        if (!$category) {
            return new JsonResponse([
                'error' => 'Category does not exists'
            ], 404);
        }

        $name = null;
        $parentId = null;
        $createdById = null;

        if (!empty($data['name'])) {
            $name = $data['name'];
        }

        if (!empty($data['parent'])) {
            $parentId = $data['parent'];
        }

        if (!empty($data['createdBy'])) {
            $createdById = $data['createdBy'];
        }

        if (!$name && !$parentId && !$createdById) {
            return new JsonResponse([
                'error' => 'No data has been sent for update'
            ], 400);
        }

        if ($parentId) {
            $newParent = $this->categoryRepository->find($parentId);

            if (!$newParent) {
                return new JsonResponse([
                    'error' => 'Parent category does not exists'
                ], 400);
            }

            $category->setParent($newParent);
        } elseif (array_key_exists('parent', $data)) {
            $category->setParent(null);
        }

        if ($createdById) {
            $createdBy = $this->userService->findUserById($createdById);

            if (!$createdBy) {
                return new JsonResponse([
                    'error' => 'Creator does does not exists'
                ], 400);
            }

            $category->setCreatedBy($createdBy);
        } elseif (array_key_exists('createdBy', $data)) {
            $category->setCreatedBy(null);
        }

        if ($name) {
            $searchCategory = $this->categoryRepository->findOneBy(['name' => $name]);

            if ($searchCategory) {
                return new JsonResponse([
                    'error' => 'Category with this name already exists'
                ], 302);
            }

            $category->setName($name);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'category' => $this->formatCategory($category)
        ], 202);
    }

    public function remove(Request $request, int $id): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return new JsonResponse([
                'error' => 'Category not found'
            ], 404);
        }

        /** @var Category $child */
        foreach ($category->getChildren() as $child) {
            $child->setParent(null);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new JsonResponse([], 204);
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

        if ($category->getChildren() != null) {
            /** @var Category $child */
            foreach ($category->getChildren() as $child) {
                $children[] = $child->getId();
            }
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
