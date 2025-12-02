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
<article class="col-12 col-sm-6 col-md-4 mb-4" id="everpsblog-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
    <div class="card h-100 shadow-sm border-0 everpsblog">
        <div class="article-img text-center mb-0">
            <div class="everpsblog-image-wrapper position-relative overflow-hidden rounded-top" style="aspect-ratio: 16 / 9;">
                {if isset($show_featured_post) && $show_featured_post && isset($item.featured_thumb) && $item.featured_thumb}
                <img src="{$item.featured_thumb|escape:'htmlall':'UTF-8'}" width="320" height="180" class="card-img-top img-fluid w-100 h-100 {if $animated}animated flipSideBySide zoomed{/if}" style="object-fit: cover;" alt="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" />
                {else}
                <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light text-muted">
                    <span class="small fw-semibold">{l s='Image not available' mod='everpsblog'}</span>
                </div>
                {/if}
            </div>
        </div>
        {assign var='mainCategory' value=null}
        {if isset($evercategory) && isset($item.id_default_category)}
            {foreach from=$evercategory item=category}
                {if $category.id_ever_category == $item.id_default_category}
                    {assign var='mainCategory' value=$category}
                    {break}
                {/if}
            {/foreach}
        {/if}
        <div class="everpsblog-meta d-flex flex-wrap align-items-center gap-3 px-3 py-2 border-bottom">
            {if isset($item.date_add) && $item.date_add}
            <span class="d-inline-flex align-items-center text-muted small">
                {$item.date_add|escape:'htmlall':'UTF-8'}
            </span>
            {/if}
            {if $mainCategory}
            <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$mainCategory.id_ever_category, 'link_rewrite'=>$mainCategory.link_rewrite])|escape:'htmlall':'UTF-8'}" class="d-inline-flex align-items-center text-muted small text-decoration-none fw-semibold">
                {$mainCategory.title|escape:'htmlall':'UTF-8'}
            </a>
            {/if}
            {if isset($item.author) && $item.author}
            <span class="d-inline-flex align-items-center text-muted small">
                {l s='By' mod='everpsblog'} {$item.author|escape:'htmlall':'UTF-8'}
            </span>
            {elseif isset($item.author_name) && $item.author_name}
            <span class="d-inline-flex align-items-center text-muted small">
                {l s='By' mod='everpsblog'} {$item.author_name|escape:'htmlall':'UTF-8'}
            </span>
            {/if}
        </div>
        <div class="card-body d-flex flex-column">
            <h3 class="everpsblog article-content h5" id="everpsblog-post-title-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'} text-decoration-none">
                    {$item.title|escape:'htmlall':'UTF-8'}
                </a>
            </h3>
            <div class="everpsblogcontent rte mb-3" id="everpsblog-post-content-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
                {if isset($item.excerpt) && !empty($item.excerpt)}{$item.excerpt|escape:'htmlall':'UTF-8'}{else}{$item.content|escape:'htmlall':'UTF-8'}{/if}
            </div>
            <div class="mt-auto">
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary w-100 {$blogcolor|escape:'htmlall':'UTF-8'} fw-semibold" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Lire la suite...' mod='everpsblog'}</a>
            </div>
        </div>
    </div>
</article>
