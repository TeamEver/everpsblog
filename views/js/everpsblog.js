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
        var $filterForm = $('#everpsblog-filter');
        var $filterButton = $('#everpsblog-filter-submit');
        var $filterLoading = $('#everpsblog-filter-loading');
        var $resetButton = $('#everpsblog-filter-reset');
        var $postsContainer = $('#everpsblog-posts');
        var $activeFilters = $('#everpsblog-active-filters');
        var $activeSummary = $('#everpsblog-active-filters-summary');
        var defaultSummaryText = $activeSummary.data('defaultText');
        var activePrefix = $activeFilters.data('activePrefix');
        var categoryLabel = $activeFilters.data('categoryLabel');
        var tagLabel = $activeFilters.data('tagLabel');
        var emptyText = $postsContainer.data('emptyText') || '';
        var defaultPostsHtml = $postsContainer.length ? $postsContainer.html() : '';

        var updateActiveFilters = function(catValue, tagValue) {
            var activeLabels = [];
            if (catValue && catValue !== '0') {
                activeLabels.push(categoryLabel + ' ' + $('#everpsblog-category option:selected').text());
            }
            if (tagValue && tagValue !== '0') {
                activeLabels.push(tagLabel + ' ' + $('#everpsblog-tag option:selected').text());
            }

            if (activeLabels.length) {
                $activeSummary.text(activePrefix + ' ' + activeLabels.join(' • '));
                $resetButton.prop('disabled', false);
            } else {
                $activeSummary.text(defaultSummaryText);
                $resetButton.prop('disabled', true);
            }
        };

        var toggleLoading = function(isLoading, catValue, tagValue) {
            $filterButton.prop('disabled', isLoading);
            $resetButton.prop('disabled', isLoading ? true : $resetButton.prop('disabled'));

            if (isLoading) {
                $filterLoading.removeClass('d-none');
                return;
            }

            $filterLoading.addClass('d-none');
            updateActiveFilters(catValue, tagValue);
        };

        var renderEmptyState = function() {
            if (!$postsContainer.length) {
                return;
            }
            $postsContainer.html('<div class="alert alert-warning text-center mb-0">' + emptyText + '</div>');
        };

        var fetchFilteredPosts = function(catValue, tagValue) {
            var requestUrl = $filterForm.data('filterUrl') || (typeof facetUrl !== 'undefined' ? facetUrl : '');

            if (!requestUrl) {
                renderEmptyState();
                updateActiveFilters(catValue, tagValue);
                return;
            }

            toggleLoading(true, catValue, tagValue);

            $.ajax({
                url: requestUrl,
                method: 'GET',
                data: {
                    category: catValue,
                    tag: tagValue,
                    ajax: 1
                },
                dataType: 'json'
            }).done(function(resp){
                if (resp && resp.html !== undefined && String(resp.html).trim() !== '') {
                    $postsContainer.html(resp.html);
                } else {
                    renderEmptyState();
                }
            }).fail(function() {
                renderEmptyState();
            }).always(function() {
                toggleLoading(false, catValue, tagValue);
                $(document).trigger('everpsblogAjaxLoaded');
            });
        };

        $filterForm.on('submit', function(event) {
            event.preventDefault();
            var catValue = $('#everpsblog-category').val();
            var tagValue = $('#everpsblog-tag').val();

            if ((!catValue || catValue === '0') && (!tagValue || tagValue === '0')) {
                $postsContainer.html(defaultPostsHtml);
                updateActiveFilters(catValue, tagValue);
                return;
            }

            fetchFilteredPosts(catValue, tagValue);
        });

        $filterButton.on('click', function() {
            $filterForm.trigger('submit');
        });

        $resetButton.on('click', function() {
            if ($resetButton.prop('disabled')) {
                return;
            }
            $filterForm[0].reset();
            $postsContainer.html(defaultPostsHtml);
            updateActiveFilters('0', '0');
        });

        updateActiveFilters($('#everpsblog-category').val(), $('#everpsblog-tag').val());
    }
});
