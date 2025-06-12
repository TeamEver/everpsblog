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
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file='page.tpl'}

{block name='head' append}
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    {* <meta name="twitter:site" content="@publisher_handle"> *}
    <meta name="twitter:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'} {if isset($pagination) && $pagination.current_page > 0}{l s='(page' mod='everpsblog'} {$pagination.current_page|escape:'htmlall':'UTF-8'}/{$pagination.pages_count|escape:'htmlall':'UTF-8'}{l s=')' mod='everpsblog'}{/if}">
    <meta name="twitter:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    {* <meta name="twitter:creator" content="@author_handle"> *}
    <meta name="twitter:image" content="{$shop.logo|escape:'htmlall':'UTF-8'}">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'} {if isset($pagination) && $pagination.current_page > 0}{l s='(page' mod='everpsblog'} {$pagination.current_page|escape:'htmlall':'UTF-8'}/{$pagination.pages_count|escape:'htmlall':'UTF-8'}{l s=')' mod='everpsblog'}{/if}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$shop.logo|escape:'htmlall':'UTF-8'}">
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
    "headline": "{$page.meta.title|escape:'htmlall':'UTF-8'}",
    "image": [
      "{$shop.logo|escape:'htmlall':'UTF-8'}"
     ],
    "datePublished": "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'|escape:'htmlall':'UTF-8'}",
    "dateModified": "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'|escape:'htmlall':'UTF-8'}",
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
<div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
    <h1 class="text-center flex-grow-1 m-0">{l s='Our blog' mod='everpsblog'}</h1>
    <form method="get" action="{$link->getModuleLink('everpsblog','search')|escape:'htmlall':'UTF-8'}" class="everpsblog-search ms-3" data-doofinder-ignore="true">
        <div class="input-group">
            <input class="form-control" type="search" name="s" data-doofinder-ignore="true" placeholder="{l s='Search by keywords' mod='everpsblog'}" required />
            <button class="btn btn-danger" type="submit">{l s='Search' mod='everpsblog'}</button>
        </div>
    </form>
</div>
{if isset($allow_feed) && $allow_feed}
<a class="rss-link" href="{$feed_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='RSS feed for' mod='everpsblog'} {$page.meta.title|escape:'htmlall':'UTF-8'}</a>
{/if}
<span class="paginated float-end d-none">{if isset($pagination) && $pagination.current_page > 0}{l s='(page' mod='everpsblog'} {$pagination.current_page|escape:'htmlall':'UTF-8'}/{$pagination.pages_count|escape:'htmlall':'UTF-8'}{l s=')' mod='everpsblog'}{/if}</span>
{if isset($paginated) && !$paginated}
{if isset($default_blog_top_text) && $default_blog_top_text}
<div class="row mt-2">
    {$default_blog_top_text nofilter}
</div>
{/if}
{if isset($prettyblocks_enabled) && $prettyblocks_enabled}
{widget name="prettyblocks" zone_name="displayBeforeBlog"}
{/if}
{* Hide categories list on blog pages *}
{*<div class="row mt-2">
{foreach from=$evercategory item=item}
    {if !$item.is_root_category}
    {include file='module:everpsblog/views/templates/front/loop/category_array.tpl'}
    {/if}
{/foreach}
</div>*}
{/if}

{if isset($post_number) && $post_number > 0}
<div class="row mt-2">
{hook h="displayBeforeEverLoop"}
{foreach from=$everpsblog item=item}
{include file='module:everpsblog/views/templates/front/loop/post_array.tpl'}
{/foreach}
</div>
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{if isset($post_number) && $post_number > 0}
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{/if}
{hook h="displayAfterEverLoop"}

{if isset($paginated) && !$paginated}
{if isset($default_blog_bottom_text) && $default_blog_bottom_text}
<div class="row mt-2">
    {$default_blog_bottom_text nofilter}
</div>
{/if}
{if isset($prettyblocks_enabled) && $prettyblocks_enabled}
{widget name="prettyblocks" zone_name="displayAfterBlog"}
{/if}
{/if}
{if isset($everhome_products) && $everhome_products}
<section id="products" class="mt-2">
  <h2 class="text-center">{l s='Our best products' mod='everpsblog'}</h2>
  <div class="products row">
    {foreach from=$everhome_products item="product"}
      {include file="catalog/_partials/miniatures/product.tpl" product=$product}
    {/foreach}
  </div>
</section>
{/if}
{/block}
