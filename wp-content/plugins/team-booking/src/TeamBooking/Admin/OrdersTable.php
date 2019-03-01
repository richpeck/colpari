<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin\Framework\Html;
use TeamBooking\Admin\Framework\Modal;
use TeamBooking\Admin\Framework\Table;
use TeamBooking\Database\Reservations,
    TeamBooking\Functions,
    TeamBooking\Database\Services;
use TeamBooking\Order;

/**
 * Class OrdersTable
 *
 * @since    2.5.0
 * @author   VonStroheim
 */
class OrdersTable extends \WP_List_Table
{
    public $total_items_all;
    public $total_items_reservations;
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
            'singular' => __('Order', 'team-booking'),
            'plural'   => __('Orders', 'team-booking'),
            'ajax'     => TRUE
        ));
        $this->new_res_ids = $new_reservation_ids;
        if (isset($_POST['s']) && !empty($_POST['s'])) {
            $this->search_term = Functions\tb_mb_strtolower(trim(filter_input(INPUT_POST, 's', FILTER_SANITIZE_STRING)));
        }
        $this->process_bulk_action();
        $this->process_single_action();
        if (Functions\isAdmin()) {
            $this->total_items_all = Reservations::count('orders');
            $this->total_items_reservations = Reservations::count();
        } else {
            $this->total_items_all = Reservations::countByCoworker(get_current_user_id(), 'orders');
            $this->total_items_reservations = Reservations::countByCoworker(get_current_user_id());
        }
        $this->filter_by = isset($_GET['filter']) ? $_GET['filter'] : FALSE;
        $this->current_page = $this->get_pagenum();
        $this->items_per_page = $this->get_items_per_page('tbk_orders_per_page');
        $this->order = isset($_GET['order']) && in_array($_GET['order'], array('asc', 'desc')) ? $_GET['order'] : 'desc';
        $order_by = isset($_GET['orderby']) && array_key_exists($_GET['orderby'], $this->get_sortable_columns()) ? $_GET['orderby'] : 'date';
        switch ($order_by) {
            case 'date':
                $this->order_by = 'created';
                break;
            case 'who':
                $this->order_by = 'customer_nicename';
                break;
        }
    }

    public function no_items()
    {
        _e('No orders available.', 'team-booking');
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'order_id'       => esc_html__('Order', 'team-booking'),
            'when'           => esc_html__('Date of booking', 'team-booking'),
            'who'            => esc_html__('Customer', 'team-booking'),
            'items_no'       => esc_html__('Reservations', 'team-booking'),
            'payment_status' => esc_html__('Amounts', 'team-booking'),
            'actions'        => esc_html__('Actions', 'team-booking')
        );

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
            case 'order_id':
            case 'who':
            case 'when':
            case 'items_no':
            case 'payment_status':
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
        if (!empty($this->search_term)) {
            $current = '';
        }

        $status_links = array(
            'all' => '<a href="'
                . admin_url('/admin.php?page=team-booking&show=orders') . '" '
                . ($current === 'all' ? 'class="current"' : '')
                . '>'
                . esc_html__('All', 'team-booking')
                . ' <span class="count">(' . $this->total_items_all . ')</span>'
                . '</a>'
        );
        if ($this->total_items_reservations > 0) {
            $status_links['reservations'] = '<a href="'
                . admin_url('/admin.php?page=team-booking') . '" '
                . '>'
                . ucfirst(esc_html__('by reservations', 'team-booking'))
                . ' <span class="count">(' . $this->total_items_reservations . ')</span>'
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
            'who'  => array('who', FALSE),
            'when' => array('when', FALSE)
        );

        return $sortable_columns;
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {
        return array(
            'tbk-bulk-csv'  => esc_html__('Export CSV', 'team-booking'),
            'tbk-bulk-xlsx' => esc_html__('Export XLSX', 'team-booking')
        );
    }

    public function process_single_action()
    {
        if (!isset($_GET['action']) || !Functions\isAdminOrCoworker()) return;

        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('action', $current_url);
        $current_url = remove_query_arg('reservation', $current_url);

        switch ($_GET['action']) {
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

        if (!isset($_POST['orders']) || empty($_POST['orders'])) {
            return;
        }

        switch ($_POST['action']) {

            case 'tbk-bulk-csv':
                $form_action_url .= '?action=tbk_bulk_csv&_wpnonce=' . wp_create_nonce('team_booking_options_verify');
                foreach ($_POST['orders'] as $id) {
                    $form_action_url .= '&orders[]=' . $id;
                }
                wp_redirect($form_action_url);
                break;

            case 'tbk-bulk-xlsx':
                $form_action_url .= '?action=tbk_bulk_xlsx&_wpnonce=' . wp_create_nonce('team_booking_options_verify');
                foreach ($_POST['orders'] as $id) {
                    $form_action_url .= '&orders[]=' . $id;
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
            '<input type="checkbox" name="orders[]" value="%s" />', $item['ID']
        );
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

        $orders = array();
        foreach ($this->reservations as $reservation) {
            if (NULL !== $reservation->getOrderId()) {
                /** @var $order Order */
                if (isset($orders[ $reservation->getOrderId() ])) {
                    $order = $orders[ $reservation->getOrderId() ];
                    $order->add_item($reservation);
                } else {
                    $order = new Order();
                    $order->setId($reservation->getOrderId());
                    $order->add_item($reservation);
                    $orders[ $reservation->getOrderId() ] = $order;
                }
            }
        }

        foreach ($orders as $order) {
            /** @var $order Order */
            $date_time_of_order_value = Framework\Html::span(array(
                'text'  =>
                    Functions\dateFormatter($order->getDatetime())->date
                    . ' ' . Functions\dateFormatter($order->getDatetime())->time,
                'class' => 'tbk-description'
            ));

            $full_amount = $order->get_full_amount();
            $paid_amount = $order->get_paid_amount();
            $payment_class = ($full_amount === $paid_amount) ? 'tbk-paid' : 'tbk-pending';

            $payment_status = Framework\Html::span(array(
                'text'   => Functions\currencyCodeToSymbol($full_amount, $order->get_currency())
                    . ' ' . __('total', 'team-booking')
                    . Framework\Html::span(array(
                        'text'  => Functions\currencyCodeToSymbol($paid_amount, $order->get_currency())
                            . ' ' . __('paid', 'team-booking'),
                        'class' => 'tbk-description'
                    )),
                'class'  => $payment_class,
                'escape' => FALSE
            ));

            $items_cancelled = $order->countItemsCancelled();
            $items[ $order->getId() ] = array(
                'ID'             => $order->getId(),
                'order_id'       => '<strong>' . $order->getId() . '</strong>',
                'who'            => Framework\Html::span(array(
                    'text'   => $order->getCustomerEmail()
                        . Framework\Html::span(array(
                            'text'  => $order->get_customer_display_name(),
                            'class' => 'tbk-description'
                        )),
                    'class'  => 'tbk-to-sort',
                    'escape' => FALSE
                ))
                ,
                'items_no'       => $order->countItems()
                    . ($items_cancelled
                        ? Framework\Html::span(array(
                            'text'  => sprintf(__('%s cancelled', 'team-booking'), $items_cancelled),
                            'class' => 'tbk-description'
                        ))
                        : ''),
                'payment_status' => $payment_status,
                'when'           => $date_time_of_order_value
            );

            $items[ $order->getId() ]['actions'] = $this->getOrderActions($order);
        }

        return $items;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getOrderActions(Order $order)
    {
        $modal_id = 'tbk-slot-details-' . \TeamBooking\Toolkit\randomNumber(10);
        ob_start();
        $button = new Framework\ActionButton('dashicons-editor-alignleft');
        $button->addClass('tbk-slots-action-details');
        $button->addData('modal', $modal_id);
        $button->setTitle(__('Details', 'team-booking'));
        $button->render();

        $table_modal = new Table();

        $table_modal->addColumns(array(
            esc_html__('Reservation ID', 'team-booking'),
            esc_html__('Status', 'team-booking'),
            esc_html__('Customer', 'team-booking'),
            esc_html__('Name', 'team-booking'),
            esc_html__('Phone', 'team-booking'),
            esc_html__('Tickets', 'team-booking'),
            esc_html__('Timezone', 'team-booking')
        ));

        $reservation_ids = array();
        foreach ($order->getItems() as $reservation) {
            switch ($reservation->getStatus()) {
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

            $table_modal->addRow(array(
                0 => '#' . '<a href="#" class="tbk-reservations-action-details" data-reservation="' . $reservation->getDatabaseId() . '">' . $reservation->getDatabaseId(TRUE) . '</a>',
                1 => $reservation_status,
                2 => $reservation->getCustomerEmail(),
                3 => $reservation->getCustomerDisplayName(),
                4 => $reservation->getCustomerPhone(),
                5 => $reservation->getTickets(),
                6 => $reservation->getCustomerTimezone()
            ));
            $reservation_ids[] = $reservation->getDatabaseId();
        }

        $modal = new Modal($modal_id);
        $modal->setWide();
        $modal->closeOnly(TRUE);
        $modal->addContent($table_modal);
        $modal->setHeaderText(array(
            'main' => $order->getId(),
            'sub'  => Functions\dateFormatter($order->getDatetime())->date
                . ' ' . Functions\dateFormatter($order->getDatetime())->time
        ));
        $modal->additionalButton(array(
            'text'  => __('Export CSV', 'team-booking'),
            'class' => 'tbk-get-slot-csv',
            'data'  => array(
                'reservations' => implode(',', $reservation_ids),
                'filename'     => sanitize_file_name($order->getId() . '.csv'
                )
            )
        ));
        $modal->additionalButton(array(
            'text'  => __('Export XLSX', 'team-booking'),
            'class' => 'tbk-get-slot-xlsx',
            'data'  => array(
                'reservations' => implode(',', $reservation_ids),
                'filename'     => sanitize_file_name($order->getId() . '.xlsx'
                )
            )
        ));
        $modal->render();

        return ob_get_clean();
    }
}