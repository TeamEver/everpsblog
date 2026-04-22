{*
 * 2019-2025 Team Ever
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<section class="everpsblog-qcd-block everpsblog-qcd-tags-cloud">
    {if !empty($attributes.title)}
        <h2 class="everpsblog-qcd-block__title h5 mb-3">{$attributes.title|escape:'htmlall':'UTF-8'}</h2>
    {/if}
    {if !empty($attributes.tags)}
        <div class="d-flex flex-wrap gap-2">
            {foreach from=$attributes.tags item=tag}
                <a href="{$tag.url|escape:'htmlall':'UTF-8'}" class="badge bg-info text-decoration-none" title="{$tag.title|escape:'htmlall':'UTF-8'}">
                    {$tag.title|escape:'htmlall':'UTF-8'}
                </a>
            {/foreach}
        </div>
    {else}
        <p class="text-muted mb-0">{l s='Aucun tag disponible.' mod='everpsblog'}</p>
    {/if}
</section>
