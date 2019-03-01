<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin\Framework\Html;
use TeamBooking\Admin\Framework\Modal;
use TeamBooking\Admin\Framework\Table;
use TeamBooking\Calendar;
use TeamBooking\Database\Reservations,
    TeamBooking\Functions,
    TeamBooking\Database\Services,
    TeamBooking\Toolkit;
use TeamBooking\Fetch\fromGoogle;
use TeamBooking\Slot;

/**
 * Class SlotsTable
 *
 * @since    2.4.0
 * @author   VonStroheim
 */
class SlotsTable extends \WP_List_Table
{
    public $total_items_all;
    public $total_items_available;
    public $total_items_booked;
    public $total_items_with_reservations;
    public $filter_by;
    public $current_page;
    public $order_by;
    public $order;
    public $items_per_page = 20;
    /** @var  \TeamBooking_ReservationData[] */
    public $reservations;
    /** @var  Slot[] */
    public $slots;

    /**
     * ReservationsTable constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => __('Slot', 'team-booking'),
            'plural'   => __('Slots', 'team-booking'),
            'ajax'     => TRUE
        ));
        #$this->process_bulk_action();
        #$this->process_single_action();
        if (isset($_GET['timezone'])) {
            try {
                $timezone = new \DateTimeZone($_GET['timezone']);
            } catch (\Exception $e) {
                $timezone = \TeamBooking\Toolkit\getTimezone();
            }
        } else {
            $timezone = \TeamBooking\Toolkit\getTimezone();
        }
        if (!isset($_GET['min_time'])) {
            $now = new \DateTime();
            $now->setTimezone($timezone);
        } else {
            try {
                $now = new \DateTime($_GET['min_time']);
            } catch (\Exception $e) {
                $now = new \DateTime();
                $now->setTimezone($timezone);
            }
        }
        $min_get_time = $now->format(DATE_ATOM);
        $calendar = new Calendar();

        if (isset($_GET['interval'])) {
            try {
                $requested_interval = new \DateInterval($_GET['interval']);
            } catch (\Exception $e) {
                $requested_interval = new \DateInterval('P1Y');
            }
        } else {
            $requested_interval = new \DateInterval('P1Y');
        }

        if (isset($_GET['past'])) {
            $max_get_time = $min_get_time;
            $requested_interval->invert = 1;
            $now->add($requested_interval);
            $min_get_time = $now->format(DATE_ATOM);
            // We should remove the cut to the min requesting time
            add_filter('tbk_parser_min_time_cut', function () {
                return FALSE;
            });
        } else {
            $now->add($requested_interval);
            $max_get_time = $now->format(DATE_ATOM);
        }

        $requested_services = isset($_GET['service']) ? array($_GET['service']) : Functions\getSettings()->getServiceIdList();

        if (Functions\isAdmin()) {
            $this->reservations = Reservations::getAll();
            $requested_coworkers = isset($_GET['coworker']) ? array($_GET['coworker']) : Functions\getCoworkersIdList();
            $this->slots = $calendar->getSlots($requested_services, $requested_coworkers, $min_get_time, $max_get_time)->getAllSlotsRawSortedByTime();
        } else {
            $this->reservations = Reservations::getByCoworker(get_current_user_id());
            $this->slots = $calendar->getSlots($requested_services, array(get_current_user_id()), $min_get_time, $max_get_time)->getAllSlotsRawSortedByTime();
        }
        if (isset($_GET['past'])) {
            $this->slots = array_reverse($this->slots);
        }
        #$parser->setRequestedServices($_GET['services']);
        $this->total_items_all = count($this->slots);
        $this->filter_by = isset($_GET['filter']) ? $_GET['filter'] : FALSE;

        foreach ($this->slots as $slot_id => $slot) {

            if (isset($_POST['s']) && !empty($_POST['s'])) {
                $this->filter_by = FALSE;
                $to_drop = TRUE;
                foreach ($slot->getCustomers() as $customer) {
                    foreach ($customer as $property) {
                        if (strpos(trim(Functions\tb_mb_strtolower($property)), trim(Functions\tb_mb_strtolower($_POST['s']))) !== FALSE) {
                            $to_drop = FALSE;
                            break 2;
                        }
                    }
                }
                if ($to_drop) unset($this->slots[ $slot_id ]);
            }

            if ($slot->isSoldout()) {
                $this->total_items_booked++;
                if ($this->filter_by === 'available') unset($this->slots[ $slot_id ]);
            } else {
                $this->total_items_available++;
                if ($this->filter_by === 'booked') unset($this->slots[ $slot_id ]);
            }
            if (count($slot->getCustomers()) > 0) $this->total_items_with_reservations++;
            if ($this->filter_by === 'with_reservations' && count($slot->getCustomers()) < 1) {
                unset($this->slots[ $slot_id ]);
            }
        }

        $this->current_page = $this->get_pagenum();
        $this->items_per_page = $this->get_items_per_page('tbk_slots_per_page');
        $this->order = isset($_GET['order']) && in_array($_GET['order'], array('asc', 'desc')) ? $_GET['order'] : 'asc';
        if ($this->order === 'desc') $this->slots = array_reverse($this->slots);
        $order_by = isset($_GET['orderby']) && in_array($_GET['orderby'], array_keys($this->get_sortable_columns())) ? $_GET['orderby'] : 'date';
        switch ($order_by) {
            case 'date':
                $this->order_by = 'created';
                break;
        }
    }

    public function no_items()
    {
        _e('No slots available.', 'team-booking');
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'        => '',
            'date'      => esc_html__('Date', 'team-booking'),
            'service'   => esc_html__('Service', 'team-booking'),
            'customers' => esc_html__('Customers', 'team-booking'),
            'status'    => esc_html__('Status', 'team-booking'),
        );
        if (Functions\isAdmin()) {
            $columns['coworker'] = esc_html__('Coworker', 'team-booking');
        }
        $columns['actions'] = esc_html__('Actions', 'team-booking');

        return $columns;
    }

    public function prepare_items()
    {
        if (isset($_POST['s']) && !empty($_POST['s'])) {
            $this->total_items_all = count($this->slots);
        }
        $which_items = 'total_items_' . (!$this->filter_by ? 'all' : $this->filter_by);
        $this->set_pagination_args(array(
            'total_items' => $this->$which_items,
            'per_page'    => $this->items_per_page
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->get_items();
    }

    /**
     * @param object $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'service':
            case 'customers':
            case 'date':
            case 'status':
            case 'coworker':
            case 'actions':
                return $item[ $column_name ];
            default:
                return print_r($item, TRUE);
        }
    }

    /**
     * @return array
     */
    public function get_views()
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('filter', $current_url);

        $current = 'all';
        if ($this->filter_by === 'available') $current = 'available';
        if ($this->filter_by === 'booked') $current = 'booked';
        if ($this->filter_by === 'with_reservations') $current = 'with_reservations';

        $status_links = array(
            'all' => '<a href="'
                . $current_url . '"'
                . ($current === 'all' ? 'class="current"' : '')
                . '>'
                . esc_html__('All', 'team-booking')
                . ' <span class="count">(' . $this->total_items_all . ')</span>'
                . '</a>'
        );
        if ($this->total_items_available > 0) {
            $filter_url = add_query_arg('filter', 'available', $current_url);
            $status_links['available'] = '<a href="'
                . $filter_url . '"'
                . ($current === 'available' ? 'class="current"' : '')
                . '>'
                . esc_html__('Available', 'team-booking')
                . ' <span class="count">(' . $this->total_items_available . ')</span>'
                . '</a>';
        }
        if ($this->total_items_booked > 0) {
            $filter_url = add_query_arg('filter', 'booked', $current_url);
            $status_links['booked'] = '<a href="'
                . $filter_url . '"'
                . ($current === 'booked' ? 'class="current"' : '')
                . '>'
                . esc_html__('Booked/Sold-out', 'team-booking')
                . ' <span class="count">(' . $this->total_items_booked . ')</span>'
                . '</a>';
        }
        if ($this->total_items_with_reservations > 0) {
            $filter_url = add_query_arg('filter', 'with_reservations', $current_url);
            $status_links['with_reservations'] = '<a href="'
                . $filter_url . '"'
                . ($current === 'with_reservations' ? 'class="current"' : '')
                . '>'
                . esc_html__('With reservations', 'team-booking')
                . ' <span class="count">(' . $this->total_items_with_reservations . ')</span>'
                . '</a>';
        }

        return $status_links;
    }

    /**
     * @param string $which
     */
    public function extra_tablenav($which = 'top')
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $service_url = remove_query_arg('service', $current_url);
        $coworker_url = remove_query_arg('coworker', $current_url);
        $interval_url = remove_query_arg(array('interval', 'past'), $current_url);
        $reset_url = remove_query_arg(array('service', 'coworker', 'interval'), $service_url);
        $filter_interval = isset($_GET['interval']) ? $_GET['interval'] : '';
        $filter_coworker = isset($_GET['coworker']) ? $_GET['coworker'] : '';
        $filter_service = isset($_GET['service']) ? $_GET['service'] : '';
        $filter_past = isset($_GET['past']) ? 1 : 0;
        if ($which === 'top') {
            echo '<div class="alignleft actions" style="overflow: visible;margin-left: -9px;">';
            echo '<select onchange="location = this.value;">';
            echo '<option value="' . $interval_url . '">' . esc_html__('A year from now', 'team-booking') . '</option>';
            $option_url = add_query_arg('interval', 'P2Y', $interval_url);
            echo '<option value="' . $option_url . '" ' . selected($filter_interval, 'P2Y') . '>' . esc_html__('Two years from now', 'team-booking') . '</option>';
            $option_url = add_query_arg('interval', 'P3Y', $interval_url);
            echo '<option value="' . $option_url . '" ' . selected($filter_interval, 'P3Y') . '>' . esc_html__('Three years from now', 'team-booking') . '</option>';
            $option_url = add_query_arg('past', '1', $interval_url);
            echo '<option value="' . $option_url . '" ' . selected($filter_past, 1) . '>' . esc_html__('Past slots (1 year)', 'team-booking') . '</option>';
            echo '</select>';
            if (Functions\isAdmin()) {
                echo '<select onchange="location = this.value;"><option value="' . $coworker_url . '">' . esc_html__('All service providers', 'team-booking') . '</option>';
                foreach (Functions\getAuthCoworkersList() as $coworker_id => $coworker) {
                    $option_url = add_query_arg('coworker', $coworker_id, $coworker_url);
                    echo '<option value="' . $option_url . '" ' . selected($filter_coworker, $coworker_id) . '>' . $coworker['name'] . '</option>';
                }
                echo '</select>';
            }
            echo '<select onchange="location = this.value;"><option value="' . $service_url . '">' . esc_html__('All services', 'team-booking') . '</option>';
            foreach (Services::get() as $service) {
                $option_url = add_query_arg('service', $service->getId(), $service_url);
                echo '<option value="' . $option_url . '" ' . selected($filter_service, $service->getId()) . '>' . $service->getName(TRUE) . '</option>';
            }
            echo '</select>';
            echo '<a class="button action"'
                . ' style="margin: 1px 8px 0 0;display:inline-block;" href="' . $reset_url . '">' . esc_html__('Reset', 'team-booking') . '</a>';
            echo '</div>';
        }
        if ($which === 'bottom') {
            // nothing yet
        }
    }

    /**
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'date' => array('date', TRUE),
        );

        return $sortable_columns;
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {
        return array();
    }

    public function process_single_action()
    {
        if (!isset($_GET['action']) || !Functions\isAdminOrCoworker()) return;

        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('action', $current_url);
        $current_url = remove_query_arg('slot', $current_url);
    }

    public function process_bulk_action()
    {
        $form_action_url = admin_url('admin-post.php');

        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');

        }

        if (!isset($_POST['slots']) || empty($_POST['slots'])) {
            return;
        }

        switch ($_POST['action']) {
            default:
                return;
                break;
        }
    }

    /**
     * @param object $item
     *
     * @return mixed
     */
    public function column_cb($item)
    {
        return sprintf(
            '<span class="tbk-slot-service-color" style="border-left-color: %s"></span>', $item['service_color']
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function get_items()
    {
        $this->slots = array_slice($this->slots, $this->items_per_page * ($this->current_page - 1), $this->items_per_page);
        $items = array();
        foreach ($this->slots as $slot_id => $slot) {
            if ($slot->isAllDay()) {
                $slot_times = Functions\dateFormatter(Functions\strtotime_tb($slot->getStartTime()), TRUE)->date
                    . Framework\Html::span(array(
                        'text'   => '<strong>' . esc_html__('All day', 'team-booking') . '</strong>',
                        'class'  => 'tbk-description',
                        'escape' => FALSE
                    ));
            } else {
                $slot_times = '<strong>' . Functions\dateFormatter(Functions\strtotime_tb($slot->getStartTime()))->date . '</strong>'
                    . Framework\Html::span(array(
                        'text'  => Functions\dateFormatter(Functions\strtotime_tb($slot->getStartTime()))->time
                            . ' - '
                            . Functions\dateFormatter(Functions\strtotime_tb($slot->getEndTime()))->time,
                        'class' => 'tbk-description'
                    ));
            }
            $customers = $slot->getCustomers();
            $items[ $slot_id ] = array(
                'ID'            => $slot_id,
                'service_color' => Services::get($slot->getServiceId())->getColor(),
                'service'       => Functions\checkServiceIdExistance($slot->getServiceId())
                    ? (Framework\Html::anchor(array(
                            'href'  => get_admin_url() . 'admin.php?page=team-booking-events&event=' . $slot->getServiceId(),
                            'text'  => $slot->getServiceName(TRUE),
                            'class' => 'tbk-to-sort'
                        )) . Framework\Html::span(array(
                            'text'  => Services::get($slot->getServiceId())->getClass(TRUE),
                            'class' => 'tbk-description'
                        )))
                    : $slot->getServiceName(TRUE)
                ,
                'customers'     => Framework\Html::span(array(
                        'text'  => !empty($customers) ? $customers[0]['email'] : '',
                        'class' => 'tbk-to-sort'
                    )) . Framework\Html::span(array(
                        'text'  => count($customers) > 1 ? sprintf(esc_html__('+ %d more'), count($customers) - 1) : '',
                        'class' => 'tbk-description'
                    ))
                ,
                'date'          => $slot_times,
                'status'        => Framework\Html::span(array(
                        'text'   => !$slot->isSoldout()
                            ? (Services::get($slot->getServiceId())->getClass() === 'event'
                            && Services::get($slot->getServiceId())->getSlotMaxTickets() - $slot->getAttendeesNumber() < 2
                                ? '<span class="tbk-pending">'
                                : '<span class="tbk-confirmed">')
                            . __('Available', 'team-booking') . '</span>'
                            : '<span class="tbk-cancelled">'
                            . (Services::get($slot->getServiceId())->getClass() === 'event' ? __('Sold-out', 'team-booking') : __('Booked', 'team-booking'))
                            . '</span>',
                        'class'  => 'tbk-to-sort',
                        'escape' => FALSE
                    )) . Framework\Html::span(array(
                        'text'  => Services::get($slot->getServiceId())->getClass() === 'event'
                            ? sprintf(__('%1$d of %2$d', 'team-booking'), Services::get($slot->getServiceId())->getSlotMaxTickets() - $slot->getAttendeesNumber(), Services::get($slot->getServiceId())->getSlotMaxTickets())
                            : '',
                        'class' => 'tbk-description'
                    ))
            );

            if (Functions\isAdmin()) {
                $row_content = Framework\Html::span(array(
                    'text'  => ucwords(Functions\getSettings()->getCoworkerData($slot->getCoworkerId())->getDisplayName()),
                    'class' => 'tbk-to-sort'
                ));
                if ($slot->getCoworkerId() == get_current_user_id()) $row_content .= ' (' . esc_html__('you', 'team-booking') . ')';
                $row_content .= Framework\Html::span(array(
                    'text'  => Functions\getSettings()->getCoworkerData($slot->getCoworkerId())->getEmail(),
                    'class' => 'tbk-description'
                ));
                $items[ $slot_id ]['coworker'] = $row_content;
            }

            $items[ $slot_id ]['actions'] = $this->getSlotActions($slot);
        }

        return $items;
    }

    /**
     * @param \TeamBooking_ReservationData $data
     * @param                              $who
     *
     * @return bool|string
     */
    protected function whoIs(\TeamBooking_ReservationData $data, $who)
    {
        if ($who === FALSE) {
            return FALSE;
        } elseif (!is_numeric($who)) {
            return 'API';
        } elseif (user_can($who, 'manage_options')) {
            return 'admin';
        } elseif ($who === $data->getCoworker()) {
            return 'coworker';
        } else {
            return 'customer';
        }
    }

    /**
     * @param Slot $slot
     *
     * @return string
     * @throws \Exception
     */
    public function getSlotActions(Slot $slot)
    {
        if (count($slot->getCustomers()) < 1) return;
        $modal_id = 'tbk-slot-details-' . Toolkit\randomNumber(10);
        ob_start();
        $button = new Framework\ActionButton('dashicons-editor-alignleft');
        $button->addClass('tbk-slots-action-details');
        $button->addData('modal', $modal_id);
        $button->setTitle(__('Details', 'team-booking'));
        $button->render();

        $table_modal = new Table();
        if (Services::get($slot->getServiceId())->getClass() === 'event') {
            $table_modal->addColumns(array(
                esc_html__('Reservation ID', 'team-booking'),
                esc_html__('Status', 'team-booking'),
                esc_html__('Customer', 'team-booking'),
                esc_html__('Name', 'team-booking'),
                esc_html__('Phone', 'team-booking'),
                esc_html__('Tickets', 'team-booking'),
                esc_html__('Timezone', 'team-booking')
            ));
        } else {
            $table_modal->addColumns(array(
                esc_html__('Reservation ID', 'team-booking'),
                esc_html__('Status', 'team-booking'),
                esc_html__('Customer', 'team-booking'),
                esc_html__('Name', 'team-booking'),
                esc_html__('Phone', 'team-booking'),
                esc_html__('Timezone', 'team-booking')
            ));
        }

        $reservation_ids = array();
        foreach ($slot->getCustomers() as $customer) {
            switch ($customer['status']) {
                case 'confirmed':
                    $reservation_status = Html::span(array(
                        'text'  => __('confirmed', 'team-booking'),
                        'class' => 'tbk-confirmed'
                    ));
                    break;
                case 'waiting_approval':
                    $reservation_status = Html::span(array(
                        'text'  => __('waiting approval', 'team-booking'),
                        'class' => 'tbk-pending'
                    ));
                    break;
                case 'pending':
                    $reservation_status = Html::span(array(
                        'text'  => __('pending', 'team-booking'),
                        'class' => 'tbk-pending'
                    ));
                    break;
                case 'cancelled':
                    $reservation_status = Html::span(array(
                        'text'  => __('cancelled', 'team-booking'),
                        'class' => 'tbk-cancelled'
                    ));
                    break;
                default:
                    $reservation_status = Html::span(array(
                        'text'  => __('confirmed', 'team-booking'),
                        'class' => 'tbk-confirmed'
                    ));
                    break;
            }
            if (Services::get($slot->getServiceId())->getClass() === 'event') {
                $table_modal->addRow(array(
                    0 => '#' . '<a href="#" class="tbk-reservations-action-details" data-reservation="' . $customer['reservation_id'] . '">' . $customer['reservation_id'] . '</a>',
                    1 => $reservation_status,
                    2 => $customer['email'],
                    3 => $customer['name'],
                    4 => $customer['phone'],
                    5 => $customer['tickets'],
                    6 => $customer['timezone']
                ));
            } else {
                $table_modal->addRow(array(
                    0 => '#' . '<a href="#" class="tbk-reservations-action-details" data-reservation="' . $customer['reservation_id'] . '">' . $customer['reservation_id'] . '</a>',
                    1 => $reservation_status,
                    2 => $customer['email'],
                    3 => $customer['name'],
                    4 => $customer['phone'],
                    5 => $customer['timezone']
                ));
            }
            $reservation_ids[] = $customer['reservation_id'];
        }

        $modal = new Modal($modal_id);
        $modal->setWide();
        $modal->closeOnly(TRUE);
        $modal->addContent($table_modal);
        $modal->setHeaderText(array(
            'main'    => $slot->getServiceName(TRUE) . ', ' . $slot->getDateString(),
            'sub'     => $slot->getTimesString() . ' (' . $slot->getTimezone() . ')',
            'escaped' => FALSE
        ));
        $modal->additionalButton(array(
            'text'  => __('Export CSV', 'team-booking'),
            'class' => 'tbk-get-slot-csv',
            'data'  => array(
                'reservations' => implode(',', $reservation_ids),
                'filename'     => sanitize_file_name($slot->getServiceName(TRUE) . '_' . $slot->getDateString() . '_' . $slot->getTimesString(FALSE)) . '.csv'
            )
        ));
        $modal->additionalButton(array(
            'text'  => __('Export XLSX', 'team-booking'),
            'class' => 'tbk-get-slot-xlsx',
            'data'  => array(
                'reservations' => implode(',', $reservation_ids),
                'filename'     => sanitize_file_name($slot->getServiceName(TRUE) . '_' . $slot->getDateString() . '_' . $slot->getTimesString(FALSE)) . '.xlsx'
            )
        ));
        $modal->render();

        return ob_get_clean();
    }
}