<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_category") */
class PostCategory
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId;

    /** @ORM\Id @ORM\Column(name="id_ever_post_category", type="integer") */
    private $categoryId;

    public static function create(?int $postId, int $categoryId): self
    {
        $postCategory = new self();
        $postCategory->postId = $postId;
        $postCategory->categoryId = $categoryId;

        return $postCategory;
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
