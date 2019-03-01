<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts,
    TeamBooking\Functions;

/**
 * Form element: Radio Group
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Radio extends Abstracts\FormElement
{
    public function __construct()
    {
        parent::__construct();
        $this->setRequired(FALSE);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'radio';
    }

    /**
     * @param bool $hidden
     *
     * @return string
     */
    public function getMarkup($hidden = FALSE)
    {
        $random_append = substr(md5(mt_rand()), 0, 8);
        $checked = 'checked="checked"';
        ob_start();
        ?>
        <div class="tbk-field tbk-field-radio <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label style="display: block;">
                <?= $this->getTitle() ?>
            </label>
            <?php
            $i = 1;
            foreach ($this->getData('options') as $option) {
                if ($this->getData('value')) {
                    $checked = $this->getData('value') === $option['text'] ? 'checked="checked"' : NULL;
                }
                ?>
                <div class="tbk-radio">
                    <input id="tb-radio-<?= $this->getHook() . '-' . $random_append ?>" type="radio"
                           value="<?= esc_attr($option['text']) ?>" name="form_fields[<?= $this->getHook() ?>]"
                           data-price-inc="<?= $option['price_increment'] ?>"
                        <?= $checked ?>
                    >
                    <label for="tb-radio-<?= $this->getHook() . '-' . $random_append ?>">
                        <?= $this->wrapStringForTranslations(esc_attr($option['text']), 'option_' . $i) ?>
                        <?php if ($option['price_increment'] > 0) { ?>
                            <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol($option['price_increment']) ?>
                                </span>
                        <?php } ?>
                    </label>
                </div>
                <?php
                $random_append = substr(md5(mt_rand()), 0, 8);
                if (!$this->getData('value')) {
                    $checked = NULL;
                }
                $i++;
            }
            ?>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
            <script>
                jQuery(document).ready(function ($) {
                    $('.tbk-radio input[type="radio"][name="form_fields[<?= $this->getHook()?>]"]').on('change', function () {
                        $(this).closest('.tbk-slide').find('.tbk-book-now-button').trigger('amount:update');
                        $(this).closest('.tbk-slide').find('.tbk-tickets-price-section').trigger('tickets:unitprice:update');
                    });
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}