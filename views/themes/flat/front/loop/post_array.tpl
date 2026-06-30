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
{if isset($item.excerpt) && $item.excerpt}
    {assign var='post_summary' value=$item.excerpt}
{elseif isset($item.summary) && $item.summary}
    {assign var='post_summary' value=$item.summary}
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
<article class="flat-post-card everpsblog" id="everpsblog-{$post_id|escape:'htmlall':'UTF-8'}">
    <a class="flat-post-card__image" href="{$post_link|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
        {if isset($show_featured_post) && $show_featured_post && isset($item.featured_thumb) && $item.featured_thumb}
        <img src="{$item.featured_thumb|escape:'htmlall':'UTF-8'}" width="260" height="190" class="{if $animated}animated flipSideBySide zoomed{/if}" alt="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" loading="lazy" />
        {else}
        <span class="flat-post-card__placeholder">{l s='Image not available' d='Modules.Everpsblog.Shop'}</span>
        {/if}
    </a>
    <div class="flat-post-card__body">
        <p class="flat-post-card__title h3" id="everpsblog-post-title-{$post_id|escape:'htmlall':'UTF-8'}">
            <a href="{$post_link|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
                {$post_title|escape:'htmlall':'UTF-8'}
            </a>
        </p>
        {if !isset($everpsblog_excerpt_length) || !$everpsblog_excerpt_length}
            {assign var='everpsblog_excerpt_length' value=300}
        {/if}
        {if $post_summary}
            <div class="flat-post-card__excerpt everpsblogcontent rte" id="everpsblog-post-content-{$post_id|escape:'htmlall':'UTF-8'}">
                {$post_summary|truncate:$everpsblog_excerpt_length:'...' nofilter}
            </div>
        {/if}
        <a href="{$post_link|escape:'htmlall':'UTF-8'}" class="flat-post-card__link" title="{$post_title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s="Lire l'article" d='Modules.Everpsblog.Shop'} &gt;</a>
    </div>
</article>
