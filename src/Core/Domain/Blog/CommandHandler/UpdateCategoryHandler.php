<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}


class UpdateCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;
    /** @var BlogFrontCacheRelationResolver */
    private $cacheRelationResolver;

    public function __construct(
        CategoryWriteRepository $repository,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogFrontCacheRelationResolver $cacheRelationResolver = null
    )
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->cacheRelationResolver = $cacheRelationResolver ?: new BlogFrontCacheRelationResolver();
    }

    public function __invoke(UpdateCategoryCommand $command): int
    {
        $beforeSnapshot = $this->cacheRelationResolver->getCategorySnapshot($command->getCategoryId());
        $this->repository->update($command->getCategoryId(), $command->getData());
        $this->cacheInvalidator->invalidateCategoryMutation(
            $command->getCategoryId(),
            $beforeSnapshot,
            ['parent_id' => (int) ($command->getData()['id_parent_category'] ?? 0)]
        );

        return $command->getCategoryId();
    }
}
