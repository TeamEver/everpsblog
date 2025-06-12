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
    {if isset($allow_feed) && $allow_feed}
    <link rel="alternate" type="application/rss+xml" title="{$page.meta.title|escape:'htmlall':'UTF-8'} {if isset($pagination) && $pagination.current_page > 0}{l s='(page' mod='everpsblog'} {$pagination.current_page|escape:'htmlall':'UTF-8'}/{$pagination.pages_count|escape:'htmlall':'UTF-8'}{l s=')' mod='everpsblog'}{/if}" href="{$feed_url|escape:'htmlall':'UTF-8'}" />
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
    "datePublished": "{$category->date_add|escape:'htmlall':'UTF-8'}",
    "dateModified": "{$category->date_upd|escape:'htmlall':'UTF-8'}",
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
{if isset($show_featured_cat) && $show_featured_cat}
<div class="category-header">
  <img src="{$featured_image|escape:'htmlall':'UTF-8'}" class="img img-fluid category-featured-image featured-image" alt="{$category->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$category->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
</div>
{/if}
<div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
    <h1 class="text-center flex-grow-1 m-0">{$category->title|escape:'htmlall':'UTF-8'}</h1>
    <form method="get" action="{$link->getModuleLink('everpsblog','search')|escape:'htmlall':'UTF-8'}" class="everpsblog-search ms-3" data-doofinder-ignore="true">
        <div class="input-group">
            <input class="form-control" type="search" name="s" data-doofinder-ignore="true" placeholder="{l s='Search the blog...' mod='everpsblog'}" required />
            <button class="btn btn-secondary" type="submit">{l s='Search' mod='everpsblog'}</button>
        </div>
    </form>
</div>
{if isset($allow_feed) && $allow_feed}
<a class="rss-link" href="{$feed_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='RSS feed for' mod='everpsblog'} {$category->title|escape:'htmlall':'UTF-8'}</a>
{/if}
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row categoryinfos d-none">
        {$category->date_add|escape:'htmlall':'UTF-8'}
    </div>
    <div class="row categorycontent">
        {if isset($prettyblocks_enabled) && $prettyblocks_enabled}
        {widget name="prettyblocks" zone_name="displayBeforeCategory{$category->id}"}
        {/if}
        {$category->content nofilter}
    </div>
</div>
{/if}
{if isset($children_categories) && $children_categories && !empty($children_categories)}
<div class="row mt-2">
{foreach from=$children_categories item=item}
    {if !$item->is_root_category}
    {include file='module:everpsblog/views/templates/front/loop/category_object.tpl'}
    {/if}
{/foreach}
</div>
{/if}
{if isset($post_number) && $post_number > 0}
<div class="container">
    <div class="row">
        {hook h="displayBeforeEverLoop"}
        {foreach from=$posts item=item}
        {include file='module:everpsblog/views/templates/front/loop/post_object.tpl'}
        {/foreach}
    </div>
</div>
{if isset($post_number) && $post_number > 0}
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{/if}
{hook h="displayAfterEverLoop"}
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row categorybottomcontent {if $animated}zoomed{/if}" itemprop="articleBody">
        {if isset($prettyblocks_enabled) && $prettyblocks_enabled}
        {widget name="prettyblocks" zone_name="displayAfterCategory{$category->id}"}
        {/if}
        {$category->bottom_content nofilter}
    </div>
</div>
{/if}
{hook h="displayAfterEverCategory" everblogcategory=$category}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{/block}
