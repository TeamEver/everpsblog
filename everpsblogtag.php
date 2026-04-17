<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsBlogTag
{
    public static function getAllTags($idLang, $idShop, $active = true)
    {
        $sql = new DbQuery();
        $sql->select('t.id_ever_tag, tl.title, tl.link_rewrite');
        $sql->from('ever_blog_tag', 't');
        $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $idShop);

        if ($active) {
            $sql->where('t.active = 1');
        }

        $sql->orderBy('tl.title ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }
}
