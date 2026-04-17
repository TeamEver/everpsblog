<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_product") */
class PostProduct
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId;

    /** @ORM\Id @ORM\Column(name="id_ever_post_product", type="integer") */
    private $productId;

    public static function create(?int $postId, int $productId): self
    {
        $postProduct = new self();
        $postProduct->postId = $postId;
        $postProduct->productId = $productId;

        return $postProduct;
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
