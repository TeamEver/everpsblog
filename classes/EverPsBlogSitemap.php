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

class EverPsBlogSitemap extends ObjectModel
{
    /**
     *
     * @var \XMLWriter
     */
    private $writer;
    private $domain;
    private $path;
    private $filename = 'sitemap';
    private $current_item = 0;
    private $current_sitemap = 0;

    const EXT = '.xml';
    const SCHEMA = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    const DEFAULT_PRIORITY = 0.5;
    const SEPERATOR = '-';
    const INDEX_SUFFIX = 'indexable';

    /**
     *
     * @param string $domain
     */
    public function __construct($domain)
    {
        $this->setDomain($domain);
    }

    /**
     * Sets root path of the website, starting with http:// or https://
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Returns root path of the website
     *
     * @return string
     */
    private function getDomain()
    {
        return $this->domain;
    }

    /**
     * Returns XMLWriter object instance
     *
     * @return \XMLWriter
     */
    private function getWriter()
    {
        return $this->writer;
    }

    /**
     * Assigns XMLWriter object instance
     *
     * @param \XMLWriter $writer
     */
    private function setWriter(\XMLWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * Returns path of sitemaps
     *
     * @return string
     */
    private function getPath()
    {
        return $this->path;
    }

    /**
     * Sets paths of sitemaps
     *
     * @param string $path
     * @return Sitemap
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Returns filename of sitemap file
     *
     * @return string
     */
    private function getFilename()
    {
        return $this->filename;
    }

    /**
     * Sets filename of sitemap file
     *
     * @param string $filename
     * @return Sitemap
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Returns current item count
     *
     * @return int
     */
    private function getCurrentItem()
    {
        return $this->current_item;
    }

    /**
     * Increases item counter
     *
     */
    private function incCurrentItem()
    {
        $this->current_item = $this->current_item + 1;
    }

    /**
     * Returns current sitemap file count
     *
     * @return int
     */
    private function getCurrentSitemap()
    {
        return $this->current_sitemap;
    }

    /**
     * Increases sitemap file count
     *
     */
    private function incCurrentSitemap()
    {
        $this->current_sitemap = $this->current_sitemap + 1;
    }

    /**
     * Prepares sitemap XML document
     *
     */
    private function startSitemap()
    {
        $this->setWriter(new \XMLWriter());
        if ($this->getCurrentSitemap()) {
            $this->getWriter()->openURI(
                $this->getPath()
                . $this->getFilename()
                . self::SEPERATOR
                . $this->getCurrentSitemap()
                . self::EXT
            );
        } else {
            $this->getWriter()->openURI(
                $this->getPath() . $this->getFilename() . self::EXT
            );
        }
        $this->getWriter()->startDocument('1.0', 'UTF-8');
        $this->getWriter()->setIndent(true);
        $this->getWriter()->startElement('urlset');
        $this->getWriter()->writeAttribute('xmlns', self::SCHEMA);
    }

    /**
     * Adds an item to sitemap
     * @param string $loc URL of the page.
     * @param string|null $priority
     * @param string|null $changefreq
     * @param string|int|null $lastmod
     * @return Sitemap
     */
    public function addItem($loc, $priority = self::DEFAULT_PRIORITY, $changefreq = null, $lastmod = null)
    {
        if (($this->getCurrentItem() % Configuration::get('EVERBLOG_SITEMAP_NUMBER')) == 0) {
            if ($this->getWriter() instanceof \XMLWriter) {
                $this->endSitemap();
            }
            $this->startSitemap();
            $this->incCurrentSitemap();
        }
        $this->incCurrentItem();
        $this->getWriter()->startElement('url');
        $this->getWriter()->writeElement('loc', $loc);
        if ($priority !== null) {
            $this->getWriter()->writeElement('priority', $priority);
        }
        if ($changefreq) {
            $this->getWriter()->writeElement('changefreq', $changefreq);
        }
        if ($lastmod) {
            $this->getWriter()->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
        }
        $this->getWriter()->endElement();
        return $this;
    }

    /**
     * Prepares given date for sitemap
     *
     * @param string $date Unix timestamp
     * @return string Year-Month-Day formatted date.
     */
    private function getLastModifiedDate($date)
    {
        if (ctype_digit($date)) {
            return date('Y-m-d', $date);
        } else {
            $date = strtotime($date);
            return date('Y-m-d', $date);
        }
    }

    /**
     * Finalizes tags of sitemap XML document.
     *
     */
    private function endSitemap()
    {
        if (!$this->getWriter()) {
            $this->startSitemap();
        }
        $this->getWriter()->endElement();
        $this->getWriter()->endDocument();
    }

    /**
     * Writes Google sitemap index for generated sitemap files
     *
     * @param string $loc
     * @param string|int
     */
    public function createSitemapIndex($loc, $lastmod = 'Today')
    {
        $this->endSitemap();
        $indexwriter = new \XMLWriter();
        $indexwriter->openURI(
            $this->getPath()
            . $this->getFilename()
            . self::SEPERATOR
            . self::INDEX_SUFFIX
            . self::EXT
        );
        $indexwriter->startDocument('1.0', 'UTF-8');
        $indexwriter->setIndent(true);
        $indexwriter->startElement('sitemapindex');
        $indexwriter->writeAttribute('xmlns', self::SCHEMA);
        for ($index = 0; $index < $this->getCurrentSitemap(); $index++) {
            $indexwriter->startElement('sitemap');
            $indexwriter->writeElement(
                'loc',
                $loc
                . $this->getFilename()
                . ($index ? self::SEPERATOR . $index : '')
                . self::EXT
            );
            $indexwriter->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
            $indexwriter->endElement();
        }
        $indexwriter->endElement();
        $indexwriter->endDocument();
    }

    public static function getSitemapIndexes()
    {
        $siteUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $indexes = [];
        $sitemap_indexes_dir = glob(_PS_ROOT_DIR_ . '/*');
        foreach ($sitemap_indexes_dir as $index) {
            if (is_file($index)
                && pathinfo($index, PATHINFO_EXTENSION) == 'xml'
                && strpos(basename($index), 'indexable')
            ) {
                $indexes[] = $siteUrl . basename($index);
            }
        }
        return (array) $indexes;
    }

    public static function getSitemaps()
    {
        $siteUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $sitemaps = [];
        $sitemap_dir = glob(_PS_ROOT_DIR_ . '/*');
        foreach ($sitemap_dir as $sitemap) {
            if (is_file($sitemap)
                && pathinfo($sitemap, PATHINFO_EXTENSION) == 'xml'
                && !strpos(basename($sitemap), 'indexable')
            ) {
                $sitemaps[] = $siteUrl . basename($sitemap);
            }
        }
        return (array) $sitemaps;
    }
}
