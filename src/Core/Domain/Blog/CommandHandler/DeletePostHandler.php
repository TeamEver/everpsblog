<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\PostWriteRepository;
use PrestaShop\Module\Everpsblog\Entity\Post;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}


class DeletePostHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var PostWriteRepository */
    private $postWriteRepository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;
    /** @var BlogFrontCacheRelationResolver */
    private $cacheRelationResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostWriteRepository $postWriteRepository,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogFrontCacheRelationResolver $cacheRelationResolver = null
    )
    {
        $this->entityManager = $entityManager;
        $this->postWriteRepository = $postWriteRepository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->cacheRelationResolver = $cacheRelationResolver ?: new BlogFrontCacheRelationResolver();
    }

    public function __invoke(DeletePostCommand $command): void
    {
        /** @var Post|null $post */
        $post = $this->entityManager->getRepository(Post::class)->find($command->getPostId());
        if (null === $post) {
            throw new InvalidArgumentException(sprintf('Post with id %d not found.', $command->getPostId()));
        }

        $beforeSnapshot = $this->cacheRelationResolver->getPostSnapshot((int) $post->getId());
        $this->postWriteRepository->delete($post);
        $this->cacheInvalidator->invalidatePostMutation((int) $command->getPostId(), $beforeSnapshot, []);
    }
}
