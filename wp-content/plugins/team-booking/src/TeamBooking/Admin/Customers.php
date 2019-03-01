<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin,
    TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit;

/**
 * Class Customers
 *
 * @author VonStroheim
 */
class Customers
{
    private $services;
    /** @var  \TeamBooking\Customer[] $customers */
    private $customers;

    public function __construct()
    {
        $this->customers = array();
        $this->services = Database\Services::get();
        foreach (Database\Reservations::sortByCustomers() as $customer_id => $data) {
            $this->customers[ $customer_id ] = new \TeamBooking\Customer($data['user'], $data['reservations']);
        }
    }

    /**
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            $row = new Framework\Row();
            $row->addElement(Framework\ElementFrom::content($this->getDataExportForms()));
            $column = Framework\Column::fullWidth();
            $column->addElement($this->getCustomersList());
            $column->appendTo($row);
            $row->render();
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * @return Framework\PanelForList
     */
    private function getCustomersList()
    {
        $panel = new Framework\PanelForList(ucfirst(__('Customers', 'team-booking')));

        $panel_button = new Framework\PanelTitleAddNewButton(__('Export CSV', 'team-booking'));
        $panel_button->addClass('tb-get-customers-csv');
        $panel->addTitleContent($panel_button);
        $panel_button = new Framework\PanelTitleAddNewButton(__('Export XLSX', 'team-booking'));
        if (class_exists('ZipArchive')) {
            $panel_button->addClass('tb-get-customers-xlsx');
        } else {
            $panel_button->addClass('inactive');
        }
        $panel->addTitleContent($panel_button);

        // List
        $table = new Framework\Table();
        $table->setId('tbk-customers-overview');
        // Preparing the table columns
        $table->addColumns(array(
            esc_html__('Status', 'team-booking'),
            esc_html__('Name', 'team-booking'),
            esc_html__('E-mail', 'team-booking'),
            esc_html__('Reservations (click for details)', 'team-booking')
        ));
        // Preparing the table rows
        foreach ($this->customers as $customer) {
            $table_modal = new Framework\Table();
            $table_modal->addColumns(array(
                esc_html__('Service', 'team-booking'),
                esc_html__('Class', 'team-booking'),
                esc_html__('Total', 'team-booking')
            ));
            $no_left = FALSE;
            foreach ($customer->getReservations() as $service_id => $reservation_number) {
                if (!isset($this->services[ $service_id ])) continue;
                $service = $this->services[ $service_id ];
                $service_label = '';
                $reset_label = '';
                $no_left = FALSE;
                if ($service->getClass() === 'unscheduled' && $service->getMaxReservationsUser()) {
                    $enumerable_reservations = $customer->getEnumerableReservations($service_id);
                    if ($service->getMaxReservationsUser() <= $enumerable_reservations
                    ) {
                        $service_label = ' <span class="ui mini horizontal orange label tbk-customer-reservation-limit">' . esc_html__('no left', 'team-booking') . '</span>';
                        $no_left = TRUE;
                    } else {
                        $service_label = ' <span class="ui mini horizontal green label tbk-customer-reservation-limit">' . ($service->getMaxReservationsUser() - $enumerable_reservations) . ' ' . esc_html__('left', 'team-booking') . '</span>';
                    }
                    $reset_label = '<a tabindex="0" class="ui mini horizontal label tbk-reset-customer-reservation-limit" data-customer="' . $customer->getID()
                        . '" data-service="' . $service_id . '" data-text="' . $service->getMaxReservationsUser() . ' ' . esc_html__('left', 'team-booking') . '">'
                        . esc_html__('reset', 'team-booking') . '</a>';
                }
                $table_modal->addRow(array(
                    0 => $service->getName(TRUE) . $service_label . $reset_label,
                    1 => $service->getClass(TRUE),
                    2 => $reservation_number
                ));
            }
            $modal_id = 'customer-reservations-' . Toolkit\randomNumber(5);
            $modal = new Framework\Modal($modal_id);
            $modal->closeOnly(TRUE);
            $modal->addContent($table_modal);
            $modal->setHeaderText(array(
                'main' => ucwords($customer->getName()),
                'sub'  => $customer->getID() ? Framework\TextLabel::blue(__('Registered', 'team-booking')) : new Framework\TextLabel(__('Guest', 'team-booking'))
            ));
            $label = new Framework\CircularTextlabel($customer->getTotalReservations());
            $label->setClass('tb-show-customer-reservations');
            $label->addData(array('modal' => $modal_id));
            if ($no_left) {
                $label->setColor('orange');
            }
            $row = array(
                0 => $customer->getID() ? Framework\TextLabel::blue(__('Registered', 'team-booking')) : new Framework\TextLabel(__('Guest', 'team-booking')),
                1 => $customer->getName(),
                2 => $customer->getEmail(),
                3 => array(
                    $modal,
                    $label
                )
            );
            $table->addRow($row);
        }
        $panel->addElement($table);

        return $panel;
    }

    /**
     * @return string
     */
    private function getDataExportForms()
    {
        ob_start();
        ?>
        <form id="tb-get-customers-csv-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_customers_csv">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <form id="tb-get-customers-xlsx-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_customers_xlsx">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <?php
        return ob_get_clean();
    }

}