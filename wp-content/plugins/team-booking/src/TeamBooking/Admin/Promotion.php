<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin\Framework\ElementFrom,
    TeamBooking\Functions,
    TeamBooking\Database;

/**
 * Class Promotion
 *
 * @author VonStroheim
 */
class Promotion
{
    private $settings;
    private $services;
    private $strings = array();

    public function __construct()
    {
        $this->settings = Functions\getSettings();
        $this->services = Database\Services::get();
        $this->strings = array(
            'wrong_date_format' => esc_html__('Wrong date or format. Please retry.', 'team-booking')
        );
    }

    /**
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">

            <div class="tbk-row">
                <div class="tbk-column tbk-span-12">
                    <?php $this->getPromotionsList()->render() ?>
                </div>
            </div>
        </div>

        <script>
            jQuery('.tbk-edit-promotion-modal').find('.tbk-promotion-services').on('click', 'input[type="checkbox"]', function () {
                var modal = jQuery(this).closest('.tbk-edit-promotion-modal');
                var promotion_to_edit = modal.data('promotion');
                if (this.checked) {
                    var base_price = jQuery('#' + promotion_to_edit + '-' + this.value + '-base').data('price');
                    modal.find('.tbk-modal-error-message').hide();
                    var discount = modal.find('input[name="discount_value"]').val();
                    var discount_type = modal.find('input[name="' + promotion_to_edit + '-discount_type"]:checked').val();
                    if (discount_type == 'percentage') {
                        if (discount > 100) {
                            discount = 100;
                        }
                        var discounted_price = base_price - (base_price * discount / 100);
                    } else {
                        var discounted_price = base_price - discount;
                        if (discounted_price < 0) {
                            discounted_price = 0;
                        }
                    }
                    jQuery('#' + promotion_to_edit + '-' + this.value + '-discounted')
                        .show()
                        .html(discounted_price)
                        .autoNumeric('update')
                    ;
                } else {
                    jQuery('#' + promotion_to_edit + '-' + this.value + '-discounted').html('')
                    ;
                }
            })
        </script>
        <script>
            jQuery('.tbk-edit-promotion-modal')
                .find('input[name$="-discount_type"]')
                .click(function () {
                    var modal = jQuery(this).closest('.tbk-edit-promotion-modal');
                    var promotion_name = modal.data('promotion');
                    var discount = jQuery(this).closest('tr').find('input[name="discount_value"]');
                    if (this.value == 'percentage') {
                        discount.attr('max', 100);
                        if (discount.val() > 100) {
                            discount.val(100);
                        }
                    } else {
                        discount.removeAttr('max');
                    }
                    modal
                        .find('input[type="checkbox"]:checked')
                        .each(function () {
                            var base_price = jQuery('#' + promotion_name + '-' + this.value + '-base').data('price');
                            var discount = modal.find('input[name="discount_value"]').val();
                            var discount_type = modal.find('input[name="' + promotion_name + '-discount_type"]:checked').val();
                            if (discount_type == 'percentage') {
                                if (discount > 100) {
                                    discount = 100;
                                }
                                var discounted_price = base_price - (base_price * discount / 100);
                            } else {
                                var discounted_price = base_price - discount;
                                if (discounted_price < 0) {
                                    discounted_price = 0;
                                }
                            }
                            jQuery('#' + promotion_name + '-' + this.value + '-discounted')
                                .html(discounted_price)
                                .autoNumeric('update')
                            ;
                        })
                });
        </script>
        <script>
            jQuery('.tbk-edit-promotion-modal')
                .find('input[name="discount_value"]')
                .on('change', function () {
                    var modal = jQuery(this).closest('.tbk-edit-promotion-modal');
                    var promotion_name = modal.data('promotion');
                    modal
                        .find('input[type="checkbox"]:checked')
                        .each(function () {
                            var base_price = jQuery('#' + promotion_name + '-' + this.value + '-base').data('price');
                            var discount = modal.find('input[name="discount_value"]').val();
                            var discount_type = modal.find('input[name="' + promotion_name + '-discount_type"]:checked').val();
                            if (discount_type == 'percentage') {
                                if (discount > 100) {
                                    discount = 100;
                                }
                                var discounted_price = base_price - (base_price * discount / 100);
                            } else {
                                var discounted_price = base_price - discount;
                                if (discounted_price < 0) {
                                    discounted_price = 0;
                                }
                            }
                            jQuery('#' + promotion_name + '-' + this.value + '-discounted')
                                .html(discounted_price)
                                .autoNumeric('update')
                            ;
                        })
                    ;
                })
        </script>
        <?php
        echo $this->getAreYouSureModal();

        return ob_get_clean();
    }

    /**
     * @return Framework\PanelForList
     */
    private function getPromotionsList()
    {
        $panel = new Framework\PanelForList(__('Promotions', 'team-booking'));
        $priced_service = FALSE;
        foreach ($this->services as $service) {
            if ($service->getPrice() > 0) {
                $priced_service = TRUE;
                break;
            }
        }
        if ($priced_service) {
            $button = new Framework\PanelTitleAddNewButton(__('New coupon', 'team-booking'));
            $button->setId('add-new-promotion-coupon');
            $panel->addTitleContent($button);
            $panel->addElement($this->getNewPromotionModal('coupon'));
            $button = new Framework\PanelTitleAddNewButton(__('New campaign', 'team-booking'));
            $button->setId('add-new-promotion-campaign');
            $panel->addTitleContent($button);
            $panel->addElement($this->getNewPromotionModal('campaign'));
        }

        ob_start();
        echo '<form method="post" class="ays-ignore">';
        $table = new PromotionTable();
        $table->views();
        $table->prepare_items();
        $table->display();
        echo '</form>';
        $table = ob_get_clean();

        $panel->addElement(ElementFrom::content($table));

        return $panel;
    }

    /**
     * @param $promotion_type
     *
     * @return Framework\Modal
     */
    private function getNewPromotionModal($promotion_type)
    {
        $modal = new Framework\Modal('tb-new-promotion-modal-' . $promotion_type);
        $modal->setHeaderText(array('main' => ($promotion_type === 'coupon') ? __('New coupon', 'team-booking') : __('New campaign', 'team-booking')));
        $modal->setButtonText(array('approve' => __('Add', 'team-booking'), 'deny' => __('Cancel', 'team-booking')));
        $modal->addErrorText(__('Error: please select at least one service', 'team-booking'));
        $currency_details = \TeamBooking\Toolkit\getCurrencies(Functions\getSettings()->getCurrencyCode());
        ob_start();
        ?>
        <ul class="tbk-list">
            <li>
                <h4><?= esc_html__('Name', 'team-booking') ?></h4>
                <?= Framework\Html::textfield(array('name' => 'promotion_name', 'placeholder' => 'e.g. special-offer', 'required' => TRUE)) ?>
                <p class="error name" style="display:none;">
                    <?= esc_html__('This name is already in use, please provide a fresh one.', 'team-booking') ?>
                </p>
            </li>

            <li>
                <h4><?= esc_html__('Discount', 'team-booking') ?></h4>
                <p><?= esc_html__('It will be applied to the base price of the service.', 'team-booking') ?></p>
                <table>
                    <tbody>
                    <tr>
                        <td>
                            <input type="number" name="discount_value" min="1" step="1" class="small-text"
                                   value="10">
                        </td>
                        <td>
                            <?= Framework\Html::radio(array(
                                'text'    => '%',
                                'name'    => $promotion_type . '-discount_type',
                                'value'   => 'percentage',
                                'checked' => TRUE
                            )) ?>
                            <br>
                            <?= Framework\Html::radio(array(
                                'text'  => Functions\currencyCodeToSymbol(),
                                'name'  => $promotion_type . '-discount_type',
                                'value' => 'direct'
                            )) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </li>
            <li>
                <h4><?= esc_html__('Promotion period', 'team-booking') ?></h4>
                <p><?= esc_html__('The promotion will be running within this date range.', 'team-booking') ?></p>
                <?= Framework\Html::textfield(array(
                        'name'        => 'validity_date_range',
                        'placeholder' => 'MM/DD/YYYY',
                        'required'    => TRUE,
                        'class'       => 'tb-flatpickr',
                        'data'        => array(
                            'mindate'          => 'today',
                            'dateformat'       => 'm/d/Y',
                            'altformat'        => str_replace('jS', 'J', get_option('date_format')),
                            'defaultdatestart' => 'today',
                            'defaultdateend'   => 'today',
                            'locale'           => TEAMBOOKING_SHORT_LANG,
                            'mode'             => 'range'
                        )
                    )
                ) ?>
                <p class="error start-date" style="display:none;">
                    <?= $this->strings['wrong_date_format'] ?>
                </p>
            </li>
        </ul>
        <?php
        $modal->addTab('general', __('General', 'team-booking'), ob_get_clean());
        ob_start();
        ?>
        <ul class="tbk-list">
            <li>
                <h4><?= esc_html__('Maximum uses', 'team-booking') ?></h4>
                <p><?= esc_html__('How many times (reservations) the discount can be used. Set 0 for no limit.', 'team-booking') ?></p>
                <input type="number" name="promotion_limit" min="0" step="1" class="small-text" value="0">
                <?php if ($promotion_type === 'coupon') { ?>
                    <p>
                        <em><?= esc_html__('This setting will be ignored if the coupon mode is set to "list"', 'team-booking') ?></em>
                    </p>
                <?php } ?>
            </li>
            <?php if ($promotion_type === 'coupon') { ?>
                <li>
                    <h4><?= esc_html__('Coupon mode', 'team-booking') ?></h4>
                    <p><?= esc_html__('If fixed, the coupon text will be equal to the promotion name. Otherwise, you can provide a list of comma-separated single use coupons. Check the documentation for more details.', 'team-booking') ?></p>
                    <?= Framework\Html::radio(array(
                        'text'    => esc_html__('fixed', 'team-booking'),
                        'name'    => $promotion_type . '-coupon_mode',
                        'value'   => 'fixed',
                        'checked' => TRUE
                    )) ?>
                    <br>
                    <?= Framework\Html::radio(array(
                        'text'  => esc_html__('list', 'team-booking'),
                        'name'  => $promotion_type . '-coupon_mode',
                        'value' => 'list'
                    )) ?>
                    <?= Framework\Html::textarea(array(
                        'placeholder' => esc_html__('List of comma-separated coupons (e.g. Summer171, Summer172, Summer173)', 'team-booking'),
                        'class'       => 'tbk-coupon-list-values',
                        'style'       => 'width:100%;display:none;',
                        'rows'        => 4,
                        'name'        => $promotion_type . '-coupon_list_values',
                        'value'       => ''
                    )) ?>
                    <script>
                        jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('.tb-advanced').on('click', 'input[name="<?= $promotion_type ?>-coupon_mode"]', function () {
                            $clicked = jQuery(this);
                            if (jQuery('input[name="coupon-coupon_mode"]:checked', '.tb-advanced').val() == 'list') {
                                $clicked.closest('li').find('.tbk-coupon-list-values').show();
                            } else {
                                $clicked.closest('li').find('.tbk-coupon-list-values').hide();
                            }
                        });
                    </script>
                </li>
            <?php } ?>
            <li>
                <h4><?= esc_html__('Timeslots min start date', 'team-booking') ?></h4>
                <p><?= esc_html__('If active, this promotion will be applied only to timeslots that begin from this date or subsequently.', 'team-booking') ?></p>
                <?= Framework\Html::textfield(array(
                    'name'        => 'bound_start_date',
                    'required'    => TRUE,
                    'placeholder' => 'MM/DD/YYYY',
                    'class'       => 'tb-flatpickr',
                    'data'        => array(
                        'mindate'     => 'today',
                        'dateformat'  => 'm/d/Y',
                        'altformat'   => str_replace('jS', 'J', get_option('date_format')),
                        'defaultdate' => 'today',
                        'locale'      => TEAMBOOKING_SHORT_LANG
                    )
                )) ?>
                <?= Framework\Html::checkbox(array(
                    'text'    => __('active', 'team-booking'),
                    'name'    => 'bound_start_date_active',
                    'checked' => FALSE
                ));
                ?>
                <p>
                    <em><?= esc_html__('This setting will be ignored by unscheduled services.', 'team-booking') ?></em>
                </p>
                <p class="error start-date" style="display:none;">
                    <?= esc_html__('Wrong date or format. Please retry.', 'team-booking') ?>
                </p>
            </li>
            <li>
                <h4><?= esc_html__('Timeslots max end date', 'team-booking') ?></h4>
                <p><?= esc_html__('If active, this promotion will be applied only to timeslots that end within this date.', 'team-booking') ?></p>
                <?= Framework\Html::textfield(array(
                    'name'        => 'bound_end_date',
                    'required'    => TRUE,
                    'placeholder' => 'MM/DD/YYYY',
                    'class'       => 'tb-flatpickr',
                    'data'        => array(
                        'mindate'     => 'today',
                        'dateformat'  => 'm/d/Y',
                        'altformat'   => str_replace('jS', 'J', get_option('date_format')),
                        'defaultdate' => 'today',
                        'locale'      => TEAMBOOKING_SHORT_LANG
                    )
                )) ?>
                <?= Framework\Html::checkbox(array(
                    'text'    => __('active', 'team-booking'),
                    'name'    => 'bound_end_date_active',
                    'checked' => FALSE
                ));
                ?>
                <p>
                    <em><?= esc_html__('This setting will be ignored by unscheduled services.', 'team-booking') ?></em>
                </p>
                <p class="error end-date" style="display:none;">
                    <?= esc_html__('Wrong date or format. Please retry.', 'team-booking') ?>
                </p>
            </li>
        </ul>
        <?php
        $modal->addTab('advanced', __('Advanced', 'team-booking'), ob_get_clean());
        ob_start();
        ?>
        <ul class="tbk-list">
            <li>
                <table class="tbk-promotion-services" style="width: 100%">
                    <tbody>
                    <tr>
                        <th>
                            <h4><?= esc_html__('Services', 'team-booking') ?></h4>
                        </th>
                        <th>
                            <h4><?= esc_html__('Base price', 'team-booking') ?></h4>
                        </th>
                        <th>
                            <h4><?= esc_html__('Discounted price', 'team-booking') ?></h4>
                        </th>
                    </tr>
                    <?php foreach ($this->services as $service) {
                        if ($service->getPrice() <= 0) {
                            continue;
                        }
                        ?>
                        <tr>
                            <td style="border-bottom: 1px dashed lightgrey;">
                                <?= Framework\Html::checkbox(array(
                                    'text'    => $service->getName(TRUE),
                                    'name'    => $service->getId(),
                                    'value'   => $service->getId(),
                                    'checked' => TRUE
                                )) ?>
                            </td>
                            <td style="border-bottom: 1px dashed lightgrey;">
                                        <span id="<?= $promotion_type . '-' . $service->getId() ?>-base"
                                              data-price="<?= $service->getPrice() ?>"
                                              class='tbk-promotion-service-base-price'>
                                            <?= Functions\currencyCodeToSymbol($service->getPrice()) ?>
                                        </span>
                            </td>
                            <td style="border-bottom: 1px dashed lightgrey;">
                                        <span id="<?= $promotion_type . '-' . $service->getId() ?>-discounted"
                                              data-a-sign="<?= Functions\currencyCodeToSymbol() ?>"
                                              class='tbk-promotion-service-discounted-price'>
                                        </span>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <script>
                    jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('.tbk-promotion-services').on('click', 'input[type="checkbox"]', function () {
                        if (this.checked) {
                            var base_price = jQuery('#<?= $promotion_type ?>-' + this.value + '-base').data('price');
                            var discount = jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('input[name="discount_value"]').val();
                            jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('.tbk-modal-error-message').hide();
                            var discount_type = jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('input[name="<?= $promotion_type ?>-discount_type"]:checked').val();
                            if (discount_type == 'percentage') {
                                if (discount > 100) {
                                    discount = 100;
                                }
                                var discounted_price = base_price - (base_price * discount / 100);
                            } else {
                                var discounted_price = base_price - discount;
                                if (discounted_price < 0) {
                                    discounted_price = 0;
                                }
                            }
                            jQuery('#<?= $promotion_type ?>-' + this.value + '-discounted')
                                .html(discounted_price)
                                .autoNumeric('init', {mDec: <?= $currency_details['decimal'] ? 2 : 0 ?>})
                            ;
                        } else {
                            jQuery('#<?= $promotion_type ?>-' + this.value + '-discounted').html('')
                                .autoNumeric('destroy')
                            ;
                        }
                    })
                </script>
                <script>
                    jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>')
                        .find('input[name="<?= $promotion_type ?>-discount_type"]')
                        .click(function () {
                            var discount = jQuery(this).closest('tr').find('input[name="discount_value"]');
                            if (this.value == 'percentage') {
                                discount.attr('max', 100);
                                if (discount.val() > 100) {
                                    discount.val(100);
                                }
                            } else {
                                discount.removeAttr('max');
                            }
                            jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>')
                                .find('input[type="checkbox"]:checked')
                                .each(function () {
                                    var base_price = jQuery('#<?= $promotion_type ?>-' + this.value + '-base').data('price');
                                    var discount = jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('input[name="discount_value"]').val();
                                    var discount_type = jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('input[name="<?= $promotion_type ?>-discount_type"]:checked').val();
                                    if (discount_type == 'percentage') {
                                        if (discount > 100) {
                                            discount = 100;
                                        }
                                        var discounted_price = base_price - (base_price * discount / 100);
                                    } else {
                                        var discounted_price = base_price - discount;
                                        if (discounted_price < 0) {
                                            discounted_price = 0;
                                        }
                                    }
                                    jQuery('#<?= $promotion_type ?>-' + this.value + '-discounted')
                                        .html(discounted_price)
                                        .autoNumeric('update')
                                    ;
                                })
                        });
                </script>
                <script>
                    jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>')
                        .find('input[name="discount_value"]')
                        .on('change', function () {
                            jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>')
                                .find('input[type="checkbox"]:checked')
                                .each(function () {
                                    var base_price = jQuery('#<?= $promotion_type ?>-' + this.value + '-base').data('price');
                                    var discount = jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('input[name="discount_value"]').val();
                                    var discount_type = jQuery('#tb-new-promotion-modal-<?= $promotion_type ?>').find('input[name="<?= $promotion_type ?>-discount_type"]:checked').val();
                                    if (discount_type == 'percentage') {
                                        if (discount > 100) {
                                            discount = 100;
                                        }
                                        var discounted_price = base_price - (base_price * discount / 100);
                                    } else {
                                        var discounted_price = base_price - discount;
                                        if (discounted_price < 0) {
                                            discounted_price = 0;
                                        }
                                    }
                                    jQuery('#<?= $promotion_type ?>-' + this.value + '-discounted')
                                        .html(discounted_price)
                                        .autoNumeric('update')
                                    ;
                                })
                            ;
                        })
                </script>
            </li>
        </ul>
        <?php
        $modal->addTab('services', __('Services', 'team-booking'), ob_get_clean());

        return $modal;
    }

    /**
     * @return string
     */
    private function getAreYouSureModal()
    {
        ob_start();
        // Confirmation Modal Markup
        $promotion_name_span = "<span class='promotion-name'></span>";
        $modal_content = sprintf(esc_html__('You are going to permanently delete %s', 'team-booking'), $promotion_name_span);
        $modal = new Framework\Modal('tbk-promotion-delete-modal');
        $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->addContent($modal_content);
        $modal->render();

        return ob_get_clean();
    }

}