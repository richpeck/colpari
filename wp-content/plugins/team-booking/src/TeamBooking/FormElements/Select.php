<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts,
    TeamBooking\Functions;

/**
 * Form element: Select
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Select extends Abstracts\FormElement
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'select';
    }

    public function getMarkup($hidden = FALSE)
    {
        $random = \TeamBooking\Toolkit\randomNumber(6);
        ob_start();
        ?>
        <div class="tbk-field tbk-field-select <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label><?= $this->getTitle() ?></label>

            <div class="tbk-dropdown" id="tb-<?= $this->getHook() . $random ?>" tabindex="0">
                <input type="hidden"
                       name="form_fields[<?= $this->getHook() ?>]" <?= $this->isRequired() ? "required='required'" : '' ?>
                    <?= $this->getData('value') ? "value='" . $this->getData('value') . "'" : '' ?>>
                <div class="default tbk-text"><?= esc_html__('Select...', 'team-booking') ?></div>
                <i class="dropdown tb-icon"></i>

                <div class="tbk-menu">
                    <?php
                    $i = 1;
                    foreach ($this->getData('options') as $option) { ?>
                        <div class="tbk-item <?= $this->getData('value') === $option['text'] ? 'active selected' : '' ?>"
                             data-value="<?= $option['text'] ?>"
                             data-price-inc="<?= $option['price_increment'] ?>">
                            <?= $this->wrapStringForTranslations($option['text'], 'option_' . $i) ?>
                            <?php if ($option['price_increment'] > 0) { ?>
                                <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol($option['price_increment']) ?>
                                </span>
                            <?php } ?>
                        </div>
                        <?php
                        $i++;
                    } ?>
                </div>
            </div>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
            <script>
                jQuery(document).ready(function ($) {
                    $('#tb-<?= $this->getHook() . $random ?>').on('click keydown', function (event) {
                        var $dropdown = $(this);
                        $dropdown.closest('.tb-frontend-calendar').css('overflow', 'visible');
                        var keycode = (event.which ? event.which : 13);
                        if (keycode != 13 && keycode != 32 && keycode != 1) {
                            // keyboard navigation
                            var item = $dropdown.find('.tbk-item');
                            var itemSelected = $dropdown.find('.tbk-item.selected');
                            var next;
                            if (keycode === 40) {
                                if (itemSelected) {
                                    itemSelected.removeClass('active selected');
                                    next = itemSelected.next();
                                    if (next.length > 0) {
                                        next.addClass('active selected');
                                    } else {
                                        item.eq(0).addClass('active selected');
                                    }
                                } else {
                                    item.eq(0).addClass('active selected');
                                }
                                event.stopPropagation();
                                return false;
                            } else if (keycode === 38) {
                                if (itemSelected) {
                                    itemSelected.removeClass('active selected');
                                    next = itemSelected.prev();
                                    if (next.length > 0) {
                                        next.addClass('active selected');
                                    } else {
                                        item.last().addClass('active selected');
                                    }
                                } else {
                                    item.last().addClass('active selected');
                                }
                                event.stopPropagation();
                                return false;
                            }
                            return true;
                        }
                        event.stopPropagation();
                        $dropdown.find('.tbk-menu').toggle();
                        $dropdown.trigger('tbkUpdatePrice');
                        return false;
                    })
                        .on('mousedown', '.tbk-item', function (event) {
                            event.stopPropagation();
                            $(this).closest('.tbk-menu').find('.tbk-item').removeClass('active selected');
                            $(this).addClass('active selected');
                            $(this).closest('.tbk-menu').hide();
                            $(this).closest('.tb-frontend-calendar').css('overflow', 'hidden');
                            $(this).closest('.tbk-dropdown').trigger('tbkUpdatePrice');
                        })
                        .focusout(function () {
                            $(this).closest('.tb-frontend-calendar').css('overflow', 'hidden');
                            $(this).find('.tbk-menu').hide();
                        })
                        .keyup(function (event) {
                            var $dropdown = $(this);
                            if (event.which == 9 && $dropdown.is(':focus')) {
                                $dropdown.find('.tbk-menu').show();
                                $dropdown.closest('.tb-frontend-calendar').css('overflow', 'visible');
                            }
                        })
                        .on('tbkUpdatePrice', function () {
                            $(this).closest('.tbk-field').removeClass('tbk-error');
                            $(this).find('.tbk-text').removeClass('default').html($(this).find('.tbk-item.selected').html());
                            $(this).find('input').val($(this).find('.tbk-item.selected').data('value'));
                            $(this).closest('.tbk-slide').find('.tbk-book-now-button').trigger('amount:update');
                            $(this).closest('.tbk-slide').find('.tbk-tickets-price-section').trigger('tickets:unitprice:update');
                        })
                    ;
                    <?php if ($this->getData('value')) { ?>
                    $('#tb-<?= $this->getHook() . $random ?>').trigger('tbkUpdatePrice');
                    <?php } ?>
                })
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}