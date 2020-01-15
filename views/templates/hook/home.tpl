{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

<div class="container bloghometitle">
    <a href="{$blogUrl|escape:'html'}" title="{l s='Latest posts from the blog' mod='everpsblog'}">
        <h2 class="h2 products-section-title text-uppercase text-center">{l s='Latest posts from the blog' mod='everpsblog'}</h2>
    </a>
</div>
<div class="container bloghome carousel slide">
    <div class="carousel-inner">
    {assign var=counter value=0}
    {foreach from=$everpsblog item=item}
        <div class="col-xs-12 col-md-3 article everpsblog{if $counter == 0} active{/if}" data-slide-to="{$counter}" id="everpsblog-{$item.id_ever_post|escape:'html'}">
                <div class="col-xs-12 article-img">
                    <img src="{$blogImg_dir|escape:'html'}posts/post_image_{$item.id_ever_post|escape:'html'}.jpg" class="img-fluid col-xs-12 {if $animated}animated flipSideBySide zoomed{/if}" alt="{$item.title nofilter}" />
                </div>
                <div class="col-xs-12">
                    <h3 class="everpsblog article-content" id="everpsblog-post-title-{$item.id_ever_post|escape:'html'}">
                        <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'html'}" title="{$item.title nofilter}">
                            {$item.title nofilter}
                        </a>
                    </h3>
                    <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item.id_ever_post|escape:'html'}">
                        {$item.content|strip_tags|truncate:150:"..."}
                    </div>
                    <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item.id_ever_post , 'link_rewrite' => $item.link_rewrite])|escape:'html'}" class="btn btn-primary" title="{l s='Read more' mod='everpsblog'} {$item.title nofilter}">{l s='Read more' mod='everpsblog'}</a>
                </div>
        </div>
    {$counter=$counter+1}
    {/foreach}
    </div>
</div>
