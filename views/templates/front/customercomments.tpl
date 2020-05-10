{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='page.tpl'}

{block name="page_content"}
{if isset($cust_comments) && $cust_comments}
<div class="content">
    <div class="row">
        <p>{l s='Here is a list of all your comments on our blog.' mod='everpsblog'}</p>
        <p>
            <a href="{$blogUrl|escape:'html'}">
                {l s='You can comment whenever on our blog.' mod='everpsblog'}
            </a>
        </p>
    </div>
</div>
<div class="content">
    <div class="row">
{foreach from=$cust_comments item=comment}
        <div class="col-xs-12 col-12 col-md-3 article everpsblog bordered" id="comment-{$comment.post->id|escape:'html'}" style="border:1px solid black;">
            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $comment.post->id , 'link_rewrite' => $comment.post->link_rewrite])|escape:'html'}">
                <div class="col-xs-12 col-12 article-img">
                    <img src="{$blogImg_dir|escape:'html'}posts/post_image_{$comment.post->id|escape:'html'}.jpg" class="col-xs-12 col-12 img img-fluid mt-2 {if $animated}animated flipSideBySide zoomed{/if}"/>
                </div>
                <div class="col-xs-12">
                    <h3>{$comment.post->title}</h3>
                </div>
                <div class="col-xs-12">
                    <div class="everpsblogcontent rte" id="everpsblog-post-content-{$comment.comment->id|escape:'html'}">
                        <p>{l s='Your comment' mod='everpsblog'}</p>
                        {$comment.comment->comment nofilter}
                    </div>
                </div>
            </a>
            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $comment.post->id , 'link_rewrite' => $comment.post->link_rewrite])|escape:'html'}" class="btn btn-primary">{l s='See post' mod='everpsblog'}</a>
        </div>
{/foreach}
    </div>
</div>
{else}
<div class="content">
    <div class="row">
        <p>{l s='There\'s no comments on your account. Feel free to comment our posts on our blog !' mod='everpsblog'}</p>
    </div>
</div>
{/if}
<a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='Back to my account' mod='everpsblog'}" class="account" rel="nofollow"><span>{l s='Back to my account' mod='everpsblog'}</span></a>
{/block}
