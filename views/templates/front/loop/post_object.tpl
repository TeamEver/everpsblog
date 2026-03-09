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
<article class="col-12 mb-4 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
    <div class="card h-100 shadow-sm border-0 everpsblog everpsblog-listing-card overflow-hidden">
        <div class="row g-0 h-100 align-items-stretch">
            <div class="col-12 col-lg-6">
                <div class="article-img text-center mb-0 h-100">
                    <div class="everpsblog-image-wrapper position-relative overflow-hidden h-100" style="aspect-ratio: 16 / 9;">
                        {if isset($show_featured_post) && $show_featured_post}
                        <img src="{$item->featured_thumb|escape:'htmlall':'UTF-8'}" width="320" height="180" class="img-fluid w-100 h-100 mx-auto d-block {if $animated}animated flipSideBySide zoomed{/if}" style="object-fit: cover;" alt="{$item->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" />
                        {/if}
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card-body d-flex flex-column h-100 p-4">
                    <p class="everpsblog article-content h2 mb-3" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$item->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'} text-dark text-decoration-none">
                            {$item->title|escape:'htmlall':'UTF-8'}
                        </a>
                    </p>
                    {if isset($item->date_add) && $item->date_add}
                    <p class="small fw-bold text-primary mb-3">{$item->date_add|escape:'htmlall':'UTF-8'}</p>
                    {/if}
                    <div class="everpsblogcontent rte mb-3" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">{if isset($item->excerpt) && !empty($item->excerpt)}{$item->excerpt|escape:'htmlall':'UTF-8'}{else}{$item->content|escape:'htmlall':'UTF-8'}{/if}</div>
                    <div class="mt-auto">
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary rounded-pill px-4 {$blogcolor|escape:'htmlall':'UTF-8'} fw-semibold" title="{$item->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{l s='Lire la suite' mod='everpsblog'} <i class="material-icons" aria-hidden="true">chevron_right</i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>
