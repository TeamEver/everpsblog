<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsBlogCategory
{
    public static function getAllCategories($idLang, $idShop, $active = true, $idParent = null)
    {
        $sql = new DbQuery();
        $sql->select('c.id_ever_category, c.is_root_category, cl.title, cl.link_rewrite');
        $sql->from('ever_blog_category', 'c');
        $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);

        if ($active) {
            $sql->where('c.active = 1');
        }

        if (null !== $idParent) {
            $sql->where('c.id_parent_category = ' . (int) $idParent);
        }

        $sql->orderBy('cl.title ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }
}
