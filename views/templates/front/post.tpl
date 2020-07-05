{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='page.tpl'}

{block name="page_content"}
{hook h="displayBeforeEverPost" everblogpost=$post}
<div class="content" itemscope="itemscope" itemtype="http://schema.org/Blog">
    <div class="container" itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost">
            <h1 itemprop="headline">{$post->title nofilter}</h1>
            <p class="postpublished" itemprop="datePublished">{l s='Published on' mod='everpsblog'} {$post->date_add|escape:'htmlall':'UTF-8'}</p>
            <div class="row">
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
            <img class="img img-fluid" src="{$blogImg_dir|escape:'htmlall':'UTF-8'}posts/post_image_{$post->id|escape:'htmlall':'UTF-8'}.jpg" alt="{$post->title|escape:'htmlall':'UTF-8'} {$shop.name|escape:'htmlall':'UTF-8'}">
        </div>
    </div>
    <div class="container">
        <div class="row postcontent {if $animated}zoomed{/if}" itemprop="articleBody">
            {$post->content nofilter}
        </div>
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
{if isset($allow_comments) && $allow_comments}
<section class="leaveComment container clearfix">
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
{/if}
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
{if isset($count_products) && $count_products > 0}
<section class="featured-products container clearfix carousel slide">
    <span id="postProducts" class="col-12 col-xs-12 my-2">{l s='Linked products' mod='everpsblog'}</span>
    <div class="row postproducts products carousel-inner">
        {assign var=counter value=0}
        {foreach from=$products item=product}
        {* {$product|var_dump} *}
        <article class="product-miniature js-product-miniature{if $counter == 0} active{/if}" data-id-product="{$product->id|escape:'htmlall':'UTF-8'}" data-slide-to="{$counter|escape:'htmlall':'UTF-8'}" itemscope itemtype="http://schema.org/Product">
            <a href="{$link->getProductLink($product)|escape:'htmlall':'UTF-8'}">
                <div class="thumbnail-container">
                    <img src="{$link->getImageLink($product->link_rewrite, $product->cover, 'home_default')|escape:'htmlall':'UTF-8'}" class="img-fluid mx-auto d-block">
                </div>
                <div class="product-description">
                    <h3 class="h3 product-title" itemprop="name">
                        {$product->name|escape:'htmlall':'UTF-8'}
                    </h3>
                    <div class="product-price-and-shipping">
                        {hook h='displayProductPriceBlock' product=$product type="before_price"}
                        <span itemprop="price" class="price">{Tools::displayPrice($product->price)|escape:'htmlall':'UTF-8'}</span>

                        {hook h='displayProductPriceBlock' product=$product type='unit_price'}

                        {hook h='displayProductPriceBlock' product=$product type='weight'}
                    </div>
                </div>
            </a>
        </article>
        {$counter=$counter+1}
        {/foreach}
    </div>
</section>
{/if}
{hook h="displayAfterEverPost" everblogpost=$post}
{/block}
