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
{if isset($categories) && $categories|@count}
<div class="everpsblog-block everpsblog-category-slider">
    {if isset($use_slider) && $use_slider && isset($carousel_id)}
        <div id="{$carousel_id|escape:'htmlall':'UTF-8'}" class="carousel slide" data-ride="carousel" data-interval="false" data-wrap="true">
            <div class="carousel-inner">
                {foreach from=$categories item=category name=categoryslider}
                    <div class="carousel-item {if $smarty.foreach.categoryslider.first}active{/if}">
                        <article class="card h-100">
                            {if isset($category.featured_thumb) && $category.featured_thumb}
                                <img class="card-img-top img-fluid" src="{$category.featured_thumb|escape:'htmlall':'UTF-8'}" alt="{$category.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                            {/if}
                            <div class="card-body">
                                <h3 class="h5 card-title">
                                    <a href="{$category.url|escape:'htmlall':'UTF-8'}" title="{$category.title|escape:'htmlall':'UTF-8'}">{$category.title|escape:'htmlall':'UTF-8'}</a>
                                </h3>
                                {if isset($category.description) && $category.description}
                                    <p class="card-text">{$category.description|escape:'htmlall':'UTF-8'}</p>
                                {/if}
                            </div>
                        </article>
                    </div>
                {/foreach}
            </div>
            <a class="carousel-control-prev" href="#{$carousel_id|escape:'htmlall':'UTF-8'}" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only visually-hidden">{l s='Previous' mod='everpsblog'}</span>
            </a>
            <a class="carousel-control-next" href="#{$carousel_id|escape:'htmlall':'UTF-8'}" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only visually-hidden">{l s='Next' mod='everpsblog'}</span>
            </a>
        </div>
    {else}
        <div class="row">
            {foreach from=$categories item=category}
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <article class="card h-100">
                        {if isset($category.featured_thumb) && $category.featured_thumb}
                            <img class="card-img-top img-fluid" src="{$category.featured_thumb|escape:'htmlall':'UTF-8'}" alt="{$category.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                        {/if}
                        <div class="card-body">
                            <h3 class="h5 card-title">
                                <a href="{$category.url|escape:'htmlall':'UTF-8'}" title="{$category.title|escape:'htmlall':'UTF-8'}">{$category.title|escape:'htmlall':'UTF-8'}</a>
                            </h3>
                            {if isset($category.description) && $category.description}
                                <p class="card-text">{$category.description|escape:'htmlall':'UTF-8'}</p>
                            {/if}
                        </div>
                    </article>
                </div>
            {/foreach}
        </div>
    {/if}
</div>
{/if}
