<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\PostWriteRepository;
use PrestaShop\Module\Everpsblog\Entity\Post;

class UpdatePostHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var PostRulesApplier */
    private $rulesApplier;
    /** @var PostWriteRepository */
    private $postWriteRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostRulesApplier $rulesApplier,
        PostWriteRepository $postWriteRepository
    ) {
        $this->entityManager = $entityManager;
        $this->rulesApplier = $rulesApplier;
        $this->postWriteRepository = $postWriteRepository;
    }

    public function __invoke(UpdatePostCommand $command): int
    {
        /** @var Post|null $post */
        $post = $this->entityManager->getRepository(Post::class)->find($command->getPostId());
        if (null === $post) {
            throw new InvalidArgumentException(sprintf('Post with id %d not found.', $command->getPostId()));
        }

        $relations = $this->rulesApplier->apply($post, $command->getData()->toArray());
        $this->postWriteRepository->save($post, $relations);

        return (int) $post->getId();
    }
}
