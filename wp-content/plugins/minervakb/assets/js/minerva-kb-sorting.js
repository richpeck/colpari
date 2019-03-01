/**
 * Project: MinervaKB
 * Copyright: 2017-2018 @KonstruktStudio
 */
(function($) {

    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;
    var i18n = GLOBAL_DATA.i18n;

    // containers
    var $wrap = $('.js-mkb-sorting-page-container');
    var $form = $wrap.find('.js-mkb-sorting-form');
    var $termsForm = $wrap.find('.js-mkb-terms-sorting-form');

    function setupDnDSorting() {
        $form.find('.fn-mkb-posts-wrap').each(function(index, el) {
            var $el = $(el);

            $el.sortable({
                items: '.fn-mkb-sorting-tree-post',
                axis: 'y'
            });
        })
    }

    function setupTermsDnDSorting() {
        $termsForm.find('ul').each(function(index, item) {
            $(item).sortable({
                items: '>li',
                axis: 'y'
            });
        })
    }

    function getSortingSaveHandler(sortingDataGetter) {
        return function handleSortingSave(e) {
            e.preventDefault();

            var $saveBtn = $(e.currentTarget);

            if ($saveBtn.hasClass('mkb-disabled')) {
                return;
            }

            $saveBtn.addClass('mkb-disabled');

            ui.fetch(sortingDataGetter()).always(function(response) {
                var text = $saveBtn.text();

                if (response.status == 1) {
                    // error

                    $saveBtn.text('Error');
                    $saveBtn.removeClass('mkb-disabled').addClass('mkb-action-danger');

                    ui.handleErrors(response);

                } else {
                    // success

                    $saveBtn.text('Success!');
                    $saveBtn.removeClass('mkb-disabled').addClass('mkb-success');

                    toastr.success('Order has been updated');
                }

                setTimeout(function() {
                    $saveBtn.text(text);
                    $saveBtn.removeClass('mkb-success mkb-action-danger');
                }, 700);
            }).fail(function() {
                toastr.error('Some error happened, try to refresh page');
            });
        }
    }

    function getTermsSortingRequestData() {
        var $tree = $termsForm.find('.fn-mkb-sorting-tree');
        var $terms = $tree.find('.fn-mkb-sorting-tree-item');
        var order = 0;

        var store = [].reduce.call($terms, function(acc, item) {
            var $el = $(item);
            var termId = $el.data('id');

            acc[termId] = order;
            ++order;

            return acc;
        }, {});

        return {
            action: 'mkb_save_terms_sorting',
            taxonomy: $termsForm.data('taxonomy'),
            sorting: store
        };
    }

    function getArticlesSortingRequestData() {
        var tax = $form.data('taxonomy');
        var $tree = $form.find('.fn-mkb-sorting-tree');
        var $terms = $tree.find('.fn-mkb-posts-wrap');
        var store = [].reduce.call($terms, function(acc, item) {
            var $el = $(item);
            var termId = $el.data('termId');

            acc[termId] = [].map.call($el.find('.fn-mkb-sorting-tree-post'), function(post) {
                return post.dataset.id;
            });

            return acc;
        }, {});

        return {
            action: 'mkb_save_sorting',
            taxonomy: tax,
            sorting: store
        };
    }

    /**
     * Init
     */
    function init() {
        setupDnDSorting();
        setupTermsDnDSorting();

        $('.js-mkb-articles-sorting-save').on('click', getSortingSaveHandler(getArticlesSortingRequestData));
        $('.js-mkb-faq-sorting-save').on('click', getSortingSaveHandler(getArticlesSortingRequestData));
        $('.js-mkb-terms-sorting-save').on('click', getSortingSaveHandler(getTermsSortingRequestData));

        ui.setupTabs($wrap);

        $form.removeClass('mkb-loading');
        $termsForm.removeClass('mkb-loading');
    }

    $(document).ready(init);
})(jQuery);