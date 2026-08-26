{*
 * 2019-2025 Team Ever
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 * Bloc QCD Page Builder : derniers articles Ever Blog.
 * Reprend volontairement le markup et les classes du bloc natif "cards" de
 * QCD Page Builder afin d'heriter de sa grille responsive, de son rail mobile
 * et du custom_css defini sur le bloc.
*}
{assign var='everColumnsDesktop' value=$attributes.columns_desktop|default:2|intval}
{assign var='everColumnsTablet' value=$attributes.columns_tablet|default:2|intval}
{assign var='everColumnsMobile' value=$attributes.columns_mobile|default:1|intval}
{assign var='everGap' value=$attributes.gap|default:20|intval}
{assign var='everMobileLayout' value=$attributes.mobile_layout|default:'rail'}
{assign var='everExcerptLength' value=$attributes.excerpt_length|default:120|intval}
{assign var='everLinkLabel' value=$attributes.link_label|default:''}
{if $everColumnsDesktop < 1}{assign var='everColumnsDesktop' value=1}{/if}
{if $everColumnsTablet < 1}{assign var='everColumnsTablet' value=1}{/if}
{if $everColumnsMobile < 1}{assign var='everColumnsMobile' value=1}{/if}
{if $everExcerptLength < 40}{assign var='everExcerptLength' value=120}{/if}
{if $everLinkLabel == ''}
  {capture name='everLinkLabelFallback'}{l s='Read the post' d='Modules.Everpsblog.Shop'}{/capture}
  {assign var='everLinkLabel' value=$smarty.capture.everLinkLabelFallback}
{/if}
{* les switchs du builder peuvent valoir false : ne pas utiliser |default qui ecraserait false *}
{assign var='everShowImage' value=true}
{assign var='everShowExcerpt' value=true}
{assign var='everShowBadge' value=true}
{if isset($attributes.show_image) && !$attributes.show_image}{assign var='everShowImage' value=false}{/if}
{if isset($attributes.show_excerpt) && !$attributes.show_excerpt}{assign var='everShowExcerpt' value=false}{/if}
{if isset($attributes.show_badge) && !$attributes.show_badge}{assign var='everShowBadge' value=false}{/if}
{assign var='everCardsInlineStyle' value="--qcd-cards-columns-desktop: `$everColumnsDesktop`; --qcd-cards-columns-tablet: `$everColumnsTablet`; --qcd-cards-columns-mobile: `$everColumnsMobile`; --qcd-cards-gap: `$everGap`px;"}
<section class="qcd-block mb-3 qcd-block-cards qcd-block-cards--mobile-{$everMobileLayout|escape:'htmlall':'UTF-8'} everpsblog-qcd-block everpsblog-qcd-latest-posts" role="region" aria-label="{if !empty($attributes.title)}{$attributes.title|escape:'htmlall':'UTF-8'}{else}{l s='Latest posts' d='Modules.Everpsblog.Shop'}{/if}" style="{$everCardsInlineStyle|escape:'htmlall':'UTF-8'}">
  {if !empty($attributes.title)}
    <div class="h2 everpsblog-qcd-block__title">{$attributes.title|escape:'htmlall':'UTF-8'}</div>
  {/if}
  {if !empty($attributes.posts)}
    <div class="qcd-block-cards__grid">
      {foreach from=$attributes.posts item=post}
        <article class="qcd-block-cards__item card h-100">
          {if $everShowImage && !empty($post.thumb)}
            <a href="{$post.url|escape:'htmlall':'UTF-8'}" title="{$post.title|escape:'htmlall':'UTF-8'}" tabindex="-1" aria-hidden="true">
              <img class="qcd-block-cards__image card-img-top" src="{$post.thumb|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" loading="lazy" decoding="async" />
            </a>
          {/if}
          <div class="card-body">
            {if $everShowBadge && !empty($post.badge)}
              <div class="qcd-block-cards__badge">{$post.badge|escape:'htmlall':'UTF-8'}</div>
            {/if}
            {if !empty($post.title)}
              <div class="h5 card-title h3">
                <a href="{$post.url|escape:'htmlall':'UTF-8'}">{$post.title|escape:'htmlall':'UTF-8'}</a>
              </div>
            {/if}
            {if $everShowExcerpt && !empty($post.excerpt)}
              <p class="card-text">{$post.excerpt|strip_tags|truncate:$everExcerptLength:'…'|escape:'htmlall':'UTF-8'}</p>
            {/if}
            <a class="btn btn-outline-primary btn-sm" href="{$post.url|escape:'htmlall':'UTF-8'}" title="{$post.title|escape:'htmlall':'UTF-8'}">{$everLinkLabel|escape:'htmlall':'UTF-8'}</a>
          </div>
        </article>
      {/foreach}
    </div>
  {/if}
</section>
