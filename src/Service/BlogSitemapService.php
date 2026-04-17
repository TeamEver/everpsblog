<?php

namespace PrestaShop\Module\Everpsblog\Service;

class BlogSitemapService
{
    public function generate($context, $shopId)
    {
        $languages = \Language::getLanguages(true, (int) $shopId);
        $result = true;

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $result = $this->processSitemapAuthor($context, $shopId, $idLang) && $result;
            $result = $this->processSitemapTag($context, $shopId, $idLang) && $result;
            $result = $this->processSitemapCategory($context, $shopId, $idLang) && $result;
            $result = $this->processSitemapPost($context, $shopId, $idLang) && $result;
        }

        return $result;
    }

    private function processSitemapPost($context, $shopId, $idLang)
    {
        $isoLang = \Language::getIsoById((int) $idLang);
        $sitemap = new \EverPsBlogSitemap(\Tools::getHttpHost(true) . __PS_BASE_URI__);
        $sitemap->setPath(_PS_ROOT_DIR_ . '/');
        $sitemap->setFilename('blogpost_' . (int) $shopId . '_lang_' . $isoLang);
        $sql = 'SELECT id_ever_post FROM ' . _DB_PREFIX_ . 'ever_blog_post WHERE sitemap = 1 AND post_status = "published"';

        if (!$results = \Db::getInstance()->executeS($sql)) {
            return true;
        }

        foreach ($results as $row) {
            $post = new \EverPsBlogPost((int) $row['id_ever_post'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($post)) {
                continue;
            }
            $link = new \Link();
            $url = $link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $post->id, 'link_rewrite' => $post->link_rewrite]);
            $sitemap->addItem($url, 1, 'weekly', $post->date_upd);
        }

        return (bool) $sitemap->createSitemapIndex(\Tools::getHttpHost(true) . __PS_BASE_URI__, 'Today');
    }

    private function processSitemapAuthor($context, $shopId, $idLang)
    {
        $isoLang = \Language::getIsoById((int) $idLang);
        $sitemap = new \EverPsBlogSitemap(\Tools::getHttpHost(true) . __PS_BASE_URI__);
        $sitemap->setPath(_PS_ROOT_DIR_ . '/');
        $sitemap->setFilename('blogauthor_' . (int) $shopId . '_lang_' . $isoLang);
        $sql = 'SELECT id_ever_author FROM ' . _DB_PREFIX_ . 'ever_blog_author WHERE sitemap = 1 AND active = 1';

        if (!$results = \Db::getInstance()->executeS($sql)) {
            return true;
        }

        foreach ($results as $row) {
            $author = new \EverPsBlogAuthor((int) $row['id_ever_author'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($author) || !(bool) $author->active) {
                continue;
            }
            $url = (new \Link())->getModuleLink('everpsblog', 'author', ['id_ever_author' => $author->id, 'link_rewrite' => $author->link_rewrite]);
            $sitemap->addItem($url, 1, 'weekly', $author->date_upd);
        }

        return (bool) $sitemap->createSitemapIndex(\Tools::getHttpHost(true) . __PS_BASE_URI__, 'Today');
    }

    private function processSitemapTag($context, $shopId, $idLang)
    {
        $isoLang = \Language::getIsoById((int) $idLang);
        $sitemap = new \EverPsBlogSitemap(\Tools::getHttpHost(true) . __PS_BASE_URI__);
        $sitemap->setPath(_PS_ROOT_DIR_ . '/');
        $sitemap->setFilename('blogtag_' . (int) $shopId . '_lang_' . $isoLang);
        $sql = 'SELECT id_ever_tag FROM ' . _DB_PREFIX_ . 'ever_blog_tag WHERE sitemap = 1 AND active = 1';

        if (!$results = \Db::getInstance()->executeS($sql)) {
            return true;
        }

        foreach ($results as $row) {
            $tag = new \EverPsBlogTag((int) $row['id_ever_tag'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($tag) || !(bool) $tag->active) {
                continue;
            }
            $url = (new \Link())->getModuleLink('everpsblog', 'tag', ['id_ever_tag' => $tag->id, 'link_rewrite' => $tag->link_rewrite]);
            $sitemap->addItem($url, 1, 'weekly', $tag->date_upd);
        }

        return (bool) $sitemap->createSitemapIndex(\Tools::getHttpHost(true) . __PS_BASE_URI__, 'Today');
    }

    private function processSitemapCategory($context, $shopId, $idLang)
    {
        $isoLang = \Language::getIsoById((int) $idLang);
        $sitemap = new \EverPsBlogSitemap(\Tools::getHttpHost(true) . __PS_BASE_URI__);
        $sitemap->setPath(_PS_ROOT_DIR_ . '/');
        $sitemap->setFilename('blogcategory_' . (int) $shopId . '_lang_' . $isoLang);
        $sql = 'SELECT id_ever_category FROM ' . _DB_PREFIX_ . 'ever_blog_category WHERE sitemap = 1 AND active = 1';

        if (!$results = \Db::getInstance()->executeS($sql)) {
            return true;
        }

        foreach ($results as $row) {
            $category = new \EverPsBlogCategory((int) $row['id_ever_category'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($category) || !(bool) $category->active || (bool) $category->is_root_category) {
                continue;
            }
            $url = (new \Link())->getModuleLink('everpsblog', 'category', ['id_ever_category' => $category->id, 'link_rewrite' => $category->link_rewrite]);
            $sitemap->addItem($url, 1, 'weekly', $category->date_upd);
        }

        return (bool) $sitemap->createSitemapIndex(\Tools::getHttpHost(true) . __PS_BASE_URI__, 'Today');
    }

    private function isRestrictedFromSitemap($entity)
    {
        if (!isset($entity->allowed_groups) || !$entity->allowed_groups) {
            return false;
        }

        $allowedGroups = json_decode($entity->allowed_groups, true);

        return is_array($allowedGroups) && !in_array('1', $allowedGroups, true) && !in_array(1, $allowedGroups, true);
    }
}
