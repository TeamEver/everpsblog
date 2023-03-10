<?php
/**
 * 2019-2021 Team Ever
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
 *  @copyright 2019-2021 Team Ever
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

    /**
     * get latest comment by customer email
     * @param customer email, int id_lang
     * @return comment obj
    */
    public function getLatestCommentByEmail($email, $id_lang)
    {
        $cache_id = 'EverPsBlogComment::getLatestCommentByEmail_'
        .$email
        .'_'
        .(int) $id_lang;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_comment');
            $sql->from('ever_blog_comments');
            $sql->where('id_lang = '.(int) $id_lang);
            $sql->where('user_email = "'.pSQL($email).'"');
            $sql->orderBy('date_add DESC');
            $return = new self(Db::getInstance()->getValue($sql));
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get all comments
     * @return array of all comments
    */
    public static function getComments()
    {
        $cache_id = 'EverPsBlogComment::getComments';
        if (!Cache::isStored($cache_id)) {
            if ($res = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'everpsblog_comments`'
            )) {
                Cache::store($cache_id, $res);
                return $res;
            }
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get all available comments for given post
     * @param int id post, int id_lang, bool only active
     * @return array of comment objs
    */
    public static function getCommentsByPost($id_ever_post, $id_lang, $active = 1)
    {
        $cache_id = 'EverPsBlogComment::getCommentsByPost_'
        .(int) $id_ever_post
        .'_'
        .(int) $id_lang
        .'_'
        .(int) $active;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_comment');
            $sql->from('ever_blog_comments');
            $sql->where('id_lang = '.(int) $id_lang);
            $sql->where('id_ever_post = '.(int) $id_ever_post);
            $sql->where('active = '.(int) $active);
            $sql->groupBy('id_ever_comment');
            $sql->orderBy('date_add DESC');
            $comments = Db::getInstance()->executeS($sql);
            $return = [];
            // die(var_dump($return));
            foreach ($comments as $comment) {
                $loop_comment = new self((int) $comment['id_ever_comment']);
                $loop_comment->date_add = date('d/m/Y', strtotime($loop_comment->date_add));
                $loop_comment->date_upd = date('d/m/Y', strtotime($loop_comment->date_upd));
                $return[] = $loop_comment;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get all available comments for given email
     * @param string email, int id_lang, bool only active
     * @return array of comment objs
    */
    public static function getCommentsByEmail($email, $id_lang, $active = 1)
    {
        $cache_id = 'EverPsBlogComment::getCommentsByEmail_'
        .$email
        .'_'
        .(int) $id_lang
        .'_'
        .(int) $active;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_comment');
            $sql->from('ever_blog_comments');
            $sql->where('id_lang = '.(int) $id_lang);
            $sql->where('user_email = \''.pSQL($email).'\'');
            $sql->where('active = '.(int) $active);
            $sql->groupBy('id_ever_comment');
            $sql->orderBy('date_add DESC');
            $comments = Db::getInstance()->executeS($sql);
            $return = [];
            foreach ($comments as $comment) {
                $loop_comment = new self((int) $comment['id_ever_comment']);
                $loop_comment->date_add = date('d/m/Y', strtotime($loop_comment->date_add));
                $loop_comment->date_upd = date('d/m/Y', strtotime($loop_comment->date_upd));
                $return[] = $loop_comment;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Count comments per post id
     * @param int id post, int id_lang, bool only active
     * @return int comments count for given post id
    */
    public static function commentsCount($id_ever_post, $id_lang, $active = 1)
    {
        $cache_id = 'EverPsBlogComment::commentsCount_'
        .(int) $id_ever_post
        .'_'
        .(int) $id_lang
        .'_'
        .(int) $active;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('COUNT(*)');
            $sql->from('ever_blog_comments');
            $sql->where('id_ever_post = '.(int) $id_ever_post);
            $sql->where('id_lang = '.(int) $id_lang);
            $sql->where('active = '.(int) $active);
            $count = Db::getInstance()->getValue($sql);
            if ($count) {
                Cache::store($cache_id, $count);
                return (int) $count;
            }
        }
        return Cache::retrieve($cache_id);
    }
}
