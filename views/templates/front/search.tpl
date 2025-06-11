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

{extends file='page.tpl'}

{block name='content'}
<h1 class="text-center">{l s='Search results for' mod='everpsblog'} "{$query|escape:'htmlall':'UTF-8'}"</h1>
{if isset($post_number) && $post_number > 0}
<div class="row mt-2">
    {foreach from=$posts item=item}
        {include file='module:everpsblog/views/templates/front/loop/post_array.tpl'}
    {/foreach}
</div>
<div class="row">
    {include file='_partials/pagination.tpl' pagination=$pagination}
</div>
{else}
<div class="alert alert-info">{l s='No post found for this search.' mod='everpsblog'}</div>
{/if}
{/block}
