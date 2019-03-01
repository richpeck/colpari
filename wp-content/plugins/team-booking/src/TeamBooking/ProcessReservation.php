<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Frontend\Components,
    TeamBooking\Database,
    TeamBooking\Frontend\Review;

/**
 * Class ProcessReservation
 *
 * @author VonStroheim
 */
class ProcessReservation
{
    /**
     * Process onsite payments
     *
     * @throws \Exception
     */
    public static function processOnsite()
    {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'teambooking_process_payment_onsite')) {
            \TeamBooking\Toolkit\ajaxJsonResponse(array(
                'status'  => 'error',
                'content' => self::getErrorMessage(__('Please refresh the page...', 'team-booking'))
            ));
        }
        ///////////////////////
        // Reservation data  //
        ///////////////////////
        $reservation_database_id = filter_input(INPUT_POST, 'reservation_database_id');
        $order_id = filter_input(INPUT_POST, 'order_id');
        $redirect_order_url = filter_input(INPUT_POST, 'order_redirect', FILTER_SANITIZE_URL);
        if (!empty($order_id)) {
            $reservations = Database\Reservations::getByOrderId($order_id);
            foreach ($reservations as $key => $reservation) {
                if (!$reservation->isToBePaid()) {
                    unset($reservations[ $key ]);
                }
            }
        } else {
            $reservation = Database\Reservations::getById($reservation_database_id);
            $reservation->setToBePaid(TRUE);
            $reservations = array($reservation);
        }
        $additional_parameter = filter_input(INPUT_POST, 'additional_parameter');
        ///////////////////
        // Gateway boot  //
        ///////////////////
        $gateway_id = filter_input(INPUT_POST, 'gateway_id');
        $response = Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->prepareGateway($reservations, $additional_parameter);
        if (!($response instanceof \TeamBooking_Error)) {
            /**
             * at this point, $response must be an array
             * with relevant payment details
             */
            $single_errors = array();
            foreach ($reservations as $reservation_data) {
                $service_id = $reservation_data->getServiceId();
                $reservation_data->setPaid(TRUE);
                $reservation_data->setPaymentGateway($gateway_id);
                $reservation_data->setPaymentDetails($response);
                /**
                 * Let's do the reservation only if it was pending,
                 * and it was of course pending if the payment is
                 * requested immediately
                 */
                if (Database\Services::get($service_id)->getSettingsFor('payment') === 'immediately') {
                    $reservation_class = new \TeamBooking_Reservation($reservation_data);
                    $reservation_data = $reservation_class->doReservation();
                    if ($reservation_data instanceof \TeamBooking_ReservationData) {
                        $reservation_data->setStatusConfirmed();
                        // Send e-mail messages
                        if ($reservation_class->getServiceObj()->getEmailToAdmin('send')) {
                            $reservation_class->sendNotificationEmail();
                        }
                        if (Functions\getSettings()->getCoworkerData($reservation_data->getCoworker())->getCustomEventSettings($reservation_data->getServiceId())->getGetDetailsByEmail()) {
                            $reservation_class->sendNotificationEmailToCoworker();
                        }
                        if ($reservation_data->getCustomerEmail() && $reservation_class->getServiceObj()->getEmailToCustomer('send')) {
                            $reservation_class->sendConfirmationEmail();
                        }
                    } elseif ($reservation_data instanceof \TeamBooking_Error) {
                        $template = new Frontend\ErrorMessages();
                        $code = $reservation_data->getCode();
                        switch ($code) {
                            case 1:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->coworkersRevokedAuth();
                                break;
                            case 2:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->eventNotAvailableAnymore();
                                break;
                            case 3:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->alreadyBooked();
                                break;
                            case 4:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->invalidAttendeeEmail();
                                break;
                            case 5:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->genericGoogleApiError($reservation_data->getMessage());
                                break;
                            case 6:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->eventFull();
                                break;
                            case 8:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->customerMaxCumulativeTicketsOvercome();
                                break;
                            case 60:
                                $single_errors[ $reservation_data->getReservationId() ] = $template->paymentGatewayError($reservation_data->getMessage(), $reservation_data->getExternalCode());
                                break;
                        }
                        continue;
                    }
                }
                Database\Reservations::update($reservation_data);
            }

            if (empty($order_id)) {
                $reservation_data = reset($reservations);
                if ($reservation_data instanceof \TeamBooking_Error) {
                    \TeamBooking\Toolkit\ajaxJsonResponse(array(
                        'status'  => 'error',
                        'content' => isset($single_errors[ $reservation_data->getDatabaseId() ])
                            ? $single_errors[ $reservation_data->getDatabaseId() ]
                            : Frontend\ErrorMessages::basicTemplate($reservation_data->getMessage())
                    ));
                }
                if (Database\Services::get($reservation_data->getServiceId())->getSettingsFor('redirect')) {
                    \TeamBooking\Toolkit\ajaxJsonResponse(array(
                        'status'   => 'redirect',
                        'redirect' => Database\Services::get($reservation_data->getServiceId())->getRedirectUrl($reservation_data->getDatabaseId())
                    ));
                } else {
                    ob_start();
                    // start of HTML response >>>>
                    echo Components\NavigationHeader::InPaymentSuccess();
                    echo Components\AfterPayment::get_positive($reservation_data);
                    // <<<< end of HTML response
                    \TeamBooking\Toolkit\ajaxJsonResponse(array(
                        'status'  => 'ok',
                        'content' => ob_get_clean()
                    ));
                }
            } else {
                if (empty($single_errors)) {
                    if (!empty($redirect_order_url)) {
                        \TeamBooking\Toolkit\ajaxJsonResponse(array(
                            'status'   => 'redirect',
                            'redirect' => $redirect_order_url
                        ));
                    } else {
                        ob_start();
                        // start of HTML response >>>>
                        echo Components\NavigationHeader::InPaymentSuccess();
                        echo Components\AfterPayment::get_positive_order($order_id);
                        // <<<< end of HTML response
                        \TeamBooking\Toolkit\ajaxJsonResponse(array(
                            'status'  => 'ok',
                            'content' => ob_get_clean()
                        ));
                    }
                } else {
                    ob_start();
                    // start of HTML response >>>>
                    echo Components\NavigationHeader::InPaymentSuccess();
                    echo Components\AfterPayment::get_half_positive_order($single_errors, $reservations);
                    // <<<< end of HTML response
                    \TeamBooking\Toolkit\ajaxJsonResponse(array(
                        'status'  => 'ok',
                        'content' => ob_get_clean()
                    ));
                }
            }
        } else {
            /*
             * $response is an error object
             */
            $template = new Frontend\ErrorMessages();
            $code = $response->getCode();
            ob_start();
            switch ($code) {
                case 60:
                    echo $template->paymentGatewayError($response->getMessage(), $response->getExternalCode());
                    break;
            }
            \TeamBooking\Toolkit\ajaxJsonResponse(array(
                'status'  => 'error',
                'content' => ob_get_clean()
            ));
        }

        exit(); // bye bye
    }

    /**
     * Process the very first payment action.
     *
     * Returns (or echoes as JSON) an array with this structure:
     *
     *      'status'   => ok|redirect
     *      'content'  => string (what must shown in the frontend)
     *      'redirect' => string (optional)
     *
     * @param \TeamBooking_ReservationData $reservation_data
     * @param null                         $gateway_id
     * @param bool                         $return
     *
     * @return array|\TeamBooking_Error
     */
    public static function submitPayment(\TeamBooking_ReservationData $reservation_data = NULL, $gateway_id = NULL, $return = FALSE)
    {
        $order_id = NULL;
        $reservations = NULL;
        $redirect_order_url = '';
        if (NULL === $reservation_data) {
            // this function is called via Ajax, parameters are POSTed
            if (isset($_POST['order'])) {
                $order_id = filter_input(INPUT_POST, 'order');
                $redirect_order_url = filter_input(INPUT_POST, 'order_redirect', FILTER_SANITIZE_URL);
                $reservations = Database\Reservations::getByOrderId($order_id);
            } else {
                $reservation_database_id = filter_input(INPUT_POST, 'reservation_database_id');
                $reservation_checksum = filter_input(INPUT_POST, 'reservation_checksum');
                $reservation_data = Database\Reservations::getById($reservation_database_id);
            }
        } else {
            $reservation_checksum = $reservation_data->getToken();
        }

        if (NULL !== $reservation_data && $reservation_data->getToken() !== $reservation_checksum) {
            return new \TeamBooking_Error(61);
        }

        if (NULL === $gateway_id) {
            // this function is called via Ajax, parameters are POSTed
            $gateway_id = filter_input(INPUT_POST, 'gateway_id');
        }

        $items = NULL === $reservations ? array($reservation_data) : $reservations;
        if (Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->isOffsite()) {
            $token = NULL === $reservations ? $reservation_data->getToken() : $order_id;
            // if the gateway is offsite, we call it directly
            $response = array(
                'status'   => 'redirect',
                'redirect' => Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->prepareGateway($items, $token)
            );
        } else {
            // otherwise, we call specific data collecting form
            $response = array(
                'status'  => 'ok',
                'content' => Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->getDataForm($items, $redirect_order_url)
            );
        }
        if ($return) return $response;
        Toolkit\ajaxJsonResponse($response);
    }

    /**
     * Ajax callback: prepare the reservation form for confirmation
     */
    public static function prepareReservation()
    {
        \TeamBooking\Functions\fix_ajax_translations();
        // Map the reservation data
        $reservation_data = Mappers\reservationFormMapper($_POST['data']);

        // Is nonce verification failed?
        if ($reservation_data instanceof \TeamBooking_Error) {
            if ($reservation_data->getCode() === 70) {
                Toolkit\ajaxJsonResponse(array(
                    'status'  => 'error',
                    'content' => self::getErrorMessage($reservation_data->getDisplayText())
                ));
            }
            if ($reservation_data->getCode() === 51) {
                Toolkit\ajaxJsonResponse(array(
                    'status'  => 'error',
                    'content' => self::getErrorMessage($reservation_data->getDisplayText())
                ));
            }
            if ($reservation_data->getCode() === 8) {
                Toolkit\ajaxJsonResponse(array(
                    'status'  => 'error',
                    'content' => Frontend\ErrorMessages::basicTemplate(__('You will reach (or reached already) the maximum number of tickets allowed for this slot. Retry with a lower number!', 'team-booking'))
                ));
            }
        }

        // Set the customer's timezone
        if ($_POST['timezone'] !== 'false') {
            $reservation_data->setCustomerTimezone(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING));
        }

        // Set the coupon code
        if (isset($_POST['coupon']) && !empty($_POST['coupon'])) {
            $reservation_data->applyCoupon($_POST['coupon']);
        }

        // Checks for files
        if (!empty($_FILES)) {
            foreach ($_FILES as $hook => $file) {
                // TODO: server-side data validation
                $returned_handle = Functions\handleFileUpload($file);
                $reservation_data->addFileReference($hook, $returned_handle);
            }
        }
        if (isset($_POST['already_uploaded_ok']) && !empty($_POST['already_uploaded_ok'])) {
            foreach (explode(',', $_POST['already_uploaded_ok']) as $hook) {
                /* file is already uploaded, we are going to use it again by making a copy,
                   otherwise deleting a reservation with this file reference may potentially
                   affect other reservations */
                $file = \TeamBooking\Functions\safe_file_copy(Cart::extractFileReference($hook));
                $reservation_data->addFileReference($hook, $file);
            }
        }

        Cart::addForm($_POST['data'], $reservation_data->getServiceId(), $reservation_data->getCustomerTimezone(), array(), $reservation_data->getFilesReferences());

        // Set the coworker, if needed
        if ($reservation_data->getServiceClass() === 'unscheduled' && !$reservation_data->getCoworker()) {
            $reservation_data->setCoworker(\TeamBooking_Reservation::chooseCoworker($reservation_data->getServiceId()));
        }

        add_action('tbk_reservation_review_header', array(
            'TeamBooking\\Frontend\\Review',
            'review_header',
        ));

        ob_start();
        Actions\reservation_review_header($reservation_data->getServiceName(TRUE));
        // WordPress custom action call
        Actions\reservation_before_processing($reservation_data);
        // Check if we should stop the reservation process
        if (!isset($reservation_data->stop) || !$reservation_data->stop) {
            // Reservation review step
            echo Review::get($reservation_data);
        }
        if (isset($reservation_data->skip_review) && $reservation_data->skip_review) {
            ob_end_clean();
            self::submitReservation($reservation_data);
        } else {
            Toolkit\ajaxJsonResponse(array(
                'status'  => 'ok',
                'content' => ob_get_clean()
            ));
        }
        exit;
    }

    /**
     * Submits the reservation and returns a response.
     *
     * If $to_frontend is FALSE, the method returns either a TeamBooking_Error object
     * or an array with the following structure:
     *
     *      'state'          => confirmed | awaiting_payment | awaiting_approval
     *      'reservation_id' => (int)
     *      'redirect'       => (string) [optional]
     *
     * If $to_frontend is TRUE, the method echoes a JSON object with the following structure:
     *
     *      'status'    => error | ok | redirect
     *      'content'   => (string) The content supposed to be displayed to the user
     *      'redirect'  => (string) [optional]
     *
     * @param null|\TeamBooking_ReservationData $reservation_data
     * @param bool                              $to_frontend
     * @param string                            $who
     *
     * @return mixed
     */
    public static function submitReservation($reservation_data = NULL, $to_frontend = TRUE, $who = NULL)
    {
        \TeamBooking\Functions\fix_ajax_translations();
        if ($to_frontend && \TeamBooking\Functions\getSettings()->allowCart()) {
            // clean the cart if a reservation is booked directly
            \TeamBooking\Cart::cleanSlots();
        }
        // Clean the forms to avoid persistent data
        \TeamBooking\Cart::cleanForms();

        if (NULL === $reservation_data) {
            $reservation_data = Toolkit\objDecode(filter_var($_POST['reservation']));
        }
        if (!($reservation_data instanceof \TeamBooking_ReservationData)) {
            $error = new \TeamBooking_Error(50);
            if (!$to_frontend) return $error;
            Toolkit\ajaxJsonResponse(array(
                'status'  => 'error',
                'content' => self::getErrorMessage($error->getDisplayText())
            ));
        }

        // Service existence check
        try {
            $service = Database\Services::get($reservation_data->getServiceId());
        } catch (\Exception $e) {
            $error = new \TeamBooking_Error(51, $e);
            if (!$to_frontend) return $error;
            Toolkit\ajaxJsonResponse(array(
                'status'  => 'error',
                'content' => self::getErrorMessage($error->getDisplayText())
            ));
        }

        /*
         * Let's save the reservation data.
         * 
         * If payment is requested immediately AND price is > 0
         * then we must set the reservation in PENDING status,
         * awaiting for the payment to be done
         */
        if ($service->getSettingsFor('payment') === 'immediately'
            && $reservation_data->getPriceIncremented() > 0
            && !$reservation_data->isPaid()
            && !\TeamBooking\Functions\isAdmin() //Admins must skip the payment process
        ) {
            // pending
            $reservation_data->setStatusPending();
            /*
             * If it's already placed because the customer went back to change something,
             * just update the reservation record with the new reservation data
             */
            $already_placed = Database\Reservations::getByToken($reservation_data->getToken());
            if ($already_placed) {
                if (!$already_placed->isPending()) {
                    // it's there, but it's not pending, throw an advice
                    $error = new \TeamBooking_Error(52);
                    if (!$to_frontend) return $error;
                    Toolkit\ajaxJsonResponse(array(
                        'status'  => 'error',
                        'content' => self::getErrorMessage($error->getDisplayText())
                    ));
                }
                $reservation_data->setDatabaseId($already_placed->getDatabaseId());
                Database\Reservations::update($reservation_data);
            } else {
                $reservation_data->insert_into_database();
            }

            if (!$to_frontend) {
                $return_array = array(
                    'state'          => 'awaiting_payment',
                    'reservation_id' => $reservation_data->getDatabaseId()
                );
                if ($service->getSettingsFor('redirect') && $service->getRedirectUrl()) {
                    $return_array['redirect'] = $service->getRedirectUrl($reservation_data->getDatabaseId());
                }

                return $return_array;
            }

            self::triggerPayment($reservation_data); //exits

        } else {
            // not pending
            if ($service->getSettingsFor('approval_rule') === 'none') {
                // put in confirmed status
                $reservation_data->setStatusConfirmed();
                // Push the reservation into the database
                $reservation_data->insert_into_database();
                // confirm
                $reservation = new \TeamBooking_Reservation($reservation_data);
                $response = $reservation->doReservation($who);
                // Check for errors
                if ($response instanceof \TeamBooking_Error) {
                    // Remove the reservation record
                    $reservation_data->removeFiles();
                    Database\Reservations::delete($reservation_data->getDatabaseId());

                    if (!$to_frontend) return $response;

                    $template = new Frontend\ErrorMessages();
                    $code = $response->getCode();
                    $content = '';
                    switch ($code) {
                        case 1:
                            $content = $template->coworkersRevokedAuth();
                            break;
                        case 2:
                            $content = $template->eventNotAvailableAnymore();
                            break;
                        case 3:
                            $content = $template->alreadyBooked();
                            break;
                        case 4:
                            $content = $template->invalidAttendeeEmail();
                            break;
                        case 5:
                            $content = $template->genericGoogleApiError($response->getMessage());
                            break;
                        case 6:
                            $content = $template->eventFull();
                            break;
                        case 8:
                            $content = $template->customerMaxCumulativeTicketsOvercome();
                            break;
                    }
                    Toolkit\ajaxJsonResponse(array(
                        'status'  => 'error',
                        'content' => $content
                    ));
                } elseif ($response instanceof \TeamBooking_ReservationData) {
                    /** @var $response \TeamBooking_ReservationData */
                    $reservation_data = $response;
                }
            } else {
                // put in waiting status
                $reservation_data->setStatusWaitingApproval();

                // Push the reservation into the database
                $reservation_data->insert_into_database();
            }

            // Send the notification e-mail
            if ($service->getEmailToAdmin('send')) {
                $reservation = new \TeamBooking_Reservation($reservation_data);
                $reservation->sendNotificationEmail();
            }

            Actions\reservation_completed($reservation_data);

            if ($reservation_data->isWaitingApproval()) {

                // send notification e-mails
                if ($service->getSettingsFor('approval_rule') === 'coworker'
                    && Functions\getSettings()->getCoworkerData($reservation_data->getCoworker())->getCustomEventSettings($reservation_data->getServiceId())->getGetDetailsByEmail()
                ) {
                    $reservation = new \TeamBooking_Reservation($reservation_data);
                    $reservation->sendNotificationEmailToCoworker();
                }

                // let's check if we should redirect or not
                if ($service->getSettingsFor('redirect') && $service->getRedirectUrl()) {
                    if (!$to_frontend) return array(
                        'state'          => 'awaiting_approval',
                        'reservation_id' => $reservation_data->getDatabaseId(),
                        'redirect'       => $service->getRedirectUrl($reservation_data->getDatabaseId())
                    );
                    Toolkit\ajaxJsonResponse(array(
                        'status'   => 'redirect',
                        'redirect' => $service->getRedirectUrl($reservation_data->getDatabaseId())
                    ));
                } else {
                    if (!$to_frontend) return array(
                        'state'          => 'awaiting_approval',
                        'reservation_id' => $reservation_data->getDatabaseId()
                    );
                    // start of HTML response >>>>
                    ob_start();
                    ?>
                    <?= Components\NavigationHeader::InReservationSuccess() ?>
                    <?= Components\AfterReservation::get_positive_waiting_approval($reservation_data) ?>
                    <?php
                    // <<<< end of HTML response
                    Toolkit\ajaxJsonResponse(array(
                        'status'  => 'ok',
                        'content' => ob_get_clean()
                    ));
                }

            } else {

                if ($reservation_data->getPrice() > 0 && $reservation_data->getPriceIncremented() <= 0) {
                    $reservation_data->setPaid(TRUE);
                }

                // Update reservation
                Database\Reservations::update($reservation_data);

                // send notification e-mails
                $reservation = new \TeamBooking_Reservation($reservation_data);
                if (Functions\getSettings()->getCoworkerData($reservation_data->getCoworker())->getCustomEventSettings($reservation_data->getServiceId())->getGetDetailsByEmail()) {
                    $reservation->sendNotificationEmailToCoworker();
                }
                if ($service->getEmailToCustomer('send')) {
                    $reservation->sendConfirmationEmail();
                }

                // let's check if we should redirect or not
                if ($service->getSettingsFor('redirect')) {
                    if (!$to_frontend) return array(
                        'state'          => 'confirmed',
                        'reservation_id' => $reservation_data->getDatabaseId(),
                        'redirect'       => $service->getRedirectUrl($reservation_data->getDatabaseId())
                    );
                    Toolkit\ajaxJsonResponse(array(
                        'status'   => 'redirect',
                        'redirect' => $service->getRedirectUrl($reservation_data->getDatabaseId())
                    ));
                } else {
                    if (!$to_frontend) return array(
                        'state'          => 'confirmed',
                        'reservation_id' => $reservation_data->getDatabaseId()
                    );
                    // start of HTML response >>>>
                    ob_start();
                    ?>
                    <?= Components\NavigationHeader::InReservationSuccess() ?>
                    <?= Components\AfterReservation::get_positive_maybe_payment($reservation_data) ?>
                    <?php
                    // <<<< end of HTML response
                    Toolkit\ajaxJsonResponse(array(
                        'status'  => 'ok',
                        'content' => ob_get_clean()
                    ));
                }
            }
            // bye bye
            exit;
        }
    }

    /**
     * @param \TeamBooking_ReservationData $reservation
     */
    public static function triggerPayment(\TeamBooking_ReservationData $reservation)
    {
        /**
         * We have only one payment gateway active, and the payment is
         * requested immediately? Then, instead of showing payment choices,
         * we would rather skip the step and go directly into the payment
         * process
         */
        $active_payment_gateways = Functions\getSettings()->getPaymentGatewaysActive();
        if (count($active_payment_gateways) === 1) {
            /* @var $gateway \TeamBooking_PaymentGateways_Settings */
            $gateway = reset($active_payment_gateways);
            $gateway_id = $gateway->getGatewayId();
            self::submitPayment($reservation, $gateway_id);
        } else {
            Toolkit\ajaxJsonResponse(array(
                'status'  => 'ok',
                'content' => Components\PaymentChoices::get($reservation)
            ));
        }
        exit;
    }

    /**
     * @param $message
     *
     * @return string
     */
    public static function getErrorMessage($message)
    {
        return '<div class="tbk-slide-body"><div class="tbk-error-message-form" style="display: block"><div class="tbk-message-header">Oops!</div><p>' . esc_html($message) . '</p></div></div>';
    }

    public static function getIcalFile()
    {
        if (isset($_POST['order'])) {
            $order_id = \TeamBooking\Toolkit\objDecode($_POST['order'], TRUE);
            $filename = $order_id;
            $reservations = Database\Reservations::getByOrderId($order_id);
        } else {
            $reservation_id = \TeamBooking\Toolkit\objDecode($_POST['reservation'], TRUE);
            $reservation = Database\Reservations::getById($reservation_id);
            $filename = $reservation->getServiceName(TRUE);
            $reservations = array($reservation);
        }
        Files\generateICSFile(
            Toolkit\filterInput($filename, TRUE) . '.ics',
            $reservations
        );
        exit;
    }

}
