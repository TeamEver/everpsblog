<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CommentWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

class CreateCommentHandler
{
    /** @var CommentWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(CommentWriteRepository $repository, ?BlogFrontCacheInvalidator $cacheInvalidator = null)
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function __invoke(CreateCommentCommand $command): int
    {
        $commentId = $this->repository->create($command->getData());
        $this->cacheInvalidator->invalidateCommentMutation($commentId, (int) ($command->getData()['id_ever_post'] ?? 0));

        return $commentId;
    }
}
