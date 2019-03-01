<?php

namespace TeamBooking\Files;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Order;

/**
 * Generate ICS file
 *
 * @param                                $filename - file name
 * @param \TeamBooking_ReservationData[] $reservations
 */
function generateICSFile($filename, $reservations)
{
    // 1. Set the correct headers for this file
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    // 2. Echo out the ics file's contents
    echo "BEGIN:VCALENDAR\r\n";
    foreach ($reservations as $reservation) {
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
        echo "CALSCALE:GREGORIAN\r\n";
        echo "BEGIN:VEVENT\r\n";
        echo 'DTEND:' . date('Ymd\THis\Z', $reservation->getEnd()) . "\r\n";
        echo 'UID:' . uniqid('', TRUE) . "\r\n";
        echo 'DTSTAMP:' . date('Ymd\THis\Z', current_time('timestamp')) . "\r\n";
        echo 'LOCATION:' . preg_replace('/([\,;])/', '\\\$1', $reservation->getServiceLocation()) . "\r\n";
        echo 'DESCRIPTION:' . preg_replace('/([\,;])/', '\\\$1', Database\Services::get($reservation->getServiceId())->getDescription(TRUE)) . "\r\n";
        echo 'URL;VALUE=URI:' . preg_replace('/([\,;])/', '\\\$1', '') . "\r\n";
        echo 'SUMMARY:' . preg_replace('/([\,;])/', '\\\$1', $reservation->getServiceName(TRUE)) . "\r\n";
        echo 'DTSTART:' . Functions\date_i18n_tb('Ymd\THis\Z', $reservation->getStart()) . "\r\n";
        echo "END:VEVENT\r\n";
    }
    echo "END:VCALENDAR\r\n";
}

function generateReservationPDF(\TeamBooking_ReservationData $reservation)
{
    ob_start();
    include_once TEAMBOOKING_PATH . '/includes/tb_pdf_reservation_model.php';

    return ob_get_clean();
}

/**
 * Generate an XLSX file with customers
 *
 * @param string $filename
 *
 * @return mixed
 * @throws \Exception
 */
function generateXLSXClients($filename = 'customers.xlsx')
{
    $services = Database\Services::get();
    $customers = array();

    foreach (Database\Reservations::sortByCustomers() as $customer_id => $data) {
        $customers[ $customer_id ] = new \TeamBooking\Customer($data['user'], $data['reservations']);
    }

    ob_start();
    header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $writer = new \XLSXWriter();
    $writer->setAuthor(get_bloginfo('name'));
    $rows = array();

    // Preparing the header array
    $headers = array(
        __('Status', 'team-booking')             => 'string',
        __('Name', 'team-booking')               => 'string',
        __('Email', 'team-booking')              => 'string',
        __('Total Reservations', 'team-booking') => 'string',
    );

    foreach ($services as $service) {
        $headers[ $service->getName(TRUE) ] = 'string';
    }

    foreach ($customers as $customer) {
        /** @var $customer \TeamBooking\Customer */
        $row = array(
            $customer->getID() ? __('Registered', 'team-booking') : __('Guest', 'team-booking'),
            $customer->getName(),
            $customer->getEmail(),
            $customer->getTotalReservations(),
        );

        foreach ($services as $service) {
            $row[] = $customer->getReservations($service->getId());
        }

        $rows[] = $row;
    }
    $writer->writeSheet($rows, '', $headers);
    $writer->writeToStdOut();

    return ob_get_clean();
}

/**
 * Generate an XLSX file with given reservation records
 *
 * @param null|\TeamBooking_ReservationData[] $reservations
 * @param string                              $filename
 *
 * @return mixed
 */
function generateXLSXFile($reservations = NULL, $filename = 'reservations.xlsx')
{
    if (NULL === $reservations) {
        $reservations = Database\Reservations::getAll();
    }
    ob_start();
    header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $writer = new \XLSXWriter();
    $writer->setAuthor(get_bloginfo('name'));
    $headers = array();
    $rows = array();

    foreach ($reservations as $id => $reservation) {
        if (NULL !== $reservation->getStart()) {
            $start = Functions\dateFormatter($reservation->getStart(), $reservation->isAllDay());
            if ($reservation->isAllDay()) {
                $when_value = $start->date . ' ' . esc_html__('All day', 'team-booking');
            } else {
                $when_value = $start->date . ' ' . $start->time;
            }
        } else {
            $when_value = esc_html__('Unscheduled', 'team-booking');
        }
        $creation = Functions\dateFormatter($reservation->getCreationInstant());
        $date_time_of_reservation_value = $creation->date . ' ' . $creation->time;
        // This skips old logs before v.1.2 if present
        if (!$reservation instanceof \TeamBooking_ReservationData) {
            continue;
        }

        // If not admin, keep only logs relative to current coworker
        $coworker_id = get_current_user_id();
        if (!Functions\isAdmin() && $reservation->getCoworker() != $coworker_id) {
            continue;
        }

        $headers[ $reservation->getServiceId() ] = array(
            __('ID', 'team-booking')                  => 'string',
            __('Service', 'team-booking')             => 'string',
            __('Order', 'team-booking')               => 'string',
            __('When', 'team-booking')                => 'string', //YYYY-MM-DD HH:MM:SS (Y-m-d h:i:s)
            __('Date of reservation', 'team-booking') => 'string',
            __('Reservation Status', 'team-booking')  => 'string',
            __('Payment Status', 'team-booking')      => 'string',
            __('Price', 'team-booking')               => 'money',
            __('Currency', 'team-booking')            => 'string',
        );
        if ($reservation->getServiceClass() === 'event') {
            $headers[ $reservation->getServiceId() ][ __('Tickets', 'team-booking') ] = 'string'; //4th column
            $headers[ $reservation->getServiceId() ][ __('Total price paid', 'team-booking') ] = 'money'; //5th column
        }
        if (Functions\isAdmin()) {
            $headers[ $reservation->getServiceId() ][ __('Coworker', 'team-booking') ] = 'string'; //6th column
        }
        $headers[ $reservation->getServiceId() ][ __('Discounts used', 'team-booking') ] = 'string';
        $headers[ $reservation->getServiceId() ][ __('WordPress User', 'team-booking') ] = 'string';

        foreach ($reservation->getFieldsArray() as $key => $value) {
            $headers[ $reservation->getServiceId() ][ ucwords(str_replace('_', ' ', $key)) ] = 'string';
        }

        // Prepare the payment status
        if ($reservation->isPaid()) {
            $payment_status = __('paid', 'team-booking');
        } else {
            $payment_status = __('not paid', 'team-booking');
        }

        if ($reservation->getStatus() === 'confirmed' && $reservation->getServiceClass() === 'unscheduled') {
            $reservation_status = __('todo', 'team-booking');
        } else {
            $reservation_status = $reservation->getStatus();
        }

        $row = array(
            $reservation->getDatabaseId(),
            $reservation->getServiceName(TRUE),
            $reservation->getOrderId(),
            $when_value,
            $date_time_of_reservation_value,
            $reservation_status,
            $payment_status,
            $reservation->getPrice(),
            $reservation->getCurrencyCode(),
        );
        if ($reservation->getServiceClass() === 'event') {
            $row[] = $reservation->getTickets(); //4th column
            $row[] = $reservation->getPriceIncremented() * $reservation->getTickets(); //5th column
        }
        if (Functions\isAdmin()) {
            $row[] = Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getEmail(); //6th column
        }

        $discount_names = array();
        foreach ($reservation->getDiscount() as $discount) {
            $discount_names[] = $discount['name'] . (isset($discount['coupon']) ? (' (' . $discount['coupon'] . ')') : '');
        }
        $row[] = implode(',', $discount_names);

        if ($reservation->getCustomerUserId()) {
            $user_data = get_userdata($reservation->getCustomerUserId());
            $row[] = $user_data ? $user_data->user_nicename : __('User removed', 'team-booking');
        } else {
            $row[] = '';
        }

        foreach ($reservation->getFieldsArray() as $key => $value) {
            $row[] = Toolkit\unfilterInput($value);
        }
        $rows[ $reservation->getServiceId() ][] = $row;
    }
    foreach ($headers as $service => $header) {
        $title = $rows[ $service ][0][1];
        $writer->writeSheet($rows[ $service ], $title, $header);
    }
    $writer->writeToStdOut();

    return ob_get_clean();
}

/**
 * @param array|null $orders
 * @param string     $filename
 *
 * @return string
 */
function generateXLSXOrdersFile($orders = NULL, $filename = 'orders.xlsx')
{
    if (NULL === $orders) {
        $reservations = Database\Reservations::getAll();
    } else {
        $reservations = Database\Reservations::getByOrderIds($orders);
    }

    $sheet_title = __('Orders', 'team-booking');
    ob_start();
    header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $writer = new \XLSXWriter();
    $writer->setAuthor(get_bloginfo('name'));
    $headers = array();
    $rows = array();

    $orders = array();
    foreach ($reservations as $reservation) {
        // If not admin, keep reservations relative to the current coworker only
        $coworker_id = get_current_user_id();
        if (!Functions\isAdmin() && $reservation->getCoworker() != $coworker_id) {
            continue;
        }
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

    foreach ($orders as $id => $order) {
        $headers[ $sheet_title ] = array(
            __('Order ID', 'team-booking')           => 'string',
            __('Date of booking', 'team-booking')    => 'string',//YYYY-MM-DD HH:MM:SS (Y-m-d h:i:s)
            __('Customer name', 'team-booking')      => 'string',
            __('Customer e-mail', 'team-booking')    => 'string',
            __('Total reservations', 'team-booking') => 'integer',
            __('Reservations ids', 'team-booking')   => 'string',
            __('Full amount')                        => 'money',
            __('Paid amount')                        => 'money',
        );

        $row = array(
            $order->getId(),
            Functions\dateFormatter($order->getDatetime())->date . ' ' . Functions\dateFormatter($order->getDatetime())->time,
            $order->get_customer_display_name(),
            $order->getCustomerEmail(),
            $order->countItems(),
            implode(', ', $order->get_reservation_ids()),
            $order->get_full_amount(),
            $order->get_paid_amount(),
        );

        $rows[ $sheet_title ][] = $row;
    }
    foreach ($headers as $sheet_title => $header) {
        $writer->writeSheet($rows[ $sheet_title ], $sheet_title, $header);
    }
    $writer->writeToStdOut();

    return ob_get_clean();
}

/**
 * @param string $filename
 *
 * @return mixed
 * @throws \Exception
 */
function generateCSVClients($filename = 'customers.csv')
{
    $services = Database\Services::get();
    $customers = array();

    foreach (Database\Reservations::sortByCustomers() as $customer_id => $data) {
        $this->customers[ $customer_id ] = new \TeamBooking\Customer($data['user'], $data['reservations']);
    }

    ob_start();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');

    // Preparing the header array
    $header_array = array(
        __('Status', 'team-booking'),
        __('Name', 'team-booking'),
        __('Email', 'team-booking'),
        __('Total Reservations', 'team-booking'),
    );

    foreach ($services as $service) {
        $header_array[] = $service->getName(TRUE);
    }

    // Output header
    fputcsv($output, $header_array);

    foreach ($customers as $customer) {
        /** @var $customer \TeamBooking\Customer */
        $row = array(
            $customer->getID() ? __('Registered', 'team-booking') : __('Guest', 'team-booking'),
            $customer->getName(),
            $customer->getEmail(),
            $customer->getTotalReservations(),
        );

        foreach ($services as $service) {
            $row[] = $customer->getReservations($service->getId());
        }

        fputcsv($output, $row);
    }

    return ob_get_clean();
}

/**
 * Generate a CSV file with given reservation records
 *
 * @param null|\TeamBooking_ReservationData[] $reservations
 * @param string                              $filename
 *
 * @return mixed
 */
function generateCSVFile($reservations = NULL, $filename = 'reservations.csv')
{
    if (NULL === $reservations) {
        $reservations = Database\Reservations::getAll();
    }
    ob_start();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');

    // Preparing the header array
    $header_array = array(
        __('ID', 'team-booking'),
        __('Service', 'team-booking'),
        __('Order', 'team-.booking'),
        __('When', 'team-booking'),
        __('Date of reservation', 'team-booking'),
        __('Reservation Status', 'team-booking'),
        __('Payment Status', 'team-booking'),
        __('Price', 'team-booking'),
        __('Tickets', 'team-booking'),
        __('Total price paid', 'team-booking'),
    );
    if (Functions\isAdmin()) {
        $header_array[] = __('Coworker', 'team-booking'); //4th column
    }
    $header_array[] = __('Discounts used', 'team-booking');
    $header_array[] = __('WordPress User', 'team-booking');
    $header_array[] = __('Details', 'team-booking');
    // Output header
    fputcsv($output, $header_array);
    foreach ($reservations as $id => $reservation) {
        if (NULL !== $reservation->getStart()) {
            $start = Functions\dateFormatter($reservation->getStart(), $reservation->isAllDay());
            if ($reservation->isAllDay()) {
                $when_value = $start->date . ' ' . esc_html__('All day', 'team-booking');
            } else {
                $when_value = $start->date . ' ' . $start->time;
            }
        } else {
            $when_value = esc_html__('Unscheduled', 'team-booking');
        }
        $creation = Functions\dateFormatter($reservation->getCreationInstant());
        $date_time_of_reservation_value = $creation->date . ' ' . $creation->time;
        // This skips old logs before v.1.2 if present
        if (!$reservation instanceof \TeamBooking_ReservationData) {
            continue;
        }
        // If not admin, keep only logs relative to current coworker
        $coworker_id = get_current_user_id();
        if (!Functions\isAdmin() && $reservation->getCoworker() != $coworker_id) {
            continue;
        }

        // Prepare the payment status
        if ($reservation->isPaid()) {
            $payment_status = __('paid', 'team-booking');
        } else {
            $payment_status = __('not paid', 'team-booking');
        }

        if ($reservation->getPrice() > 0) {
            $price_string = $reservation->getCurrencyCode() . ' ' . $reservation->getPrice();
            $total_price_string = $reservation->getCurrencyCode() . ' ' . $reservation->getPriceIncremented() * $reservation->getTickets();
        } else {
            $price_string = 0;
            $total_price_string = 0;
        }

        if ($reservation->getStatus() === 'confirmed' && $reservation->getServiceClass() === 'unscheduled') {
            $reservation_status = __('todo', 'team-booking');
        } else {
            $reservation_status = $reservation->getStatus();
        }

        $row = array(
            $reservation->getDatabaseId(),
            $reservation->getServiceName(TRUE),
            $reservation->getOrderId(),
            $when_value,
            $date_time_of_reservation_value,
            $reservation_status,
            $payment_status,
            $price_string,
            $reservation->getTickets(),
            $total_price_string,
        );
        if (Functions\isAdmin()) {
            $row[] = Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getEmail(); //4th column
        }

        $discount_names = array();
        foreach ($reservation->getDiscount() as $discount) {
            $discount_names[] = $discount['name'] . (isset($discount['coupon']) ? (' (' . $discount['coupon'] . ')') : '');
        }
        $row[] = implode(',', $discount_names);

        if ($reservation->getCustomerUserId()) {
            $user_data = get_userdata($reservation->getCustomerUserId());
            $row[] = $user_data ? $user_data->user_nicename : __('User removed', 'team-booking');
        } else {
            $row[] = '';
        }

        $details = '';
        foreach ($reservation->getFieldsArray() as $key => $value) {
            $details .= ucwords(str_replace('_', ' ', $key)) . ': ' . Toolkit\unfilterInput($value) . '-';
        }
        $row[] = $details;
        fputcsv($output, $row);
    }

    return ob_get_clean();
}

/**
 * @param array|null $orders
 * @param string     $filename
 *
 * @return string
 */
function generateCSVOrdersFile($orders = NULL, $filename = 'orders.csv')
{
    if (NULL === $orders) {
        $reservations = Database\Reservations::getAll();
    } else {
        $reservations = Database\Reservations::getByOrderIds($orders);
    }
    ob_start();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');

    $orders = array();
    foreach ($reservations as $reservation) {
        // If not admin, keep reservations relative to the current coworker only
        $coworker_id = get_current_user_id();
        if (!Functions\isAdmin() && $reservation->getCoworker() != $coworker_id) {
            continue;
        }
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

    // Preparing the header array
    $header_array = array(
        __('Order ID', 'team-booking'),
        __('Date of booking', 'team-booking'),
        __('Customer name', 'team-booking'),
        __('Customer e-mail', 'team-booking'),
        __('Total reservations', 'team-booking'),
        __('Reservations ids', 'team-booking'),
        __('Full amount'),
        __('Paid amount')
    );

    // Output header
    fputcsv($output, $header_array);
    foreach ($orders as $id => $order) {
        $row = array(
            $order->getId(),
            Functions\dateFormatter($order->getDatetime())->date . ' ' . Functions\dateFormatter($order->getDatetime())->time,
            $order->get_customer_display_name(),
            $order->getCustomerEmail(),
            $order->countItems(),
            implode(', ', $order->get_reservation_ids()),
            $order->get_full_amount(),
            $order->get_paid_amount(),
        );
        fputcsv($output, $row);
    }

    return ob_get_clean();
}

/**
 * Generate the settings backup file
 */
function generateSettingsBackup()
{
    $settings = Functions\getSettings()->get_json();
    ob_start();
    header('Content-type: application/json');
    header('Content-Disposition: attachment; filename=team-booking-settings-backup.json');
    $output = fopen('php://output', 'w');
    fwrite($output, $settings);

    return ob_get_clean();
}
