<?php

defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Actions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Frontend\Components,
    TeamBooking\Google;

////////////////////////////////
//  FRONTEND AJAX CALLBACKS   //
////////////////////////////////

/**
 * Ajax callback: change month on frontend calendar
 */
function tbajax_action_change_month_callback()
{
    Functions\fix_ajax_translations();
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $parameters->setMonth(date('m', strtotime($_POST['month'] . '/01')));
    $parameters->setYear($_POST['year']);
    $parameters->setIsAjaxCall(TRUE);
    ob_start();

    // WordPress custom hook
    Actions\calendar_change_month($parameters);

    if (!isset($parameters->stop) || $parameters->stop === FALSE) {
        $calendar->getCalendar($parameters);
    }

    Toolkit\ajaxJsonResponse(array(
        'content'    => ob_get_clean(),
        'parameters' => $parameters->encode()
    ));
}

/**
 * Ajax callback: show day schedule on frontend calendar
 */
function tbajax_action_show_day_schedule_callback()
{
    Functions\fix_ajax_translations();
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    ob_start();
    if ($parameters instanceof TeamBooking\RenderParameters) {
        $parameters->setDay($_POST['day']);
        $parameters->setSlots($parameters->decode($_POST['slots']));
        $parameters->setIsAjaxCall(TRUE);

        // WordPress custom hook
        Actions\calendar_click_on_day($parameters);

        if (!isset($parameters->override_schedule) || $parameters->override_schedule === FALSE) {
            $calendar->getSchedule($parameters);
        }
    } else {
        $error = new TeamBooking_Error(9);
        echo $error->getMessage();
    }
    Toolkit\ajaxJsonResponse(array(
        'content' => ob_get_clean()
    ));
}

/**
 * Ajax callback: get reservation modal content
 */
function tbajax_action_get_reservation_modal_callback()
{
    Functions\fix_ajax_translations();
    /** @var $slot TeamBooking\Slot */
    $slot = Toolkit\objDecode($_POST['slot'], TRUE);
    $form = TeamBooking\Frontend\Form::fromSlot($slot);
    ob_start();
    echo $form->getContent();
    Toolkit\ajaxJsonResponse(array(
        'content' => ob_get_clean()
    ));
}

function tbajax_action_checkout_callback()
{
    Functions\fix_ajax_translations();
    $ordered_slots = array();
    if (count(\TeamBooking\Cart::getSlots()) < 1) {
        ob_start();
        echo \TeamBooking\Frontend\ErrorMessages::cartTimeExpired();
        Toolkit\ajaxJsonResponse(array(
            'content' => ob_get_clean(),
            'action'  => 'empty_cart'
        ));
    }
    if (count(\TeamBooking\Cart::getSlots()) === 1) {
        $slots = \TeamBooking\Cart::getSlots();
        $_POST['slot'] = Toolkit\objEncode(reset($slots), TRUE);
        tbajax_action_get_reservation_modal_callback();
    }
    foreach (\TeamBooking\Cart::getSlots() as $slot) {
        $ordered_slots[ $slot->getServiceId() ][] = $slot;
    }
    $steps_count = 1;
    $services = array_keys($ordered_slots);
    $services_left = array_flip($services);

    // Conditional check if a Form data is present in the request, save it
    if (isset($_POST['data'])) {
        $specific_slot = isset($_POST['specific_slot']) ? $_POST['specific_slot'] : array();
        $file_refs = array();
        if (!empty($_FILES)) {
            foreach ($_FILES as $hook => $file) {
                // TODO: server-side data validation
                $returned_handle = Functions\handleFileUpload($file);
                $file_refs[ $hook ] = $returned_handle;
            }
        }
        if (isset($_POST['already_uploaded_ok']) && !empty($_POST['already_uploaded_ok'])) {
            foreach (explode(',', $_POST['already_uploaded_ok']) as $hook) {
                /* file is already uploaded, we are going to use it again by making a copy,
                   otherwise deleting a reservation with this file reference may potentially
                   affect other reservations */
                $file = Functions\safe_file_copy(\TeamBooking\Cart::extractFileReference($hook));
                $file_refs[ $hook ] = $file;
            }
        }
        \TeamBooking\Cart::addForm($_POST['data'], $_POST['service_id'], $_POST['timezone'], $specific_slot, $file_refs);
    }

    // Conditional check if a Form data is already in cart
    $forms = \TeamBooking\Cart::getForms();
    foreach ($forms as $service_id => $form_data) {
        if (isset($services_left[ $service_id ])) {
            unset($services_left[ $service_id ]);
            $steps_count++;
        }
    }

    // if $services_left is empty, there is no form left, so go further
    if (empty($services_left)) {
        $content = Components\NavigationHeader::basic(sprintf(
                __('Step %d of %d', 'team-booking'),
                count($services) + 1,
                count($services) + 1
            ) . ' - ' . __('summary', 'team-booking')
        );
        $tickets = \TeamBooking\Cart::getTickets();
        $total_amount = (float)0;
        $total_amount_due = (float)0;
        ob_start();
        foreach ($ordered_slots as $service_id => $slots) {
            $general_form = array();
            $forms = \TeamBooking\Cart::getFormsByService($service_id);
            foreach ($forms as $form) {
                if (empty($form['specific_slot'])) $general_form = $form;
                $files = $form['file_references'];
            }
            $service = Database\Services::get($service_id);
            echo Components\Summary::service_header($service);
            Components\Summary::table_open($service);
            foreach ($slots as $slot) {
                /** @var $slot \TeamBooking\Slot */
                $slot_form = NULL;
                $slot_files = array();
                foreach ($forms as $form) {
                    if (in_array($slot->getUniqueId(), $form['specific_slot'])) {
                        $slot_form = $form;
                        $slot_files = $form['file_references'];
                    }
                }
                if (NULL === $slot_form) $slot_form = NULL === $general_form ? reset($forms) : $general_form;
                if (empty($slot_files)) $slot_files = NULL !== $files ? $files : array();
                $reservation_data = \TeamBooking\Mappers\reservationFormMapper($slot_form['raw_data'] . '&slot=' . urlencode(Toolkit\objEncode($slot, TRUE)), TRUE);
                $reservation_data->setTickets(isset($tickets[ $slot->getUniqueId() ]) ? $tickets[ $slot->getUniqueId() ]['normal'] : 1);
                $reservation_data->setCoworker($slot->getCoworkerId());
                foreach ($slot_files as $hook => $slot_file) {
                    $reservation_data->addFileReference($hook, $slot_file);
                }
                echo Components\Summary::slot_row($slot, $reservation_data);
                if ($service->getSettingsFor('payment') === 'immediately') {
                    $total_amount_due += $reservation_data->getPriceIncremented();
                }
                $total_amount += $reservation_data->getPriceIncremented();
            }
            Components\Summary::table_close();
        }
        if (Functions\isThereOneCouponAtLeast()) {
            echo Components\Summary::coupon_line();
        }
        if (Functions\isAdmin()) {
            echo Components\Summary::skip_payment_notice();
        }
        echo Components\Summary::footer_actions($total_amount_due, $total_amount);
        $content .= ob_get_clean();

    } else {
        $content = \TeamBooking\Frontend\Components\NavigationHeader::basic(sprintf(
            __('Step %d of %d', 'team-booking'),
            $steps_count,
            count($services) + 1
        ));
        $form = TeamBooking\Frontend\Form::fromService($services[ $steps_count - 1 ], TRUE);
        $content .= Components\Form::header($form->service->getName(TRUE));
        $content .= $form->getContent();
    }

    Toolkit\ajaxJsonResponse(array(
        'total_services' => count($services),
        'content'        => $content
    ));
}

function tbajax_action_checkout_edit_form_callback()
{
    Functions\fix_ajax_translations();
    $content = \TeamBooking\Frontend\Components\NavigationHeader::basic('Edit data');
    $form = TeamBooking\Frontend\Form::fromService(filter_input(INPUT_POST, 'service'), TRUE);
    $content .= Components\Form::header($form->service->getName(TRUE));
    $content .= $form->getContent(TRUE);
    Toolkit\ajaxJsonResponse(array(
        'content' => $content
    ));
}

/**
 *
 */
function tbajax_action_checkout_confirm_callback()
{
    Functions\fix_ajax_translations();
    parse_str($_POST['inputs'], $inputs);
    $reservations = array();
    $outcomes = array();
    $output = '';
    $show_payment_step = FALSE;
    $there_are_errors = FALSE;
    $all_are_errors = TRUE;
    $order = new \TeamBooking\Order();
    foreach ($_POST['reservations'] as $reservation_enc) {
        /** @var TeamBooking_ReservationData $obj */
        $obj = Toolkit\objDecode($reservation_enc);
        if (isset($inputs['tickets'][ $obj->getToken() ])) {
            $obj->setTickets($inputs['tickets'][ $obj->getToken() ]);
        }
        if (isset($_POST['tbk_coupon']) && !empty($_POST['tbk_coupon'])) {
            $obj->applyCoupon($_POST['tbk_coupon']);
        }
        $obj->setToBePaid(isset($inputs['to_be_paid'][ $obj->getToken() ]));
        $obj->setOrderId($order->getId());
        $reservations[ $obj->getToken() ] = $obj;
    }

    foreach ($reservations as $reservation) {
        /** @var TeamBooking_ReservationData $reservation */
        $attempt = \TeamBooking\ProcessReservation::submitReservation($reservation, FALSE);
        if ($attempt instanceof TeamBooking_Error) {
            $order->add_item_with_error($reservation, $attempt);
            $outcomes[ $reservation->getToken() ] = array(
                'status'         => 'error',
                'service_id'     => $reservation->getServiceId(),
                'reservation_id' => $reservation->getDatabaseId(),
                'reason'         => $attempt->getCode(),
                'output'         => $attempt->getDisplayText(),
                'amount'         => $reservation->getPriceIncremented() * $reservation->getTickets()
            );
            $there_are_errors = TRUE;
        } else {
            if ($attempt instanceof TeamBooking_ReservationData) $reservation = $attempt;
            $order->add_item($reservation);
            $outcomes[ $reservation->getToken() ] = array(
                'status'         => $attempt['state'],
                'service_id'     => $reservation->getServiceId(),
                'reservation_id' => $reservation->getDatabaseId(),
                'redirect'       => isset($attempt['redirect']) ? $attempt['redirect'] : '',
                'output'         => '',
                'amount'         => $reservation->getPriceIncremented() * $reservation->getTickets()
            );
            if ($reservation->isToBePaid()) {
                $show_payment_step = TRUE;
            }
            $all_are_errors = FALSE;
        }
    }

    $redirect_url = Actions\order_redirect_url($order->getId(), $outcomes);
    $order->setRedirectUrl($redirect_url);

    if ($show_payment_step && !Functions\isAdmin()) {
        if ($all_are_errors) {
            $output = Components\AfterReservation::get_order_errors($order, TRUE);
        } elseif ($there_are_errors) {
            $output = Components\AfterReservation::get_order_errors_payment($order);
        } else {
            $output = Components\AfterReservation::get_order_positive_payment($order);
        }
    } else {
        if ($all_are_errors) {
            $output = Components\AfterReservation::get_order_errors($order, TRUE);
        } elseif ($there_are_errors) {
            $output = Components\AfterReservation::get_order_errors($order);
        } else {
            $output = Components\AfterReservation::get_order_positive($order);
        }
    }

    \TeamBooking\Cart::cleanSlots();
    \TeamBooking\Cart::cleanForms();

    \TeamBooking\EmailHandlerBatch::send();

    Actions\order_completed($order->getId(), $outcomes);

    Toolkit\ajaxJsonResponse(array(
        'results'      => $outcomes,
        'content'      => $output,
        'redirect'     => $show_payment_step || empty($redirect_url) ? 'no' : 'yes',
        'redirect_url' => $redirect_url
    ));
}

/**
 * Ajax callback: cancel the checkout data
 */
function tbajax_action_checkout_cancel_callback()
{
    \TeamBooking\Cart::cleanForms();
    Toolkit\ajaxJsonResponse(array(
        'status' => 'ok'
    ));
}

/**
 * Ajax callback: put a slot into the cart
 */
function tbajax_action_put_slot_into_cart_callback()
{
    Functions\fix_ajax_translations();
    if (filter_input(INPUT_POST, 'remove', FILTER_VALIDATE_BOOLEAN)) {
        \TeamBooking\Cart::removeSlot(Toolkit\objDecode($_POST['slot_id'], TRUE));
        $did = 'removed';
        Actions\cart_slot_removed(Toolkit\objDecode($_POST['slot_id'], TRUE));
    } else {
        /** @var $slot TeamBooking\Slot */
        $slot = Toolkit\objDecode($_POST['slot'], TRUE);
        // Set the customer's timezone
        if ($_POST['timezone'] !== 'false' && !$slot->isAllDay()) {
            $slot->setTimezone(Functions\parse_timezone_aliases(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING)));
        }
        $slot->updatePriceDiscounted();
        \TeamBooking\Cart::addSlot($slot);
        $did = 'added';
        $data = \TeamBooking\Frontend\Components\Cart::menuItem($slot);
        Actions\cart_slot_added($slot);
    }

    Toolkit\ajaxJsonResponse(array(
        'response' => $did,
        'data'     => $data
    ));
}

/**
 * Ajax callback: get register/login modal content
 */
function tbajax_action_get_register_modal_callback()
{
    Functions\fix_ajax_translations();
    $event_id = $_POST['event'];
    $coworker_id = isset($_POST['coworker']) ? $_POST['coworker'] : '';
    $service_id = $_POST['service'];
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : NULL;
    echo TeamBooking\Frontend\Form::getContentRegisterAdvice(FALSE, $event_id, $service_id, $coworker_id, $post_id);
    exit;
}

/**
 * Ajax callback: save the cookie consent
 */
function tbajax_action_save_cookie_consent_callback()
{
    \TeamBooking\Cart::setPreference('keep_preferences', filter_input(INPUT_POST, 'allow', FILTER_VALIDATE_BOOLEAN));
    echo 'ok';
    exit;
}


function tbajax_action_upcoming_more_callback()
{
    Functions\fix_ajax_translations();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $limit = filter_var($_POST['limit'], FILTER_SANITIZE_NUMBER_INT);
    $increment = filter_var($_POST['increment'], FILTER_SANITIZE_NUMBER_INT);
    if ($_POST['increment'] !== 'false') {
        if ($limit > 0 && $parameters->getSlotsShown() + $increment > $limit) {
            $parameters->setSlotsShown($limit);
        } else {
            $parameters->setSlotsShown($parameters->getSlotsShown() + $increment);
        }
    }
    $parameters->setIsAjaxCall(TRUE);
    ob_start();
    echo \TeamBooking\Shortcodes\Upcoming::getView($parameters, strlen($parameters->getInstance()) !== 8);
    Toolkit\ajaxJsonResponse(array(
        'content'    => ob_get_clean(),
        'parameters' => $parameters->encode()
    ));
}

function tbajax_action_filter_upcoming_callback()
{
    Functions\fix_ajax_translations();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    if ($_POST['timezone'] !== 'false') {
        $parameters->setTimezone(Toolkit\getTimezone(Functions\parse_timezone_aliases(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING))));
        \TeamBooking\Cart::setPreference('timezone', filter_var($_POST['timezone'], FILTER_SANITIZE_STRING));
    }
    $parameters->setIsAjaxCall(TRUE);
    $response = array();
    if (Functions\getSettings()->allowCart()) {
        $cart_slots = \TeamBooking\Cart::getSlots();
        $cart_slots_data = array();
        foreach ($cart_slots as $cart_slot) {
            $cart_slots_data[ Toolkit\objEncode($cart_slot->getUniqueId()) ] = array(
                'menu_item' => Components\Cart::menuItem($cart_slot)
            );
        }
        $response['cart_slots'] = $cart_slots_data;
    }
    ob_start();
    echo \TeamBooking\Shortcodes\Upcoming::getView($parameters, strlen($parameters->getInstance()) !== 8);
    $response['upcoming'] = ob_get_clean();
    $response['parameters'] = $parameters->encode();
    Toolkit\ajaxJsonResponse($response);
}

function tbajax_action_filter_calendar_callback()
{
    Functions\fix_ajax_translations();
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $unscheduled = FALSE;
    if ($_POST['services'] !== 'false') {
        $parameters->setRequestedServiceIds(unserialize(base64_decode($_POST['services'])));
    } else {
        if ($_POST['service'] !== 'false') {
            $parameters->setRequestedServiceIds(array($_POST['service']));
            if (Database\Services::get($_POST['service'])->getClass() === 'unscheduled') $unscheduled = TRUE;
        } else {
            $parameters->setRequestedServiceIds($parameters->getServiceIds());
        }
    }
    if ($_POST['coworkers'] !== 'false') {
        $parameters->setRequestedCoworkerIds(unserialize(base64_decode($_POST['coworkers'])));
    } else {
        if ($_POST['coworker'] !== 'false') {
            $parameters->setRequestedCoworkerIds(array($_POST['coworker']));
        } else {
            $parameters->setRequestedCoworkerIds($parameters->getCoworkerIds());
        }
    }
    if ($_POST['timezone'] !== 'false') {
        $parameters->setTimezone(Toolkit\getTimezone(Functions\parse_timezone_aliases(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING))));
        \TeamBooking\Cart::setPreference('timezone', filter_var($_POST['timezone'], FILTER_SANITIZE_STRING));
    }
    $response = array();
    $parameters->setIsAjaxCall(TRUE);
    if (Functions\getSettings()->allowCart()) {
        $cart_slots = \TeamBooking\Cart::getSlots();
        $cart_slots_data = array();
        foreach ($cart_slots as $cart_slot) {
            $cart_slots_data[ Toolkit\objEncode($cart_slot->getUniqueId()) ] = array(
                'menu_item' => Components\Cart::menuItem($cart_slot)
            );
        }
        $response['cart_slots'] = $cart_slots_data;
    }
    ob_start();
    $calendar->getCalendar($parameters, TRUE);
    $response['calendar'] = ob_get_clean();
    $response['parameters'] = $parameters->encode();
    $response['unscheduled'] = $unscheduled;
    Toolkit\ajaxJsonResponse($response);
}

function tbajax_action_fast_month_selector_callback()
{
    Functions\fix_ajax_translations();
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $parameters->setMonth($_POST['month']);
    $parameters->setIsAjaxCall(TRUE);
    ob_start();
    $calendar->getCalendar($parameters);
    Toolkit\ajaxJsonResponse(array(
        'content'    => ob_get_clean(),
        'parameters' => $parameters->encode()
    ));
}

function tbajax_action_fast_year_selector_callback()
{
    Functions\fix_ajax_translations();
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $parameters->setYear($_POST['year']);
    $parameters->setIsAjaxCall(TRUE);
    ob_start();
    $calendar->getCalendar($parameters);
    Toolkit\ajaxJsonResponse(array(
        'content'    => ob_get_clean(),
        'parameters' => $parameters->encode()
    ));
}

function tbajax_action_cancel_reservation_callback()
{
    Functions\fix_ajax_translations();
    $hash = $_POST['reservation_hash'];
    $id = $_POST['reservation_id'];
    $response = '';
    /**
     * Retrieving the reservation record in database
     */
    $reservation_db_record = Database\Reservations::getById($id);
    /**
     * Hash check
     */
    if ($reservation_db_record->getToken() !== $hash) {
        exit;
    }
    /**
     * Instantiating the reservation service class
     */
    $reservation = new TeamBooking_Reservation($reservation_db_record);
    /**
     * Calling the cancel method
     */
    $updated_record = $reservation->cancelReservation($id);
    if ($updated_record instanceof TeamBooking_ReservationData) {
        /**
         * Everything went fine, let's update the database record
         */
        Database\Reservations::update($updated_record);
        $response = 'ok';
    } elseif ($updated_record instanceof TeamBooking_Error) {
        /**
         * Something goes wrong
         */
        if ($updated_record->getCode() == 7) {
            /*
             * The reservation is already cancelled, let's update the database record
             */
            $reservation_db_record->setStatusCancelled();
            Database\Reservations::update($reservation_db_record);
        }
        $response = $updated_record->getMessage();
    }
    Toolkit\ajaxJsonResponse(array(
        'response' => $response
    ));
}

function tbajax_action_validate_coupon_callback()
{
    Functions\fix_ajax_translations();
    $response = array('status' => 'ok');
    $code = Toolkit\filterInput($_POST['code']);
    /** @var $slot \TeamBooking\Slot */
    $slot = Toolkit\objDecode(filter_input(INPUT_POST, 'slot'), TRUE);
    \TeamBooking\Cart::cleanPreference('coupon_code');
    try {
        if (!$slot) {
            $service = Database\Services::get(filter_input(INPUT_POST, 'service_id'));
        } else {
            $service = Database\Services::get($slot->getServiceId());
        }
        if ($service->getClass() === 'unscheduled') {
            $discount = Functions\getPriceWithCoupon($service, $code);
        } else {
            $discount = Functions\getPriceWithCoupon($service, $code, $slot->getPriceBase(), $slot->getPriceDiscounted());
        }
        if (!$discount) {
            $response['status'] = 'error';
        } else {
            \TeamBooking\Cart::setPreference('coupon_code', array('code' => $code, 'promotion_id' => $discount['promotion']['id']));
            $response['value'] = $discount['discounted'];
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
    }
    Toolkit\ajaxJsonResponse($response);
}

function tbajax_action_validate_coupon_cart_callback()
{
    Functions\fix_ajax_translations();
    $response = array();
    $code = Toolkit\filterInput($_POST['code']);
    $slots = \TeamBooking\Cart::getSlots();
    foreach ($slots as $slot) {
        try {
            $service = Database\Services::get($slot->getServiceId());
        } catch (Exception $e) {
            continue;
        }
        $new_price = Functions\getPriceWithCoupon($service, $code, $slot->getPriceBase(), $slot->getPriceDiscounted());
        if ($new_price === FALSE) {
            continue;
        }
        $response[ wp_hash($slot->getUniqueId()) ] = $new_price;
    }
    Toolkit\ajaxJsonResponse(array(
        'value'  => $response,
        'status' => empty($response) ? 'error' : 'ok'
    ));
}

///////////////////////////////
//  BACKEND AJAX CALLBACKS   //
///////////////////////////////

/**
 * oAuth2 callback, for authentication
 */
function teambooking_oauth_callback()
{
    // Check if there is auth code and user capability
    if (isset($_GET['code']) && current_user_can('tb_can_sync_calendar')) {
        // casting the REQUEST array to an object and saving it
        $request = (object)$_REQUEST;
        // initialize session
        if (!session_id()) {
            session_start();
        }
        // nonce check (defined in TeamBooking_Calendar class)
        if (isset($_SESSION['tbk-auth-state'])) {
            if ($_SESSION['tbk-auth-state'] != $request->state) {
                $location = admin_url('admin.php?page=team-booking-personal&nag_auth_failed=1');
                exit(wp_redirect($location));
            }
        }
        // get settings
        $settings = Functions\getSettings();
        // get the coworker data
        $coworker = $settings->getCoworkerData(get_current_user_id());
        // instantiate a calendar class
        $calendar = new TeamBooking\Calendar();
        // retrieve the coworker's access token, if present
        $access_token = $coworker->getAccessToken();
        if (!empty($access_token)) {
            // an access token is already present... revoke it?
        } else {
            /**
             * Access token not present, let's exchange the auth code
             * for access token, refresh token, id token set.
             *
             * Before saving them, we'll check if there is a refresh token.
             * If the refresh token is not present, the Google Account
             * thinks that this application is already trusted, and a previous
             * refresh token was already granted without being revoked yet.
             *
             * We'll check also if the Google Account email is actually used
             * by another coworker.
             *
             */
            $tokens = $calendar->authenticate($request->code);
            // is there a refresh token?
            if (NULL === $calendar->setAccessToken($tokens)) {
                // There is no refresh token
                $location = admin_url('admin.php?page=team-booking-personal&nag_auth_no_refresh=1');
                exit(wp_redirect($location));
            } else {
                /**
                 * There is a refresh token, is the Google Account already used?
                 *
                 * NOTE:
                 * this configuration (refresh token provided && Google Account already authorized)
                 * should NOT be possible, according to oAuth flow.
                 *
                 * TODO: test if this whole block is useless
                 */
                $already_used = FALSE;
                $this_email = $calendar->getTokenEmailAccount($tokens, get_current_user_id());
                if ($this_email instanceof Google\Google_Auth_Exception) {
                    /** @var $this_email Google\Google_Auth_Exception */
                    $this_email_message = $this_email->getMessage();
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        $u_id = get_current_user_id();
                        $e_code = $this_email->getCode();
                        $e_file = $this_email->getFile();
                        $e_line = $this_email->getLine();
                        trigger_error("Gmail address cannot be retrieved for user id {$u_id} (current), error: {$e_code} {$this_email_message} in file {$e_file} at line {$e_line}");
                    }
                }
                $coworker_id_list = Functions\getAuthCoworkersList();
                foreach ($coworker_id_list as $coworker_id => $coworker_data) {
                    $that_email = $calendar->getTokenEmailAccount($coworker_data['tokens'], $coworker_id);
                    if ($that_email instanceof Google\Google_Auth_Exception) {
                        /** @var $that_email Google\Google_Auth_Exception */
                        $that_email_message = $that_email->getMessage();
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            $e_code = $that_email->getCode();
                            $e_file = $that_email->getFile();
                            $e_line = $that_email->getLine();
                            trigger_error("Gmail address cannot be retrieved for user id {$coworker_id}, error: {$e_code} {$that_email_message} in file {$e_file} at line {$e_line}");
                        }
                    }
                    if ($this_email == $that_email) {
                        $already_used = TRUE;
                    }
                }
                if ($already_used) {
                    $location = admin_url('admin.php?page=team-booking-personal&nag_auth_already_used=1');
                    exit(wp_redirect($location));
                }

                /**
                 * All is correct, let's save the tokens
                 */
                $coworker->setAccessToken($tokens);
                $coworker->setAuthAccount($this_email);
                $settings->updateCoworkerData($coworker);
                $settings->save();
                // set redirect
                $location = admin_url('admin.php?page=team-booking-personal&nag_auth_success=1');
                exit(wp_redirect($location));
            }
        }
    } elseif (isset($_GET['error'])) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $u_id = get_current_user_id();
            trigger_error("Error during oAuth phase for user id {$u_id}, error: {$_GET['error']}");
        }
    }
    exit;
}

/**
 * Instantiates the listeners for IPN payments of any gateway
 */
function teambooking_ipn_listener()
{
    foreach (Functions\getSettings()->getPaymentGatewaySettingObjects() as $gateway_id => $gateway) {
        /* @var $gateway TeamBooking_PaymentGateways_Settings */
        if (isset($_REQUEST[ $gateway_id ])) {
            // use raw POST data
            $raw_post_data = file_get_contents('php://input');
            $raw_post_array = explode('&', $raw_post_data);
            $post_data = array();
            foreach ($raw_post_array as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) === 2) {
                    $post_data[ $keyval[0] ] = urldecode($keyval[1]);
                }
            }
            $gateway->listenerIPN($post_data);
            exit;
        }
    }
    exit;
}

function teambooking_rest_api()
{
    $method = $_SERVER['REQUEST_METHOD'];
    $response = \TeamBooking\API\REST::call($method, $_REQUEST);

    Actions\api_response($response);

    if ($response['code'] == 302) {
        header('Location: ' . $response['response']);
    } else {
        if (isset($response['output'])) {

        } else {
            header('Content-Type: application/json', TRUE, $response['code']);
            echo json_encode($response);
        }
    }
    exit;
}