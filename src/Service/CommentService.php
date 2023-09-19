<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CommentPatchDto;
use App\Entity\Comment;
use App\Factory\CommentFactory;
use App\Paginator\Paginator;
use App\Repository\CommentRepository;
use App\Response\AbstractResponse;
use App\Response\CustomResponse;
use App\Response\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;
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

    public function getAll(?int $page, ?int $maxResults): AbstractResponse
    {
        if ($page == null) {
            $comments = $this->getAllComments();
            $commentsCount = count($comments);

            return new CustomResponse([
                'paginator' => [
                    'page' => -1,
                    'maxResults' => $commentsCount,
                    'results' => $commentsCount
                ],
                'docs' => $this->formatComments($this->getAllComments())
            ]);
        }

        $comments = $this->getComments($page, $maxResults);

        if (0 === count($comments)) {
            return new ErrorResponse('Page not found', 404);
        }

        return new CustomResponse([
            'paginator' => [
                'page' => -1,
                'maxResults' => $maxResults,
                'results' => count($comments)
            ],
            'docs' => $this->formatComments($this->getAllComments())
        ]);
    }

    public function get(int $id): AbstractResponse
    {
        $comment = $this->findById($id);

        if (!$comment) {
            return new ErrorResponse('Comment not found', 404);
        }
        return new CustomResponse([
            'comment' => $this->formatComment($comment)
        ]);
    }

    public function getPostComments(int $id): AbstractResponse
    {
        $post = $this->postService->findById($id);

        if (!$post) {
            return new ErrorResponse('Post not found', 404);
        }

        return new CustomResponse([
            'post' => $this->postService->formatPost($post),
            'docs' => $this->formatComments($post->getComments()->toArray())
        ]);
    }

    public function post(string $userToken, $content, int $postId): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedInResponse = $this->authTokenService->responseNotLoggedIn($user);

        if ($notLoggedInResponse) {
            return $notLoggedInResponse;
        }

        $post = $this->postService->findById($postId);

        if (!$post) {
            return new ErrorResponse('Post not found', 404);
        }

        $data = json_decode($content, true);

        if (empty($data['content'])) {
            return new ErrorResponse('Post content is required', 400);
        }

        $comment = CommentFactory::create($data['content'], $user, $post);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new CustomResponse(['comment' => $this->formatComment($comment)]);
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
            return new ErrorResponse('Comment id is required', 400);
        }

        /** @var CommentPatchDto $commentPatchDto */
        $commentPatchDto = $this->serializer->deserialize($content, CommentPatchDto::class, 'json');
        $comment = $this->findById($commentPatchDto->getId());

        if (!$comment) {
            return new ErrorResponse('Comment not found', 404);
        }

        if ($commentPatchDto->getCreatedBy()) {
            $newCreator = $this->userService->findUserById($commentPatchDto->getCreatedBy());

            if (!$newCreator) {
                return new ErrorResponse('New creator not found', 404);
            }

            $comment->setCreatedBy($newCreator);
        }

        if ($commentPatchDto->getPost()) {
            $newPost = $this->postService->findById($commentPatchDto->getId());

            if (!$newPost) {
                return new ErrorResponse('New post not found', 404);
            }

            $comment->setPost($newPost);
        }

        if ($commentPatchDto->getContent()) {
            $comment->setContent($commentPatchDto->getContent());
        }

        $this->entityManager->flush();
        return new CustomResponse(['comment' => $this->formatComment($comment)]);
    }

    public function remove(string $userToken, int $commentId): AbstractResponse
    {
        $user = $this->authTokenService->loggedInAs($userToken);
        $notLoggedNotAdminResponse = $this->authTokenService->responseNotLoggedNotAdmin($user);

        if ($notLoggedNotAdminResponse) {
            return $notLoggedNotAdminResponse;
        }

        $comment = $this->findById($commentId);

        if (!$comment) {
            return new ErrorResponse('Comment not found', 404);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return new CustomResponse([], 204);
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
