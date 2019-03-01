<?php

namespace TeamBooking\Frontend\Components;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Slot
 *
 * @author VonStroheim
 * @since  2.5.0
 */
class Slot
{

    /**
     * @param string $bg_color
     *
     * @return string
     */
    public static function actionButtons($bg_color)
    {
        ob_start(); ?>
        <div class="tbk-slot-actions">
            <button class="tbk-slot-button tbk-add" style="border-color: <?= $bg_color ?>;">
                <div class="tbk-bgnd" style="background-color:<?= $bg_color ?>;"></div>
                <span class="tbk-select"><?= esc_html__('Select', 'team-booking') ?></span>
                <span class="tbk-selected"><?= esc_html__('Selected', 'team-booking') ?></span>
                <span class="tbk-remove"><?= esc_html__('Remove', 'team-booking') ?></span>
            </button>
            <button class="tbk-slot-button tbk-book tb-book" style="border-color: <?= $bg_color ?>;">
                <div class="tbk-bgnd" style="background-color:<?= $bg_color ?>;"></div>
                <?= esc_html__('Book now', 'team-booking') ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param string $bg_color
     *
     * @return string
     */
    public static function actionButtonsReadOnly($bg_color)
    {
        ob_start(); ?>
        <div class="tbk-slot-actions">
            <button class="tbk-slot-button tbk-book tb-book" style="border-color: <?= $bg_color ?>;">
                <div class="tbk-bgnd" style="background-color:<?= $bg_color ?>;"></div>
                <?= esc_html__('View', 'team-booking') ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param string $bg_color
     *
     * @return string
     */
    public static function actionButtonsDimmer($bg_color)
    {
        ob_start(); ?>
        <div class="tbk-slot-actions">
            <button class="tbk-slot-button tb-book-advice" style="border-color: <?= $bg_color ?>;">
                <div class="tbk-bgnd" style="background-color:<?= $bg_color ?>;"></div>
                <span class="tbk-select"><?= esc_html__('Select', 'team-booking') ?></span>
            </button>
            <button class="tbk-slot-button tb-book-advice" style="border-color: <?= $bg_color ?>;">
                <div class="tbk-bgnd" style="background-color:<?= $bg_color ?>;"></div>
                <?= esc_html__('Book now', 'team-booking') ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

}