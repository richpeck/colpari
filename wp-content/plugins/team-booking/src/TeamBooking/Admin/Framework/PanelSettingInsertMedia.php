<?php

namespace TeamBooking\Admin\Framework;

/**
 * Class PanelSettingInsertMedia
 *
 * @since  2.3.0
 * @author VonStroheim
 */
class PanelSettingInsertMedia extends PanelSetting implements Element
{
    protected $field_classes = array();
    protected $default_media_id = NULL;


    public function addFieldClass($class)
    {
        $this->field_classes[] = $class;
    }

    public function addDefaultMediaId($media_id)
    {
        $this->default_media_id = $media_id;
    }

    public function render()
    {
        wp_enqueue_media();
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) echo '<p>' . $this->description . '</p>';
        ?>
        <p class="tbk-media-control <?= implode(' ', $this->field_classes) ?>"
           id="tbk-media-control<?= \TeamBooking\Toolkit\filterInput($this->fieldname, TRUE) ?>"
           data-target=".tbk-media-control-target"
           data-select-multiple="false">

            <input type="hidden" name="<?= $this->fieldname ?>"
                   value="<?php echo $this->default_media_id; ?>"
                   class="tbk-media-control-target">

            <?php
            $preview_img = wp_get_attachment_image($this->default_media_id, 'medium', FALSE, array('class' => 'tbk-preview-media'));
            if (!$preview_img) {
                echo '<a href="#" class="button tbk-media-control-choose"></a>';
                echo '<img src="" class="tbk-preview-media" alt="">';
                echo '<a href="#" class="button tbk-media-control-remove"></a>';
            } else {
                echo '<a href="#" class="button tbk-media-control-choose" style="display: none;"></a>';
                echo $preview_img;
                echo '<a href="#" class="button tbk-media-control-remove" style="display: inline-block;"></a>';
            }
            ?>

        </p>
        <?php $this->renderAlerts(); ?>
        <script>
            jQuery(document).ready(function ($) {
                var metaBox = $('#tbk-media-control<?= \TeamBooking\Toolkit\filterInput($this->fieldname, TRUE) ?>'),
                    addImgLink = metaBox.find('.tbk-media-control-choose'),
                    delImgLink = metaBox.find('.tbk-media-control-remove'),
                    imgContainer = metaBox.find('.tbk-preview-media'),
                    imgIdInput = metaBox.find('.tbk-media-control-target');
                var tbkMediaControl = {
                    frame: function () {
                        if (this._frame)
                            return this._frame;

                        this._frame = wp.media({
                            title   : '<?= esc_html__('Choose a logo', 'team-booking') ?>',
                            library : {
                                type: 'image'
                            },
                            button  : {
                                text: '<?= esc_html__('Select', 'team-booking') ?>'
                            },
                            multiple: false
                        });

                        this._frame.on('open', this.updateFrame).state('library').on('select', this.select);

                        return this._frame;
                    },

                    select: function () {
                        var attachment = tbkMediaControl.frame().state().get('selection').first().toJSON();
                        imgIdInput.val(attachment.id);
                        imgContainer.attr('src', attachment.url);
                        imgContainer.attr('width', attachment.width);
                        imgContainer.attr('height', attachment.height);
                        imgContainer.attr('alt', attachment.alt);
                        addImgLink.hide();
                        delImgLink.css('display', 'inline-block');
                    },

                    updateFrame: function () {
                        // Do something when the media frame is opened.
                    },

                    init: function () {
                        $('.tbk-wrapper').on('click keydown', '.tbk-media-control-choose', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.which == 13 || e.which == 32 || e.which == 1) {
                                tbkMediaControl.frame().open();
                            }
                        });
                        delImgLink.on('click keydown', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.which == 13 || e.which == 32 || e.which == 1) {
                                imgContainer.attr('src', '');
                                imgContainer.attr('srcset', '');
                                imgContainer.attr('width', '');
                                imgContainer.attr('height', '');
                                imgContainer.attr('alt', '');
                                imgIdInput.val('');
                                addImgLink.show();
                                $(this).hide();
                            }
                        });
                    }
                };

                tbkMediaControl.init();
            })
        </script>
        <?php
    }
}