<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\PostWriteRepository;
use PrestaShop\Module\Everpsblog\Entity\Post;

class DeletePostHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var PostWriteRepository */
    private $postWriteRepository;

    public function __construct(EntityManagerInterface $entityManager, PostWriteRepository $postWriteRepository)
    {
        $this->entityManager = $entityManager;
        $this->postWriteRepository = $postWriteRepository;
    }

    public function __invoke(DeletePostCommand $command): void
    {
        /** @var Post|null $post */
        $post = $this->entityManager->getRepository(Post::class)->find($command->getPostId());
        if (null === $post) {
            throw new InvalidArgumentException(sprintf('Post with id %d not found.', $command->getPostId()));
        }

        $this->postWriteRepository->delete($post);
    }
}
