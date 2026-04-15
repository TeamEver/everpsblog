<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\CategoryRepository")
 * @ORM\Table(name="ever_blog_category")
 */
class Category
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_category", type="integer") */
    private $id;

    /** @ORM\Column(name="id_parent_category", type="integer", nullable=true) */
    private $parentId;

    /** @ORM\Column(name="active", type="boolean", nullable=true) */
    private $active;

    /** @ORM\Column(name="indexable", type="boolean", nullable=true) */
    private $indexable;

    /** @ORM\Column(name="follow", type="boolean", nullable=true) */
    private $follow;

    /** @ORM\Column(name="sitemap", type="boolean", options={"default": 1}) */
    private $sitemap = true;

    /** @ORM\Column(name="is_root_category", type="boolean", nullable=true) */
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
}
