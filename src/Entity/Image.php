<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\ImageRepository")
 * @ORM\Table(name="ever_blog_image")
 */
class Image
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_image", type="integer") */
    private $id;

    /** @ORM\Column(name="image_type", type="string", length=255, nullable=true) */
    private $type;

    /** @ORM\Column(name="image_link", type="string", length=255, nullable=true) */
    private $link;

    /** @ORM\Column(name="id_element", type="integer") */
    private $elementId;

    /** @ORM\Column(name="id_shop", type="integer") */
    private $shopId;

    /** @ORM\OneToMany(targetEntity="ImageShop", mappedBy="image", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $shops;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
    }
}
