<?php

namespace App\Service;

use App\Dto\CommentPatchDto;
use App\Dto\PostPatchDto;
use App\Entity\Comment;
use App\Factory\CommentFactory;
use App\Factory\PostFactory;
use App\Paginator\Paginator;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class CommentService
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly Paginator $paginator,
        private readonly AuthTokenService $authTokenService,
        private readonly PostService $postService,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly UserService $userService
    ) {
    }

    public function getAll(Request $request): JsonResponse
    {
        $page = $request->get('page');

        if ($page == null) {
            $comments = $this->getAllComments();
            $commentsCount = count($comments);

            return new JsonResponse([
                'info' => [
                    'page' => -1,
                    'maxResults' => $commentsCount,
                    'results' => $commentsCount
                ],
                'comments' => $this->formatComments($comments)
            ], 200);
        }


        $maxResults = (int) $request->get('maxResults', Paginator::DEFAULT_MAX_RESULTS);
        $comments = $this->getComments($page, $maxResults);

        if (0 === count($comments)) {
            return new JsonResponse([
                'error' => 'Page not found'
            ], 404);
        }

        return new JsonResponse([
            'info' => [
                'page' => (int) $page,
                'maxResults' => $maxResults,
                'results' => count($comments)
            ],
            'comments' => $this->formatComments($comments)
        ], 200);
    }

    public function get(int $id): JsonResponse
    {
        $comment = $this->findById($id);

        if (!$comment) {
            return new JsonResponse([
                'error' => 'Comment not found'
            ], 404);
        }

        return new JsonResponse([
            'comment' => $this->formatComment($comment)
        ], 200);
    }

    public function getPostComments(int $id): JsonResponse
    {
        $post = $this->postService->findById($id);

        if (!$post) {
            return new JsonResponse([
                'error' => 'Post not found'
            ], 404);
        }

        return new JsonResponse([
            'post' => $this->postService->formatPost($post),
            'count' => count($post->getComments()),
            'comments' => $this->formatComments($post->getComments()->toArray())
        ], 200);
    }

    public function post(Request $request, int $postId): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedInResponse = $this->authTokenService->responseNotLoggedIn($user);

        if ($notLoggedInResponse) {
            return $notLoggedInResponse;
        }

        $post = $this->postService->findById($postId);

        if (!$post) {
            return new JsonResponse([
                'error' => 'Post not found'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['content'])) {
            return new JsonResponse([
                'error' => 'Type comment content'
            ], 400);
        }

        $comment = CommentFactory::create($data['content'], $user, $post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new JsonResponse([
            'comment' => $this->formatComment($comment)
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
                'error' => 'Enter a comment id'
            ], 400);
        }

        /** @var CommentPatchDto $commentPatchDto */
        $commentPatchDto = $this->serializer->deserialize($request->getContent(), CommentPatchDto::class, 'json');
        $comment = $this->findById($commentPatchDto->getId());

        if (!$comment) {
            return new JsonResponse([
                'error' => 'Comment does not exists'
            ], 404);
        }

        if ($commentPatchDto->getCreatedBy()) {
            $newCreator = $this->userService->findUserById($commentPatchDto->getCreatedBy());

            if (!$newCreator) {
                return new JsonResponse([
                    'error' => 'New creator does not exists'
                ], 404);
            }

            $comment->setCreatedBy($newCreator);
        }

        if ($commentPatchDto->getPost()) {
            $newPost = $this->postService->findById($commentPatchDto->getId());

            if (!$newPost) {
                return new JsonResponse([
                    'error' => 'New post does not exists'
                ], 404);
            }

            $comment->setPost($newPost);
        }

        if ($commentPatchDto->getContent()) {
            $comment->setContent($commentPatchDto->getContent());
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'comment' => $this->formatComment($comment)
        ], 202);
    }

    public function remove(Request $request, int $commentId): JsonResponse
    {
        $userToken = $request->headers->get('Authorization');

        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $comment = $this->findById($commentId);

        if (!$comment) {
            return new JsonResponse([
                'error' => 'Comment not found'
            ], 404);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return new JsonResponse([], 204);
    }

    private function getComments(int $page = 1, int $maxResults = Paginator::DEFAULT_MAX_RESULTS): array
    {
        if ($page == (-1)) {
            return $this->getAllComments();
        }

        $this->paginator->setEntity(Comment::class);
        $this->paginator->setPage($page);
        $this->paginator->setMaxResults($maxResults);

        return $this->paginator->getResults();
    }

    public function getAllComments(): ?array
    {
        return $this->commentRepository->findAll();
    }

    public function formatComment(Comment $comment): array
    {
        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'post' => $comment->getPost()->getId(),
            'createdBy' => ($comment->getCreatedBy()?->getId()),
            'createdAt' => $comment->getCreatedAt()
        ];
    }

    public function formatComments(array $comments): array
    {
        $formattedComments = [];

        foreach ($comments as $comment) {
            $formattedComments[] = $this->formatComment($comment);
        }

        return $formattedComments;
    }

    public function findById(int $id): ?Comment
    {
        return $this->commentRepository->find($id);
    }
}
