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
<article class="flat-category-card evercategory everpsblog" id="everpsblog-{$item.id_ever_category|escape:'htmlall':'UTF-8'}">
    <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$item.id_ever_category, 'link_rewrite'=>$item.link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'}">
        {if isset($show_featured_cat) && $show_featured_cat && isset($item.featured_image) && $item.featured_image}
        <img src="{$item.featured_image|escape:'htmlall':'UTF-8'}" class="category-featured-image featured-image" alt="{$item.title|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'}" loading="lazy" />
        {else}
        <span class="flat-category-card__placeholder"></span>
        {/if}
        <span class="flat-category-card__title" id="everpsblog-post-title-{$item.id_ever_category|escape:'htmlall':'UTF-8'}">{$item.title|escape:'htmlall':'UTF-8'}</span>
    </a>
</article>
