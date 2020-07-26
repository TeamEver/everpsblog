{*
* Project : everpsblog
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
    <meta name="twitter:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}authors/author_image_{$author->id|escape:'htmlall':'UTF-8'}.jpg">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}authors/author_image_{$author->id|escape:'htmlall':'UTF-8'}.jpg">
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://google.com/article"
    },
    "headline": "{$author->nickhandle|escape:'htmlall':'UTF-8'}",
    "image": [
      "{$blogImg_dir|escape:'htmlall':'UTF-8'}authors/author_image_{$author->id|escape:'htmlall':'UTF-8'}.jpg"
     ],
    "datePublished": "{$author->date_add|escape:'htmlall':'UTF-8'}",
    "dateModified": "{$author->date_upd|escape:'htmlall':'UTF-8'}",
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
{hook h="displayBeforeEverAuthor" everblogauthor=$author}
<div class="content" itemscope="itemscope" itemtype="http://schema.org/Blog">
    <div class="container" itemscope="itemscope" itemtype="http://schema.org/BlogAuthoring" itemprop="blogAuthor">
        <h1 itemprop="headline" class="text-center">{$author->nickhandle|escape:'htmlall':'UTF-8'}</h1>
        {if isset($paginated) && !$paginated}
        <div class="row author-header">
            <img class="img img-fluid author-featured-image featured-image" src="{$blogImg_dir|escape:'htmlall':'UTF-8'}authors/author_image_{$author->id|escape:'htmlall':'UTF-8'}.jpg" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
        </div>
        {/if}
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
                    <a href="{$author->facebook|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow" class="facebook icon-gray" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Follow me !' mod='everpsblog'}</a>
                </li>
            {/if}
            {if isset($author->linkedin) && $author->linkedin}
                <li>
                    <a href="{$author->linkedin|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow" class="linkedin icon-gray" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Follow me !' mod='everpsblog'}</a>
                </li>
            {/if}
            {if isset($author->twitter) && $author->twitter}
                <li>
                    <a href="{$author->twitter|escape:'htmlall':'UTF-8'}" target="_blank" rel="nofollow" class="twitter icon-gray" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Follow me !' mod='everpsblog'}</a>
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
    <div class="col-xs-12 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
        <div class="col-md-12">
            <div class="col-xs-12 col-md-4 article-img">
                <img src="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$item->id_ever_post|escape:'htmlall':'UTF-8'}.jpg" class="img-fluid mx-auto d-block {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title nofilter} {$shop.name|escape:'htmlall':'UTF-8'}"  title="{$item->title nofilter} {$shop.name|escape:'htmlall':'UTF-8'}"/>
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
{hook h="displayAfterEverCategory" everblogauthor=$author}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{hook h="displayAfterEverAuthor" everblogauthor=$author}
{/block}
