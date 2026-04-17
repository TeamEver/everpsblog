<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_category_product") */
class CategoryProduct
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Category", inversedBy="products") @ORM\JoinColumn(name="id_ever_category", referencedColumnName="id_ever_category", nullable=false, onDelete="CASCADE") */
    private $category;

    /** @ORM\Id @ORM\Column(name="id_ever_category_product", type="integer") */
    private $productId;
}
