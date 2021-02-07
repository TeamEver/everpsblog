{*
 * 2019-2020 Team Ever
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
 *  @copyright 2019-2021 Team Ever
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
{/block}

{block name="page_content"}
<h1 class="text-center">{l s='Our blog' mod='everpsblog'}</h1>
{if isset($allow_feed) && $allow_feed}
<a class="rss-link" href="{$feed_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='RSS feed for' mod='everpsblog'} {$page.meta.title|escape:'htmlall':'UTF-8'}</a>
{/if}
<span class="paginated float-right d-none">{if isset($pagination) && $pagination.current_page > 0}{l s='(page' mod='everpsblog'} {$pagination.current_page|escape:'htmlall':'UTF-8'}/{$pagination.pages_count|escape:'htmlall':'UTF-8'}{l s=')' mod='everpsblog'}{/if}</span>
{if isset($paginated) && !$paginated}
{if isset($default_blog_top_text) && $default_blog_top_text}
<div class="row mt-2">
    {$default_blog_top_text nofilter}
</div>
{/if}
<div class="row mt-2">
{foreach from=$evercategory item=item}
    {if !$item.is_root_category}
    <div class="col-12 col-xs-12 col-md-6 evercategory everpsblog" id="everpsblog-{$item.id_ever_category|escape:'htmlall':'UTF-8'}">
        <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$item.id_ever_category, 'link_rewrite'=>$item.link_rewrite])|escape:'htmlall':'UTF-8'}" class="col-md-12" title="{$item.title|escape:'htmlall':'UTF-8'}">
            <h2 class="everpsblogcategory text-center" id="everpsblog-post-title-{$item.id_ever_category|escape:'htmlall':'UTF-8'}">{$item.title|escape:'htmlall':'UTF-8'}</h2>
            {if isset($show_featured_cat) && $show_featured_cat}
            <img src="{$item.featured_image|escape:'htmlall':'UTF-8'}" class="img img-fluid category-featured-image featured-image" alt="{$item.title|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'}" />
            {/if}
        </a>
    </div>
    {/if}
{/foreach}
</div>
{/if}

{if isset($post_number) && $post_number > 0}
<div class="row mt-2">
{hook h="displayBeforeEverLoop"}
{foreach from=$everpsblog item=item}
    <article class="col-12 col-xs-12 article everpsblog" id="everpsblog-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
        <div class="row">
            <div class="col-12 col-xs-12 col-md-4 article-img">
                <img src="{$item.featured_image|escape:'htmlall':'UTF-8'}" class="img-fluid {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" />
            </div>
            <div class="col-12 col-xs-12 col-md-8">
                <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}">
                        {$item.title|escape:'htmlall':'UTF-8'}
                    </a>
                </h3>
                <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
                    {$item.content nofilter}
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-blog-primary" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
                </div>
            </div>
        </div>
    </article>
{/foreach}
</div>
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{hook h="displayAfterEverLoop"}

{if isset($paginated) && !$paginated}
{if isset($default_blog_bottom_text) && $default_blog_bottom_text}
<div class="row mt-2">
    {$default_blog_bottom_text nofilter}
</div>
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
