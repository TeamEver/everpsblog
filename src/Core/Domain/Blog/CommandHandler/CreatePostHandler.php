<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use EverPsBlogPost;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreatePostCommand;

class CreatePostHandler
{
    /** @var PostRulesApplier */
    private $rulesApplier;

    public function __construct(PostRulesApplier $rulesApplier)
    {
        $this->rulesApplier = $rulesApplier;
    }

    public function __invoke(CreatePostCommand $command): int
    {
        $post = new EverPsBlogPost();
        $this->rulesApplier->apply($post, $command->getData()->toArray());
        $post->save();

        return (int) $post->id;
    }
}
