/**
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
 */
$(document).ready(function(){
    $('.pagination a:not(.disabled)').on('click', function(event) {
        event.preventDefault();
        window.location.href = $(this).prop('href');
        return false;
    });
    if ($('#ever_fancy_mark').length) {
        // Post featured img
        var featured_img = $('#module-everpsblog-post .post-header .post-featured-image');
        var featured_src = featured_img.attr('src');
        var featured_link = $('<a/>').attr('href', featured_src);
        featured_img.wrap(featured_link).parent().addClass('fancybox').attr('rel', 'gallery').fancybox();
        // Post content medias
        $('#module-everpsblog-post .postcontent img').each(function() {
            var $this = $(this);
            var src = $this.attr('src');
            $this.addClass('image');
            var a = $('<a/>').attr('href', src);
            $this.wrap(a).parent().addClass('fancybox').attr('rel', 'gallery').fancybox();
        });
    }
});
