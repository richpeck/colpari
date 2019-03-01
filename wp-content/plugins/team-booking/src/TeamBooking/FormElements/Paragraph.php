<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts;

class Paragraph extends Abstracts\FormElement
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'paragraph';
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
        <div class="tbk-paragraph tbk-field">
            <label><?= $this->getTitle() ?></label>
            <div class="tbk-field-description">
                <?= $this->getDescription() ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}