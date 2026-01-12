{*
 * Ever Blog Category Slider - Corrected for block.extra.states
*}

{if isset($block.extra.states) && $block.extra.states|@count}
<div class="everpsblog-block everpsblog-category-slider">

    {if isset($block.settings.bootstrap_slider) && $block.settings.bootstrap_slider && isset($carousel_id)}
        {assign var=carousel_id value='everpsblog-post-slider-'|cat:$block.id_prettyblocks}
        <div id="{$carousel_id|escape:'htmlall':'UTF-8'}" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-wrap="true" data-wrap="true">

            <div class="carousel-inner">

                {foreach from=$block.extra.states item=state name=categoryslider}

                    {assign var="category" value=$state.category}

                    <div class="carousel-item {if $smarty.foreach.categoryslider.first}active{/if}">
                        <article class="card h-100">

                            {if $category.featured_thumb}
                                <img class="card-img-top img-fluid"
                                     src="{$category.featured_thumb|escape:'htmlall':'UTF-8'}"
                                     alt="{$category.title|escape:'htmlall':'UTF-8'}"
                                     loading="lazy">
                            {/if}

                            <div class="card-body">

                                <h3 class="h5 card-title">
                                    <a href="{$category.url|escape:'htmlall':'UTF-8'}"
                                       title="{$category.title|escape:'htmlall':'UTF-8'}">
                                        {$category.title|escape:'htmlall':'UTF-8'}
                                    </a>
                                </h3>

                                {if $category.description}
                                    <p class="card-text">
                                        {$category.description|escape:'htmlall':'UTF-8'}
                                    </p>
                                {/if}

                            </div>

                        </article>
                    </div>

                {/foreach}

            </div>

            <a class="carousel-control-prev" href="#{$carousel_id|escape:'htmlall':'UTF-8'}" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only visually-hidden">{l s='Previous' mod='everpsblog'}</span>
            </a>

            <a class="carousel-control-next" href="#{$carousel_id|escape:'htmlall':'UTF-8'}" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only visually-hidden">{l s='Next' mod='everpsblog'}</span>
            </a>

        </div>

    {else}

        <div class="row">

            {foreach from=$block.extra.states item=state}

                {assign var="category" value=$state.category}

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <article class="card h-100">

                        {if $category.featured_thumb}
                            <img class="card-img-top img-fluid"
                                 src="{$category.featured_thumb|escape:'htmlall':'UTF-8'}"
                                 alt="{$category.title|escape:'htmlall':'UTF-8'}"
                                 loading="lazy">
                        {/if}

                        <div class="card-body">

                            <h3 class="h5 card-title">
                                <a href="{$category.url|escape:'htmlall':'UTF-8'}"
                                   title="{$category.title|escape:'htmlall':'UTF-8'}">
                                    {$category.title|escape:'htmlall':'UTF-8'}
                                </a>
                            </h3>

                            {if $category.description}
                                <p class="card-text">
                                    {$category.description|escape:'htmlall':'UTF-8'}
                                </p>
                            {/if}

                        </div>

                    </article>
                </div>

            {/foreach}

        </div>

    {/if}

</div>
{/if}
