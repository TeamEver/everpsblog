<?php

declare(strict_types=1);

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

use PrestaShop\Module\Everpsblog\Controller\Front\AbstractFrontController;
use PrestaShop\Module\Everpsblog\Controller\Front\FrontBlogDataProviderTrait;

class EverPsBlogfeedModuleFrontController extends AbstractFrontController
{
    use FrontBlogDataProviderTrait;

    /** @var \stdClass */
    protected $feed;
    /** @var \stdClass */
    protected $category;
    /** @var \stdClass */
    protected $tag;
    /** @var \stdClass */
    protected $post;
    /** @var \stdClass */
    protected $blog;
    /** @var string[] */
    protected $allowed_feeds = [];
    public $post_number;
    public $controller_name = 'feed';

    public function init()
    {
        $this->errors = [];
        if ((bool) Configuration::get('EVERBLOG_RSS') === false) {
            Tools::redirect('index.php');
        }
        $this->allowed_feeds = ['category', 'tag', 'author', 'blog'];
        if (!Tools::getValue('feed')
            || !in_array(Tools::getValue('feed'), $this->allowed_feeds)
        ) {
            Tools::redirect('index.php');
        }
        header('Content-Type: application/rss+xml; charset=UTF-8');
        parent::init();
    }


    public function initContent()
    {
        parent::initContent();
        $channelLink = '';
        switch (Tools::getValue('feed')) {
            case 'category':
                $feed_obj = $this->getFrontCategory(
                    (int) Tools::getValue('id_obj'),
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $posts = $this->getFrontPostsByCategory(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $feed_obj->id,
                    0,
                    null
                );
                $channelLink = $this->context->link->getModuleLink(
                    $this->module->name,
                    'category',
                    [
                        'id_ever_category' => (int) $feed_obj->id,
                        'link_rewrite' => (string) $feed_obj->link_rewrite,
                    ],
                    true,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                break;

            case 'tag':
                $feed_obj = $this->getFrontTag(
                    (int) Tools::getValue('id_obj'),
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $posts = $this->getFrontPostsByTag(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $feed_obj->id,
                    0,
                    null
                );
                $channelLink = $this->context->link->getModuleLink(
                    $this->module->name,
                    'tag',
                    [
                        'id_ever_tag' => (int) $feed_obj->id,
                        'link_rewrite' => (string) $feed_obj->link_rewrite,
                    ],
                    true,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                break;

            case 'author':
                $feed_obj = $this->getFrontAuthor(
                    (int) Tools::getValue('id_obj'),
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $feed_obj->title = $feed_obj->nickhandle;
                $posts = $this->getFrontPostsByAuthor(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $feed_obj->id,
                    0,
                    null
                );
                $channelLink = $this->context->link->getModuleLink(
                    $this->module->name,
                    'author',
                    [
                        'id_ever_author' => (int) $feed_obj->id,
                        'link_rewrite' => (string) $feed_obj->link_rewrite,
                    ],
                    true,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                break;
            
            default:
                $feed_obj = new stdClass();
                // SEO title and meta desc
                $everblog_title = $this->getModuleConfigInMultipleLangs('EVERBLOG_TITLE');
                $meta_title = $everblog_title[(int) $this->context->language->id];
                // Default blog text
                $everblog_top_text = $this->getModuleConfigInMultipleLangs('EVERBLOG_TOP_TEXT');
                $default_blog_top_text = $everblog_top_text[(int) $this->context->language->id];
                $default_blog_top_text = 
                    $default_blog_top_text;
                $feed_obj->title = $meta_title;
                $feed_obj->content = $default_blog_top_text;
                $feed_obj->link_rewrite = $this->context->link->getModuleLink(
                    $this->module->name,
                    'blog',
                    [],
                    true,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $posts = $this->getFrontLatestPosts(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    0,
                    null
                );
                $channelLink = (string) $feed_obj->link_rewrite;
                break;
        }
        $this->prepareFeedChannel($feed_obj, $channelLink);
        $posts = $this->prepareFeedPosts($posts);
        $feed_url = $this->context->link->getModuleLink(
            $this->module->name,
            'feed',
            [
                'feed' => Tools::getValue('feed'),
                'id_obj' => Tools::getValue('id_obj'),
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $this->context->smarty->assign([
            'feed_url' => $feed_url,
            'feed_obj' => $feed_obj,
            'posts' => $posts,
            'locale' => $this->context->language->locale,
        ]);
        $this->setTemplate($this->getFrontThemeTemplatePath('feed.tpl'));
    }

    private function prepareFeedChannel($feedObj, $channelLink)
    {
        if (!is_object($feedObj)) {
            return;
        }

        $feedObj->feed_link = (string) $channelLink;
        $descriptionSource = '';
        if (isset($feedObj->excerpt) && trim((string) $feedObj->excerpt) !== '') {
            $descriptionSource = (string) $feedObj->excerpt;
        } elseif (isset($feedObj->meta_description) && trim((string) $feedObj->meta_description) !== '') {
            $descriptionSource = (string) $feedObj->meta_description;
        } elseif (isset($feedObj->content)) {
            $descriptionSource = (string) $feedObj->content;
        } elseif (isset($feedObj->title)) {
            $descriptionSource = (string) $feedObj->title;
        }

        $feedObj->feed_description = $this->prepareFeedText($descriptionSource, 500);
    }

    private function prepareFeedPosts(array $posts)
    {
        foreach ($posts as $post) {
            if (!is_object($post)) {
                continue;
            }

            $postId = (int) ($post->id_ever_post ?? $post->id ?? 0);
            $post->feed_link = isset($post->url) && trim((string) $post->url) !== ''
                ? (string) $post->url
                : $this->context->link->getModuleLink(
                    $this->module->name,
                    'post',
                    [
                        'id_ever_post' => $postId,
                        'link_rewrite' => (string) ($post->link_rewrite ?? ''),
                    ],
                    true,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
            $post->feed_pub_date = $this->formatFeedDate((string) ($post->date_add ?? ''));
            $post->feed_description = $this->prepareFeedText((string) ($post->summary ?? $post->excerpt ?? $post->meta_description ?? $post->content ?? ''), 500);
            $post->feed_content = $this->prepareFeedCdata((string) ($post->content ?? ''));
        }

        return $posts;
    }

    private function prepareFeedText($value, $limit = 0)
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', trim($value));
        $value = $this->stripInvalidXmlChars((string) $value);
        if ($limit > 0) {
            if (function_exists('mb_substr')) {
                return mb_substr($value, 0, $limit);
            }

            return substr($value, 0, $limit);
        }

        return $value;
    }

    private function prepareFeedCdata($value)
    {
        $value = $this->stripInvalidXmlChars((string) $value);

        return str_replace(']]>', ']]]]><![CDATA[>', $value);
    }

    private function stripInvalidXmlChars($value)
    {
        $value = preg_replace('/[^\P{C}\t\n\r]/u', '', (string) $value);

        return null !== $value ? $value : '';
    }

    private function formatFeedDate($value)
    {
        $timestamp = strtotime((string) $value);
        if (false === $timestamp) {
            return date(DATE_RSS);
        }

        return date(DATE_RSS, $timestamp);
    }
}
