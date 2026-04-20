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
        $sql = 'SELECT p.id_ever_post, p.date_upd, p.allowed_groups, pl.link_rewrite
                FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
                INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_post_lang` pl
                    ON pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang . '
                WHERE p.sitemap = 1 AND p.post_status = "published"';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            if ($this->isRestrictedFromSitemap($row['allowed_groups'] ?? null)) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'post', [
                    'id_ever_post' => (int) $row['id_ever_post'],
                    'link_rewrite' => (string) $row['link_rewrite'],
                ]),
                'date' => (string) $row['date_upd'],
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function processSitemapAuthor($shopId, $idLang)
    {
        $filename = 'blogauthor_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT a.id_ever_author, a.date_upd, a.allowed_groups, a.active, al.link_rewrite
                FROM `' . _DB_PREFIX_ . 'ever_blog_author` a
                INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_author_lang` al
                    ON al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) $idLang . '
                WHERE a.sitemap = 1 AND a.active = 1';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            if ($this->isRestrictedFromSitemap($row['allowed_groups'] ?? null) || !(int) $row['active']) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'author', [
                    'id_ever_author' => (int) $row['id_ever_author'],
                    'link_rewrite' => (string) $row['link_rewrite'],
                ]),
                'date' => (string) $row['date_upd'],
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function processSitemapTag($shopId, $idLang)
    {
        $filename = 'blogtag_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT t.id_ever_tag, t.date_upd, t.allowed_groups, t.active, tl.link_rewrite
                FROM `' . _DB_PREFIX_ . 'ever_blog_tag` t
                INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_tag_lang` tl
                    ON tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang . '
                WHERE t.sitemap = 1 AND t.active = 1';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            if ($this->isRestrictedFromSitemap($row['allowed_groups'] ?? null) || !(int) $row['active']) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'tag', [
                    'id_ever_tag' => (int) $row['id_ever_tag'],
                    'link_rewrite' => (string) $row['link_rewrite'],
                ]),
                'date' => (string) $row['date_upd'],
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function processSitemapCategory($shopId, $idLang)
    {
        $filename = 'blogcategory_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT c.id_ever_category, c.date_upd, c.allowed_groups, c.active, c.is_root_category, cl.link_rewrite
                FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
                INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_category_lang` cl
                    ON cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang . '
                WHERE c.sitemap = 1 AND c.active = 1';
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        if (!$rows) {
            return true;
        }

        $items = [];
        $link = new \Link();
        foreach ($rows as $row) {
            if ($this->isRestrictedFromSitemap($row['allowed_groups'] ?? null) || !(int) $row['active'] || (int) $row['is_root_category']) {
                continue;
            }
            $items[] = [
                'url' => $link->getModuleLink('everpsblog', 'category', [
                    'id_ever_category' => (int) $row['id_ever_category'],
                    'link_rewrite' => (string) $row['link_rewrite'],
                ]),
                'date' => (string) $row['date_upd'],
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

    /**
     * @param mixed $allowedGroups
     */
    private function isRestrictedFromSitemap($allowedGroups)
    {
        if (!$allowedGroups) {
            return false;
        }
        $allowedGroups = json_decode((string) $allowedGroups, true);

        return is_array($allowedGroups) && !in_array('1', $allowedGroups, true) && !in_array(1, $allowedGroups, true);
    }
}
