{*
* Project : EverPsBlog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}
{if isset($showArchives) && $showArchives}
{/if}

{if isset($showCategories) && $showCategories}
<div class="columns_everblog_wrapper category_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Categories from the blog' mod='everpsblog'}</p>
    <ul>
{foreach from=$categories item=category}
{if $category.is_root_category == 0}
    <li>
        <a href="{$link->getModuleLink('everpsblog', 'category',['id_ever_category'=>$category.id_ever_category, 'link_rewrite'=>$category.link_rewrite])|escape:'html'}" class="category" title="{$category.title nofilter}">
            {$category.title nofilter}
        </a>
    </li>
{/if}
{/foreach}
    </ul>
</div>
{/if}
{if isset($showTags) && $showTags}
<div class="columns_everblog_wrapper tag_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Tags from the blog' mod='everpsblog'}</p>
{foreach from=$tags item=tag}
    <a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag.id_ever_tag, 'link_rewrite' => $tag.link_rewrite])|escape:'html'}" class="tag" title="{$tag.title nofilter}">
        {$tag.title nofilter}
    </a>
{/foreach}
</div>
{/if}

{if isset($everpsblog) && $everpsblog}
<div id="latestCarousel" class="columns_everblog_wrapper latests_wrapper carousel slide" data-ride="carousel">
    <p class="text-uppercase h6 hidden-sm-down">
        <a href="{$blogUrl|escape:'html'}">
            {l s='Latest from the blog' mod='everpsblog'}
        </a>
    </p>
    <ol class="carousel-indicators">
        {assign var=counter value=0}
        {foreach from=$everpsblog item=item}
        <li data-target="#latestCarousel" data-slide-to="{$counter}" {if $counter == 0} class="active"{/if}></li>
        {$counter=$counter+1}
        {/foreach}
    </ol>
    <div class="carousel-inner">
{assign var=postcounter value=1}
{foreach from=$everpsblog item=item}
        <div class="carousel-item {if $postcounter == 1} active{/if} article everpsblog" id="everpsblog-{$item.id_ever_post|escape:'html'}">
            <div class="d-block w-100">
                <div class="col-xs-12 article-img">
                    <img src="{$blogImg_dir|escape:'html'}posts/post_image_{$item.id_ever_post|escape:'html'}.jpg" class="col-xs-12 {if $animate}animate flipSideBySide zoomed{/if}" alt="{$item.title nofilter}" alt="{$item.title nofilter}" />
                </div>
                <div class="col-xs-12">
                    <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item.id_ever_post|escape:'html'}">
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'html'}" title="{$item.title nofilter}">
                            {$item.title nofilter}
                        </a>
                    </h3>
                    <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item.id_ever_post|escape:'html'}">
                        {$item.content|strip_tags|truncate:150:"..."}
                    </div>
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'html'}" class="btn btn-primary" title="{l s='Read more' mod='everpsblog'} {$item.title nofilter}">{l s='Read more' mod='everpsblog'}</a>
                </div>
            </div>
        </div>
{$postcounter = $postcounter+1}
{/foreach}
        <a class="carousel-control-prev" href="#latestCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only"></span>
        </a>
        <a class="carousel-control-next" href="#latestCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">{l s='Previous' mod='everpsblog'}</span>
        </a>
    </div>
</div>
{/if}
