<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\Reservations,
    TeamBooking\Functions,
    TeamBooking\Database\Services,
    TeamBooking\Toolkit;

/**
 * Class ReservationsTable
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class ReservationsTable extends \WP_List_Table
{
    public $total_items_all;
    public $total_items_pending;
    public $total_items_upcoming;
    public $total_items_unscheduled;
    public $total_items_waiting_approval;
    public $total_items_orders;
    public $filter_by;
    public $current_page;
    public $order_by;
    public $order;
    public $items_per_page = 20;
    /** @var  \TeamBooking_ReservationData[] */
    public $reservations;
    public $new_res_ids;
    public $search_term = '';

    /**
     * ReservationsTable constructor.
     *
     * @param array|string $new_reservation_ids
     */
    public function __construct($new_reservation_ids)
    {
        parent::__construct(array(
            'singular' => __('Reservation', 'team-booking'),
            'plural'   => __('Reservations', 'team-booking'),
            'ajax'     => TRUE
        ));
        $this->new_res_ids = $new_reservation_ids;
        $this->process_bulk_action();
        $this->process_single_action();
        if (Functions\isAdmin()) {
            $this->total_items_all = Reservations::count();
            $this->total_items_pending = Reservations::count('pending');
            $this->total_items_upcoming = Reservations::count('upcoming');
            $this->total_items_unscheduled = Reservations::count('unscheduled');
            $this->total_items_waiting_approval = Reservations::count('waiting_approval');
            $this->total_items_orders = Reservations::count('orders');
        } else {
            $this->total_items_all = Reservations::countByCoworker(get_current_user_id());
            $this->total_items_pending = Reservations::countByCoworker(get_current_user_id(), 'pending');
            $this->total_items_upcoming = Reservations::countByCoworker(get_current_user_id(), 'upcoming');
            $this->total_items_unscheduled = Reservations::countByCoworker(get_current_user_id(), 'unscheduled');
            $this->total_items_waiting_approval = Reservations::countByCoworker(get_current_user_id(), 'waiting_approval');
            $this->total_items_orders = Reservations::countByCoworker(get_current_user_id(), 'orders');
        }
        $this->filter_by = isset($_GET['filter']) ? $_GET['filter'] : FALSE;
        if (isset($_POST['s']) && !empty($_POST['s'])) {
            $this->search_term = Functions\tb_mb_strtolower(trim(filter_input(INPUT_POST, 's', FILTER_SANITIZE_STRING)));
        }
        $this->current_page = $this->get_pagenum();
        $this->items_per_page = $this->get_items_per_page('tbk_reservations_per_page');
        $this->order = isset($_GET['order']) && in_array($_GET['order'], array('asc', 'desc')) ? $_GET['order'] : 'desc';
        $order_by = isset($_GET['orderby']) && in_array($_GET['orderby'], array_keys($this->get_sortable_columns())) ? $_GET['orderby'] : 'date';
        switch ($order_by) {
            case 'when':
                $this->order_by = 'start';
                break;
            case 'date':
                $this->order_by = 'created';
                break;
            case 'service':
                $this->order_by = 'service_name';
                break;
            case 'status':
                $this->order_by = 'status';
                break;
            case 'payment_status':
                $this->order_by = 'paid';
                break;
            case 'who':
                $this->order_by = 'customer_nicename';
                break;
        }
    }

    public function no_items()
    {
        _e('No reservations available.', 'team-booking');
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'date'           => esc_html__('Booking', 'team-booking'),
            'service'        => esc_html__('Service', 'team-booking'),
            'who'            => esc_html__('Who', 'team-booking'),
            'when'           => esc_html__('Date of service', 'team-booking'),
            'status'         => esc_html__('Status', 'team-booking'),
            'payment_status' => esc_html__('Payment Status', 'team-booking')
        );
        if (Functions\isAdmin()) {
            $columns['coworker'] = esc_html__('Coworker', 'team-booking');
        }
        $columns['actions'] = esc_html__('Actions', 'team-booking');

        return $columns;
    }

    public function prepare_items()
    {
        if (!empty($this->search_term)) {
            $this->items_per_page = 100000000000000000000000000000; // why not 0? So it shows the count of items found
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
            case 'who':
            case 'when':
            case 'date':
            case 'status':
            case 'payment_status':
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
        $current = 'all';
        if ($this->filter_by === 'pending') $current = 'pending';
        if ($this->filter_by === 'upcoming') $current = 'upcoming';
        if ($this->filter_by === 'unscheduled') $current = 'unscheduled';
        if ($this->filter_by === 'waiting_approval') $current = 'waiting_approval';
        if (!empty($this->search_term)) $current = '';

        $status_links = array(
            'all' => '<a href="'
                . admin_url('/admin.php?page=team-booking') . '" '
                . ($current === 'all' ? 'class="current"' : '')
                . '>'
                . esc_html__('All', 'team-booking')
                . ' <span class="count">(' . $this->total_items_all . ')</span>'
                . '</a>'
        );
        $status_links['upcoming'] = '<a href="'
            . admin_url('/admin.php?page=team-booking&filter=upcoming') . '" '
            . ($current === 'upcoming' ? 'class="current"' : '')
            . '>'
            . esc_html__('Upcoming', 'team-booking')
            . ' <span class="count">(' . $this->total_items_upcoming . ')</span>'
            . '</a>';
        if ($this->total_items_unscheduled > 0) {
            $status_links['unscheduled'] = '<a href="'
                . admin_url('/admin.php?page=team-booking&filter=unscheduled') . '" '
                . ($current === 'unscheduled' ? 'class="current"' : '')
                . '>'
                . esc_html__('Unscheduled', 'team-booking')
                . ' <span class="count">(' . $this->total_items_unscheduled . ')</span>'
                . '</a>';
        }
        if ($this->total_items_pending > 0) {
            $status_links['pending'] = '<a href="'
                . admin_url('/admin.php?page=team-booking&filter=pending') . '" '
                . ($current === 'pending' ? 'class="current"' : '')
                . '>'
                . esc_html__('Pending payments', 'team-booking')
                . ' <span class="count">(' . $this->total_items_pending . ')</span>'
                . '</a>';
        }
        if ($this->total_items_waiting_approval > 0) {
            $status_links['waiting_approval'] = '<a href="'
                . admin_url('/admin.php?page=team-booking&filter=waiting_approval') . '" '
                . ($current === 'waiting_approval' ? 'class="current"' : '')
                . '>'
                . ucfirst(esc_html__('waiting for approval', 'team-booking'))
                . ' <span class="count">(' . $this->total_items_waiting_approval . ')</span>'
                . '</a>';
        }
        if ($this->total_items_orders > 0) {
            $status_links['orders'] = '<a href="'
                . admin_url('/admin.php?page=team-booking&show=orders') . '" '
                . '>'
                . ucfirst(esc_html__('by orders', 'team-booking'))
                . ' <span class="count">(' . $this->total_items_orders . ')</span>'
                . '</a>';
        }

        return $status_links;
    }

    /**
     * @param string $which
     */
    public function extra_tablenav($which = 'top')
    {
        if ($which === 'top') {
            echo '<div class="alignleft actions" style="overflow: visible">';
            echo '<button class="button action '
                . ($this->total_items_all > 0 ? 'tb-get-csv' : 'disabled')
                . '" style="margin: 1px 8px 0 0;">' . esc_html__('Export CSV', 'team-booking') . '</button>';
            echo '<button class="button action '
                . (class_exists('ZipArchive') && $this->total_items_all > 0 ? 'tb-get-xlsx' : 'disabled')
                . '" style="margin: 1px 8px 0 0;">' . esc_html__('Export XLSX', 'team-booking') . '</button>';
            if (Functions\isAdmin()) {
                echo '<button class="button action '
                    . ($this->total_items_all > 0 ? 'delete_all_tb_reservations' : 'disabled')
                    . '" style="margin: 1px 8px 0 0;">' . esc_html__('Delete All', 'team-booking') . '</button>';
            }
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
            'date'           => array('date', FALSE),
            'service'        => array('service', FALSE),
            'who'            => array('who', FALSE),
            'when'           => array('when', FALSE),
            'status'         => array('status', FALSE),
            'payment_status' => array('payment_status', FALSE)
        );

        return $sortable_columns;
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {
        return array(
            'tbk-bulk-delete' => esc_html__('Delete', 'team-booking'),
            'tbk-bulk-csv'    => esc_html__('Export CSV', 'team-booking'),
            'tbk-bulk-xlsx'   => esc_html__('Export XLSX', 'team-booking')
        );
    }

    public function process_single_action()
    {
        if (!isset($_GET['action']) || !Functions\isAdminOrCoworker()) return;

        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('action', $current_url);
        $current_url = remove_query_arg('reservation', $current_url);

        switch ($_GET['action']) {

            case 'set_paid':
                $reservation = Reservations::getById($_GET['reservation']);
                $reservation->setPaid(TRUE);
                Reservations::update($reservation);
                wp_redirect($current_url);
                break;

            case 'set_unpaid':
                $reservation = Reservations::getById($_GET['reservation']);
                $reservation->setPaid(FALSE);
                Reservations::update($reservation);
                wp_redirect($current_url);
                break;

            case 'set_done':
                $reservation = Reservations::getById($_GET['reservation']);
                $reservation->setStatusDone();
                Reservations::update($reservation);
                wp_redirect($current_url);
                break;

            case 'set_todo':
                $reservation = Reservations::getById($_GET['reservation']);
                $reservation->setStatusConfirmed();
                Reservations::update($reservation);
                wp_redirect($current_url);
                break;
            case 'set_confirmed':
                $reservation = Reservations::getById($_GET['reservation']);
                $reservation->setStatusConfirmed();
                Reservations::update($reservation);
                wp_redirect($current_url);
                break;
        }
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

        if (!isset($_POST['reservations']) || empty($_POST['reservations'])) {
            return;
        }

        switch ($_POST['action']) {

            case 'tbk-bulk-delete':
                if (Functions\isAdmin()) {
                    $reservations = Reservations::getByIds(array_values($_POST['reservations']));
                    foreach ($reservations as $reservation) {
                        $reservation->removeFiles();
                    }
                    Reservations::delete(array_values($_POST['reservations']));
                }
                break;

            case 'tbk-bulk-csv':
                $form_action_url .= '?action=tbk_bulk_csv&_wpnonce=' . wp_create_nonce('team_booking_options_verify');
                foreach ($_POST['reservations'] as $id) {
                    $form_action_url .= '&reservations[]=' . $id;
                }
                wp_redirect($form_action_url);
                break;

            case 'tbk-bulk-xlsx':
                $form_action_url .= '?action=tbk_bulk_xlsx&_wpnonce=' . wp_create_nonce('team_booking_options_verify');
                foreach ($_POST['reservations'] as $id) {
                    $form_action_url .= '&reservations[]=' . $id;
                }
                wp_redirect($form_action_url);
                break;

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
            '<input type="checkbox" name="reservations[]" value="%s" />', $item['ID']
        );
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    public function column_payment_status($item)
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('action', $current_url);
        $current_url = remove_query_arg('reservation', $current_url);
        $actions = array();
        $current_reservation = $this->reservations[ $item['ID'] ];
        if ($current_reservation->getPrice() > 0
            && (!Functions\isReservationTimedOut($current_reservation)
                || !$current_reservation->isPending())
        ) {
            if ($current_reservation->isPaid()) {
                $actions['set_unpaid'] = sprintf('<a href="%s&action=%s&reservation=%s">'
                    . esc_html__('Mark as unpaid', 'team-booking')
                    . '</a>', $current_url, 'set_unpaid', $item['ID']);
            } else {
                if (!$current_reservation->isPending()) {
                    $actions['set_paid'] = sprintf('<a href="%s&action=%s&reservation=%s">'
                        . esc_html__('Mark as paid', 'team-booking')
                        . '</a>', $current_url, 'set_paid', $item['ID']);
                }
            }
        }

        return sprintf('%1$s %2$s', $item['payment_status'], $this->row_actions($actions));
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    public function column_status($item)
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('action', $current_url);
        $current_url = remove_query_arg('reservation', $current_url);
        $actions = array();
        $current_reservation = $this->reservations[ $item['ID'] ];
        if ($current_reservation->getServiceClass() === 'unscheduled') {
            if ($current_reservation->isDone()) {
                $actions['set_todo'] = sprintf('<a href="%s&action=%s&reservation=%s">'
                    . esc_html__('Mark as todo', 'team-booking')
                    . '</a>', $current_url, 'set_todo', $item['ID']);
            } else {
                $actions['set_done'] = sprintf('<a href="%s&action=%s&reservation=%s">'
                    . esc_html__('Mark as done', 'team-booking')
                    . '</a>', $current_url, 'set_done', $item['ID']);
            }
        } elseif (Functions\isReservationPastInTime($current_reservation)
            && !$current_reservation->isConfirmed()
            && !$current_reservation->isCancelled()
        ) {
            $actions['set_confirmed'] = sprintf('<a href="%s&action=%s&reservation=%s">'
                . esc_html__('Mark as confirmed', 'team-booking')
                . '</a>', $current_url, 'set_confirmed', $item['ID']);
        }

        return sprintf('%1$s %2$s', $item['status'], $this->row_actions($actions));
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function get_items()
    {
        if (!empty($this->search_term)) {
            if (Functions\isAdmin()) {
                $this->reservations = Reservations::getBySearch($this->search_term, 0, $this->current_page, $this->order_by, $this->order);
            } else {
                $this->reservations = Reservations::getByCoworkerSearch(get_current_user_id(), $this->search_term, 0, $this->current_page, $this->order_by, $this->order);
            }
            $this->set_pagination_args(array(
                'total_items' => count($this->reservations),
                'per_page'    => $this->items_per_page
            ));
        } else {
            if (Functions\isAdmin()) {
                $this->reservations = Reservations::getAll($this->filter_by, $this->items_per_page, $this->current_page, $this->order_by, $this->order);
            } else {
                $this->reservations = Reservations::getByCoworker(get_current_user_id(), $this->filter_by, $this->items_per_page, $this->current_page, $this->order_by, $this->order);
            }
        }
        $items = array();

        foreach ($this->reservations as $reservation) {
            $date_time_of_reservation_value = Framework\Html::span(array(
                'text'  =>
                    Functions\dateFormatter($reservation->getCreationInstant())->date
                    . ' ' . Functions\dateFormatter($reservation->getCreationInstant())->time,
                'class' => 'tbk-description'
            ));
            if (NULL !== $reservation->getStart()) {
                if ($reservation->isAllDay()) {
                    $when_value = Functions\dateFormatter($reservation->getStart(), TRUE)->date
                        . Framework\Html::span(array(
                            'text'  => __('All day', 'team-booking'),
                            'class' => 'tbk-description'
                        ));
                } else {
                    $when_value = Functions\dateFormatter($reservation->getStart())->date
                        . Framework\Html::span(array(
                            'text'  => Functions\dateFormatter($reservation->getStart())->time
                                . ' - '
                                . Functions\dateFormatter($reservation->getEnd())->time,
                            'class' => 'tbk-description'
                        ));
                }
            } else {
                $when_value = esc_html__('Unscheduled', 'team-booking');
            }

            $items[ $reservation->getDatabaseId() ] = array(
                'ID'             => $reservation->getDatabaseId(),
                'service'        => Functions\checkServiceIdExistance($reservation->getServiceId())
                    ? (Framework\Html::anchor(array(
                            'href'  => get_admin_url() . 'admin.php?page=team-booking-events&event=' . $reservation->getServiceId(),
                            'text'  => $reservation->getServiceName(TRUE),
                            'class' => 'tbk-to-sort'
                        )) . Framework\Html::span(array(
                            'text'  => Services::get($reservation->getServiceId())->getClass(TRUE),
                            'class' => 'tbk-description'
                        )))
                    : ($reservation->getServiceName(TRUE) . Framework\Html::span(array(
                            'text'  => '(' . ucfirst($reservation->getServiceClass()) . ')',
                            'class' => 'tbk-description'
                        )))
                ,
                'who'            => Framework\Html::span(array(
                        'text'  => $reservation->getCustomerDisplayName(),
                        'class' => 'tbk-to-sort'
                    )) . Framework\Html::span(array(
                        'text'  => $reservation->getCustomerEmail(),
                        'class' => 'tbk-description'
                    ))
                ,
                'when'           => $when_value,
                'date'           => '<strong>#' . $reservation->getDatabaseId(TRUE)
                    . (in_array($reservation->getDatabaseId(), $this->new_res_ids) ? ' <span class="tbk-new-reservation">' . __('new', 'team-booking') . '</span>' : '')
                    . '</strong> ' . $date_time_of_reservation_value,
                'status'         => '',
                'payment_status' => ''
            );
            if (Functions\checkServiceIdExistance($reservation->getServiceId())) {
                if (Functions\isReservationTimedOut($reservation)
                    && $reservation->isPending()
                ) {
                    $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                        'text'  => __('expired', 'team-booking'),
                        'class' => 'tbk-table-status-expired'
                    ));
                } else {
                    if ($reservation->isConfirmed() && $reservation->getServiceClass() !== 'unscheduled') {
                        $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                            'text'  => __('confirmed', 'team-booking'),
                            'class' => 'tbk-confirmed'
                        ));
                        switch ($this->whoIs($reservation, $reservation->getConfirmationWho())) {
                            case 'admin':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('by Admin', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            case 'coworker':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('by Coworker', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            case 'API':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('via API', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            default:
                                break;
                        }
                    }
                    if ($reservation->isConfirmed() && $reservation->getServiceClass() === 'unscheduled')
                        $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                            'text'  => __('todo', 'team-booking'),
                            'class' => 'tbk-todo'
                        ));
                    if ($reservation->isDone())
                        $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                            'text'  => __('done', 'team-booking'),
                            'class' => 'tbk-confirmed'
                        ));
                    if ($reservation->isPending())
                        $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                            'text'  => __('on hold', 'team-booking'),
                            'class' => 'tbk-pending'
                        ));
                    if ($reservation->isCancelled()) {
                        $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                            'text'  => __('cancelled', 'team-booking'),
                            'class' => 'tbk-cancelled'
                        ));
                        switch ($this->whoIs($reservation, $reservation->getCancellationWho())) {
                            case 'admin':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('by Admin', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            case 'coworker':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('by Coworker', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            case 'customer':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('by Customer', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            case 'API':
                                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                                    'text'  => __('via API', 'team-booking'),
                                    'class' => 'tbk-description'
                                ));
                                break;
                            default:
                                break;
                        }
                    }
                    if ($reservation->isWaitingApproval())
                        $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                            'text'  => __('waiting for approval', 'team-booking'),
                            'class' => 'tbk-pending'
                        ));
                }
            } else {
                $items[ $reservation->getDatabaseId() ]['status'] .= Framework\Html::span(array(
                    'text'  => __('service removed', 'team-booking'),
                    'class' => 'tbk-service-removed'
                ));
            }

            if ($reservation->isPending() && !$reservation->isPaid()) {
                if (!Functions\isReservationTimedOut($reservation)) {
                    $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                        'text'  => __('pending', 'team-booking'),
                        'class' => 'tbk-pending tbk-to-sort'
                    ));
                } else {
                    $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                        'text'  => __('expired', 'team-booking'),
                        'class' => 'tbk-table-status-expired tbk-to-sort'
                    ));
                }
                $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                    'text'  => Functions\currencyCodeToSymbol($reservation->getPriceIncremented() * $reservation->getTickets(), $reservation->getCurrencyCode()),
                    'class' => 'tbk-status-price'
                ));
            } else {
                if ($reservation->isPaid()) {
                    $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                        'text'  => __('paid', 'team-booking'),
                        'class' => 'tbk-paid tbk-to-sort'
                    ));
                    if ($reservation->getPaymentGateway()) {
                        $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                            'text'  => ' ' . Functions\getSettings()->getPaymentGatewaySettingObject($reservation->getPaymentGateway())->getLabel(),
                            'class' => 'tbk-paid-via'
                        ));
                    }
                    $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                        'text'  => Functions\currencyCodeToSymbol($reservation->getPriceIncremented() * $reservation->getTickets(), $reservation->getCurrencyCode()),
                        'class' => 'tbk-status-price'
                    ));
                } elseif ($reservation->getPrice() > 0) {
                    $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                        'text'  => __('not paid', 'team-booking'),
                        'class' => 'tbk-not-paid tbk-to-sort'
                    ));
                    $items[ $reservation->getDatabaseId() ]['payment_status'] .= Framework\Html::span(array(
                        'text'  => Functions\currencyCodeToSymbol($reservation->getPriceIncremented() * $reservation->getTickets(), $reservation->getCurrencyCode()),
                        'class' => 'tbk-status-price'
                    ));
                } else {
                    $items[ $reservation->getDatabaseId() ]['payment_status'] = NULL;
                }
            }

            if (Functions\isAdmin()) {
                $row_content = Framework\Html::span(array(
                    'text'  => ucwords(Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getDisplayName()),
                    'class' => 'tbk-to-sort'
                ));
                if ($reservation->getCoworker() == get_current_user_id()) $row_content .= ' (' . esc_html__('you', 'team-booking') . ')';
                $row_content .= Framework\Html::span(array(
                    'text'  => Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getEmail(),
                    'class' => 'tbk-description'
                ));
                $items[ $reservation->getDatabaseId() ]['coworker'] = $row_content;
            }

            $items[ $reservation->getDatabaseId() ]['actions'] = $this->getReservationActions($reservation);
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
        }
        if (!is_numeric($who)) {
            return 'API';
        }
        if (user_can($who, 'manage_options')) {
            return 'admin';
        }
        if ((int)$who === (int)$data->getCoworker()) {
            return 'coworker';
        }

        return 'customer';
    }

    /**
     * @param \TeamBooking_ReservationData $reservation
     *
     * @return string
     * @throws \Exception
     */
    public function getReservationActions(\TeamBooking_ReservationData $reservation)
    {
        $pending = FALSE;
        if ($this->filter_by === 'pending' || !Functions\checkServiceIdExistance($reservation->getServiceId())) {
            /**
             * The service is not present anymore
             * so we'll use the pending reservations actions set
             */
            $pending = TRUE;
        }
        ob_start();

        $button = new Framework\ActionButton('dashicons-editor-alignleft');
        $button->addClass('tbk-reservations-action-details');
        $button->addData('reservation', $reservation->getDatabaseId());
        $button->setTitle(__('Details', 'team-booking'));
        $button->render();

        if (Functions\isAdmin()) {
            $button = new Framework\ActionButton('dashicons-trash');
            $button->addClass('tbk-reservations-action-delete');
            $button->addClass('team-booking-delete-reservation');
            $button->setTitle(__('Delete', 'team-booking'));
            $button->addData('reservationid', $reservation->getDatabaseId());
            $button->render();
        }

        if (($pending || $reservation->isPending()) && Functions\isAdmin()) {
            if (!Functions\isReservationPastInTime($reservation)) {
                // Pending reservation actions
                if (Functions\checkServiceIdExistance($reservation->getServiceId())) {
                    $button = new Framework\ActionButton('dashicons-yes');
                    $button->addClass('tbk-reservations-action-approve');
                    $button->addClass('team-booking-confirm-pending-reservation');
                    $button->setTitle(__('Confirm', 'team-booking'));
                    $button->addData('reservationid', $reservation->getDatabaseId());
                    $button->render();
                }
                // Confirm pending Modal
                $modal = new Framework\Modal('tb-reservation-confirm-pending-modal-' . $reservation->getDatabaseId());
                $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'secondary' => __('Yes and set as paid', 'team-booking'), 'deny' => __('No', 'team-booking')));
                $modal->setHeaderText(array('main' => __('Are you sure you want to confirm this pending reservation?', 'team-booking')));
                $modal->addContent(esc_html__('Accordingly to the service settings, the confirmation and notification e-mail messages will be sent.', 'team-booking'));
                $modal->addContent(Framework\Html::paragraph(array(
                    'text'   => esc_html__('The pending reservation cannot be confirmed due the following error:', 'team-booking') . Framework\Html::span(),
                    'class'  => 'error',
                    'escape' => FALSE
                )));
                $modal->render();
            }
        } else {
            // Normal reservation actions
            if ($reservation->isWaitingApproval()) {
                if ((Functions\isAdmin()
                        && !Functions\isReservationPastInTime($reservation))
                    || (Services::get($reservation->getServiceId())->getSettingsFor('approval_rule') === 'coworker'
                        && !Functions\isReservationPastInTime($reservation)
                        && get_current_user_id() == $reservation->getCoworker()
                    )
                ) {
                    $button = new Framework\ActionButton('dashicons-thumbs-up');
                    $button->addClass('tbk-reservations-action-approve');
                    $button->addClass('team-booking-approve-reservation');
                    $button->setTitle(__('Approve', 'team-booking'));
                    $button->addData('reservationid', $reservation->getDatabaseId());
                    $button->render();

                    // Approval modal
                    $modal = new Framework\Modal('tb-reservation-approve-modal-' . $reservation->getDatabaseId());
                    $modal->setHeaderText(array('main' => __('Are you sure you want to confirm this reservation?', 'team-booking')));
                    $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
                    $modal->addContent(esc_html__('Accordingly to the service settings, the confirmation and notification e-mail messages will be sent.', 'team-booking'));
                    $modal->addContent(Framework\Html::paragraph(array(
                        'text'   => esc_html__('The reservation cannot be approved due the following error:', 'team-booking') . Framework\Html::span(),
                        'class'  => 'error',
                        'escape' => FALSE
                    )));
                    $modal->render();
                }
            }

            try {
                if (Functions\isAdmin()
                    || ($reservation->isConfirmed()
                        && get_current_user_id() == $reservation->getCoworker())
                    || (Services::get($reservation->getServiceId())->getSettingsFor('approval_rule') === 'coworker'
                        && $reservation->isWaitingApproval()
                        && get_current_user_id() == $reservation->getCoworker())
                ) {
                    if (!$reservation->isCancelled()
                        && !Functions\isReservationPastInTime($reservation)
                    ) {
                        if ($reservation->getServiceClass() !== 'unscheduled'
                            || ($reservation->getServiceClass() === 'unscheduled'
                                && $reservation->isWaitingApproval())
                        ) {
                            $button = new Framework\ActionButton($reservation->isWaitingApproval() ? 'dashicons-thumbs-down' : 'dashicons-no-alt');
                            $button->addClass('tbk-reservations-action-cancel');
                            $button->addClass('team-booking-cancel-reservation');
                            $button->setTitle($reservation->isWaitingApproval() ? __('Decline', 'team-booking') : __('Cancel', 'team-booking'));
                            $button->addData('reservationid', $reservation->getDatabaseId());
                            $button->addData('previouslyconfirmed', $reservation->isWaitingApproval() ? 'no' : 'yes');
                            $button->render();
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return ob_get_clean();
    }
}