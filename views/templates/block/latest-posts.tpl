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
{if isset($block.extra.states) && $block.extra.states|@count}
{assign var="items_per_slide_mobile" value=$block.settings.items_per_slide_mobile|default:1}
{assign var="items_per_slide_desktop" value=$block.settings.items_per_slide_desktop|default:2}
{if $items_per_slide_mobile < 1}
    {assign var="items_per_slide_mobile" value=1}
{/if}
{if $items_per_slide_desktop < 1}
    {assign var="items_per_slide_desktop" value=1}
{/if}
{assign var="col_mobile" value="col-12"}
{assign var="col_desktop" value="col-md-6"}
{if $items_per_slide_mobile == 2}
    {assign var="col_mobile" value="col-6"}
{elseif $items_per_slide_mobile == 3}
    {assign var="col_mobile" value="col-4"}
{elseif $items_per_slide_mobile >= 4}
    {assign var="col_mobile" value="col-3"}
{/if}
{if $items_per_slide_desktop == 1}
    {assign var="col_desktop" value="col-md-12"}
{elseif $items_per_slide_desktop == 2}
    {assign var="col_desktop" value="col-md-6"}
{elseif $items_per_slide_desktop == 3}
    {assign var="col_desktop" value="col-md-4"}
{elseif $items_per_slide_desktop >= 4}
    {assign var="col_desktop" value="col-md-3"}
{/if}
<section class="everpsblog-block everpsblog-latest-posts">
    <div class="row align-items-start">
        <div class="col-12 col-lg-4 mb-4 mb-lg-0">
            <div class="everpsblog-latest-posts__intro">
                <h2 class="everpsblog-latest-posts__title">{l s='Le blog' mod='everpsblog'}</h2>
                {if isset($block.extra.blog_url) && $block.extra.blog_url}
                    <a class="btn btn-outline-primary everpsblog-latest-posts__cta" href="{$block.extra.blog_url|escape:'htmlall':'UTF-8'}">
                        {l s='Voir tous nos articles' mod='everpsblog'}
                    </a>
                {/if}
            </div>
        </div>
        <div class="col-12 col-lg-8">
            {if isset($block.settings.bootstrap_slider) && $block.settings.bootstrap_slider}
                {assign var=carousel_id_mobile value='everpsblog-latest-posts-mobile-'|cat:$block.id_prettyblocks}
                {assign var=carousel_id_desktop value='everpsblog-latest-posts-desktop-'|cat:$block.id_prettyblocks}
                <div class="d-md-none">
                    <div id="{$carousel_id_mobile|escape:'htmlall':'UTF-8'}" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-wrap="true">
                        <div class="carousel-inner">
                            {foreach from=$block.extra.states item=state name=latestpostsmobile}
                                {assign var="post" value=$state.post}
                                {if $smarty.foreach.latestpostsmobile.index % $items_per_slide_mobile == 0}
                                <div class="carousel-item {if $smarty.foreach.latestpostsmobile.first}active{/if}">
                                    <div class="row">
                                {/if}
                                        <div class="{$col_mobile} mb-3">
                                            <article class="card h-100">
                                                {if isset($post.featured_thumb) && $post.featured_thumb}
                                                    <img class="card-img-top img-fluid" src="{$post.featured_thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                                                {/if}
                                                <div class="card-body">
                                                    <h3 class="h5 card-title">
                                                        <a href="{$post.url|escape:'htmlall':'UTF-8'}" title="{$post.title|escape:'htmlall':'UTF-8'}">{$post.title|escape:'htmlall':'UTF-8'}</a>
                                                    </h3>
                                                    {if isset($post.category) && $post.category}
                                                        <div class="mb-2">
                                                            <a class="text-muted" href="{$post.category.url|escape:'htmlall':'UTF-8'}" title="{$post.category.title|escape:'htmlall':'UTF-8'}">{$post.category.title|escape:'htmlall':'UTF-8'}</a>
                                                        </div>
                                                    {/if}
                                                    {if isset($post.excerpt) && $post.excerpt}
                                                        <p class="card-text">{$post.excerpt|escape:'htmlall':'UTF-8'}</p>
                                                    {/if}
                                                </div>
                                            </article>
                                        </div>
                                {if ($smarty.foreach.latestpostsmobile.index+1) % $items_per_slide_mobile == 0 || $smarty.foreach.latestpostsmobile.last}
                                    </div>
                                </div>
                                {/if}
                            {/foreach}
                        </div>
                        <a class="carousel-control-prev" role="button" data-bs-target="#{$carousel_id_mobile|escape:'htmlall':'UTF-8'}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only visually-hidden">{l s='Previous' mod='everpsblog'}</span>
                        </a>
                        <a class="carousel-control-next" role="button" data-bs-target="#{$carousel_id_mobile|escape:'htmlall':'UTF-8'}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only visually-hidden">{l s='Next' mod='everpsblog'}</span>
                        </a>
                    </div>
                </div>
                <div class="d-none d-md-block">
                    <div id="{$carousel_id_desktop|escape:'htmlall':'UTF-8'}" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-wrap="true">
                        <div class="carousel-inner">
                            {foreach from=$block.extra.states item=state name=latestpostsdesktop}
                                {assign var="post" value=$state.post}
                                {if $smarty.foreach.latestpostsdesktop.index % $items_per_slide_desktop == 0}
                                <div class="carousel-item {if $smarty.foreach.latestpostsdesktop.first}active{/if}">
                                    <div class="row">
                                {/if}
                                        <div class="{$col_mobile} {$col_desktop} mb-3">
                                            <article class="card h-100">
                                                {if isset($post.featured_thumb) && $post.featured_thumb}
                                                    <img class="card-img-top img-fluid" src="{$post.featured_thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                                                {/if}
                                                <div class="card-body">
                                                    <h3 class="h5 card-title">
                                                        <a href="{$post.url|escape:'htmlall':'UTF-8'}" title="{$post.title|escape:'htmlall':'UTF-8'}">{$post.title|escape:'htmlall':'UTF-8'}</a>
                                                    </h3>
                                                    {if isset($post.category) && $post.category}
                                                        <div class="mb-2">
                                                            <a class="text-muted" href="{$post.category.url|escape:'htmlall':'UTF-8'}" title="{$post.category.title|escape:'htmlall':'UTF-8'}">{$post.category.title|escape:'htmlall':'UTF-8'}</a>
                                                        </div>
                                                    {/if}
                                                    {if isset($post.excerpt) && $post.excerpt}
                                                        <p class="card-text">{$post.excerpt|escape:'htmlall':'UTF-8'}</p>
                                                    {/if}
                                                </div>
                                            </article>
                                        </div>
                                {if ($smarty.foreach.latestpostsdesktop.index+1) % $items_per_slide_desktop == 0 || $smarty.foreach.latestpostsdesktop.last}
                                    </div>
                                </div>
                                {/if}
                            {/foreach}
                        </div>
                        <a class="carousel-control-prev" role="button" data-bs-target="#{$carousel_id_desktop|escape:'htmlall':'UTF-8'}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only visually-hidden">{l s='Previous' mod='everpsblog'}</span>
                        </a>
                        <a class="carousel-control-next" role="button" data-bs-target="#{$carousel_id_desktop|escape:'htmlall':'UTF-8'}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only visually-hidden">{l s='Next' mod='everpsblog'}</span>
                        </a>
                    </div>
                </div>
            {else}
                <div class="row">
                    {foreach from=$block.extra.states item=state}
                        {assign var="post" value=$state.post}
                        <div class="{$col_mobile} {$col_desktop} mb-3">
                            <article class="card h-100">
                                {if isset($post.featured_thumb) && $post.featured_thumb}
                                    <img class="card-img-top img-fluid" src="{$post.featured_thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                                {/if}
                                <div class="card-body">
                                    <h3 class="h5 card-title">
                                        <a href="{$post.url|escape:'htmlall':'UTF-8'}" title="{$post.title|escape:'htmlall':'UTF-8'}">{$post.title|escape:'htmlall':'UTF-8'}</a>
                                    </h3>
                                    {if isset($post.category) && $post.category}
                                        <div class="mb-2">
                                            <a class="text-muted" href="{$post.category.url|escape:'htmlall':'UTF-8'}" title="{$post.category.title|escape:'htmlall':'UTF-8'}">{$post.category.title|escape:'htmlall':'UTF-8'}</a>
                                        </div>
                                    {/if}
                                    {if isset($post.excerpt) && $post.excerpt}
                                        <p class="card-text">{$post.excerpt|escape:'htmlall':'UTF-8'}</p>
                                    {/if}
                                </div>
                            </article>
                        </div>
                    {/foreach}
                </div>
            {/if}
        </div>
    </div>
</section>
{/if}
