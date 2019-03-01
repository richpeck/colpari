<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts;

/**
 * Form element: Text Area
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class TextArea extends Abstracts\FormElement
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'text_area';
    }

    /**
     * @param bool $hidden
     *
     * @return string
     */
    public function getMarkup($hidden = FALSE)
    {
        ob_start();
        ?>
        <div class="tbk-field tbk-field-textarea <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label><?= $this->getTitle() ?></label>
            <textarea name="form_fields[<?= $this->getHook() ?>]"
                      style="height:inherit" <?= $this->isRequired() ? "required='required'" : '' ?>
            ><?= $this->getData('value') ?></textarea>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}