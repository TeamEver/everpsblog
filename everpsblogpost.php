<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsBlogPost
{
    public $id;
    public $id_ever_post;

    public function __construct($idEverPost = null, $idLang = null, $idShop = null)
    {
        if (!$idEverPost) {
            return;
        }

        $idLang = (int) ($idLang ?: Context::getContext()->language->id);
        $idShop = (int) ($idShop ?: Context::getContext()->shop->id);

        $row = self::getSinglePost((int) $idEverPost, $idLang, $idShop);
        if (!$row) {
            return;
        }

        foreach ($row as $key => $value) {
            $this->{$key} = $value;
        }

        $this->id_ever_post = (int) $idEverPost;
        $this->id = (int) $idEverPost;
    }

    public static function countPosts($idLang, $idShop, $status = 'published')
    {
        $sql = new DbQuery();
        $sql->select('COUNT(DISTINCT p.id_ever_post)');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
        $sql->where('p.active = 1');

        if (null !== $status) {
            $sql->where('p.post_status = "' . pSQL((string) $status) . '"');
        }

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public static function getPosts($idLang, $idShop, $start = 0, $limit = null, $status = 'published', $active = true, $starred = false, $orderBy = null, $orderWay = null)
    {
        $sql = new DbQuery();
        $sql->select('p.id_ever_post, p.id_author as id_ever_author, p.date_add, p.starred as is_featured, p.post_status, p.active, p.count as view_count, pl.title, pl.link_rewrite, pl.meta_title, pl.meta_description, pl.excerpt, pl.content, a.nickhandle');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
        $sql->leftJoin('ever_blog_author', 'a', 'a.id_ever_author = p.id_author');

        if ($active) {
            $sql->where('p.active = 1');
        }

        if (null !== $status) {
            $sql->where('p.post_status = "' . pSQL((string) $status) . '"');
        }

        if ($starred) {
            $sql->where('p.starred = 1');
        }

        $allowedOrderBy = ['date_add', 'title', 'id_ever_post'];
        $allowedOrderWay = ['ASC', 'DESC'];
        $orderBy = in_array($orderBy, $allowedOrderBy, true) ? $orderBy : 'date_add';
        $orderWay = in_array(strtoupper((string) $orderWay), $allowedOrderWay, true) ? strtoupper((string) $orderWay) : 'DESC';

        if ('title' === $orderBy) {
            $sql->orderBy('pl.title ' . $orderWay . ', p.id_ever_post DESC');
        } elseif ('id_ever_post' === $orderBy) {
            $sql->orderBy('p.id_ever_post ' . $orderWay);
        } else {
            $sql->orderBy('p.date_add ' . $orderWay . ', p.id_ever_post DESC');
        }

        if (null !== $limit) {
            $sql->limit((int) $limit, (int) $start);
        } elseif ((int) $start > 0) {
            $sql->limit(18446744073709551615, (int) $start);
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }

    private static function getSinglePost($idEverPost, $idLang, $idShop)
    {
        $sql = new DbQuery();
        $sql->select('p.id_ever_post, p.id_author as id_ever_author, p.date_add, p.starred as is_featured, p.post_status, p.active, p.count as view_count, pl.title, pl.link_rewrite, pl.meta_title, pl.meta_description, pl.excerpt, pl.content, a.nickhandle');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
        $sql->leftJoin('ever_blog_author', 'a', 'a.id_ever_author = p.id_author');
        $sql->where('p.id_ever_post = ' . (int) $idEverPost);
        $sql->limit(1);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }
}
