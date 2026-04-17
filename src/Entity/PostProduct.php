<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_product") */
class PostProduct
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Post", inversedBy="postProducts") @ORM\JoinColumn(name="id_ever_post", referencedColumnName="id_ever_post", nullable=false, onDelete="CASCADE") */
    private $post;

    /** @ORM\Id @ORM\Column(name="id_ever_post_product", type="integer") */
    private $productId;
}
