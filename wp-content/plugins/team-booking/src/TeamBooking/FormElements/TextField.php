<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts;

/**
 * Form element: Text Field
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class TextField extends Abstracts\FormElement
{
    /**
     * TextField constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setData('validation', array(
            'validation_regex' => array(
                'email'        => '^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$',
                'alphanumeric' => '^[a-zA-Z0-9]+$',
                'phone'        => '^(1\s*[-\/\.]?\s*)?(\((\d{3})\)|(\d{3}))\s*[-\/\.]?\s*(\d{3})\s*[-\/\.]?\s*(\d{4})\s*(([xX]|[eE][xX][tT]?)\.?\s*([#*\d]+))*$',
                'custom'       => NULL
            ),
            'validate'         => FALSE
        ));
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'text_field';
    }

    /**
     * @param bool $hidden
     *
     * @return string
     */
    public function getMarkup($hidden = FALSE)
    {
        ob_start();
        if ($hidden) { ?>
            <input type="hidden" name="form_fields[<?= esc_attr($this->getHook()) ?>]"
                   value="<?= esc_attr($this->getData('value')) ?>">
            <?php
        } else {
            $validation_regex = '';
            if ($this->data['validation']['validate']) {
                $validation_regex = 'data-validation="' . base64_encode($this->data['validation']['validation_regex'][ $this->data['validation']['validate'] ]) . '"';
            }
            $random_append = substr(md5(mt_rand()), 0, 8);
            $type = 'text';
            if ($this->getHook() === 'email') $type = 'email';
            if ($this->getHook() === 'url') $type = 'url';
            ?>
            <div class="tbk-field tbk-field-text <?= $this->isRequired() ? 'tbk-required' : '' ?>">
                <label><?= $this->getTitle() ?></label>
                <input id="tbk-<?= esc_attr($this->hook) ?>-<?= $random_append ?>" type="<?= $type ?>"
                       name="form_fields[<?= esc_attr($this->hook) ?>]" value="<?= esc_attr($this->getData('value')) ?>"
                       style="height:inherit;max-width: none;" <?= $this->required ? "required='required'" : '' ?> <?= $validation_regex ?>>
                <?php $this->getValidationMessageLabel() ?>
                <p class="tbk-field-description"><?= $this->getDescription() ?></p>

                <?php if ($this->getData('value_confirmation')) { ?>
                    <input id="tbk-<?= esc_attr($this->hook) ?>-<?= $random_append ?>-value_confirmation"
                           type="<?= $type ?>"
                           name="trash[<?= esc_attr($this->hook) ?>_value_confirmation]"
                           class="tbk-input-value-confirmation"
                           placeholder="<?= $this->getTitle() ?> (<?= __('confirmation', 'team-booking') ?>)"
                           style="height:inherit;max-width: none;margin-top: 1em;">
                    <?php $this->getValueConfirmationMessageLabel() ?>
                <?php } ?>
            </div>
            <?php if ($this->getHook() === 'address' && NULL !== \TeamBooking\Functions\getSettings()->getGmapsApiKey()) { ?>
                <!-- GeoComplete -->
                <script>
                    jQuery(document).ready(function ($) {
                        if (typeof google !== 'undefined' && typeof google.maps === 'object' && typeof google.maps.places === 'object') {
                            $("#tbk-<?= $this->hook . '-' . $random_append ?>").geocomplete();
                        }
                    });
                </script>
            <?php }
        }

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

    protected function getValueConfirmationMessageLabel()
    {
        ?>
        <div class="tbk-reservation-form-pointing-error tbk-value-confirmation" style="display:none;">
            <?= esc_html__("The value doesn't match", 'team-booking') ?>
        </div>
        <?php
    }
}