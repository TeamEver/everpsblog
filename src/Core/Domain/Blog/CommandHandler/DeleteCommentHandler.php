<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CommentWriteRepository;

class DeleteCommentHandler
{
    /** @var CommentWriteRepository */
    private $repository;

    public function __construct(CommentWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteCommentCommand $command): void
    {
        $this->repository->delete($command->getCommentId());
    }
}
