<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CommentWriteRepository;

class CreateCommentHandler
{
    /** @var CommentWriteRepository */
    private $repository;

    public function __construct(CommentWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateCommentCommand $command): int
    {
        return $this->repository->create($command->getData());
    }
}
