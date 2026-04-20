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
        $authorId = $command->getAuthorId();
        $reassignTo = $command->getReassignToAuthorId();

        if ($reassignTo !== null) {
            if ($reassignTo === $authorId) {
                throw new \InvalidArgumentException('Cannot reassign content to the same author being deleted.');
            }
            $this->repository->reassignPostsAuthor($authorId, $reassignTo);
        } else {
            $postsCount = $this->repository->countPostsForAuthor($authorId);
            if ($postsCount > 0 && $this->repository->countOtherAuthors($authorId) > 0) {
                throw new \RuntimeException(sprintf(
                    'Impossible de supprimer cet auteur : %d article(s) lui sont rattachés. Réassignez-les à un autre auteur avant suppression.',
                    $postsCount
                ));
            }
            if ($postsCount > 0) {
                // No other author available: clear the link so we don't orphan posts to a missing author id.
                $this->repository->clearAuthorForPosts($authorId);
            }
        }

        $this->repository->delete($authorId);
    }
}
