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
<div class="panel everheader">
    <div class="panel-heading">
        <i class="icon icon-smile"></i> {l s='Ever Blog' mod='everpsblog'}
    </div>
    <div class="panel-body">
        {if isset($everpsblog_quick_links) && $everpsblog_quick_links}
        <div class="row everpsblog-quick-links">
            {foreach from=$everpsblog_quick_links item=quickLink}
                <div class="col-sm-6 col-md-4 everpsblog-quick-link">
                    <a class="everpsblog-quick-link-card" href="{$quickLink.href|escape:'htmlall':'UTF-8'}"{if isset($quickLink.blank) && $quickLink.blank} target="_blank" rel="noopener"{/if}>
                        <span class="everpsblog-quick-link-icon"><i class="icon {$quickLink.icon|escape:'htmlall':'UTF-8'}"></i></span>
                        <span class="everpsblog-quick-link-label">{$quickLink.label|escape:'htmlall':'UTF-8'}</span>
                        {if isset($quickLink.description) && $quickLink.description}
                            <span class="everpsblog-quick-link-desc">{$quickLink.description|escape:'htmlall':'UTF-8'}</span>
                        {/if}
                    </a>
                </div>
            {/foreach}
        </div>
        {/if}
        <div class="row everpsblog-header-body">
            <div class="col-lg-6">
                <p class="everpsblog-logo">
                    <a href="#everbottom" id="evertop">
                       <img id="everlogo" src="{$image_dir|escape:'htmlall':'UTF-8'}/ever.png" alt="{l s='Ever Blog logo' mod='everpsblog'}" class="img-responsive">
                    </a>
                </p>
                <p class="everpsblog-welcome">
                    <strong>{l s='Welcome to Ever Blog !' mod='everpsblog'}</strong><br />{l s='Use the form below to configure the key parameters of your blog.' mod='everpsblog'}
                </p>
                {if $blog_sitemaps}
                    <div class="dropdown everpsblog-sitemaps">
                      <button class="btn btn-secondary btn-lg dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {l s='View generated sitemaps' mod='everpsblog'}
                      </button>
                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        {foreach from=$blog_sitemaps item=sitemap}
                            {if $sitemap != '.' && $sitemap != '..' && $sitemap != 'index.php' && $sitemap != 'indexes'}
                            <p><a class="dropdown-item" href="{$sitemap|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener">{$sitemap|escape:'htmlall':'UTF-8'}</a></p>
                            {/if}
                        {/foreach}
                      </div>
                    </div>
                {/if}
                <div class="alert alert-info everpsblog-cron">
                    <strong>{l s='Set up these cron tasks to automate maintenance' mod='everpsblog'}</strong>
                    <ul class="list-unstyled everpsblog-cron-list">
                        <li><span class="icon icon-trash"></span> <strong>{l s='Empty trash' mod='everpsblog'} :</strong> <a href="{$everpsblogcron|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener">{$everpsblogcron|escape:'htmlall':'UTF-8'}</a></li>
                        <li><span class="icon icon-calendar"></span> <strong>{l s='Publish planned posts' mod='everpsblog'} :</strong> <a href="{$everpsblogcronplanned|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener">{$everpsblogcronplanned|escape:'htmlall':'UTF-8'}</a></li>
                        <li><span class="icon icon-bell"></span> <strong>{l s='Pending notifications' mod='everpsblog'} :</strong> <a href="{$everpsblogcronpending|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener">{$everpsblogcronpending|escape:'htmlall':'UTF-8'}</a></li>
                        <li><span class="icon icon-sitemap"></span> <strong>{l s='XML sitemaps generation' mod='everpsblog'} :</strong> <a href="{$everpsblogcronsitemap|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener">{$everpsblogcronsitemap|escape:'htmlall':'UTF-8'}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="alert alert-warning everpsblog-support">
                    <p>{l s='This module is free and will always be! You can support our free modules by making a donation using the button below.' mod='everpsblog'}</p>
                    {if isset($everpsblog_support_links) && $everpsblog_support_links}
                        <ul class="list-unstyled everpsblog-support-links">
                            {foreach from=$everpsblog_support_links item=supportLink}
                                <li><a href="{$supportLink.href|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener">{$supportLink.label|escape:'htmlall':'UTF-8'}</a></li>
                            {/foreach}
                        </ul>
                    {/if}
                </div>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="everpsblog-donation">
                    <input type="hidden" name="cmd" value="_s-xclick" />
                    <input type="hidden" name="hosted_button_id" value="3LE8ABFYJKP98" />
                    <input type="image" src="https://www.team-ever.com/wp-content/uploads/2019/06/appel_a_dons-1.jpg" border="0" name="submit" title="{l s='Support Team Ever modules with a donation' mod='everpsblog'}" alt="{l s='Support Team Ever modules with a donation' mod='everpsblog'}" />
                    <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
                </form>
            </div>
        </div>
    </div>
</div>
