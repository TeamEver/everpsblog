{*
 * 2019-2025 Team Ever
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<section class="everpsblog-qcd-block everpsblog-qcd-posts-by-tag">
    <header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
        <h2 class="everpsblog-qcd-block__title h4 mb-2 mb-md-0">
            {if !empty($attributes.title)}
                {$attributes.title|escape:'htmlall':'UTF-8'}
            {elseif !empty($attributes.tag.title)}
                {l s='Tag posts' d='Modules.Everpsblog.Shop'} "{$attributes.tag.title|escape:'htmlall':'UTF-8'}"
            {else}
                {l s='Posts linked to this tag' d='Modules.Everpsblog.Shop'}
            {/if}
        </h2>
        {if !empty($attributes.tag.url)}
            <a class="btn btn-sm btn-outline-primary" href="{$attributes.tag.url|escape:'htmlall':'UTF-8'}">{l s='View tag' d='Modules.Everpsblog.Shop'}</a>
        {/if}
    </header>
    {if !empty($attributes.posts)}
        <div class="row">
            {foreach from=$attributes.posts item=post}
                <article class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        {if isset($attributes.show_image) && $attributes.show_image && !empty($post.thumb)}
                            <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none">
                                <img src="{$post.thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" class="card-img-top" loading="lazy">
                            </a>
                        {/if}
                        <div class="card-body">
                            <h3 class="card-title h6 mb-2">
                                <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none">{$post.title|escape:'htmlall':'UTF-8'}</a>
                            </h3>
                            {if !empty($post.excerpt)}
                                <p class="card-text text-muted small mb-0">{$post.excerpt|strip_tags|truncate:140:'…'|escape:'htmlall':'UTF-8'}</p>
                            {/if}
                        </div>
                    </div>
                </article>
            {/foreach}
        </div>
    {else}
        <p class="text-muted mb-0">{l s='No post for this tag.' d='Modules.Everpsblog.Shop'}</p>
    {/if}
</section>
