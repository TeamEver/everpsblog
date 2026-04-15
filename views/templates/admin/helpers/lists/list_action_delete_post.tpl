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

<form method="post" action="{$href|escape:'htmlall':'UTF-8'}" style="display:inline;">
	<button type="submit" class="btn btn-link p-0" title="{$action|escape:'htmlall':'UTF-8'}"{if isset($confirm)} onclick="return confirm('{$confirm|escape:'htmlall':'UTF-8'}');"{/if}>
		<i class="icon-trash"></i> {$action|escape:'htmlall':'UTF-8'}
	</button>
</form>
