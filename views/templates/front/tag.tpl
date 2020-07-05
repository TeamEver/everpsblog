{*
* Project : EverPsBlog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='page.tpl'}

{block name="page_content"}
{hook h="displayBeforeEverTag" everblogtag=$tag}
<div class="container">
    <div class="row">
        <h1>{$tag->title nofilter}</h1>
        {if isset($paginated) && !$paginated}
        <img src="{$blogImg_dir|escape:'htmlall':'UTF-8'}tags/tag_image_{$tag->id|escape:'htmlall':'UTF-8'}.jpg" class="img img-fluid mx-auto d-block" alt="{$tag->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">
        {/if}
    </div>
</div>
<div class="container">
    <div class="row tagcontent">
        {$tag->content nofilter}
    </div>
</div>

{if isset($post_number) && $post_number > 0}
<div class="container">
{hook h="displayBeforeEverLoop"}
{foreach from=$posts item=item}
    <div class="col-xs-12 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
        <div class="col-md-12">
            <div class="col-xs-12 col-md-4 article-img">
                <img src="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$item->id_ever_post|escape:'htmlall':'UTF-8'}.jpg" class="col-xs-12 img-fluid mx-auto d-block {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}" />
            </div>
            <div class="col-xs-12 col-md-8">
                <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">
                        {$item->title nofilter}
                    </a>
                </h3>
                <div class="everpsblogcontent" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">{$item->content|truncate:350:"..." nofilter}</div>
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary" title="{$item->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
            </div>
        </div>
    </div>
{/foreach}
{hook h="displayAfterEverLoop"}
</div>
{include file='_partials/pagination.tpl' pagination=$pagination}
{hook h="displayAfterEverTag" everblogtag=$tag}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{/block}
