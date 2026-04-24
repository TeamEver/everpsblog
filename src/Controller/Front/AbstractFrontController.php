<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Controller\Front;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCache;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheTags;
use PrestaShop\PrestaShop\Core\Product\Search\Pagination;

require_once dirname(__DIR__, 3) . '/everpsblog.php';

abstract class AbstractFrontController extends \ModuleFrontController
{
    protected $page = 1;
    private $blogImageService;
    private $blogTaxonomyService;
    private $blogSortOrderService;
    private $blogFrontCache;
    private $blogFrontCacheInvalidator;
    private $qcdBuilderModule;
    private $qcdBuilderModuleResolved = false;

    public function getTemplateVarPage()
    {
        $pageName = $this->getPageName();
        $idLang = (int) $this->context->language->id;
        $seo = [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'robots' => '',
        ];

        if ($pageName === 'module-everpsblog-category' && ($idCategory = \Tools::getValue('id_ever_category'))) {
            $sql = 'SELECT ebcl.`title`, ebcl.`meta_title`, ebcl.`meta_description`, ebc.`indexable`, ebc.`follow`
                FROM `' . _DB_PREFIX_ . 'ever_blog_category_lang` ebcl
                LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category` ebc
                ON ebcl.`id_ever_category` = ebc.`id_ever_category`
                WHERE ebcl.`id_lang` = ' . (int) $idLang . '
                AND ebcl.`id_ever_category` = ' . (int) $idCategory;
        } elseif ($pageName === 'module-everpsblog-post' && ($idPost = \Tools::getValue('id_ever_post'))) {
            $sql = 'SELECT ebpl.`title`, ebpl.`meta_title`, ebpl.`meta_description`, ebp.`indexable`, ebp.`follow`
                FROM `' . _DB_PREFIX_ . 'ever_blog_post_lang` ebpl
                LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post` ebp
                ON ebpl.`id_ever_post` = ebp.`id_ever_post`
                WHERE ebpl.`id_lang` = ' . (int) $idLang . '
                AND ebpl.`id_ever_post` = ' . (int) $idPost;
        } elseif ($pageName === 'module-everpsblog-tag' && ($idTag = \Tools::getValue('id_ever_tag'))) {
            $sql = 'SELECT ebtl.`title`, ebtl.`meta_title`, ebtl.`meta_description`, ebt.`indexable`, ebt.`follow`
                FROM `' . _DB_PREFIX_ . 'ever_blog_tag_lang` ebtl
                LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_tag` ebt
                ON ebtl.`id_ever_tag` = ebt.`id_ever_tag`
                WHERE ebtl.`id_lang` = ' . (int) $idLang . '
                AND ebtl.`id_ever_tag` = ' . (int) $idTag;
        } elseif ($pageName === 'module-everpsblog-author' && ($idAuthor = \Tools::getValue('id_ever_author'))) {
            $sql = 'SELECT eba.`nickhandle` as title, ebal.`meta_title`, ebal.`meta_description`, eba.`indexable`, eba.`follow`
                FROM `' . _DB_PREFIX_ . 'ever_blog_author_lang` ebal
                LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_author` eba
                ON ebal.`id_ever_author` = eba.`id_ever_author`
                WHERE ebal.`id_lang` = ' . (int) $idLang . '
                AND ebal.`id_ever_author` = ' . (int) $idAuthor;
        }

        if (isset($sql)) {
            $seoMetas = $this->frontCacheRemember(
                __METHOD__,
                [$pageName, (int) ($idCategory ?? $idPost ?? $idTag ?? $idAuthor ?? 0), $idLang],
                function () use ($sql) {
                    $row = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

                    return is_array($row) ? $row : [];
                },
                $this->frontTagsForControllerEntity(
                    str_replace('module-everpsblog-', '', $pageName),
                    (int) ($idCategory ?? $idPost ?? $idTag ?? $idAuthor ?? 0)
                )
            );
            if (!is_array($seoMetas) || empty($seoMetas)) {
                $seoMetas = [];
            }

            if (!empty($seoMetas)) {
            $index = ((int) $seoMetas['indexable']) ? 'index' : 'noindex';
            $follow = ((int) $seoMetas['follow']) ? 'follow' : 'nofollow';

            $seo['title'] = $seoMetas['meta_title'] ?: $seoMetas['title'];
            $seo['description'] = $seoMetas['meta_description'];
            $seo['robots'] = $index . ', ' . $follow;
            }
        }

        $page = parent::getTemplateVarPage();
        $page['meta'] = $seo;

        return $page;
    }

    public function init()
    {
        parent::init();
        $this->assignHeaderConfiguration();

        $params = [];
        $controllerName = \Dispatcher::getInstance()->getController();

        \Hook::exec('beforeEverBlogInit', [
            'blog_page_name' => $controllerName,
        ]);

        switch ($controllerName) {
            case 'post':
                $params = $this->resolveCanonicalParams('ever_blog_post_lang', 'id_ever_post', (int) \Tools::getValue('id_ever_post'));
                break;
            case 'category':
                $params = $this->resolveCanonicalParams('ever_blog_category_lang', 'id_ever_category', (int) \Tools::getValue('id_ever_category'));
                break;
            case 'tag':
                $params = $this->resolveCanonicalParams('ever_blog_tag_lang', 'id_ever_tag', (int) \Tools::getValue('id_ever_tag'));
                break;
            case 'author':
                $params = $this->resolveCanonicalParams('ever_blog_author_lang', 'id_ever_author', (int) \Tools::getValue('id_ever_author'));
                break;
        }

        if ($params) {
            $canonicalUrl = $this->context->link->getModuleLink('everpsblog', $controllerName, $params);

            \Hook::exec('afterEverBlogInit', [
                'blog_page_name' => $controllerName,
                'param' => $params,
            ]);

            $this->canonicalRedirection($canonicalUrl);
        }
    }

    protected function transShop(string $message, array $parameters = []): string
    {
        return $this->context->getTranslator()->trans($message, $parameters, 'Modules.Everpsblog.Shop');
    }

    /**
     * @return \EverPsBlog|null
     */
    protected function getEverPsBlogModule(): ?\EverPsBlog
    {
        return $this->module instanceof \EverPsBlog ? $this->module : null;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getModuleConfigInMultipleLangs(string $key): array
    {
        $module = $this->getEverPsBlogModule();
        if (null === $module) {
            return [];
        }

        return (array) $module::getConfigInMultipleLangs($key);
    }

    private function assignHeaderConfiguration(): void
    {
        $backgroundColor = $this->normalizeHexColor((string) \Configuration::get('EVERBLOG_HEADER_BG_COLOR'), '#0a0f54');
        $titleColor = $this->normalizeHexColor((string) \Configuration::get('EVERBLOG_HEADER_TITLE_COLOR'), '#ffffff');

        $this->context->smarty->assign([
            'everpsblog_header_bg_color' => $backgroundColor,
            'everpsblog_header_title_color' => $titleColor,
        ]);
    }

    private function normalizeHexColor(string $color, string $default): string
    {
        $color = trim($color);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : $default;
    }


    /**
     * @return array<string, int|string>
     */
    private function resolveCanonicalParams(string $table, string $idColumn, int $resourceId): array
    {
        if ($resourceId <= 0) {
            return [];
        }

        $sql = sprintf(
            'SELECT `%s`, `link_rewrite` FROM `%s%s` WHERE `%s` = %d AND `id_lang` = %d',
            \pSQL($idColumn),
            _DB_PREFIX_,
            \pSQL($table),
            \pSQL($idColumn),
            $resourceId,
            (int) $this->context->language->id
        );

        $row = $this->frontCacheRemember(
            __METHOD__,
            [$table, $idColumn, $resourceId, (int) $this->context->language->id],
            function () use ($sql) {
                $data = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

                return is_array($data) ? $data : [];
            },
            $this->frontTagsForControllerEntity($this->frontControllerNameFromIdColumn($idColumn), $resourceId)
        );
        if (!is_array($row) || empty($row[$idColumn]) || empty($row['link_rewrite'])) {
            return [];
        }

        return [
            $idColumn => (int) $row[$idColumn],
            'link_rewrite' => (string) $row['link_rewrite'],
        ];
    }


    protected function renderQcdBuilderField(string $targetType, int $targetId, string $targetField, string $fallbackContent): string
    {
        $builder = $this->getQcdBuilderModule();
        if (!$builder || !method_exists($builder, 'renderTargetField')) {
            return $fallbackContent;
        }

        return (string) $builder->renderTargetField(
            $targetType,
            $targetId,
            $targetField,
            $fallbackContent,
            (int) $this->context->shop->id,
            (int) $this->context->language->id
        );
    }

    protected function getQcdBuilderModule(): ?\Module
    {
        if ($this->qcdBuilderModuleResolved) {
            return $this->qcdBuilderModule;
        }

        static $cachedQcdBuilderModule;
        static $cachedQcdBuilderModuleResolved = false;

        if (!$cachedQcdBuilderModuleResolved) {
            $cachedQcdBuilderModule = null;
            if (\Module::isInstalled('qcdpagebuilder') && \Module::isEnabled('qcdpagebuilder')) {
                $module = \Module::getInstanceByName('qcdpagebuilder');
                if ($module instanceof \Module && (bool) $module->active) {
                    if (!method_exists($module, 'isEnabledForShopContext') || (bool) $module->isEnabledForShopContext()) {
                        $cachedQcdBuilderModule = $module;
                    }
                }
            }
            $cachedQcdBuilderModuleResolved = true;
        }

        $this->qcdBuilderModule = $cachedQcdBuilderModule;
        $this->qcdBuilderModuleResolved = true;

        return $this->qcdBuilderModule;
    }

    protected function getTemplateVarPagination($total = 0)
    {
        $totalItems = (int) $total;
        $page = (int) \Tools::getValue('page') ?: 1;
        $totalPerPage = (int) \Configuration::get('EVERPSBLOG_PAGINATION') ?: 10;

        $pagination = new Pagination();
        $pagination->setPage($page)->setPagesCount((int) ceil($totalItems / $totalPerPage));

        $pages = array_map(function ($link) {
            $link['url'] = $this->updateQueryString(['page' => $link['page'] > 1 ? $link['page'] : null]);

            return $link;
        }, $pagination->buildLinks());

        $pages = array_filter($pages, function ($entry) use ($pagination) {
            if ($entry['type'] === 'previous' && $pagination->getPage() === 1) {
                return false;
            }

            if ($entry['type'] === 'next' && $pagination->getPagesCount() === $pagination->getPage()) {
                return false;
            }

            return true;
        });

        $itemsShownFrom = $totalItems > 0 ? ($totalPerPage * ($page - 1)) + 1 : 0;
        $itemsShownTo = $totalPerPage * $page;

        return [
            'total_items' => $totalItems,
            'items_shown_from' => $itemsShownFrom,
            'items_shown_to' => ($itemsShownTo <= $totalItems) ? $itemsShownTo : $totalItems,
            'current_page' => $pagination->getPage(),
            'pages_count' => $pagination->getPagesCount(),
            'pages' => $pages,
            'should_be_displayed' => $pagination->getPagesCount() > 1,
        ];
    }

    protected function canonicalRedirection($canonicalUrl = '')
    {
        if (!$canonicalUrl || !\Configuration::get('PS_CANONICAL_REDIRECT') || \Tools::strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET') {
            return;
        }

        $matchUrl = (\Configuration::get('PS_SSL_ENABLED') && ($this->ssl || \Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? 'https://' : 'http://')
            . $_SERVER['HTTP_HOST']
            . $_SERVER['REQUEST_URI'];

        $matchUrl = rawurldecode($matchUrl);
        if (preg_match('/^' . \Tools::pRegexp(rawurldecode($canonicalUrl), '/') . '([&?].*)?$/', $matchUrl)) {
            return;
        }

        $params = [];
        $urlDetails = parse_url($canonicalUrl);

        if (!empty($urlDetails['query'])) {
            parse_str($urlDetails['query'], $query);
            foreach ($query as $key => $value) {
                $params[\Tools::safeOutput($key)] = \Tools::safeOutput($value);
            }
        }

        $excludedKey = ['isolang', 'id_lang', 'controller', 'id_ever_category', 'id_ever_post', 'id_ever_tag', 'id_ever_author', 'fc', 'module'];
        foreach ($_GET as $key => $value) {
            if (!in_array($key, $excludedKey, true) && \Validate::isUrl($key) && \Validate::isUrl($value)) {
                $params[\Tools::safeOutput($key)] = \Tools::safeOutput($value);
            }
        }

        $strParams = http_build_query($params, '', '&');
        $finalUrl = !empty($strParams)
            ? preg_replace('/^([^?]*)?.*$/', '$1', $canonicalUrl) . '?' . $strParams
            : preg_replace('/^([^?]*)?.*$/', '$1', $canonicalUrl);

        \Context::getContext()->cookie->disallowWriting();
        header('HTTP/1.0 301 Moved');
        header('Cache-Control: no-cache');
        \Tools::redirectLink($finalUrl);
    }

    /**
     * @param array<int, array<string, int|string>> $localizedParamsByLang
     */
    protected function assignHreflangLinks(string $controllerName, array $localizedParamsByLang = []): void
    {
        $idShop = (int) $this->context->shop->id;
        $defaultLangId = (int) \Configuration::get('PS_LANG_DEFAULT', null, null, $idShop);
        $currentPage = (int) \Tools::getValue('page');
        $hreflangLinks = [];
        $xDefaultHref = '';
        $requiredParams = $this->getHreflangRequiredParams($controllerName);

        foreach (\Language::getLanguages(true, $idShop) as $language) {
            $idLang = (int) ($language['id_lang'] ?? 0);
            if ($idLang <= 0) {
                continue;
            }

            $params = $localizedParamsByLang[$idLang] ?? [];
            if (!$this->hasHreflangRequiredParams($params, $requiredParams)) {
                continue;
            }

            if ($currentPage > 1) {
                $params['page'] = $currentPage;
            }

            $href = $this->context->link->getModuleLink(
                $this->module->name,
                $controllerName,
                $params,
                true,
                $idLang,
                $idShop
            );

            if (!$href) {
                continue;
            }

            $hreflangLinks[] = [
                'hreflang' => $this->formatHreflangCode((array) $language),
                'href' => $href,
            ];

            if ($idLang === $defaultLangId) {
                $xDefaultHref = $href;
            }
        }

        if (!$xDefaultHref && !empty($hreflangLinks[0]['href'])) {
            $xDefaultHref = (string) $hreflangLinks[0]['href'];
        }

        $this->context->smarty->assign([
            'hreflang_links' => $hreflangLinks,
            'hreflang_x_default' => $xDefaultHref,
        ]);
    }

    /**
     * @return string[]
     */
    private function getHreflangRequiredParams(string $controllerName): array
    {
        $requiredParamsByController = [
            'post' => ['id_ever_post', 'link_rewrite'],
            'category' => ['id_ever_category', 'link_rewrite'],
            'tag' => ['id_ever_tag', 'link_rewrite'],
            'author' => ['id_ever_author', 'link_rewrite'],
        ];

        return $requiredParamsByController[$controllerName] ?? [];
    }

    /**
     * @param array<string, int|string> $params
     * @param string[] $requiredParams
     */
    private function hasHreflangRequiredParams(array $params, array $requiredParams): bool
    {
        foreach ($requiredParams as $requiredParam) {
            if (!array_key_exists($requiredParam, $params) || (string) $params[$requiredParam] === '') {
                return false;
            }

            if (strpos($requiredParam, 'id_ever_') === 0 && (int) $params[$requiredParam] <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    protected function getLocalizedParamsByLang(string $langTable, string $idColumn, int $resourceId): array
    {
        if ($resourceId <= 0) {
            return [];
        }

        $sql = sprintf(
            'SELECT `id_lang`, `%s`, `link_rewrite`
            FROM `%s%s`
            WHERE `%s` = %d',
            \pSQL($idColumn),
            _DB_PREFIX_,
            \pSQL($langTable),
            \pSQL($idColumn),
            $resourceId
        );

        $rows = $this->frontCacheRemember(
            __METHOD__,
            [$langTable, $idColumn, $resourceId],
            function () use ($sql) {
                $data = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

                return is_array($data) ? $data : [];
            },
            $this->frontTagsForControllerEntity($this->frontControllerNameFromIdColumn($idColumn), $resourceId)
        );
        if (!$rows) {
            return [];
        }

        $paramsByLang = [];
        foreach ($rows as $row) {
            $idLang = (int) ($row['id_lang'] ?? 0);
            $idValue = (int) ($row[$idColumn] ?? 0);
            $linkRewrite = (string) ($row['link_rewrite'] ?? '');

            if ($idLang <= 0 || $idValue <= 0 || $linkRewrite === '') {
                continue;
            }

            $paramsByLang[$idLang] = [
                $idColumn => $idValue,
                'link_rewrite' => $linkRewrite,
            ];
        }

        return $paramsByLang;
    }

    private function formatHreflangCode(array $language): string
    {
        $locale = (string) ($language['locale'] ?? $language['language_code'] ?? $language['iso_code'] ?? '');
        $locale = str_replace('_', '-', $locale);
        $parts = array_values(array_filter(explode('-', $locale)));

        if (empty($parts)) {
            return 'x-default';
        }

        $code = strtolower((string) $parts[0]);
        if (!empty($parts[1])) {
            $code .= '-' . strtoupper((string) $parts[1]);
        }

        return $code;
    }

    protected function getBlogImageService()
    {
        if (!$this->blogImageService) {
            $this->blogImageService = new \PrestaShop\Module\Everpsblog\Service\BlogImageService();
        }

        return $this->blogImageService;
    }

    protected function getBlogTaxonomyService()
    {
        if (!$this->blogTaxonomyService) {
            $this->blogTaxonomyService = new \PrestaShop\Module\Everpsblog\Service\BlogTaxonomyService();
        }

        return $this->blogTaxonomyService;
    }

    protected function getBlogSortOrderService()
    {
        if (!$this->blogSortOrderService) {
            $this->blogSortOrderService = new \PrestaShop\Module\Everpsblog\Service\BlogSortOrderService();
        }

        return $this->blogSortOrderService;
    }

    protected function getBlogFrontCacheService(): BlogFrontCache
    {
        if (!$this->blogFrontCache) {
            $this->blogFrontCache = new BlogFrontCache();
        }

        return $this->blogFrontCache;
    }

    protected function getBlogFrontCacheInvalidatorService(): BlogFrontCacheInvalidator
    {
        if (!$this->blogFrontCacheInvalidator) {
            $this->blogFrontCacheInvalidator = new BlogFrontCacheInvalidator();
        }

        return $this->blogFrontCacheInvalidator;
    }

    /**
     * @param array<int|string, mixed> $parts
     * @param callable(): mixed $resolver
     * @param string[] $tags
     * @param null|callable(mixed): array<int, string> $dynamicTagsResolver
     *
     * @return mixed
     */
    protected function frontCacheRemember(string $scope, array $parts, callable $resolver, array $tags = [], ?callable $dynamicTagsResolver = null)
    {
        return $this->getBlogFrontCacheService()->remember($scope, $parts, $resolver, $tags, $dynamicTagsResolver);
    }

    /**
     * @param mixed $items
     * @param string[] $idFields
     *
     * @return string[]
     */
    protected function frontExtractEntityTags($items, string $entityType, array $idFields = ['id']): array
    {
        if (!is_iterable($items)) {
            return [];
        }

        $tags = [];
        foreach ($items as $item) {
            $entityId = $this->frontExtractEntityId($item, $idFields);
            if ($entityId <= 0) {
                continue;
            }

            $tags[] = $this->frontEntityTag($entityType, $entityId);
        }

        return array_values(array_unique(array_filter($tags)));
    }

    protected function frontEntityTag(string $entityType, int $entityId): string
    {
        switch ($entityType) {
            case 'post':
                return BlogFrontCacheTags::post($entityId);
            case 'category':
                return BlogFrontCacheTags::category($entityId);
            case 'tag':
                return BlogFrontCacheTags::tag($entityId);
            case 'author':
                return BlogFrontCacheTags::author($entityId);
            case 'comment':
                return BlogFrontCacheTags::comment($entityId);
        }

        return trim((string) preg_replace('/[^A-Za-z0-9_.-]+/', '.', $entityType . '.' . $entityId), '.');
    }

    /**
     * @return string[]
     */
    private function frontTagsForControllerEntity(string $controllerName, int $entityId): array
    {
        if ($entityId <= 0) {
            return [];
        }

        switch ($controllerName) {
            case 'post':
            case 'category':
            case 'tag':
            case 'author':
                return [$this->frontEntityTag($controllerName, $entityId)];
        }

        return [];
    }

    private function frontControllerNameFromIdColumn(string $idColumn): string
    {
        switch ($idColumn) {
            case 'id_ever_post':
                return 'post';
            case 'id_ever_category':
                return 'category';
            case 'id_ever_tag':
                return 'tag';
            case 'id_ever_author':
                return 'author';
        }

        return '';
    }

    /**
     * @param mixed $item
     * @param string[] $idFields
     */
    private function frontExtractEntityId($item, array $idFields): int
    {
        foreach ($idFields as $idField) {
            if (is_array($item) && isset($item[$idField])) {
                return (int) $item[$idField];
            }

            if (is_object($item) && isset($item->{$idField})) {
                return (int) $item->{$idField};
            }
        }

        return 0;
    }
}
