<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CommentWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}


class DeleteCommentHandler
{
    /** @var CommentWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;
    /** @var BlogFrontCacheRelationResolver */
    private $cacheRelationResolver;

    public function __construct(
        CommentWriteRepository $repository,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogFrontCacheRelationResolver $cacheRelationResolver = null
    )
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->cacheRelationResolver = $cacheRelationResolver ?: new BlogFrontCacheRelationResolver();
    }

    public function __invoke(DeleteCommentCommand $command): void
    {
        $postId = $this->cacheRelationResolver->getCommentPostId($command->getCommentId());
        $this->repository->delete($command->getCommentId());
        $this->cacheInvalidator->invalidateCommentMutation($command->getCommentId(), $postId);
    }
}
