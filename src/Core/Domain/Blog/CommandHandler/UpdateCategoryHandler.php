<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;

class UpdateCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;

    public function __construct(CategoryWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateCategoryCommand $command): int
    {
        $this->repository->update($command->getCategoryId(), $command->getData());

        return $command->getCategoryId();
    }
}
