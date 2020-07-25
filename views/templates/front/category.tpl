{*
* Project : EverPsBlog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='page.tpl'}

{block name='head' append}
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    {* <meta name="twitter:site" content="@publisher_handle"> *}
    <meta name="twitter:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta name="twitter:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    {* <meta name="twitter:creator" content="@author_handle"> *}
    <meta name="twitter:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}categories/category_image_{$category->id|escape:'htmlall':'UTF-8'}.jpg">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}categories/category_image_{$category->id|escape:'htmlall':'UTF-8'}.jpg">
{/block}

{block name="page_content"}
{hook h="displayBeforeEverCategory" everblogcategory=$category}
<div class="category-header">
  <img src="{$blogImg_dir|escape:'htmlall':'UTF-8'}categories/category_image_{$category->id|escape:'htmlall':'UTF-8'}.jpg" class="img img-fluid category-featured-image featured-image" alt="{$category->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$category->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
</div>
<h1 class="text-center">{$category->title|escape:'htmlall':'UTF-8'}</h1>
{if isset($paginated) && !$paginated}
<div class="container">
    <div class="row categoryinfos d-none">
        {$category->date_add|escape:'htmlall':'UTF-8'}
    </div>
    <div class="row categorycontent">
        {$category->content nofilter}
    </div>
</div>
{/if}
{if isset($post_number) && $post_number > 0}
<div class="container">
{hook h="displayBeforeEverLoop"}
{foreach from=$posts item=item}
    <div class="col-xs-12 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
        <div class="col-md-12">
            <div class="col-xs-12 col-md-4 article-img">
                <img src="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$item->id_ever_post|escape:'htmlall':'UTF-8'}.jpg" class="img-fluid mx-auto d-block {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title nofilter} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$item->title nofilter} {$shop.name|escape:'htmlall':'UTF-8'}" />
            </div>
            <div class="col-xs-12 col-md-8">
                <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title nofilter} {$shop.name|escape:'htmlall':'UTF-8'}">
                        {$item->title nofilter}
                    </a>
                </h3>
                <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">{$item->content|truncate:350:"..." nofilter}</div>
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary" title="{$item->title nofilter} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
            </div>
        </div>
    </div>
{/foreach}
{hook h="displayAfterEverLoop"}
</div>
{include file='_partials/pagination.tpl' pagination=$pagination}
{hook h="displayAfterEverCategory" everblogcategory=$category}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{/block}
