<?php

namespace TeamBooking\Frontend\Components;

use TeamBooking\Order;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class PaymentChoices
 *
 * Shows the payment choices to the customer
 *
 * @author VonStroheim
 */
class PaymentChoices
{
    /**
     * @param \TeamBooking_ReservationData $data
     * @param bool                         $navigation
     */
    public static function render(\TeamBooking_ReservationData $data, $navigation = TRUE)
    {
        if ($navigation) echo NavigationHeader::InPaymentChoice($data->getServiceName(TRUE));
        $data->setToBePaid(TRUE);
        ?>
        <div class="tbk-pre-payment"
             data-checksum="<?= $data->getToken() ?>"
             data-id="<?= $data->getDatabaseId() ?>">
            <div class="ui stackable equal width center aligned tbk-grid tbk-payment-choices">
                <?php
                $active_payment_gateways = \TeamBooking\Functions\getSettings()->getPaymentGatewaysActive();
                $i = 1;
                foreach ($active_payment_gateways as $gateway) {
                    /* @var $gateway \TeamBooking_PaymentGateways_Settings */
                    ?>
                    <?php if ($i !== 1) { ?>
                        <div class="ui vertical tbk-divider">
                            <?= esc_html__('or', 'team-booking') ?>
                        </div>
                    <?php } ?>
                    <div class="tbk-column">
                        <?= $gateway->getPayButton() ?>
                    </div>
                    <?php
                    $i++;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return string
     */
    public static function get(\TeamBooking_ReservationData $data)
    {
        ob_start();
        self::render($data);

        return ob_get_clean();
    }

    /**
     * @param Order $order
     */
    public static function render_order(Order $order)
    {
        ?>
        <div class="tbk-pre-payment"
             data-order="<?= $order->getId() ?>"
             data-order-redirect="<?= $order->getRedirectUrl() ?>">
            <div class="ui stackable equal width center aligned tbk-grid tbk-payment-choices">
                <?php
                $active_payment_gateways = \TeamBooking\Functions\getSettings()->getPaymentGatewaysActive();
                $i = 1;
                foreach ($active_payment_gateways as $gateway) {
                    /* @var $gateway \TeamBooking_PaymentGateways_Settings */
                    ?>
                    <?php if ($i !== 1) { ?>
                        <div class="ui vertical tbk-divider">
                            <?= esc_html__('or', 'team-booking') ?>
                        </div>
                    <?php } ?>
                    <div class="tbk-column">
                        <?= $gateway->getPayButton() ?>
                    </div>
                    <?php
                    $i++;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param Order $data
     *
     * @return string
     */
    public static function get_order(Order $data)
    {
        ob_start();
        self::render_order($data);

        return ob_get_clean();
    }

}