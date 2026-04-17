<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CategoryWriteRepository;

class CreateCategoryHandler
{
    /** @var CategoryWriteRepository */
    private $repository;

    public function __construct(CategoryWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateCategoryCommand $command): int
    {
        return $this->repository->create($command->getData());
    }
}
