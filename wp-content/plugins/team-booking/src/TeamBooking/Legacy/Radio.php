<?php

// Blocks direct access to this file
defined('ABSPATH') or die("No script kiddies please!");

#TeamBookingFormRadio extends TeamBookingFormSelect
#TeamBooking_Components_Form_Radio

use TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\Radio
 *
 * Class TeamBookingFormRadio
 */
class TeamBookingFormRadio extends TeamBookingFormSelect
{

    // HTML Semantic UI markup
    public function getMarkup($input_size = '')
    {
        $random_append = substr(md5(mt_rand()), 0, 8);
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label
                style="display: block;"><?= $this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)) ?></label>
            <?php if (!empty($this->value)) {
                //legacy
                if (!($this->value instanceof TeamBooking_Components_Form_Option)) {
                    $this->value = new TeamBooking_Components_Form_Option($this->value);
                }
                ?>
                <div class="tbk-radio">
                    <input id="tb-radio-<?= $this->hook . "-" . $random_append ?>" type="radio"
                           value="<?= $this->value->getText() ?>" name="form_fields[<?= $this->hook ?>]"
                           data-price-inc="<?= $this->value->getPriceIncrement() ?>"
                           checked="checked">
                    <label for="tb-radio-<?= $this->hook . "-" . $random_append ?>">
                        <?= $this->wrapStringForTranslations($this->value->getText()) ?>
                        <?php if ($this->value->getPriceIncrement() > 0) { ?>
                            <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol($this->value->getPriceIncrement()) ?>
                                </span>
                        <?php } ?>
                    </label>
                </div>
                <?php
                $random_append = substr(md5(rand()), 0, 8);
            }
            foreach ($this->getOptions() as $option) {
                //legacy
                if (!($option instanceof TeamBooking_Components_Form_Option)) {
                    $option = new TeamBooking_Components_Form_Option($option);
                }
                ?>
                <div class="tbk-radio">
                    <input id="tb-radio-<?= $this->hook . "-" . $random_append ?>" type="radio"
                           value="<?= $option->getText() ?>" name="form_fields[<?= $this->hook ?>]"
                           data-price-inc="<?= $option->getPriceIncrement() ?>"
                    >
                    <label for="tb-radio-<?= $this->hook . "-" . $random_append ?>">
                        <?= $this->wrapStringForTranslations($option->getText()) ?>
                        <?php if ($option->getPriceIncrement() > 0) { ?>
                            <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol($option->getPriceIncrement()) ?>
                                </span>
                        <?php } ?>
                    </label>
                </div>
                <?php
                $random_append = substr(md5(rand()), 0, 8);
            }
            ?>
            <?php $this->getValidationMessageLabel() ?>
        </div>
        <?php
    }

    public function getProperties()
    {
        $properties = array(
            'hook'        => $this->getHook(),
            'description' => '',
            'visible'     => $this->getIsActive(),
            'title'       => htmlspecialchars_decode($this->getLabel(), ENT_QUOTES)
        );
        if (!($this->value instanceof TeamBooking_Components_Form_Option)) {
            $this->value = new TeamBooking_Components_Form_Option($this->value);
        }
        $properties['data']['options'][] = array(
            'text'            => htmlspecialchars_decode($this->value->getText(), ENT_QUOTES),
            'price_increment' => $this->value->getPriceIncrement()
        );
        foreach ($this->getOptions() as $option) {
            if (!($option instanceof TeamBooking_Components_Form_Option)) {
                $option = new TeamBooking_Components_Form_Option($option);
            }
            $properties['data']['options'][] = array(
                'text'            => htmlspecialchars_decode($option->getText(), ENT_QUOTES),
                'price_increment' => $option->getPriceIncrement()
            );
        }

        return $properties;
    }

}

