<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}


class BlogSitemapService
{
    private const ROBOTS_BLOCK_START = '# BEGIN EverPsBlog sitemaps';
    private const ROBOTS_BLOCK_END = '# END EverPsBlog sitemaps';

    public function generate($context, $shopId)
    {
        $languages = \Language::getLanguages(true, (int) $shopId);
        $result = true;
        $this->removeLegacyTaxonomySitemapFiles((int) $shopId);

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $result = $this->processSitemapPost($shopId, $idLang) && $result;
        }

        return $result;
    }

    public function refreshForShop(int $shopId): bool
    {
        $generated = (bool) $this->generate(\Context::getContext(), (int) $shopId);
        $robotsUpdated = (bool) $this->synchronizeRobotsTxt($this->getSitemapIndexes());

        return $generated && $robotsUpdated;
    }

    public function getSitemapIndexes(?int $shopId = null)
    {
        $shopId = null !== $shopId ? (int) $shopId : null;
        $indexes = [];
        foreach (glob(_PS_ROOT_DIR_ . '/*') as $index) {
            $filename = basename($index);
            if (is_file($index) && pathinfo($index, PATHINFO_EXTENSION) === 'xml' && $this->isModuleSitemapIndex($filename, $shopId)) {
                $filenameShopId = $this->extractShopIdFromSitemapFilename($filename);
                $indexes[] = $this->getShopBaseUrl($filenameShopId ?: $shopId) . $filename;
            }
        }

        sort($indexes);

        return $indexes;
    }

    public function getRobotsDirectives(?array $sitemapIndexes = null): array
    {
        $sitemapIndexes = $sitemapIndexes ?? $this->getSitemapIndexes();
        $directives = [];

        foreach ($sitemapIndexes as $sitemapIndex) {
            $sitemapIndex = trim((string) $sitemapIndex);
            if ('' === $sitemapIndex) {
                continue;
            }

            $path = parse_url($sitemapIndex, PHP_URL_PATH);
            if (is_string($path) && '' !== $path) {
                $directives[] = 'Allow: ' . $path;
            }

            $directives[] = 'Sitemap: ' . $sitemapIndex;
        }

        return array_values(array_unique($directives));
    }

    public function synchronizeRobotsTxt(?array $sitemapIndexes = null): bool
    {
        $robotsPath = rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'robots.txt';
        $content = is_file($robotsPath) ? file_get_contents($robotsPath) : '';
        if (false === $content) {
            \PrestaShopLogger::addLog('[everpsblog][BlogSitemapService] Unable to read robots.txt.', 3);

            return false;
        }

        $contentWithoutManagedBlock = $this->removeManagedRobotsBlock((string) $content);
        $existingLines = $this->normalizeRobotsLines($contentWithoutManagedBlock);
        $missingDirectives = [];

        foreach ($this->getRobotsDirectives($sitemapIndexes) as $directive) {
            if (!in_array($this->normalizeRobotsLine($directive), $existingLines, true)) {
                $missingDirectives[] = $directive;
            }
        }

        $newContent = rtrim($contentWithoutManagedBlock);
        if (!empty($missingDirectives)) {
            if ($this->hasAllowDirective($missingDirectives)) {
                array_unshift($missingDirectives, 'User-agent: *');
            }

            $newContent .= ('' === $newContent ? '' : PHP_EOL)
                . self::ROBOTS_BLOCK_START . PHP_EOL
                . implode(PHP_EOL, $missingDirectives) . PHP_EOL
                . self::ROBOTS_BLOCK_END;
        }

        $newContent .= PHP_EOL;
        if ($newContent === (string) $content) {
            return true;
        }

        if (false === @file_put_contents($robotsPath, $newContent, LOCK_EX)) {
            \PrestaShopLogger::addLog('[everpsblog][BlogSitemapService] Unable to update robots.txt.', 3);

            return false;
        }

        return true;
    }

    private function processSitemapPost($shopId, $idLang)
    {
        $filename = 'blogpost_' . (int) $shopId . '_lang_' . \Language::getIsoById((int) $idLang);
        $sql = 'SELECT DISTINCT p.id_ever_post, p.date_upd, p.allowed_groups, pl.link_rewrite
                FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
                INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_post_lang` pl
                    ON pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang . '
                LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post_shop` ps
                    ON ps.id_ever_post = p.id_ever_post
                WHERE p.post_status = "published"
                    AND TRIM(COALESCE(pl.title, "")) != ""
                    AND (p.id_shop = ' . (int) $shopId . ' OR ps.id_shop = ' . (int) $shopId . ')';
        $rows = \Db::getInstance()->executeS($sql) ?: [];

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
                ], true, (int) $idLang, (int) $shopId),
                'date' => (string) $row['date_upd'],
            ];
        }

        return $this->writeSitemapFiles($filename, $items);
    }

    private function writeSitemapFiles($filename, array $items)
    {
        $chunkSize = max(1, (int) \Configuration::get('EVERBLOG_SITEMAP_NUMBER'));
        $shopId = (int) preg_replace('/^blogpost_([0-9]+)_.+$/', '$1', $filename);
        $domain = $this->getShopBaseUrl($shopId);
        $path = _PS_ROOT_DIR_ . '/';
        $this->removeExistingSitemapFiles($path, $filename);

        if (empty($items)) {
            return true;
        }

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

    private function removeLegacyTaxonomySitemapFiles(int $shopId): void
    {
        foreach (['blogauthor', 'blogtag', 'blogcategory'] as $prefix) {
            foreach ((array) glob(_PS_ROOT_DIR_ . '/' . $prefix . '_' . (int) $shopId . '_lang_*.xml') as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private function isModuleSitemapIndex(string $filename, ?int $shopId = null): bool
    {
        $shopPattern = null !== $shopId ? (string) (int) $shopId : '[0-9]+';

        return (bool) preg_match('/^blogpost_' . $shopPattern . '_lang_.+-indexable\.xml$/', $filename);
    }

    private function extractShopIdFromSitemapFilename(string $filename): int
    {
        if (preg_match('/^blogpost_([0-9]+)_lang_.+-indexable\.xml$/', $filename, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function getShopBaseUrl(?int $shopId = null): string
    {
        $shopId = (int) ($shopId ?: \Context::getContext()->shop->id);
        $link = new \Link();
        if (method_exists($link, 'getBaseLink')) {
            return rtrim((string) $link->getBaseLink($shopId, true), '/') . '/';
        }

        return rtrim(\Tools::getHttpHost(true) . __PS_BASE_URI__, '/') . '/';
    }

    private function removeExistingSitemapFiles(string $path, string $filename): void
    {
        foreach ((array) glob($path . $filename . '*.xml') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    private function removeManagedRobotsBlock(string $content): string
    {
        $pattern = '/(?:\R|^)' . preg_quote(self::ROBOTS_BLOCK_START, '/') . '.*?' . preg_quote(self::ROBOTS_BLOCK_END, '/') . '\R?/s';

        return (string) preg_replace($pattern, PHP_EOL, $content);
    }

    private function normalizeRobotsLines(string $content): array
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $normalized = [];

        foreach ($lines as $line) {
            $line = $this->normalizeRobotsLine((string) $line);
            if ('' !== $line) {
                $normalized[] = $line;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeRobotsLine(string $line): string
    {
        return preg_replace('/\s+/', ' ', trim($line)) ?: '';
    }

    private function hasAllowDirective(array $directives): bool
    {
        foreach ($directives as $directive) {
            if (0 === stripos((string) $directive, 'Allow:')) {
                return true;
            }
        }

        return false;
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
