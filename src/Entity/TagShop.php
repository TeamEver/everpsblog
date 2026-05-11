<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_tag_shop") */
class TagShop
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Tag", inversedBy="shops") @ORM\JoinColumn(name="id_ever_tag", referencedColumnName="id_ever_tag", nullable=false, onDelete="CASCADE") */
    private $tag;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
