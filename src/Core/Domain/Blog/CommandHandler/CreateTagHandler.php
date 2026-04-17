<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\TagWriteRepository;

class CreateTagHandler
{
    /** @var TagWriteRepository */
    private $repository;

    public function __construct(TagWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateTagCommand $command): int
    {
        return $this->repository->create($command->getData());
    }
}
