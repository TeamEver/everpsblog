{*
 * 2019-2020 Team Ever
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

<div class="row bloghometitle">
    <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='Latest posts from the blog' mod='everpsblog'}">
        <h2 class="h2 products-section-title text-uppercase text-center">{l s='Latest posts from the blog' mod='everpsblog'}</h2>
    </a>
</div>
<div class="row bloghome carousel slide mt-2">
    <div class="carousel-inner">
    {assign var=counter value=0}
    {foreach from=$everpsblog item=item}
        <div class="col-12 col-xs-12 col-md-3 article everpsblog{if $counter == 0} active{/if}" data-slide-to="{$counter|escape:'htmlall':'UTF-8'}" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                <div class="col-12 col-xs-12 article-img">
                    <img src="{$item->featured_image|escape:'htmlall':'UTF-8'}" class="img-fluid col-12 col-xs-12 {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item->title|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}" />
                </div>
                <div class="col-12 col-xs-12">
                    <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'}">
                            {$item->title|strip_tags nofilter}
                        </a>
                    </h3>
                    <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                        {$item->content|strip_tags nofilter}
                    </div>
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary" title="{l s='Read more' mod='everpsblog'} {$item->title|escape:'htmlall':'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
                </div>
        </div>
    {$counter=$counter+1}
    {/foreach}
    </div>
</div>
