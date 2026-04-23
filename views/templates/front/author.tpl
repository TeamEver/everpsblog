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
    <meta name="twitter:image" content="{$featured_image|escape:'htmlall':'UTF-8'}">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$featured_image|escape:'htmlall':'UTF-8'}">
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
    "headline": "{$author->nickhandle|escape:'htmlall':'UTF-8'}",
    "image": [
      "{$featured_image|escape:'htmlall':'UTF-8'}"
     ],
    "datePublished": "{$author->date_add|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}",
    "dateModified": "{$author->date_upd|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}",
    "author": {
      "@type": "Person",
      "name": "{$author->nickhandle|escape:'htmlall':'UTF-8'}"
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
{hook h="displayBeforeEverAuthor" everblogauthor=$author}
<div class="content" itemscope="itemscope" itemtype="http://schema.org/Blog">
    <div itemscope="itemscope" itemtype="http://schema.org/BlogAuthoring" itemprop="blogAuthor">
        <div class="everpsblog-blog-header container-fluid px-0 mb-4">
            <div class="everpsblog-blog-header__inner text-center py-5"{if isset($everpsblog_header_bg_color) && $everpsblog_header_bg_color} style="background: {$everpsblog_header_bg_color|escape:'htmlall':'UTF-8'};"{/if}>
                {if isset($paginated) && !$paginated && isset($featured_image) && $featured_image}
                <div class="author-header mb-3">
                    <img class="img img-fluid author-featured-image featured-image mx-auto d-block" src="{$featured_image|escape:'htmlall':'UTF-8'}" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
                </div>
                {/if}
                <h1 itemprop="headline" class="m-0 everpsblog-blog-header__title">{$author->nickhandle|escape:'htmlall':'UTF-8'}</h1>
                {if isset($author->excerpt) && $author->excerpt}
                    <p class="everpsblog-author-excerpt mt-2 mb-0">{$author->excerpt|escape:'htmlall':'UTF-8'}</p>
                {/if}
            </div>
        </div>
        <div class="container my-4">
            <div class="d-flex justify-content-center mb-3">
                {include file='module:everpsblog/views/templates/front/loop/search_form.tpl'}
            </div>
            {if isset($allow_feed) && $allow_feed}
            <div class="text-center mb-3">
                <a class="rss-link" href="{$feed_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='RSS feed for' d='Modules.Everpsblog.Shop'} {$author->nickhandle|escape:'htmlall':'UTF-8'}</a>
            </div>
            {/if}
        </div>
    </div>
    {if isset($paginated) && !$paginated}
    <div class="container">
        <div class="row authorcontent {if $animated}zoomed{/if}" itemprop="articleBody">
            {$author->content nofilter}
        </div>
    </div>
    {/if}
</div>
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row">
        <div class="social-sharing d-flex justify-content-center">
            <ul>
            {if isset($author->facebook) && $author->facebook}
                <li>
                    <a href="{$author->facebook|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow" class="facebook icon-gray" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Follow me !' d='Modules.Everpsblog.Shop'}</a>
                </li>
            {/if}
            {if isset($author->linkedin) && $author->linkedin}
                <li>
                    <a href="{$author->linkedin|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow" class="linkedin icon-gray" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Follow me !' d='Modules.Everpsblog.Shop'}</a>
                </li>
            {/if}
            {if isset($author->twitter) && $author->twitter}
                <li>
                    <a href="{$author->twitter|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow" class="twitter icon-gray" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Follow me !' d='Modules.Everpsblog.Shop'}</a>
                </li>
            {/if}
            </ul>
        </div>
    </div>
</div>
{/if}
{if isset($post_number) && $post_number > 0}
<div class="container">
{hook h="displayBeforeEverLoop"}
{foreach from=$posts item=item}
{include file='module:everpsblog/views/templates/front/loop/post_array.tpl'}
{/foreach}
</div>
{if isset($post_number) && $post_number > 0 && isset($pagination.should_be_displayed) && $pagination.should_be_displayed}
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{/if}
{hook h="displayAfterEverLoop"}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' d='Modules.Everpsblog.Shop'}</div>
{/if}
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row authorbottomcontent {if $animated}zoomed{/if}" itemprop="articleBody">
        {$author->bottom_content nofilter}
    </div>
</div>
{/if}
{hook h="displayAfterEverAuthor" everblogauthor=$author}
{/block}
