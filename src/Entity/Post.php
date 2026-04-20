<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PrestaShop\Module\Everpsblog\Repository\PostRepository;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ORM\Table(name="ever_blog_post")
 */
class Post
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_post", type="integer") */
    private $id;

    /** @ORM\Column(name="id_shop", type="integer") */
    private $shopId = 0;

    /** @ORM\Column(name="id_author", type="integer", nullable=true, options={"default": 0}) */
    private $authorId = 0;

    /** @ORM\Column(name="id_default_category", type="integer", options={"default": 0}) */
    private $defaultCategoryId = 0;

    /** @ORM\Column(name="post_status", type="string", length=255) */
    private $status = 'draft';

    /** @ORM\Column(name="active", type="integer", nullable=true) */
    private $active = 1;

    /** @ORM\Column(name="indexable", type="integer", nullable=true) */
    private $indexable = 0;

    /** @ORM\Column(name="follow", type="integer", nullable=true) */
    private $follow = 0;

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

    public function getShopId(): int
    {
        return (int) $this->shopId;
    }

    public function setShopId($shopId): self
    {
        $this->shopId = (int) $shopId;

        return $this;
    }

    public function getAuthorId(): int
    {
        return (int) $this->authorId;
    }

    public function setAuthorId($authorId): self
    {
        $authorId = (int) $authorId;
        $this->authorId = $authorId > 0 ? $authorId : null;

        return $this;
    }

    public function getDefaultCategoryId(): int
    {
        return (int) $this->defaultCategoryId;
    }

    public function setDefaultCategoryId($defaultCategoryId): self
    {
        $this->defaultCategoryId = (int) $defaultCategoryId;

        return $this;
    }

    public function getStatus(): string
    {
        return (string) $this->status;
    }

    public function setStatus($status): self
    {
        $this->status = (string) $status;

        return $this;
    }

    public function getActive(): int
    {
        return (int) $this->active;
    }

    public function setActive($active): self
    {
        $this->active = (int) $active;

        return $this;
    }

    public function getIndexable(): int
    {
        return (int) $this->indexable;
    }

    public function setIndexable($indexable): self
    {
        $this->indexable = (int) ((bool) $indexable);

        return $this;
    }

    public function getFollow(): int
    {
        return (int) $this->follow;
    }

    public function setFollow($follow): self
    {
        $this->follow = (int) ((bool) $follow);

        return $this;
    }

    public function getSitemap(): int
    {
        return (int) $this->sitemap;
    }

    public function setSitemap($sitemap): self
    {
        $this->sitemap = (int) ((bool) $sitemap);

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword($password): self
    {
        $this->password = (null === $password || '' === $password) ? null : (string) $password;

        return $this;
    }

    public function getStarred(): int
    {
        return (int) $this->starred;
    }

    public function setStarred($starred): self
    {
        $this->starred = (int) $starred;

        return $this;
    }

    public function getViewCount(): int
    {
        return (int) $this->viewCount;
    }

    public function setViewCount($viewCount): self
    {
        $this->viewCount = (int) $viewCount;

        return $this;
    }

    public function getAllowedGroups(): ?string
    {
        return $this->allowedGroups;
    }

    public function setAllowedGroups($allowedGroups): self
    {
        $this->allowedGroups = (null === $allowedGroups || '' === $allowedGroups) ? null : (string) $allowedGroups;

        return $this;
    }

    public function getGroups(): ?string
    {
        return $this->groups;
    }

    public function setGroups($groups): self
    {
        $this->groups = (null === $groups || '' === $groups) ? null : (string) $groups;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PostLang>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
