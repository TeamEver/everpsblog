<?php
/**
 * 2019-2020 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2020 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
        )) {
            return $res;
        }
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
