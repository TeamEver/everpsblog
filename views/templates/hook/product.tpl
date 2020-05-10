{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

<div class="row blogproducttitle mt-2">
    <div class="col-12 col-xs-12">
        <a href="{$blogUrl|escape:'html'}">
            <h2 class="h2 products-section-title text-uppercase text-center">{l s='Associated posts' mod='everpsblog'}</h2>
        </a>
    </div>
</div>
<div class="row blogproduct mt-2">
{foreach from=$everpsblog item=item}
    <div class="col-xs-12 col-md-3 article everpsblog" id="everpsblog-{$item->id_ever_post|escape:'html'}">
            <div class="col-xs-12 article-img">
                <img src="{$blogImg_dir|escape:'html'}posts/post_image_{$item->id_ever_post|escape:'html'}.jpg" class="img img-fluid {if $animated}animated flipSideBySide zoomed{/if}"/>
            </div>
            <div class="col-xs-12">
                <h3 class="everpsblog article-content h3 product-title" id="everpsblog-post-title-{$item->id_ever_post|escape:'html'}">
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'html'}">
                        {$item->title nofilter}
                    </a>
                </h3>
                <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'html'}">
                    {$item->content|strip_tags|truncate:150:"..."}
                </div>
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post , 'link_rewrite' => $item->link_rewrite])|escape:'html'}" class="btn btn-primary">{l s='Read more' mod='everpsblog'}</a>
            </div>
    </div>
{/foreach}
</div>
