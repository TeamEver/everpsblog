<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;

class DeleteAuthorHandler
{
    /** @var AuthorWriteRepository */
    private $repository;

    public function __construct(AuthorWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteAuthorCommand $command): void
    {
        $this->repository->delete($command->getAuthorId());
    }
}
