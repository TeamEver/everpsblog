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
 *  @category    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if isset($count_products) && $count_products > 0}
{assign var="blpCarouselId" value="linkedProductsCarousel"}
{if isset($linked_products_block_id) && $linked_products_block_id}
  {assign var="blpCarouselId" value="linkedProductsCarousel-"|cat:$linked_products_block_id}
{/if}
<section id="linked-products" class="blog-linked-products container mt-4">
  <p class="h2 text-center mb-3">{l s='Linked products' d='Modules.Everpsblog.Shop'}</p>
  {if $count_products > 4 && isset($ps_products_chunks) && $ps_products_chunks}
    <div id="{$blpCarouselId|escape:'htmlall':'UTF-8'}" class="carousel slide blog-linked-products-carousel position-relative" data-bs-ride="false" data-bs-interval="false">
      <div class="carousel-inner">
        {foreach from=$ps_products_chunks item="slide" name="blpSlides"}
          <div class="carousel-item{if $smarty.foreach.blpSlides.first} active{/if}">
            <div class="row">
              {foreach from=$slide item="product"}
                <div class="col-12 col-sm-6 col-lg-3 mb-3 d-flex">
                  {include file="catalog/_partials/miniatures/product.tpl" product=$product}
                </div>
              {/foreach}
            </div>
          </div>
        {/foreach}
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#{$blpCarouselId|escape:'htmlall':'UTF-8'}" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">{l s='Previous' d='Shop.Theme.Actions'}</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#{$blpCarouselId|escape:'htmlall':'UTF-8'}" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">{l s='Next' d='Shop.Theme.Actions'}</span>
      </button>
      <div class="carousel-indicators position-relative mt-3 mx-0">
        {foreach from=$ps_products_chunks item="slide" name="blpInd"}
          <button type="button" data-bs-target="#{$blpCarouselId|escape:'htmlall':'UTF-8'}" data-bs-slide-to="{$smarty.foreach.blpInd.index|escape:'htmlall':'UTF-8'}"{if $smarty.foreach.blpInd.first} class="active" aria-current="true"{/if} aria-label="{l s='Slide' d='Modules.Everpsblog.Shop'} {$smarty.foreach.blpInd.iteration|escape:'htmlall':'UTF-8'}"></button>
        {/foreach}
      </div>
    </div>
  {else}
    <div class="row">
      {foreach from=$ps_products item="product"}
        <div class="col-12 col-sm-6 col-lg-3 mb-3 d-flex">
          {include file="catalog/_partials/miniatures/product.tpl" product=$product}
        </div>
      {/foreach}
    </div>
  {/if}
</section>
{/if}
