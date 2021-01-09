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
<div class="panel everheader">
    <div class="panel-heading">
        <i class="icon icon-smile"></i> {l s='Ever Blog' mod='everpsblog'}
    </div>
    <div class="panel-body">
        <div class="col-xs-12 col-lg-6">
            <a href="#everbottom" id="evertop">
               <img id="everlogo" src="{$image_dir|escape:'htmlall':'UTF-8'}/ever.png" style="max-width: 120px;">
            </a>
            <p>
                <a href="{$blog_url|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-default">
                    {l s='See blog !' mod='everpsblog'}
                </a>
            </p>
            <strong>{l s='Welcome to Ever Blog !' mod='everpsblog'}</strong><br />{l s='Please configure your this form to set first parameters for your blog' mod='everpsblog'}<br />
            <p>
                <strong>
                    {l s='Click on our logo to go direct to bottom' mod='everpsblog'}
                </strong>
            </p>
            <p>
                <strong>
                    {l s='Don\'t forget to set this cron for schedules tasks' mod='everpsblog'}
                </strong>
                <div class="alert alert-info">
                    <h4>{l s='Empty trash' mod='everpsblog'}</h4>
                    <a href="{$everpsblogcron|escape:'htmlall':'UTF-8'}" target="_blank">{$everpsblogcron|escape:'htmlall':'UTF-8'}</a>
                </div>
                <div class="alert alert-info">
                    <h4>{l s='Publish planned posts' mod='everpsblog'}</h4>
                    <a href="{$everpsblogcronplanned|escape:'htmlall':'UTF-8'}" target="_blank">{$everpsblogcronplanned|escape:'htmlall':'UTF-8'}</a>
                </div>
                <div class="alert alert-info">
                    <h4>{l s='Pending notifications' mod='everpsblog'}</h4>
                    <a href="{$everpsblogcronpending|escape:'htmlall':'UTF-8'}" target="_blank">{$everpsblogcronpending|escape:'htmlall':'UTF-8'}</a>
                </div>
            </p>
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
