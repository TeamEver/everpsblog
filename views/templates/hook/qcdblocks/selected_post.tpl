{*
 * 2019-2025 Team Ever
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<section class="everpsblog-qcd-block everpsblog-qcd-selected-post">
    {if !empty($attributes.post)}
        {assign var="post" value=$attributes.post}
        <article class="card shadow-sm border-0 mb-3">
            <div class="row g-0">
                {if isset($attributes.show_image) && $attributes.show_image && !empty($post.thumb)}
                    <div class="col-md-5">
                        <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="d-block h-100">
                            <img src="{$post.thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" class="img-fluid rounded-start h-100 w-100" style="object-fit:cover;" loading="lazy">
                        </a>
                    </div>
                    <div class="col-md-7">
                {else}
                    <div class="col-12">
                {/if}
                    <div class="card-body h-100 d-flex flex-column">
                        <h3 class="card-title h4 mb-2">
                            <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none">{$post.title|escape:'htmlall':'UTF-8'}</a>
                        </h3>
                        {if isset($attributes.show_excerpt) && $attributes.show_excerpt && !empty($post.excerpt)}
                            <p class="card-text text-muted">{$post.excerpt|strip_tags|truncate:260:'…'|escape:'htmlall':'UTF-8'}</p>
                        {/if}
                        <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="btn btn-outline-primary mt-auto align-self-start">{l s='Lire l\'article' mod='everpsblog'}</a>
                    </div>
                </div>
            </div>
        </article>
    {else}
        <p class="text-muted mb-0">{l s='Aucun article sélectionné.' mod='everpsblog'}</p>
    {/if}
</section>
