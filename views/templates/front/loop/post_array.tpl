{*
 * 2019-2021 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
    <article class="col-12 col-xs-12 article everpsblog" id="everpsblog-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
        <div class="row">
            <div class="col-12 col-xs-12 col-md-4 article-img">
                <img src="{$item.featured_image|escape:'htmlall':'UTF-8'}" class="img-fluid {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" />
            </div>
            <div class="col-12 col-xs-12 col-md-8">
                <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}">
                        {$item.title|escape:'htmlall':'UTF-8'}
                    </a>
                </h3>
                <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item.id_ever_post|escape:'htmlall':'UTF-8'}">
                    {if isset($item.excerpt) && !empty($item.excerpt)}{$item.excerpt|escape:'htmlall':'UTF-8'}{else}{$item.content|escape:'htmlall':'UTF-8'}{/if}
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-blog-primary" title="{$item.title|escape:'htmlall':'UTF-8'} {$shop.name|escape:htmlall:'UTF-8'}">{l s='Read more' mod='everpsblog'}</a>
                </div>
            </div>
        </div>
    </article>