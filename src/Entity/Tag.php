<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\TagRepository")
 * @ORM\Table(name="ever_blog_tag")
 */
class Tag
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_tag", type="integer") */
    private $id;

    /** @ORM\Column(name="id_shop", type="integer") */
    private $shopId;

    /** @ORM\Column(name="active", type="integer", nullable=true) */
    private $active;

    /** @ORM\Column(name="indexable", type="integer", nullable=true) */
    private $indexable;

    /** @ORM\Column(name="follow", type="integer", nullable=true) */
    private $follow;

    /** @ORM\Column(name="sitemap", type="integer", options={"default": 1}) */
    private $sitemap = 1;

    /** @ORM\Column(name="allowed_groups", type="string", length=255, nullable=true) */
    private $allowedGroups;

    /** @ORM\Column(name="count", type="integer", options={"default": 0}) */
    private $count = 0;

    /** @ORM\OneToMany(targetEntity="TagLang", mappedBy="tag", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $translations;

    /** @ORM\OneToMany(targetEntity="TagShop", mappedBy="tag", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $shops;

    /** @ORM\OneToMany(targetEntity="TagProduct", mappedBy="tag", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $products;

    /** @ORM\OneToMany(targetEntity="PostTag", mappedBy="tag") */
    private $postTags;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->postTags = new ArrayCollection();
    }
}
