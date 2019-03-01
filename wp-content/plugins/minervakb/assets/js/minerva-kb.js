/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function ($) {

    var GLOBAL_DATA = window.MinervaKB;

    var i18n = GLOBAL_DATA.i18n;
    var platform = GLOBAL_DATA.platform;
    var settings = GLOBAL_DATA.settings;
    var info = GLOBAL_DATA.info;

    var $body = $('body');

    /**
     * libs
     */
    if (!String.prototype.trim) {
        String.prototype.trim = function () {
            return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
        };
    }

    /**
     * Debounces function execution
     * TODO: make shared utils lib
     * @param func
     * @param wait
     * @param immediate
     * @returns {Function}
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) {
                    func.apply(context, args);
                }
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) {
                func.apply(context, args);
            }
        };
    }

    /**
     * Throttles function execution. Based on Ben Alman implementation
     * TODO: make shared utils lib
     * @param delay
     * @param noTrailing
     * @param callback
     * @param atBegin
     * @returns {wrapper}
     */
    function throttle(delay, noTrailing, callback, atBegin) {
        var timeoutId;
        var lastExec = 0;

        if (typeof noTrailing !== 'boolean') {
            atBegin = callback;
            callback = noTrailing;
            noTrailing = undefined;
        }

        function wrapper() {
            var elapsed = +new Date() - lastExec;
            var args = arguments;

            var exec = function _exec() {
                lastExec = +new Date();
                callback.apply(this, args );
            }.bind(this);

            function clear() {
                timeoutId = undefined;
            }

            if (atBegin && !timeoutId) {
                exec();
            }

            timeoutId && clearTimeout(timeoutId);

            if (atBegin === undefined && elapsed > delay) {
                exec();
            } else if (noTrailing !== true) {
                timeoutId = setTimeout(
                    atBegin ?
                        clear :
                        exec,
                    atBegin === undefined ?
                    delay - elapsed :
                        delay
                );
            }
        }

        return wrapper;
    }

    function addAjaxNonce(data) {
        data['nonce_key'] = GLOBAL_DATA.nonce.nonceKey;
        data['nonce_value'] = GLOBAL_DATA.nonce.nonce;

        return data;
    }

    /**
     * Sends Google Analytics event, if API available
     * @param category
     * @param action
     * @param label
     * @param value
     */
    function trackGoogleAnalytics(category, action, label, value) {
        if (window.ga && typeof window.ga === 'function') {
            ga('send', 'event', category, action, label, value, {
                nonInteraction: true
            });
        }
    }

    // theme
    var ajaxUrl = GLOBAL_DATA.ajaxUrl;
    var $kbSearch = $('.kb-search__input');
    var NO_RESULTS_CLASS = 'kb-search__input-wrap--no-results';
    var HAS_CONTENT_CLASS = 'kb-search__input-wrap--has-content';
    var HAS_RESULTS_CLASS = 'kb-search__input-wrap--has-results';
    var REQUEST_CLASS = 'kb-search__input-wrap--request';
    var hasResults = false;
    var resultsCount = 0;
    var activeResult = -1;
    var ESC = 27;
    var ENTER = 13;
    var ARROW_LEFT = 37;
    var ARROW_UP = 38;
    var ARROW_RIGHT = 39;
    var ARROW_DOWN = 40;
    var $doc = $('html, body');
    var $adminBar = $('#wpadminbar');
    var adminOffset = $adminBar.length ? $adminBar.height() : 0;
    var searchMode = settings['search_mode'];
    var searchNeedleLength = parseInt(settings['search_needle_length']);
    var searchRequestsCount = 0;
    var searchCache = {};
    var trackingCache = {};

    /**
     * Live search result handler
     * @param $search
     * @param response
     */
    function handleSearchResultsReceive($search, response) {
        var $wrap = $search.parents('.kb-search__input-wrap');
        var $summary = $wrap.find('.kb-summary-text-holder');
        var $results = $wrap.find('.kb-search__results');
        var results = response.result;
        var searchNeedle = response.search;
        var resultsContent;
        var resultsInfoHtml = response.results_info || '';
        var searchShowTopics = $search.data('show-results-topic') === 1;
        var showTopicsLabel = $search.data('topic-label');
        var useCustomTopicColors = Boolean($search.data('custom-topic-colors'));
        var showExcerpt = settings['live_search_show_excerpt'];

        if (searchMode === 'nonblocking') {
            var needle = $search.val() && $search.val().trim();

            if (!needle || needle.length < searchNeedleLength) {
                // in nonblocking mode user could have already removed the typed string

                results = [];
                hasResults = false;
                resultsCount = 0;
                activeResult = -1;
                $wrap.removeClass(HAS_RESULTS_CLASS).removeClass(NO_RESULTS_CLASS);
                $summary.html('');
                $results.html('');

                return;
            }
        }

        if (results && results.length) {
            if (settings['track_search_with_results']) {
                trackGoogleAnalytics(
                    settings['ga_good_search_category'],
                    settings['ga_good_search_action'],
                    searchNeedle,
                    settings['ga_good_search_value'] || 0
                );
            }

            hasResults = true;
            resultsCount = results.length;
            activeResult = -1;
            $wrap.removeClass(NO_RESULTS_CLASS).addClass(HAS_RESULTS_CLASS);
            $summary.html(results.length + ' ' + (results.length === 1 ? i18n['result'] : i18n['results']));
            resultsContent = results.reduce(function ($el, result) {
                var isTopicPresent = Boolean(result.topics[0]);
                var isProductArticle = Boolean(result.product);
                var firstTopic = result.topics[0];
                var topicColorStyle = isTopicPresent && useCustomTopicColors ?
                    'style="background-color: ' + firstTopic.color + '"' :
                    '';

                return $el.append(
                    '<li>' +
                        '<a href="' + result.link + '">' +
                            '<span class="kb-search__result-header">' +
                                '<span class="kb-search__result-title">' +
                                    result.title +
                                '</span>' +
                                (searchShowTopics && isTopicPresent ?
                                    '<span class="kb-search__result-topic">' +
                                        '<span class="kb-search__result-topic-label">' + showTopicsLabel + '</span>' +
                                        '<span class="kb-search__result-topic-name" ' + topicColorStyle + '>' +
                                            (isProductArticle && result.product !== firstTopic.name ?
                                                result.product + ' / ' : '') + (firstTopic.name)  +
                                        '</span>' +
                                    '</span>' :
                                    '') +
                            '</span>' +
                            (showExcerpt ? '<span class="kb-search__result-excerpt">' + result.excerpt + ' ...</span>' : '') +
                        '</a>' +
                    '</li>'
                );
            }, $('<ul></ul>'));

            $results.html('');
            $results.append(resultsInfoHtml);
            $results.append(resultsContent);
        } else {
            if (settings['track_search_without_results']) {
                trackGoogleAnalytics(
                    settings['ga_bad_search_category'],
                    settings['ga_bad_search_action'],
                    searchNeedle,
                    settings['ga_bad_search_value'] || 0
                );
            }

            hasResults = false;
            resultsCount = 0;
            activeResult = -1;
            $wrap.removeClass(HAS_RESULTS_CLASS).addClass(NO_RESULTS_CLASS);
            $summary.html(i18n['no-results']);
            $results.html('');
        }
    }

    function focusInput() {
        $kbSearch.filter('[data-autofocus="1"]').focus();
    }

    function nextSearchResult() {
        var $resultItems = $('.kb-search__results li a');

        activeResult = activeResult + 1 >= resultsCount ? 0 : activeResult + 1;
        $resultItems.eq(activeResult).focus();
    }

    function prevSearchResult() {
        var $resultItems = $('.kb-search__results li a');

        activeResult = activeResult - 1 < 0 ? resultsCount - 1 : activeResult - 1;
        $resultItems.eq(activeResult).focus();
    }

    /**
     * Live search keypress handler
     * @param e
     */
    function onSearchKeyPress(e) {

        if (!$(".kb-search__input").is(":focus") && !$(".kb-search__results a").is(":focus")) {
            return; //we do not to mess with keypress unless search is in focus
        }

        var $search = $(".kb-search__input:focus");

        switch (e.keyCode) {
            case ESC:
                focusInput();
                break;

            case ARROW_UP:
                prevSearchResult();
                break;

            case ARROW_DOWN:
                nextSearchResult();
                break;

            case ENTER:
                if ($search.length && !$search.val().trim()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
                return;

            default:
                return;
        }

        e.preventDefault(); // prevent the default action (scroll / move caret)
    }

    function serializeRequestParams(params) {
        var serialized = "";

        for (var key in params) {
            if (serialized !== "") {
                serialized += "&";
            }

            serialized += key + "=" + encodeURIComponent(params[key]);
        }

        return serialized;
    }



    /**
     * Search request stats is saved separately for non-blocking search
     */
    function trackSearchRequest(searchParams) {

        searchParams.trackResults = true;

        return $.ajax({
            method: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: searchParams
        });
    }

    /**
     * Waits for a timeout untill tracking results
     * @param searchParams
     */
    var trackingTimerId = null;

    function handleNonBlockingTracking(searchParams) {
        var serializedParams = serializeRequestParams(searchParams);

        if (trackingTimerId) {
            clearTimeout(trackingTimerId);
        }

        if (trackingCache[serializedParams]) {
            return;
        }

        trackingTimerId = setTimeout(function() {
            trackSearchRequest(searchParams).then(function() {
                trackingCache[serializedParams] = true;
            });
        }, 1000);
    }

    /**
     * Lice search type handler
     * @param $search
     */
    function onSearchType($search) {
        var $wrap = $search.parents('.kb-search__input-wrap');
        var needle = $search.val() && $search.val().trim();
        var $topics = $wrap.find('input[name="topics"]');
        var topics = $topics.length ? $topics.val() : null;
        var $kbId = $wrap.find('input[name="kb_id"]');
        var $lang = $wrap.find('input[name="lang"]');
        var kbId = $kbId.length ? parseInt($kbId.val()) : null;
        var searchParams = {
            action: 'mkb_kb_search',
            search: needle,
            mode: searchMode
        };

        if (kbId) {
            searchParams.kb_id = kbId;
        }

        if (topics) {
            searchParams.topics = topics;
        }

        if ($lang.length && $lang.val()) {
            searchParams.lang = $lang.val();
        }

        var serializedParams = serializeRequestParams(searchParams);

        if (needle) {
            $wrap.addClass(HAS_CONTENT_CLASS);
        } else {
            $wrap.removeClass(HAS_CONTENT_CLASS);
        }

        // check cache for response
        if (settings['search_request_fe_cache'] && searchCache[serializedParams]) {
            // track cached result, if it wasn tracked before
            if (searchMode === 'nonblocking') {
                handleNonBlockingTracking(searchParams);
            }

            return handleSearchResultsReceive.call(this, $search, searchCache[serializedParams]);
        }

        if (!needle || needle.length < searchNeedleLength) {
            hasResults = false;
            resultsCount = 0;
            activeResult = -1;
            $wrap.removeClass(HAS_RESULTS_CLASS).removeClass(NO_RESULTS_CLASS);

            if (searchMode === 'nonblocking' && needle.length > 0) {
                fakeRequest($wrap); // progress indicator for input to be more responsive
            }

            return;
        }

        if (searchMode === 'nonblocking') {
            handleNonBlockingTracking(searchParams);
        }

        if (searchMode === 'blocking') {
            $search.attr('disabled', 'disabled');
        }

        $wrap.addClass(REQUEST_CLASS);
        ++searchRequestsCount;

        $.ajax({
            method: settings['live_search_use_post'] ? 'POST' : 'GET',
            url: ajaxUrl,
            dataType: 'json',
            data: searchParams
        })
            .then(function(response) {
                if (settings['search_request_fe_cache']) {
                    searchCache[serializedParams] = response;
                }

                return handleSearchResultsReceive.call(this, $search, response);
            }.bind(this))
            .always(function () {
                if (searchMode === 'blocking') {
                    $search
                        .attr('disabled', false)
                        .focus();
                }

                --searchRequestsCount;

                if (searchRequestsCount === 0) {
                    $wrap.removeClass(REQUEST_CLASS);
                }
            });
    }

    /**
     * Progress indicator for short requests
     * @param $wrap
     */
    function fakeRequest($wrap) {
        $wrap.addClass(REQUEST_CLASS);
        ++searchRequestsCount;

        setTimeout(function() {
            --searchRequestsCount;
            if (searchRequestsCount === 0) {
                $wrap.removeClass(REQUEST_CLASS);
            }
        }, 500);
    }

    /**
     * Article pageview tracking
     */
    function trackArticleView() {
        var $tracking_meta = $('.mkb-article-extra__tracking-data');

        if (!$tracking_meta.length) {
            return;
        }

        var $id = $tracking_meta.data('article-id');

        if (!$id) {
            return;
        }

        jQuery.ajax({
            method: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: addAjaxNonce({
                action: 'mkb_article_pageview',
                id: $id
            })
        });
    }

    /**
     * Article like
     * @param e
     */
    function handleArticleLike(e) {
        e.preventDefault();

        var $likeBtn = $(e.currentTarget);
        var id = $likeBtn.data('article-id');
        var title = $likeBtn.data('article-title');
        var $count = $('.mkb-article-extra__stats-likes');
        var likes = parseInt($count.text(), 10);

        if (!id || $likeBtn.hasClass('mkb-voted') || $likeBtn.hasClass('mkb-disabled')) {
            return;
        }

        $likeBtn.addClass('mkb-voted');
        $('.mkb-article-extra__dislike').addClass('mkb-disabled');
        $count.text(++likes);

        jQuery.ajax({
            method: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: addAjaxNonce({
                action: 'mkb_article_like',
                id: id
            })
        }).done(function() {
            if (settings['track_article_likes']) {
                trackGoogleAnalytics(
                    settings['ga_like_category'],
                    settings['ga_like_action'],
                    settings['ga_like_label'] === 'article_title' ? title : id,
                    settings['ga_like_value'] || 0
                );
            }

            if (settings['show_like_message']) {
                $('.fn-rating-likes-block')
                    .html($('<div class="mkb-article-extra__message">' + i18n['like_message_text'] + '</div>'));
            }

            if (settings['enable_feedback'] &&
                (settings['feedback_mode'] === 'like' || settings['feedback_mode'] === 'any')) {
                addFeedbackForm();
            }
        });
    }

    /**
     * Article dislike
     * @param e
     */
    function handleArticleDislike(e) {
        e.preventDefault();

        var $dislikeBtn = $(e.currentTarget);
        var id = $dislikeBtn.data('article-id');
        var title = $dislikeBtn.data('article-title');
        var $count = $('.mkb-article-extra__stats-dislikes');
        var dislikes = parseInt($count.text(), 10);

        if (!id || $dislikeBtn.hasClass('mkb-voted') || $dislikeBtn.hasClass('mkb-disabled')) {
            return;
        }

        $dislikeBtn.addClass('mkb-voted');
        $('.mkb-article-extra__like').addClass('mkb-disabled');
        $count.text(++dislikes);

        jQuery.ajax({
            method: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: addAjaxNonce({
                action: 'mkb_article_dislike',
                id: id
            })
        }).done(function() {
            if (settings['track_article_dislikes']) {
                trackGoogleAnalytics(
                    settings['ga_dislike_category'],
                    settings['ga_dislike_action'],
                    settings['ga_dislike_label'] === 'article_title' ? title : id,
                    settings['ga_dislike_value'] || 0
                );
            }

            if (settings['show_dislike_message']) {
                $('.fn-rating-likes-block')
                    .html($('<div class="mkb-article-extra__message">' + i18n['dislike_message_text'] + '</div>'));
            }

            if (settings['enable_feedback'] &&
                (settings['feedback_mode'] === 'dislike' || settings['feedback_mode'] === 'any')) {
                addFeedbackForm();
            }
        });
    }

    /**
     * Renders feedback form if configured
     */
    function addFeedbackForm() {
        $('.fn-article-feedback-container').append($(
            '<div class="mkb-article-extra__feedback-form mkb-article-extra__feedback-form--no-content fn-feedback-form">' +
                '<div class="mkb-article-extra__feedback-form-title">' +
                i18n['feedback_label'] +
                '</div>' +
                '<div class="mkb-article-extra__feedback-form-message">' +
                '<textarea class="mkb-article-extra__feedback-form-message-area" rows="5"></textarea>' +
                (i18n['feedback_info_text'] ?
                    ('<div class="mkb-article-extra__feedback-info">' + i18n['feedback_info_text'] + '</div>') :
                    '') +
                '</div>' +
                '<div class="mkb-article-extra__feedback-form-submit">' +
                '<a href="#">' + i18n['feedback_submit_label'] + '</a>' +
                '</div>' +
            '</div>'
        ));
    }

    /**
     * Sends article feedback to server
     * @param e
     */
    function handleFeedbackSubmit(e) {
        var $trackingMeta = $('.mkb-article-extra__tracking-data');

        e.preventDefault();

        if (!$trackingMeta.length) {
            return;
        }

        var id = $trackingMeta.data('article-id');
        var title = $trackingMeta.data('article-title');
        var $btn = $(e.target);
        var $content = $('.mkb-article-extra__feedback-form-message-area');

        if (!id || !$content.val()) {
            return;
        }

        $btn
            .text(i18n['feedback_submit_request_label'])
            .attr('disabled', 'disabled');

        jQuery.ajax({
            method: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: addAjaxNonce({
                action: 'mkb_article_feedback',
                id: id,
                content: $content.val()
            })
        }).done(function() {
            if (settings['track_article_feedback']) {
                trackGoogleAnalytics(
                    settings['ga_feedback_category'],
                    settings['ga_feedback_action'],
                    settings['ga_feedback_label'] === 'article_title' ? title : id,
                    settings['ga_feedback_value'] || 0
                );
            }

            $('.fn-article-feedback-container').html(
                '<div class="mkb-article-extra__feedback-sent-message">' +
                i18n['feedback_sent_text'] +
                '</div>'
            );
        });
    }

    /**
     * Toggle submit available
     * @param e
     */
    function handleFeedbackType(e) {
        $('.fn-feedback-form').toggleClass('mkb-article-extra__feedback-form--no-content', Boolean($(e.currentTarget).val() < 1));
    }

    /**
     * Back to top in articles
     */
    function handleArticleBackToTop() {
        var $container = $('.mkb-container');

        $container.on('click', '.mkb-back-to-top', function (e) {
            e.preventDefault();

            $doc.animate({
                scrollTop: settings['back_to_site_top'] ? 0 : $container.offset().top - adminOffset
            }, 300);

            window.location.hash = '';
        });
    }

    /**
     * Article Table of Contents
     */
    function handleArticleTOC() {
        var $entryContent = $('.mkb-article-text');
        var $tocList = $('.mkb-anchors-list');
        var scrollOffset = parseInt(settings['toc_scroll_offset']['size']);
        var headingsExclude = settings['toc_headings_exclude'].trim().toLowerCase();
        var $headings;
        var isScrollSpy = settings['scrollspy_switch'] && settings['toc_in_content_disable'] &&
            platform === 'desktop' &&
            window.outerWidth >= parseInt(settings['article_sidebar_sticky_min_width']['size'], 10);

        // dynamic TOC
        if ($tocList.hasClass('mkb-anchors-list--dynamic')) {
            var headingsPool = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

            if (headingsExclude) {
                var excluded = headingsExclude.split(',').map(function(heading) {
                    return heading.trim();
                }).filter(function(heading) {
                    return Boolean(heading);
                });

                headingsPool = headingsPool.filter(function(heading) {
                    return excluded.indexOf(heading) === -1;
                });
            }

            $headings = $entryContent.find(headingsPool.join(', '));

            if (settings['show_back_to_top']) {
                wrapTOCAnchors($headings);
            }

        } else { // via shortcodes
            $headings = $('.mkb-anchor__title');
        }

        // scroll
        var headingOffsets = Array.prototype.map.call($headings, function(heading) {
            return $(heading).offset().top;
        });
        var $links = $tocList.find('.mkb-anchors-list__item-link');
        var linksArr = Array.prototype.map.call($links, function(link) {
            return link;
        }).filter(function(link) {
            return link.dataset.index !== '-1'; // other pages links
        });

        function recalculateMetrics() {
            headingOffsets = Array.prototype.map.call($headings, function(heading) {
                return $(heading).offset().top;
            });
        }

        $(window).on('load', recalculateMetrics);
        $(window).on('resize', debounce(recalculateMetrics, 1000));

        setTimeout(recalculateMetrics, 3000); // in case load fails

        var navScrolling = false;

        function navigateToChapter(index) {
            navScrolling = true;

            recalculateMetrics();

            if (settings['toc_url_update']) { // TODO: replace with browserHistory instead
                window.location.hash = 'ch_' + (index + 1);
            }

            $doc.animate({
                scrollTop: headingOffsets[index] - scrollOffset - 10
            }, 300, 'swing', function() {


                if (isScrollSpy && linksArr) {
                    $(linksArr).removeClass('active');
                    $(linksArr[index]).addClass('active');
                }

                setTimeout(function() {
                    navScrolling = false;
                }, 300);
            });
        }

        // link click handlers
        $tocList.on('click', '.mkb-anchors-list__item-link', function (e) {
            var $item = $(e.currentTarget);

            if ($item.attr('href') !== '#') {
                return; // normal link, just navigate
            }

            var index = parseInt($item.data('index'), 10);

            e.preventDefault();

            if (isScrollSpy) {
                $links.removeClass('active');
                $item.addClass('active');
            }

            navigateToChapter(index);
        });

        var chapterHash = window.location.hash && window.location.hash.replace(/^#/, '');

        if (chapterHash && chapterHash.indexOf('ch_') !== -1) {
            var chapterIndex = parseInt(chapterHash.replace(/^ch_/, '')) - 1;

            if (chapterIndex && chapterIndex > 0) {
                setTimeout(function() {
                    navigateToChapter(chapterIndex);
                }, 300);
            } else if (isScrollSpy && chapterIndex === 0) {
                $(linksArr[0]).addClass('active');
            }
        } else if (isScrollSpy) {
            $(linksArr[0]).addClass('active');
        }

        if (!isScrollSpy) {
            return;
        }

        // ScrollSpy
        var win = window;
        var doc = document.documentElement;

        $(win).on('scroll', throttle(150, function() {
            if (navScrolling) { return; }

            var top = win.pageYOffset || doc.scrollTop;

            linksArr.forEach(function(item, index) {
                var curr = index === 0 ? 0 : headingOffsets[index] - scrollOffset - 1;
                var next = (index === headingOffsets.length - 1 ? 9999999 : headingOffsets[index + 1]) - scrollOffset - 1;

                $(item).toggleClass('active', top >= curr && top < next);
            });
        }));
    }

    /**
     * Wraps headings n back to top containers when necessary
     * @param $headings
     */
    function wrapTOCAnchors($headings) {
        $headings.each(function(index, el) {
            var $heading = $(el);

            $heading.wrap('<div class="mkb-anchor mkb-clearfix mkb-back-to-top-' +
            settings['back_to_top_position'] +
            '"></div>');

            $heading.addClass('mkb-anchor__title');

            $heading.parent().append('<a href="#" class="mkb-back-to-top" title="' +
            settings['back_to_top_text'] + '">' +
            settings['back_to_top_text'] +
            (
                settings['show_back_to_top_icon'] ?
                '<i class="mkb-back-to-top-icon fa ' + settings['back_to_top_icon'] + '"></i>' :
                    ''
            ) +
            '</a>');
        });
    }

    /**
     * Articles fancy box
     */
    function initArticlesFancyBox() {
        if (!$.fn.fancybox) {
            return;
        }

        // with captions
        $('figure[id^="attachment"] a').each(function (index, item) {
            var $item = $(item);
            var text = $item.parent().find('.wp-caption-text').text();

            $item.fancybox({
                titlePosition: 'over',
                title: text
            });
        });

        // no captions
        $('.mkb-single-content img').each(function(i, img) {
            var $img = $(img);
            var $link = $img.parent();

            if ($img.parents('figure.wp-caption').length || !$link.attr('href')) {
                return;
            }

            $link.fancybox({
                titlePosition: 'none',
                title: ''
            });
        });
    }

    /**
     * Search clear
     * @param e
     */
    function handleSearchClear(e) {
        e.preventDefault();

        $(e.currentTarget)
            .parents('.kb-search__input-wrap')
            .find('.kb-search__input')
            .val('')
            .trigger('input')
            .focus();
    }

    function initSearchInputs() {
        $kbSearch.each(function (index, el) {
            var $search = $(el);

            var searchHandler = searchMode === 'blocking' ?
                debounce(onSearchType.bind(this, $search), parseInt(settings['search_delay'], 10) || 1000, false) :
                throttle(parseInt(settings['search_delay'], 10) || 300, true, onSearchType.bind(this, $search), true);

            $search.on('input', searchHandler);
        });
    }

    /**
     * Detects if live search disabled for current platform
     * @returns {boolean}
     */
    function isSearchDisabled() {
        return Boolean(settings['live_search_disable_' + platform]);
    }

    /**
     * Sticky sidebar
     */
    function setupArticleStickySidebar() {

        if (settings['single_template'] === 'theme' ||
            !settings['article_sidebar_sticky'] ||
            !info.isSingle ||
            platform !== 'desktop' ||
            window.outerWidth < parseInt(settings['article_sidebar_sticky_min_width']['size'], 10)) {

            // sticky sidebar not enabled or not allowed
            return;
        }

        var sticky = false;
        var sidebarPosition = settings['article_sidebar'];
        var atBottom = false;
        var $sidebar = $('.mkb-sidebar');
        var sidebarHeight = $sidebar.outerHeight();
        var $root = $sidebar.parents('.mkb-root');
        var rootHeight = $root.outerHeight();
        var rootHeightInner = $root.height();
        var rootPad = rootHeight - rootHeightInner;
        var rootTop = $root.offset().top;
        var triggerPos = rootTop - rootPad / 2;
        var winHeight = window.innerHeight;
        var bottomOffset = winHeight > sidebarHeight + rootPad ? winHeight - sidebarHeight - rootPad : 0;
        var width = $sidebar.outerWidth();
        var win = window;
        var doc = document.documentElement;

        function recalculateMetrics() {
            rootHeight = $root.outerHeight();
            rootHeightInner = $root.height();
            rootPad = rootHeight - rootHeightInner;
            rootTop = $root.offset().top;
            triggerPos = rootTop - rootPad / 2;
            winHeight = window.innerHeight;
            sidebarHeight = $sidebar.outerHeight();
            bottomOffset = winHeight > sidebarHeight + rootPad ? winHeight - sidebarHeight - rootPad : 0;
        }

        $(win).on('load', recalculateMetrics);
        $(win).on('resize', debounce(recalculateMetrics, 1000));

        setInterval(recalculateMetrics, 500); // sometimes content height changes dynamically

        function updateSidebarLeftPosition() {
            var left = $root.get(0).getBoundingClientRect().left;
            $sidebar.css('left', left + parseInt($root.css('padding-left')) + 'px');
        }

        function handleScroll() {
            var top = win.pageYOffset || doc.scrollTop;
            var bottom = top + winHeight - bottomOffset;

            if (bottom > rootHeight + rootTop && !atBottom || bottom <= rootHeight + rootTop && atBottom) {
                atBottom = !atBottom;
                $sidebar.toggleClass('mkb-fixed-bottom', atBottom);

                if (sidebarPosition === 'left') {
                    if (atBottom) {
                        $sidebar.css('left', '');
                    } else {
                        updateSidebarLeftPosition();
                    }
                }
            }

            if (sticky && top >= triggerPos || !sticky && top < triggerPos) {
                return;
            }

            sticky = !sticky;
            $sidebar.toggleClass('mkb-fixed', sticky);
            $sidebar.css('max-width', sticky ? width + 'px' : 'none');

            if (sidebarPosition === 'left') {
                if (sticky) {
                    if (!atBottom) {
                        updateSidebarLeftPosition();
                    }

                    $(window).on('resize', updateSidebarLeftPosition);
                } else {
                    $sidebar.css('left', 0);
                    $(window).off('resize', updateSidebarLeftPosition);
                }
            }
        }

        $(win).on('scroll', handleScroll);
    }

    /**
     * FAQ
     */
    function setupFaq() {
        var $faqContainer = $('.fn-kb-faq-container');

        if (!$faqContainer.length) {
            return;
        }

        function getHashFaqId() {
            var currentHash = window.location.hash.replace('#', '');
            return /^qa_/.test(currentHash) && currentHash.replace('qa_', '') || false;
        }

        function setHashFaq(id) {
            history.replaceState(null, '',
                window.location.origin + window.location.pathname + window.location.search +
                '#qa_' + id);
        }

        function clearHash() {
            history.replaceState(null, '',
                window.location.origin + window.location.pathname + window.location.search);
        }

        $faqContainer.each(function(index, item) {
            var $container = $(item);
            var FAQ_HIDDEN_CLASS = 'mkb-faq-item-hidden';
            var FAQ_SECTION_HIDDEN_CLASS = 'mkb-faq-section-hidden';
            var $filterForm = $container.find('.fn-kb-faq-filter');
            var $filter = $filterForm.find('.fn-kb-faq-filter-input');
            var $sections = $container.find('.fn-kb-faq-section');
            var $noResults = $container.find('.fn-kb-faq-no-results');

            // FAQ sections
            var sections = [].map.call($sections, function(section) {
                var $section = $(section);
                var $count = $section.find('.fn-kb-faq-section-count');
                var $items = [].map.call($section.find('.fn-kb-faq-item'), function(item) {
                    var $item = $(item);

                    return {
                        $el: $item,
                        question: $item.find('.fn-kb-faq-question').text().trim().toLowerCase(),
                        answer: $item.find('.fn-kb-faq-answer').text().trim().toLowerCase(),
                        isVisible: true
                    };
                });

                return {
                    $el: $section,
                    $countEl: $count.length ? $count : null,
                    items: $items,
                    visible: [].map.call($items, function(item) { return $(item) }),
                    hidden: []
                };
            });

            /**
             * FAQ Filter
             */
            var currentFilter;

            function updateFilter(visCheck) {
                visCheck = visCheck || checkVisibility;

                var totalVisible = 0;

                // check visibility
                sections.forEach(function(section) {
                    section.visible = [];
                    section.hidden = [];
                    section.items.forEach(function(item) {
                        section[(item.isVisible = visCheck(item)) ? 'visible' : 'hidden'].push(item);
                    });
                    totalVisible += section.visible.length;
                });

                $noResults.toggleClass('mkb-hidden', totalVisible > 0);

                sections.forEach(function(section) {
                    section.visible.forEach(function(item) {
                        item.$el.removeClass(FAQ_HIDDEN_CLASS);
                    });
                    section.hidden.forEach(function(item) {
                        item.$el.addClass(FAQ_HIDDEN_CLASS);
                    });
                    section.$countEl && section.$countEl.html(section.visible.length +
                    ' ' + (section.visible.length === 1 ? i18n['question'] : i18n['questions'])
                    );
                    section.$el.toggleClass(FAQ_SECTION_HIDDEN_CLASS, !section.visible.length);
                    section.visible = [];
                    section.hidden = [];
                });

                if (settings['faq_filter_open_single'] && totalVisible === 1) {
                    var $onlyItem = $container.find('.fn-kb-faq-item:not(.' + FAQ_HIDDEN_CLASS + ')');

                    if ($onlyItem.length && !$onlyItem.hasClass('kb-faq__questions-list-item--open')) {
                        toggleAnswer($onlyItem);
                    }
                }
            }

            var resetFilter = updateFilter.bind(this, function() { return true; });

            function checkVisibility(item) {
                return item.question.indexOf(currentFilter) !== -1 || item.answer.indexOf(currentFilter) !== -1;
            }

            function handleFilterChange(e) {
                var needle = (e.currentTarget.value || '').trim();

                if (needle.length < 3) {
                    $filterForm.addClass('kb-faq__filter--empty');
                    currentFilter = '';
                    resetFilter();

                    $filter.focus();
                    return
                }

                $filterForm.removeClass('kb-faq__filter--empty');
                currentFilter = needle.toLowerCase();
                updateFilter();
            }

            function handleFilterClear(e) {
                e.preventDefault();

                $filter.val('').trigger('input');
            }

            if ($filterForm.length) {
                $container.on('input', '.fn-kb-faq-filter-input', handleFilterChange);
                $container.on('click', '.fn-kb-faq-filter-clear', handleFilterClear);
            }

            var OPEN_SPEED = settings['faq_slow_animation'] ? 400 : 100;

            function getMaxHeight(el) {
                return Array.prototype.reduce.call(el.childNodes, function(store, current) {
                    return store + (current.offsetHeight || 0);
                }, 0);
            }

            /**
             * FAQ Toggle
             */
            function toggleAnswer($item) {
                var $answer = $item.find('.fn-kb-faq-answer');
                var $link = $item.find('.fn-kb-faq-link');
                var answerEl = $answer.get(0);
                var maxHeight = getMaxHeight(answerEl);

                if ($item.hasClass('kb-faq__questions-list-item--open')) {
                    $answer.css('max-height', maxHeight);
                    $answer.animate({maxHeight: 0}, OPEN_SPEED, 'swing', function() {
                        $item.removeClass('kb-faq__questions-list-item--open');
                    });

                    if (settings['faq_url_update']) {
                        var hashFaqId = getHashFaqId();

                        if (hashFaqId && hashFaqId == $link.data('id')) {
                            clearHash();
                        }
                    }
                } else {
                    if (settings['faq_toggle_mode']) {
                        sections.forEach(function(section) {
                            section.items.forEach(function(item) {
                                closeAnswer(item.$el);
                            });
                        });
                    }

                    $answer.animate({maxHeight: maxHeight}, OPEN_SPEED, 'swing', function() {
                        $answer.css('max-height', 'none');
                        $item.addClass('kb-faq__questions-list-item--open');
                    });

                    if (settings['faq_url_update']) {
                        setHashFaq($link.data('id'));
                    }
                }
            }

            function openAnswer($item) {
                var $answer = $item.find('.fn-kb-faq-answer');
                var answerEl = $answer.get(0);
                var maxHeight = getMaxHeight(answerEl);

                if (!$item.hasClass('kb-faq__questions-list-item--open')) {
                    $answer.animate({maxHeight: maxHeight}, OPEN_SPEED, 'swing', function() {
                        $answer.css('max-height', 'none');
                        $item.addClass('kb-faq__questions-list-item--open');
                    });
                }
            }

            function closeAnswer($item) {
                var $answer = $item.find('.fn-kb-faq-answer');
                var answerEl = $answer.get(0);
                var maxHeight = getMaxHeight(answerEl);

                if ($item.hasClass('kb-faq__questions-list-item--open')) {
                    $answer.css('max-height', maxHeight);
                    $answer.animate({maxHeight: 0}, OPEN_SPEED, 'swing', function() {
                        $item.removeClass('kb-faq__questions-list-item--open');
                    });
                }
            }

            function handleQuestionClick (e) {
                e.preventDefault();

                var $link = $(e.currentTarget);
                var $item = $link.parent();

                toggleAnswer($item);
            }

            function handleToggleAllClick (e) {
                e.preventDefault();

                var $link = $(e.currentTarget);
                var isOpen = $link.hasClass('kb-faq__toggle-all-link--open');

                sections.forEach(function(section) {
                    section.items.forEach(function(item) {
                        isOpen ? closeAnswer(item.$el) : openAnswer(item.$el);
                    });
                });

                $link.toggleClass('kb-faq__toggle-all-link--open');
            }

            $container.on('click', '.fn-kb-faq-link', handleQuestionClick);
            $container.on('click', '.fn-kb-faq-toggle-all', handleToggleAllClick);
        });

        if (settings['faq_url_update']) {
            // navigate to faq from hash
            var hashFaqId = getHashFaqId();
            var $faqLink = hashFaqId && $('.fn-kb-faq-link[data-id="' + hashFaqId + '"]');
            var scrollOffset = parseInt(settings['faq_scroll_offset']['size']);

            if ($faqLink && $faqLink.length) {
                $faqLink.trigger('click');

                setTimeout(function() {
                    $doc.animate({
                        scrollTop: $faqLink.offset().top - scrollOffset
                    }, 300);
                }, 800);
            }
        }
    }

    /**
     * Content Tree
     */
    function setupContentTreeWidgets() {
        var $contentTree = $('.mkb_content_tree_widget');
        var openActiveBranch = settings['content_tree_widget_open_active_branch'];

        function setListMaxHeight(index, list) {
            var $list = $(list);
            $list.animate({'max-height': list.scrollHeight}, 200, function() {
                $list.css('max-height', 'none');
            });
        }

        $contentTree.each(function(index, tree) {
            var $tree  = $(tree);
            var $topics = $tree.find('.mkb-widget-content-tree__topic');

            $tree.on('click', '.mkb-widget-content-tree__topic-name', function(e) {
                var topicName = e.currentTarget;
                var $topic = $(topicName).parent();
                var topic = $topic.get(0);

                if ($topic.hasClass('topic-open')) {
                    $topic.removeClass('topic-open');
                    $topic.find('>ul').css('max-height', '0');
                } else {
                    var activeBranch = [topic.dataset.id];
                    var $parents = $topic.parents('.mkb-widget-content-tree__topic');

                    $parents.each(function(index, parent) {
                        activeBranch.push(parent.dataset.id);
                    });

                    // hide topics that are not in current branch
                    $topics.each(function(index, item) {
                        if (activeBranch.indexOf(item.dataset.id) !== -1) {
                            return;
                        }

                        var $item = $(item);

                        $item.removeClass('topic-open');
                        $item.find('>ul').css('max-height', '0');
                    });

                    $topic.find('>ul').each(setListMaxHeight);
                    $topic.addClass('topic-open');

                    $parents.each(function(index, item) {
                        var $parentTopic = $(item);

                        if ($parentTopic.hasClass('topic-open')) {
                            return;
                        }

                        $parentTopic.find('>ul').each(setListMaxHeight);
                        $parentTopic.addClass('topic-open');
                    });
                }

                e.preventDefault();
                e.stopImmediatePropagation();
            });

            if (openActiveBranch) {
                setTimeout(function() {
                    $tree.find('.mkb-widget-content-tree__article--active')
                        .closest('.mkb-widget-content-tree__topic')
                        .find('>.mkb-widget-content-tree__topic-name')
                        .trigger('click');
                }, 300);
            }
        });
    }

    /**
     * Client submission form
     */
    function setupSubmissionForm() {
        var $submisionContainers = $('.js-mkb-client-submission');

        $submisionContainers.each(function(index, container) {
            var $container = $(container);
            var $form = $container.find('.js-mkb-client-submission-form');

            var $title = $container.find('.js-mkb-submission-title');
            var $topic = $container.find('.js-mkb-submission-topic');
            var $content = $container.find('#mkb-client-editor');
            var $antispamAnswer = $container.find('.js-mkb-real-human-answer');
            var $messagesContainer = $container.find('.js-mkb-form-messages');

            var toolbarOptions = [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['blockquote', 'code-block'],
                ['clean']
            ];

            var quillEditor = new Quill('#mkb-client-editor', {
                modules: {
                    toolbar: toolbarOptions,
                    history: {
                        delay: 2000,
                        maxStack: 500,
                        userOnly: true
                    }
                },
                theme: settings.submit_content_editor_skin
            });

            function getData() {
                return {
                    action: 'mkb_save_client_submission',
                    title: $title.val().trim(),
                    content: $content.find('.ql-editor').get(0).innerHTML.trim(),
                    topic: $topic.length ? $topic.val().trim() : '',
                    antispam: $antispamAnswer.length ? $antispamAnswer.val().trim() : ''
                }
            }

            function validateData(data) {
                var status = 0;
                var errors = [];

                if (!data.title) {
                    status = 1;
                    errors.push(i18n.submission_empty_title);
                }

                if (!data.content
                        .replace('<p>', '')
                        .replace('</p>', '')
                        .replace('<br>', '')) {

                    status = 1;
                    errors.push(i18n.submission_empty_content);
                }

                if (!data.antispam && $antispamAnswer.length) {
                    status = 1;
                    errors.push(settings.antispam_failed_message);
                }

                return {
                    status: status,
                    errors: errors
                }
            }

            function renderMessages(messages) {
                $messagesContainer.html(messages.reduce(function(html, err) {
                    return html + '<div class="mkb-form-message">' + err + '</div>'
                }), '');
            }

            function showMessages(messageClass, noScroll) {
                noScroll = noScroll || false;

                $messagesContainer.addClass(messageClass).removeClass('mkb-hidden');

                if (!noScroll) {
                    $doc.animate({
                        scrollTop: $messagesContainer.offset().top - adminOffset - 30
                    }, 100);
                }
            }

            function showErrorMessages(noScroll) {
                showMessages('mkb-form-error', noScroll);
            }

            function showSuccessMessages(noScroll) {
                showMessages('mkb-form-success', noScroll);
            }

            $container.on('click', '.js-mkb-client-submission-send', function(e) {
                var requestData = getData();
                var $btn = $(e.currentTarget);

                if ($btn.hasClass('mkb-disabled')) {
                    return;
                }

                var validationResult = validateData(requestData);

                if (validationResult.status !== 0) {
                    renderMessages(validationResult.errors);
                    showErrorMessages();

                    $doc.animate({
                        scrollTop: $messagesContainer.offset().top - adminOffset - 30
                    }, 100);

                    return;
                }

                $btn.addClass('mkb-disabled');

                jQuery.ajax({
                    method: 'POST',
                    url: ajaxUrl,
                    dataType: 'json',
                    data: addAjaxNonce(requestData)
                }).done(function(response) {

                    $btn.removeClass('mkb-disabled');

                    if (response.status == 1) {
                        renderMessages([response.error]);
                        showErrorMessages();
                    } else {
                        renderMessages([settings.submit_success_message]);
                        showSuccessMessages(true);

                        $form.html('');

                        $doc.animate({
                            scrollTop: $container.offset().top - adminOffset - 30
                        }, 100);
                    }
                });
            });
        });
    }

    /**
     * KB floating helper
     */
    function setupHelper() {
        var $helper = $('.js-mkb-floating-helper');

        if (!$helper.length) {
            return;
        }

        var $searchInput = $helper.find('.kb-search__input');

        $helper.on('click', '.js-mkb-floating-helper-btn', function() {
            $helper.addClass('mkb-floating-helper-wrap--open');
            $searchInput.get(0).focus();
        });

        $helper.on('click', '.js-mkb-floating-helper-close', function() {
            $helper.removeClass('mkb-floating-helper-wrap--open');
        });

        setTimeout(function() {
            $helper.addClass('mkb-floating-helper-wrap--ready');
        }, settings.fh_show_delay);
    }

    /**
     * Attachments
     */
    function setupAttachments() {
        // download tracking
        $body.on('click', '.js-mkb-attachment-link', function(e) {
            var link = e.currentTarget;
            var id = link.dataset.id;

            if (!id) {
                return;
            }

            jQuery.ajax({
                method: 'POST',
                url: ajaxUrl,
                dataType: 'json',
                data: addAjaxNonce({
                    action: 'mkb_track_attachment_download',
                    id: id
                })
            })
        });
    }

    /**
     * Main plugin startup
     */
    function init() {
        $adminBar = $('#wpadminbar');
        adminOffset = $adminBar.length ? $adminBar.height() : 0;

        if ($kbSearch.length && !isSearchDisabled()) {
            initSearchInputs();
            $body.on('keydown', onSearchKeyPress);
            $body.on('click', '.kb-search__clear', handleSearchClear);
            focusInput();
            onSearchType($kbSearch.eq(0)); // restore previous search
        }

        // FAQ items
        setupFaq();

        // article related code
        if (info.isSingle) {
            $body.on('click', '.mkb-article-extra__like', handleArticleLike);
            $body.on('click', '.mkb-article-extra__dislike', handleArticleDislike);
            $body.on('click', '.mkb-article-extra__feedback-form-submit', handleFeedbackSubmit);
            $body.on('input', '.mkb-article-extra__feedback-form-message-area', handleFeedbackType);

            handleArticleBackToTop();
            handleArticleTOC();

            if (settings['article_fancybox']) {
                initArticlesFancyBox();
            }

            trackArticleView();

            // sticky articles sidebar
            setupArticleStickySidebar();

            setupAttachments();
        }

        setupContentTreeWidgets();
        setupSubmissionForm();
        setupHelper();
    }

    // start
    $(document).ready(init);

})(jQuery);