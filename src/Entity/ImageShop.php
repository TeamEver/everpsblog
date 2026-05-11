<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}


/** @ORM\Entity @ORM\Table(name="ever_blog_image_shop") */
class ImageShop
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Image", inversedBy="shops") @ORM\JoinColumn(name="id_ever_image", referencedColumnName="id_ever_image", nullable=false, onDelete="CASCADE") */
    private $image;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
