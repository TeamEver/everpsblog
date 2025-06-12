{*
 * 2019-2025 Team Ever
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
 *  @copyright 2019-2025 Team Ever
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
    <meta name="twitter:image" content="{$featured_image|escape:'htmlall':'UTF-8'}>
    <!-- Open Graph Card data -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
    <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
    <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
    <meta property="og:image" content="{$featured_image|escape:'htmlall':'UTF-8'}">
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "{$blog_type|escape:'htmlall':'UTF-8'}",
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
        <div class="col-12 col-md-12 alert alert-danger" role="alert">
        {foreach from=$errors item=error}
          <p>{$error|escape:'htmlall':'UTF-8'}</p>
        {/foreach}
        </div>
        {/if}
        {if isset($successes) && $successes}
        <div class="col-12 col-md-12 alert alert-success" role="alert">
        {foreach from=$successes item=success}
          <p>{$success|escape:'htmlall':'UTF-8'}</p>
        {/foreach}
        </div>
        {/if}
        {if isset($show_featured_post) && $show_featured_post}
        <div class="row post-header">
            <img class="img img-fluid post-featured-image featured-image" src="{$featured_image|escape:'htmlall':'UTF-8'}" alt="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
        </div>
        {/if}
        <h1 class="text-start">{$post->title|escape:'htmlall':'UTF-8'}</h1>
        <p class="postpublished text-start">
            <strong>{$post->date_add|date_format:'%d %B %Y'|escape:'htmlall':'UTF-8'}</strong>
        </p>
        {if isset($show_author) && $show_author}
        <p class="text-center author_cover_container">
            <a href="{$author->url|escape:'htmlall':'UTF-8'}" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
                <img src="{$author_cover|escape:'htmlall':'UTF-8'}" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" class="img-fluid author-icon rounded-circle" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'}" title="{$author->nickhandle|escape:'htmlall':'UTF-8'}">
                <br>
                {l s='By' mod='everpsblog'} {$author->nickhandle|escape:'htmlall':'UTF-8'}
            </a>
        </p>
        {/if}
    </div>
    <div class="row">
        <div class="col-12 col-md-12 postcontent {if $animated}zoomed{/if}">
            {if isset($post->password_protected) && $post->password_protected}
            <div class="alert alert-warning">
                {if isset($prettyblocks_enabled) && $prettyblocks_enabled}
                {widget name="prettyblocks" zone_name="displayPost{$post->id}"}
                {/if}
                {$post->content nofilter}
            </div>
            <form method="POST">
                <div class="mb-3">
                    <input type="password" class="form-control" id="post_psswd" name="post_psswd" placeholder="{l s='Password' mod='everpsblog'}" required>
                </div>
                <button type="submit" class="btn btn-primary">{l s='Validate' mod='everpsblog'}</button>
            </form>
            {else}
            {if isset($prettyblocks_enabled) && $prettyblocks_enabled}
            {widget name="prettyblocks" zone_name="displayPost{$post->id}"}
            {/if}
            {$post->content nofilter}
            {/if}
        </div>
    </div>
</div>
{if !isset($post->password_protected)}
<div class="container">
    <div class="row mt-2">
        <div class="col-12 col-md-6">
            {if isset($allow_views_count) && $allow_views_count > 0}
            <span class="postviews"> | {$post->count|escape:'htmlall':'UTF-8'} {l s='Views' mod='everpsblog'}</span>
            {/if}
            {if isset($tags) && $tags}
            <p class="taggedIn d-none">{l s='Tagged in' mod='everpsblog'}
            {foreach from=$tags item=tag}
                <a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag->id, 'link_rewrite'=>$tag->link_rewrite])|escape:'htmlall':'UTF-8'}" title="{$tag->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{$tag->title|escape:'htmlall':'UTF-8'}</a>&nbsp;
            {/foreach}
            </p>
            {/if}
        </div>
            <div class="col-12 col-md-6">
          {if $social_share_links}
            <div class="social-sharing d-none">
              <span>{l s='Share' d='Shop.Theme.Actions'}</span>
              <ul>
                {foreach from=$social_share_links item='social_share_link'}
                  <li class="{$social_share_link.class|escape:'htmlall':'UTF-8'} icon-gray"><a href="{$social_share_link.url|escape:'htmlall':'UTF-8'}" class="text-hide" title="{$social_share_link.label|escape:'htmlall':'UTF-8'}" target="_blank">{$social_share_link.label|escape:'htmlall':'UTF-8'}</a></li>
                {/foreach}
              </ul>
            </div>
          {/if}
        </div>
    </div>
</div>
{/if}

{if isset($allow_comments) && $allow_comments && !isset($post->password_protected)}

{if isset($logged) && $logged ==  false && isset($only_logged_comment) && $only_logged_comment == true}
<div class="card card-body mt-2">
    <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?back={$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $post->id_ever_post , 'link_rewrite' => $post->link_rewrite])|escape:'htmlall':'UTF-8'}" method="post" id="login-form" class="box">
        <h3 class="page-subheading">{l s='Log in to comment' mod='everpsblog'}</h3>
        <div class="form_content clearfix">
            <div class="mb-3">
                <label>{l s='Email address' mod='everpsblog'}</label> 
                <input class="is_required validate account_input form-control" id="email" name="email" value="" type="text" />
            </div>
        <div class="mb-3">
            <label>{l s='Password' mod='everpsblog'}</label>
            <input class="form-control js-child-focus js-visible-password" type="password" id="password" name="password" value="" />
        </div>
        <p class="lost_password mb-3">
            <a href="{$link->getPageLink('password', true)|escape:'htmlall':'UTF-8'}" title="{l s='Recover your forgotten password' mod='everpsblog'}">{l s='Forgot your password ?' mod='everpsblog'}</a>
        </p>
        <p class="submit">
            <input type="hidden" name="submitLogin" value="1">
            <input type="hidden" class="hidden" name="back" value="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $post->id_ever_post , 'link_rewrite' => $post->link_rewrite])|escape:'htmlall':'UTF-8'}" />
            <button id="submit-login" class="btn btn-primary btn-blog-primary" data-link-action="sign-in" type="submit">
            {l s='Login' mod='everpsblog'}
          </button>
        </p>
        </div>
    </form>
</div>
{else}
<section class="container clearfix">
    <div class="row mt-2">
        <span id="leaveComment">{l s='Leave a comment' mod='everpsblog'}</span>
        <form enctype="multipart/form-data" method="post">
            {if isset($logged) && $logged}
            <input type="hidden" name="customerEmail" id="customerEmail" value="{$customer.email|escape:'htmlall':'UTF-8'}">
            {else}
            <div class="mb-3">
                <label for="customerEmail">{l s='Email address' mod='everpsblog'}</label>
                <input type="email" class="form-control" id="customerEmail" name="customerEmail" aria-describedby="emailHelp" placeholder="Enter email">
                <small id="emailHelp" class="form-text text-muted">{l s='We\'ll never share your email with anyone else.' mod='everpsblog'}</small>
            </div>
            <div class="mb-3">
                <label for="name">{l s='Name' mod='everpsblog'}</label>
                <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" placeholder="Enter your name">
            </div>
            {/if}
            <div class="mb-3">
            <label for="evercomment">{l s='Your comment' mod='everpsblog'}</label>
            <textarea class="form-control" id="evercomment" name="evercomment" rows="3"></textarea>
            </div>
            <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="RgpdCompliance" name="RgpdCompliance">
            <label class="form-check-label" for="RgpdCompliance">
                {l s='RGPD compliance' mod='everpsblog'}
            </label>
            </div>
            <button type="submit" class="btn btn-primary btn-blog-primary" id="everpostcomment" name="everpostcomment">{l s='Submit' mod='everpsblog'}</button>
        </form>
    </div>
</section>
{/if}

{if isset($commentsCount) && $commentsCount > 0}
{hook h="displayBeforeEverComment"}
<section class="comments container clearfix mt-2">
    <span id="commentsTitle">{$commentsCount|escape:'htmlall':'UTF-8'} {l s='comment(s)' mod='everpsblog'}</span>
    <div class="commentcontainer row g-3">
        {foreach from=$comments item=comment}
            <div class="container commentblock" id="{$comment->id|escape:'htmlall':'UTF-8'}">
                <div class="row">
                    <div class="col-12 col-md-8 commentname">
                        {$comment->name|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="col-12 col-md-4 commentdate">
                        {$comment->date_upd|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="col-12 col-md-12 comment">
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
{if isset($related_posts) && $related_posts}
<section id="related-posts" class="mt-2">
  <h2 class="text-center">{l s='Related posts' mod='everpsblog'}</h2>
  <div class="row blogrelated mt-2">
    {foreach from=$related_posts item=item}
      {include file='module:everpsblog/views/templates/front/loop/post_product.tpl'}
    {/foreach}
  </div>
</section>
{/if}
{hook h="displayAfterEverPost" everblogpost=$post}
{/block}
