<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_tag") */
class PostTag
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId;

    /** @ORM\Id @ORM\Column(name="id_ever_post_tag", type="integer") */
    private $tagId;

    public static function create(?int $postId, int $tagId): self
    {
        $postTag = new self();
        $postTag->postId = $postId;
        $postTag->tagId = $tagId;

        return $postTag;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }
}
