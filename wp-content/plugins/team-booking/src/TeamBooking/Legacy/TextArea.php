<?php
// Blocks direct access to this file
defined('ABSPATH') or die("No script kiddies please!");

#TeamBookingFormTextarea extends TeamBookingFormTextField
#TeamBooking_Components_Form_TextArea

use TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\TextArea
 *
 * Class TeamBookingFormTextarea
 */
class TeamBookingFormTextarea extends TeamBookingFormTextField
{

    // HTML Semantic UI mapper
    public function getMarkup($input_size = '')
    {
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label><?= $this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)) ?></label>
            <textarea name="form_fields[<?= $this->hook ?>]"
                      style="height:inherit" <?= $this->required ? "required='required'" : "" ?>><?= $this->value ?></textarea>
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
            'title'       => Toolkit\unfilterInput($this->label)
        );
        $properties['data']['value'] = $this->getValue() instanceof TeamBooking_Components_Form_Option ? $this->getValue()->getText() : $this->getValue();

        return $properties;
    }

}
