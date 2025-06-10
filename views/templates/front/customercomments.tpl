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

{block name="page_content"}
{if isset($cust_comments) && $cust_comments}
<div class="content">
    <div class="row">
        <p>{l s='Here is a list of all your comments on our blog.' mod='everpsblog'}</p>
        <p>
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}">
                {l s='You can comment whenever on our blog.' mod='everpsblog'}
            </a>
        </p>
    </div>
</div>
<div class="content">
    <div class="row">
{foreach from=$cust_comments item=comment}
        <div class="col-12 article everpsblog bordered card card-body" id="comment-{$comment.post->id|escape:'htmlall':'UTF-8'}" style="border:1px solid black;">
            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $comment.post->id , 'link_rewrite' => $comment.post->link_rewrite])|escape:'htmlall':'UTF-8'}">
                <div class="col-12">
                    <h3>{$comment.post->title|escape:'htmlall':'UTF-8'}</h3>
                </div>
                <div class="col-12">
                    <div class="everpsblogcontent rte" id="everpsblog-post-content-{$comment.comment->id|escape:'htmlall':'UTF-8'}">
                        <p>{l s='Your comment' mod='everpsblog'}</p>
                        {$comment.comment->comment nofilter}
                    </div>
                </div>
            </a>
            <a href="{$link->getModuleLink('everpsblog', 'post', ['id_ever_post' => $comment.post->id , 'link_rewrite' => $comment.post->link_rewrite])|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-blog-primary">{l s='See post' mod='everpsblog'}</a>
        </div>
{/foreach}
    </div>
</div>
{else}
<div class="content">
    <div class="row">
        <p>{l s='There\'s no comments on your account. Feel free to comment our posts on our blog !' mod='everpsblog'}</p>
        <p>
            <a href="{$blogUrl|escape:'htmlall':'UTF-8'}">
                {l s='You can comment whenever on our blog.' mod='everpsblog'}
            </a>
        </p>
    </div>
</div>
{/if}
<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" title="{l s='Back to my account' mod='everpsblog'}" class="account" rel="nofollow"><span>{l s='Back to my account' mod='everpsblog'}</span></a>
{/block}
