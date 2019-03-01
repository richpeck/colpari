<?php

namespace TeamBooking\Frontend\Components;

use TeamBooking\Abstracts\Service;
use TeamBooking\Database\Services;
use TeamBooking\Slot,
    TeamBooking\Functions;

/**
 * Class Summary
 *
 * @author VonStroheim
 * @since  2.5.0
 */
class Summary
{

    /**
     * @param Service $service
     *
     * @return string
     */
    public static function service_header(Service $service)
    {
        ob_start();
        ?>
        <div class="tbk-cart-summary-service-header"
             style="border-top: 1px solid <?= $service->getColor() ?>;background-color:<?= \TeamBooking\Toolkit\hex2RGBa($service->getColor(), 0.6) ?>;color:<?= Functions\getRightTextColor($service->getColor(), TRUE) ?> ">
            <?= esc_html($service->getName(TRUE)) ?>
            <span class="tbk-edit-form" data-service="<?= $service->getId() ?>" tabindex="0">
                (<?= esc_html__('edit the form', 'team-booking') ?>)
            </span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param Slot                         $slot
     * @param \TeamBooking_ReservationData $reservation
     *
     * @return mixed
     */
    public static function slot_row(Slot $slot, \TeamBooking_ReservationData $reservation)
    {
        try {
            $service = Services::get($reservation->getServiceId());
            $service_payment = $service->getSettingsFor('payment');
            $tickets_left = 1;
            if ($service->getClass() === 'event') {
                if (is_user_logged_in()) {
                    $tickets_left = $slot->getTicketsLeft(get_current_user_id());
                } else {
                    $tickets_left = $slot->getTicketsLeft($reservation->getCustomerEmail());
                }
            }
            $reservation_data_param = '';
            if ($tickets_left > 0) {
                $reservation_data_param = 'data-reservation="' . \TeamBooking\Toolkit\objEncode($reservation) . '"';
            }
            ob_start();
            ?>
            <tr <?= $reservation_data_param ?> data-slot-id="<?= wp_hash($slot->getUniqueId()) ?>">
                <td>
                    <div class="tbk-summary-slot-header">
                        <span class="tbk-date"><?= $slot->getDateString() ?></span>
                        <span class="tbk-time"><?= $slot->getTimesString() ?></span>
                    </div>
                    <div class="tbk-summary-slot-details">
                        <?php if ($service->getSettingsFor('show_coworker')) { ?>
                            <span>
                                <?= sprintf(esc_html__('Service provider: %s', 'team-booking'), $slot->getCoworkerDisplayString()) ?>
                            </span>
                        <?php } ?>
                        <?php if ($service->getClass() === 'event') { ?>
                            <div>
                                <?php if ($tickets_left > 0) { ?>
                                    <?= esc_html__('Tickets:', 'team-booking') ?>
                                    <input type="number" name="tickets[<?= $reservation->getToken() ?>]"
                                           value="<?= $reservation->getTickets() ?>" pattern="[0-9]"
                                           step="1" min="1" max="<?= $tickets_left ?>"
                                           style="width: 65px;padding: 2px 5px;vertical-align: baseline;">
                                <?php } else { ?>
                                    <div class="tbk-summary-tickets-limit">
                                        <?= esc_html__("You can't book any more tickets for this slot", 'team-booking') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </td>
                <?php if ($service->getPrice() > 0) { ?>
                    <td class="tbk-summary-row-payment">
                        <?php if ($tickets_left > 0) {
                            $id = 'tbk-summary-row-unit-price' . \TeamBooking\Toolkit\randomNumber(10);
                            $currency = \TeamBooking\Toolkit\getCurrencies(Functions\getSettings()->getCurrencyCode());
                            ?>
                            <span class="tbk-summary-row-unit-price" id="<?= $id ?>"
                                  data-raw-unit-price="<?= $reservation->getPriceIncremented() ?>">
                                <?= Functions\currencyCodeToSymbol($reservation->getPriceIncremented(), $reservation->getCurrencyCode()) ?>
                                <script>
                                    jQuery('#<?=$id?>').tbkSummaryRowPrice({
                                        currencySymbol  : '<?=$currency['symbol']?>',
                                        currencyFormat  : '<?=$currency['format']?>',
                                        decimals        : <?=$currency['decimal'] ? 2 : 0?>,
                                        unitPrice       : <?=$reservation->getPriceDiscounted()?>,
                                        incrementedPrice: <?=$reservation->getPriceIncremented()?>
                                    });
                                </script>
                            </span>
                            <span class="tbk-payment-info" style="display: block;">
                                <?php if ($service_payment === 'discretional' && !Functions\isAdmin()) { ?>
                                    <span class="tbk-summary-pay-if-you-want"><?= esc_html__('pay now', 'team-booking') ?></span>
                                    <input type="checkbox" name="to_be_paid[<?= $reservation->getToken() ?>]"
                                           value="1">
                                <?php } elseif ($service_payment === 'later') { ?>
                                    <span class="tbk-summary-pay-later"><?= esc_html__('later', 'team-booking') ?></span>
                                <?php } elseif ($service_payment === 'immediately') { ?>
                                    <span class="tbk-summary-pay-immediately"><?= esc_html__('immediate', 'team-booking') ?></span>
                                <?php } ?>
                            </span>
                        <?php } else { ?>
                            <div class="tbk-summary-tickets-limit">
                                <?= esc_html__("You can't book any more tickets for this slot", 'team-booking') ?>
                            </div>
                        <?php } ?>
                    </td>
                <?php } ?>
            </tr>
            <?php
            return ob_get_clean();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public static function skip_payment_notice()
    {
        ob_start(); ?>
        <div class="tbk-summary-skip-payment-notice">
            <div class="ui negative message">
                <?= esc_html__('You are logged-in as Administrator. You will skip any eventual payment step!', 'team-booking') ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function coupon_line()
    {
        $id = 'tbk-coupon-cart-' . \TeamBooking\Toolkit\randomNumber(10);
        ob_start();
        ?>
        <div class="tbk-summary-coupon" id="<?= $id ?>">
            <input class="tbk-summary-coupon-input" type="text" name="tbk_coupon"
                   placeholder="<?= esc_html(__('Coupon?', 'team-booking')) ?>">
            <span class="tbk-summary-coupon-applied"><span></span></span>
            <button class="tbk-button tbk-summary-apply-coupon"></button>
        </div>
        <script>
            jQuery('#<?= $id ?>').tbkCouponInput({
                applyButtonText : '<?= esc_html(__('Apply', 'team-booking')) ?>',
                removeButtonText: '<?= esc_html(__('Remove', 'team-booking')) ?>',
                wrongCouponText : '<?= esc_html(__('Wrong or expired code!', 'team-booking')) ?>',
                isCheckout      : true
            });
        </script>
        <?php
        return ob_get_clean();
    }

    public static function footer_actions($total_amount_due, $total)
    {
        $formatted_amount = $total ? Functions\currencyCodeToSymbol($total) : '';
        $formatted_amount_due = $total_amount_due ? Functions\currencyCodeToSymbol($total_amount_due) : '';
        $currency_format = Functions\currencyCodeToSymbol(0, NULL, 0, TRUE);
        $currency_symbol = htmlentities(Functions\currencyCodeToSymbol());
        $currency_array = \TeamBooking\Toolkit\getCurrencies(Functions\getSettings()->getCurrencyCode());
        $id = 'tbk-checkout-confirm-' . \TeamBooking\Toolkit\randomNumber(10);
        ob_start();
        ?>
        <div class="tbk-summary-footer">
            <button class="tbk-button tbk-cart-cancel-process">
                <?= esc_html(__('Cancel', 'team-booking')) ?>
            </button>
            <button id="<?= $id ?>" class="tbk-button tbk-checkout-confirm" type="submit">
                <?= esc_html(__('Confirm', 'team-booking')) ?>
                <span class="tbk-amount"><?= $formatted_amount ?></span>
                <span class="tbk-to-pay-now"
                      style="display: <?= ($total_amount_due !== $total && $total_amount_due > 0 ? 'block' : 'none') ?>">
                    <?= esc_html(__('To pay now', 'team-booking')) ?>
                    <span class="tbk-amount-to-be-paid"><?= $formatted_amount_due ?></span>
                </span>
            </button>
            <script>
                jQuery('#<?= $id ?>').tbkAmountButton({
                    isCheckout    : true,
                    buttonText    : '<?= esc_html(__('Confirm', 'team-booking')) ?>',
                    toPayNowText  : '<?= esc_html(__('To pay now', 'team-booking')) ?>',
                    currencySymbol: '<?= html_entity_decode($currency_symbol) ?>',
                    currencyFormat: '<?= $currency_format ?>',
                    decimals      : <?= $currency_array['decimal'] === TRUE ? 2 : 0 ?>
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function table_open(Service $service)
    {
        echo '<table class="tbk-summary-table"><thead><tr class="tbk-header-row" style="background:' . \TeamBooking\Toolkit\hex2RGBa($service->getColor(), 0.2) . '">';
        echo '</tr></thead><tbody>';
    }

    public static function table_close()
    {
        echo '</tbody></table>';
    }
}