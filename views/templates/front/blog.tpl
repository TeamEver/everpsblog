{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='page.tpl'}

{block name="page_content"}
{if isset($default_blog_top_text) && $default_blog_top_text}
<div class="row">
    {$default_blog_top_text nofilter}
</div>
{/if}
{if isset($paginated) && !$paginated}
<div class="row">
{foreach from=$evercategory item=item}
    {if !$item.is_root_category}
    <div class="col-xs-12 col-md-3 evercategory everpsblog" id="everpsblog-{$item.id_ever_category|escape:'html'}">
        <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$item.id_ever_category, 'link_rewrite'=>$item.link_rewrite])|escape:'html'}" class="col-md-12">
            <h3 class="everpsblogcategory" id="everpsblog-post-title-{$item.id_ever_category|escape:'html'}">{$item.title|escape:'html'}</h3>
            <img src="{$blogImg_dir|escape:'html'}categories/category_image_{$item.id_ever_category|escape:'html'}.jpg" class="img img-fluid"/>
            <p class="everpsblogcategory" id="everpsblog-post-content-{$item.id_ever_category|escape:'htmlall':'UTF-8'}">{$item.content|truncate:150:"...":true nofilter}</p>
        </a>
    </div>
    {/if}
{/foreach}
</div>
{/if}

{if isset($post_number) && $post_number > 0}
<div class="row">
{foreach from=$everpsblog item=item}
    <article class="col-xs-12 article everpsblog" id="everpsblog-{$item.id_ever_post|escape:'html'}">
        <div class="row">
            <div class="col-xs-12 col-md-4 article-img">
                <img src="{$blogImg_dir|escape:'html'}posts/post_image_{$item.id_ever_post|escape:'html'}.jpg" class="img-fluid {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item.title nofilter} {$shop.name|escape:htmlall:'UTF-8'}"/>
            </div>
            <div class="col-xs-12 col-md-8">
                <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item.id_ever_post|escape:'html'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'html'}" title="{$item.title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">
                        {$item.title nofilter}
                    </a>
                </h3>
                <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item.id_ever_post|escape:'html'}">
                    {$item.content|truncate:350:"..." nofilter}
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'html'}" class="btn btn-primary" title="{$item.title nofilter} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
                </div>
            </div>
        </div>
    </article>
{/foreach}
</div>
{else}
<div class="alert alert-info">{l s='Sorry, there is no post, please come back later !' mod='everpsblog'}</div>
{/if}
{if isset($default_blog_bottom_text) && $default_blog_bottom_text}
<div class="row">
    {$default_blog_bottom_text nofilter}
</div>
{/if}
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{/block}
