/**
 * Project : everpsblog
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://team-ever.com
 */
$(document).ready(function(){
    if ($('#ever_ck_mark').length) {
        CKEDITOR.replace( 'evercomment' );
    }
    if ($('#ever_fancy_mark').length) {
        $('#module-everpsblog-post .postcontent img').each(function() {
            var $this = $(this);
            var src = $this.attr('src');
            $this.addClass('image');
            var a = $('<a/>').attr('href', src);
            $this.wrap(a).parent().addClass('fancybox').attr('rel', 'gallery').fancybox();
        });
    }
});