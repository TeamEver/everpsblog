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
{if isset($showArchives) && $showArchives}
{/if}

{if isset($showCategories) && $showCategories && isset($categories) && !empty($categories)}
<div class="columns_everblog_wrapper category_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Categories from the blog' mod='everpsblog'}</p>
    {foreach from=$categories item=category}
    {if $category.is_root_category == 0}
        <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category' => $category.id_ever_category, 'link_rewrite' => $category.link_rewrite])|escape:'htmlall':'UTF-8'}" class="category d-block" title="{$category.title|escape:'htmlall':'UTF-8'}">
            {$category.title|escape:'htmlall':'UTF-8'}
        </a>
    {/if}
    {/foreach}
</div>
{/if}

{if isset($page.page_name) && $page.page_name == 'module-everpsblog-post'
    && isset($ps_products) && $ps_products}
<div class="columns_everblog_wrapper products_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Linked products' mod='everpsblog'}</p>
    <div class="products">
        {foreach from=$ps_products item="product"}
            {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses="col-12"}
        {/foreach}
    </div>
</div>
{/if}
{if isset($showTags) && $showTags && isset($tags) && !empty($tags)}
<div class="columns_everblog_wrapper tag_wrapper">
    <p class="text-uppercase h6 hidden-sm-down">{l s='Tags from the blog' mod='everpsblog'}</p>
{foreach from=$tags item=tag}
    <a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag.id_ever_tag, 'link_rewrite' => $tag.link_rewrite])|escape:'htmlall':'UTF-8'}" class="badge badge-info m-1 tag" title="{$tag.title|escape:'htmlall':'UTF-8'}">
        {$tag.title|escape:'htmlall':'UTF-8'}
    </a>
{/foreach}
</div>
{/if}


