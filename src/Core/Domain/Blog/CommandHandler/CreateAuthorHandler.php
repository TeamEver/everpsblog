<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;

class CreateAuthorHandler
{
    /** @var AuthorWriteRepository */
    private $repository;

    public function __construct(AuthorWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateAuthorCommand $command): int
    {
        return $this->repository->create($command->getData());
    }
}
