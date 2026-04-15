<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use EverPsBlogPost;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdatePostCommand;

class UpdatePostHandler
{
    /** @var PostRulesApplier */
    private $rulesApplier;

    public function __construct(PostRulesApplier $rulesApplier)
    {
        $this->rulesApplier = $rulesApplier;
    }

    public function __invoke(UpdatePostCommand $command): int
    {
        $post = new EverPsBlogPost($command->getPostId());
        $this->rulesApplier->apply($post, $command->getData()->toArray());
        $post->save();

        return (int) $post->id;
    }
}
