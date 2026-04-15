<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use EverPsBlogPost;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;

class DeletePostHandler
{
    public function __invoke(DeletePostCommand $command): void
    {
        $post = new EverPsBlogPost($command->getPostId());
        $post->delete();
    }
}
