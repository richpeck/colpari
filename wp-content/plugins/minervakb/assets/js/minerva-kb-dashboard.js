/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {

    var GLOBAL_DATA = window.MinervaKB;
    var DASHBOARD_DATA = window.MinervaDashboard;
    var ui = window.MinervaUI;
    var i18n = GLOBAL_DATA.i18n;

    var graphDates = DASHBOARD_DATA.graphDates;
    var graphViews = DASHBOARD_DATA.graphViews;
    var graphLikes = DASHBOARD_DATA.graphLikes;
    var graphDislikes = DASHBOARD_DATA.graphDislikes;

    var $dashboard = $('#mkb-dashboard');
    var $chartHolder = $('.mkb-chart-holder');
    var $period = $('#mkb-analytics-period');

    var DASHBOARD_TAB_KEY = 'mkb_last_dashboard_tab';

    function initCounters() {
        var $counters = $('.fn-mkb-counter');

        $counters.each(function(index, el) {
            var $counter = $(el);

            var numAnim = new CountUp($counter.attr('id'), 0, parseInt($counter.data('target'), 10));
            numAnim.start();
        });
    }

    function initAnalytics() {
        var BORDER_WIDTH = 3;

        var ctx = document.getElementById("mkb-analytics-canvas");
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: graphDates.map(function(item) { return item['label']; }),
                datasets: [
                    {
                        label: 'Dislikes',
                        data: graphDislikes,
                        backgroundColor: 'rgba(200, 92, 94, 0.7)',
                        borderColor: 'rgba(200, 92, 94, 1)',
                        borderWidth: BORDER_WIDTH
                    },
                    {
                        label: 'Likes',
                        data: graphLikes,
                        backgroundColor: 'rgba(75, 182, 81, 0.7)',
                        borderColor: 'rgba(75, 182, 81, 1)',
                        borderWidth: BORDER_WIDTH
                    },
                    {
                        label: 'Views',
                        data: graphViews,
                        backgroundColor: 'rgba(0, 115, 170, 0.7)',
                        borderColor: 'rgba(0, 115, 170, 1)',
                        borderWidth: BORDER_WIDTH
                    }
                ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }

    function onPeriodChange(e) {
        var $select = $(e.currentTarget);
        var period = $select.val();

        $chartHolder.addClass('mkb-request');
        $select.attr('disabled', 'disabled');

        ui.fetch({
            action: 'mkb_get_' + period + '_analytics'
        }).done(function(response) {
            graphDates = response.graphDates;
            graphViews = response.graphViews;
            graphLikes = response.graphLikes;
            graphDislikes = response.graphDislikes;

            initAnalytics();
        }).always(function(response) {
            $chartHolder.removeClass('mkb-request');
            $select.attr('disabled', false);
        });
    }

    function initPeriodSelect() {
        $period.on('change', onPeriodChange);
    }

    function showHitResults($el, results) {
        $el.html($(
            '<ul class="mkb-search-results-list">' +
                results.reduce(function (store, result, index) {
                    return store +
                    '<li>' +
                        '<span class="mkb-search-result-index">' + (index + 1) + '</span>' +
                        '<a class="mkb-unstyled-link" href="' +
                            result.link +
                            '" target="_blank">' +
                            result.title +
                        '</a>' +
                    '</li>';
                }, '') +
            '</ul>'
        ));
    }

    /**
     * Dashboard tabs functionality
     */
    function initDashboardTabs() {
        $dashboard.on('click', '.fn-dashboard-tab-link', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);

            ui.storage.set(DASHBOARD_TAB_KEY, $link.attr('href'));

            $('.fn-dashboard-tab-link').removeClass('mkb-dashboard__tabs-list-item--active');
            $link.addClass('mkb-dashboard__tabs-list-item--active');

            $('.mkb-dashboard-page').removeClass('mkb-dashboard-page--active');
            $($link.attr('href')).addClass('mkb-dashboard-page--active')
        });

        // restore last tab
        var lastTab = ui.storage.get(DASHBOARD_TAB_KEY);

        if (lastTab) {
            $dashboard.find('.fn-dashboard-tab-link[href="' + lastTab + '"]').trigger('click');
        }
    }

    /**
     * Search analytics
     */
    function initSearch() {
        var hitResultsCache = {};
        var hitStatsPagesCache = [];
        var pageCache = 0;
        var orderCache = {
            field: 'hits',
            order: 'DESC'
        };

        // renders search pagination results
        function renderSearchStats(items) {
            var $searchTable = $('.mkb-dashboard__search-table');
            var newItems = '';

            newItems = items.reduce(function(html, item) {
                return html +
                '<tr class="mkb-dashboard__search-item-row">'+
                '<td class="mkb-dashboard__search-keyword">' + item.keyword + '</td>' +
                '<td class="mkb-dashboard__search-hit-count">' + item.hit_count + '</td>' +

                '<td class="mkb-dashboard__search-results">' + (
                    parseInt(item.last_results, 10) !== 0 ?
                        (
                        '<span class="fn-search-results-container mkb-search-results-container">' +
                        '<a class="fn-show-search-results show-search-results mkb-unstyled-link" href="#" data-hit-id="' + item.hit_id + '">' +
                        item.last_results +
                        ' <i class="fa fa-eye"></i>' +
                        '</a>' +
                        '<span class="fn-search-results mkb-search-results"></span>' +
                        '</span>'
                        ) :
                        item.last_results
                ) + '</td>' +
                '<td class="mkb-dashboard__search-last-date">' + item.last_search + '</td>' +
                '</tr>'
            }, '');

            $searchTable.find('.mkb-dashboard__search-item-row').remove();

            $searchTable.append($(newItems));
        }

        // show search results
        $dashboard.on('mouseenter', '.fn-show-search-results', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);
            var $results = $link.parents('.fn-search-results-container').find('.fn-search-results');
            var hitId = parseInt($link.data('hit-id'), 10);

            if (hitResultsCache[hitId]) {
                showHitResults($results, hitResultsCache[hitId]);
                return;
            }

            $results.html($('<p class="mkb-search-results-loading">' + i18n['loading'] + '</p>'));

            ui.fetch({
                action: 'mkb_get_hit_results',
                hit_id: hitId
            }).then(function(response) {
                hitResultsCache[hitId] = response.articles;

                showHitResults($results, hitResultsCache[hitId]);
            });
        });

        // hide results
        $dashboard.on('mouseleave', '.fn-search-results-container', function(e) {
            e.preventDefault();

            var $el = $(e.currentTarget);
            var $results = $el.find('.fn-search-results');

            $results.html('');
        });

        // reorder search stats
        $dashboard.on('click', '.fn-reorder-search-results', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);
            var field = $link.data('field');
            var order = $link.data('order');
            var $searchTable = $('.mkb-dashboard__search-table');

            // clear pages cache
            hitStatsPagesCache = [];
            $searchTable.addClass('mkb-data-loading');

            ui.fetch({
                action: 'mkb_get_ordered_search_stats',
                field: field,
                order: order,
                page: pageCache
            }).then(function(response) {
                $dashboard.find('.fn-reorder-search-results').removeClass('reorder-search-results--active');
                    $link.toggleClass('reorder-search-results--asc').addClass('reorder-search-results--active');

                $link.data('order', order === 'ASC' ? 'DESC' : 'ASC');

                orderCache = {
                    field: field,
                    order: order
                };

                hitStatsPagesCache[pageCache] = response.stats;
                renderSearchStats(hitStatsPagesCache[pageCache]);

                $searchTable.removeClass('mkb-data-loading');
            });
        });

        // pagination
        $dashboard.on('click', '.fn-search-pagination-item', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);
            var $searchTable = $('.mkb-dashboard__search-table');
            var page = parseInt($link.data('page'), 10);

            if ($link.hasClass('mkb-pagination-item--active')) {
                return;
            }

            $dashboard.find('.fn-search-pagination-item').removeClass('mkb-pagination-item--active');
            $link.addClass('mkb-pagination-item--active');

            $searchTable.addClass('mkb-data-loading');

            if (hitStatsPagesCache[page]) {
                renderSearchStats(hitStatsPagesCache[page]);
                pageCache = page;

                $searchTable.removeClass('mkb-data-loading');
                return;
            }

            ui.fetch({
                action: 'mkb_get_search_stats_page',
                field: orderCache.field,
                order: orderCache.order,
                page: page
            }).then(function(response) {
                hitStatsPagesCache[page] = response.stats;
                renderSearchStats(hitStatsPagesCache[page]);

                pageCache = page;

                $searchTable.removeClass('mkb-data-loading');
            });
        });
    }

    /**
     * Feedback handlers
     */
    function initFeedback() {
        $dashboard.on('click', '.fn-remove-feedback', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);
            var $row = $link.parents('.mkb-dashboard__feedback-item-row');

            $row.addClass('mkb-dashboard__feedback-item-row--removing');

            ui.fetch({
                action: 'mkb_remove_feedback',
                feedback_id: parseInt($link.data('id'))
            }).then(function() {
                $row.slideUp('fast', function() {
                    $row.remove();
                });
            });
        });
    }

    /**
     * Resets dashboard stats
     */
    function initReset() {
        $('.fn-mkb-reset-stats-btn').on('click', function(e) {
            e.preventDefault();

            var resetConfig = ui.getFormData($('.fn-mkb-dashboard-reset-form'));

            if(!Object.keys(resetConfig).filter(function(key) {
                return resetConfig[key] === true;
            }).length) {
                return;
            }

            if (!confirm('Confirm data reset')) {
                return;
            }

            ui.fetch({
                action: 'mkb_reset_stats',
                resetConfig: resetConfig
            }).then(function(response) {
                if (response.status == 0) {
                    toastr.success('Data was reset successfully. Refresh the dashboard page to see changes.');
                } else {
                    toastr.error('Could not reset data, try to refresh the page');
                }
            });
        });
    }

    function init() {
        initDashboardTabs();
        initFeedback();
        initSearch();
        initCounters();
        initAnalytics();
        initPeriodSelect();
        initReset();

        toastr.options.positionClass = "toast-top-right";
        toastr.options.timeOut = 5000;
        toastr.options.showDuration = 200;
    }

    $(document).ready(init);
})(jQuery);