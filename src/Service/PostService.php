<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\PostDto;
use App\Dto\PostPatchDto;
use App\Entity\Comment;
use App\Entity\Post;
use App\Factory\PostFactory;
use App\Paginator\Paginator;
use App\Repository\PostRepository;
use App\Response\AbstractResponse;
use App\Response\CustomResponse;
use App\Response\ErrorResponse;
use App\Validation\PostValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly Paginator $paginator,
        private readonly AuthTokenService $authTokenService,
        private readonly SerializerInterface $serializer,
        private readonly PostValidator $postValidator,
        private readonly CategoryService $categoryService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService $userService
    ) {
    }

    public function getAll(?int $page, ?int $maxResults): AbstractResponse
    {

        if ($page == null) {
            $posts = $this->getAllPosts();
            $postsCount = count($posts);

            return new CustomResponse([
                'paginator' => [
                    'page' => -1,
                    'maxResults' => $postsCount,
                    'results' => $postsCount
                ],
                'docs' => $this->formatPosts($this->getAllPosts())
            ]);
        }

        $posts = $this->getPosts($page, $maxResults);

        if (0 === count($posts)) {
            return new ErrorResponse('Page not found', 404);
        }

        return new CustomResponse([
            'paginator' => [
                'page' => -1,
                'maxResults' => $maxResults,
                'results' => count($posts)
            ],
            'docs' => $this->formatPosts($posts)
        ]);
    }

    public function get(int $id): AbstractResponse
    {
        $post = $this->findById($id);

        if (!$post) {
            return new ErrorResponse('Post not found', 404);
        }

        return new CustomResponse(['post' => $this->formatPost($post)]);
    }

    public function getCategoryPosts(int $categoryId): AbstractResponse
    {
        $category = $this->categoryService->findById($categoryId);

        if (!$category) {
            return new ErrorResponse('Category not found', 404);
        }

        return new CustomResponse([
            'category' => $this->categoryService->formatCategory($category),
            'docs' => $this->formatPosts($category->getPosts()->toArray())
        ], 200);
    }

    public function post(string $userToken, $content, int $categoryId): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedInResponse = $this->authTokenService->responseNotLoggedIn($user);

        if ($notLoggedInResponse) {
            return $notLoggedInResponse;
        }

        $category = $this->categoryService->findById($categoryId);

        if (!$category) {
            return new ErrorResponse('Category not found', 404);
        }

        $data = json_decode($content, true);

        if (empty($data['title'])) {
            return new ErrorResponse('Post title is required', 400);
        }

        if (empty($data['content'])) {
            return new ErrorResponse('Post content is required', 400);
        }

        $postDto = $this->serializer->deserialize($content, PostDto::class, 'json');
        $this->postValidator->validate($postDto);

        if ($this->postValidator->hasErrors()) {
            return new ErrorResponse($this->postValidator->getErrors()[0], $this->postValidator->getCode());
        }

        $post = PostFactory::create($postDto, $user, $category);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return new CustomResponse(['post' => $this->formatPost($post)], 201);
    }

    public function remove(string $userToken, int $id): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $post = $this->findById($id);

        if (!$post) {
            return new ErrorResponse('Post not found', 404);
        }

        /** @var Comment $comment */
        foreach ($post->getComments() as $comment) {
            $this->entityManager->remove($comment);
        }

        $this->entityManager->flush();
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return new CustomResponse([], 204);
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
            return new ErrorResponse('Post id is required', 400);
        }

        /** @var PostPatchDto $postPatchDto */
        $postPatchDto = $this->serializer->deserialize($content, PostPatchDto::class, 'json');
        $post = $this->findById($postPatchDto->getId());

        if (!$post) {
            return new ErrorResponse('Post not found', 404);
        }

        if ($postPatchDto->getCreatedBy()) {
            $newCreator = $this->userService->findUserById($postPatchDto->getCreatedBy());

            if (!$newCreator) {
                return new ErrorResponse('New creator not found', 404);
            }

            $post->setCreatedBy($newCreator);
        }

        if ($postPatchDto->getCategory()) {
            $category = $this->categoryService->findById($postPatchDto->getCategory());

            if (!$category) {
                return new ErrorResponse('New category not found', 404);
            }

            $post->setCategory($category);
        }

        PostFactory::patchPost($post, $postPatchDto);
        $this->entityManager->flush();

        return new CustomResponse(['post' => $this->formatPost($post)], 202);
    }

    private function getPosts(int $page = 1, int $maxResults = Paginator::DEFAULT_MAX_RESULTS): array
    {
        if ($page == (-1)) {
            return $this->getAllPosts();
        }

        $this->paginator->setEntity(Post::class);
        $this->paginator->setPage($page);
        $this->paginator->setMaxResults($maxResults);

        return $this->paginator->getResults();
    }

    public function getAllPosts(): ?array
    {
        return $this->postRepository->findAll();
    }

    public function formatPost(Post $post): array
    {
        $comments = [];

        if ($post->getComments() != null) {
            /** @var Comment $comment */
            foreach ($post->getComments() as $comment) {
                $comments[] = $comment->getId();
            }
        }

        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'views' => $post->getViews(),
            'createdAt' => $post->getCreatedAt(),
            'createdBy' => ($post->getCreatedBy()?->getId()),
            'category' => $post->getCategory()->getId(),
            'comments' => $comments
        ];
    }

    private function formatPosts(array $posts): array
    {
        $formattedPosts = [];

        /** @var Post $post */
        foreach ($posts as $post) {
            $formattedPosts[] = $this->formatPost($post);
        }

        return $formattedPosts;
    }

    public function findById(int $id): ?Post
    {
        return $this->postRepository->find($id);
    }
}
