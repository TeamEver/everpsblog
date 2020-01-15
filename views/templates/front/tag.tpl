{*
* Project : EverPsBlog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='page.tpl'}

{block name="page_content"}

<div class="container">
    <div class="row">
        <h1>{$tag->title nofilter}</h1>
        <img src="{$blogImg_dir}tags/tag_image_{$tag->id}.jpg" class="col-xs-12 img-fluid mx-auto d-block" alt="{$tag->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">
    </div>
</div>
<div class="container">
    <div class="row tagcontent">
        {$tag->content nofilter}
    </div>
</div>

{if isset($post_number) && $post_number > 0}
<div class="container">
{foreach from=$posts item=item}
    <div class="col-xs-12 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'html'}">
        <div class="col-md-12">
            <div class="col-xs-12 col-md-4 article-img">
                <img src="{$blogImg_dir|escape:'html'}posts/post_image_{$item->id_ever_post|escape:'html'}.jpg" class="col-xs-12 img-fluid mx-auto d-block {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}" />
            </div>
            <div class="col-xs-12 col-md-8">
                <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'html'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'html'}" title="{$item->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">
                        {$item->title nofilter}
                    </a>
                </h3>
                <div class="everpsblogcontent" id="everpsblog-post-content-{$item->id_ever_post|escape:'html'}">{$item->content|truncate:350:"..." nofilter}</div>
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'html'}" class="btn btn-primary" title="{$item->title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
            </div>
        </div>
    </div>
{/foreach}
</div>
{if isset($previous_page) && $previous_page}
{if $previous_page == 1}
<a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag->id, 'link_rewrite'=>$tag->link_rewrite])|escape:'html'}" class="btn btn-primary float-xs-left" id="previousPage">{l s='Previous page' mod='everpsblog'}</a>
{else}
<a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag->id, 'link_rewrite'=>$tag->link_rewrite])|escape:'html'}?page={$previous_page}" class="btn btn-primary float-xs-left" id="previousPage" rel="nofollow">{l s='Previous page' mod='everpsblog'}</a>
{/if}
{/if}
{if isset($next_page) && $next_page}
<a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag->id, 'link_rewrite'=>$tag->link_rewrite])|escape:'html'}?page={$next_page}" class="btn btn-primary float-xs-right" id="nextPage" rel="nofollow">{l s='Next page' mod='everpsblog'}</a>
{/if}
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{/block}
