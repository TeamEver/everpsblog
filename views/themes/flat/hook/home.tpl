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
{if isset($everpsblog) && $everpsblog|@count}
    {if !isset($everpsblog_excerpt_length) || !$everpsblog_excerpt_length}
        {assign var='everpsblog_excerpt_length' value=300}
    {/if}
    <div class="bloghome container">
        <div class="row bloghometitle">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='Latest posts from the blog' d='Modules.Everpsblog.Shop'}">
                <p class="h2 products-section-title text-uppercase text-center">
                    {l s='Latest posts from the blog' d='Modules.Everpsblog.Shop'}
                </p>
            </a>
        </div>
        <div class="flat-post-grid flat-post-grid--home">
            {foreach from=$everpsblog item=item}
                {include file="{$everpsblog_theme_front_template_base}/loop/post_array.tpl"}
            {/foreach}
        </div>
        <div class="text-center">
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}" title="{l s='See all posts from the blog' d='Modules.Everpsblog.Shop'}" class="btn btn-primary text-white">{l s='See all posts from the blog' d='Modules.Everpsblog.Shop'}</a>
        </div>
    </div>
{/if}
