<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_author_product") */
class AuthorProduct
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Author", inversedBy="products") @ORM\JoinColumn(name="id_ever_author", referencedColumnName="id_ever_author", nullable=false, onDelete="CASCADE") */
    private $author;

    /** @ORM\Id @ORM\Column(name="id_ever_author_product", type="integer") */
    private $productId;
}
