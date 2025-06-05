{*
 * 2019-2021 Team Ever
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
{if isset($showArchives) && $showArchives}
{/if}

{if isset($showCategories) && $showCategories && isset($categories) && !empty($categories)}
<div class="columns_everblog_wrapper category_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Categories from the blog' mod='everpsblog'}</p>
    <ul>
{foreach from=$categories item=category}
{if $category.is_root_category == 0}
    <li>
        <a href="{$link->getModuleLink('everpsblog', 'category',['id_ever_category'=>$category.id_ever_category, 'link_rewrite'=>$category.link_rewrite])|escape:'htmlall':'UTF-8'}" class="category" title="{$category.title|escape:'htmlall':'UTF-8'}">
            {$category.title|escape:'htmlall':'UTF-8'}
        </a>
    </li>
{/if}
{/foreach}
    </ul>
</div>
{/if}
{if isset($showTags) && $showTags && isset($tags) && !empty($tags)}
<div class="columns_everblog_wrapper tag_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Tags from the blog' mod='everpsblog'}</p>
{foreach from=$tags item=tag}
    <a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag.id_ever_tag, 'link_rewrite' => $tag.link_rewrite])|escape:'htmlall':'UTF-8'}" class="tag" title="{$tag.title|escape:'htmlall':'UTF-8'}">
        {$tag.title|escape:'htmlall':'UTF-8'}
    </a>
{/foreach}
</div>
{/if}

{if isset($everpsblog) && $everpsblog}
<div id="latestCarousel" class="columns_everblog_wrapper latests_wrapper carousel slide" data-ride="carousel">
    <p class="text-uppercase h6 hidden-sm-down">
        <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='Latest from the blog' mod='everpsblog'}">
            {l s='Latest from the blog' mod='everpsblog'}
        </a>
    </p>
    <ol class="carousel-indicators">
        {assign var=counter value=0}
        {foreach from=$everpsblog item=item}
        <li data-target="#latestCarousel" data-slide-to="{$counter|escape:'htmlall':'UTF-8'}" {if $counter == 0} class="active"{/if}></li>
        {$counter = $counter+1}
        {/foreach}
    </ol>
    <div class="carousel-inner">
{assign var=postcounter value=1}
{foreach from=$everpsblog item=item}
        <div class="carousel-item {if $postcounter == 1} active{/if} article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}">
                <div class="d-block w-100">
                    <div class="col-12 article-img">
                        <img src="{$item->featured_image|escape:'htmlall':'UTF-8'}" class="col-12 {if $animate}animate flipSideBySide zoomed{/if}" alt="{$item->title|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}" />
                    </div>
                    <div class="col-12 col-12">
                        <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                                {$item->title|escape:'htmlall':'UTF-8'}
                        </h3>
                        <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                            {if isset($item->excerpt) && !empty($item->excerpt)}{$item->excerpt|truncate:90:"..."|escape:'htmlall':'UTF-8'}{else}{$item->content|truncate:90:"..."|escape:'htmlall':'UTF-8'}{/if}
                        </div>
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-blog-primary" title="{l s='Read more' mod='everpsblog'} {$item->title|escape:'htmlall':'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
                    </div>
                </div>
            </a>
        </div>
{$postcounter = $postcounter+1}
{/foreach}
        <a class="carousel-control-prev" href="#latestCarousel" role="button" data-slide="prev" title="{l s='Previous' mod='everpsblog'}">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only"></span>
        </a>
        <a class="carousel-control-next" href="#latestCarousel" role="button" data-slide="next" title="{l s='Next' mod='everpsblog'}">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">{l s='Previous' mod='everpsblog'}</span>
        </a>
    </div>
</div>
{/if}
