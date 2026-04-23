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
{*
<div class="panel everheader">
    <div class="panel-heading">
        <i class="icon icon-smile"></i> {l s='Ever Blog' d='Modules.Everpsblog.Admin'}
    </div>
    <div class="panel-body">
        <div class="col-lg-6 col-12">
            <a href="#everbottom" id="evertop">
               <img id="everlogo" src="{$image_dir|escape:'htmlall':'UTF-8'}/ever.png" style="max-width: 120px;">
            </a>
            <strong>{l s='Welcome to Ever Blog !' d='Modules.Everpsblog.Admin'}</strong>
            <p>
                <strong>
                    {l s='Click on our logo to go direct to bottom' d='Modules.Everpsblog.Admin'}
                </strong>
            </p>
            <p>
                <a href="{$moduleConfUrl|escape:'htmlall':'UTF-8'}" class="btn btn-success">{l s='Direct link to module configuration' d='Modules.Everpsblog.Admin'}</a>
                <a href="{$postUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to posts' d='Modules.Everpsblog.Admin'}</a>
                <a href="{$categoryUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to categories' d='Modules.Everpsblog.Admin'}</a>
                <a href="{$tagUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to tags' d='Modules.Everpsblog.Admin'}</a>
                <a href="{$commentUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to comments' d='Modules.Everpsblog.Admin'}</a>
                <a href="{$authorUrl|escape:'htmlall':'UTF-8'}" class="btn btn-info">{l s='Direct link to authors' d='Modules.Everpsblog.Admin'}</a>
                <a href="https://www.team-ever.com/contact" target="_blank" class="btn btn-info">{l s='Ask for support' d='Modules.Everpsblog.Admin'}</a>
            </p>
            </div>
        </div>
        <div class="col-lg-6">
            <p class="alert alert-warning">
                {l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' d='Modules.Everpsblog.Admin'}
            </p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="3LE8ABFYJKP98" />
            <input type="image" src="https://www.team-ever.com/wp-content/uploads/2019/06/appel_a_dons-1.jpg" border="0" name="submit" title="{l s='Support Team Ever free modules!' d='Modules.Everpsblog.Admin'}" alt="{l s='Support Team Ever free modules!' d='Modules.Everpsblog.Admin'}" />
            <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
            </form>
        </div>
    </div>
</div>
*}
