<?php

defined('ABSPATH') or die('No script kiddies please!');

#TeamBookingFormSelect extends TeamBookingFormTextField
#TeamBooking_Components_Form_Select

use TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\Select
 *
 * Class TeamBookingFormSelect
 */
class TeamBookingFormSelect extends TeamBookingFormTextField
{
    protected $options = array();

    //------------------------------------------------------------

    public function addOption(TeamBooking_Components_Form_Option $option)
    {
        $this->options[] = $option;
    }

    //------------------------------------------------------------

    public function resetOptions()
    {
        $this->options = array();
    }

    //------------------------------------------------------------

    /**
     * @return TeamBooking_Components_Form_Option[]
     */
    public function getOptions()
    {
        // legacy
        foreach ($this->options as $key => $option) {
            if (!($option instanceof TeamBooking_Components_Form_Option)) {
                $this->options[ $key ] = new TeamBooking_Components_Form_Option($option);
            }
        }

        return $this->options;
    }

    //------------------------------------------------------------

    public function getMarkup($input_size = '')
    {
        $random = Toolkit\randomNumber(6);
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label><?= $this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)) ?></label>

            <div class="tbk-dropdown" id="tb-<?= $this->hook . $random ?>" tabindex="0">
                <input type="hidden"
                       name="form_fields[<?= $this->hook ?>]" <?= $this->required ? "required='required'" : '' ?>>

                <div class="default tbk-text"><?= esc_html__('Select...', 'team-booking') ?></div>
                <i class="dropdown tb-icon"></i>

                <div class="tbk-menu">
                    <?php
                    //legacy
                    if (!($this->value instanceof TeamBooking_Components_Form_Option)) {
                        $this->value = new TeamBooking_Components_Form_Option($this->value);
                    }
                    ?>
                    <div class="tbk-item" data-value="<?= $this->value->getText() ?>">
                        <?= $this->value->getText() ?>
                        <?php if ($this->value->getPriceIncrement() > 0) { ?>
                            <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol($this->value->getPriceIncrement()) ?>
                            </span>
                        <?php } ?>
                    </div>
                    <?php foreach ($this->getOptions() as $option) {
                        //legacy
                        if (!($option instanceof TeamBooking_Components_Form_Option)) {
                            $option = new TeamBooking_Components_Form_Option($option);
                        }
                        ?>
                        <div class="tbk-item" data-value="<?= $option->getText() ?>"
                             data-price-inc="<?= $option->getPriceIncrement() ?>">
                            <?= $this->wrapStringForTranslations($option->getText()) ?>
                            <?php if ($option->getPriceIncrement() > 0) { ?>
                                <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol($option->getPriceIncrement()) ?>
                                </span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php $this->getValidationMessageLabel() ?>
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
