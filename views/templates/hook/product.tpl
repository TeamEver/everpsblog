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
 *  @copyright 2019-2020 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<section class="card my-2 py-1">
    <div class="row blogproducttitle">
        <div class="col-12 col-xs-12">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}">
                <h2 class="h2 products-section-title text-uppercase text-center">{l s='Associated posts' mod='everpsblog'}</h2>
            </a>
        </div>
    </div>
    <div class="row blogproduct mt-2">
    {foreach from=$everpsblog item=item}
        <div class="col-xs-12 col-md-3 article everpsblog mb-3" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                <div class="col-xs-12 article-img">
                    <img src="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$item->id_ever_post|escape:'htmlall':'UTF-8'}.jpg" class="img img-fluid {if $animated}animated flipSideBySide zoomed{/if}" title="{$item->title nofilter}" alt="{$item->title nofilter}"/>
                </div>
                <div class="col-xs-12">
                    <h3 class="everpsblog article-content h3 product-title" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title nofilter}">
                            {$item->title nofilter}
                        </a>
                    </h3>
                    <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                        {$item->content|strip_tags nofilter}
                    </div>
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary" title="{$item->title nofilter}">{l s='Read more' mod='everpsblog'}</a>
                </div>
        </div>
    {/foreach}
    </div>
</section>
