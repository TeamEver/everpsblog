<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_post_product") */
class PostProduct
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId = 0;

    /** @ORM\Id @ORM\Column(name="id_ever_post_product", type="integer") */
    private $productId = 0;

    public static function create(?int $postId, int $productId): self
    {
        $self = new self();
        $self->postId = (int) $postId;
        $self->productId = $productId;

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

    public function getProductId(): int
    {
        return (int) $this->productId;
    }
}
