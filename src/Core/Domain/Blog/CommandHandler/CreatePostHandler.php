<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\PostWriteRepository;
use PrestaShop\Module\Everpsblog\Entity\Post;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

if (!defined('_PS_VERSION_')) {
    exit;
}


class CreatePostHandler
{
    /** @var PostRulesApplier */
    private $rulesApplier;
    /** @var PostWriteRepository */
    private $postWriteRepository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(
        PostRulesApplier $rulesApplier,
        PostWriteRepository $postWriteRepository,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null
    ) {
        $this->rulesApplier = $rulesApplier;
        $this->postWriteRepository = $postWriteRepository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function __invoke(CreatePostCommand $command): int
    {
        $post = new Post();
        $relations = $this->rulesApplier->apply($post, $command->getData()->toArray());

        $this->postWriteRepository->save($post, $relations);
        $this->cacheInvalidator->invalidatePostMutation((int) $post->getId());

        return (int) $post->getId();
    }
}
