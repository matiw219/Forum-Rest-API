<?php

namespace App\Service;

use App\Dto\PostDto;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Factory\PostFactory;
use App\Paginator\Paginator;
use App\Repository\PostRepository;
use App\Validation\PostValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getAll(Request $request): JsonResponse
    {
        $page = $request->get('page');

        if ($page == null) {
            $posts = $this->getAllPosts();
            $postsCount = count($posts);

            return new JsonResponse([
                'info' => [
                    'page' => -1,
                    'maxResults' => $postsCount,
                    'results' => $postsCount
                ],
                'posts' => $this->formatPosts($posts)
            ], 200);
        }


        $maxResults = (int) $request->get('maxResults', Paginator::DEFAULT_MAX_RESULTS);
        $posts = $this->getPosts($page, $maxResults);

        if (0 === count($posts)) {
            return new JsonResponse([
                'error' => 'Page not found'
            ], 404);
        }

        return new JsonResponse([
            'info' => [
                'page' => (int) $page,
                'maxResults' => $maxResults,
                'results' => count($posts)
            ],
            'posts' => $this->formatPosts($posts)
        ], 200);
    }

    public function get(int $id): JsonResponse
    {
        $post = $this->findById($id);

        if (!$post) {
            return new JsonResponse([
                'error' => 'Post not found'
            ], 404);
        }

        return new JsonResponse([
            'post' => $this->formatPost($post)
        ], 200);
    }

    public function post(Request $request, int $categoryId): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedInResponse = $this->authTokenService->responseNotLoggedIn($user);

        if ($notLoggedInResponse) {
            return $notLoggedInResponse;
        }

        $category = $this->categoryService->findById($categoryId);

        if (!$category) {
            return new JsonResponse([
                'error' => 'Category not found'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return new JsonResponse([
                'error' => 'Type post title'
            ], 400);
        }

        if (empty($data['content'])) {
            return new JsonResponse([
                'error' => 'Type post content'
            ], 400);
        }

        $postDto = $this->serializer->deserialize($request->getContent(), PostDto::class, 'json');
        $this->postValidator->validate($postDto);

        if ($this->postValidator->hasErrors()) {
            return new JsonResponse([
                'error' => $this->postValidator->getErrors()[0],
            ], $this->postValidator->getCode());
        }

        $post = PostFactory::create($postDto, $user, $category);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return new JsonResponse([
            'post' => $this->formatPost($post)
        ], 201);
    }

    public function remove(Request $request, int $id): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $post = $this->findById($id);

        if (!$post) {
            return new JsonResponse([
                'error' => 'Post not found'
            ], 404);
        }

        /** @var Comment $comment */
        foreach ($post->getComments() as $comment) {
            $this->entityManager->remove($comment);
        }

        $this->entityManager->flush();
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return new JsonResponse([], 204);
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
