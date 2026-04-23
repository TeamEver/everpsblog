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
    <meta name="twitter:image" content="{if isset($has_author_banner) && $has_author_banner}{$author_banner_image|escape:'htmlall':'UTF-8'}{else}{$featured_image|escape:'htmlall':'UTF-8'}{/if}">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{if isset($has_author_banner) && $has_author_banner}{$author_banner_image|escape:'htmlall':'UTF-8'}{else}{$featured_image|escape:'htmlall':'UTF-8'}{/if}">
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
      "{if isset($has_author_banner) && $has_author_banner}{$author_banner_image|escape:'htmlall':'UTF-8'}{else}{$featured_image|escape:'htmlall':'UTF-8'}{/if}"
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
        <div class="everpsblog-blog-header container-fluid px-0 mb-4{if isset($has_author_banner) && $has_author_banner && isset($author_banner_image) && $author_banner_image} everpsblog-blog-header--has-banner{/if}"{if isset($has_author_banner) && $has_author_banner && isset($author_banner_image) && $author_banner_image} style="background-image: url('{$author_banner_image|escape:'htmlall':'UTF-8'}');"{/if}>
            <div class="everpsblog-blog-header__inner text-center py-5">
                <div class="everpsblog-taxonomy-hero-overlay">
                    <h1 itemprop="headline" class="m-0 everpsblog-blog-header__title">{$author->nickhandle|escape:'htmlall':'UTF-8'}</h1>
                </div>
            </div>
        </div>
        <div class="container my-4">
            {include file='module:everpsblog/views/templates/front/loop/search_form.tpl'}
        </div>
    </div>
    {if isset($show_author_intro) && $show_author_intro}
    <section class="container mb-4" itemprop="author" itemscope itemtype="https://schema.org/Person">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    {if isset($has_author_image) && $has_author_image && isset($author_cover) && $author_cover}
                    <div class="col-12 col-md-3 text-center mb-3 mb-md-0">
                        <img src="{$author_cover|escape:'htmlall':'UTF-8'}" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'}" class="img-fluid rounded-circle" style="max-width: 180px;" itemprop="image" loading="lazy">
                    </div>
                    <div class="col-12 col-md-9">
                    {else}
                    <div class="col-12">
                    {/if}
                        <p class="h4 mb-3" itemprop="name">{$author->nickhandle|escape:'htmlall':'UTF-8'}</p>
                        {if isset($author_summary) && $author_summary}
                        <div class="mb-3" itemprop="description">{$author_summary nofilter}</div>
                        {/if}
                        {if isset($author_social_links) && $author_social_links}
                        <div class="d-flex flex-wrap gap-2">
                            {foreach from=$author_social_links item=author_social_link}
                            <a class="btn btn-outline-secondary btn-sm" href="{$author_social_link.url|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow noopener noreferrer" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} - {$author_social_link.label|escape:'htmlall':'UTF-8'}">
                                {$author_social_link.label|escape:'htmlall':'UTF-8'}
                            </a>
                            {/foreach}
                        </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </section>
    {/if}
    {if isset($paginated) && !$paginated}
    <div class="container">
        <div class="row authorcontent {if $animated}zoomed{/if}" itemprop="articleBody">
            {$author->content nofilter}
        </div>
    </div>
    {/if}
</div>
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
{include file='module:everpsblog/views/templates/front/loop/linked_products.tpl'}
{hook h="displayAfterEverAuthor" everblogauthor=$author}
{/block}
