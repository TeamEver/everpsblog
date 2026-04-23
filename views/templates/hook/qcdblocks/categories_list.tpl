{*
 * 2019-2025 Team Ever
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<section class="everpsblog-qcd-block everpsblog-qcd-categories-list">
    {if !empty($attributes.title)}
        <h2 class="everpsblog-qcd-block__title h5 mb-3">{$attributes.title|escape:'htmlall':'UTF-8'}</h2>
    {/if}
    {if !empty($attributes.categories)}
        <ul class="list-group list-group-flush">
            {foreach from=$attributes.categories item=category}
                <li class="list-group-item px-0">
                    <a href="{$category.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none">
                        {$category.title|escape:'htmlall':'UTF-8'}
                    </a>
                </li>
            {/foreach}
        </ul>
    {else}
        <p class="text-muted mb-0">{l s='No category available.' d='Modules.Everpsblog.Shop'}</p>
    {/if}
</section>
