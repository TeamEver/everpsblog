<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_tag_product") */
class TagProduct
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Tag", inversedBy="products") @ORM\JoinColumn(name="id_ever_tag", referencedColumnName="id_ever_tag", nullable=false, onDelete="CASCADE") */
    private $tag;

    /** @ORM\Id @ORM\Column(name="id_ever_tag_product", type="integer") */
    private $productId;
}
