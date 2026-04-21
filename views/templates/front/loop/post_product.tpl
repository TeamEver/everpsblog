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
{if isset($item.id_ever_post) && $item.id_ever_post}
    {assign var='post_id' value=$item.id_ever_post}
{elseif isset($item.id) && $item.id}
    {assign var='post_id' value=$item.id}
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
<div class="col-12 col-md-3 article everpsblog mb-3" id="everpsblog-{$post_id|escape:'htmlall':'UTF-8'}">
    <div class="col-12 article-img">
        {if isset($show_featured_post) && $show_featured_post && isset($item.featured_thumb) && $item.featured_thumb}
        <a href="{$post_link|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'}" class="d-block">
            <img src="{$item.featured_thumb|escape:'htmlall':'UTF-8'}" width="320" height="180" class="img img-fluid {if $animated}animated flipSideBySide zoomed{/if}" title="{$post_title|escape:'htmlall':'UTF-8'}" alt="{$post_title|escape:'htmlall':'UTF-8'}"/>
        </a>
        {/if}
    </div>
    <div class="col-12">
        <h3 class="everpsblog article-content h3 product-title" id="everpsblog-post-title-{$post_id|escape:'htmlall':'UTF-8'}">
            <a href="{$post_link|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'} text-dark">
                {$post_title|escape:'htmlall':'UTF-8'}
            </a>
        </h3>
        <div class="everpsblogcontent rte" id="everpsblog-post-content-{$post_id|escape:'htmlall':'UTF-8'}">
            {$post_summary|strip_tags|truncate:300:'...'|escape:'htmlall':'UTF-8'}
        </div>
        <a href="{$post_link|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'}" title="{$post_title|escape:'htmlall':'UTF-8'}">&gt;&gt; {l s='Read more' mod='everpsblog'}</a>
    </div>
</div>
