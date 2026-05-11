<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

if (!defined('_PS_VERSION_')) {
    exit;
}


class CreateCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(CategoryWriteRepository $repository, ?BlogFrontCacheInvalidator $cacheInvalidator = null)
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function __invoke(CreateCategoryCommand $command): int
    {
        $categoryId = $this->repository->create($command->getData());
        $this->cacheInvalidator->invalidateCategoryMutation(
            $categoryId,
            [],
            ['parent_id' => (int) ($command->getData()['id_parent_category'] ?? 0)]
        );

        return $categoryId;
    }
}
