/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ARTICLE_DATA = GLOBAL_DATA.articleEdit;
    var ui = window.MinervaUI;
    var settings = GLOBAL_DATA.settings;
    var i18n = GLOBAL_DATA.i18n;

    function setupRelatedArticles() {
        var $addBtn = $('#mkb_add_related_article');
        var $relatedContainer = $('.fn-related-articles');

        $addBtn.on('click', function(e) {
            e.preventDefault();

            var btnText = $addBtn.text();

            $addBtn.text(i18n['loading']).attr('disabled', 'disabled');

            ui.fetch({
                action: 'mkb_get_articles_list',
                currentId: $addBtn.data('id')
            }).then(function(response) {
                var $related = $('<div class="mkb-related-articles__item"></div>');
                var $select = $('<select class="mkb-related-articles__select" name="mkb_related_articles[]"></select>');
                var articlesList = response.articles || [];

                articlesList.forEach(function(article) {
                    $select.append(
                        $('<option value="' + article.id + '">' + article.title + '</option>')
                    );
                });

                var $noRelatedMessage = $('.fn-no-related-message');

                $noRelatedMessage.length && $noRelatedMessage.remove();

                $related.append($select);
                $related.append(
                    $('<a class="mkb-related-articles__item-remove fn-related-remove mkb-unstyled-link" href="#">' +
                    '<i class="fa fa-close"></i>' +
                    '</a>')
                );

                $('.fn-related-articles').append($related);

                $addBtn.text(btnText).attr('disabled', false);
            });
        });

        $relatedContainer.sortable({
            'items': '.mkb-related-articles__item',
            'axis': 'y'
        });

        $relatedContainer.on('click', '.fn-related-remove', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);

            $link.parents('.mkb-related-articles__item').remove();

            if ($relatedContainer.find('.mkb-related-articles__item').length === 0) {
                $relatedContainer.append(
                    $('<div class="fn-no-related-message mkb-no-related-message">' +
                        '<p>' + i18n['no-related'] + '</p>' +
                    '</div>'
                ));
            }
        });
    }

    function initFeedback() {
        $('#poststuff').on('click', '.fn-remove-feedback', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);
            var $row = $link.parents('.mkb-article-feedback-item');

            $row.addClass('mkb-article-feedback-item--removing');

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

    function initReset() {
        $('.fn-mkb-article-reset-stats-btn').on('click', function(e) {
            e.preventDefault();

            var resetConfig = ui.getFormData($('.fn-mkb-article-reset-form'));

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
                articleId: e.currentTarget.dataset.id,
                resetConfig: resetConfig
            }).then(function(response) {
                if (response.status == 0) {
                    toastr.success('Data was reset successfully.');
                } else {
                    toastr.error('Could not reset data, try to refresh the page');
                }
            });
        });
    }

    function setupArticleAttachments() {
        var $attachmentsContainer = $('.js-mkb-attachments');
        var $addBtn = $('.js-mkb-add-attachment');
        var frame;
        var attachmentsIconMap = ARTICLE_DATA.attachmentsIconMap;
        var attachmentsTracking = ARTICLE_DATA.attachmentsTracking;
        var attachmentsIconDefault = ARTICLE_DATA.attachmentsIconDefault;

        attachmentsTracking = Array.isArray(attachmentsTracking) ? {} : attachmentsTracking;

        function getDownloads(id) {
            return attachmentsTracking[id] && attachmentsTracking[id]['downloads'] || 0;
        }

        var attachmentItemTmpl = wp.template('mkb-attachment-item');
        var noAttachmentsTmpl = wp.template('mkb-no-attachments');

        if (!$attachmentsContainer.length) {
            return;
        }

        // remove
        $attachmentsContainer.on('click', '.js-mkb-attachment-remove', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);

            if (!confirm('Are you sure you want to remove attachment?')) {
                return;
            }

            $link.parents('.js-mkb-attachment-item').remove();

            if ($attachmentsContainer.find('.js-mkb-attachment-item').length === 0) {
                $attachmentsContainer.html(noAttachmentsTmpl());
            }
        });

        // edit
        $attachmentsContainer.on('click', '.js-mkb-attachment-edit', function(e) {
            e.preventDefault();

            var $link = $(e.currentTarget);
            var $item = $link.parents('.js-mkb-attachment-item');
            var $store = $item.find('.js-mkb-attachment-value');

            openMedia({
                onSelect: function(attachments) {
                    appendAttachment(attachments[0], $item);
                    $item.remove();
                },
                selectedId: parseInt($store.val())
            });
        });

        function openMedia(options) {
            frame = wp.media({
                title: 'Select or Upload Media',
                button: {
                    text: 'Use this media'
                },
                multiple: Boolean(options.multiple)
            });

            frame.on('select', function() {
                var attachments = frame.state().get('selection').map(function(attachment) {
                    return attachment.toJSON();
                });

                options.onSelect(attachments);
            });

            // add preselected attachment
            if (options.selectedId) {
                frame.on('open',function() {
                    var selection = frame.state().get('selection');
                    var attachment = wp.media.attachment(options.selectedId);
                    attachment.fetch();
                    selection.add( attachment ? [ attachment ] : [] );
                });
            }

            frame.open();
        }

        function matchByMimeType(attachment, config) {
            return config.mime.length && (
                config.mimeBase ?
                    config.mime.map(function(type) { return config.mimeBase + '/' + type; }).includes(attachment.mime) :
                    config.mime.includes(attachment.subtype)
            );
        }

        function getExtension(filename) {
            return filename.split('.').pop();
        }

        function matchByExtension(attachment, config) {
            return config.extension.includes(getExtension(attachment.filename));
        }

        function getAttachmentIconConfig(attachment) {
            return _.find(attachmentsIconMap, function(configEntry) {
                return matchByMimeType(attachment, configEntry) || matchByExtension(attachment, configEntry);
            }) || attachmentsIconDefault;
        }

        function appendAttachment(attachment, $after) {
            var iconConfig = getAttachmentIconConfig(attachment);
            var itemHTML = attachmentItemTmpl(
                _.extend({}, attachment,
                    {
                        color: iconConfig.color,
                        icon: iconConfig.icon,
                        description: iconConfig.description,
                        extension: getExtension(attachment.filename),
                        downloads: getDownloads(attachment.id)
                    })
            );
            var $html = $(itemHTML);

            if ($after) {
                $after.after($html);
            } else {
                $attachmentsContainer.append($html);
            }

            ui.setupTooltips($html);
        }

        $addBtn.on('click', function(e) {
            e.preventDefault();

            openMedia({
                onSelect: function(attachments) {
                    $attachmentsContainer.find('.js-mkb-no-attachments').remove();

                    attachments.forEach(function(attachment) {
                        appendAttachment(attachment);
                    });
                },
                multiple: true
            });
        });

        if (ARTICLE_DATA.attachments.length) {
            $attachmentsContainer.html('');
            ARTICLE_DATA.attachments.forEach(function(attachment) {
                appendAttachment(attachment);
            });
        } else {
            $attachmentsContainer.html(noAttachmentsTmpl());
        }

        $attachmentsContainer.sortable({
            'items': '.mkb-article-attachments__item',
            'axis': 'y'
        });
    }

    function init() {
        var $restrictContainer = $('#mkb-article-meta-restrict-id');

        setupRelatedArticles();
        setupArticleAttachments();
        initFeedback();
        initReset();

        ui.setupRolesSelector($restrictContainer);
    }

    $(document).ready(init);
})(jQuery);