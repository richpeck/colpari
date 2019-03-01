<?php

// Blocks direct access to this file
defined('ABSPATH') or die("No script kiddies please!");

#TeamBookingFormFileUpload extends TeamBookingFormTextField
#TeamBooking_Components_Form_FileUpload

use TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\FileUpload
 *
 * Class TeamBookingFormFileUpload
 */
class TeamBookingFormFileUpload extends TeamBookingFormTextField
{

    //------------------------------------------------------------

    protected $file_extensions;
    protected $max_size;

    //------------------------------------------------------------

    public function setFileExtensions($extensions)
    {
        $this->file_extensions = $extensions;
    }

    public function getFileExtensions($backend = FALSE)
    {
        if (!$backend) {
            if (!$this->file_extensions) {
                // Defaults
                return ".jpg .png .jpeg .zip";
            } else {
                $array = explode(",", $this->file_extensions);
                foreach ($array as $value) {
                    if (empty($value)) {
                        continue;
                    }
                    $new_array[] = "." . trim($value) . " ";
                }

                return trim(implode($new_array));
            }
        } else {
            if (!$this->file_extensions) {
                // Defaults
                return "jpg, png, jpeg, zip";
            } else {
                return $this->file_extensions;
            }
        }
    }

    //------------------------------------------------------------


    public function setMaxSize($size)
    {
        if (is_numeric($size)) {
            $this->max_size(abs($size));
        }
    }

    public function getMaxSize()
    {
        if (!$this->max_size) {
            // default
            return 30;
        } else {
            return $this->max_size;
        }
    }

    //------------------------------------------------------------

    protected function getValidationMessageLabel()
    {
        ?>
        <div class="tbk-reservation-form-pointing-error" style="display:none;">
            <?= esc_html__('Allowed file types:', 'team-booking') ?> <?= $this->getFileExtensions() ?>
        </div>
        <?php
    }

    //------------------------------------------------------------

    // HTML Semantic UI mapper
    public function getMarkup($input_size = '')
    {
        $random_append = substr(md5(rand()), 0, 8);
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label><?= $this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)) ?></label>

            <div class="tbk-file-input <?= $input_size ?>">
                <input style="height:inherit;" type="text" id="_<?= $this->hook . $random_append ?>" readonly="">
                <label for="<?= $this->hook . $random_append ?>"
                       class="tbk-button tbk-file-button <?= $input_size ?>"
                       id="label_<?= $this->hook . $random_append ?>">
                    <input type="file" id="<?= $this->hook . $random_append ?>" name="<?= $this->hook ?>"
                           style="display: none;height: inherit;" <?= $this->required ? "required='required'" : "" ?>>
                    <i class="dashicons dashicons-upload"></i>
                </label>
            </div>
            <?php $this->getValidationMessageLabel() ?>
            <script>
                jQuery(document).on('change', '#label_<?= $this->hook . $random_append ?> :file', function () {
                    var input = jQuery(this);

                    if (navigator.appVersion.indexOf("MSIE") != -1) { // IE
                        var label = input.val();
                        input.trigger('fileselect', [label, 0]);
                    } else {
                        if (typeof input.get(0).files[0] === 'undefined') return;
                        var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                        var size = input.get(0).files[0].size;

                        input.trigger('fileselect', [label, size]);
                    }
                });
                jQuery('#label_<?= $this->hook . $random_append ?> :file').on('fileselect', function (event, label, size) {
                    jQuery('#<?= $this->hook . $random_append ?>').closest('.tbk-field').find('.tbk-reservation-form-pointing-error').hide();
                    var fileExtentionRange = '<?= $this->getFileExtensions() ?>';
                    var MAX_SIZE = <?= $this->getMaxSize() ?>; // MB
                    jQuery('#<?= $this->hook . $random_append ?>').attr('name', '<?= $this->hook ?>'); // allow upload.
                    var postfix = label.substr(label.lastIndexOf('.'));
                    if (fileExtentionRange.indexOf(postfix.toLowerCase()) > -1) {
                        if (size > 1024 * 1024 * MAX_SIZE) {
                            alert('max size: ' + MAX_SIZE + ' MB.');
                            jQuery('#<?= $this->hook . $random_append ?>').removeAttr('name').val(''); // cancel upload file.
                            jQuery('#_<?= $this->hook . $random_append ?>').val('');
                        } else {
                            jQuery('#_<?= $this->hook . $random_append ?>').val(label);
                        }
                    } else {
                        jQuery('#<?= $this->hook . $random_append ?>').closest('.tbk-field').find('.tbk-reservation-form-pointing-error').show();
                        jQuery('#<?= $this->hook . $random_append ?>').removeAttr('name').val(''); // cancel upload file.
                        jQuery('#_<?= $this->hook . $random_append ?>').val('');
                    }
                });
                jQuery(document).ready(function () {
                    jQuery('#_<?= $this->hook . $random_append ?>').on('keyup', function (e) {
                        if (e.keyCode == 13) {
                            jQuery('#label_<?= $this->hook . $random_append ?>').trigger('click');
                            e.stopPropagation();
                        }
                    });
                })
            </script>
        </div>
        <?php
    }

    public function getProperties()
    {
        $properties = array(
            'hook'        => $this->getHook(),
            'description' => '',
            'required'    => $this->getIsRequired(),
            'visible'     => $this->getIsActive(),
            'title'       => htmlspecialchars_decode($this->getLabel(), ENT_QUOTES),
            'data'        => array(
                'max_size'        => $this->getMaxSize(),
                'file_extensions' => $this->getFileExtensions(TRUE)
            )
        );

        return $properties;
    }

}

