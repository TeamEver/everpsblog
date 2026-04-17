<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\PostWriteRepository;
use PrestaShop\Module\Everpsblog\Entity\Post;

class CreatePostHandler
{
    /** @var PostRulesApplier */
    private $rulesApplier;
    /** @var PostWriteRepository */
    private $postWriteRepository;

    public function __construct(
        PostRulesApplier $rulesApplier,
        PostWriteRepository $postWriteRepository
    ) {
        $this->rulesApplier = $rulesApplier;
        $this->postWriteRepository = $postWriteRepository;
    }

    public function __invoke(CreatePostCommand $command): int
    {
        $post = new Post();
        $relations = $this->rulesApplier->apply($post, $command->getData()->toArray());

        $this->postWriteRepository->save($post, $relations);

        return (int) $post->getId();
    }
}
