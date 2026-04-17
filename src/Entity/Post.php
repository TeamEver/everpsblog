<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\PostRepository")
 * @ORM\Table(name="ever_blog_post")
 */
class Post
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_post", type="integer") */
    private $id;

    /** @ORM\Column(name="id_shop", type="integer") */
    private $shopId;

    /** @ORM\ManyToOne(targetEntity="Author", inversedBy="posts") @ORM\JoinColumn(name="id_author", referencedColumnName="id_ever_author", nullable=false) */
    private $author;

    /** @ORM\ManyToOne(targetEntity="Category", inversedBy="defaultPosts") @ORM\JoinColumn(name="id_default_category", referencedColumnName="id_ever_category", nullable=false) */
    private $defaultCategory;

    /** @ORM\Column(name="post_status", type="string", length=255) */
    private $status;

    /** @ORM\Column(name="active", type="integer", nullable=true) */
    private $active;

    /** @ORM\Column(name="indexable", type="integer", nullable=true) */
    private $indexable;

    /** @ORM\Column(name="follow", type="integer", nullable=true) */
    private $follow;

    /** @ORM\Column(name="sitemap", type="integer", options={"default": 1}) */
    private $sitemap = 1;

    /** @ORM\Column(name="psswd", type="string", length=255, nullable=true) */
    private $password;

    /** @ORM\Column(name="starred", type="integer", options={"default": 0}) */
    private $starred = 0;

    /** @ORM\Column(name="count", type="integer", options={"default": 0}) */
    private $viewCount = 0;

    /** @ORM\Column(name="allowed_groups", type="string", length=255, nullable=true) */
    private $allowedGroups;

    /** @ORM\Column(name="groups", type="text", nullable=true) */
    private $groups;

    /** @ORM\Column(name="date_add", type="datetime", nullable=true) */
    private $createdAt;

    /** @ORM\Column(name="date_upd", type="datetime", nullable=true) */
    private $updatedAt;

    /** @ORM\OneToMany(targetEntity="PostLang", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $translations;

    /** @ORM\OneToMany(targetEntity="PostShop", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $shops;

    /** @ORM\OneToMany(targetEntity="PostCategory", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $postCategories;

    /** @ORM\OneToMany(targetEntity="PostTag", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $postTags;

    /** @ORM\OneToMany(targetEntity="PostProduct", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true) */
    private $postProducts;

    /** @ORM\OneToMany(targetEntity="Comment", mappedBy="post") */
    private $comments;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->postCategories = new ArrayCollection();
        $this->postTags = new ArrayCollection();
        $this->postProducts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getViewCount()
    {
        return (int) $this->viewCount;
    }

    public function setViewCount($viewCount)
    {
        $this->viewCount = (int) $viewCount;

        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PostLang>
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
