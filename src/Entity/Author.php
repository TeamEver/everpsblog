<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use PrestaShop\Module\Everpsblog\Repository\AuthorRepository;

/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 * @ORM\Table(name="ever_blog_author")
 */
class Author
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_author", type="integer") */
    private $id;

    /** @ORM\Column(name="id_employee", type="integer") */
    private $employeeId;

    /** @ORM\Column(name="id_shop", type="integer") */
    private $shopId;

    /** @ORM\Column(name="nickhandle", type="string", length=255) */
    private $nickhandle;

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

    /** @ORM\OneToMany(targetEntity="AuthorLang", mappedBy="author", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $translations;

    /** @ORM\OneToMany(targetEntity="AuthorShop", mappedBy="author", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $shops;

    /** @ORM\OneToMany(targetEntity="AuthorProduct", mappedBy="author", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $products;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->products = new ArrayCollection();
    }
}
