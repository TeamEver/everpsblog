<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use PrestaShop\Module\Everpsblog\Repository\CategoryRepository;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\Table(name="ever_blog_category")
 */
class Category
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_category", type="integer") */
    private $id;

    /** @ORM\Column(name="id_parent_category", type="integer", nullable=true) */
    private $parentId;

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

    /** @ORM\Column(name="is_root_category", type="integer", nullable=true) */
    private $rootCategory;

    /** @ORM\Column(name="count", type="integer", options={"default": 0}) */
    private $count = 0;

    /** @ORM\Column(name="allowed_groups", type="string", length=255, nullable=true) */
    private $allowedGroups;

    /** @ORM\Column(name="groups", type="text", nullable=true) */
    private $groups;

    /** @ORM\Column(name="date_add", type="datetime", nullable=true) */
    private $createdAt;

    /** @ORM\Column(name="date_upd", type="datetime", nullable=true) */
    private $updatedAt;

    /** @ORM\OneToMany(targetEntity="CategoryLang", mappedBy="category", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $translations;

    /** @ORM\OneToMany(targetEntity="CategoryShop", mappedBy="category", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $shops;

    /** @ORM\OneToMany(targetEntity="CategoryProduct", mappedBy="category", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $products;

    /** @ORM\OneToMany(targetEntity="PostCategory", mappedBy="category") */
    private $postCategories;

    /** @ORM\OneToMany(targetEntity="Post", mappedBy="defaultCategory") */
    private $defaultPosts;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->postCategories = new ArrayCollection();
        $this->defaultPosts = new ArrayCollection();
    }
}
