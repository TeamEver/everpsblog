<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\TagRepository")
 * @ORM\Table(name="ever_blog_tag")
 */
class Tag
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_tag", type="integer") */
    private $id;

    /** @ORM\Column(name="active", type="boolean", nullable=true) */
    private $active;

    /** @ORM\Column(name="count", type="integer", options={"default": 0}) */
    private $count = 0;
}
