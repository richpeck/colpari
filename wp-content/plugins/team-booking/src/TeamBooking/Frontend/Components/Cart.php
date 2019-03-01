<?php

namespace TeamBooking\Frontend\Components;

use TeamBooking\Slot;
use TeamBooking\Toolkit;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Cart
 *
 * @author VonStroheim
 * @since  2.5.0
 */
class Cart
{
    /**
     * @param Slot $item
     *
     * @return mixed
     */
    public static function menuItem(Slot $item)
    {
        ob_start(); ?>
        <div class="tbk-menu-item">
            <button class="tbk-button tbk-cart-remove-item"
                    data-item="<?= Toolkit\objEncode($item->getUniqueId()) ?>"
                    data-type="slot">
                <?= esc_html__('Remove', 'team-booking') ?>
            </button>
            <span><strong><?= $item->getServiceName(TRUE) ?></strong></span>
            <span><?= $item->getDateString() ?></span>
            <span style="font-style: italic"><?= $item->getTimesString() ?></span>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @return string
     */
    public static function checkoutButton()
    {
        ob_start(); ?>
        <div class="tbk-menu-item tbk-cart-booking">
            <?= esc_html__('Proceed with the booking', 'team-booking') ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param $number
     *
     * @return mixed
     */
    public static function numberedDot($number)
    {
        ob_start(); ?>
        <span class="ui mini tbk-circular label tbk-cart-dot"><?= $number ?></span>
        <?php return ob_get_clean();
    }

    public static function countdown($elapsed, $interval)
    {
        ob_start(); ?>
        <span class="tbk-cart-countdown" data-elapsed="<?= $elapsed ?>" data-interval="<?= $interval ?>"></span>
        <?php return ob_get_clean();
    }

    /**
     * @param bool $is_widget
     *
     * @return string
     */
    public static function getCartButton($is_widget = FALSE)
    {
        $cart = \TeamBooking\Cart::loadCart();
        $in_cart_slots = $cart::getSlots();
        ob_start(); ?>
        <div class="tbk-setting-button tbk-cart <?= empty($in_cart_slots) ? 'tbk-cart-empty' : '' ?>"
             tabindex="0" <?= empty($in_cart_slots) ? 'style="display:none;"' : '' ?>
             title="<?= esc_html__('Selected slots', 'team-booking') ?>"
             aria-label="<?= esc_html__('Selected slots', 'team-booking') ?>">
            <i class="archive tb-icon"></i>
            <?= self::numberedDot(count($in_cart_slots)) ?>
            <?= self::countdown($cart::getCartTime(TRUE), \TeamBooking\Functions\getSettings()->getSlotsInCartExpirationTime()) ?>
            <div class="<?= $is_widget ? 'mini' : 'tiny' ?> tbk-menu">
                <?php foreach ($in_cart_slots as $slot_id => $in_cart_slot) {
                    echo self::menuItem($in_cart_slot);
                }
                echo self::checkoutButton();
                ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

}