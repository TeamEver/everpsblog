<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ever_blog_post_shop")
 */
class PostShop
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;

    public static function create(?int $postId, int $shopId): self
    {
        $postShop = new self();
        $postShop->postId = $postId;
        $postShop->shopId = $shopId;

        return $postShop;
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
