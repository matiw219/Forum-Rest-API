<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Paginator\Paginator;
use App\Repository\CategoryRepository;
use App\Response\AbstractResponse;
use App\Response\CustomResponse;
use App\Response\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;

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

    public function getAll(?int $page, ?int $maxResults): AbstractResponse
    {
        if ($page == null) {
            $categories = $this->getAllCategories();
            $categoriesCount = count($categories);

            return new CustomResponse([
                'paginator' => [
                    'page' => -1,
                    'maxResults' => $categoriesCount,
                    'results' => $categoriesCount
                ],
                'docs' => $this->formatCategories($this->getAllCategories())
            ]);
        }

        $categories = $this->getCategories($page, $maxResults);

        if (0 === count($categories)) {
            return new ErrorResponse('Page not found', 404);
        }

        return new CustomResponse([
            'paginator' => [
                'page' => $page,
                'maxResults' => $maxResults,
                'results' => count($categories)
            ],
            'docs' => $this->formatCategories($categories)
        ]);
    }

    public function get(int $id): AbstractResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return new ErrorResponse('Category not found', 404);
        }

        return new CustomResponse(['category' => $this->formatCategory($category)]);
    }

    public function post(string $userToken, $content): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $data = json_decode($content, true);

        if (empty($data['name'])) {
            return new ErrorResponse('Category name not found', 400);
        }

        $name = $data['name'];
        $parentId = 0;

        if (!empty($data['parent'])) {
            $parentId = (int) $data['parent'];
        }

        $category = $this->categoryRepository->findOneBy(['name' => $name]);

        if ($category) {
            return new ErrorResponse('Category with this name already exists');
        }

        $parent = null;

        if ($parentId !== 0) {
            $parent = $this->categoryRepository->find($parentId);

            if (!$parent) {
                return new ErrorResponse('Parent category not found', 404);
            }

            if ($parent->getParent()) {
                return new ErrorResponse('Parent category is already a child of another category');
            }
        }

        $category = CategoryFactory::create($name, $parent, $user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new CustomResponse(['category' => $this->formatCategory($category)], 201);
    }

    public function patch(string $userToken, $content): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $data = json_decode($content, true);

        if (empty($data['id'])) {
            return new ErrorResponse('Category id not found', 400);
        }

        $category = $this->categoryRepository->find($data['id']);

        if (!$category) {
            return new ErrorResponse('Category not found', 404);
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
            return new ErrorResponse('No data has been sent for update', 400);
        }

        if ($parentId) {
            $newParent = $this->categoryRepository->find($parentId);

            if (!$newParent) {
                return new ErrorResponse('Parent category not found', 404);
            }

            $category->setParent($newParent);
        } elseif (array_key_exists('parent', $data)) {
            $category->setParent(null);
        }

        if ($createdById) {
            $createdBy = $this->userService->findUserById($createdById);

            if (!$createdBy) {
                return new ErrorResponse('New creator not found', 404);
            }

            $category->setCreatedBy($createdBy);
        } elseif (array_key_exists('createdBy', $data)) {
            $category->setCreatedBy(null);
        }

        if ($name) {
            $searchCategory = $this->categoryRepository->findOneBy(['name' => $name]);

            if ($searchCategory) {
                return new ErrorResponse('Category with this name already exists', 302);
            }

            $category->setName($name);
        }

        $this->entityManager->flush();

        return new CustomResponse(['category' => $this->formatCategory($category)], 202);
    }

    public function remove(string $userToken, int $id): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return new ErrorResponse('Category not found', 404);
        }

        /** @var Category $child */
        foreach ($category->getChildren() as $child) {
            $child->setParent(null);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new CustomResponse([], 204);
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
            'parent' => ($category->getParent()?->getId()),
            'children' => $children,
            'posts' => $posts
        ];
    }

    public function formatCategories(array $categories): array
    {
        $formattedCategories = [];

        /** @var Category $category */
        foreach ($categories as $category) {
            $formattedCategories[] = $this->formatCategory($category);
        }

        return $formattedCategories;
    }

    public function findById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }
}
