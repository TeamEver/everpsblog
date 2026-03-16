<form method="get" action="{$link->getModuleLink('everpsblog','search')|escape:'htmlall':'UTF-8'}" class="everpsblog-search" data-doofinder-ignore="true">
    <div class="input-group">
        <label class="input-group-text" for="everpsblog-search-input">{l s='Search the blog' mod='everpsblog'}</label>
        <input id="everpsblog-search-input" class="form-control" type="search" name="s" data-doofinder-ignore="true" placeholder="{l s='Search by keywords' mod='everpsblog'}" required />
        <button class="btn btn-primary" type="submit">{l s='Search' mod='everpsblog'}</button>
    </div>
</form>
