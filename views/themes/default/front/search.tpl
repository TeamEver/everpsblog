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
<div class="everpsblog-blog-header container-fluid px-0 mb-4">
    <div class="everpsblog-blog-header__inner text-center py-5">
        <h1 class="m-0 everpsblog-blog-header__title">{l s='Search results for' d='Modules.Everpsblog.Shop'} "{$query|escape:'htmlall':'UTF-8'}"</h1>
    </div>
</div>
<div class="container my-4">
    {include file="{$everpsblog_theme_front_template_base}/loop/search_form.tpl"}
    {if isset($post_number) && $post_number > 0}
    <div class="row mt-2">
        {foreach from=$posts item=item}
            {include file="{$everpsblog_theme_front_template_base}/loop/post_array.tpl"}
        {/foreach}
    </div>
    {if isset($pagination.should_be_displayed) && $pagination.should_be_displayed}
    <div class="row">
        {include file='_partials/pagination.tpl' pagination=$pagination}
    </div>
    {/if}
    {else}
    <div class="alert alert-info">{l s='No post found for this search.' d='Modules.Everpsblog.Shop'}</div>
    {/if}
</div>
{/block}
