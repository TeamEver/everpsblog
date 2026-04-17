<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\PostRepository")
 * @ORM\Table(name="ever_blog_post")
 */
class Post
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_post", type="integer") */
    private $id;

    /** @ORM\Column(name="id_author", type="integer") */
    private $authorId;

    /** @ORM\Column(name="id_default_category", type="integer") */
    private $defaultCategoryId;

    /** @ORM\Column(name="post_status", type="string", length=255) */
    private $status;

    /** @ORM\Column(name="active", type="boolean", nullable=true) */
    private $active;

    /** @ORM\Column(name="indexable", type="boolean", nullable=true) */
    private $indexable;

    /** @ORM\Column(name="follow", type="boolean", nullable=true) */
    private $follow;

    /** @ORM\Column(name="sitemap", type="boolean", options={"default": 1}) */
    private $sitemap = true;

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

    public function getId()
    {
        return $this->id;
    }

    public function getAuthorId()
    {
        return $this->authorId;
    }

    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getDefaultCategoryId()
    {
        return $this->defaultCategoryId;
    }

    public function setDefaultCategoryId($defaultCategoryId)
    {
        $this->defaultCategoryId = $defaultCategoryId;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setIndexable($indexable)
    {
        $this->indexable = $indexable;

        return $this;
    }

    public function setFollow($follow)
    {
        $this->follow = $follow;

        return $this;
    }

    public function setSitemap($sitemap)
    {
        $this->sitemap = $sitemap;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setStarred($starred)
    {
        $this->starred = $starred;

        return $this;
    }

    public function setAllowedGroups($allowedGroups)
    {
        $this->allowedGroups = $allowedGroups;

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
}
