<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Admin;

/**
 * Class Slots
 *
 * @author VonStroheim
 * @since  2.4.0
 */
class Slots
{
    /**
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper" xmlns="http://www.w3.org/1999/html">

            <div class="tbk-row">
                <?= $this->getDataExportForms() ?>
                <div class="tbk-column tbk-span-12">
                    <?php
                    $panel = new Framework\PanelForList(__('Slots', 'team-booking'));
                    ob_start();
                    echo '<form method="post" class="ays-ignore">';
                    $table = new SlotsTable();
                    $table->views();
                    $table->prepare_items();
                    $table->search_box(esc_html__('Search', 'team-booking'), 'tbk-search');
                    $table->display();
                    echo '</form>';
                    $panel->addElement(Admin\Framework\ElementFrom::content(ob_get_clean()));
                    $panel->render();
                    ?>
                </div>
            </div>
            <div class="ui small modal tbk-reservation-details-modal" style="display: none"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getDataExportForms()
    {
        ob_start();
        ?>
        <form id="tb-get-slot-csv-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_bulk_csv">
            <input type="hidden" name="reservations" value="">
            <input type="hidden" name="filename" value="">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <form id="tb-get-slot-xlsx-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_bulk_xlsx">
            <input type="hidden" name="reservations" value="">
            <input type="hidden" name="filename" value="">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <?php
        return ob_get_clean();
    }
}