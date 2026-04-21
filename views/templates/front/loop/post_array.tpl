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
{assign var='post_id' value=0}
{if isset($item.id) && $item.id}
    {assign var='post_id' value=$item.id}
{elseif isset($item.id_ever_post) && $item.id_ever_post}
    {assign var='post_id' value=$item.id_ever_post}
{/if}
{assign var='post_title' value=''}
{if isset($item.title) && $item.title}
    {assign var='post_title' value=$item.title}
{elseif isset($item.meta_title) && $item.meta_title}
    {assign var='post_title' value=$item.meta_title}
{/if}
{assign var='post_rewrite' value=''}
{if isset($item.link_rewrite) && $item.link_rewrite}
    {assign var='post_rewrite' value=$item.link_rewrite}
{/if}
{assign var='post_summary' value=''}
{if isset($item.summary) && $item.summary}
    {assign var='post_summary' value=$item.summary}
{elseif isset($item.excerpt) && $item.excerpt}
    {assign var='post_summary' value=$item.excerpt}
{elseif isset($item.meta_description) && $item.meta_description}
    {assign var='post_summary' value=$item.meta_description}
{elseif isset($item.content) && $item.content}
    {assign var='post_summary' value=$item.content}
{/if}
{if isset($item.url) && $item.url}
    {assign var='post_link' value=$item.url}
{elseif isset($item.link) && $item.link}
    {assign var='post_link' value=$item.link}
{else}
    {assign var='post_link' value=$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $post_id, 'link_rewrite' => $post_rewrite])}
{/if}
<article class="col-12 mb-4" id="everpsblog-{$post_id|escape:'htmlall':'UTF-8'}">
    <div class="card h-100 shadow-sm border-0 everpsblog everpsblog-listing-card overflow-hidden">
        <div class="row g-0 align-items-stretch">
            <div class="col-12 col-lg-5">
                <div class="article-img text-center mb-0 h-100">
                    <div class="everpsblog-image-wrapper position-relative overflow-hidden w-100 h-100" style="aspect-ratio: 16 / 9;">
                        {if isset($show_featured_post) && $show_featured_post && isset($item.featured_thumb) && $item.featured_thumb}
                        <a href="{$post_link|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" class="d-block h-100">
                            <img src="{$item.featured_thumb|escape:'htmlall':'UTF-8'}" width="320" height="180" class="card-img-top img-fluid w-100 h-100 {if $animated}animated flipSideBySide zoomed{/if}" style="object-fit: cover;" alt="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" />
                        </a>
                        {else}
                        <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light text-muted">
                            <span class="small fw-semibold">{l s='Image not available' mod='everpsblog'}</span>
                        </div>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="card-body d-flex flex-column h-100 p-4">
                    <h2 class="everpsblog article-content h2 mb-3" id="everpsblog-post-title-{$post_id|escape:'htmlall':'UTF-8'}">
                        <a href="{$post_link|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" class="text-dark text-decoration-none">
                            {$post_title|escape:'htmlall':'UTF-8'}
                        </a>
                    </h2>
                    {if isset($item.date_add) && $item.date_add}
                    <p class="small text-muted mb-3">{$item.date_add|escape:'htmlall':'UTF-8'}</p>
                    {/if}
                    <div class="everpsblogcontent rte mb-3 text-body" id="everpsblog-post-content-{$post_id|escape:'htmlall':'UTF-8'}">
                        {$post_summary|strip_tags|truncate:300:'...'|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="mt-auto text-center text-lg-start">
                        <a href="{$post_link|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-blog-primary rounded-pill px-4 fw-semibold" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Lire la suite' mod='everpsblog'} <i class="material-icons" aria-hidden="true">chevron_right</i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>
