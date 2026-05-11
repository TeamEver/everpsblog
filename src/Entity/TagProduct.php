<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_tag_product") */
class TagProduct
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Tag", inversedBy="products") @ORM\JoinColumn(name="id_ever_tag", referencedColumnName="id_ever_tag", nullable=false, onDelete="CASCADE") */
    private $tag;

    /** @ORM\Id @ORM\Column(name="id_ever_tag_product", type="integer") */
    private $productId;
}
