<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_post_shop") */
class PostShop
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId = 0;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId = 0;

    public static function create(?int $postId, int $shopId): self
    {
        $self = new self();
        $self->postId = (int) $postId;
        $self->shopId = $shopId;

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

    public function getShopId(): int
    {
        return (int) $this->shopId;
    }
}
