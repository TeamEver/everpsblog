<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_tag") */
class PostTag
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId = 0;

    /** @ORM\Id @ORM\Column(name="id_ever_post_tag", type="integer") */
    private $tagId = 0;

    public static function create(?int $postId, int $tagId): self
    {
        $self = new self();
        $self->postId = (int) $postId;
        $self->tagId = $tagId;

        return $self;
    }

    public function getPostId(): int
    {
        return (int) $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    public function getTagId(): int
    {
        return (int) $this->tagId;
    }
}
