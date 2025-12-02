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
    var $imageModal = $('#everpsblog-image-modal');
    if ($imageModal.length) {
        var $modalImg = $imageModal.find('.modal-body img');
        var $modalTitle = $imageModal.find('.modal-title');

        var openModal = function(src, alt) {
            $modalImg.attr('src', src || '');
            $modalImg.attr('alt', alt || '');
            $modalTitle.text(alt || '');
            $imageModal.modal('show');
        };

        // Post featured img
        $('#module-everpsblog-post .post-header .post-featured-image').each(function() {
            var $image = $(this);
            var src = $image.attr('src');
            $image.addClass('img-fluid').css('cursor', 'pointer');
            $image.on('click', function() {
                openModal(src, $image.attr('alt'));
            });
        });

        // Post content medias
        $('#module-everpsblog-post .postcontent img').each(function() {
            var $this = $(this);
            $this.addClass('image img-fluid').css('cursor', 'pointer');
            $this.on('click', function() {
                openModal($this.attr('src'), $this.attr('alt'));
            });
        });
    }

    if ($('#everpsblog-filter').length) {
        $('#everpsblog-filter-submit').on('click', function() {
            var cat = $('#everpsblog-category').val();
            var tag = $('#everpsblog-tag').val();
            if ((!cat || cat === '0') && (!tag || tag === '0')) {
                return;
            }
            $.ajax({
                url: typeof facetUrl !== 'undefined' ? facetUrl : '',
                method: 'GET',
                data: {
                    category: cat,
                    tag: tag,
                    ajax: 1
                },
                dataType: 'json'
            }).done(function(resp){
                if (resp.html !== undefined) {
                    $('#everpsblog-posts').html(resp.html);
                    $(document).trigger('everpsblogAjaxLoaded');
                }
            });
        });
    }
});
