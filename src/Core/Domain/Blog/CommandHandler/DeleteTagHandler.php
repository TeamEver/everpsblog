<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\TagWriteRepository;

class DeleteTagHandler
{
    /** @var TagWriteRepository */
    private $repository;

    public function __construct(TagWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteTagCommand $command): void
    {
        $this->repository->delete($command->getTagId());
    }
}
