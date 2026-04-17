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
            $result = $this->processSitemapAuthor($shopId, $idLang) && $result;
            $result = $this->processSitemapTag($shopId, $idLang) && $result;
            $result = $this->processSitemapCategory($shopId, $idLang) && $result;
            $result = $this->processSitemapPost($shopId, $idLang) && $result;
        }

        return $result;
    }

    public function getSitemapIndexes()
    {
        $siteUrl = \Tools::getHttpHost(true) . __PS_BASE_URI__;
        $indexes = [];
        foreach (glob(_PS_ROOT_DIR_ . '/*') as $index) {
            if (is_file($index) && pathinfo($index, PATHINFO_EXTENSION) === 'xml' && strpos(basename($index), 'indexable') !== false) {
                $indexes[] = $siteUrl . basename($index);
            }
        }

        return $indexes;
    }

    private function processSitemapPost($shopId, $idLang)
    {
        $filename = 'blogpost_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT id_ever_post FROM ' . _DB_PREFIX_ . 'ever_blog_post WHERE sitemap = 1 AND post_status = "published"';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            $post = new \EverPsBlogPost((int) $row['id_ever_post'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($post)) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $post->id, 'link_rewrite' => $post->link_rewrite]),
                'date' => $post->date_upd,
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function processSitemapAuthor($shopId, $idLang)
    {
        $filename = 'blogauthor_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT id_ever_author FROM ' . _DB_PREFIX_ . 'ever_blog_author WHERE sitemap = 1 AND active = 1';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            $author = new \EverPsBlogAuthor((int) $row['id_ever_author'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($author) || !(bool) $author->active) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'author', ['id_ever_author' => $author->id, 'link_rewrite' => $author->link_rewrite]),
                'date' => $author->date_upd,
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function processSitemapTag($shopId, $idLang)
    {
        $filename = 'blogtag_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT id_ever_tag FROM ' . _DB_PREFIX_ . 'ever_blog_tag WHERE sitemap = 1 AND active = 1';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            $tag = new \EverPsBlogTag((int) $row['id_ever_tag'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($tag) || !(bool) $tag->active) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'tag', ['id_ever_tag' => $tag->id, 'link_rewrite' => $tag->link_rewrite]),
                'date' => $tag->date_upd,
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function processSitemapCategory($shopId, $idLang)
    {
        $filename = 'blogcategory_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT id_ever_category FROM ' . _DB_PREFIX_ . 'ever_blog_category WHERE sitemap = 1 AND active = 1';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            $category = new \EverPsBlogCategory((int) $row['id_ever_category'], (int) $idLang, (int) $shopId);
            if ($this->isRestrictedFromSitemap($category) || !(bool) $category->active || (bool) $category->is_root_category) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'category', ['id_ever_category' => $category->id, 'link_rewrite' => $category->link_rewrite]),
                'date' => $category->date_upd,
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function writeSitemapFiles($filename, array $items)
    {
        $chunkSize = max(1, (int) \Configuration::get('EVERBLOG_SITEMAP_NUMBER'));
        $domain = \Tools::getHttpHost(true) . __PS_BASE_URI__;
        $path = _PS_ROOT_DIR_ . '/';

        $chunks = array_chunk($items, $chunkSize);
        $count = 0;
        foreach ($chunks as $index => $chunk) {
            $file = $path . $filename . ($index ? '-' . $index : '') . '.xml';
            $writer = new \XMLWriter();
            $writer->openURI($file);
            $writer->startDocument('1.0', 'UTF-8');
            $writer->setIndent(true);
            $writer->startElement('urlset');
            $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            foreach ($chunk as $item) {
                $writer->startElement('url');
                $writer->writeElement('loc', $item['url']);
                $writer->writeElement('priority', '1');
                $writer->writeElement('changefreq', 'weekly');
                $writer->writeElement('lastmod', date('Y-m-d', strtotime((string) $item['date'])));
                $writer->endElement();
            }
            $writer->endElement();
            $writer->endDocument();
            $count++;
        }

        $indexwriter = new \XMLWriter();
        $indexwriter->openURI($path . $filename . '-indexable.xml');
        $indexwriter->startDocument('1.0', 'UTF-8');
        $indexwriter->setIndent(true);
        $indexwriter->startElement('sitemapindex');
        $indexwriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        for ($index = 0; $index < $count; $index++) {
            $indexwriter->startElement('sitemap');
            $indexwriter->writeElement('loc', $domain . $filename . ($index ? '-' . $index : '') . '.xml');
            $indexwriter->writeElement('lastmod', date('Y-m-d'));
            $indexwriter->endElement();
        }
        $indexwriter->endElement();
        $indexwriter->endDocument();

        return true;
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
