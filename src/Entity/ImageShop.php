<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_image_shop") */
class ImageShop
{
    /** @ORM\Id @ORM\Column(name="id_ever_image", type="integer") */
    private $imageId;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
