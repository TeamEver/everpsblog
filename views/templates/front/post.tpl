{*
 * 2019-2020 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file='page.tpl'}

{block name='head' append}
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    {* <meta name="twitter:site" content="@publisher_handle"> *}
    <meta name="twitter:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta name="twitter:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    {* <meta name="twitter:creator" content="@author_handle"> *}
    <meta name="twitter:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$post->id|escape:'htmlall':'UTF-8'}.jpg">
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$post->id|escape:'htmlall':'UTF-8'}.jpg">
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://google.com/article"
    },
    "headline": "{$post->title|escape:'htmlall':'UTF-8'}",
    "image": [
      "{$featured_image|escape:'htmlall':'UTF-8'}"
     ],
    "datePublished": "{$post->date_add|escape:'htmlall':'UTF-8'}",
    "dateModified": "{$post->date_upd|escape:'htmlall':'UTF-8'}",
    "author": {
      "@type": "Person",
      "name": "{$author->nickhandle|escape:'htmlall':'UTF-8'}"
    },
     "publisher": {
      "@type": "Organization",
      "name": "{$shop.name|escape:'htmlall':'UTF-8'}",
      "logo": {
        "@type": "ImageObject",
        "url": "{$shop.logo|escape:'htmlall':'UTF-8'}"
      }
    }
    }
    </script>
{/block}

{block name="page_content"}
{hook h="displayBeforeEverPost" everblogpost=$post}
<div class="content">
    <div class="container">
        {if isset($errors) && $errors}
        <div class="col-12 col-xs-12 col-md-12 alert alert-danger" role="alert">
        {foreach from=$errors item=error}
          <p>{$error|escape:'htmlall':'UTF-8'}</p>
        {/foreach}
        </div>
        {/if}
        {if isset($successes) && $successes}
        <div class="col-12 col-xs-12 col-md-12 alert alert-success" role="alert">
        {foreach from=$successes item=success}
          <p>{$success|escape:'htmlall':'UTF-8'}</p>
        {/foreach}
        </div>
        {/if}
        <div class="row post-header">
            <img class="img img-fluid post-featured-image featured-image" src="{$featured_image|escape:'htmlall':'UTF-8'}" alt="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
        </div>
        <h1 class="text-center">{$post->title|escape:'htmlall':'UTF-8'}</h1>
        <p class="text-center">
            <a href="{$author->url|escape:'htmlall':'UTF-8'}" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
                <img src="{$author_cover|escape:'htmlall':'UTF-8'}" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" class="img-fluid author-icon rounded-circle" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'}" title="{$author->nickhandle|escape:'htmlall':'UTF-8'}">
                {l s='By' mod='everpsblog'} {$author->nickhandle|escape:'htmlall':'UTF-8'}
            </a>
        </p>
    </div>
    <div class="row">
        <div class="col-12 col-md-12 postcontent {if $animated}zoomed{/if}">
            {$post->content nofilter}
        </div>
        <p class="postpublished">{l s='Published on' mod='everpsblog'} {$post->date_add|escape:'htmlall':'UTF-8'}</p>
    </div>
</div>
<div class="container">
    <div class="row">
        {if isset($tags) && $tags}
        <p class="taggedIn">{l s='Tagged in' mod='everpsblog'}
        {foreach from=$tags item=tag}
            <a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag->id, 'link_rewrite'=>$tag->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$tag->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{$tag->title|escape:'htmlall':'UTF-8'}</a>&nbsp;
        {/foreach}
        </p>
        {/if}
    </div>
</div>
  {if $social_share_links}
    <div class="social-sharing">
      <span>{l s='Share' d='Shop.Theme.Actions'}</span>
      <ul>
        {foreach from=$social_share_links item='social_share_link'}
          <li class="{$social_share_link.class} icon-gray"><a href="{$social_share_link.url}" class="text-hide" title="{$social_share_link.label}" target="_blank">{$social_share_link.label}</a></li>
        {/foreach}
      </ul>
    </div>
  {/if}

{if isset($allow_comments) && $allow_comments}
<section class="container clearfix">
    <span id="leaveComment">{l s='Leave a comment' mod='everpsblog'}</span>
    <div class="row">
        <form enctype="multipart/form-data" method="post">
            {if isset($logged) && $logged}
            <input type="hidden" name="customerEmail" id="customerEmail" value="{$customer.email|escape:'htmlall':'UTF-8'}">
            {else}
            <div class="form-group">
                <label for="customerEmail">{l s='Email address' mod='everpsblog'}</label>
                <input type="email" class="form-control" id="customerEmail" name="customerEmail" aria-describedby="emailHelp" placeholder="Enter email">
                <small id="emailHelp" class="form-text text-muted">{l s='We\'ll never share your email with anyone else.' mod='everpsblog'}</small>
            </div>
            <div class="form-group">
                <label for="name">{l s='Name' mod='everpsblog'}</label>
                <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" placeholder="Enter your name">
            </div>
            {/if}
            <div class="form-group">
            <label for="evercomment">{l s='Your comment' mod='everpsblog'}</label>
            <textarea class="form-control" id="evercomment" name="evercomment" rows="3"></textarea>
            </div>
            <div class="form-check">
            <input class="checkbox" type="checkbox" value="1" id="RgpdCompliance" name="RgpdCompliance">
            <label class="form-check-label" for="RgpdCompliance">
                {l s='RGPD compliance' mod='everpsblog'}
            </label>
            </div>
            <button type="submit" class="btn btn-primary" id="everpostcomment" name="everpostcomment">{l s='Submit' mod='everpsblog'}</button>
        </form>
    </div>
</section>

{if isset($commentsCount) && $commentsCount > 0}
{hook h="displayBeforeEverComment"}
<section class="comments container clearfix">
    <span id="commentsTitle">{$commentsCount|escape:'htmlall':'UTF-8'} {l s='comment(s)' mod='everpsblog'}</span>
    <div class="commentcontainer row">
        {foreach from=$comments item=comment}
            <div class="container commentblock" id="{$comment->id|escape:'htmlall':'UTF-8'}">
                <div class="row">
                    <div class="col-12 col-xs-12 col-md-8 commentname">
                        {$comment->name|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="col-12 col-xs-12 col-md-4 commentdate">
                        {$comment->date_upd|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="col-12 col-xs-12 col-md-12 comment">
                        <div class="rte">
                            {$comment->comment nofilter}
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</section>
{hook h="displayAfterEverComment"}
{/if}
{/if}
{if isset($count_products) && $count_products > 0}
<section id="products">
  <h2 class="text-center">{l s='Linked products' mod='everpsblog'}</h2>
  <div class="products row">
    {foreach from=$ps_products item="product"}
      {include file="catalog/_partials/miniatures/product.tpl" product=$product}
    {/foreach}
  </div>
</section>
{/if}
{hook h="displayAfterEverPost" everblogpost=$post}
{/block}
