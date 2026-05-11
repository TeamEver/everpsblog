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
    <div class="bloghome container">
        <div class="row bloghometitle">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='Latest posts from the blog' d='Modules.Everpsblog.Shop'}">
                <h2 class="h2 products-section-title text-uppercase text-center">
                    {l s='Latest posts from the blog' d='Modules.Everpsblog.Shop'}
                </h2>
            </a>
        </div>
        {if $everpsblog|@count > 1}
            <div id="{$carousel_id|escape:'htmlall':'UTF-8'}" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-wrap="true">
                <div class="carousel-inner">
                    {foreach from=$everpsblog item=item name=homecarousel}
                        {if $smarty.foreach.homecarousel.index % 4 == 0}
                            <div class="carousel-item {if $smarty.foreach.homecarousel.first}active{/if}">
                                <div class="row">
                        {/if}
                                    {include file="{$everpsblog_theme_front_template_base}/loop/post_product.tpl"}
                        {if $smarty.foreach.homecarousel.index % 4 == 3 || $smarty.foreach.homecarousel.last}
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <a class="carousel-control-prev" role="button" data-bs-target="#{$carousel_id|escape:'htmlall':'UTF-8'}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">{l s='Previous' d='Modules.Everpsblog.Shop'}</span>
                </a>
                <a class="carousel-control-next" role="button" data-bs-target="#{$carousel_id|escape:'htmlall':'UTF-8'}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">{l s='Next' d='Modules.Everpsblog.Shop'}</span>
                </a>
            </div>
        {else}
            <div class="bloghome Container mt-2 row">
                {foreach from=$everpsblog item=item}
                    {include file="{$everpsblog_theme_front_template_base}/loop/post_product.tpl"}
                {/foreach}
            </div>
        {/if}
        <div class="text-center">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='See all posts from the blog' d='Modules.Everpsblog.Shop'}" class="btn btn-primary text-white">{l s='See all posts from the blog' d='Modules.Everpsblog.Shop'}</a>
        </div>
    </div>
{/if}
