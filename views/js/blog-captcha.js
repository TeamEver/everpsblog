/**
 * Project : EverPsCaptcha
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://team-ever.com
 */

$(document).ready(function(){
    contactform = $(contactform);
    if (contactform.length > 0){
        var captcha = $('<div class="g-recaptcha col-lg-4" data-sitekey="'+ googlecaptchasitekey + '">');
        var submit = contactform.find(submitbutton);
        submit.before(captcha);
        submit.click(function(event) {
            if (contactform.find('#g-recaptcha-response').val().length == 0) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        });
    }
});
