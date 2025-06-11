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

require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogAuthor.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogTaxonomy.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogSitemap.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogCleaner.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogSortOrders.php';
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlog extends Module
{
    private $html;
    private $postErrors = [];
    private $postSuccess = [];
    public static $route = [];

    public function __construct()
    {
        $this->name = 'everpsblog';
        $this->tab = 'front_office_features';
        $this->version = '6.0.1';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_folder = _PS_MODULE_DIR_ . 'everpsblog';
        parent::__construct();
        $this->displayName = $this->l('Ever Blog');
        $this->description = $this->l('Simply a blog 😀');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module ?');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->context = Context::getContext();
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
        // Creating root category
        $shops = Shop::getShops();
        foreach ($shops as $shop) {
            $root_category = new EverPsBlogCategory();
            $root_category->is_root_category = 1;
            $root_category->active = 1;
            $root_category->id_shop = (int) $shop['id_shop'];
            foreach (Language::getLanguages(false) as $language) {
                $root_category->title[$language['id_lang']] = 'Root';
                $root_category->content[$language['id_lang']] = 'Root';
                $root_category->link_rewrite[$language['id_lang']] = 'root';
            }
            $root_category->save();
            // Unclassed
            $unclassed_category = new EverPsBlogCategory();
            $unclassed_category->id_parent_category = 0;
            $unclassed_category->active = 1;
            $unclassed_category->id_shop = (int) $shop['id_shop'];
            foreach (Language::getLanguages(false) as $language) {
                $unclassed_category->title[$language['id_lang']] = $this->l('Unclassed');
                $unclassed_category->content[$language['id_lang']] = '';
                $unclassed_category->link_rewrite[$language['id_lang']] = $this->l('Unclassed');
            }
            $unclassed_category->save();
            Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $unclassed_category->id);
        }
        // Install
        return parent::install()
            && $this->installModuleTab(
                'AdminEverPsBlog',
                'IMPROVE',
                $this->l('Blog')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogPost',
                'AdminEverPsBlog',
                $this->l('Posts')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogCategory',
                'AdminEverPsBlog',
                $this->l('Categories')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogTag',
                'AdminEverPsBlog',
                $this->l('Tags')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogComment',
                'AdminEverPsBlog',
                $this->l('Comments')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogAuthor',
                'AdminEverPsBlog',
                $this->l('Authors')
            )
            && Configuration::updateValue('EVERPSBLOG_ROUTE', 'blog')
            && Configuration::updateValue('EVERBLOG_ADMIN_EMAIL', 1)
            && Configuration::updateValue('EVERBLOG_EMPTY_TRASH', 7)
            && Configuration::updateValue('EVERBLOG_ALLOW_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_CHECK_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_BANNED_USERS', '')
            && Configuration::updateValue('EVERBLOG_BANNED_IP', '')
            && Configuration::updateValue('EVERPSBLOG_PAGINATION', '10')
            && Configuration::updateValue('EVERPSBLOG_HOME_NBR', '4')
            && Configuration::updateValue('EVERPSBLOG_PRODUCT_NBR', '4')
            && Configuration::updateValue('EVERPSBLOG_EXCERPT', '150')
            && Configuration::updateValue('EVERPSBLOG_TITLE_LENGTH', '150')
            && Configuration::updateValue('EVERBLOG_PRODUCT_COLUMNS', 1)
            && Configuration::updateValue('EVERBLOG_CATEG_COLUMNS', 1)
            && Configuration::updateValue('EVERPSBLOG_BLOG_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_POST_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_CAT_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_AUTHOR_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_TAG_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERBLOG_SITEMAP_NUMBER', 5000)
            && $this->checkHooks()
            && $this->checkObligatoryHooks();
    }

    public function uninstall()
    {
        include dirname(__FILE__).'/install/uninstall.php';
        include dirname(__FILE__).'/install/hooks-uninstall.php';
        include dirname(__FILE__).'/install/images-uninstall.php';
        Db::getInstance()->delete(
            'hook_module',
            'id_module = ' . (int) $this->id
        );
        Configuration::deleteByName('EVERBLOG_CATEG_COLUMNS');
        return parent::uninstall()
            && $this->uninstallModuleTab('AdminEverPsBlog')
            && $this->uninstallModuleTab('AdminEverPsBlogPost')
            && $this->uninstallModuleTab('AdminEverPsBlogCategory')
            && $this->uninstallModuleTab('AdminEverPsBlogTag')
            && $this->uninstallModuleTab('AdminEverPsBlogComment')
            && $this->uninstallModuleTab('AdminEverPsBlogAuthor');
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
                ],
            ],
        ];
    }

    public function clearEverblogContent()
    {
        // Suppression des anciens posts, catégories, et tags
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
        // Supprimer le contenu actuel d'Everblog
        $this->clearEverblogContent();

        // Récupérer les catégories, tags et posts de Magento
        $categories = Db::getInstance()->executeS('SELECT * FROM aw_blog_cat');
        $tags = Db::getInstance()->executeS('SELECT * FROM aw_blog_tags');
        $posts = Db::getInstance()->executeS('SELECT * FROM aw_blog');

        // Récupérer tous les IDs de groupes dans PrestaShop
        $groups = Db::getInstance()->executeS('SELECT id_group FROM ' . _DB_PREFIX_ . 'group');
        $groupIds = array_column($groups, 'id_group');
        $allowedGroupsJson = json_encode($groupIds); // Convertir en JSON

        // Créer une catégorie par défaut "Non classé"
        $defaultCategory = new EverPsBlogCategory();
        $defaultCategory->title = [1 => 'Non classé']; // Assuming language ID = 1
        $defaultCategory->meta_title = [1 => 'Non classé'];
        $defaultCategory->meta_description = [1 => ''];
        $defaultCategory->link_rewrite = [1 => Tools::link_rewrite('non-classe')];
        $defaultCategory->date_add = date('Y-m-d H:i:s');
        $defaultCategory->date_upd = date('Y-m-d H:i:s');
        $defaultCategory->id_parent_category = 1;
        $defaultCategory->allowed_groups = $allowedGroupsJson;
        $defaultCategory->indexable = true;
        $defaultCategory->follow = true;
        $defaultCategory->active = true;
        $defaultCategory->id_shop = 1;
        $defaultCategory->save();
        // Récupérer l'ID de la catégorie par défaut
        $defaultCategoryId = Configuration::get('EVERBLOG_UNCLASSED_ID');

        // Insérer les catégories dans Everblog
        foreach ($categories as $category) {
            $newCategory = new EverPsBlogCategory();
            $newCategory->title = [1 => $category['title']]; // Assuming language ID = 1
            $newCategory->meta_title = [1 => $category['meta_keywords']];
            $newCategory->meta_description = [1 => $category['meta_description']];
            $newCategory->link_rewrite = [1 => Tools::link_rewrite($category['title'])];
            $newCategory->date_add = date('Y-m-d H:i:s');
            $newCategory->date_upd = date('Y-m-d H:i:s');
            $newCategory->allowed_groups = $allowedGroupsJson;
            $newCategory->active = true;
            $newCategory->id_shop = 1;
            $newCategory->save();
        }

        // Insérer les tags dans Everblog
        foreach ($tags as $tag) {
            $newTag = new EverPsBlogTag();
            $newTag->title = [1 => $tag['tag']];
            $newTag->meta_title = [1 => $tag['tag']];
            $newTag->meta_description = [1 => ''];
            $newTag->link_rewrite = [1 => Tools::link_rewrite($tag['tag'])];
            $newTag->allowed_groups = $allowedGroupsJson;
            $newTag->date_add = date('Y-m-d H:i:s');
            $newTag->date_upd = date('Y-m-d H:i:s');
            $newTag->indexable = true;
            $newTag->follow = true;
            $newTag->active = true;
            $newTag->id_shop = 1;
            $newTag->save();
        }

        // Insérer les posts dans Everblog
        foreach ($posts as $post) {
            $post['post_content'] = str_replace('\r\n', '<p></p>', $post['post_content']);
            // Nettoyage et remplacement des images dans le contenu
            $cleanedContent = $this->replaceAndDownloadImages($post['post_content']);
            $cleanedContent = Tools::purifyHTML($cleanedContent);
            $cleanedExcerpt = $this->replaceAndDownloadImages($post['short_content']);
            // Création du post
            $newPost = new EverPsBlogPost();
            $newPost->title = [1 => $post['title']];
            $newPost->meta_title = [1 => $post['meta_keywords']];
            $newPost->meta_description = [1 => $post['meta_description']];
            $newPost->link_rewrite = [1 => Tools::link_rewrite($post['title'])];
            $newPost->date_add = $post['created_time'] ? $post['created_time'] : date('Y-m-d H:i:s');
            $newPost->date_upd = $post['update_time'] ? $post['update_time'] : date('Y-m-d H:i:s');
            $newPost->active = ($post['status'] == 1) ? true : false;
            $newPost->indexable = ($post['status'] == 1) ? true : false;
            $newPost->follow = ($post['status'] == 1) ? true : false;
            $newPost->content = [1 => $cleanedContent];
            $newPost->post_status = 'published';
            $newPost->id_shop = 1;
            $newPost->id_default_category = $defaultCategoryId;
            $newPost->allowed_groups = $allowedGroupsJson; // Ajouter les groupes autorisés
            $newPost->save();
            // dump($post['post_content']);
            // die();
            // Récupérer l'ID du post enregistré
            $postId = $newPost->id;
            // dump(pSQL($post['post_content'], true));
            // die();
            // dump($cleanedContent);
            // die();
            // Mise à jour directe du contenu dans la base de données
            // Db::getInstance()->execute('
            //     UPDATE ' . _DB_PREFIX_ . 'ever_blog_post_lang
            //     SET content = "' . pSQL($post['post_content'], true) . '", excerpt = "' . pSQL($cleanedExcerpt) . '"
            //     WHERE id_ever_post = ' . (int)$postId
            // );
            EverPsBlogTaxonomy::insertTaxonomy($defaultCategoryId, $postId, 'category');
            $newPost->save();
            // Insérer la catégorie par défaut "Non classé" pour chaque post

            // Insérer les autres catégories associées au post
            $postCategories = Db::getInstance()->executeS('SELECT * FROM aw_blog_post_cat WHERE post_id = ' . (int)$post['post_id']);
            foreach ($postCategories as $postCategory) {
                EverPsBlogTaxonomy::insertTaxonomy($postCategory['cat_id'], $postId, 'category');
            }

            // Insérer les tags associés au post
            $postTags = explode(',', $post['tags']);
            foreach ($postTags as $tag) {
                $existingTag = Db::getInstance()->getRow('SELECT id_ever_tag FROM ' . _DB_PREFIX_ . 'ever_blog_tag_lang WHERE title = "' . pSQL($tag) . '"');
                if ($existingTag) {
                    EverPsBlogTaxonomy::insertTaxonomy($existingTag['id_ever_tag'], $postId, 'tag');
                }
            }
            $newPost->save();
        }
    }

    public function replaceAndDownloadImages($content)
    {
        // Convert WordPress [caption] shortcodes to Bootstrap 5 figure markup
        $content = preg_replace_callback(
            '/\[caption[^\]]*\](<img[^>]+>)(.*?)\[\/caption\]/si',
            function ($matches) {
                $imgTag = $matches[1];
                $caption = trim($matches[2]);
                $imgDom = new DOMDocument();
                @$imgDom->loadHTML($imgTag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $img = $imgDom->getElementsByTagName('img')->item(0);
                if ($img) {
                    $classes = $img->getAttribute('class');
                    $img->setAttribute('class', trim($classes . ' img-fluid figure-img'));
                    $imgTag = $imgDom->saveHTML($img);
                }
                return '<figure class="figure text-center">' . $imgTag
                    . '<figcaption class="figure-caption">' . $caption . '</figcaption></figure>';
            },
            $content
        );

        // Replace common WordPress alignment classes with Bootstrap 5 ones
        $replace = [
            'aligncenter' => 'mx-auto d-block',
            'alignright'  => 'float-end',
            'alignleft'   => 'float-start',
        ];
        $content = str_replace(array_keys($replace), array_values($replace), $content);

        // Remplacer {{media url="..."}}
        $pattern = '/\{\{media url="wysiwyg\/([^"]+)"\}\}/';
        $content = preg_replace_callback($pattern, function($matches) {
            $fullUrl = 'https://www.comptoir-de-vie.com/media/wysiwyg/' . $matches[1];
            $localPath = $this->downloadImage($fullUrl);
            if ($localPath) {
                return $localPath; // Replace the {{media url="..."}} with the local path
            }
            return $fullUrl; // If download fails, return the original full URL
        }, $content);

        // Remplacer les URLs d'images dans les balises <img>
        $dom = new DOMDocument;
        if (!empty($content)) {
            @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }
        $xpath = new DOMXPath($dom);
        $images = $xpath->query("//img");

        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (!empty($src)) {
                // Identifier et traiter toutes les URLs valides
                if (strpos($src, 'http://www.comptoir-de-vie.com/') !== false) {
                    // Cible tous les domaines nécessaires
                    $localPath = $this->downloadImage($src);
                    if ($localPath) {
                        $img->setAttribute('src', $localPath);
                    }
                } elseif (strpos($src, 'http://www.comptoir-de-vie.com/') === false && strpos($src, '/comptoir/') !== false) {
                    // Cible tous les domaines nécessaires
                    $localPath = $this->downloadImage('http://www.comptoir-de-vie.com/'.$src);
                    if ($localPath) {
                        $img->setAttribute('src', $localPath);
                    }
                }
            }
            // Apply Bootstrap 5 responsive class to images
            $currentClass = $img->getAttribute('class');
            $img->setAttribute('class', trim($currentClass . ' img-fluid'));
        }

        return $dom->saveHTML();
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
        $this->checkObligatoryHooks();
        $this->checkAndFixDatabase();
        $this->html = '';
        // Process internal linking
        if (Tools::isSubmit('submitGenerateBlogSitemap')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->generateBlogSitemap();
            }
        }
        if (Tools::isSubmit('submitEverPsBlogConf')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }
        if (Tools::isSubmit('submitWooImport')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->importWooCommercePosts(
                    Tools::getValue('EVER_WOO_API_URL'),
                    Tools::getValue('EVER_WOO_CK'),
                    Tools::getValue('EVER_WOO_CS')
                );
            }
        }
        if (Tools::isSubmit('submitWpImport')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->importWordPressPosts(
                    Tools::getValue('EVER_WP_API_URL')
                );
            }
        }
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }
        // Display confirmations
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            $this->name,
            'emptytrash',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $pending = $this->context->link->getModuleLink(
            $this->name,
            'pending',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $planned = $this->context->link->getModuleLink(
            $this->name,
            'planned',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $sitemap_link = $this->context->link->getModuleLink(
            $this->name,
            'sitemaps',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $default_blog = $this->context->link->getModuleLink(
            $this->name,
            'blog',
            [],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $this->context->smarty->assign([
            'blog_sitemaps' => $this->getSitemapIndexes(),
            'image_dir' => $this->_path.'views/img',
            'everpsblogcron' => $emptytrash,
            'everpsblogcronpending' => $pending,
            'everpsblogcronplanned' => $planned,
            'everpsblogcronsitemap' => $sitemap_link,
            'blog_url' => $default_blog,
        ]);
        if ($this->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/upgrade.tpl');
        }
        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/header.tpl');
        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/footer.tpl');
        return $this->html;
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submitEverPsBlogConf')) {
            if (!Tools::getValue('EVERPSBLOG_ROUTE')
                || !Validate::isLinkRewrite(Tools::getValue('EVERPSBLOG_ROUTE'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Blog route" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_EXCERPT')
                || !Validate::isInt(Tools::getValue('EVERPSBLOG_EXCERPT'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Excerpt length" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_TITLE_LENGTH')
                || !Validate::isInt(Tools::getValue('EVERPSBLOG_TITLE_LENGTH'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Title length" is not valid');
            }
            if (Tools::getValue('EVERBLOG_SHOW_POST_COUNT')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_POST_COUNT'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Show post count" is not valid');
            }
            if (Tools::getValue('EVERBLOG_TINYMCE')
                && !Validate::isBool(Tools::getValue('EVERBLOG_TINYMCE'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Extends TinyMCE" is not valid');
            }
            if (Tools::getValue('EVERBLOG_SHOW_HOME')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_HOME'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Show post on homepage" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_PAGINATION')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_PAGINATION'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts per page" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_HOME_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_HOME_NBR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts for home" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_PRODUCT_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_PRODUCT_NBR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts for product" is not valid');
            }
            if (!Tools::getValue('EVERBLOG_ADMIN_EMAIL')
                || !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_ADMIN_EMAIL'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Admin email" is not valid');
            }
            if (Tools::getValue('EVERBLOG_ALLOW_COMMENTS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ALLOW_COMMENTS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Allow comments" is not valid');
            }
            if (Tools::getValue('EVERBLOG_CHECK_COMMENTS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_CHECK_COMMENTS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Check comments" is not valid');
            }
            if (Tools::getValue('EVERBLOG_RSS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_RSS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Use RSS feed" is not valid');
            }
            if (Tools::getValue('EVERBLOG_SHOW_AUTHOR')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_AUTHOR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Show author" is not valid');
            }
            if (Tools::getValue('EVERBLOG_BANNED_USERS')
                && !Validate::isGenericName(Tools::getValue('EVERBLOG_BANNED_USERS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Banned users" is not valid');
            }
            if (Tools::getValue('EVERBLOG_BANNED_IP')
                && !Validate::isGenericName(Tools::getValue('EVERBLOG_BANNED_IP'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Banned IP" is not valid');
            }
            if (Tools::getValue('EVERBLOG_ONLY_LOGGED_COMMENT')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ONLY_LOGGED_COMMENT'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Only logged can comment" is not valid');
            }
            if (Tools::getValue('EVERBLOG_EMPTY_TRASH')
                && !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_EMPTY_TRASH'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Empty trash" is not valid'
                );
            }
            if (!Tools::getValue('EVERPSBLOG_TYPE')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_TYPE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Default blog type" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ANIMATE')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ANIMATE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Use cool CSS" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_RELATED_POST')
                && !Validate::isBool(Tools::getValue('EVERBLOG_RELATED_POST'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show related posts on product page" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_SHOW_FEAT_CAT')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_FEAT_CAT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show featured category image" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_SHOW_FEAT_TAG')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_FEAT_TAG'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show featured tag image" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ARCHIVE_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ARCHIVE_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show archives on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_PRODUCT_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_PRODUCT_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show products on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_TAG_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_TAG_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show tags on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_CATEG_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_CATEG_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show categories on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_FANCYBOX')
                && !Validate::isBool(Tools::getValue('EVERBLOG_FANCYBOX'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Fancybox" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_CAT_FEATURED')
                && !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_CAT_FEATURED'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Featured category" is not valid'
                );
            }
            // Multilingual fields
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('EVERBLOG_TITLE_'.$lang['id_lang'])
                    && !Validate::isString(Tools::getValue('EVERBLOG_TITLE_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog title is invalid'
                    );
                }
                if (Tools::getValue('EVERBLOG_META_DESC_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERBLOG_META_DESC_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog meta description is invalid'
                    );
                }
                if (Tools::getValue('EVERBLOG_TOP_TEXT_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERBLOG_TOP_TEXT_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog top text is invalid'
                    );
                }
                if (Tools::getValue('EVERBLOG_BOTTOM_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERBLOG_BOTTOM_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog bottom text is invalid'
                    );
                }
            }
            // Layouts
            if (Tools::getValue('EVERPSBLOG_BLOG_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_BLOG_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Blog layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_POST_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_POST_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Post layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_CAT_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_CAT_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Category layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_AUTHOR_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_AUTHOR_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Author layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_TAG_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_TAG_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Tag layout" is not valid'
                );
            }
            if (isset($_FILES['wordpress_xml'])
                && isset($_FILES['wordpress_xml']['tmp_name'])
                && !empty($_FILES['wordpress_xml']['tmp_name'])
            ) {
                if (pathinfo($_FILES['wordpress_xml']['name'], PATHINFO_EXTENSION) != 'xml') {
                    $this->postErrors[] = $this->l(
                        'Error : The field "Tag layout" is not valid'
                    );
                } else {
                    $this->importWordPressFile($_FILES['wordpress_xml']);
                }
            }
            if (Tools::getValue('EVERBLOG_IMPORT_POST_STATE')
                && !Validate::isString(Tools::getValue('EVERBLOG_IMPORT_POST_STATE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Default post status on import from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_IMPORT_AUTHORS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_IMPORT_AUTHORS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Import authors from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_IMPORT_CATS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_IMPORT_CATS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Import categories from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_IMPORT_TAGS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_IMPORT_TAGS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Import tags from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ENABLE_AUTHORS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ENABLE_AUTHORS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Enable authors from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ENABLE_CATS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ENABLE_CATS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Enable categories from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ENABLE_TAGS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ENABLE_TAGS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Enable tags from WordPress xml file" is not valid'
                );
            }
            if (Tools::isSubmit('submitWooImport')) {
                if (Tools::getValue('EVER_WOO_API_URL') && !Validate::isUrl(Tools::getValue('EVER_WOO_API_URL'))) {
                    $this->postErrors[] = $this->l('Error : The field "API URL" is not valid');
                }
            } elseif (Tools::isSubmit('submitWpImport')) {
                if (Tools::getValue('EVER_WP_API_URL') && !Validate::isUrl(Tools::getValue('EVER_WP_API_URL'))) {
                    $this->postErrors[] = $this->l('Error : The field "API URL" is not valid');
                }
            }
        }
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        // Reset hooks
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-blog');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-category');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-post');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-search');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-tag');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-author');
        Hook::exec('hookModuleRoutes');
        // Preparing multilingual datas
        $everblog_title = [];
        $everblog_meta_desc = [];
        $everblog_top_text = [];
        $everblog_bottom_text = [];
        foreach (Language::getLanguages(false) as $lang) {
            $everblog_title[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            ) : '';
            $everblog_meta_desc[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            ) : '';
            $everblog_bottom_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            ) : '';
        }
        // Save all datas
        foreach (array_keys($form_values) as $key) {
            if ($key == 'EVERBLOG_TITLE') {
                Configuration::updateValue(
                    $key,
                    $everblog_title
                );
            } elseif ($key == 'EVERBLOG_META_DESC') {
                Configuration::updateValue(
                    $key,
                    $everblog_meta_desc
                );
            } elseif ($key == 'EVERBLOG_TOP_TEXT') {
                Configuration::updateValue(
                    $key,
                    $everblog_top_text,
                    true
                );
            } elseif ($key == 'EVERBLOG_BOTTOM_TEXT') {
                Configuration::updateValue(
                    $key,
                    $everblog_bottom_text,
                    true
                );
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
        if ((bool) Tools::getValue('EVERBLOG_SHOW_HOME') === true) {
            $this->registerHook('displayHome');
        } else {
            $this->unregisterHook('displayHome');
        }
        $handle = fopen(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom.css',
            'w+'
        );
        fclose($handle);
        /* Insert new values to the CSS file */
        file_put_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom.css',
            Tools::getValue('EVERBLOG_CSS')
        );
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function getConfigFormValues()
    {
        $custom_css = Tools::file_get_contents(
            _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/custom.css'
        );
        $formValues = [];
        $everblog_title = [];
        $everblog_meta_desc = [];
        $everblog_top_text = [];
        $everblog_bottom_text = [];
        foreach (Language::getLanguages(false) as $lang) {
            $everblog_title[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            ) : '';
            $everblog_meta_desc[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            ) : '';
            $everblog_bottom_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            ) : '';
        }
        $formValues[] = [
            'EVERPSBLOG_ROUTE' => Configuration::get('EVERPSBLOG_ROUTE'),
            'EVERPSBLOG_EXCERPT' => Configuration::get('EVERPSBLOG_EXCERPT'),
            'EVERPSBLOG_TITLE_LENGTH' => Configuration::get('EVERPSBLOG_TITLE_LENGTH'),
            'EVERBLOG_TINYMCE' => Configuration::get('EVERBLOG_TINYMCE'),
            'EVERBLOG_SHOW_POST_COUNT' => Configuration::get('EVERBLOG_SHOW_POST_COUNT'),
            'EVERBLOG_SHOW_HOME' => Configuration::get('EVERBLOG_SHOW_HOME'),
            'EVERPSBLOG_PAGINATION' => Configuration::get('EVERPSBLOG_PAGINATION'),
            'EVERPSBLOG_HOME_NBR' => Configuration::get('EVERPSBLOG_HOME_NBR'),
            'EVERPSBLOG_PRODUCT_NBR' => Configuration::get('EVERPSBLOG_PRODUCT_NBR'),
            'EVERBLOG_ADMIN_EMAIL' => Configuration::get('EVERBLOG_ADMIN_EMAIL'),
            'EVERBLOG_ALLOW_COMMENTS' => Configuration::get('EVERBLOG_ALLOW_COMMENTS'),
            'EVERBLOG_CHECK_COMMENTS' => Configuration::get('EVERBLOG_CHECK_COMMENTS'),
            'EVERBLOG_RSS' => Configuration::get('EVERBLOG_RSS'),
            'EVERBLOG_SHOW_AUTHOR' => Configuration::get('EVERBLOG_SHOW_AUTHOR'),
            'EVERBLOG_BANNED_USERS' => Configuration::get('EVERBLOG_BANNED_USERS'),
            'EVERBLOG_BANNED_IP' => Configuration::get('EVERBLOG_BANNED_IP'),
            'EVERBLOG_ONLY_LOGGED_COMMENT' => Configuration::get('EVERBLOG_ONLY_LOGGED_COMMENT'),
            'EVERBLOG_EMPTY_TRASH' => Configuration::get('EVERBLOG_EMPTY_TRASH'),
            'EVERPSBLOG_TYPE' => Configuration::get('EVERPSBLOG_TYPE'),
            'EVERBLOG_ANIMATE' => Configuration::get('EVERBLOG_ANIMATE'),
            'EVERBLOG_RELATED_POST' => Configuration::get('EVERBLOG_RELATED_POST'),
            'EVERBLOG_SHOW_FEAT_CAT' => Configuration::get('EVERBLOG_SHOW_FEAT_CAT'),
            'EVERBLOG_SHOW_FEAT_TAG' => Configuration::get('EVERBLOG_SHOW_FEAT_TAG'),
            'EVERBLOG_ARCHIVE_COLUMNS' => Configuration::get('EVERBLOG_ARCHIVE_COLUMNS'),
            'EVERBLOG_TAG_COLUMNS' => Configuration::get('EVERBLOG_TAG_COLUMNS'),
            'EVERBLOG_PRODUCT_COLUMNS' => Configuration::get('EVERBLOG_PRODUCT_COLUMNS'),
            'EVERBLOG_CATEG_COLUMNS' => Configuration::get('EVERBLOG_CATEG_COLUMNS'),
            'EVERBLOG_FANCYBOX' => Configuration::get('EVERBLOG_FANCYBOX'),
            'EVERBLOG_CAT_FEATURED' => Configuration::get('EVERBLOG_CAT_FEATURED'),
            'EVERBLOG_TITLE' => static::getConfigInMultipleLangs(
                'EVERBLOG_TITLE'
            ),
            'EVERBLOG_META_DESC' => static::getConfigInMultipleLangs(
                'EVERBLOG_META_DESC'
            ),
            'EVERBLOG_TOP_TEXT' => static::getConfigInMultipleLangs(
                'EVERBLOG_TOP_TEXT'
            ),
            'EVERBLOG_BOTTOM_TEXT' => static::getConfigInMultipleLangs(
                'EVERBLOG_BOTTOM_TEXT'
            ),
            'EVERPSBLOG_BLOG_LAYOUT' => Configuration::get('EVERPSBLOG_BLOG_LAYOUT'),
            'EVERPSBLOG_POST_LAYOUT' => Configuration::get('EVERPSBLOG_POST_LAYOUT'),
            'EVERPSBLOG_CAT_LAYOUT' => Configuration::get('EVERPSBLOG_CAT_LAYOUT'),
            'EVERPSBLOG_AUTHOR_LAYOUT' => Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT'),
            'EVERPSBLOG_TAG_LAYOUT' => Configuration::get('EVERPSBLOG_TAG_LAYOUT'),
            'EVERBLOG_CSS' => $custom_css,
            'EVERBLOG_CSS_FILE' => Configuration::get('EVERBLOG_CSS_FILE'),
            'EVERBLOG_IMPORT_AUTHORS' => Configuration::get('EVERBLOG_IMPORT_AUTHORS'),
            'EVERBLOG_IMPORT_CATS' => Configuration::get('EVERBLOG_IMPORT_CATS'),
            'EVERBLOG_IMPORT_TAGS' => Configuration::get('EVERBLOG_IMPORT_TAGS'),
            'EVERBLOG_ENABLE_AUTHORS' => Configuration::get('EVERBLOG_ENABLE_AUTHORS'),
            'EVERBLOG_ENABLE_CATS' => Configuration::get('EVERBLOG_ENABLE_CATS'),
            'EVERBLOG_ENABLE_TAGS' => Configuration::get('EVERBLOG_ENABLE_TAGS'),
            'EVERBLOG_IMPORT_POST_STATE' => Configuration::get('EVERBLOG_IMPORT_POST_STATE'),
            'EVER_WP_API_URL' => Configuration::get('EVER_WP_API_URL'),
            'EVER_WOO_API_URL' => Configuration::get('EVER_WOO_API_URL'),
            'EVER_WOO_CK' => Configuration::get('EVER_WOO_CK'),
            'EVER_WOO_CS' => Configuration::get('EVER_WOO_CS'),
            'wordpress_xml' => ''
        ];
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEverPsBlogConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int) $this->context->language->id,
        ];
        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        // TODO : add default blog text per lang ?
        $employees = Employee::getEmployeesByProfile(
            1,
            true
        );
        $default_snippet = [
            [
                'snippet' => 'Article',
                'name' => $this->l('Simple article'),
            ],
            [
                'snippet' => 'NewsArticle',
                'name' => $this->l('News article'),
            ],
        ];
        $layouts = [
            [
                'layout' => 'layouts/layout-full-width.tpl',
                'name' => $this->l('Full width'),
            ],
            [
                'layout' => 'layouts/layout-left-column.tpl',
                'name' => $this->l('Left column'),
            ],
            [
                'layout' => 'layouts/layout-right-column.tpl',
                'name' => $this->l('Right column'),
            ],
            [
                'layout' => 'layouts/layout-both-columns.tpl',
                'name' => $this->l('Both columns'),
            ],
        ];
        $trash_days = [
            [
                'id_trash' => 0,
                'name' => $this->l('Do not empty trash'),
            ],
            [
                'id_trash' => 1,
                'name' => $this->l('One day'),
            ],
            [
                'id_trash' => 2,
                'name' => $this->l('Two days'),
            ],
            [
                'id_trash' => 3,
                'name' => $this->l('Three days'),
            ],
            [
                'id_trash' => 4,
                'name' => $this->l('Four days'),
            ],
            [
                'id_trash' => 5,
                'name' => $this->l('Five days'),
            ],
            [
                'id_trash' => 6,
                'name' => $this->l('Six days'),
            ],
            [
                'id_trash' => 7,
                'name' => $this->l('One week'),
            ],
        ];
        $css_files = [
            [
                'id_file' => 'default',
                'name' => $this->l('default.css file'),
            ],
            [
                'id_file' => 'red',
                'name' => $this->l('red.css file'),
            ],
            [
                'id_file' => 'green',
                'name' => $this->l('green.css file'),
            ],
            [
                'id_file' => 'yellow',
                'name' => $this->l('yellow.css file'),
            ],
            [
                'id_file' => 'white',
                'name' => $this->l('white.css file'),
            ],
        ];
        $post_status = [
            [
                'id_status' => 'draft',
                'name' => $this->l('draft'),
            ],
            [
                'id_status' => 'pending',
                'name' => $this->l('pending'),
            ],
            [
                'id_status' => 'published',
                'name' => $this->l('published'),
            ],
            [
                'id_status' => 'trash',
                'name' => $this->l('trash'),
            ],
            [
                'id_status' => 'planned',
                'name' => $this->l('planned'),
            ],
        ];
        $form_fields = [];
        $form_fields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Blog default Settings'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Blog base route'),
                        'name' => 'EVERPSBLOG_ROUTE',
                        'desc' => $this->l('Leaving empty will set "blog"'),
                        'hint' => $this->l('Use a keyword associated to your shop'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Post content excerpt'),
                        'name' => 'EVERPSBLOG_EXCERPT',
                        'desc' => $this->l('Post excerpt length for content on listing'),
                        'hint' => $this->l('Please set post content excerpt'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Post title length'),
                        'name' => 'EVERPSBLOG_TITLE_LENGTH',
                        'desc' => $this->l('Post title length for content on listing'),
                        'hint' => $this->l('Please set post title length'),
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Extends TinyMCE on blog management ?'),
                        'desc' => $this->l('Set yes to extends TinyMCE on blog management pages'),
                        'hint' => $this->l('Else TinyMCE will be default'),
                        'required' => false,
                        'name' => 'EVERBLOG_TINYMCE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show post views count ?'),
                        'desc' => $this->l('Set yes to show views count'),
                        'hint' => $this->l('Else will only be shown on admin'),
                        'required' => false,
                        'name' => 'EVERBLOG_SHOW_POST_COUNT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show post on homepage ?'),
                        'desc' => $this->l('Set yes to show posts on homepage'),
                        'hint' => $this->l('Else posts won\'t be shown on homepage'),
                        'required' => false,
                        'name' => 'EVERBLOG_SHOW_HOME',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Number of posts for home'),
                        'name' => 'EVERPSBLOG_HOME_NBR',
                        'desc' => $this->l('Leaving empty will set 4 posts'),
                        'hint' => $this->l('Posts are 4 per row'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Number of posts for product'),
                        'name' => 'EVERPSBLOG_PRODUCT_NBR',
                        'desc' => $this->l('Leaving empty will set 4 posts'),
                        'hint' => $this->l('Posts are 4 per row'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Posts per page'),
                        'name' => 'EVERPSBLOG_PAGINATION',
                        'desc' => $this->l('Leaving empty will set 10 posts per page'),
                        'hint' => $this->l('Will add pagination'),
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Admin email'),
                        'desc' => $this->l('Will receive new comments notification by email'),
                        'hint' => $this->l('You can set a new account on your shop'),
                        'required' => true,
                        'name' => 'EVERBLOG_ADMIN_EMAIL',
                        'options' => [
                            'query' => $employees,
                            'id' => 'id_employee',
                            'name' => 'email',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Allow comments on posts ?'),
                        'desc' => $this->l('Set yes to allow comments'),
                        'hint' => $this->l('You can check them before publishing'),
                        'required' => false,
                        'name' => 'EVERBLOG_ALLOW_COMMENTS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Check comments on posts before they are published ?'),
                        'desc' => $this->l('Set yes to check comments before publishing'),
                        'hint' => $this->l('In order to avoid spam'),
                        'required' => false,
                        'name' => 'EVERBLOG_CHECK_COMMENTS',
                        'is_bool' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Allow only registered customers to comment ?'),
                        'desc' => $this->l('Set yes to allow only registered customers to comment'),
                        'hint' => $this->l('Else everyone will be able to comment'),
                        'required' => false,
                        'name' => 'EVERBLOG_ONLY_LOGGED_COMMENT',
                        'is_bool' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Empty trash'),
                        'desc' => $this->l('Please choose auto empty trash in days'),
                        'hint' => $this->l('Will auto delete trashed posts on CRON task'),
                        'required' => true,
                        'name' => 'EVERBLOG_EMPTY_TRASH',
                        'options' => [
                            'query' => $trash_days,
                            'id' => 'id_trash',
                            'name' => 'name',
                        ],
                        'lang' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Default blog SEO title'),
                        'name' => 'EVERBLOG_TITLE',
                        'desc' => $this->l('Max 65 characters for SEO'),
                        'hint' => $this->l('Will impact SEO'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Default blog SEO meta description'),
                        'name' => 'EVERBLOG_META_DESC',
                        'desc' => $this->l('Max 165 characters for SEO'),
                        'hint' => $this->l('Will impact SEO'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Default blog type'),
                        'desc' => $this->l('Will be used for structured metadatas'),
                        'hint' => $this->l('Select blog type depending on your posts'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_TYPE',
                        'options' => [
                            'query' => $default_snippet,
                            'id' => 'snippet',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Default blog top text'),
                        'name' => 'EVERBLOG_TOP_TEXT',
                        'desc' => $this->l('Will be shown on blog top default page'),
                        'hint' => $this->l('Explain your blog purpose'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                        'autoload_rte' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Default blog bottom text'),
                        'name' => 'EVERBLOG_BOTTOM_TEXT',
                        'desc' => $this->l('Will be shown on blog bottom default page'),
                        'hint' => $this->l('Explain your blog purpose'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                        'autoload_rte' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Use RSS feed ?'),
                        'desc' => $this->l('Will add a link to RSS feed on blog and each tag, category, author'),
                        'hint' => $this->l('Else feed wont be used'),
                        'required' => false,
                        'name' => 'EVERBLOG_RSS',
                        'is_bool' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show author ?'),
                        'desc' => $this->l('Will show author name and avatar on posts'),
                        'hint' => $this->l('Else author name and avatar will be hidden'),
                        'required' => false,
                        'name' => 'EVERBLOG_SHOW_AUTHOR',
                        'is_bool' => false,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Banned users'),
                        'name' => 'EVERBLOG_BANNED_USERS',
                        'desc' => $this->l('Add banned users typing their emails, one per line'),
                        'hint' => $this->l('Unwanted users won\'t be able to post comments'),
                        'cols' => 36,
                        'rows' => 4,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Banned IP'),
                        'name' => 'EVERBLOG_BANNED_IP',
                        'desc' => $this->l('Add banned users typing their IP addresses, one per line'),
                        'hint' => $this->l('Unwanted users won\'t be able to post comments'),
                        'cols' => 36,
                        'rows' => 4,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show parent categories list on left/right columns ?'),
                        'desc' => $this->l('Set yes show a list of all parent categories on left or right columns'),
                        'hint' => $this->l('Will show ordered parent categories on left/right columns'),
                        'name' => 'EVERBLOG_CATEG_COLUMNS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show tags list on left/right columns ?'),
                        'desc' => $this->l('Set yes to activate cool stuff'),
                        'hint' => $this->l('Set yes show a tags cloud on left or right columns'),
                        'required' => false,
                        'name' => 'EVERBLOG_TAG_COLUMNS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show archives list on left/right columns ?'),
                        'desc' => $this->l('Set yes show links for monthly posts on left or right columns'),
                        'hint' => $this->l('Will show yearly and monthly posts'),
                        'required' => false,
                        'name' => 'EVERBLOG_ARCHIVE_COLUMNS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show related products on columns ?'),
                        'desc' => $this->l('Set yes to show products linked to the post'),
                        'hint' => $this->l('Will display related products in left or right columns'),
                        'required' => false,
                        'name' => 'EVERBLOG_PRODUCT_COLUMNS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show related posts on products pages ?'),
                        'desc' => $this->l('Set yes show related posts on product pages footer'),
                        'hint' => $this->l('Will show related posts on product page footer'),
                        'required' => false,
                        'name' => 'EVERBLOG_RELATED_POST',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show featured images on categories ?'),
                        'desc' => $this->l('Set yes to show each category featured image'),
                        'hint' => $this->l('Else category featured image won\'t be shown'),
                        'name' => 'EVERBLOG_SHOW_FEAT_CAT',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show featured images on tags ?'),
                        'desc' => $this->l('Set yes to show each tag featured image'),
                        'hint' => $this->l('Else tag featured image won\'t be shown'),
                        'name' => 'EVERBLOG_SHOW_FEAT_TAG',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Activate cool CSS animations ?'),
                        'desc' => $this->l('Set yes to activate cool stuff'),
                        'hint' => $this->l('Will add animations on posts, images, etc'),
                        'name' => 'EVERBLOG_ANIMATE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Fancybox'),
                        'hint' => $this->l('Set no if your theme already uses it'),
                        'desc' => $this->l('Use Fancybox for popups on post images'),
                        'name' => 'EVERBLOG_FANCYBOX',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Featured category on blog default page'),
                        'name' => 'EVERBLOG_CAT_FEATURED',
                        'desc' => $this->l('Featured category'),
                        'hint' => $this->l('Will show category products on blog page'),
                        'cols' => 36,
                        'rows' => 4,
                    ],
                ],
                'buttons' => [
                    'generateBlogSitemap' => [
                        'name' => 'submitGenerateBlogSitemap',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Generate sitemaps'),
                    ],
                ],
                'submit' => [
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ],
            ],
        ];
        $form_fields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Blog layout settings'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Default blog layout'),
                        'desc' => $this->l('Will add or remove columns from blog page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_BLOG_LAYOUT',
                        'options' => [
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Default post layout'),
                        'desc' => $this->l('Will add or remove columns from post page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_POST_LAYOUT',
                        'options' => [
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Default category layout'),
                        'desc' => $this->l('Will add or remove columns from category page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_CAT_LAYOUT',
                        'options' => [
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Default author layout'),
                        'desc' => $this->l('Will add or remove columns from author page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_AUTHOR_LAYOUT',
                        'options' => [
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Default tag layout'),
                        'desc' => $this->l('Will add or remove columns from tag page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_TAG_LAYOUT',
                        'options' => [
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ],
            ],
        ];
        $form_fields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('WordPress XML import settings'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Default post state on XML import'),
                        'desc' => $this->l('Will set default post state on XML import'),
                        'hint' => $this->l('Please select default post state on XML file import'),
                        'required' => true,
                        'name' => 'EVERBLOG_IMPORT_POST_STATE',
                        'options' => [
                            'query' => $post_status,
                            'id' => 'id_status',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Import WordPress authors from xml file ?'),
                        'desc' => $this->l('Set yes to import WordPress authors'),
                        'hint' => $this->l('Else no authors will be imported'),
                        'required' => false,
                        'name' => 'EVERBLOG_IMPORT_AUTHORS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Import WordPress categories from xml file ?'),
                        'desc' => $this->l('Set yes to import WordPress categories'),
                        'hint' => $this->l('Else no categories will be imported'),
                        'required' => false,
                        'name' => 'EVERBLOG_IMPORT_CATS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Import WordPress tags from xml file ?'),
                        'desc' => $this->l('Set yes to import WordPress tags'),
                        'hint' => $this->l('Else no tags will be imported'),
                        'required' => false,
                        'name' => 'EVERBLOG_IMPORT_TAGS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable WordPress authors from xml file ?'),
                        'desc' => $this->l('Set yes to enable WordPress authors'),
                        'hint' => $this->l('Else no authors will be enabled'),
                        'required' => false,
                        'name' => 'EVERBLOG_ENABLE_AUTHORS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable WordPress categories from xml file ?'),
                        'desc' => $this->l('Set yes to enable WordPress categories'),
                        'hint' => $this->l('Else no categories will be enabled'),
                        'required' => false,
                        'name' => 'EVERBLOG_ENABLE_CATS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable WordPress tags from xml file ?'),
                        'desc' => $this->l('Set yes to enable WordPress tags'),
                        'hint' => $this->l('Else no tags will be enabled'),
                        'required' => false,
                        'name' => 'EVERBLOG_ENABLE_TAGS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Import WordPress XML file'),
                        'desc' => $this->l('Import WordPress XML posts file'),
                        'hint' => $this->l('Will import posts from WordPress XML file'),
                        'name' => 'wordpress_xml',
                        'required' => false,
                    ],
                ],
                'submit' => [
                    'name' => 'submit',
                    'title' => $this->l('Save and import'),
                ],
            ],
        ];
        $form_fields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('WordPress REST import settings'),
                    'icon' => 'icon-cloud-download',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API URL'),
                        'name' => 'EVER_WP_API_URL',
                        'required' => false,
                    ],
                ],
                'buttons' => [
                    'importWp' => [
                        'name' => 'submitWpImport',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'title' => $this->l('Import WordPress posts'),
                    ],
                ],
            ],
        ];
        $form_fields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('WooCommerce API import settings'),
                    'icon' => 'icon-cloud-download',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API URL'),
                        'name' => 'EVER_WOO_API_URL',
                        'required' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Consumer key'),
                        'name' => 'EVER_WOO_CK',
                        'required' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Consumer secret'),
                        'name' => 'EVER_WOO_CS',
                        'required' => false,
                    ],
                ],
                'buttons' => [
                    'importWoo' => [
                        'name' => 'submitWooImport',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'title' => $this->l('Import WooCommerce posts'),
                    ],
                ],
            ],
        ];
        $form_fields[] = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Design settings'),
                    'icon' => 'icon-smile',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Custom CSS file'),
                        'desc' => $this->l('You can change here default CSS file'),
                        'hint' => $this->l('By changing CSS file, you will change blog colors'),
                        'required' => true,
                        'name' => 'EVERBLOG_CSS_FILE',
                        'options' => [
                            'query' => $css_files,
                            'id' => 'id_file',
                            'name' => 'name',
                        ],
                        'lang' => false,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS for blog'),
                        'desc' => $this->l('Add here your custom CSS rules'),
                        'hint' => $this->l('Webdesigners here can manage CSS rules for blog'),
                        'name' => 'EVERBLOG_CSS',
                    ],
                ],
                'submit' =>[
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ],
            ],
        ];
        return $form_fields;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path . 'views/css/ever.css');
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJs($this->_path . 'views/js/ever.js');
        }
        if ((bool) Configuration::get('EVERBLOG_TINYMCE') === true) {
            $blogAdminControllers = [
                'AdminEverPsBlogPost',
                'AdminEverPsBlogTag',
                'AdminEverPsBlogAuthor',
                'AdminEverPsBlogCategory',
                'AdminEverPsBlogComment',
            ];
            if (in_array(Tools::getValue('controller'), $blogAdminControllers)
                || Tools::getValue('configure') == $this->name
            ) {
                $this->context->controller->addJs($this->_path . 'views/js/adminTinyMce.js');
            }
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        return $this->hookActionAdminControllerSetMedia();
    }

    public function hookDisplayAdminAfterHeader()
    {
        if ($this->checkLatestEverModuleVersion()) {
            return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/upgrade.tpl');
        }
    }

    public function hookDisplayHeader()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');
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
            if ($controller_name == 'post') {
                if ((int) Configuration::get('EVERBLOG_FANCYBOX')) {
                    if ($controller_name != 'order') {
                        $this->context->controller->addCSS(($this->_path) . 'views/css/jquery.fancybox.min.css', 'all');
                        $this->context->controller->addJS(($this->_path) . 'views/js/jquery.fancybox.min.js', 'all');
                    }
                }
            }
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
                $post_products = EverPsBlogTaxonomy::getPostProductsTaxonomies($id_post);
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
        $tags = EverPsBlogTag::getAllTags(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $categories = EverPsBlogCategory::getAllCategories(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $latest_posts = EverPsBlogPost::getLatestPosts(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            0,
            (int) $post_number
        );
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
        $cacheId = $this->name . '-hookDisplayBanner-' . $idLang . '-' . $idShop;
        if (!$this->isCached('home.tpl', $cacheId)) {
            if ((int) Configuration::get('EVERPSBLOG_HOME_NBR') > 0) {
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
            $starredPosts = EverPsBlogPost::getStarredPosts(
                (int) $this->context->language->id,
                (int) $this->context->shop->id,
                0,
                (int) $post_number
            );
            if (!$starredPosts || !count($starredPosts)) {
                return;
            }
            $evercategories = EverPsBlogCategory::getAllCategories(
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            $siteUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
            $this->context->smarty->assign([
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blogUrl' => $blogUrl,
                'everpsblog' => $starredPosts,
                'evercategory' => $evercategories,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => $siteUrl . '/modules/everpsblog/views/img/',
                'animated' => $animate,
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
        if ($module_name == 'everpsblog') {
            if ($controller_name == 'post') {
                $this->context->smarty->assign([
                    'everfancybox' => (bool) Configuration::get('EVERBLOG_FANCYBOX'),
                ]);
                return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
            }
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        try {
            foreach (Shop::getShops() as $shop) {
                $this->publishPlannedPosts(
                    (int) $shop['id_shop']
                );
                $this->emptyTrash(
                    (int) $shop['id_shop']
                );
            }
            if (isset($params['html'])) {
                $params['html'] = $this->parseShortcodes($params['html']);
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($this->name . ' : ' . $e->getMessage());
        }
    }


    public function emptyTrash($id_shop)
    {
        $return = false;
        $days = (int) Configuration::get('EVERBLOG_EMPTY_TRASH');
        foreach (Language::getLanguages(false) as $language) {
            $posts = EverPsBlogPost::getPosts(
                (int) $language['id_lang'],
                (int) $id_shop,
                0,
                null,
                'trash'
            );
            if (!$posts) {
                return true;
            }
            foreach ($posts as $trash_post) {
                if ((strtotime($trash_post['date_upd']) >= strtotime('-' . $days . ' days'))) {
                    $post = new EverPsBlogPost(
                        (int) $trash_post['id_ever_post']
                    );
                    if ($post->delete()) {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    public function sendPendingNotification($id_shop)
    {
        $employee = new Employee(
            (int) Configuration::get('EVERBLOG_ADMIN_EMAIL')
        );
        $posts = EverPsBlogPost::getPosts(
            (int) $employee->id_lang,
            (int) $id_shop,
            0,
            0,
            'pending'
        );
        if (!count($posts)) {
            return true;
        }
        $post_list = '';
        foreach ($posts as $pending) {
            $post = new EverPsBlogPost(
                (int) $pending['id_ever_post'],
                (int) $employee->id_lang,
                (int) $id_shop
            );
            $post_list .= '<br/><p>' . $post->title . '</p>';
        }
        $mailDir = $this->module_folder . '/mails/';
        $everShopEmail = Configuration::get('PS_SHOP_EMAIL');
        $sent = Mail::send(
            (int) $this->context->language->id,
            'pending',
            $this->l('Review on pending posts'),
            [
                '{shop_name}'=> Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}'=> _PS_IMG_DIR_ . Configuration::get(
                    'PS_LOGO',
                    null,
                    null,
                    (int) $this->context->shop->id
                ),
                '{posts}' => $post_list,
            ],
            $employee->email,
            null,
            $everShopEmail,
            Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            $mailDir,
            false,
            null,
            $everShopEmail,
            $everShopEmail,
            Configuration::get('PS_SHOP_NAME')
        );
        return $sent;
    }

    public function publishPlannedPosts($id_shop)
    {
        $context = Context::getContext();
        $posts = EverPsBlogPost::getPosts(
            (int) $context->language->id,
            (int) $id_shop,
            0,
            0,
            'planned'
        );
        if (!count($posts)) {
            return;
        }
        foreach ($posts as $planned) {
            $post = new EverPsBlogPost(
                (int) $planned['id_ever_post'],
                (int) $context->language->id,
                (int) $id_shop
            );
            if ($post->date_add <= date('Y-m-d H:i:s')) {
                $post->post_status = 'published';
                $post->save();
            }
        }
        return true;
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
        $post_categories = EverPsBlogCleaner::convertToArray(
            json_decode($params['object']->post_categories, true)
        );
        $post_tags = EverPsBlogCleaner::convertToArray(
            json_decode($params['object']->post_tags, true)
        );
        $post_products = EverPsBlogCleaner::convertToArray(
            json_decode($params['object']->post_products, true)
        );
        // First drop post taxonomies
        EverPsBlogTaxonomy::dropTaxonomy(
            (int) $params['object']->id,
            'category'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int) $params['object']->id,
            'tag'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int) $params['object']->id,
            'product'
        );
        // Then insert taxonomies
        foreach ($post_categories as $id_post_category) {
            EverPsBlogTaxonomy::insertTaxonomy(
                (int) $id_post_category,
                (int) $params['object']->id,
                'category'
            );
        }
        foreach ($post_tags as $id_post_tag) {
            EverPsBlogTaxonomy::insertTaxonomy(
                (int) $id_post_tag,
                (int) $params['object']->id,
                'tag'
            );
        }
        foreach ($post_products as $id_post_product) {
            EverPsBlogTaxonomy::insertTaxonomy(
                (int) $id_post_product,
                (int) $params['object']->id,
                'product'
            );
        }
        // At least check root taxonomy
        EverPsBlogTaxonomy::checkDefaultPostCategory(
            $params['object']->id
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
        $image = EverPsBlogImage::getBlogImage(
            (int) $params['object']->id,
            (int) Context::getContext()->shop->id,
            'post'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        EverPsBlogTaxonomy::dropTaxonomy(
            (int) $params['object']->id,
            'category'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int) $params['object']->id,
            'tag'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int) $params['object']->id,
            'product'
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogCategoryDeleteAfter($params)
    {
        if ((int) $params['object']->id == (int) Configuration::get('EVERBLOG_UNCLASSED_ID')) {
            // Unclassed
            $unclassed_category = new EverPsBlogCategory();
            $unclassed_category->id_parent_category = 0;
            $unclassed_category->active = 1;
            $unclassed_category->active = $root_category->id;
            $unclassed_category->id_shop = (int) $shop['id_shop'];
            foreach (Language::getLanguages(false) as $language) {
                $unclassed_category->title[$language['id_lang']] = $this->l('Unclassed');
                $unclassed_category->content[$language['id_lang']] = '';
                $unclassed_category->link_rewrite[$language['id_lang']] = $this->l('Unclassed');
            }
            $unclassed_category->save();
            Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $unclassed_category->id);
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
        $image = EverPsBlogImage::getBlogImage(
            (int) $params['object']->id,
            (int) Context::getContext()->shop->id,
            'category'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        EverPsBlogTaxonomy::dropCategoryTaxonomy(
            (int) $params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogTagDeleteAfter($params)
    {
        $old_img = $this->module_folder . '/views/img/tags/tag_image_' . (int) $params['object']->id . '.jpg';
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        EverPsBlogTaxonomy::dropTagTaxonomy(
            (int) $params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectAuthorDeleteAfter($params)
    {
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
        $image = EverPsBlogImage::getBlogImage(
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
        EverPsBlogTaxonomy::dropProductTaxonomy(
            (int) $params['object']->id
        );
    }

    public function generateBlogSitemap($id_shop = null, $cron = false)
    {
        if (!$id_shop) {
            $id_shop = (int) $this->context->shop->id;
        }
        $languages = Language::getLanguages(
            true,
            (int) $id_shop
        );
        $result = false;
        foreach ($languages as $id_lang) {
            $result &= $this->processSitemapAuthor((int) $id_shop, (int) $id_lang);
            $result &= $this->processSitemapTag((int) $id_shop, (int) $id_lang);
            $result &= $this->processSitemapCategory((int) $id_shop, (int) $id_lang);
            $result &= $this->processSitemapPost((int) $id_shop, (int) $id_lang);
        }
        $this->postSuccess[] = $this->l('All XML sitemaps have been generated');
        if ((bool) $cron === true) {
            return $result;
        }
    }

    private function processSitemapPost($id_shop, $id_lang)
    {
        $iso_lang = Language::getIsoById((int) $id_lang);
        $sitemap = new EverPsBlogSitemap(
            Tools::getHttpHost(true) . __PS_BASE_URI__
        );
        $sitemap->setPath(_PS_ROOT_DIR_.'/');
        $sitemap->setFilename('blogpost_' . (int) $id_shop . '_lang_' . $iso_lang);
        $sql = 'SELECT id_ever_post FROM ' . _DB_PREFIX_ . 'ever_blog_post
            WHERE sitemap = 1 AND post_status = "published"';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $post = new EverPsBlogPost(
                    (int) $result['id_ever_post'],
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                if (isset($post->allowed_groups) && $post->allowed_groups) {
                    $allowedGroups = json_decode($post->allowed_groups);
                    // Allow on sitemap only on visitor group
                    if (is_array($allowedGroups) && !in_array('1', $allowedGroups)) {
                        continue;
                    }
                }
                $post_url = $link->getModuleLink(
                    'everpsblog',
                    'post',
                    [
                        'id_ever_post' => $post->id,
                        'link_rewrite' => $post->link_rewrite,
                    ],
                );
                $sitemap->addItem(
                    $post_url,
                    1,
                    'weekly',
                    $post->date_upd
                );
            }
            return $sitemap->createSitemapIndex(
                Tools::getHttpHost(true) . __PS_BASE_URI__,
                'Today'
            );
        }
    }

    private function processSitemapAuthor($id_shop, $id_lang)
    {
        $iso_lang = Language::getIsoById((int) $id_lang);

        $sitemap = new EverPsBlogSitemap(
            Tools::getHttpHost(true) . __PS_BASE_URI__
        );
        $sitemap->setPath(_PS_ROOT_DIR_ . '/');
        $sitemap->setFilename('blogauthor_' . (int) $id_shop . '_lang_' . $iso_lang);
        $sql = 'SELECT id_ever_author FROM ' . _DB_PREFIX_ . 'ever_blog_author
            WHERE sitemap = 1 AND active = 1';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $author = new EverPsBlogAuthor(
                    (int) $result['id_ever_author'],
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                if (isset($author->allowed_groups) && $author->allowed_groups) {
                    $allowedGroups = json_decode($author->allowed_groups);
                    // Allow on sitemap only on visitor group
                    if (is_array($allowedGroups) && !in_array('1', $allowedGroups)) {
                        continue;
                    }
                }
                $author_url = $link->getModuleLink(
                    'everpsblog',
                    'author',
                    [
                        'id_ever_author' => $author->id,
                        'link_rewrite' => $author->link_rewrite,
                    ],
                );
                if ((bool) $author->active === true) {
                    $sitemap->addItem(
                        $author_url,
                        1,
                        'weekly',
                        $author->date_upd
                    );
                }
            }
            return $sitemap->createSitemapIndex(
                Tools::getHttpHost(true) . __PS_BASE_URI__,
                'Today'
            );
        }
    }

    private function processSitemapTag($id_shop, $id_lang)
    {
        $iso_lang = Language::getIsoById((int) $id_lang);
        $sitemap = new EverPsBlogSitemap(
            Tools::getHttpHost(true) . __PS_BASE_URI__
        );
        $sitemap->setPath(_PS_ROOT_DIR_ . '/');
        $sitemap->setFilename('blogtag_' . (int) $id_shop . '_lang_' . $iso_lang);
        $sql = 'SELECT id_ever_tag FROM ' . _DB_PREFIX_ . 'ever_blog_tag
            WHERE sitemap = 1 AND active = 1';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $tag = new EverPsBlogTag(
                    (int) $result['id_ever_tag'],
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                if (isset($tag->allowed_groups) && $tag->allowed_groups) {
                    $allowedGroups = json_decode($tag->allowed_groups);
                    // Allow on sitemap only on visitor group
                    if (is_array($allowedGroups) && !in_array('1', $allowedGroups)) {
                        continue;
                    }
                }
                $tag_url = $link->getModuleLink(
                    'everpsblog',
                    'tag',
                    [
                        'id_ever_tag' => $tag->id,
                        'link_rewrite' => $tag->link_rewrite,
                    ],
                );
                if ((bool) $tag->active === true) {
                    $sitemap->addItem(
                        $tag_url,
                        1,
                        'weekly',
                        $tag->date_upd
                    );
                }
            }
            return $sitemap->createSitemapIndex(
                Tools::getHttpHost(true) . __PS_BASE_URI__,
                'Today'
            );
        }
    }

    private function processSitemapCategory($id_shop, $id_lang)
    {
        $iso_lang = Language::getIsoById((int) $id_lang);
        $sitemap = new EverPsBlogSitemap(
            Tools::getHttpHost(true) . __PS_BASE_URI__
        );
        $sitemap->setPath(_PS_ROOT_DIR_.'/');
        $sitemap->setFilename('blogcategory_' . (int) $id_shop . '_lang_' . $iso_lang);
        $sql = 'SELECT id_ever_category FROM ' . _DB_PREFIX_ . 'ever_blog_category
            WHERE sitemap = 1 AND active = 1';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $category = new EverPsBlogCategory(
                    (int) $result['id_ever_category'],
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                if (isset($category->allowed_groups) && $category->allowed_groups) {
                    $allowedGroups = json_decode($category->allowed_groups);
                    // Allow on sitemap only on visitor group
                    if (is_array($allowedGroups) && !in_array('1', $allowedGroups)) {
                        continue;
                    }
                }
                $category_url = $link->getModuleLink(
                    'everpsblog',
                    'category',
                    [
                        'id_ever_category' => $category->id,
                        'link_rewrite' => $category->link_rewrite,
                    ],
                );
                if ((bool) $category->active === true
                    && (bool) $category->is_root_category === false
                ) {
                    $sitemap->addItem(
                        $category_url,
                        1,
                        'weekly',
                        $category->date_upd
                    );
                }
            }
            return $sitemap->createSitemapIndex(
                Tools::getHttpHost(true) . __PS_BASE_URI__,
                'Today'
            );
        }
    }

    public function getSitemapIndexes()
    {
        $indexes = [];
        $sitemap_indexes_dir = glob(_PS_ROOT_DIR_ . '/*');
        foreach ($sitemap_indexes_dir as $index) {
            if (is_file($index)
                && pathinfo($index, PATHINFO_EXTENSION) == 'xml'
                && strpos(basename($index), 'index')
            ) {
                $indexes[] = Tools::getHttpHost(true) . __PS_BASE_URI__ . basename($index);
            }
        }
        return (array) $indexes;
    }

    public function hookActionAdminMetaAfterWriteRobotsFile($params)
    {
        $indexes = $this->getSitemapIndexes();
        // Panda theme uses random int on css file parameter
        $allowSitemap = 'Disallow: /modules/stthemeeditor/views/css' . "\r\n";
        $allowSitemap .= "\n";
        if ($indexes) {
            foreach ($indexes as $index) {
                $allowSitemap .= 'Sitemap: '
                . $index
                . "\r\n";
            }
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
            $this->registerHook('actionAdminControllerSetMedia');
            $this->registerHook('displayHome');
            $this->registerHook('displayLeftColumn');
            $this->registerHook('displayRightColumn');
            $this->registerHook('displayFooterProduct');
            $this->registerHook('displayFooter');
            $this->registerHook('displayCustomerAccount');
            $this->registerHook('moduleRoutes');
            $this->registerHook('displayBackOfficeHeader');
            $this->registerHook('actionObjectProductDeleteAfter');
            $this->registerHook('displayAdminAfterHeader');
            $this->registerHook('actionAdminMetaAfterWriteRobotsFile');
            $this->registerHook('actionOutputHTMLBefore');
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
            $this->registerHook('displayBackOfficeHeader');
            $this->registerHook('displayAdminAfterHeader');
            $this->registerHook('actionAdminMetaAfterWriteRobotsFile');
            $this->registerHook('actionOutputHTMLBefore');
            $this->registerHook('actionObjectEverPsBlogPostAddAfter');
            $this->registerHook('actionObjectEverPsBlogPostUpdateAfter');
            $this->registerHook('actionObjectEverPsBlogCategoryUpdateAfter');
            $this->registerHook('actionObjectEverPsBlogTagUpdateAfter');
            $this->registerHook('actionObjectEverPsBlogAuthorUpdateAfter');
            $this->registerHook('actionObjectShopDeleteAfter');
            $this->registerHook('actionObjectEverPsBlogPostDeleteAfter');
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
                        $category = new EverPsBlogCategory();
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
                        $tag = new EverPsBlogTag();
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
                $author = new EverPsBlogAuthor();
                $author->nickhandle = (string) $el->creator;
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
                $dom->loadHTML($el->content);
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
                $post_content = preg_replace('/<!--(.|\s)*?-->/', '', $post_content);
                $post_content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $post_content);
                $post_content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $post_content);
                $post_content = $this->cleanWpShortcodes($post_content);
                $post = new EverPsBlogPost();
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
                        $image = EverPsBlogImage::getBlogImage(
                            (int) $post->id,
                            (int) Context::getContext()->shop->id,
                            'post'
                        );
                        if (!$image) {
                            $image = new EverPsBlogImage();
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
            $this->postSuccess[] = $this->l('WordPress posts have been imported');
        } else {
            $this->postErrors[] = $this->l('An error has occured while importing WordPress file');
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
                $post = new EverPsBlogPost();
                $id_lang = $this->getIdLangFromWpData($data);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $language) {
                    $content = $this->replaceAndDownloadImages(
                        $this->cleanWpShortcodes($data->content->rendered)
                    );
                    $content = Tools::purifyHTML($content);
                    $excerpt = $this->replaceAndDownloadImages(
                        $this->cleanWpShortcodes($data->excerpt->rendered)
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
                $post->post_status = 'publish';

                $post_categories = [];
                if (!empty($data->categories)) {
                    foreach ($data->categories as $cat_id) {
                        $catData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/categories/' . (int) $cat_id);
                        if ($catData && isset($catData->slug)) {
                            $category = EverPsBlogCategory::getCategoryByLinkRewrite($catData->slug);
                            if (!Validate::isLoadedObject($category)) {
                                $category = new EverPsBlogCategory();
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
                            $author = new EverPsBlogAuthor();
                            $author->nickhandle = $authorData->slug;
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
                            $tag = new EverPsBlogTag();
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
                            $image = new EverPsBlogImage();
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
            $this->postSuccess[] = $this->l('WooCommerce posts have been imported');
        } else {
            $this->postErrors[] = $this->l('An error occured while importing WooCommerce posts');
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
                if (Validate::isLoadedObject($post)) {
                    continue;
                }
                $post = new EverPsBlogPost();
                $id_lang = $this->getIdLangFromWpData($data);
                $langs = $id_lang ? [ ['id_lang' => $id_lang] ] : Language::getLanguages(false);
                foreach ($langs as $language) {
                    $content = $this->replaceAndDownloadImages(
                        $this->cleanWpShortcodes($data->content->rendered)
                    );
                    $content = $this->removeJavascript($content);
                    $excerpt = $this->replaceAndDownloadImages(
                        $this->cleanWpShortcodes($data->excerpt->rendered)
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
                $post->date_add = $data->date;
                $post->date_upd = $data->modified;
                $post->post_status = 'publish';

                $post_categories = [];
                if (!empty($data->categories)) {
                    foreach ($data->categories as $cat_id) {
                        $catData = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/categories/' . (int) $cat_id);
                        if ($catData && isset($catData->slug)) {
                            $category = EverPsBlogCategory::getCategoryByLinkRewrite($catData->slug);
                            if (!Validate::isLoadedObject($category)) {
                                $category = new EverPsBlogCategory();
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
                            $author = new EverPsBlogAuthor();
                            $author->nickhandle = $authorData->slug;
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
                                $tag = new EverPsBlogTag();
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

                if (!empty($post_categories)) {
                    $post->id_default_category = $post_categories[0];
                    $post->post_categories = json_encode($post_categories);
                }
                if (!empty($post_tags)) {
                    $post->post_tags = json_encode(array_unique($post_tags));
                }

                $result &= $post->save();

                if (!empty($data->featured_media)) {
                    $media = $this->wpRequest(rtrim($apiUrl, '/') . '/wp-json/wp/v2/media/' . (int) $data->featured_media);
                    if ($media && isset($media->source_url)) {
                        $local = $this->downloadImage($media->source_url);
                        if ($local) {
                            $image = new EverPsBlogImage();
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
            $this->postSuccess[] = $this->l('WordPress posts have been imported');
        } else {
            $this->postErrors[] = $this->l('An error occured while importing WordPress posts');
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
        $redirect_lines = [];
        foreach ($redirects as $datas) {
            foreach ($datas as $from => $to) {
                $redirect_lines[] = 'Redirect 301 ' . rtrim($from, '/') . ' ' . $to;
            }
        }
        if (!empty($redirect_lines)) {
            file_put_contents(
                dirname(__FILE__) . '/wordpress_redirects.txt',
                implode(PHP_EOL, $redirect_lines)
            );
        }
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
        $db = Db::getInstance();
        // Ajoute les colonnes manquantes à la table ever_blog_post
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
        // Ajoute les colonnes manquantes à la table ps_ever_blog_post_lang
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
        // Ajoute les colonnes manquantes à la table ever_blog_category
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
        // Ajoute les colonnes manquantes à la table ps_ever_blog_category_lang
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
        // Ajoute les colonnes manquantes à la table ever_blog_tag
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
        // Ajoute les colonnes manquantes à la table ps_ever_blog_tag_lang
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
        // Ajoute les colonnes manquantes à la table ever_blog_author
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
        // Ajoute les colonnes manquantes à la table ps_ever_blog_author_lang
        $columnsToAdd = [
            'id_ever_author' => 'int(10) unsigned NOT NULL',
            'meta_title' => 'varchar(255) DEFAULT NULL',
            'meta_description' => 'varchar(255) DEFAULT NULL',
            'link_rewrite' => 'varchar(255) DEFAULT NULL',
            'content' => 'text NOT NULL',
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

    private function cleanWpShortcodes($html)
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


    private function parseShortcodes($html)
    {
        return preg_replace_callback('/\[everpsblog([^\]]*)\]/i', function ($m) {
            $attrs = [];
            if (preg_match_all('/(\w+)="?([^\s"]+)"?/', trim($m[1]), $attrMatches, PREG_SET_ORDER)) {
                foreach ($attrMatches as $attr) {
                    $attrs[strtolower($attr[1])] = $attr[2];
                }
            }
            if (empty($attrs['category'])) {
                return '';
            }
            $order = isset($attrs['order']) ? strtolower($attrs['order']) : 'desc';
            $limit = isset($attrs['limit']) ? (int) $attrs['limit'] : null;
            return $this->renderPostsShortcode((int) $attrs['category'], $order, $limit);
        }, $html);
    }

    private function renderPostsShortcode($category, $order, $limit)
    {
        $orderWay = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        $posts = EverPsBlogPost::getPostsByCategory(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            (int) $category,
            0,
            $limit,
            'published',
            false,
            false,
            $orderWay
        );
        if (!$posts) {
            return '';
        }
        $this->context->smarty->assign([
            'posts' => $posts,
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/shortcode.tpl');
    }
}
