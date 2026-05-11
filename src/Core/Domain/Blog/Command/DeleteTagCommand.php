<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

if (!defined('_PS_VERSION_')) {
    exit;
}


class DeleteTagCommand
{
    /** @var int */
    private $tagId;

    public function __construct(int $tagId)
    {
        $this->tagId = $tagId;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }
}
