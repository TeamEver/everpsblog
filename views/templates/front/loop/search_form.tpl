<div class="everpsblog-search-toolbar">
    <div class="everpsblog-search-toolbar__search">
        <form method="get" action="{$link->getModuleLink('everpsblog','search')|escape:'htmlall':'UTF-8'}" class="everpsblog-search" data-doofinder-ignore="true">
            <div class="input-group">
                <label class="input-group-text d-none d-md-flex" for="everpsblog-search-input">{l s='Search the blog' d='Modules.Everpsblog.Shop'}</label>
                <input id="everpsblog-search-input" class="form-control" type="search" name="keyword" data-doofinder-ignore="true" placeholder="{l s='Search by keywords' d='Modules.Everpsblog.Shop'}" required />
                <button class="btn btn-primary" type="submit">{l s='Search' d='Modules.Everpsblog.Shop'}</button>
            </div>
        </form>
    </div>
    {if isset($allow_feed) && $allow_feed && isset($feed_url) && $feed_url}
    <a class="rss-link" href="{$feed_url|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener noreferrer" aria-label="{l s='RSS feed' d='Modules.Everpsblog.Shop'}">
        <span class="visually-hidden">{l s='RSS feed' d='Modules.Everpsblog.Shop'}</span>
    </a>
    {/if}
</div>
