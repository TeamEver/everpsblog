<?php
/**
 * 2019-2025 Team Ever
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
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once __DIR__ . '/vendor/autoload.php';
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\Module\Everpsblog\Entity\Post as EverPsBlogPost;

class EverPsBlog extends Module
{
    private $html;
    private $postErrors = [];
    private $postSuccess = [];
    private $blogInstallService;
    private $blogTaxonomyService;
    private $blogImageService;
    private $blogCleanerService;
    private $blogSortOrderService;
    private $blogSitemapService;
    private $blogScheduledTaskRunner;
    private $blogRedirectService;
    private $blogFrontCacheInvalidator;
    private $legacyImportAdapter;
    public static $route = [];

    public function __construct()
    {
        $this->name = 'everpsblog';
        $this->tab = 'front_office_features';
        $this->version = '6.0.10';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_folder = _PS_MODULE_DIR_ . 'everpsblog';
        parent::__construct();
        $this->displayName = $this->transAdmin('Ever Blog');
        $this->description = $this->transAdmin('Simply a blog');
        $this->confirmUninstall = $this->transAdmin('Do you really want to uninstall this module ?');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->context = Context::getContext();
    }

    private function transAdmin(string $message, array $parameters = []): string
    {
        return Context::getContext()->getTranslator()->trans($message, $parameters, 'Modules.Everpsblog.Admin');
    }

    public function install()
    {
        // Install SQL
        include dirname(__FILE__).'/install/install.php';
        // Create hooks
        include dirname(__FILE__).'/install/hooks-install.php';
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_ . 'post')) {
            mkdir(_PS_IMG_DIR_ . 'post', 0755, true);
        }
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_ . 'category')) {
            mkdir(_PS_IMG_DIR_ . 'category', 0755, true);
        }
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_ . 'tag')) {
            mkdir(_PS_IMG_DIR_ . 'tag', 0755, true);
        }
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_ . 'author')) {
            mkdir(_PS_IMG_DIR_ . 'author', 0755, true);
        }
        Configuration::updateValue('EVERBLOG_SHOW_HOME', true);
        // Creating root + unclassed categories through application service
        $this->getBlogInstallService()->seedRootAndUnclassedCategories($this);
        $translationsInstalled = $this->installBundledTranslations();
        // Install
        return parent::install()
            && $this->registerHook('actionObjectLanguageAddAfter')
            && $this->installModuleTab(
                'AdminEverPsBlog',
                'IMPROVE',
                $this->transAdmin('Blog')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogPost',
                'AdminEverPsBlog',
                $this->transAdmin('Posts')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogCategory',
                'AdminEverPsBlog',
                $this->transAdmin('Categories')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogTag',
                'AdminEverPsBlog',
                $this->transAdmin('Tags')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogComment',
                'AdminEverPsBlog',
                $this->transAdmin('Comments')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogAuthor',
                'AdminEverPsBlog',
                $this->transAdmin('Authors')
            )
            && Configuration::updateValue('EVERPSBLOG_ROUTE', 'blog')
            && Configuration::updateValue('EVERBLOG_ADMIN_EMAIL', 1)
            && Configuration::updateValue('EVERBLOG_EMPTY_TRASH', 7)
            && Configuration::updateValue('EVERBLOG_ALLOW_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_CHECK_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_BANNED_USERS', '')
            && Configuration::updateValue('EVERBLOG_BANNED_IP', '')
            && Configuration::updateValue('EVERPSBLOG_PAGINATION', '10')
            && Configuration::updateValue('EVERPSBLOG_HOME_NBR', '12')
            && Configuration::updateValue('EVERPSBLOG_PRODUCT_NBR', '4')
            && Configuration::updateValue('EVERPSBLOG_EXCERPT', '200')
            && Configuration::updateValue('EVERPSBLOG_TITLE_LENGTH', '150')
            && Configuration::updateValue('EVERBLOG_PRODUCT_COLUMNS', 0)
            && Configuration::updateValue('EVERBLOG_CATEG_COLUMNS', 1)
            && Configuration::updateValue('EVERPSBLOG_BLOG_LAYOUT', 'layouts/layout-full-width.tpl')
            && Configuration::updateValue('EVERPSBLOG_POST_LAYOUT', 'layouts/layout-full-width.tpl')
            && Configuration::updateValue('EVERPSBLOG_CAT_LAYOUT', 'layouts/layout-full-width.tpl')
            && Configuration::updateValue('EVERPSBLOG_AUTHOR_LAYOUT', 'layouts/layout-full-width.tpl')
            && Configuration::updateValue('EVERPSBLOG_TAG_LAYOUT', 'layouts/layout-full-width.tpl')
            && Configuration::updateValue('EVERBLOG_SHOW_FEAT_POST', 1)
            && Configuration::updateValue('EVERBLOG_SHOW_RELATED_POSTS', 0)
            && Configuration::updateValue('EVERBLOG_SHOW_POST_TAGS', 1)
            && Configuration::updateValue('EVERBLOG_SHOW_AUTHOR', 1)
            && Configuration::updateValue('EVERBLOG_DEFAULT_AUTHOR_NAME', Configuration::get('PS_SHOP_NAME'))
            && Configuration::updateValue('EVERBLOG_DEFAULT_AUTHOR_ID', 0)
            && Configuration::updateValue('EVERBLOG_SITEMAP_NUMBER', 5000)
            && Configuration::updateValue('EVERBLOG_MAIN_TITLE', (function () {
                $title = [];
                foreach (Language::getLanguages(false) as $language) {
                    $title[$language['id_lang']] = 'Our blog';
                }
                return $title;
            })())
            && Configuration::updateValue('EVERBLOG_HEADER_BG_COLOR', '#0a0f54')
            && Configuration::updateValue('EVERBLOG_HEADER_TITLE_COLOR', '#ffffff')
            && $translationsInstalled
            && $this->checkAndFixDatabase()
            && $this->checkHooks()
            && $this->checkObligatoryHooks();
    }

    public function uninstall()
    {
        $translationsRemoved = $this->uninstallBundledTranslations();
        include dirname(__FILE__).'/install/uninstall.php';
        include dirname(__FILE__).'/install/hooks-uninstall.php';
        // include dirname(__FILE__).'/install/images-uninstall.php';
        Db::getInstance()->delete(
            'hook_module',
            'id_module = ' . (int) $this->id
        );
        Configuration::deleteByName('EVERBLOG_CATEG_COLUMNS');
        Configuration::deleteByName('EVERBLOG_SHOW_POST_TAGS');
        Configuration::deleteByName('EVERBLOG_DEFAULT_AUTHOR_ID');
        return $translationsRemoved
            && parent::uninstall()
            && $this->uninstallModuleTab('AdminEverPsBlog')
            && $this->uninstallModuleTab('AdminEverPsBlogPost')
            && $this->uninstallModuleTab('AdminEverPsBlogCategory')
            && $this->uninstallModuleTab('AdminEverPsBlogTag')
            && $this->uninstallModuleTab('AdminEverPsBlogComment')
            && $this->uninstallModuleTab('AdminEverPsBlogAuthor');
    }

    private function installBundledTranslations(): bool
    {
        try {
            $catalog = new \PrestaShop\Module\Everpsblog\Service\ModuleTranslationCatalogService();
            $catalog->importFromFile(__DIR__ . '/translations/everpsblog-translations-20260424-170745.json');

            return true;
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog] Unable to import bundled translations during module installation: ' . $exception->getMessage(),
                3
            );

            return false;
        }
    }

    /**
     * Ensure bundled module translations are imported when a new language is installed.
     *
     * @param array<string, mixed> $params
     */
    public function hookActionObjectLanguageAddAfter(array $params): void
    {
        $object = $params['object'] ?? null;
        if (!$object instanceof \Language) {
            return;
        }

        $idLang = (int) $object->id;
        if ($idLang <= 0) {
            return;
        }

        try {
            $catalog = new \PrestaShop\Module\Everpsblog\Service\ModuleTranslationCatalogService();
            $catalog->importLanguageFromFile(
                __DIR__ . '/translations/everpsblog-translations-20260424-170745.json',
                $idLang
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                sprintf(
                    '[everpsblog] Unable to import bundled translations for language #%d: %s',
                    $idLang,
                    $exception->getMessage()
                ),
                3
            );
        }
    }

    private function uninstallBundledTranslations(): bool
    {
        try {
            $catalog = new \PrestaShop\Module\Everpsblog\Service\ModuleTranslationCatalogService();
            $catalog->deleteModuleTranslations();

            return true;
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog] Unable to remove bundled translations during module uninstall: ' . $exception->getMessage(),
                3
            );

            return false;
        }
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int) Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverPsBlog') {
            $tab->icon = 'icon-team-ever';
        }
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = $tabName;
        }
        return $tab->add();
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int) Tab::getIdFromClassName($tabClass));
        return $tab->delete();
    }

    private function getBlogInstallService()
    {
        if (!$this->blogInstallService) {
            $this->blogInstallService = new \PrestaShop\Module\Everpsblog\Service\BlogInstallService();
        }

        return $this->blogInstallService;
    }

    private function getBlogTaxonomyService()
    {
        if (!$this->blogTaxonomyService) {
            $this->blogTaxonomyService = new \PrestaShop\Module\Everpsblog\Service\BlogTaxonomyService();
        }

        return $this->blogTaxonomyService;
    }

    private function getBlogImageService()
    {
        if (!$this->blogImageService) {
            $this->blogImageService = new \PrestaShop\Module\Everpsblog\Service\BlogImageService();
        }

        return $this->blogImageService;
    }

    private function getBlogFrontCacheInvalidator()
    {
        if (!$this->blogFrontCacheInvalidator) {
            $this->blogFrontCacheInvalidator = new \PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator();
        }

        return $this->blogFrontCacheInvalidator;
    }


    private function getFrontLocalizedCategories($idLang, $idShop)
    {
        $sql = new DbQuery();
        $sql->select('c.id_ever_category, c.is_root_category, cl.title, cl.link_rewrite');
        $sql->from('ever_blog_category', 'c');
        $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
        $sql->where('c.active = 1');
        $sql->orderBy('cl.title ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }

    private function getFrontLocalizedTags($idLang, $idShop)
    {
        $sql = new DbQuery();
        $sql->select('t.id_ever_tag, tl.title, tl.link_rewrite');
        $sql->from('ever_blog_tag', 't');
        $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $idShop);
        $sql->where('t.active = 1');
        $sql->orderBy('tl.title ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }

    private function getStarredPostsForHome($idLang, $idShop, $limit)
    {
        $sql = new DbQuery();
        $sql->select('p.id_ever_post, p.id_default_category, pl.title, pl.link_rewrite, pl.excerpt, pl.content, dcl.id_ever_category AS default_category_id, dcl.title AS default_category_title, dcl.link_rewrite AS default_category_link_rewrite');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
        $sql->leftJoin('ever_blog_category_lang', 'dcl', 'dcl.id_ever_category = p.id_default_category AND dcl.id_lang = ' . (int) $idLang);
        $sql->where('p.post_status = "published"');
        $sql->where('p.starred = 1');
        $sql->orderBy('p.date_add DESC, p.id_ever_post DESC');
        $sql->limit((int) $limit);

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
        $posts = [];
        foreach ($rows as $row) {
            $post = (object) $row;
            if (!empty($row['default_category_id'])) {
                $post->default_cat_obj = (object) [
                    'id_ever_category' => (int) $row['default_category_id'],
                    'title' => $row['default_category_title'],
                    'link_rewrite' => $row['default_category_link_rewrite'],
                ];
            } else {
                $post->default_cat_obj = null;
            }
            $posts[] = $post;
        }

        return $posts;
    }

    private function getBlogCleanerService()
    {
        if (!$this->blogCleanerService) {
            $this->blogCleanerService = new \PrestaShop\Module\Everpsblog\Service\BlogCleanerService();
        }

        return $this->blogCleanerService;
    }

    private function getBlogSortOrderService()
    {
        if (!$this->blogSortOrderService) {
            $this->blogSortOrderService = new \PrestaShop\Module\Everpsblog\Service\BlogSortOrderService();
        }

        return $this->blogSortOrderService;
    }

    private function getBlogSitemapService()
    {
        if (!$this->blogSitemapService) {
            $this->blogSitemapService = new \PrestaShop\Module\Everpsblog\Service\BlogSitemapService();
        }

        return $this->blogSitemapService;
    }

    private function getBlogScheduledTaskRunner()
    {
        if (!$this->blogScheduledTaskRunner) {
            $this->blogScheduledTaskRunner = new \PrestaShop\Module\Everpsblog\Service\BlogScheduledTaskRunner();
        }

        return $this->blogScheduledTaskRunner;
    }

    private function getBlogRedirectService()
    {
        if (!$this->blogRedirectService) {
            $this->blogRedirectService = new \PrestaShop\Module\Everpsblog\Service\BlogRedirectService();
        }

        return $this->blogRedirectService;
    }

    private function getLegacyImportAdapter()
    {
        if (!$this->legacyImportAdapter) {
            $this->legacyImportAdapter = new \PrestaShop\Module\Everpsblog\Service\LegacyImportAdapter(
                $this->getBlogImageService()
            );
        }

        return $this->legacyImportAdapter;
    }

    private function buildLegacyPostCacheSnapshot($post): array
    {
        if (!is_object($post)) {
            return [
                'author_id' => 0,
                'default_category_id' => 0,
                'category_ids' => [],
                'tag_ids' => [],
            ];
        }

        return [
            'author_id' => (int) ($post->id_author ?? 0),
            'default_category_id' => (int) ($post->id_default_category ?? 0),
            'category_ids' => $this->normalizeLegacyCacheIds($post->post_categories ?? []),
            'tag_ids' => $this->normalizeLegacyCacheIds($post->post_tags ?? []),
        ];
    }

    private function normalizeLegacyCacheIds($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    /**
     * Add link rewrite rule
     * @see https://stackoverflow.com/questions/49430883/creating-a-url-rewrite-module-in-prestashop
     */
    public function hookModuleRoutes($params)
    {
        $base_route = Configuration::get('EVERPSBLOG_ROUTE') ? Configuration::get('EVERPSBLOG_ROUTE') : 'blog';
        return [
            'module-everpsblog-blog' => [
                'controller' => 'blog',
                'rule' => $base_route,
                'keywords' => [
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'blog',
                ],
            ],
            'module-everpsblog-search' => [
                'controller' => 'search',
                'rule' => $base_route . '/search',
                'keywords' => [
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'search',
                ],
            ],
            'module-everpsblog-category' => [
                'controller' => 'category',
                'rule' => $base_route . '/category{/:id_ever_category}-{:link_rewrite}',
                'keywords' => [
                    'id_ever_category' => ['regexp' => '[0-9]+', 'param' => 'id_ever_category'],
                    'link_rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'category',
                ],
            ],
            'module-everpsblog-post' => [
                'controller' => 'post',
                'rule' => $base_route . '/post{/:id_ever_post}-{:link_rewrite}',
                'keywords' => [
                    'id_ever_post' => ['regexp' => '[0-9]+', 'param' => 'id_ever_post'],
                    'link_rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'post',
                ],
            ],
            'module-everpsblog-tag' => [
                'controller' => 'tag',
                'rule' => $base_route . '/tag{/:id_ever_tag}-{:link_rewrite}',
                'keywords' => [
                    'id_ever_tag' => ['regexp' => '[0-9]+', 'param' => 'id_ever_tag'],
                    'link_rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'tag',
                ],
            ],
            'module-everpsblog-author' => [
                'controller' => 'author',
                'rule' => $base_route . '/author{/:id_ever_author}-{:link_rewrite}',
                'keywords' => [
                    'id_ever_author' => ['regexp' => '[0-9]+', 'param' => 'id_ever_author'],
                    'link_rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'author',
                ],
            ],
            'module-everpsblog-feed' => [
                'controller' => 'feed',
                'rule' => $base_route . '/feed',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'feed',
                ],
            ],
            'module-everpsblog-filter' => [
                'controller' => 'filter',
                'rule' => $base_route . '/filter',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'filter',
                ],
            ],
            'module-everpsblog-customercomments' => [
                'controller' => 'customercomments',
                'rule' => $base_route . '/customercomments',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'customercomments',
                ],
            ],
        ];
    }

    public function hookActionDispatcherBefore($params)
    {
        $controllerType = isset($params['controller_type']) ? (int) $params['controller_type'] : 0;
        if (class_exists('Dispatcher') && defined('Dispatcher::FC_FRONT') && $controllerType > 0 && $controllerType !== (int) Dispatcher::FC_FRONT) {
            return;
        }

        $this->redirectLegacyWordPressUrl();
    }

    private function redirectLegacyWordPressUrl()
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            return;
        }

        $context = Context::getContext();
        $shopId = isset($context->shop) ? (int) $context->shop->id : (int) Configuration::get('PS_SHOP_DEFAULT');
        $redirect = $this->getBlogRedirectService()->findRedirectForCurrentRequest($shopId);
        if (!is_array($redirect) || empty($redirect['target_url'])) {
            return;
        }

        $targetUrl = str_replace(["\r", "\n"], '', (string) $redirect['target_url']);
        if ('' === $targetUrl) {
            return;
        }

        header('Location: ' . $targetUrl, true, (int) ($redirect['http_code'] ?? 301));
        exit;
    }

    public function clearEverblogContent()
    {
        // Remove old posts, categories and tags.
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_post');
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_post_lang');
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_category');
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_category_lang');
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_tag');
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_post_category');
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ever_blog_post_tag');
    }

    public function migrateMagentoToEverblog()
    {
        $shopId = (int) Context::getContext()->shop->id;
        $langId = (int) Context::getContext()->language->id;

        // Delete the current Everblog content.
        $this->clearEverblogContent();

        // Fetch categories, tags and posts from Magento.
        $categories = Db::getInstance()->executeS('SELECT * FROM aw_blog_cat');
        $tags = Db::getInstance()->executeS('SELECT * FROM aw_blog_tags');
        $posts = Db::getInstance()->executeS('SELECT * FROM aw_blog');

        // Fetch all PrestaShop group IDs.
        $groups = Db::getInstance()->executeS('SELECT id_group FROM ' . _DB_PREFIX_ . 'group');
        $groupIds = array_column($groups, 'id_group');
        $allowedGroupsJson = json_encode($groupIds); // Convertir en JSON

        // Create the default "Uncategorized" category.
        $rootCategoryId = $this->getBlogInstallService()->ensureRootCategory($shopId);
        $defaultCategoryId = $this->getBlogInstallService()->ensureUnclassedCategory($shopId, $rootCategoryId, $this);

        // Insert categories into Everblog.
        foreach ($categories as $category) {
            $newCategory = new EverPsBlogCategory();
            $newCategory->title = [$langId => $category['title']];
            $newCategory->meta_title = [$langId => $category['meta_keywords']];
            $newCategory->meta_description = [$langId => $category['meta_description']];
            $newCategory->link_rewrite = [$langId => Tools::link_rewrite($category['title'])];
            $newCategory->date_add = date('Y-m-d H:i:s');
            $newCategory->date_upd = date('Y-m-d H:i:s');
            $newCategory->allowed_groups = $allowedGroupsJson;
            $newCategory->active = true;
            $newCategory->id_shop = $shopId;
            $newCategory->save();
        }

        // Insert tags into Everblog.
        foreach ($tags as $tag) {
            $newTag = new EverPsBlogTag();
            $newTag->title = [$langId => $tag['tag']];
            $newTag->meta_title = [$langId => $tag['tag']];
            $newTag->meta_description = [$langId => ''];
            $newTag->link_rewrite = [$langId => Tools::link_rewrite($tag['tag'])];
            $newTag->allowed_groups = $allowedGroupsJson;
            $newTag->date_add = date('Y-m-d H:i:s');
            $newTag->date_upd = date('Y-m-d H:i:s');
            $newTag->indexable = true;
            $newTag->follow = true;
            $newTag->active = true;
            $newTag->id_shop = $shopId;
            $newTag->save();
        }

        // Insert posts into Everblog.
        foreach ($posts as $post) {
            $post['post_content'] = str_replace('\r\n', '<p></p>', $post['post_content']);
            // Nettoyage et remplacement des images dans le contenu
            $cleanedContent = $this->replaceAndDownloadImages($post['post_content']);
            $cleanedContent = Tools::purifyHTML($cleanedContent);
            $cleanedExcerpt = $this->replaceAndDownloadImages($post['short_content']);
            // Create the post.
            $newPost = new EverPsBlogPost();
            $newPost->title = [$langId => $post['title']];
            $newPost->meta_title = [$langId => $post['meta_keywords']];
            $newPost->meta_description = [$langId => $post['meta_description']];
            $newPost->link_rewrite = [$langId => Tools::link_rewrite($post['title'])];
            $newPost->date_add = $post['created_time'] ? $post['created_time'] : date('Y-m-d H:i:s');
            $newPost->date_upd = $post['update_time'] ? $post['update_time'] : date('Y-m-d H:i:s');
            $newPost->active = ($post['status'] == 1) ? true : false;
            $newPost->indexable = ($post['status'] == 1) ? true : false;
            $newPost->follow = ($post['status'] == 1) ? true : false;
            $newPost->content = [$langId => $cleanedContent];
            $newPost->post_status = 'published';
            $newPost->id_shop = $shopId;
            $newPost->id_default_category = $defaultCategoryId;
            $newPost->allowed_groups = $allowedGroupsJson; // Add allowed groups.
            $newPost->save();
            // dump($post['post_content']);
            // die();
            // Fetch the saved post ID.
            $postId = $newPost->id;
            // dump(pSQL($post['post_content'], true));
            // die();
            // dump($cleanedContent);
            // die();
            // Update the content directly in the database.
            // Db::getInstance()->execute('
            //     UPDATE ' . _DB_PREFIX_ . 'ever_blog_post_lang
            //     SET content = "' . pSQL($post['post_content'], true) . '", excerpt = "' . pSQL($cleanedExcerpt) . '"
            //     WHERE id_ever_post = ' . (int)$postId
            // );
            $this->getBlogTaxonomyService()->insert($defaultCategoryId, $postId, 'category');
            $newPost->save();
            // Insert the default "Uncategorized" category for each post.

            // Insert the other categories attached to the post.
            $postCategories = Db::getInstance()->executeS('SELECT * FROM aw_blog_post_cat WHERE post_id = ' . (int)$post['post_id']);
            foreach ($postCategories as $postCategory) {
                $this->getBlogTaxonomyService()->insert($postCategory['cat_id'], $postId, 'category');
            }

            // Insert the tags attached to the post.
            $postTags = explode(',', $post['tags']);
            foreach ($postTags as $tag) {
                $existingTag = Db::getInstance()->getRow('SELECT id_ever_tag FROM ' . _DB_PREFIX_ . 'ever_blog_tag_lang WHERE title = "' . pSQL($tag) . '"');
                if ($existingTag) {
                    $this->getBlogTaxonomyService()->insert($existingTag['id_ever_tag'], $postId, 'tag');
                }
            }
            $newPost->save();
        }
    }

    public function replaceAndDownloadImages($content)
    {
        // 1. Convertir les legendes WordPress en <figure>
        $content = preg_replace_callback(
            '/\[caption[^\]]*\](<img[^>]+>)(.*?)\[\/caption\]/si',
            function ($matches) {
                $imgTag = $matches[1];
                $caption = trim(strip_tags($matches[2]));
                // Add img-fluid and figure-img classes to the image.
                $imgTag = preg_replace(
                    '/<img(.*?)class=["\']?([^"\']*)["\']?/i',
                    '<img$1class="$2 img-fluid figure-img"',
                    $imgTag
                );
                return '<figure class="figure text-center">' . $imgTag . '<figcaption class="figure-caption">' . $caption . '</figcaption></figure>';
            },
            $content
        );

        // 2. Ajouter les classes Bootstrap aux <img> classiques
        $content = preg_replace_callback(
            '/<img([^>]+)>/i',
            function ($matches) {
                $tag = $matches[0];
                if (strpos($tag, 'class=') !== false) {
                    $tag = preg_replace(
                        '/class=["\']([^"\']*)["\']/i',
                        'class="$1 img-fluid"',
                        $tag
                    );
                } else {
                    $tag = str_replace('<img', '<img class="img-fluid"', $tag);
                }
                return $tag;
            },
            $content
        );

        // 3. Remplacer les classes d’alignement WordPress
        $replace = [
            'aligncenter' => 'mx-auto d-block',
            'alignright'  => 'float-end',
            'alignleft'   => 'float-start',
        ];
        $content = str_replace(array_keys($replace), array_values($replace), $content);

        // 4. Remplacer les URLs {{media url="..."}}
        $content = preg_replace_callback('/\{\{media url="wysiwyg\/([^"]+)"\}\}/', function ($matches) {
            $url = 'https://www.comptoir-de-vie.com/media/wysiwyg/' . $matches[1];
            $localPath = $this->downloadImage($url);
            return $localPath ?: $url;
        }, $content);

        // 5. Remplacer les src des balises <img>
        $content = preg_replace_callback('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function ($matches) {
            $src = $matches[1];
            $newSrc = $src;

            if (strpos($src, 'http://www.comptoir-de-vie.com/') !== false) {
                $localPath = $this->downloadImage($src);
                if ($localPath) {
                    $newSrc = $localPath;
                }
            } elseif (strpos($src, '/comptoir/') !== false) {
                $localPath = $this->downloadImage('http://www.comptoir-de-vie.com/' . ltrim($src, '/'));
                if ($localPath) {
                    $newSrc = $localPath;
                }
            }

            return str_replace($src, $newSrc, $matches[0]);
        }, $content);

        return $content;
    }


    public function downloadImage($url)
    {
        $url = preg_replace('#^(https?:\/\/)(https?:\/\/)#', '$1', $url);
        $imageContent = @file_get_contents($url);
        if ($imageContent !== false) {
            $imageName = basename(parse_url($url, PHP_URL_PATH));
            $imageName = preg_replace('/[^\w.-]/', '', $imageName);
            $targetDir = _PS_IMG_DIR_ . 'cms/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $localPath = $targetDir . $imageName;

            file_put_contents($localPath, $imageContent);

            return _PS_IMG_ . 'cms/' . $imageName;
        }

        return false;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminEverPsBlog'));

        return '';
    }

    public function hookDisplayBackOfficeHeader()
    {
        return;
    }

    public function hookDisplayAdminAfterHeader()
    {
        if ($this->checkLatestEverModuleVersion()) {
            $upgradeUrl = 'https://www.team-ever.com/prestashop-module-de-blog-gratuit/';

            return sprintf(
                '<div class="panel everheader"><div class="panel-body"><div class="col-12 col-lg-12"><p class="alert alert-warning">%s <a href="%s" target="_blank" rel="noopener noreferrer">%s</a> %s</p></div></div></div>',
                Tools::safeOutput($this->transAdmin('An upgrade is available for Ever Blog. Please check')),
                Tools::safeOutput($upgradeUrl),
                Tools::safeOutput($upgradeUrl),
                Tools::safeOutput($this->transAdmin('to get latest version of this module'))
            );
        }
    }

    public function hookDisplayHeader()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');
        $dynamic_header_css = '';
        if ($module_name == 'everpsblog') {
            $this->context->controller->addCSS(
                $this->module_folder . '/views/css/everpsblog-all.css',
                'all'
            );
            $this->context->controller->addCSS(
                $this->module_folder . '/views/css/everpsblog.css',
                'all'
            );
            $this->context->controller->addCSS(
                $this->module_folder . 'everpsblog/views/css/everpsblog.css',
                'all'
            );
            $this->context->controller->addJs(
                $this->_path . 'views/js/everpsblog.js'
            );
            $header_bg_color = Configuration::get('EVERBLOG_HEADER_BG_COLOR');
            if (!$header_bg_color || !Validate::isColor($header_bg_color)) {
                $header_bg_color = '#0a0f54';
            }
            $header_title_color = Configuration::get('EVERBLOG_HEADER_TITLE_COLOR');
            if (!$header_title_color || !Validate::isColor($header_title_color)) {
                $header_title_color = '#ffffff';
            }
            $this->context->smarty->assign('everpsblog_header_bg_color', $header_bg_color);
            $this->context->smarty->assign('everpsblog_header_title_color', $header_title_color);
            $dynamic_header_css = '<style>'
                . '#module-everpsblog-blog .everpsblog-blog-header,'
                . '#module-everpsblog-category .everpsblog-blog-header,'
                . '#module-everpsblog-tag .everpsblog-blog-header,'
                . '#module-everpsblog-author .everpsblog-blog-header,'
                . '#module-everpsblog-search .everpsblog-blog-header'
                . '{background-color:' . $header_bg_color . ' !important;}'
                . '#module-everpsblog-blog .everpsblog-blog-header__title,'
                . '#module-everpsblog-category .everpsblog-blog-header__title,'
                . '#module-everpsblog-tag .everpsblog-blog-header__title,'
                . '#module-everpsblog-author .everpsblog-blog-header__title,'
                . '#module-everpsblog-search .everpsblog-blog-header__title,'
                . '#module-everpsblog-post .everpsblog-post-title'
                . '{color:' . $header_title_color . ' !important;}'
                . '</style>';
        }
        $this->context->controller->addCSS(
            $this->module_folder . '/views/css/everpsblog-columns.css',
            'all'
        );
        $css_file = Configuration::get('EVERBLOG_CSS_FILE');
        if ($css_file && $css_file != 'default') {
            $this->context->controller->addCSS(
                $this->module_folder . '/views/css/'.$css_file.'.css',
                'all'
            );
        }
        if (file_exists($this->module_folder . '/views/css/custom.css')) {
            $this->context->controller->addCSS(
                $this->module_folder . '/views/css/custom.css',
                'all'
            );
        }
        return $dynamic_header_css;
    }

    public function hookDisplayLeftColumn($params)
    {
        $controller = Tools::getValue('controller');
        $module = Tools::getValue('module');
        $ps_products = [];
        if ($module == $this->name
            && $controller == 'post'
            && Configuration::get('EVERBLOG_PRODUCT_COLUMNS')
        ) {
            $id_post = (int) Tools::getValue('id_ever_post');
            if ($id_post) {
                $post_products = $this->getBlogTaxonomyService()->getPostProductsTaxonomies($id_post);
                if ($post_products) {
                    $assembler = new ProductAssembler($this->context);
                    $presenterFactory = new ProductPresenterFactory($this->context);
                    $presentationSettings = $presenterFactory->getPresentationSettings();
                    $presenter = new ProductListingPresenter(
                        new ImageRetriever($this->context->link),
                        $this->context->link,
                        new PriceFormatter(),
                        new ProductColorsRetriever(),
                        $this->context->getTranslator()
                    );
                    foreach ($post_products as $productId) {
                        $product = new Product(
                            (int) $productId,
                            true,
                            (int) $this->context->language->id,
                            (int) $this->context->shop->id
                        );
                        if (Product::checkAccessStatic((int) $product->id, false)) {
                            $cover = Product::getCover((int) $product->id);
                            $product->cover = (int) $cover['id_image'];
                            $ps_products[] = $presenter->present(
                                $presentationSettings,
                                $assembler->assembleProduct(['id_product' => $product->id]),
                                $this->context->language
                            );
                        }
                    }
                }
            }
        }
        if ((int) Configuration::get('EVERPSBLOG_HOME_NBR')) {
            $post_number = (int) Configuration::get('EVERPSBLOG_HOME_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            [],
            true
        );
        $tags = $this->getFrontLocalizedTags(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $categories = $this->getFrontLocalizedCategories(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $latest_posts = [];
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $showArchives = Configuration::get(
            'EVERBLOG_ARCHIVE_COLUMNS'
        );
        $showCategories = Configuration::get(
            'EVERBLOG_CATEG_COLUMNS'
        );
        $showTags = Configuration::get(
            'EVERBLOG_TAG_COLUMNS'
        );
        $siteUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->context->smarty->assign([
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
            'everpsblog' => $latest_posts,
            'showArchives' => $showArchives,
            'showCategories' => $showCategories,
            'showTags' => $showTags,
            'blogUrl' => $blogUrl,
            'tags' => $tags,
            'categories' => $categories,
            'animate' => $animate,
            'show_featured_post' => true,
            'blogImg_dir' => $siteUrl . '/modules/everpsblog/views/img/',
            'ps_products' => $ps_products,
        ]);
        return $this->display(__FILE__, 'views/templates/hook/columns.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHome2()
    {
        return $this->hookDisplayHome();
    }

    public function hookDisplayHome4()
    {
        return $this->hookDisplayHome();
    }

    public function hookDisplayContainerBottom2()
    {
        return $this->hookDisplayHome();
    }

    public function hookDisplayHome()
    {
        $idLang = $this->context->language->id;
        $idShop = $this->context->shop->id;
        $post_number = (int) Configuration::get('EVERPSBLOG_HOME_NBR') > 0
            ? (int) Configuration::get('EVERPSBLOG_HOME_NBR')
            : 4;
        $cacheId = $this->name . '-hookDisplayBanner-' . $idLang . '-' . $idShop . '-' . $post_number;
        if (!$this->isCached('home.tpl', $cacheId)) {
            $blogUrl = Context::getContext()->link->getModuleLink(
                $this->name,
                'blog',
                [],
                true
            );
            $starredPosts = $this->getStarredPostsForHome(
                (int) $this->context->language->id,
                (int) $this->context->shop->id,
                (int) $post_number
            );
            if (!$starredPosts || !count($starredPosts)) {
                return;
            }
            foreach ($starredPosts as &$post) {
                $featuredThumb = $this->getBlogImageService()->getBlogThumbUrl(
                    (int) (is_array($post) ? $post['id_ever_post'] : $post->id_ever_post),
                    (int) $this->context->shop->id,
                    'post'
                );
                if (is_array($post)) {
                    $post['featured_thumb'] = $featuredThumb;
                } else {
                    $post->featured_thumb = $featuredThumb;
                }
            }
            $evercategories = $this->getFrontLocalizedCategories(
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            $siteUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
            $carouselId = 'everpsblog-home-slider-' . str_replace('.', '-', uniqid('', true));
            $this->context->smarty->assign([
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blogUrl' => $blogUrl,
                'everpsblog' => $starredPosts,
                'evercategory' => $evercategories,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => $siteUrl . '/modules/everpsblog/views/img/',
                'animated' => $animate,
                'show_featured_post' => true,
                'carousel_id' => $carouselId,
            ]);
        }
        return $this->display(__FILE__, 'views/templates/hook/home.tpl', $cacheId);
    }

    public function hookDisplayCustomerAccount()
    {
        if ((bool) Configuration::get('EVERBLOG_ALLOW_COMMENTS') === true) {
            return $this->display(__FILE__, 'views/templates/hook/my-account.tpl');
        }
    }

    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookDisplayCustomerAccount();
    }

    public function hookDisplayFooterProduct()
    {
        if ((bool) Configuration::get('EVERBLOG_RELATED_POST') === false) {
            return;
        }
        if ((int) Configuration::get('EVERPSBLOG_PRODUCT_NBR')) {
            $post_number = (int) Configuration::get('EVERPSBLOG_PRODUCT_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            [],
            true
        );
        $posts = EverPsBlogPost::getPostsByProduct(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            (int) Tools::getValue('id_product'),
            0,
            (int) $post_number
        );
        if (!$posts
            || !count($posts)
        ) {
            return;
        }
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $everpsblog = $posts;
        $siteUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->context->smarty->assign([
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
            'blogUrl' => $blogUrl,
            'everpsblog' => $everpsblog,
            'evercategory' => $evercategories,
            'default_lang' => (int) $this->context->language->id,
            'id_lang' => (int) $this->context->language->id,
            'blogImg_dir' => $siteUrl.'/modules/everpsblog/views/img/',
            'animated' => $animate,
            'show_featured_post' => (bool) Configuration::get('EVERBLOG_SHOW_FEAT_POST'),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/product.tpl');
    }

    public function hookDisplayFooter()
    {
        return $this->hookDisplayBeforeBodyClosingTag();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');
        if ($module_name == 'everpsblog' && $controller_name == 'post') {
            return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        return;
    }

    private function getPostRepository()
    {
        if (!isset($this->context->controller) || !method_exists($this->context->controller, 'getContainer')) {
            return null;
        }

        return $this->context->controller
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(\PrestaShop\Module\Everpsblog\Entity\Post::class);
    }

    public function sendPendingNotification($id_shop)
    {
        return (bool) $this->getBlogScheduledTaskRunner()->sendPendingNotification((int) $id_shop);
    }

    public function emptyTrash($id_shop)
    {
        return (bool) $this->getBlogScheduledTaskRunner()->emptyTrash((int) $id_shop);
    }

    public function publishPlannedPosts($id_shop)
    {
        return (bool) $this->getBlogScheduledTaskRunner()->publishPlannedPosts((int) $id_shop);
    }

    public function hookActionObjectShopAddAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $shop = $params['object'];
        $root_category = new EverPsBlogCategory();
        $root_category->is_root_category = 1;
        $root_category->active = 1;
        $root_category->id_shop = (int) $shop->id;
        $root_category->id_shop_list = [(int) $shop->id];
        foreach (Language::getLanguages(false) as $language) {
            $root_category->title[$language['id_lang']] = 'Root';
            $root_category->content[$language['id_lang']] = 'Root';
            $root_category->link_rewrite[$language['id_lang']] = 'root';
        }
        $root_category->save();
    }

    public function hookActionObjectEverPsBlogPostAddAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        return $this->hookActionObjectEverPsBlogPostUpdateAfter($params);
    }

    public function hookActionObjectEverPsBlogPostUpdateAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $post_categories = $this->getBlogCleanerService()->convertToArray(
            json_decode($params['object']->post_categories, true)
        );
        $post_tags = $this->getBlogCleanerService()->convertToArray(
            json_decode($params['object']->post_tags, true)
        );
        $post_products = $this->getBlogCleanerService()->convertToArray(
            json_decode($params['object']->post_products, true)
        );
        // First drop post taxonomies
        $this->getBlogTaxonomyService()->dropPostTaxonomies((int) $params['object']->id);
        // Then insert taxonomies
        foreach ($post_categories as $id_post_category) {
            $this->getBlogTaxonomyService()->insert(
                (int) $id_post_category,
                (int) $params['object']->id,
                'category'
            );
        }
        foreach ($post_tags as $id_post_tag) {
            $this->getBlogTaxonomyService()->insert(
                (int) $id_post_tag,
                (int) $params['object']->id,
                'tag'
            );
        }
        foreach ($post_products as $id_post_product) {
            $this->getBlogTaxonomyService()->insert(
                (int) $id_post_product,
                (int) $params['object']->id,
                'product'
            );
        }
        // At least check root taxonomy
        $this->getBlogTaxonomyService()->checkDefaultPostCategory(
            $params['object']->id
        );
        $this->getBlogFrontCacheInvalidator()->invalidatePostMutation(
            (int) $params['object']->id,
            [],
            $this->buildLegacyPostCacheSnapshot($params['object'])
        );
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        . 'tmp/ever_blog_post_mini_'
        . (int) $params['object']->id
        . '_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogCategoryUpdateAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $this->getBlogFrontCacheInvalidator()->invalidateCategoryMutation(
            (int) $params['object']->id,
            [],
            ['parent_id' => (int) ($params['object']->id_parent_category ?? 0)]
        );
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        . 'tmp/ever_blog_category_mini_'
        . (int) $params['object']->id
        . '_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogTagUpdateAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $this->getBlogFrontCacheInvalidator()->invalidateTagMutation((int) $params['object']->id);
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        . 'tmp/ever_blog_tag_mini_'
        . (int) $params['object']->id
        . '_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogAuthorUpdateAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $this->getBlogFrontCacheInvalidator()->invalidateAuthorMutation((int) $params['object']->id);
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        . 'tmp/ever_blog_author_mini_'
        . (int) $params['object']->id
        . '_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectShopDeleteAfter($params)
    {
        $controllerTypes = ['admin', 'moduleadmin'];
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $shop = $params['object'];
        Db::getInstance()->delete(
            'ever_blog_category',
            'id_shop = ' . (int) $shop->id
        );
    }

    public function hookActionObjectEverPsBlogPostDeleteAfter($params)
    {
        $this->getBlogFrontCacheInvalidator()->invalidatePostMutation(
            (int) $params['object']->id,
            $this->buildLegacyPostCacheSnapshot($params['object']),
            []
        );
        $old_img = _PS_MODULE_DIR_
        . 'everpsblog/views/img/posts/post_image_'
        . (int) $params['object']->id
        . '.jpg';
        $old_ps_img = _PS_IMG_DIR_
        . 'posts/'
        . (int) $params['object']->id
        . '.jpg';
        if (file_exists($old_ps_img)) {
            unlink($old_ps_img);
        }
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $image = $this->getBlogImageService()->getBlogImage(
            (int) $params['object']->id,
            (int) Context::getContext()->shop->id,
            'post'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        $this->getBlogTaxonomyService()->dropPostTaxonomies((int) $params['object']->id);
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogCategoryDeleteAfter($params)
    {
        $this->getBlogFrontCacheInvalidator()->invalidateCategoryMutation(
            (int) $params['object']->id,
            ['parent_id' => (int) ($params['object']->id_parent_category ?? 0)],
            []
        );
        $shopId = (int) Context::getContext()->shop->id;
        if ((int) $params['object']->id == $this->getBlogInstallService()->getUnclassedCategoryId($shopId)) {
            $rootCategoryId = $this->getBlogInstallService()->getRootCategoryId($shopId);
            if ($rootCategoryId > 0) {
                $this->getBlogInstallService()->recreateUnclassedCategory(
                    $this,
                    $shopId,
                    $rootCategoryId
                );
            }
        }
        $old_img = _PS_MODULE_DIR_
        . 'everpsblog/views/img/categories/category_image_'
        . (int) $params['object']->id
        . '.jpg';
        $old_ps_img = _PS_IMG_DIR_
        . 'categories/'
        . (int) $params['object']->id
        . '.jpg';
        if (file_exists($old_ps_img)) {
            unlink($old_ps_img);
        }
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $image = $this->getBlogImageService()->getBlogImage(
            (int) $params['object']->id,
            (int) Context::getContext()->shop->id,
            'category'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        $this->getBlogTaxonomyService()->dropCategoryTaxonomy(
            (int) $params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogCategoryDeleteBefore($params)
    {
        $categoryId = isset($params['object']->id) ? (int) $params['object']->id : 0;
        $shopId = (int) Context::getContext()->shop->id;
        if ($this->getBlogInstallService()->isProtectedCategoryId($categoryId, $shopId)) {
            throw new PrestaShopException($this->transAdmin('Root and Unclassed categories cannot be deleted.'));
        }
    }

    public function hookActionObjectEverPsBlogTagDeleteAfter($params)
    {
        $this->getBlogFrontCacheInvalidator()->invalidateTagMutation((int) $params['object']->id);
        $old_img = $this->module_folder . '/views/img/tags/tag_image_' . (int) $params['object']->id . '.jpg';
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $this->getBlogTaxonomyService()->dropTagTaxonomy(
            (int) $params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectAuthorDeleteAfter($params)
    {
        $this->getBlogFrontCacheInvalidator()->invalidateAuthorMutation((int) $params['object']->id);
        $old_img = _PS_MODULE_DIR_
        . 'everpsblog/views/img/authors/author_image_'
        . (int) $params['object']->id
        . '.jpg';
        $old_ps_img = _PS_IMG_DIR_
        . 'authors/'
        . (int) $params['object']->id
        . '.jpg';
        if (file_exists($old_ps_img)) {
            unlink($old_ps_img);
        }
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $image = $this->getBlogImageService()->getBlogImage(
            (int) $params['object']->id,
            (int) Context::getContext()->shop->id,
            'author'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        EverPsBlogPost::dropBlogAuthorPosts(
            (int) $params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        $this->getBlogTaxonomyService()->dropProductTaxonomy(
            (int) $params['object']->id
        );
    }

    public function generateBlogSitemap($id_shop = null, $cron = false)
    {
        if (!$id_shop) {
            $id_shop = (int) $this->context->shop->id;
        }
        $result = (bool) $this->getBlogSitemapService()->refreshForShop((int) $id_shop);
        $this->postSuccess[] = $this->transAdmin('All XML sitemaps have been generated');
        if ((bool) $cron === true) {
            return $result;
        }
    }

    public function getSitemapIndexes()
    {
        return $this->getBlogSitemapService()->getSitemapIndexes((int) $this->context->shop->id);
    }

    public function hookActionAdminMetaAfterWriteRobotsFile($params)
    {
        // Panda theme uses random int on css file parameter
        $allowSitemap = 'User-agent: *' . "\r\n";
        $allowSitemap .= 'Disallow: /modules/stthemeeditor/views/css' . "\r\n";
        $allowSitemap .= "\n";
        foreach ($this->getBlogSitemapService()->getRobotsDirectives() as $directive) {
            $allowSitemap .= $directive . "\r\n";
        }
        fwrite($params['write_fd'], "#Rules from everpsblog\n");
        fwrite($params['write_fd'], $allowSitemap);
    }

    /**
     * Register module blog and PS hooks
    */
    private function checkHooks()
    {
        try {
            $this->registerHook('displayHeader');
            $this->registerHook('actionDispatcherBefore');
            $this->registerHook('beforeRenderingEverpsblogPostSlider');
            $this->registerHook('actionAdminControllerSetMedia');
            $this->registerHook('displayHome');
            $this->registerHook('displayFooterProduct');
            $this->registerHook('displayFooter');
            $this->registerHook('displayCustomerAccount');
            $this->registerHook('moduleRoutes');
            $this->registerHook('displayBackOfficeHeader');
            $this->registerHook('actionObjectProductDeleteAfter');
            $this->registerHook('displayAdminAfterHeader');
            $this->registerHook('actionAdminMetaAfterWriteRobotsFile');
            $this->registerHook('actionRegisterBlock');
            $this->registerHook('actionObjectLanguageAddAfter');
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' : ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Register module blog and PS hooks
    */
    private function checkObligatoryHooks()
    {
        try {
            $this->registerHook('moduleRoutes');
            $this->registerHook('actionDispatcherBefore');
            $this->registerHook('displayBackOfficeHeader');
            $this->registerHook('displayAdminAfterHeader');
            $this->registerHook('actionAdminMetaAfterWriteRobotsFile');
            $this->registerHook('actionObjectEverPsBlogPostAddAfter');
            $this->registerHook('actionObjectEverPsBlogPostUpdateAfter');
            $this->registerHook('actionObjectEverPsBlogCategoryUpdateAfter');
            $this->registerHook('actionObjectEverPsBlogTagUpdateAfter');
            $this->registerHook('actionObjectEverPsBlogAuthorUpdateAfter');
            $this->registerHook('actionObjectShopDeleteAfter');
            $this->registerHook('actionObjectEverPsBlogPostDeleteAfter');
            $this->registerHook('actionObjectEverPsBlogCategoryDeleteBefore');
            $this->registerHook('actionObjectEverPsBlogCategoryDeleteAfter');
            $this->registerHook('actionObjectEverPsBlogTagDeleteAfter');
            $this->registerHook('actionObjectAuthorDeleteAfter');
            $this->registerHook('actionObjectProductDeleteAfter');
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' : ' . $e->getMessage());
        }
        return true;
    }

    private function importWordPressFile($file)
    {
        $allow_iframes = Configuration::get('PS_ALLOW_HTML_IFRAME');
        if ((bool) $allow_iframes === false) {
            Configuration::updateValue('PS_ALLOW_HTML_IFRAME', true);
        }
        $result = true;
        $xml_str = Tools::file_get_contents($file['tmp_name']);
        // Force UTF-8 encoding for proper special character handling
        $encoding = mb_detect_encoding(
            $xml_str,
            ['UTF-8', 'ISO-8859-1', 'WINDOWS-1252'],
            true
        );
        if ($encoding && $encoding !== 'UTF-8') {
            $xml_str = iconv($encoding, 'UTF-8', $xml_str);
        }
        $xml_str = str_replace(
            'content:encoded',
            'content',
            $xml_str
        );
        $xml_str = str_replace(
            'dc:creator',
            'creator',
            $xml_str
        );
        $xml_str = str_replace(
            'wp:post_date',
            'date_add',
            $xml_str
        );
        $xml_str = str_replace(
            'wp:post_name',
            'link_rewrite',
            $xml_str
        );
        $obj = new SimpleXMLElement($xml_str, LIBXML_NOCDATA);
        $redirects = [
            'posts' => [],
            'categories' => [],
            'tags' => [],
            'authors' => [],
        ];
        $link = new Link();
        $default_lang = (int) Context::getContext()->language->id;
        foreach ($obj->channel->item as $el) {
            // Post categories and post tags
            $post_categories = [];
            $post_tags = [];
            $parent_category = 1;
            foreach ($el->category as $wp_taxonomy) {
                if ($wp_taxonomy->attributes()['domain'] == 'category'
                    && (bool)Configuration::get('EVERBLOG_IMPORT_CATS') === true
                ) {
                    $category = EverPsBlogCategory::getCategoryByLinkRewrite(
                        (string) $wp_taxonomy['nicename']
                    );
                    if (!Validate::isLoadedObject($category)) {
                        $category = $this->getLegacyImportAdapter()->getOrCreateCategoryByLinkRewrite(
                            (string) $wp_taxonomy['nicename']
                        );
                $id_lang = $this->getIdLangFromWpData($wp_taxonomy);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $lang) {
                    $category->title[$lang['id_lang']] = (string) $wp_taxonomy;
                    $category->meta_title[$lang['id_lang']] = (string) $wp_taxonomy;
                    $category->link_rewrite[$lang['id_lang']] = (string) $wp_taxonomy['nicename'];
                }
                    $category->id_parent_category = (int) $parent_category;
                    $category->id_shop = (int) Context::getContext()->shop->id;
                    $category->active = true;
                    $category->indexable = true;
                    $category->follow = true;
                    $category->sitemap = true;
                    $category->active = (bool)Configuration::get('EVERBLOG_ENABLE_CATS');
                    $result &= $category->save();
                    $post_categories[] = $category->id;
                    } else {
                        $post_categories[] = $category->id;
                    }
                    $old_path = '/category/' . (string) $wp_taxonomy['nicename'];
                    if (!isset($redirects['categories'][$old_path])) {
                        $redirects['categories'][$old_path] = $link->getModuleLink(
                            'everpsblog',
                            'category',
                            [
                                'id_ever_category' => $category->id,
                                'link_rewrite' => $category->link_rewrite[$default_lang],
                            ]
                        );
                    }
                } elseif ($wp_taxonomy->attributes()['domain'] == 'post_tag'
                    && (bool)Configuration::get('EVERBLOG_IMPORT_TAGS') === true
                ) {
                    $tag = EverPsBlogTag::getTagByLinkRewrite(
                        (string) $wp_taxonomy['nicename']
                    );
                    if (!Validate::isLoadedObject($tag)) {
                        $tag = $this->getLegacyImportAdapter()->getOrCreateTagByLinkRewrite(
                            (string) $wp_taxonomy['nicename']
                        );
                        $id_lang = $this->getIdLangFromWpData($wp_taxonomy);
                        $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                        foreach ($langs as $lang) {
                            $tag->title[$lang['id_lang']] = (string) $wp_taxonomy;
                            $tag->meta_title[$lang['id_lang']] = (string) $wp_taxonomy;
                            $tag->link_rewrite[$lang['id_lang']] = (string) $wp_taxonomy['nicename'];
                        }
                        $tag->id_shop = (int) Context::getContext()->shop->id;
                        $tag->active = true;
                        $tag->indexable = true;
                        $tag->follow = true;
                        $tag->sitemap = true;
                        $tag->active = (bool)Configuration::get('EVERBLOG_ENABLE_TAGS');
                        $result &= $tag->save();
                        $post_tags[] = $tag->id;
                    } else {
                        $post_tags[] = $tag->id;
                    }
                    $old_path = '/tag/' . (string) $wp_taxonomy['nicename'];
                    if (!isset($redirects['tags'][$old_path])) {
                        $redirects['tags'][$old_path] = $link->getModuleLink(
                            'everpsblog',
                            'tag',
                            [
                                'id_ever_tag' => $tag->id,
                                'link_rewrite' => $tag->link_rewrite[$default_lang],
                            ]
                        );
                    }
                }
            }
            // Post author
            $author = EverPsBlogAuthor::getAuthorByNickhandle(
                $el->creator
            );
            if (!Validate::isLoadedObject($author)
                && (bool) Configuration::get('EVERBLOG_IMPORT_AUTHORS') === true
            ) {
                $author = $this->getLegacyImportAdapter()->getOrCreateAuthorByNickhandle(
                    (string) $el->creator
                );
                $id_lang = $this->getIdLangFromWpData($el);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $lang) {
                    $author->meta_title[$lang['id_lang']] = (string) $el->creator;
                    $author->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        (string) $el->creator
                    );
                }
                $author->id_shop = (int) Context::getContext()->shop->id;
                $author->active = true;
                $author->indexable = true;
                $author->follow = true;
                $author->sitemap = true;
                $author->active = (bool) Configuration::get('EVERBLOG_ENABLE_AUTHORS');
                $result &= $author->save();
            }
            $author_slug = Tools::str2url((string) $el->creator);
            $old_path = '/author/' . $author_slug;
            if (!isset($redirects['authors'][$old_path])) {
                $redirects['authors'][$old_path] = $link->getModuleLink(
                    'everpsblog',
                    'author',
                    [
                        'id_ever_author' => $author->id,
                        'link_rewrite' => $author->link_rewrite[$default_lang],
                    ]
                );
            }
            // Post
            $parsed_url = parse_url((string) $el->link);
            $host = $parsed_url['host'];
            $post_link_rewrite = Tools::str2url(basename($parsed_url['path']));
            $post = EverPsBlogPost::getPostByLinkRewrite(
                $post_link_rewrite
            );
            if (!Validate::isLoadedObject($post)) {
                // Copy images
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                // Inject a UTF-8 declaration so DOMDocument does not interpret content as Latin1.
                $content = '<?xml encoding="UTF-8">' . $el->content;

                $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                $images = $dom->getElementsByTagName('img');
                foreach ($images as $item) {
                    $src = $item->getAttribute('src');
                    // Let's avoid 404 errors
                    $handle = curl_init($src);
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($handle);
                    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                    if ($httpCode != 200) {
                        curl_close($handle);
                        continue;
                    }
                    curl_close($handle);
                    // Download remote image
                    $local = $this->downloadImage($src);
                    if ($local) {
                        $item->setAttribute('src', $local);
                    }
                    $item->setAttribute(
                        'style',
                        'max-width:100%;'
                    );
                    if (!$item->getAttribute('alt') || empty($item->getAttribute('alt'))) {
                        $item->setAttribute(
                            'alt',
                            Tools::htmlentitiesDecodeUTF8(basename($src))
                        );
                    }
                }
                // Clean anchors, but internal links wont be available
                $anchors = $dom->getElementsByTagName('a');
                foreach ($anchors as $item) {
                    $href = $item->getAttribute('href');
                    $href_array = parse_url($href);
                    if (isset($href_array['host'])) {
                        $host = $href_array['host'];
                        $item->setAttribute(
                            'href',
                            str_replace($host, Tools::getHttpHost(true) . __PS_BASE_URI__, $href)
                        );
                    }
                }
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                $post_content = $dom->saveHTML();
                // Get featured image if provided
                $featured_url = '';
                $namespaces = $el->getNameSpaces(true);
                if (isset($namespaces['wp'])) {
                    $wp = $el->children($namespaces['wp']);
                    if (isset($wp->attachment_url)) {
                        $featured_url = (string) $wp->attachment_url;
                    }
                }
                // Then fix HTML entities and malformed encodings.
                $post_content = html_entity_decode($post_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $post_content = mb_convert_encoding($post_content, 'UTF-8', 'UTF-8');
                $post_content = str_replace('<?xml encoding="UTF-8">', '', $post_content);
                $post_content = preg_replace('/<!--(.|\s)*?-->/', '', $post_content);
                $post_content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $post_content);
                $post_content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $post_content);
                $post_content = $this->removeUnsupportedWpMarkup($post_content);
                $post = $this->getLegacyImportAdapter()->getOrCreatePostByLinkRewrite($post_link_rewrite);
                // Multilingual fields
                $id_lang = $this->getIdLangFromWpData($el);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $lang) {
                    $post->title[$lang['id_lang']] = html_entity_decode((string) $el->title, ENT_QUOTES, 'UTF-8');
                    $post->meta_title[$lang['id_lang']] = html_entity_decode((string) $el->title, ENT_QUOTES, 'UTF-8');
                    $post->meta_description[$lang['id_lang']] = Tools::substr(
                        strip_tags($post_content),
                        0,
                        160
                    );
                    $post->link_rewrite[$lang['id_lang']] = $post_link_rewrite;
                    $post->content[$lang['id_lang']] = $post_content;
                }
                if (!Validate::isCleanHtml($post_content, true)) {
                    continue;
                }
                $post->id_shop = (int) Context::getContext()->shop->id;
                $post->active = true;
                $post->indexable = true;
                $post->follow = true;
                $post->sitemap = true;
                $post->active = true;
                $post->date_add = (string) $el->date_add;
                $post->date_upd = $post->date_add;
                $post->post_status = Configuration::get('EVERBLOG_IMPORT_POST_STATE');
                if (Validate::isLoadedObject($author)) {
                    $post->id_author = $author->id;
                }
                if (!empty($post_categories)) {
                    $post->id_default_category = $post_categories[0];
                    $post->post_categories = json_encode($post_categories);
                }
                if (!empty($post_tags)) {
                    $post->post_tags = json_encode($post_tags);
                }
                $result &= $post->save();
                $post->date_add = (string) $el->date_add;
                $post->date_upd = (string) $el->date_add;
                $post->save();

                if ($featured_url) {
                    $local = $this->downloadImage($featured_url);
                    if ($local) {
                        $image = $this->getBlogImageService()->getBlogImage(
                            (int) $post->id,
                            (int) Context::getContext()->shop->id,
                            'post'
                        );
                        if (!$image) {
                            $image = $this->getLegacyImportAdapter()->getOrCreatePostImage(
                                (int) $post->id,
                                (int) Context::getContext()->shop->id
                            );
                        }
                        $image->id_element = (int) $post->id;
                        $image->image_type = 'post';
                        $image->image_link = ltrim(str_replace(Tools::getHttpHost(true) . __PS_BASE_URI__, '', $local), '/');
                        $image->id_shop = (int) Context::getContext()->shop->id;
                        $result &= $image->save();
                    }
                }
                $old_path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                if (!isset($redirects['posts'][$old_path])) {
                    $redirects['posts'][$old_path] = $link->getModuleLink(
                        'everpsblog',
                        'post',
                        [
                            'id_ever_post' => $post->id,
                            'link_rewrite' => $post_link_rewrite,
                        ]
                    );
                }
            }
        }
        $this->saveRedirects($redirects);
        // Reset iframes
        if ((bool) $allow_iframes === false) {
            Configuration::updateValue('PS_ALLOW_HTML_IFRAME', false);
        }
        if ((bool) $result === true) {
            $this->generateBlogSitemap();
            $this->postSuccess[] = $this->transAdmin('WordPress posts have been imported');
        } else {
            $this->postErrors[] = $this->transAdmin('An error has occured while importing WordPress file');
        }
    }

    private function importWooCommercePosts($apiUrl, $consumerKey, $consumerSecret)
    {
        $result = true;
        $page = 1;
        $root = EverPsBlogCategory::getRootCategory();
        $redirects = [
            'posts' => [],
            'categories' => [],
            'tags' => [],
            'authors' => [],
        ];
        $link = new Link();
        $default_lang = (int) Context::getContext()->language->id;
        do {
            $endpoint = rtrim($apiUrl, '/') . '/wp-json/wp/v2/posts?per_page=100&page=' . (int) $page;
            $posts = $this->wooRequest($endpoint, $consumerKey, $consumerSecret);
            if (!$posts) {
                break;
            }
            foreach ($posts as $data) {
                $parsed_url = parse_url($data->link);
                $post_link_rewrite = Tools::str2url($data->slug);
                $post = EverPsBlogPost::getPostByLinkRewrite($post_link_rewrite);
                if (Validate::isLoadedObject($post)) {
                    continue;
                }
                $post = $this->getLegacyImportAdapter()->getOrCreatePostByLinkRewrite($post_link_rewrite);
                $id_lang = $this->getIdLangFromWpData($data);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $language) {
                    $content = $this->replaceAndDownloadImages(
                        $this->removeUnsupportedWpMarkup($data->content->rendered)
                    );
                    $content = Tools::purifyHTML($content);
                    $excerpt = $this->replaceAndDownloadImages(
                        $this->removeUnsupportedWpMarkup($data->excerpt->rendered)
                    );
                    $excerpt = Tools::purifyHTML($excerpt);
                    $post->title[$language['id_lang']] = html_entity_decode($data->title->rendered, ENT_QUOTES, 'UTF-8');
                    $post->meta_title[$language['id_lang']] = html_entity_decode($data->title->rendered, ENT_QUOTES, 'UTF-8');
                    $post->meta_description[$language['id_lang']] = Tools::substr(strip_tags($content), 0, 160);
                    $post->link_rewrite[$language['id_lang']] = $post_link_rewrite;
                    $post->content[$language['id_lang']] = $content;
                    $post->excerpt[$language['id_lang']] = Tools::substr(strip_tags($excerpt), 0, 255);
                }
                $post->id_shop = (int) Context::getContext()->shop->id;
                $post->active = true;
                $post->indexable = true;
                $post->follow = true;
                $post->sitemap = true;
                $post->date_add = $data->date;
                $post->date_upd = $data->modified;
                $post->post_status = 'published';

                $post_categories = [];
                if (!empty($data->categories)) {
                    foreach ($data->categories as $cat_id) {
                        $catData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/categories/' . (int) $cat_id);
                        if ($catData && isset($catData->slug)) {
                            $category = EverPsBlogCategory::getCategoryByLinkRewrite($catData->slug);
                            if (!Validate::isLoadedObject($category)) {
                                $category = $this->getLegacyImportAdapter()->getOrCreateCategoryByLinkRewrite(
                                    Tools::str2url($catData->slug)
                                );
                                $id_lang = $this->getIdLangFromWpData($catData);
                                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                                foreach ($langs as $langCat) {
                                    $category->title[$langCat['id_lang']] = html_entity_decode($catData->name, ENT_QUOTES, 'UTF-8');
                                    $category->meta_title[$langCat['id_lang']] = html_entity_decode($catData->name, ENT_QUOTES, 'UTF-8');
                                    $category->link_rewrite[$langCat['id_lang']] = Tools::str2url($catData->slug);
                                }
                                $category->id_parent_category = (int) $root->id;
                                $category->id_shop = (int) Context::getContext()->shop->id;
                                $category->active = (bool) Configuration::get('EVERBLOG_ENABLE_CATS');
                                $category->indexable = true;
                                $category->follow = true;
                                $category->sitemap = true;
                                $category->save();
                            }
                            $post_categories[] = $category->id;
                            if (isset($catData->link)) {
                                $catParsed = parse_url($catData->link);
                                $old_path = isset($catParsed['path']) ? $catParsed['path'] : '';
                                if (!isset($redirects['categories'][$old_path])) {
                                    $redirects['categories'][$old_path] = $link->getModuleLink(
                                        'everpsblog',
                                        'category',
                                        [
                                            'id_ever_category' => $category->id,
                                            'link_rewrite' => $category->link_rewrite[$default_lang],
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }

                if (!empty($data->author)) {
                    $authorData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/users/' . (int) $data->author);
                    if ($authorData && isset($authorData->slug)) {
                        $author = EverPsBlogAuthor::getAuthorByNickhandle($authorData->slug);
                        if (!Validate::isLoadedObject($author)) {
                            $author = $this->getLegacyImportAdapter()->getOrCreateAuthorByNickhandle($authorData->slug);
                            $id_lang = $this->getIdLangFromWpData($authorData);
                            $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                            foreach ($langs as $langAuthor) {
                                $author->meta_title[$langAuthor['id_lang']] = html_entity_decode($authorData->name, ENT_QUOTES, 'UTF-8');
                                $author->link_rewrite[$langAuthor['id_lang']] = Tools::str2url($authorData->slug);
                            }
                            $author->id_shop = (int) Context::getContext()->shop->id;
                            $author->active = (bool) Configuration::get('EVERBLOG_ENABLE_AUTHORS');
                            $author->indexable = true;
                            $author->follow = true;
                            $author->sitemap = true;
                            $author->save();
                        }
                        $post->id_author = $author->id;
                        if (isset($authorData->link)) {
                            $authParsed = parse_url($authorData->link);
                            $old_path = isset($authParsed['path']) ? $authParsed['path'] : '';
                            if (!isset($redirects['authors'][$old_path])) {
                                $redirects['authors'][$old_path] = $link->getModuleLink(
                                    'everpsblog',
                                    'author',
                                    [
                                        'id_ever_author' => $author->id,
                                        'link_rewrite' => $author->link_rewrite[$default_lang],
                                    ]
                                );
                            }
                        }
                    }
                }
                // Prepare tags
                $post_tags = [];
                if (!empty($data->tags)) {
                    foreach ($data->tags as $tag_id) {
                        $tagData = $this->wooRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/tags/' . (int) $tag_id, $consumerKey, $consumerSecret);
                        if ($tagData && isset($tagData->name)) {
                            $tag = EverPsBlogTag::getTagByLinkRewrite(Tools::str2url($tagData->slug));
                            if (!Validate::isLoadedObject($tag)) {
                            $tag = $this->getLegacyImportAdapter()->getOrCreateTagByLinkRewrite(
                                Tools::str2url($tagData->slug)
                            );
                                $id_lang = $this->getIdLangFromWpData($tagData);
                                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                                foreach ($langs as $languageTag) {
                                    $tag->title[$languageTag['id_lang']] = html_entity_decode($tagData->name, ENT_QUOTES, 'UTF-8');
                                    $tag->meta_title[$languageTag['id_lang']] = html_entity_decode($tagData->name, ENT_QUOTES, 'UTF-8');
                                    $tag->link_rewrite[$languageTag['id_lang']] = Tools::str2url($tagData->slug);
                                }
                                $tag->id_shop = (int) Context::getContext()->shop->id;
                                $tag->active = (bool) Configuration::get('EVERBLOG_ENABLE_TAGS');
                                $tag->indexable = true;
                                $tag->follow = true;
                                $tag->sitemap = true;
                                $tag->save();
                            }
                            $post_tags[] = $tag->id;
                            if (isset($tagData->link)) {
                                $tagParsed = parse_url($tagData->link);
                                $old_path = isset($tagParsed['path']) ? $tagParsed['path'] : '';
                                if (!isset($redirects['tags'][$old_path])) {
                                    $redirects['tags'][$old_path] = $link->getModuleLink(
                                        'everpsblog',
                                        'tag',
                                        [
                                            'id_ever_tag' => $tag->id,
                                            'link_rewrite' => $tag->link_rewrite[$default_lang],
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }

                $post_products = [];
                if (!empty($data->meta)) {
                    foreach ([ 'product_ids', '_product_ids', '_related_product_ids' ] as $field) {
                        if (isset($data->meta->$field) && is_array($data->meta->$field)) {
                            foreach ($data->meta->$field as $pid) {
                                $post_products[] = (int) $pid;
                            }
                        }
                    }
                }

                if (!empty($post_tags)) {
                    $post->post_tags = json_encode(array_unique($post_tags));
                }
                if (!empty($post_products)) {
                    $post->post_products = json_encode(array_unique($post_products));
                }

                $result &= $post->save();

                if (!empty($data->featured_media)) {
                    $media = $this->wooRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/media/' . (int) $data->featured_media, $consumerKey, $consumerSecret);
                    if ($media && isset($media->source_url)) {
                        $local = $this->downloadImage($media->source_url);
                        if ($local) {
                            $image = $this->getLegacyImportAdapter()->getOrCreatePostImage(
                                (int) $post->id,
                                (int) Context::getContext()->shop->id
                            );
                            $image->id_element = (int) $post->id;
                            $image->image_type = 'post';
                            $image->image_link = ltrim(str_replace(Tools::getHttpHost(true) . __PS_BASE_URI__, '', $local), '/');
                            $image->id_shop = (int) Context::getContext()->shop->id;
                            $result &= $image->save();
                        }
                    }
                }
                $old_path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                if (!isset($redirects['posts'][$old_path])) {
                    $redirects['posts'][$old_path] = $link->getModuleLink(
                        'everpsblog',
                        'post',
                        [
                            'id_ever_post' => $post->id,
                            'link_rewrite' => $post_link_rewrite,
                        ]
                    );
                }
            }
            $page++;
        } while (!empty($posts));

        $this->saveRedirects($redirects);
        if ($result) {
            $this->generateBlogSitemap();
            $this->postSuccess[] = $this->transAdmin('WooCommerce posts have been imported');
        } else {
            $this->postErrors[] = $this->transAdmin('An error occured while importing WooCommerce posts');
        }
    }

    private function importWordPressPosts($apiUrl)
    {
        $result = true;
        $page = 1;
        $root = EverPsBlogCategory::getRootCategory();
        $redirects = [
            'posts' => [],
            'categories' => [],
            'tags' => [],
            'authors' => [],
        ];
        $link = new Link();
        $default_lang = (int) Context::getContext()->language->id;
        do {
            $endpoint = rtrim($apiUrl, '/') . '/wp-json/wp/v2/posts?per_page=100&page=' . (int) $page;
            $posts = $this->wpRequest($endpoint);
            if (!$posts) {
                break;
            }
            foreach ($posts as $data) {
                $parsed_url = parse_url($data->link);
                $post_link_rewrite = Tools::str2url($data->slug);
                $post = EverPsBlogPost::getPostByLinkRewrite($post_link_rewrite);
                if (!Validate::isLoadedObject($post)) {
                    $post = $this->getLegacyImportAdapter()->getOrCreatePostByLinkRewrite($post_link_rewrite);
                }
                $id_lang = $this->getIdLangFromWpData($data);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $language) {
                    $content = $this->removeUnsupportedWpMarkup($data->content->rendered);
                    $content = $this->replaceAndDownloadImages($content);
                    $content = $this->removeJavascript($content);

                    // Double decode when needed.
                    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    $excerpt = $this->replaceAndDownloadImages(
                        $this->removeUnsupportedWpMarkup($data->excerpt->rendered)
                    );
                    $post->title[$language['id_lang']] = html_entity_decode($data->title->rendered, ENT_QUOTES, 'UTF-8');
                    $post->meta_title[$language['id_lang']] = html_entity_decode($data->title->rendered, ENT_QUOTES, 'UTF-8');
                    $post->meta_description[$language['id_lang']] = Tools::substr(strip_tags($content), 0, 160);
                    $post->link_rewrite[$language['id_lang']] = $post_link_rewrite;
                    $post->content[$language['id_lang']] = $content;
                    $post->excerpt[$language['id_lang']] = Tools::substr(strip_tags($excerpt), 0, 255);
                }
                $post->id_shop = (int) Context::getContext()->shop->id;
                $post->active = true;
                $post->indexable = true;
                $post->follow = true;
                $post->sitemap = true;
                $post->date_add = date('Y-m-d H:i:s', strtotime($data->date));
                $post->date_upd = date('Y-m-d H:i:s', strtotime($data->modified));
                $post->post_status = 'published';

                $post_categories = [];
                if (!empty($data->categories)) {
                    foreach ($data->categories as $cat_id) {
                        $catData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/categories/' . (int) $cat_id);
                        if ($catData && isset($catData->slug)) {
                            $category = EverPsBlogCategory::getCategoryByLinkRewrite($catData->slug);
                            if (!Validate::isLoadedObject($category)) {
                                $category = $this->getLegacyImportAdapter()->getOrCreateCategoryByLinkRewrite(
                                    Tools::str2url($catData->slug)
                                );
                                $id_lang = $this->getIdLangFromWpData($catData);
                                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                                foreach ($langs as $langCat) {
                                    $category->title[$langCat['id_lang']] = html_entity_decode($catData->name, ENT_QUOTES, 'UTF-8');
                                    $category->meta_title[$langCat['id_lang']] = html_entity_decode($catData->name, ENT_QUOTES, 'UTF-8');
                                    $category->link_rewrite[$langCat['id_lang']] = Tools::str2url($catData->slug);
                                }
                                $category->id_parent_category = (int) $root->id;
                                $category->id_shop = (int) Context::getContext()->shop->id;
                                $category->active = (bool) Configuration::get('EVERBLOG_ENABLE_CATS');
                                $category->indexable = true;
                                $category->follow = true;
                                $category->sitemap = true;
                                $category->save();
                            }
                            $post_categories[] = $category->id;
                            if (isset($catData->link)) {
                                $catParsed = parse_url($catData->link);
                                $old_path = isset($catParsed['path']) ? $catParsed['path'] : '';
                                if (!isset($redirects['categories'][$old_path])) {
                                    $redirects['categories'][$old_path] = $link->getModuleLink(
                                        'everpsblog',
                                        'category',
                                        [
                                            'id_ever_category' => $category->id,
                                            'link_rewrite' => $category->link_rewrite[$default_lang],
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }

                if (!empty($data->author)) {
                    $authorData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/users/' . (int) $data->author);
                    if ($authorData && isset($authorData->slug)) {
                        $author = EverPsBlogAuthor::getAuthorByNickhandle($authorData->slug);
                        if (!Validate::isLoadedObject($author)) {
                            $author = $this->getLegacyImportAdapter()->getOrCreateAuthorByNickhandle($authorData->slug);
                            $id_lang = $this->getIdLangFromWpData($authorData);
                            $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                            foreach ($langs as $langAuthor) {
                                $author->meta_title[$langAuthor['id_lang']] = html_entity_decode($authorData->name, ENT_QUOTES, 'UTF-8');
                                $author->link_rewrite[$langAuthor['id_lang']] = Tools::str2url($authorData->slug);
                            }
                            $author->id_shop = (int) Context::getContext()->shop->id;
                            $author->active = (bool) Configuration::get('EVERBLOG_ENABLE_AUTHORS');
                            $author->indexable = true;
                            $author->follow = true;
                            $author->sitemap = true;
                            $author->save();
                        }
                        $post->id_author = $author->id;
                        if (isset($authorData->link)) {
                            $authParsed = parse_url($authorData->link);
                            $old_path = isset($authParsed['path']) ? $authParsed['path'] : '';
                            if (!isset($redirects['authors'][$old_path])) {
                                $redirects['authors'][$old_path] = $link->getModuleLink(
                                    'everpsblog',
                                    'author',
                                    [
                                        'id_ever_author' => $author->id,
                                        'link_rewrite' => $author->link_rewrite[$default_lang],
                                    ]
                                );
                            }
                        }
                    }
                }

                $post_tags = [];
                if (!empty($data->tags)) {
                    foreach ($data->tags as $tag_id) {
                        $tagData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/tags/' . (int) $tag_id);
                        if ($tagData && isset($tagData->name)) {
                            $tag = EverPsBlogTag::getTagByLinkRewrite(Tools::str2url($tagData->slug));
                            if (!Validate::isLoadedObject($tag)) {
                                $tag = $this->getLegacyImportAdapter()->getOrCreateTagByLinkRewrite(
                                    Tools::str2url($tagData->slug)
                                );
                                $id_lang = $this->getIdLangFromWpData($tagData);
                                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                                foreach ($langs as $languageTag) {
                                    $tag->title[$languageTag['id_lang']] = html_entity_decode($tagData->name, ENT_QUOTES, 'UTF-8');
                                    $tag->meta_title[$languageTag['id_lang']] = html_entity_decode($tagData->name, ENT_QUOTES, 'UTF-8');
                                    $tag->link_rewrite[$languageTag['id_lang']] = Tools::str2url($tagData->slug);
                                }
                                $tag->id_shop = (int) Context::getContext()->shop->id;
                                $tag->active = (bool) Configuration::get('EVERBLOG_ENABLE_TAGS');
                                $tag->indexable = true;
                                $tag->follow = true;
                                $tag->sitemap = true;
                                $tag->save();
                            }
                            $post_tags[] = $tag->id;
                            if (isset($tagData->link)) {
                                $tagParsed = parse_url($tagData->link);
                                $old_path = isset($tagParsed['path']) ? $tagParsed['path'] : '';
                                if (!isset($redirects['tags'][$old_path])) {
                                    $redirects['tags'][$old_path] = $link->getModuleLink(
                                        'everpsblog',
                                        'tag',
                                        [
                                            'id_ever_tag' => $tag->id,
                                            'link_rewrite' => $tag->link_rewrite[$default_lang],
                                        ]
                                    );
                                }
                            }
                        }
                    }
                }
                $post_products = [];
                if (isset($data->acf) && isset($data->acf->pages_products_choice) && is_array($data->acf->pages_products_choice)) {
                    foreach ($data->acf->pages_products_choice as $choice) {
                        if (isset($choice->{'pages_products-id'})) {
                            $post_products[] = (int) $choice->{'pages_products-id'};
                        }
                    }
                }
                if (!empty($post_categories)) {
                    $post->id_default_category = $post_categories[0];
                    $post->post_categories = json_encode($post_categories);
                }
                if (!empty($post_tags)) {
                    $post->post_tags = json_encode(array_unique($post_tags));
                }
                if (!empty($post_products)) {
                    $post->post_products = json_encode(array_unique($post_products));
                }
                $result &= $post->save();
                if (!empty($data->featured_media)) {
                    $media = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/media/' . (int) $data->featured_media);
                    if ($media && isset($media->source_url)) {
                        $local = $this->downloadImage($media->source_url);
                        if ($local) {
                            $image = $this->getLegacyImportAdapter()->getOrCreatePostImage(
                                (int) $post->id,
                                (int) Context::getContext()->shop->id
                            );
                            $image->id_element = (int) $post->id;
                            $image->image_type = 'post';
                            $image->image_link = ltrim(str_replace(Tools::getHttpHost(true) . __PS_BASE_URI__, '', $local), '/');
                            $image->id_shop = (int) Context::getContext()->shop->id;
                            $result &= $image->save();
                        }
                    }
                }
                $old_path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                if (!isset($redirects['posts'][$old_path])) {
                    $redirects['posts'][$old_path] = $link->getModuleLink(
                        'everpsblog',
                        'post',
                        [
                            'id_ever_post' => $post->id,
                            'link_rewrite' => $post_link_rewrite,
                        ]
                    );
                }
            }
            $page++;
        } while (!empty($posts));
        $this->saveRedirects($redirects);
        if ($result) {
            $this->generateBlogSitemap();
            $this->postSuccess[] = $this->transAdmin('WordPress posts have been imported');
        } else {
            $this->postErrors[] = $this->transAdmin('An error occured while importing WordPress posts');
        }
    }

    private function wpRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode != 200) {
            return false;
        }
        return json_decode($data);
    }

    private function wooRequest($url, $ck, $cs)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $ck . ':' . $cs);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode != 200) {
            return false;
        }
        return json_decode($data);
    }

    private function saveRedirects($redirects)
    {
        $shopId = isset($this->context->shop) ? (int) $this->context->shop->id : (int) Configuration::get('PS_SHOP_DEFAULT');

        return $this->getBlogRedirectService()->saveRedirects((array) $redirects, $shopId);
    }

    private function getIdLangFromWpData($data)
    {
        $iso = '';
        if (isset($data->lang)) {
            $iso = (string) $data->lang;
        } elseif (isset($data->language)) {
            $iso = (string) $data->language;
        } elseif (isset($data->locale)) {
            $iso = (string) $data->locale;
        }
        if ($iso === '') {
            return false;
        }
        if (strpos($iso, '_') !== false) {
            $iso = substr($iso, 0, 2);
        }
        $id_lang = (int) Language::getIdByIso($iso);
        return $id_lang ? $id_lang : false;
    }

    public function checkLatestEverModuleVersion()
    {
        try {
            $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module='
            . $this->name
            . '&version='
            . $this->version;
            $handle = curl_init($upgrade_link);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
            curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            if ($httpCode != 200) {
                return false;
            }
            $module_version = Tools::file_get_contents(
                $upgrade_link
            );
            if ($module_version && $module_version > $this->version) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' : unable to check update. ' . $e->getMessage());
        }
    }

    public function checkAndFixDatabase()
    {
        return (new \PrestaShop\Module\Everpsblog\Service\DatabaseIntegrityService())->checkAndFix();

        $db = Db::getInstance();
        // Add missing columns to the ever_blog_post table.
        $columnsToAdd = [
            'id_ever_post' => 'int(10) unsigned NOT NULL auto_increment',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'id_author' => 'int(10) unsigned NOT NULL',
            'id_default_category' => 'int(10) unsigned NOT NULL',
            'post_status' => 'varchar(255) NOT NULL',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
            'indexable' => 'int(1) unsigned DEFAULT NULL',
            'follow' => 'int(1) unsigned DEFAULT NULL',
            'sitemap' => 'int(1) unsigned DEFAULT 1',
            'active' => 'int(1) unsigned DEFAULT NULL',
            'allowed_groups' => 'varchar(255) DEFAULT NULL',
            'post_categories' => 'varchar(255) DEFAULT NULL',
            'post_tags' => 'varchar(255) DEFAULT NULL',
            'post_products' => 'varchar(255) DEFAULT NULL',
            'psswd' => 'varchar(255) DEFAULT NULL',
            'starred' => 'int(10) unsigned DEFAULT 0',
            'count' => 'int(10) unsigned DEFAULT 0',
            'groups' => 'text DEFAULT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_post` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_post` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog post table');
                }
            }
        }
        // Add missing columns to the ps_ever_blog_post_lang table.
        $columnsToAdd = [
            'title' => 'varchar(255) NOT NULL',
            'meta_title' => 'varchar(255) DEFAULT NULL',
            'meta_description' => 'varchar(255) DEFAULT NULL',
            'link_rewrite' => 'varchar(255) DEFAULT NULL',
            'content' => 'text NOT NULL',
            'excerpt' => 'varchar(255) DEFAULT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_post_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_post_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog post lang table');
                }
            }
        }
        // Add missing columns to the ever_blog_category table.
        $columnsToAdd = [
            'id_ever_category' => 'int(10) unsigned NOT NULL auto_increment',
            'id_parent_category' => 'int(10) DEFAULT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
            'indexable' => 'int(1) unsigned DEFAULT NULL',
            'follow' => 'int(1) unsigned DEFAULT NULL',
            'sitemap' => 'int(1) unsigned DEFAULT 1',
            'active' => 'int(1) unsigned DEFAULT NULL',
            'category_products' => 'varchar(255) DEFAULT NULL',
            'allowed_groups' => 'varchar(255) DEFAULT NULL',
            'is_root_category' => 'int(1) unsigned DEFAULT NULL',
            'count' => 'int(10) unsigned DEFAULT 0',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_category` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_category` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog category table');
                }
            }
        }
        // Add missing columns to the ps_ever_blog_category_lang table.
        $columnsToAdd = [
            'id_ever_category' => 'int(10) unsigned NOT NULL',
            'title' => 'varchar(255) NOT NULL',
            'meta_title' => 'varchar(255) DEFAULT NULL',
            'meta_description' => 'varchar(255) DEFAULT NULL',
            'link_rewrite' => 'varchar(255) DEFAULT NULL',
            'content' => 'text NOT NULL',
            'bottom_content' => 'text DEFAULT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_category_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_tag` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog category lang table');
                }
            }
        }
        // Add missing columns to the ever_blog_tag table.
        $columnsToAdd = [
            'id_ever_tag' => 'int(10) unsigned NOT NULL auto_increment',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
            'indexable' => 'int(10) unsigned DEFAULT NULL',
            'follow' => 'int(10) unsigned DEFAULT NULL',
            'sitemap' => 'int(1) unsigned DEFAULT 1',
            'active' => 'int(1) unsigned DEFAULT NULL',
            'allowed_groups' => 'varchar(255) DEFAULT NULL',
            'tag_products' => 'varchar(255) DEFAULT NULL',
            'count' => 'int(10) unsigned DEFAULT 0',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_tag` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_tag` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog category lang table');
                }
            }
        }
        // Add missing columns to the ps_ever_blog_tag_lang table.
        $columnsToAdd = [
            'id_ever_tag' => 'int(10) unsigned NOT NULL',
            'title' => 'varchar(255) NOT NULL',
            'meta_title' => 'varchar(255) DEFAULT NULL',
            'meta_description' => 'varchar(255) DEFAULT NULL',
            'link_rewrite' => 'varchar(255) DEFAULT NULL',
            'content' => 'text NOT NULL',
            'bottom_content' => 'text DEFAULT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_tag_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_tag_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog category lang table');
                }
            }
        }
        // Add missing columns to the ever_blog_author table.
        $columnsToAdd = [
            'id_ever_author' => 'int(10) unsigned NOT NULL auto_increment',
            'id_employee' => 'int(10) unsigned NOT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'nickhandle' => 'varchar(255) NOT NULL',
            'twitter' => 'varchar(255) DEFAULT NULL',
            'facebook' => 'varchar(255) DEFAULT NULL',
            'linkedin' => 'varchar(255) DEFAULT NULL',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
            'indexable' => 'int(10) unsigned DEFAULT NULL',
            'follow' => 'int(10) unsigned DEFAULT NULL',
            'sitemap' => 'int(1) unsigned DEFAULT 1',
            'allowed_groups' => 'varchar(255) DEFAULT NULL',
            'author_products' => 'varchar(255) DEFAULT NULL',
            'active' => 'int(10) unsigned DEFAULT NULL',
            'count' => 'int(10) unsigned DEFAULT 0',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_author` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog category lang table');
                }
            }
        }
        // Add missing columns to the ps_ever_blog_author_lang table.
        $columnsToAdd = [
            'id_ever_author' => 'int(10) unsigned NOT NULL',
            'meta_title' => 'varchar(255) DEFAULT NULL',
            'meta_description' => 'varchar(255) DEFAULT NULL',
            'link_rewrite' => 'varchar(255) DEFAULT NULL',
            'content' => 'text NOT NULL',
            'excerpt' => 'varchar(255) DEFAULT NULL',
            'bottom_content' => 'text DEFAULT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_author_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever blog category lang table');
                }
            }
        }
    }

    public static function getConfigInMultipleLangs($key, $idShopGroup = null, $idShop = null)
    {
        if (is_callable(['Configuration', 'getConfigInMultipleLangs'])) {
            return Configuration::getConfigInMultipleLangs($key, $idShopGroup, $idShop);
        }

        $resultsArray = [];
        foreach (Language::getIDs() as $idLang) {
            $resultsArray[$idLang] = Configuration::get($key, $idLang, $idShopGroup, $idShop);
        }

        return $resultsArray;
    }

    private function removeUnsupportedWpMarkup($html)
    {
        return preg_replace('/\[(?!everpsblog)(?:\/)?[\w\-]+(?:\s[^\]]*)?\]/i', '', $html);
    }

    private function removeJavascript($html)
    {
        $html = preg_replace('#<script[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace("/on\w+=(\"[^\"]*\"|'[^']*'|[^\s>]+)/i", '', $html);
        $html = preg_replace('/javascript:/i', '', $html);
        return $html;
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }
}
