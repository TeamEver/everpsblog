<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;
use PrestaShop\Module\Everpsblog\Repository\CommentRepository;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\Table(name="ever_blog_comments")
 */
class Comment
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_comment", type="integer") */
    private $id;

    /** @ORM\Column(name="id_ever_post", type="integer") */
    private $postId = 0;

    /** @ORM\Column(name="id_lang", type="integer") */
    private $langId;

    /** @ORM\Column(name="comment", type="text") */
    private $comment;

    /** @ORM\Column(name="name", type="text") */
    private $name;

    /** @ORM\Column(name="user_email", type="text") */
    private $userEmail;

    /** @ORM\Column(name="active", type="integer", nullable=true) */
    private $active;

    /** @ORM\Column(name="date_add", type="datetime", nullable=true) */
    private $createdAt;

    /** @ORM\Column(name="date_upd", type="datetime", nullable=true) */
    private $updatedAt;
}
