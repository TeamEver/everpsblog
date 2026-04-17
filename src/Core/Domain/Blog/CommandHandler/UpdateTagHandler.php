<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\TagWriteRepository;

class UpdateTagHandler
{
    /** @var TagWriteRepository */
    private $repository;

    public function __construct(TagWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateTagCommand $command): int
    {
        $this->repository->update($command->getTagId(), $command->getData());

        return $command->getTagId();
    }
}
