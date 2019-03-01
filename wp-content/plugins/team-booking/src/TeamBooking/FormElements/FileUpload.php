<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts;

/**
 * Form element: File Upload
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class FileUpload extends Abstracts\FormElement
{
    /**
     * FileUpload constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setData('max_size', 30);
        $this->setData('file_extensions', 'jpg, png, jpeg, zip');
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'file_upload';
    }

    /**
     * @param bool $hidden
     *
     * @return string
     */
    public function getMarkup($hidden = FALSE)
    {
        $random_append = substr(md5(mt_rand()), 0, 8);
        ob_start();
        ?>
        <div class="tbk-field tbk-field-file-upload <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label><?= $this->getTitle() ?></label>
            <?php
            if ($this->getData('value')) {
                $this->getAlreadyUploadedMessage($random_append);
            } ?>
            <div class="tbk-file-input">
                <input style="height:inherit;" type="text" id="_<?= $this->getHook() . $random_append ?>" readonly="">
                <label for="<?= $this->getHook() . $random_append ?>"
                       class="tbk-button tbk-file-button"
                       id="label_<?= $this->getHook() . $random_append ?>">
                    <input type="file" id="<?= $this->getHook() . $random_append ?>" name="<?= $this->getHook() ?>"
                           style="display: none;height: inherit;" <?= $this->isRequired() ? "required='required'" : '' ?> <?= $this->getData('value') ? 'class="tbk-field-skip"' : '' ?>>
                    <i class="dashicons dashicons-upload"></i>
                </label>
            </div>
            <?php $this->getValidationMessageLabel() ?>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
            <script>
                jQuery(document).on('change', '#label_<?= $this->getHook() . $random_append ?> :file', function () {
                    var input = jQuery(this);
                    var label;
                    if (navigator.appVersion.indexOf("MSIE") != -1) { // IE
                        label = input.val();
                        input.trigger('fileselect', [label, 0]);
                    } else {
                        if (typeof input.get(0).files[0] === 'undefined') return;
                        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                        var size = input.get(0).files[0].size;

                        input.trigger('fileselect', [label, size]);
                    }
                });
                jQuery('#label_<?= $this->getHook() . $random_append ?> :file').on('fileselect', function (event, label, size) {
                    jQuery('#<?= $this->getHook() . $random_append ?>').closest('.tbk-field').find('.tbk-reservation-form-pointing-error').hide();
                    var fileExtentionRange = '<?= $this->getFileExtensions() ?>';
                    var MAX_SIZE = <?= $this->getData('max_size') ?>; // MB
                    jQuery('#<?= $this->getHook() . $random_append ?>').attr('name', '<?= $this->getHook() ?>'); // allow upload.
                    var postfix = label.substr(label.lastIndexOf('.'));
                    if (fileExtentionRange.indexOf(postfix.toLowerCase()) > -1) {
                        if (size > 1024 * 1024 * MAX_SIZE) {
                            alert('max size: ' + MAX_SIZE + ' MB.');
                            jQuery('#<?= $this->getHook() . $random_append ?>').removeAttr('name').val(''); // cancel upload file.
                            jQuery('#_<?= $this->getHook() . $random_append ?>').val('');
                        } else {
                            jQuery('#_<?= $this->getHook() . $random_append ?>').val(label);
                        }
                    } else {
                        jQuery('#<?= $this->getHook() . $random_append ?>').closest('.tbk-field').find('.tbk-reservation-form-pointing-error').show();
                        jQuery('#<?= $this->getHook() . $random_append ?>').removeAttr('name').val(''); // cancel upload file.
                        jQuery('#_<?= $this->getHook() . $random_append ?>').val('');
                    }
                    jQuery(this).closest('.tb-frontend-calendar').trigger('tbk:slider:adapt');
                });
                jQuery(document).ready(function ($) {
                    $('#_<?= $this->getHook() . $random_append ?>').on('keyup', function (e) {
                        if (e.keyCode == 13) {
                            $('#label_<?= $this->getHook() . $random_append ?>').trigger('click');
                            e.stopPropagation();
                        }
                    });
                });
            </script>
        </div>
        <?php

        return ob_get_clean();
    }

    protected function getValidationMessageLabel()
    {
        ?>
        <div class="tbk-reservation-form-pointing-error" style="display:none;">
            <?= esc_html__('Allowed file types:', 'team-booking') ?> <?= $this->getFileExtensions() ?>
        </div>
        <?php
    }

    /**
     * @return string
     */
    protected function getFileExtensions()
    {
        $new_array = array();
        $array = explode(',', $this->getData('file_extensions'));
        foreach ($array as $value) {
            if (empty($value)) {
                continue;
            }
            $new_array[] = '.' . trim($value) . ' ';
        }

        return trim(implode($new_array));
    }

    protected function getAlreadyUploadedMessage($random_append)
    {
        ?>
        <div class="ui positive message tbk-already-uploaded-message" data-already-uploaded="<?= $this->getHook() ?>">
            <?= esc_html__('Already available', 'team-booking') ?>
            <strong><?= basename($this->getData('value')) ?></strong><br>
            <span class="tbk-already-uploaded-remove"
                  tabindex="0"> <?= esc_html__('Change it', 'team-booking') ?></span>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                var $file_field = $('#_<?= $this->getHook() . $random_append ?>').parent();
                var $slider = $(this).closest('.tb-frontend-calendar');
                <?php if ($this->getData('value')) { ?>
                $file_field.hide();
                $slider.trigger('tbk:slider:adapt');
                <?php } ?>
                $file_field.closest('.tbk-field').on('click keydown', '.tbk-already-uploaded-remove', function (e) {
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        e.stopPropagation();
                        $(this).closest('.tbk-already-uploaded-message').remove();
                        $file_field.show();
                        $('#<?= $this->getHook() . $random_append ?>').removeClass('tbk-field-skip');
                        $slider.trigger('tbk:slider:adapt');
                    }
                });
            });
        </script>
        <?php
    }
}