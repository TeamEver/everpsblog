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
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:creativeCommons="http://backend.userland.com/creativeCommonsRssModule" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" version="2.0">
<channel>
    <title>{$feed_obj->title|escape:'htmlall':'UTF-8'}</title>
    <atom:link href="{$feed_url|escape:'htmlall':'UTF-8'}" rel="self" type="application/rss+xml" />
    <link>{$feed_obj->feed_link|escape:'htmlall':'UTF-8'}</link>
    <description>{$feed_obj->feed_description|escape:'htmlall':'UTF-8'}</description>
    <language>{$locale|escape:'htmlall':'UTF-8'}</language>
    {foreach from=$posts item=item}
    <item>
       <title>{$item->title|escape:'htmlall':'UTF-8'}</title>
       <link>{$item->feed_link|escape:'htmlall':'UTF-8'}</link>
       <guid isPermaLink="true">{$item->feed_link|escape:'htmlall':'UTF-8'}</guid>
       <pubDate>{$item->feed_pub_date|escape:'htmlall':'UTF-8'}</pubDate>
       <description>{$item->feed_description|escape:'htmlall':'UTF-8'}</description>
       <content:encoded><![CDATA[{$item->feed_content nofilter}]]></content:encoded>
    </item>
    {/foreach}
</channel>
</rss>
