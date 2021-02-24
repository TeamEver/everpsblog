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

<section class="card my-2 py-1">
    <div class="row blogproducttitle">
        <div class="col-12 col-xs-12">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}">
                <h2 class="h2 products-section-title text-uppercase text-center {$blogcolor|escape:'htmlall':'UTF-8'}">{l s='Associated posts' mod='everpsblog'}</h2>
            </a>
        </div>
    </div>
    <div class="row blogproduct mt-2">
    {foreach from=$everpsblog item=item}
    {include file='module:everpsblog/views/templates/front/loop/post_product.tpl'}
    {/foreach}
    </div>
</section>
