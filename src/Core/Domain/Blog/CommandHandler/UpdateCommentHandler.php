<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\CommentWriteRepository;

class UpdateCommentHandler
{
    /** @var CommentWriteRepository */
    private $repository;

    public function __construct(CommentWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateCommentCommand $command): int
    {
        $this->repository->update($command->getCommentId(), $command->getData());

        return $command->getCommentId();
    }
}
