<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\CommentRepository")
 * @ORM\Table(name="ever_blog_comments")
 */
class Comment
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_comment", type="integer") */
    private $id;

    /** @ORM\Column(name="id_ever_post", type="integer") */
    private $postId;

    /** @ORM\Column(name="id_lang", type="integer") */
    private $langId;

    /** @ORM\Column(name="comment", type="text") */
    private $comment;

    /** @ORM\Column(name="user_email", type="text") */
    private $userEmail;

    /** @ORM\Column(name="active", type="boolean", nullable=true) */
    private $active;
}
