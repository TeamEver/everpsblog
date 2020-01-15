{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
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
