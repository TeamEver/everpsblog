<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;
use PrestaShop\Module\Everpsblog\Service\BlogInstallService;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}


class DeleteCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;
    /** @var BlogInstallService */
    private $blogInstallService;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;
    /** @var BlogFrontCacheRelationResolver */
    private $cacheRelationResolver;

    public function __construct(
        CategoryWriteRepository $repository,
        BlogInstallService $blogInstallService,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogFrontCacheRelationResolver $cacheRelationResolver = null
    )
    {
        $this->repository = $repository;
        $this->blogInstallService = $blogInstallService;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->cacheRelationResolver = $cacheRelationResolver ?: new BlogFrontCacheRelationResolver();
    }

    public function __invoke(DeleteCategoryCommand $command): void
    {
        $categoryId = $command->getCategoryId();
        if ($this->blogInstallService->isProtectedCategoryId($categoryId)) {
            throw new \RuntimeException('Root and Unclassed categories cannot be deleted.');
        }

        $beforeSnapshot = $this->cacheRelationResolver->getCategorySnapshot($categoryId);
        $this->repository->delete($categoryId);
        $this->cacheInvalidator->invalidateCategoryMutation($categoryId, $beforeSnapshot, []);
    }
}
