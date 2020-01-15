{*
* Project : everpsseo
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}
<div>
	<h3>{l s='An error occurred' mod='everpsseo'}:</h3>
	<ul class="alert alert-danger">
		{foreach from=$errors item='error'}
			<li>{$error|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
	</ul>
</div>