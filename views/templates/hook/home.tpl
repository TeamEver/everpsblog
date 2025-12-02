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
{if isset($everpsblog) && $everpsblog|@count}
    <div class="bloghome">
        <div class="row bloghometitle">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='Latest posts from the blog' mod='everpsblog'}">
                <h2 class="h2 products-section-title text-uppercase text-center">
                    {l s='Latest posts from the blog' mod='everpsblog'}
                </h2>
            </a>
        </div>
        {if $everpsblog|@count > 1}
            {assign var=carousel_id value='everpsblog-home-slider-'|cat:uniqid()}
            <div id="{$carousel_id|escape:'htmlall':'UTF-8'}" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-wrap="true">
                <div class="carousel-inner">
                    {foreach from=$everpsblog item=item name=homecarousel}
                        {if $smarty.foreach.homecarousel.index % 4 == 0}
                            <div class="carousel-item {if $smarty.foreach.homecarousel.first}active{/if}">
                                <div class="row">
                        {/if}
                                    <div class="col-12 col-sm-6 col-lg-3 mb-3 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                                        <div class="col-12 article-img {$blogcolor|escape:'htmlall':'UTF-8'}">
                                            {if isset($show_featured_post) && $show_featured_post}
                                                <img src="{$item->featured_thumb|escape:'htmlall':'UTF-8'}" width="320" height="180" class="img-fluid col-12 w-100 {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}" />
                                            {/if}
                                        </div>
                                        <div class="col-12">
                                            <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                                                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'}">
                                                    {$item->title|escape:'htmlall':'UTF-8'}
                                                </a>
                                            </h3>
                                            {if isset($item->default_cat_obj) && $item->default_cat_obj}
                                                <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$item->default_cat_obj->id_ever_category, 'link_rewrite'=>$item->default_cat_obj->link_rewrite])|escape:'htmlall':'UTF-8'}" class="d-block {$blogcolor|escape:'htmlall':'UTF-8'}" title="{$item->default_cat_obj->title|escape:'htmlall':'UTF-8'}">
                                                    {$item->default_cat_obj->title|escape:'htmlall':'UTF-8'}
                                                </a>
                                            {/if}
                                            <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                                                {if isset($item->excerpt) && !empty($item->excerpt)}{$item->excerpt|escape:'htmlall':'UTF-8'}{else}{$item->content|escape:'htmlall':'UTF-8'}{/if}
                                            </div>
                                            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'}" title="{l s='Read more' mod='everpsblog'} {$item->title|escape:'htmlall':'UTF-8'}">&gt;&gt; {l s='Read more' mod='everpsblog'}</a>
                                        </div>
                                    </div>
                        {if $smarty.foreach.homecarousel.index % 4 == 3 || $smarty.foreach.homecarousel.last}
                                </div>
                            </div>
                        {/if}
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
            <div class="bloghome mt-2 row">
                {foreach from=$everpsblog item=item}
                    <div class="col-12 col-sm-6 col-lg-3 mb-3 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                        <div class="col-12 article-img {$blogcolor|escape:'htmlall':'UTF-8'}">
                            {if isset($show_featured_post) && $show_featured_post}
                                <img src="{$item->featured_thumb|escape:'htmlall':'UTF-8'}" width="320" height="180" class="img-fluid col-12 w-100 {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}" />
                            {/if}
                        </div>
                        <div class="col-12">
                            <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'}">
                                    {$item->title|escape:'htmlall':'UTF-8'}
                                </a>
                            </h3>
                            {if isset($item->default_cat_obj) && $item->default_cat_obj}
                                <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$item->default_cat_obj->id_ever_category, 'link_rewrite'=>$item->default_cat_obj->link_rewrite])|escape:'htmlall':'UTF-8'}" class="d-block {$blogcolor|escape:'htmlall':'UTF-8'}" title="{$item->default_cat_obj->title|escape:'htmlall':'UTF-8'}">
                                    {$item->default_cat_obj->title|escape:'htmlall':'UTF-8'}
                                </a>
                            {/if}
                            <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                                {if isset($item->excerpt) && !empty($item->excerpt)}{$item->excerpt|escape:'htmlall':'UTF-8'}{else}{$item->content|escape:'htmlall':'UTF-8'}{/if}
                            </div>
                            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'}" title="{l s='Read more' mod='everpsblog'} {$item->title|escape:'htmlall':'UTF-8'}">&gt;&gt; {l s='Read more' mod='everpsblog'}</a>
                        </div>
                    </div>
                {/foreach}
            </div>
        {/if}
        <div class="text-center">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='See all posts from the blog' mod='everpsblog'}" class="btn btn-info text-white">{l s='See all posts from the blog' mod='everpsblog'}</a>
        </div>
    </div>
{/if}
