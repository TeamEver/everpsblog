<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;

class DeleteCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;

    public function __construct(CategoryWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteCategoryCommand $command): void
    {
        $this->repository->delete($command->getCategoryId());
    }
}
