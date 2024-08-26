{*
 * 2019-2021 Team Ever
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
<div class="panel everheader">
    <div class="panel-heading">
        <i class="icon icon-smile"></i> {l s='Ever Blog' mod='everpsblog'}
    </div>
    <div class="panel-body">
        <div class="col-lg-6 col-xs-12">
            <a href="#everbottom" id="evertop">
               <img id="everlogo" src="{$image_dir|escape:'htmlall':'UTF-8'}/ever.png" style="max-width: 120px;">
            </a>
            <strong>{l s='Welcome to Ever Blog !' mod='everpsblog'}</strong>
            <p>
                <strong>
                    {l s='Click on our logo to go direct to bottom' mod='everpsblog'}
                </strong>
            </p>
            <p>
                <a href="{$moduleConfUrl|escape:'htmlall':'UTF-8'}" class="btn btn-success">{l s='Direct link to module configuration' mod='everpsblog'}</a>
                <a href="{$postUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to posts' mod='everpsblog'}</a>
                <a href="{$categoryUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to categories' mod='everpsblog'}</a>
                <a href="{$tagUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to tags' mod='everpsblog'}</a>
                <a href="{$commentUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to comments' mod='everpsblog'}</a>
                <a href="{$authorUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to authors' mod='everpsblog'}</a>
                <a href="https://www.team-ever.com/contact" target="_blank" class="btn btn-info">{l s='Ask for support' mod='everpsblog'}</a>
            </p>
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {l s='See all available shortcodes' mod='everpsblog'}
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <p class="dropdown-item"><code>[shop_url]</code> => {l s='Shop URL' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[shop_name]</code>=> {l s='Shop name' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[start_cart_link]</code> => {l s='Start cart link' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[end_cart_link]</code> => {l s='End cart link' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[start_shop_link]</code> => {l s='Start shop link' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[end_shop_link]</code> => {l s='End shop link' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[start_contact_link]</code> => {l s='Start contact link' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[end_contact_link]</code> => {l s='End contact link' mod='everpsblog'}</p>
                <p class="dropdown-item"><code>[1F600]</code> => 😀</p>
                <p class="dropdown-item"><code>[1F601]</code> => 😁</p>
                <p class="dropdown-item"><code>[1F602]</code> => 😂</p>
                <p class="dropdown-item"><code>[1F603]</code> => 😃</p>
                <p class="dropdown-item"><code>[1F604]</code> => 😄</p>
                <p class="dropdown-item"><code>[1F605]</code> => 😅</p>
                <p class="dropdown-item"><code>[1F606]</code> => 😆</p>
                <p class="dropdown-item"><code>[1F607]</code> => 😇</p>
                <p class="dropdown-item"><code>[1F608]</code> => 😈</p>
                <p class="dropdown-item"><code>[1F609]</code> => 😉</p>
                <p class="dropdown-item"><code>[1F60A]</code> => 😊</p>
                <p class="dropdown-item"><code>[1F60B]</code> => 😋</p>
                <p class="dropdown-item"><code>[1F60C]</code> => 😌</p>
                <p class="dropdown-item"><code>[1F60D]</code> => 😍</p>
                <p class="dropdown-item"><code>[1F60E]</code> => 😎</p>
                <p class="dropdown-item"><code>[1F60F]</code> => 😏</p>
                <p class="dropdown-item"><code>[1F610]</code> => 😐</p>
                <p class="dropdown-item"><code>[1F611]</code> => 😑</p>
                <p class="dropdown-item"><code>[1F612]</code> => 😒</p>
                <p class="dropdown-item"><code>[1F613]</code> => 😓</p>
                <p class="dropdown-item"><code>[1F614]</code> => 😔</p>
                <p class="dropdown-item"><code>[1F615]</code> => 😕</p>
                <p class="dropdown-item"><code>[1F616]</code> => 😖</p>
                <p class="dropdown-item"><code>[1F617]</code> => 😗</p>
                <p class="dropdown-item"><code>[1F618]</code> => 😘</p>
                <p class="dropdown-item"><code>[1F619]</code> => 😙</p>
                <p class="dropdown-item"><code>[1F61A]</code> => 😚</p>
                <p class="dropdown-item"><code>[1F61B]</code> => 😛</p>
                <p class="dropdown-item"><code>[1F61C]</code> => 😜</p>
                <p class="dropdown-item"><code>[1F61D]</code> => 😝</p>
                <p class="dropdown-item"><code>[1F61E]</code> => 😞</p>
                <p class="dropdown-item"><code>[1F61F]</code> => 😟</p>
                <p class="dropdown-item"><code>[1F620]</code> => 😠</p>
                <p class="dropdown-item"><code>[1F621]</code> => 😡</p>
                <p class="dropdown-item"><code>[1F622]</code> => 😢</p>
                <p class="dropdown-item"><code>[1F623]</code> => 😣</p>
                <p class="dropdown-item"><code>[1F624]</code> => 😤</p>
                <p class="dropdown-item"><code>[1F625]</code> => 😥</p>
                <p class="dropdown-item"><code>[1F626]</code> => 😦</p>
                <p class="dropdown-item"><code>[1F627]</code> => 😧</p>
                <p class="dropdown-item"><code>[1F628]</code> => 😨</p>
                <p class="dropdown-item"><code>[1F629]</code> => 😩</p>
                <p class="dropdown-item"><code>[1F62A]</code> => 😪</p>
                <p class="dropdown-item"><code>[1F62B]</code> => 😫</p>
                <p class="dropdown-item"><code>[1F62C]</code> => 😬</p>
                <p class="dropdown-item"><code>[1F62D]</code> => 😭</p>
                <p class="dropdown-item"><code>[1F62E]</code> => 😮</p>
                <p class="dropdown-item"><code>[1F62F]</code> => 😯</p>
                <p class="dropdown-item"><code>[1F630]</code> => 😰</p>
                <p class="dropdown-item"><code>[1F631]</code> => 😱</p>
                <p class="dropdown-item"><code>[1F632]</code> => 😲</p>
                <p class="dropdown-item"><code>[1F633]</code> => 😳</p>
                <p class="dropdown-item"><code>[1F634]</code> => 😴</p>
                <p class="dropdown-item"><code>[1F635]</code> => 😵</p>
                <p class="dropdown-item"><code>[1F636]</code> => 😶</p>
                <p class="dropdown-item"><code>[1F637]</code> => 😷</p>
                <p class="dropdown-item"><code>[1F641]</code> => 🙁</p>
                <p class="dropdown-item"><code>[1F642]</code> => 🙂</p>
                <p class="dropdown-item"><code>[1F643]</code> => 🙃</p>
                <p class="dropdown-item"><code>[1F644]</code> => 🙄</p>
                <p class="dropdown-item"><code>[1F910]</code> => 🤐</p>
                <p class="dropdown-item"><code>[1F911]</code> => 🤑</p>
                <p class="dropdown-item"><code>[1F912]</code> => 🤒</p>
                <p class="dropdown-item"><code>[1F913]</code> => 🤓</p>
                <p class="dropdown-item"><code>[1F914]</code> => 🤔</p>
                <p class="dropdown-item"><code>[1F915]</code> => 🤕</p>
                <p class="dropdown-item"><code>[1F920]</code> => 🤠</p>
                <p class="dropdown-item"><code>[1F921]</code> => 🤡</p>
                <p class="dropdown-item"><code>[1F922]</code> => 🤢</p>
                <p class="dropdown-item"><code>[1F923]</code> => 🤣</p>
                <p class="dropdown-item"><code>[1F924]</code> => 🤤</p>
                <p class="dropdown-item"><code>[1F925]</code> => 🤥</p>
                <p class="dropdown-item"><code>[1F927]</code> => 🤧</p>
                <p class="dropdown-item"><code>[1F928]</code> => 🤨</p>
                <p class="dropdown-item"><code>[1F929]</code> => 🤩</p>
                <p class="dropdown-item"><code>[1F92A]</code> => 🤪</p>
                <p class="dropdown-item"><code>[1F92B]</code> => 🤫</p>
                <p class="dropdown-item"><code>[1F92C]</code> => 🤬</p>
                <p class="dropdown-item"><code>[1F92D]</code> => 🤭</p>
                <p class="dropdown-item"><code>[1F92E]</code> => 🤮</p>
                <p class="dropdown-item"><code>[1F92F]</code> => 🤯</p>
                <p class="dropdown-item"><code>[1F9D0]</code> => 🧐</p>
              </div>
            </div>
        </div>
        <div class="col-xs-12 col-lg-6">
            <p class="alert alert-warning">
                {l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' mod='everpsblog'}
            </p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="3LE8ABFYJKP98" />
            <input type="image" src="https://www.team-ever.com/wp-content/uploads/2019/06/appel_a_dons-1.jpg" border="0" name="submit" title="Soutenez le développement des modules gratuits de Team Ever !" alt="Soutenez le développement des modules gratuits de Team Ever !" />
            <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
            </form>
        </div>
    </div>
</div>
