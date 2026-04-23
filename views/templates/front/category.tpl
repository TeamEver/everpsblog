{*
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
 *  @category    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file='page.tpl'}

{block name='head' append}
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    {* <meta name="twitter:site" content="@publisher_handle"> *}
    <meta name="twitter:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta name="twitter:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    {* <meta name="twitter:creator" content="@category_handle"> *}
    <meta name="twitter:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}categories/category_image_{$category->id|escape:'htmlall':'UTF-8'}.jpg">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}categories/category_image_{$category->id|escape:'htmlall':'UTF-8'}.jpg">
    {if isset($hreflang_links) && $hreflang_links}
        {foreach from=$hreflang_links item=hreflang_link}
            <link rel="alternate" hreflang="{$hreflang_link.hreflang|escape:'htmlall':'UTF-8'}" href="{$hreflang_link.href|escape:'htmlall':'UTF-8'}">
        {/foreach}
        {if isset($hreflang_x_default) && $hreflang_x_default}
            <link rel="alternate" hreflang="x-default" href="{$hreflang_x_default|escape:'htmlall':'UTF-8'}">
        {/if}
    {/if}
    {if isset($allow_feed) && $allow_feed}
    <link rel="alternate" type="application/rss+xml" title="{$page.meta.title|escape:'htmlall':'UTF-8'} {if isset($pagination) && $pagination.current_page > 0}{l s='(page' d='Modules.Everpsblog.Shop'} {$pagination.current_page|escape:'htmlall':'UTF-8'}/{$pagination.pages_count|escape:'htmlall':'UTF-8'}{l s=')' d='Modules.Everpsblog.Shop'}{/if}" href="{$feed_url|escape:'htmlall':'UTF-8'}" />
    {/if}
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "{$blog_type|escape:'htmlall':'UTF-8'}",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://google.com/article"
    },
    "headline": "{$category->title|escape:'htmlall':'UTF-8'}",
    "image": [
      "{$featured_image|escape:'htmlall':'UTF-8'}"
     ],
    "datePublished": "{$category->date_add|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}",
    "dateModified": "{$category->date_upd|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}",
    "author": {
      "@type": "Person",
      "name": "{$shop.name|escape:'htmlall':'UTF-8'}"
    },
     "publisher": {
      "@type": "Organization",
      "name": "{$shop.name|escape:'htmlall':'UTF-8'}",
      "logo": {
        "@type": "ImageObject",
        "url": "{$shop.logo|escape:'htmlall':'UTF-8'}"
      }
    }
    }
    </script>
{/block}

{block name="page_content"}
{hook h="displayBeforeEverCategory" everblogcategory=$category}
<div class="everpsblog-blog-header everpsblog-category-header container-fluid px-0 mb-4">
    <div class="everpsblog-blog-header__inner everpsblog-category-hero text-center py-5"{if isset($show_featured_cat) && $show_featured_cat && isset($featured_image) && $featured_image} style="background-image:url('{$featured_image|escape:'htmlall':'UTF-8'}'); background-size: cover; background-position: center;"{elseif isset($everpsblog_header_bg_color) && $everpsblog_header_bg_color} style="background: {$everpsblog_header_bg_color|escape:'htmlall':'UTF-8'};"{/if}>
        <div class="everpsblog-category-hero-overlay">
            <h1 class="m-0 everpsblog-blog-header__title everpsblog-category-title">{$category->title|escape:'htmlall':'UTF-8'}</h1>
            {if isset($children_categories) && $children_categories && !empty($children_categories)}
            <div class="everpsblog-subcategories everpsblog-blog-header__categories d-flex flex-wrap justify-content-center gap-2 mt-4" role="navigation" aria-label="{l s='Subcategories' d='Modules.Everpsblog.Shop'}">
                <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$category->id_ever_category, 'link_rewrite'=>$category->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn everpsblog-top-category-btn everpsblog-subcategory-btn active" title="{$category->title|escape:'htmlall':'UTF-8'}">{$category->title|escape:'htmlall':'UTF-8'}</a>
                {foreach from=$children_categories item=item}
                    {if !$item->is_root_category}
                    <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$item->id_ever_category, 'link_rewrite'=>$item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn everpsblog-top-category-btn everpsblog-subcategory-btn" title="{$item->title|escape:'htmlall':'UTF-8'}">{$item->title|escape:'htmlall':'UTF-8'}</a>
                    {/if}
                {/foreach}
            </div>
            {/if}
        </div>
    </div>
</div>
<div class="container my-4">
    <div class="d-flex justify-content-center mb-3">
        {include file='module:everpsblog/views/templates/front/loop/search_form.tpl'}
    </div>
    {if isset($allow_feed) && $allow_feed}
    <div class="text-center mb-3">
        <a class="rss-link" href="{$feed_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='RSS feed for' d='Modules.Everpsblog.Shop'} {$category->title|escape:'htmlall':'UTF-8'}</a>
    </div>
    {/if}
</div>
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row categoryinfos d-none">
        {$category->date_add|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}
    </div>
    <div class="row categorycontent">
        {$category->content nofilter}
    </div>
</div>
{/if}
{if isset($post_number) && $post_number > 0}
<div class="container">
    <div class="row">
        {hook h="displayBeforeEverLoop"}
        {foreach from=$posts item=item}
        {include file='module:everpsblog/views/templates/front/loop/post_array.tpl'}
        {/foreach}
    </div>
</div>
{if isset($post_number) && $post_number > 0 && isset($pagination.should_be_displayed) && $pagination.should_be_displayed}
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{/if}
{hook h="displayAfterEverLoop"}
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row categorybottomcontent {if $animated}zoomed{/if}" itemprop="articleBody">
        {$category->bottom_content nofilter}
    </div>
</div>
{/if}
{hook h="displayAfterEverCategory" everblogcategory=$category}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' d='Modules.Everpsblog.Shop'}</div>
{/if}
{/block}
