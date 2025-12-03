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
<div class="everpsblog-block everpsblog-post-slider">
    {if isset($use_slider) && $use_slider && isset($carousel_id)}
        <div id="{$carousel_id|escape:'htmlall':'UTF-8'}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false" data-bs-wrap="true">
            <div class="carousel-inner">
                {foreach from=$block.extra.states item=post name=postslider}
                {assign var="post" value=$post.post}
                    <div class="carousel-item {if $smarty.foreach.postslider.first}active{/if}">
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
            <a class="carousel-control-prev" role="button" data-bs-target="#{$carousel_id|escape:'htmlall':'UTF-8'}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only visually-hidden">{l s='Previous' mod='everpsblog'}</span>
            </a>
            <a class="carousel-control-next" role="button" data-bs-target="#{$carousel_id|escape:'htmlall':'UTF-8'}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only visually-hidden">{l s='Next' mod='everpsblog'}</span>
            </a>
        </div>
    {else}
        <div class="row">
            {foreach from=$block.extra.states item=post}
            {assign var="post" value=$post.post}
                <div class="col-12 col-md-6 col-lg-4 mb-3">
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
{/if}
