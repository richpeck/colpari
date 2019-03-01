<?php

// Blocks direct access to this file
defined('ABSPATH') or die("No script kiddies please!");

#TeamBookingFormCheckbox extends TeamBookingFormTextField
#TeamBooking_Components_Form_Checkbox

use TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\Checkbox
 *
 * Class TeamBookingFormCheckbox
 */
class TeamBookingFormCheckbox extends TeamBookingFormTextField
{
    //------------------------------------------------------------

    protected $checked;

    //------------------------------------------------------------

    public function setCheckedOn()
    {
        $this->checked = TRUE;
    }

    public function setCheckedOff()
    {
        $this->checked = FALSE;
    }

    public function isChecked()
    {
        return $this->checked;
    }

    //------------------------------------------------------------

    // HTML Semantic UI mapper
    public function getMarkup($input_size = '')
    {
        $random_append = substr(md5(rand()), 0, 8);
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label for="tbk-<?= $this->hook ?>-<?= $random_append ?>">
                <?= $this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)) ?>
            </label>
            <div class="tbk-checkbox">
                <input id="tbk-<?= $this->hook ?>-<?= $random_append ?>" name="form_fields[<?= $this->hook ?>]"
                       type="checkbox"
                       value="<?= $this->value ?>" <?php checked(TRUE, $this->checked) ?> <?= $this->required ? "required='required'" : "" ?>>
                <label></label>
            </div>
            <?php $this->getValidationMessageLabel() ?>
            <script>
                jQuery(document).ready(function () {
                    jQuery("#tbk-<?= $this->hook ?>-<?= $random_append ?>").on('keyup', function (e) {
                        if (e.keyCode == 13) {
                            jQuery(this).prop("checked", !jQuery(this).prop("checked"));
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
                'price_increment' => 0,
                'checked'         => $this->isChecked()
            )
        );

        return $properties;
    }

}
