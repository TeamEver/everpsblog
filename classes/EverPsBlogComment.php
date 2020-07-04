<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsBlogComment extends ObjectModel
{
    public $id_ever_comment;
    public $id_ever_post;
    public $id_lang;
    public $comment;
    public $name;
    public $user_email;
    public $date_add;
    public $date_upd;
    public $active;

    public static $definition = array(
        'table' => 'ever_blog_comments',
        'primary' => 'id_ever_comment',
        'multilang' => false,
        'fields' => array(
            'id_ever_post' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isunsignedInt',
                'required' => true
            ),
            'id_lang' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isunsignedInt',
                'required' => true
            ),
            'comment' => array(
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml'
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => true
            ),
            'user_email' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isEmail',
                'required' => true
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
        )
    );

    public function getLatestCommentByEmail($email, $id_lang)
    {
        $sql = new DbQuery;
        $sql->select('id_ever_comment');
        $sql->from('ever_blog_comments');
        $sql->where('id_lang = '.(int)$id_lang);
        $sql->where('user_email = "'.pSQL($email).'"');
        $sql->orderBy('date_add DESC');
        return new self(Db::getInstance()->getValue($sql));
    }

    public static function getComments()
    {
        if ($res = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'everpsblog_comments`'
        ))
        return $res;
    }

    public static function getCommentsByPost($id_ever_post, $id_lang, $active = 1)
    {
        $sql = new DbQuery;
        $sql->select('id_ever_comment');
        $sql->from('ever_blog_comments');
        $sql->where('id_lang = '.(int)$id_lang);
        $sql->where('id_ever_post = '.(int)$id_ever_post);
        $sql->where('active = '.(int)$active);
        $sql->groupBy('id_ever_comment');
        $sql->orderBy('date_add DESC');
        $comments = Db::getInstance()->executeS($sql);
        $return = array();
        // die(var_dump($return));
        foreach ($comments as $comment) {
            $return[] = new self((int)$comment['id_ever_comment']);
        }
        return $return;
    }

    public static function getCommentsByEmail($email, $id_lang, $active = 1)
    {
        $sql = new DbQuery;
        $sql->select('id_ever_comment');
        $sql->from('ever_blog_comments');
        $sql->where('id_lang = '.(int)$id_lang);
        $sql->where('user_email = \''.pSQL($email).'\'');
        $sql->where('active = '.(int)$active);
        $sql->groupBy('id_ever_comment');
        $sql->orderBy('date_add DESC');
        $comments = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($comments as $comment) {
            $return[] = new self((int)$comment['id_ever_comment']);
        }
        // die(var_dump($return));
        return $return;
    }

    public static function commentsCount($id_ever_post, $id_lang, $active = 1)
    {
        $sql = new DbQuery;
        $sql->select('COUNT(*)');
        $sql->from('ever_blog_comments');
        $sql->where('id_ever_post = '.(int)$id_ever_post);
        $sql->where('id_lang = '.(int)$id_lang);
        $sql->where('active = '.(int)$active);
        $count = Db::getInstance()->getValue($sql);
        if ($count) {
            return (int)$count;
        }
    }
}
