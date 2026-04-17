<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;

class UpdateAuthorHandler
{
    /** @var AuthorWriteRepository */
    private $repository;

    public function __construct(AuthorWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateAuthorCommand $command): int
    {
        $this->repository->update($command->getAuthorId(), $command->getData());

        return $command->getAuthorId();
    }
}
