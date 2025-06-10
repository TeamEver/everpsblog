<div class="everpsblog-shortcode row">
    {foreach from=$posts item=item}
    <div class="col-12 col-md-4 article everpsblog mb-3" id="everpsblog-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
        <div class="col-12 article-img">
            <img src="{$item->featured_image|escape:'htmlall':'UTF-8'}" class="img img-fluid" alt="{$item->title|escape:'htmlall':'UTF-8'}" />
        </div>
        <div class="col-12">
            <h3 class="everpsblog article-content h3" id="everpsblog-post-title-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $item->id_ever_post, 'link_rewrite' => $item->link_rewrite])|escape:'htmlall':'UTF-8'}" class="{$blogcolor|escape:'htmlall':'UTF-8'}">
                    {$item->title|escape:'htmlall':'UTF-8'}
                </a>
            </h3>
            <div class="everpsblogcontent rte" id="everpsblog-post-content-{$item->id_ever_post|escape:'htmlall':'UTF-8'}">
                {if isset($item->excerpt) && !empty($item->excerpt)}{$item->excerpt|escape:'htmlall':'UTF-8'}{else}{$item->content|escape:'htmlall':'UTF-8'}{/if}
            </div>
        </div>
    </div>
    {/foreach}
</div>
