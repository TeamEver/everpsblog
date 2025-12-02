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
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                {if isset($show_featured_post) && $show_featured_post}
                <div class="mb-3">
                    <img class="img img-fluid post-featured-image featured-image rounded w-100" src="{$featured_image|escape:'htmlall':'UTF-8'}" alt="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" title="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
                </div>
                {/if}
                <h1 class="text-start h2 mb-3">{$post->title|escape:'htmlall':'UTF-8'}</h1>
                <p class="postpublished text-start text-muted mb-3">
                    <strong>{$post->date_add|date_format:'%d %B %Y'|escape:'htmlall':'UTF-8'}</strong>
                    {if isset($default_category) && $default_category && !$default_category->is_root_category}
                        - <a href="{$link->getModuleLink('everpsblog', 'category', ['id_ever_category'=>$default_category->id_ever_category, 'link_rewrite'=>$default_category->link_rewrite])|escape:'htmlall':'UTF-8'}" class="text-decoration-none" title="{$default_category->title|escape:'htmlall':'UTF-8'}">{$default_category->title|escape:'htmlall':'UTF-8'}</a>
                    {/if}
                </p>
                {if isset($show_author) && $show_author}
                <div class="d-flex flex-column align-items-center mb-2 author_cover_container">
                    <a href="{$author->url|escape:'htmlall':'UTF-8'}" class="text-center text-decoration-none" title="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
                        <img src="{$author_cover|escape:'htmlall':'UTF-8'}" alt="{$author->nickhandle|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}" class="img-fluid author-icon rounded-circle mb-2" title="{$author->nickhandle|escape:'htmlall':'UTF-8'}">
                        <strong>{l s='By' mod='everpsblog'} {$author->nickhandle|escape:'htmlall':'UTF-8'}</strong>
                    </a>
                </div>
                {/if}
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body postcontent {if $animated}zoomed{/if}">
                {if isset($post->password_protected) && $post->password_protected}
                <div class="alert alert-warning">
                    {if isset($prettyblocks_enabled) && $prettyblocks_enabled}
                    {widget name="prettyblocks" zone_name="displayPost{$post->id}"}
                    {/if}
                    {$post->content nofilter}
                </div>
                <form method="POST" class="mt-3">
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
            {if isset($show_post_tags) && $show_post_tags && isset($tags) && $tags}
            <div class="post-tags">
            {foreach from=$tags item=tag}
                <a href="{$link->getModuleLink('everpsblog', 'tag', ['id_ever_tag'=>$tag->id, 'link_rewrite'=>$tag->link_rewrite])|escape:'htmlall':'UTF-8'}" class="badge badge-info bg-info m-1" title="{$tag->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">{$tag->title|escape:'htmlall':'UTF-8'}</a>
            {/foreach}
            </div>
            {/if}
        </div>
            <div class="col-12 col-md-6">
          {if $social_share_links}
            <div class="social-sharing">
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
<div class="card card-body mt-2 shadow-sm border-0">
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
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <span id="leaveComment" class="h5 mb-2 mb-md-0">{l s='Leave a comment' mod='everpsblog'}</span>
                        <span class="text-muted small">{l s='Fields marked with * are required.' mod='everpsblog'}</span>
                    </div>
                    <form enctype="multipart/form-data" method="post" class="comment-form">
            {if isset($logged) && $logged}
            <input type="hidden" name="customerEmail" id="customerEmail" value="{$customer.email|escape:'htmlall':'UTF-8'}">
            {else}
            <div class="mb-3">
                <label for="customerEmail" class="form-label">{l s='Email address *' mod='everpsblog'}</label>
                <input type="email" class="form-control" id="customerEmail" name="customerEmail" aria-describedby="emailHelp" placeholder="{l s='Enter your email' mod='everpsblog'}" required>
                <small id="emailHelp" class="form-text text-muted">{l s='We\'ll never share your email with anyone else.' mod='everpsblog'}</small>
                <div class="invalid-feedback d-block" style="display:none;" data-error-for="customerEmail" data-message="{l s='Please enter a valid email address.' mod='everpsblog'}"></div>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">{l s='Name *' mod='everpsblog'}</label>
                <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" placeholder="{l s='Enter your name' mod='everpsblog'}" required>
                <small id="nameHelp" class="form-text text-muted">{l s='This will be displayed with your comment.' mod='everpsblog'}</small>
                <div class="invalid-feedback d-block" style="display:none;" data-error-for="name" data-message="{l s='Please enter your name.' mod='everpsblog'}"></div>
            </div>
            {/if}
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="evercomment" class="form-label mb-0">{l s='Your comment *' mod='everpsblog'}</label>
                    <small class="text-muted" id="commentCounter" data-maxlength="800">0/800</small>
                </div>
                <textarea class="form-control" id="evercomment" name="evercomment" rows="3" maxlength="800" aria-describedby="commentHelp" required></textarea>
                <small id="commentHelp" class="form-text text-muted">{l s='Share your thoughts in a constructive way. Maximum 800 characters.' mod='everpsblog'}</small>
                <div class="invalid-feedback d-block" style="display:none;" data-error-for="evercomment" data-message="{l s='Please add a comment.' mod='everpsblog'}"></div>
            </div>
            <div class="form-check form-switch mb-4 p-0">
                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="checkbox" value="1" id="RgpdCompliance" name="RgpdCompliance">
                    <label class="form-check-label fw-semibold" for="RgpdCompliance">
                        {l s='RGPD compliance' mod='everpsblog'}
                    </label>
                </div>
                <small class="text-muted d-block mt-1">{l s='By enabling this option, you agree that your comment may be stored according to our privacy policy.' mod='everpsblog'}</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-blog-primary" id="everpostcomment" name="everpostcomment">{l s='Submit' mod='everpsblog'}</button>
                <button type="reset" class="btn btn-secondary">{l s='Reset' mod='everpsblog'}</button>
            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
{/if}

<script>
{literal}
document.addEventListener('DOMContentLoaded', function () {
    const commentField = document.getElementById('evercomment');
    const counter = document.getElementById('commentCounter');
    const form = document.querySelector('.comment-form');
    const fields = ['customerEmail', 'name', 'evercomment'];

    const updateCounter = () => {
        if (!commentField || !counter) {
            return;
        }
        const max = parseInt(counter.dataset.maxlength, 10) || commentField.maxLength || 0;
        const current = commentField.value.length;
        counter.textContent = max ? `${current}/${max}` : `${current}`;
    };

    updateCounter();

    if (commentField) {
        commentField.addEventListener('input', updateCounter);
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            let hasError = false;
            fields.forEach(function (fieldName) {
                const field = document.getElementById(fieldName);
                const errorContainer = document.querySelector(`[data-error-for="${fieldName}"]`);
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                    errorContainer.textContent = '';
                }
                if (field && field.hasAttribute('required') && !field.value.trim()) {
                    hasError = true;
                    if (errorContainer) {
                        errorContainer.style.display = 'block';
                        errorContainer.textContent = errorContainer.dataset.message || 'This field is required.';
                    }
                }
            });

            if (hasError) {
                event.preventDefault();
            }
        });
    }
});
{/literal}
</script>

{if isset($commentsCount) && $commentsCount > 0}
{hook h="displayBeforeEverComment"}
<section class="comments container clearfix mt-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
        <div class="d-flex align-items-center">
            <h2 id="commentsTitle" class="h5 mb-0">{l s='Comments' mod='everpsblog'}</h2>
            <span class="badge bg-secondary text-white ms-2">{$commentsCount|escape:'htmlall':'UTF-8'}</span>
        </div>
        <small class="text-muted">{l s='Join the discussion below.' mod='everpsblog'}</small>
    </div>
    <div class="commentcontainer mt-3">
        {foreach from=$comments item=comment name=commentLoop}
            {assign var=commentInitial value=$comment->name|substr:0:1|escape:'htmlall':'UTF-8'}
            <article class="d-flex py-3 {if !$smarty.foreach.commentLoop.last}border-bottom{/if}" id="{$comment->id|escape:'htmlall':'UTF-8'}">
                <div class="flex-shrink-0 me-3">
                    {if isset($comment->image) && $comment->image}
                        <img src="{$comment->image|escape:'htmlall':'UTF-8'}" alt="{$comment->name|escape:'htmlall':'UTF-8'}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                    {else}
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <span class="fw-semibold">{$commentInitial|escape:'htmlall':'UTF-8'}</span>
                        </div>
                    {/if}
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                        <strong class="commentname">{$comment->name|escape:'htmlall':'UTF-8'}</strong>
                        <span class="commentdate text-muted small">{$comment->date_upd|escape:'htmlall':'UTF-8'}</span>
                    </div>
                    <div class="comment mt-2">
                        <div class="rte mb-0">
                            {$comment->comment nofilter}
                        </div>
                    </div>
                </div>
            </article>
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
