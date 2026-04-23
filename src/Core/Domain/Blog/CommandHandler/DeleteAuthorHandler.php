<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

class DeleteAuthorHandler
{
    /** @var AuthorWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;
    /** @var BlogFrontCacheRelationResolver */
    private $cacheRelationResolver;

    public function __construct(
        AuthorWriteRepository $repository,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogFrontCacheRelationResolver $cacheRelationResolver = null
    )
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->cacheRelationResolver = $cacheRelationResolver ?: new BlogFrontCacheRelationResolver();
    }

    public function __invoke(DeleteAuthorCommand $command): void
    {
        $authorId = $command->getAuthorId();
        $reassignTo = $command->getReassignToAuthorId();
        $affectedPostIds = $this->cacheRelationResolver->listPostIdsByAuthor($authorId);

        if ($reassignTo !== null) {
            if ($reassignTo === $authorId) {
                throw new \InvalidArgumentException('Cannot reassign content to the same author being deleted.');
            }
            $this->repository->reassignPostsAuthor($authorId, $reassignTo);
        } else {
            $postsCount = $this->repository->countPostsForAuthor($authorId);
            if ($postsCount > 0 && $this->repository->countOtherAuthors($authorId) > 0) {
                throw new \RuntimeException(sprintf(
                    'Unable to delete this author: %d post(s) are attached to this author. Reassign them to another author before deletion.',
                    $postsCount
                ));
            }
            if ($postsCount > 0) {
                // No other author available: clear the link so we don't orphan posts to a missing author id.
                $this->repository->clearAuthorForPosts($authorId);
            }
        }

        $this->repository->delete($authorId);
        $this->cacheInvalidator->invalidateAuthorMutation($authorId, $affectedPostIds);
        if ($reassignTo !== null) {
            $this->cacheInvalidator->invalidateAuthorMutation($reassignTo, $affectedPostIds);
        }
    }
}
