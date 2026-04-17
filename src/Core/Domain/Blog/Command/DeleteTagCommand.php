<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

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
