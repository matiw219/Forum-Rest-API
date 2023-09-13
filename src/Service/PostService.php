<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Paginator\Paginator;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly Paginator $paginator,
        private readonly AuthTokenService $authTokenService
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
                ]
            ], 200);
        }


        $maxResults = (int) $request->get('maxResults', Paginator::DEFAULT_MAX_RESULTS);
        $categories = $this->getPosts($page, $maxResults);

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
            'categories' => $this->formatPosts($categories)
        ], 200);
    }


    public function post(Request $request): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedInResponse = $this->authTokenService->responseNotLoggedIn($user);

        if ($notLoggedInResponse) {
            return $notLoggedInResponse;
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
            ]);
        }

    }

    private function getPosts(int $page = 1, int $maxResults = Paginator::DEFAULT_MAX_RESULTS): array
    {
        if ($page == (-1)) {
            return $this->getAllPosts();
        }

        $this->paginator->setEntity(Category::class);
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
}
