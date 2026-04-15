<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_category_shop") */
class CategoryShop
{
    /** @ORM\Id @ORM\Column(name="id_ever_category", type="integer") */
    private $categoryId;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
