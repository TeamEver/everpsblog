<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;
use PrestaShop\Module\Everpsblog\Service\BlogInstallService;

class DeleteCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;
    /** @var BlogInstallService */
    private $blogInstallService;

    public function __construct(CategoryWriteRepository $repository, BlogInstallService $blogInstallService)
    {
        $this->repository = $repository;
        $this->blogInstallService = $blogInstallService;
    }

    public function __invoke(DeleteCategoryCommand $command): void
    {
        $categoryId = $command->getCategoryId();
        if ($this->blogInstallService->isProtectedCategoryId($categoryId)) {
            throw new \RuntimeException('Root and Unclassed categories cannot be deleted.');
        }

        $this->repository->delete($categoryId);
    }
}
