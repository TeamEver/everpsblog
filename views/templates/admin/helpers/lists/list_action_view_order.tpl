{*
* Project : everpsblog
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

<a href="{$href|escape:'html':'UTF-8'}"{if isset($confirm)} onclick="if (confirm('{$confirm}')){ldelim}return true;{rdelim}else{ldelim}event.stopPropagation(); event.preventDefault();{rdelim};"{/if} title="{$action|escape:'html':'UTF-8'}" target="_blank">
	<i class="icon-file"></i> {$action|escape:'html':'UTF-8'}
</a>