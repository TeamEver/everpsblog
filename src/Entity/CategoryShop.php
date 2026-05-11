<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_category_shop") */
class CategoryShop
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Category", inversedBy="shops") @ORM\JoinColumn(name="id_ever_category", referencedColumnName="id_ever_category", nullable=false, onDelete="CASCADE") */
    private $category;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
