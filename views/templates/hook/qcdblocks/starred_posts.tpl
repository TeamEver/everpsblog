{*
 * 2019-2025 Team Ever
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<section class="everpsblog-qcd-block everpsblog-qcd-starred-posts">
    {if !empty($attributes.title)}
        <h2 class="everpsblog-qcd-block__title h4 mb-3">
            <span class="me-1" aria-hidden="true">★</span>
            {$attributes.title|escape:'htmlall':'UTF-8'}
        </h2>
    {/if}
    {if !empty($attributes.posts)}
        <div class="row">
            {foreach from=$attributes.posts item=post}
                <article class="col-12 col-sm-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm border-0 everpsblog-qcd-starred-card">
                        {if isset($attributes.show_image) && $attributes.show_image && !empty($post.thumb)}
                            <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none position-relative">
                                <img src="{$post.thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" class="card-img-top" loading="lazy">
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">{l s='Featured' d='Modules.Everpsblog.Shop'}</span>
                            </a>
                        {/if}
                        <div class="card-body">
                            <h3 class="card-title h6 mb-2">
                                <a href="{$post.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none">{$post.title|escape:'htmlall':'UTF-8'}</a>
                            </h3>
                            {if isset($attributes.show_excerpt) && $attributes.show_excerpt && !empty($post.excerpt)}
                                <p class="card-text text-muted small mb-0">{$post.excerpt|strip_tags|truncate:120:'…'|escape:'htmlall':'UTF-8'}</p>
                            {/if}
                        </div>
                    </div>
                </article>
            {/foreach}
        </div>
    {else}
        <p class="text-muted mb-0">{l s='No featured post.' d='Modules.Everpsblog.Shop'}</p>
    {/if}
</section>
