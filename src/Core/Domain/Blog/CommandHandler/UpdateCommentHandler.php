<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CommentWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}


class UpdateCommentHandler
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

    public function __invoke(UpdateCommentCommand $command): int
    {
        $previousPostId = $this->cacheRelationResolver->getCommentPostId($command->getCommentId());
        $this->repository->update($command->getCommentId(), $command->getData());
        $this->cacheInvalidator->invalidateCommentMutation($command->getCommentId(), $previousPostId);
        $newPostId = (int) ($command->getData()['id_ever_post'] ?? 0);
        if ($newPostId > 0 && $newPostId !== $previousPostId) {
            $this->cacheInvalidator->invalidateCommentMutation(0, $newPostId);
        }

        return $command->getCommentId();
    }
}
