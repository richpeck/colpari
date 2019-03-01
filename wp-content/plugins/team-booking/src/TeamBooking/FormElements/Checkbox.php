<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts;

/**
 * Form element: Checkbox
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Checkbox extends Abstracts\FormElement
{
    public function __construct()
    {
        parent::__construct();
        $this->setData('price_increment', 0);
        $this->setData('checked', FALSE);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'checkbox';
    }

    /**
     * @param bool $hidden
     *
     * @return mixed
     */
    public function getMarkup($hidden = FALSE)
    {
        ob_start();
        $random_append = substr(md5(mt_rand()), 0, 8);
        ?>
        <div class="tbk-field tbk-field-checkbox <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label for="tbk-<?= $this->getHook() ?>-<?= $random_append ?>">
                <?= $this->getTitle() ?>
            </label>
            <div class="tbk-checkbox">
                <input id="tbk-<?= $this->getHook() ?>-<?= $random_append ?>"
                       name="form_fields[<?= $this->getHook() ?>]"
                       type="checkbox"
                       value="<?= $this->getData('value') ?>" <?php checked(TRUE, $this->getData('checked')) ?> <?= $this->isRequired() ? "required='required'" : '' ?>>
                <label></label>
            </div>
            <?php $this->getValidationMessageLabel() ?>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
            <script>
                jQuery(document).ready(function () {
                    jQuery("#tbk-<?= $this->getHook() ?>-<?= $random_append ?>").on('keyup', function (e) {
                        if (e.keyCode == 13) {
                            jQuery(this).prop("checked", !jQuery(this).prop("checked"));
                            e.stopPropagation();
                        }
                    });
                })
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function getValidationMessageLabel()
    {
        ?>
        <div class="tbk-reservation-form-pointing-error" style="display:none;">
            <?= esc_html__('Please enter a correct value', 'team-booking') ?>
        </div>
        <?php
    }
}