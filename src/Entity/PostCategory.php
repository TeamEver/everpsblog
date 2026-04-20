<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_category") */
class PostCategory
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId = 0;

    /** @ORM\Id @ORM\Column(name="id_ever_post_category", type="integer") */
    private $categoryId = 0;

    public static function create(?int $postId, int $categoryId): self
    {
        $self = new self();
        $self->postId = (int) $postId;
        $self->categoryId = $categoryId;

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

    public function getCategoryId(): int
    {
        return (int) $this->categoryId;
    }
}
