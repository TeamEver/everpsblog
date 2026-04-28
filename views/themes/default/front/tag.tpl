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
    <meta name="twitter:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta name="twitter:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    {* <meta name="twitter:creator" content="@author_handle"> *}
    <meta name="twitter:image" content="{if isset($has_tag_banner) && $has_tag_banner}{$tag_banner_image|escape:'htmlall':'UTF-8'}{else}{$featured_image|escape:'htmlall':'UTF-8'}{/if}">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{if isset($has_tag_banner) && $has_tag_banner}{$tag_banner_image|escape:'htmlall':'UTF-8'}{else}{$featured_image|escape:'htmlall':'UTF-8'}{/if}">
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
{/block}

{block name="page_content"}
{hook h="displayBeforeEverTag" everblogtag=$tag}
<div class="everpsblog-blog-header container-fluid px-0 mb-4{if isset($has_tag_banner) && $has_tag_banner && isset($tag_banner_image) && $tag_banner_image} everpsblog-blog-header--has-banner{/if}"{if isset($has_tag_banner) && $has_tag_banner && isset($tag_banner_image) && $tag_banner_image} style="background-image: url('{$tag_banner_image|escape:'htmlall':'UTF-8'}');"{/if}>
    <div class="everpsblog-blog-header__inner text-center py-5">
        <div class="everpsblog-taxonomy-hero-overlay">
            <h1 class="m-0 everpsblog-blog-header__title">{$tag->title|escape:'htmlall':'UTF-8'}</h1>
        </div>
    </div>
</div>
<div class="container my-4">
    {include file="{$everpsblog_theme_front_template_base}/loop/search_form.tpl"}
    {if isset($paginated) && !$paginated}
    <div class="row tagcontent">
        {$tag->content nofilter}
    </div>
    {/if}

    {if isset($post_number) && $post_number > 0}
    <div class="row mt-2">
        {hook h="displayBeforeEverLoop"}
        {foreach from=$posts item=item}
            {include file="{$everpsblog_theme_front_template_base}/loop/post_array.tpl"}
        {/foreach}
    </div>
    {if isset($pagination.should_be_displayed) && $pagination.should_be_displayed}
    <div class="row">
        {include file='_partials/pagination.tpl' pagination=$pagination}
    </div>
    {/if}
    {hook h="displayAfterEverLoop"}
    {if isset($paginated) && !$paginated}
    <div class="row tagbottomcontent {if $animated}zoomed{/if}" itemprop="articleBody">
        {$tag->bottom_content nofilter}
    </div>
    {/if}
    {else}
    <div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' d='Modules.Everpsblog.Shop'}</div>
    {/if}
</div>
{include file="{$everpsblog_theme_front_template_base}/loop/linked_products.tpl"}
{hook h="displayAfterEverTag" everblogtag=$tag}
{/block}
