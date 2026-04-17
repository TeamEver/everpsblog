<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_shop") */
class PostShop
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Post", inversedBy="shops") @ORM\JoinColumn(name="id_ever_post", referencedColumnName="id_ever_post", nullable=false, onDelete="CASCADE") */
    private $post;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
