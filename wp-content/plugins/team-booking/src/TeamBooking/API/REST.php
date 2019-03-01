<?php

namespace TeamBooking\API;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Actions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Files,
    TeamBooking\Calendar,
    TeamBooking\ProcessReservation;

class REST
{
    private $params;
    private $response;
    /** @var  $service \TeamBooking\Abstracts\Service */
    private $service;

    private function parse($data)
    {
        if (isset($data['checksum'])) $this->params['checksum'] = filter_var($data['checksum'], FILTER_SANITIZE_STRING);
        if (isset($data['operation'])) $this->params['operation'] = filter_var($data['operation'], FILTER_SANITIZE_STRING);
        if (isset($data['gateway'])) $this->params['gateway'] = filter_var($data['gateway'], FILTER_SANITIZE_STRING);
        if (isset($data['id'])) $this->params['id'] = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
        if (isset($data['coworker'])) $this->params['coworker'] = filter_var($data['coworker'], FILTER_SANITIZE_NUMBER_INT);
        if (isset($data['ctoken'])) $this->params['ctoken'] = filter_var($data['ctoken'], FILTER_SANITIZE_STRING);
        if (isset($data['auth_token'])) $this->params['auth_token'] = filter_var($data['auth_token'], FILTER_SANITIZE_STRING);
        if (isset($data['reservation'])) $this->params['reservation'] = filter_var($data['reservation']);
        if (isset($data['slot'])) $this->params['slot'] = filter_var($data['slot']);
        if (isset($data['service_id'])) $this->params['service_id'] = filter_var($data['service_id'], FILTER_SANITIZE_STRING);
        if (isset($data['reason'])) $this->params['reason'] = filter_var($data['reason'], FILTER_SANITIZE_STRING);
        if (isset($data['service'])) $this->params['service'] = filter_var($data['service']);
        if (isset($data['show_confirm'])) {
            $this->params['show_confirm'] = filter_var($data['show_confirm'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $this->params['show_confirm'] = FALSE;
        }
        if (isset($data['show_past'])) {
            $this->params['show_past'] = filter_var($data['show_past'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $this->params['show_past'] = TRUE;
        }
    }

    public static function call($method, $data)
    {
        $api = new REST();
        $api->response['header'] = '403 Forbidden';
        $api->response['code'] = 403;
        $api->parse($data);
        if (!isset($api->params['operation'])) {
            $api->response['header'] = '400 Bad Request';
            $api->response['code'] = 400;
            $api->response['reason'] = 'missing parameters';
        } else {
            switch ($method) {
                case 'GET':
                    $api->GET();
                    break;
                case 'POST':
                    $api->POST();
                    break;
            }
        }

        return $api->response;
    }

    /**
     * @return bool|\TeamBooking_ReservationData
     */
    private function scopeReservation()
    {
        if (!$this->isRequestComplete(array('id', 'checksum'))) return FALSE;

        $reservation = Database\Reservations::getById($this->params['id']);
        if (!$reservation) {
            $this->response['header'] = '404 Not Found';
            $this->response['code'] = 404;
            $this->response['reason'] = __('Reservation not found', 'team-booking');

            return FALSE;
        }
        if ($reservation->getToken() !== $this->params['checksum']) {
            $this->response['reason'] = 'checksum fail';

            return FALSE;
        }

        try {
            $this->service = Database\Services::get($reservation->getServiceId());
        } catch (\Exception $e) {
            $this->response['header'] = '404 Not Found';
            $this->response['code'] = 404;
            $this->response['reason'] = __('Service not found', 'team-booking');

            return FALSE;
        }

        return $reservation;
    }

    private function GET()
    {
        switch ($this->params['operation']) {
            case 'get_reservation':
                if (!$this->isRequestComplete(array('auth_token', 'id'))) return FALSE;
                if (!$this->isTokenValid()) return FALSE;
                $reservation = Database\Reservations::getById($this->params['id']);
                if (!($reservation instanceof \TeamBooking_ReservationData)) {
                    $this->response['header'] = '404 Not Found';
                    $this->response['code'] = 404;
                    $this->response['reason'] = 'Record not found';

                    return FALSE;
                }
                Functions\getSettings()->incrementTokenUsage('get_reservation', $this->params['auth_token']);
                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;
                $this->response['response'] = $reservation->getApiResource();
                break;
            case 'get_reservations':
                if (!$this->isRequestComplete(array('auth_token'))) return FALSE;
                if (!$this->isTokenValid()) return FALSE;
                if (isset($this->params['coworker'], $this->params['service_id'])) {
                    $reservations = Database\Reservations::getByServiceAndCoworker($this->params['service_id'], $this->params['coworker']);
                } else {
                    if (isset($this->params['coworker'])) {
                        $reservations = Database\Reservations::getByCoworker($this->params['coworker']);
                    } elseif (isset($this->params['service_id'])) {
                        $reservations = Database\Reservations::getByService($this->params['service_id']);
                    } else {
                        $reservations = Database\Reservations::getAll();
                    }
                }

                if (isset($this->params['show_past']) && !$this->params['show_past']) {
                    foreach ($reservations as $id => $reservation) {
                        if (Functions\isReservationPastInTime($reservation)) unset($reservations[ $id ]);
                    }
                }
                $results = array();
                foreach ($reservations as $id => $reservation) {
                    $results[] = $reservation->getApiResource();
                }
                Functions\getSettings()->incrementTokenUsage('get_reservations', $this->params['auth_token']);
                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;
                $this->response['response'] = $results;
                break;
            case 'get_services':
                if (!$this->isRequestComplete(array('auth_token'))) return FALSE;
                if (!$this->isTokenValid()) return FALSE;
                $results = array();
                foreach (Database\Services::get() as $service) {
                    $results[] = $service->getApiResource();
                }
                Functions\getSettings()->incrementTokenUsage('get_services', $this->params['auth_token']);
                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;
                $this->response['response'] = $results;
                break;
            case 'get_slots':
                if (!$this->isRequestComplete(array('auth_token', 'service_id'))) return FALSE;
                if (!$this->isTokenValid()) return FALSE;
                try {
                    Database\Services::get($this->params['service_id']);
                    Functions\getSettings()->incrementTokenUsage('get_slots', $this->params['auth_token']);
                    $calendar = new Calendar();
                    $slots = $calendar->getSlots(array($this->params['service_id']), Functions\getAuthCoworkersIdList())->getAllSlots();
                    $results = array();
                    foreach ($slots as $slot) {
                        $results[] = $slot->getApiResource();
                    }
                    $this->response['header'] = '200 OK';
                    $this->response['code'] = 200;
                    $this->response['response'] = $results;
                } catch (\Exception $e) {
                    $this->response['header'] = '404 Not Found';
                    $this->response['code'] = 404;
                    $this->response['reason'] = 'Service not found';
                }
                break;
            /**
             *  THIS IS RESERVED
             *  Customer payment e-mail link
             */
            case 'pay':
                $reservation = $this->scopeReservation();
                if (!$reservation) break;
                $this->showPayment($reservation);
                break;
            /**
             *  THIS IS RESERVED
             *  Customer get iCal file e-mail link
             */
            case 'ics':
                $reservation = $this->scopeReservation();
                if (!$reservation) break;
                if (Functions\getSettings()->getShowIcal()) {
                    Files\generateICSFile(
                        Toolkit\filterInput($reservation->getServiceName(TRUE), TRUE) . '.ics',
                        array($reservation)
                    );
                    exit;
                }
                break;
            /**
             *  THIS IS RESERVED
             *  Customer cancellation e-mail link
             */
            case 'cancel':
                $reservation = $this->scopeReservation();
                if (!$reservation) {
                    if ($this->params['show_confirm']) {
                        $this->integrateWithTheme(
                            '<h2>' . esc_html($this->response['reason']) . '</h2>',
                            esc_html__('Cancellation error', 'team-booking')
                        );
                        $this->response['output'] = TRUE;
                    }
                    break;
                }
                if ($reservation->isCancelled()) {
                    $this->integrateWithTheme(
                        '<h2>'
                        . esc_html__('This reservation is cancelled already!', 'team-booking')
                        . '</h2>',
                        sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                    );
                    $this->response['output'] = TRUE;
                    break;
                }
                if ($this->service->getSettingsFor('customer_cancellation') && $this->service->getClass() !== 'unscheduled') {
                    $now = current_time('timestamp', TRUE);
                    if ($now + $this->service->getSettingsFor('cancellation_allowed_until') < $reservation->getStart()) {
                        if ($this->params['show_confirm']) {
                            $ok_link = admin_url() . 'admin-ajax.php?action=teambooking_rest_api&operation=cancel&id='
                                . $reservation->getDatabaseId()
                                . '&checksum=' . $reservation->getToken();
                            $this->showConfirmation(__('Do you want to cancel this reservation?', 'team-booking'), $ok_link, $reservation);
                        } else {
                            $this->cancel_reservation($reservation, get_current_user_id());
                            $this->integrateWithTheme(
                                '<h2>'
                                . esc_html__('Reservation successfully cancelled!', 'team-booking')
                                . '</h2>',
                                sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                            );
                            $this->response['output'] = TRUE;
                        }
                    } else {
                        $this->integrateWithTheme(
                            '<h2>'
                            . esc_html__('This reservation can no longer be cancelled!', 'team-booking')
                            . '</h2>',
                            sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                        );
                        $this->response['output'] = TRUE;
                    }
                }
                break;
            /**
             *  THIS IS RESERVED
             *  Approval e-mail or gcal event link
             */
            case 'approve':
                $reservation = $this->scopeReservation();
                if (!$reservation) {
                    if ($this->params['show_confirm']) {
                        $this->integrateWithTheme(
                            '<h2>' . $this->response['reason'] . '</h2>'
                            , esc_html__('Approval error', 'team-booking')
                        );
                        $this->response['output'] = TRUE;
                    }
                    break;
                }
                if (!Functions\getCoworkerFromApiToken($this->params['ctoken'])->isAdministrator()
                    && Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getApiToken() !== $this->params['ctoken']
                ) break;

                if ($this->service->getSettingsFor('approval_rule') === 'none') {
                    $this->integrateWithTheme(
                        '<h2>' . esc_html__('Approval not required!', 'team-booking') . '</h2>',
                        sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                    );
                    $this->response['output'] = TRUE;
                    break;
                }
                if ($this->params['show_confirm']) {
                    $ok_link = admin_url() . 'admin-ajax.php?action=teambooking_rest_api&operation=approve&id='
                        . $reservation->getDatabaseId()
                        . '&ctoken=' . $this->params['ctoken']
                        . '&checksum=' . $reservation->getToken();
                    $this->showConfirmation(__('Do you want to approve this reservation?', 'team-booking'), $ok_link, $reservation);
                } else {
                    $this->approve_reservation($reservation);
                    $this->integrateWithTheme(
                        '<h2>' . esc_html__('Reservation successfully approved!', 'team-booking') . '</h2>',
                        sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                    );
                    $this->response['output'] = TRUE;
                }
                break;
            /**
             *  THIS IS RESERVED
             *  Decline e-mail or gcal event link
             */
            case 'decline':
                $reservation = $this->scopeReservation();
                if (!$reservation) {
                    if ($this->params['show_confirm']) {
                        $this->integrateWithTheme(
                            '<h2>' . $this->response['reason'] . '</h2>',
                            esc_html__('Decline error', 'team-booking')
                        );
                        $this->response['output'] = TRUE;
                    }
                    break;
                }
                if (!Functions\getCoworkerFromApiToken($this->params['ctoken'])->isAdministrator()
                    && Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getApiToken() !== $this->params['ctoken']
                ) break;
                if ($this->service->getSettingsFor('approval_rule') === 'none') {
                    $this->integrateWithTheme(
                        '<h2>' . esc_html__('Approval not required!', 'team-booking') . '</h2>',
                        sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                    );
                    $this->response['output'] = TRUE;
                    break;
                }
                if ($this->params['show_confirm']) {
                    $ok_link = admin_url() . 'admin-ajax.php?action=teambooking_rest_api&operation=decline&id='
                        . $reservation->getDatabaseId()
                        . '&ctoken=' . $this->params['ctoken']
                        . '&checksum=' . $reservation->getToken();
                    $this->showConfirmation(__('Do you want to decline this reservation?', 'team-booking'), $ok_link, $reservation);
                } else {
                    $who = Functions\getCoworkerFromApiToken($this->params['ctoken'])->getId();
                    $this->cancel_reservation($reservation, $who);
                    $this->integrateWithTheme(
                        '<h2>' . esc_html__('Reservation successfully declined!', 'team-booking') . '</h2>',
                        sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE))
                    );
                    $this->response['output'] = TRUE;
                }
                break;
            case 'test':
                break;
        }

        return TRUE;
    }

    private function POST()
    {
        switch ($this->params['operation']) {
            case 'edit_reservation':
                if (!$this->isRequestComplete(array('reservation', 'auth_token'))) return FALSE;
                if (!$this->isTokenValid(TRUE)) return FALSE;
                // getting rid of the slashes added by WordPress...
                $data = file_get_contents('php://input');
                parse_str($data, $data);
                if (get_magic_quotes_gpc()) {
                    $data = array_map('TeamBooking\Toolkit\stripslashes_deep', $data);
                }
                $this->params['reservation'] = $data['reservation'];
                // ... done!
                $resource_obj = json_decode($this->params['reservation']);
                if (!$this->validateReservationResource($resource_obj)) return FALSE;

                $reservation = Database\Reservations::getById($resource_obj->id);
                if (!$reservation) {
                    $this->response['header'] = '404 Not Found';
                    $this->response['code'] = 404;

                    return FALSE;
                }

                if (isset($resource_obj->customerUserID)) $reservation->setCustomerUserId($resource_obj->customerUserID);
                if (isset($resource_obj->customerTimezone)) $reservation->setCustomerTimezone($resource_obj->customerTimezone);
                if (isset($resource_obj->service->id)) {
                    try {
                        $this->service = Database\Services::get($resource_obj->service->id);
                    } catch (\Exception $e) {
                        $this->response['header'] = '404 Not Found';
                        $this->response['code'] = 404;
                        $this->response['reason'] = 'Resource service not found';

                        return FALSE;
                    }
                    $reservation->setServiceId($resource_obj->service->id);
                    $reservation->setServiceName($this->service->getName());
                    $reservation->setServiceClass($this->service->getClass());
                }
                if (isset($resource_obj->service->location)) $reservation->setServiceLocation($resource_obj->service->location);
                if (isset($resource_obj->datetime->start)) $reservation->setStart($resource_obj->datetime->start);
                if (isset($resource_obj->datetime->end)) $reservation->setEnd($resource_obj->datetime->end);
                if (isset($resource_obj->payment->price)) $reservation->setPrice($resource_obj->payment->price);
                if (isset($resource_obj->payment->priceDiscounted)) $reservation->setPriceDiscounted($resource_obj->payment->priceDiscounted);
                if (isset($resource_obj->payment->isPaid)) $reservation->setPaid((bool)$resource_obj->payment->isPaid);
                if (isset($resource_obj->payment->currency)) $reservation->setCurrencyCode($resource_obj->payment->currency);
                if (isset($resource_obj->payment->gateway)) $reservation->setPaymentGateway($resource_obj->payment->gateway);
                if (isset($resource_obj->status)) {
                    if ($resource_obj->status === 'confirmed') $reservation->setStatusConfirmed();
                    if ($resource_obj->status === 'pending') $reservation->setStatusPending();
                    if ($resource_obj->status === 'cancelled') $reservation->setStatusCancelled();
                    if ($resource_obj->status === 'done') $reservation->setStatusDone();
                    if ($resource_obj->status === 'waiting_approval') $reservation->setStatusWaitingApproval();
                }
                if (isset($resource_obj->tickets)) $reservation->setTickets($resource_obj->tickets);
                if (isset($resource_obj->reminderSent)) $reservation->setEmailReminderSent((bool)$resource_obj->reminderSent);
                if (isset($resource_obj->referer->postID)) $reservation->setPostId($resource_obj->referer->postID);
                if (isset($resource_obj->referer->postID)) $reservation->setPostTitle($resource_obj->referer->postTitle);

                if (isset($resource_obj->formFields) && is_array($resource_obj->formFields)) {
                    $form_fields = array();
                    foreach ($resource_obj->formFields as $formField) {
                        if (!$this->validateFormFieldResource($formField)) return FALSE;
                        $form_field = new \TeamBooking_ReservationFormField();
                        $form_field->setName($formField->name);
                        $form_field->setLabel(Database\Forms::getTitleFromHook($this->service->getForm(), $formField->name));
                        $form_field->setValue($formField->value);
                        $form_field->setServiceId($resource_obj->service->id);
                        $form_field->setPriceIncrement($formField->priceIncrement);
                        $form_fields[] = $form_field;
                    }
                    $reservation->setFormFields($form_fields);
                }

                Database\Reservations::update($reservation);
                Functions\getSettings()->incrementTokenUsage('edit_reservation', $this->params['auth_token']);

                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;

                break;
            case 'delete_reservation':
                if (!$this->isRequestComplete(array('id', 'auth_token'))) return FALSE;
                if (!$this->isTokenValid(TRUE)) return FALSE;
                $reservation = Database\Reservations::getById($this->params['id']);
                $reservation->removeFiles();
                Database\Reservations::delete($this->params['id']);
                Functions\getSettings()->incrementTokenUsage('delete_reservation', $this->params['auth_token']);
                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;
                break;
            case 'cancel_reservation':
                if (!$this->isRequestComplete(array('id', 'auth_token'))) return FALSE;
                if (!$this->isTokenValid(TRUE)) return FALSE;
                $reservation = Database\Reservations::getById($this->params['id']);
                if (!$reservation) {
                    $this->response['header'] = '404 Not Found';
                    $this->response['code'] = 404;

                    return FALSE;
                }
                $this->cancel_reservation($reservation, $this->params['auth_token']);
                Functions\getSettings()->incrementTokenUsage('cancel_reservation', $this->params['auth_token']);
                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;
                break;
            case 'do_reservation':
                if (!$this->isRequestComplete(array('reservation', 'slot', 'auth_token'))) return FALSE;
                if (!$this->isTokenValid(TRUE)) return FALSE;
                // getting rid of the slashes added by WordPress...
                $data = file_get_contents('php://input');
                parse_str($data, $data);
                if (get_magic_quotes_gpc()) {
                    $data = array_map('TeamBooking\Toolkit\stripslashes_deep', $data);
                }
                $this->params['reservation'] = $data['reservation'];
                $this->params['slot'] = $data['slot'];
                // ... done!
                $resource_obj = json_decode($this->params['reservation']);
                $slot_obj = json_decode($this->params['slot']);
                if (!$this->validateReservationResource($resource_obj)) return FALSE;
                if (!$this->validateSlotResource($slot_obj)) return FALSE;
                // Merge service data
                $resource_obj->service->id = $slot_obj->serviceID;
                $reservation_data = \TeamBooking_ReservationData::parseApiResource($resource_obj);
                if (NULL !== $slot_obj->gcalEvent) $reservation_data->setGoogleCalendarEvent($slot_obj->gcalEvent);
                if (NULL !== $slot_obj->gcalParentEvent) $reservation_data->setGoogleCalendarEventParent($slot_obj->gcalParentEvent);
                if (NULL !== $slot_obj->gcalID) $reservation_data->setGoogleCalendarId($slot_obj->gcalID);
                if (NULL !== $slot_obj->start) $reservation_data->setSlotStart($slot_obj->start);
                if (NULL !== $slot_obj->end) $reservation_data->setSlotEnd($slot_obj->end);
                $result = ProcessReservation::submitReservation($reservation_data, FALSE, $this->params['auth_token']);
                if ($result instanceof \TeamBooking_Error) {
                    $this->response['header'] = '409 Conflict';
                    $this->response['code'] = 409;
                    $this->response['reason'] = $result->getMessage();

                    return FALSE;
                }
                Functions\getSettings()->incrementTokenUsage('do_reservation', $this->params['auth_token']);
                $this->response['header'] = '200 OK';
                $this->response['code'] = 200;
                if (is_array($result)) {
                    $this->response['response'] = $result;
                }
                break;
        }

        return TRUE;
    }

    private function cancel_reservation(\TeamBooking_ReservationData $reservation, $who = NULL)
    {
        if (isset($this->params['reason'])) $reservation->setCancellationReason($this->params['reason']);
        $process = new \TeamBooking_Reservation($reservation);
        $updated_record = $process->cancelReservation($this->params['id'], $who);
        if ($updated_record instanceof \TeamBooking_ReservationData) {
            // Everything went fine, let's update the database record
            Database\Reservations::update($updated_record);
            $this->response['header'] = '200 OK';
            $this->response['code'] = 200;
        } elseif ($updated_record instanceof \TeamBooking_Error) {
            // Something goes wrong
            if ($updated_record->getCode() == 7) {
                // The reservation is already cancelled, let's update the database record
                $reservation->setStatusCancelled();
                Database\Reservations::update($reservation);
            }
            $this->response['response'] = $updated_record->getMessage();
        }
    }

    private function approve_reservation(\TeamBooking_ReservationData $reservation)
    {
        $who = Functions\getCoworkerFromApiToken($this->params['ctoken'])->getId();
        $process = new \TeamBooking_Reservation($reservation);
        $updated_record = $process->doReservation($who);
        if ($updated_record instanceof \TeamBooking_ReservationData) {
            $this->response['header'] = '200 OK';
            $this->response['code'] = 200;
            $updated_record->setStatusConfirmed();
            Database\Reservations::update($updated_record);
            // Send e-mail messages
            if ($this->service->getSettingsFor('approval_rule') === 'admin'
                && Functions\getSettings()->getCoworkerData($updated_record->getCoworker())->getCustomEventSettings($updated_record->getServiceId())->getGetDetailsByEmail()
            ) {
                $process->sendNotificationEmailToCoworker();
            }
            if ($this->service->getEmailToCustomer('send') && $updated_record->getCustomerEmail()) {
                $process->sendConfirmationEmail();
            }
        } elseif ($updated_record instanceof \TeamBooking_Error) {
            $this->response['response'] = $updated_record->getMessage();
        } else {
            $this->response['response'] = 'Not approvable (wrong service class)';
        }
    }

    private function showConfirmation($question_text, $ok_link, \TeamBooking_ReservationData $reservation)
    {
        $timezone = new \DateTimeZone(Functions\parse_timezone_aliases($reservation->getCustomerTimezone()));
        $when_value = Functions\dateFormatter($reservation->getStart(), $reservation->isAllDay(), $timezone);
        $service = Database\Services::get($reservation->getServiceId());
        ob_start();
        ?>
        <h2 class="entry-title"><?= esc_html($question_text) ?></h2>
        <p>
            <strong><?= $reservation->getServiceName(TRUE) ?></strong>
            <?php printf(esc_html__('on %1$s at %2$s', 'team-booking'),
                '<strong>' . $when_value->date . '</strong>',
                '<strong>' . $when_value->time . '</strong>'
            ); ?>
            (<?= str_replace('_', ' ', $timezone->getName()) ?>)
        </p>

        <?php if ($this->params['operation'] === 'cancel' && $service->getSettingsFor('cancellation_reason_allowed')) { ?>
        <div>
            <p><?= esc_html__('If yes, please tell us why', 'team-booking') ?></p>
            <p><textarea class="cancellation-reason" style="width: auto"></textarea></p>
        </div>
        <script>
            jQuery(document).ready(function () {
                jQuery('.button.confirm').on('click keypress', function (e) {
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        var reason = jQuery('.cancellation-reason').val();
                        if (reason.length !== 0) {
                            e.preventDefault();
                            jQuery(this).attr("href", jQuery(this).attr("href") + '&reason=' + reason);
                            window.location.href = jQuery(this).attr("href");
                        }
                    }
                })
            });
        </script>
    <?php } ?>
        <div class="buttons">
            <a class="button confirm" href="<?= $ok_link ?>">
                <?= esc_html__('yes', 'team-booking') ?>
            </a>
            <a class="button deny" href="<?= site_url() ?>">
                <?= esc_html__('no', 'team-booking') ?>
            </a>
        </div>
        <?php
        $this->integrateWithTheme(ob_get_clean(), sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE)));
        exit;
    }

    private function showPayment(\TeamBooking_ReservationData $reservation)
    {
        $timezone = new \DateTimeZone(Functions\parse_timezone_aliases($reservation->getCustomerTimezone()));
        $gateways = Functions\getSettings()->getPaymentGatewaysActive();
        wp_enqueue_script('jquery');
        wp_enqueue_script('stripejs', 'https://js.stripe.com/v2/');
        ob_start();
        if ($reservation->isPaid()) {
            echo '<h1>' . esc_html__('This reservation is paid already!') . '</h1>';
        } elseif ($reservation->isCancelled()) {
            echo '<h1>' . esc_html__('This reservation is cancelled.') . '</h1>';
        } elseif ($reservation->getPriceIncremented() == 0) {
            echo '<h1>' . esc_html__('Payment is not due.') . '</h1>';
        } elseif (empty($gateways)) {
            echo '<h1>' . esc_html__('There are currently no payment gateways active.') . '</h1>';
        } else {
            $when_value = Functions\dateFormatter($reservation->getStart(), $reservation->isAllDay(), $timezone);
            ?>
            <h2 class="entry-title"><?= esc_html__('Please choose a payment method') ?></h2>
            <p><?= esc_html__('Reservation details', 'team-booking') ?></p>
            <div>
                <table>
                    <thead>
                    <tr>
                        <th>
                            <?= esc_html__('Service', 'team-booking') ?>
                        </th>
                        <th>
                            <?= esc_html__('When', 'team-booking') ?>
                        </th>
                        <th>
                            <?= esc_html__('Amount', 'team-booking') ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?= $reservation->getServiceName(TRUE) ?>
                        </td>
                        <td>
                            <p>
                                <?php printf(esc_html__('on %1$s at %2$s', 'team-booking'),
                                    '<strong>' . $when_value->date . '</strong>',
                                    '<strong>' . $when_value->time . '</strong>'
                                ); ?>
                                (<?= str_replace('_', ' ', $timezone->getName()) ?>)
                            </p>
                        </td>
                        <td>
                            <?= Functions\currencyCodeToSymbol($reservation->getPriceIncremented(), $reservation->getCurrencyCode()) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="buttons">
                <?php
                foreach ($gateways as $gateway) {
                    echo $gateway->getPayButton();
                }
                ?>
            </div>
            <script>
                jQuery(document).ready(function () {
                    jQuery('.tbk-api-wrapper').on('click keydown', '.tbk-pay-button', function (e) {
                        if (e.which == 13 || e.which == 32 || e.which == 1) {
                            var clicked_button = jQuery(this);
                            if (clicked_button.hasClass('tbk-loading')) {
                                return;
                            }
                            clicked_button.addClass('tbk-loading');
                            var gateway_id = clicked_button.data('gateway');
                            var offsite = clicked_button.data('offsite');
                            var reservation_checksum = '<?= $reservation->getToken() ?>';
                            var reservation_database_id = <?= $reservation->getDatabaseId() ?>;
                            jQuery.post(
                                '<?= admin_url('admin-ajax.php') ?>',
                                {
                                    action                 : 'tb_submit_payment',
                                    reservation_checksum   : reservation_checksum,
                                    gateway_id             : gateway_id,
                                    reservation_database_id: reservation_database_id
                                },
                                function (response) {
                                    if (offsite == true || response.status == 'redirect') {
                                        window.location.href = response.redirect;
                                    } else {
                                        var content = jQuery(jQuery.parseHTML(response.content, document, true));
                                        clicked_button.closest('.buttons').html(content);
                                    }
                                }
                            );
                        }
                    });
                });
            </script>
            <?php
        }
        $this->integrateWithTheme(ob_get_clean(), sprintf(esc_html__('Reservation %s', 'team-booking'), '#' . $reservation->getDatabaseId(TRUE)));
        exit;
    }

    private function validateSlotResource($resource_obj)
    {
        $this->response['header'] = '400 Bad Request';
        $this->response['code'] = 400;
        $this->response['reason'] = 'invalid resource';
        if (!$resource_obj) return FALSE;
        if (isset($resource_obj->type)) {
            if ($resource_obj->type !== 'slot') return FALSE;
        } else {
            return FALSE;
        }
        if (isset($resource_obj->coworkerID)) {
            if (filter_var($resource_obj->coworkerID, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }
        if (isset($resource_obj->tickets)) {
            if (filter_var($resource_obj->tickets, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }

        // defaults
        $this->response['header'] = '403 Forbidden';
        $this->response['code'] = 403;
        unset($this->response['reason']);

        return TRUE;
    }

    private function validateFormFieldResource($resource_obj)
    {
        $this->response['header'] = '400 Bad Request';
        $this->response['code'] = 400;
        $this->response['reason'] = 'invalid resource';
        if (!$resource_obj) return FALSE;
        if (isset($resource_obj->type)) {
            if ($resource_obj->type !== 'form_field') return FALSE;
        } else {
            return FALSE;
        }

        if (!isset($resource_obj->name)) return FALSE;
        if (!isset($resource_obj->value)) return FALSE;
        if (!isset($resource_obj->priceIncrement)) return FALSE;
        if (filter_var($resource_obj->priceIncrement, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 0)
            )) === FALSE
        ) {
            return FALSE;
        }

        // defaults
        $this->response['header'] = '403 Forbidden';
        $this->response['code'] = 403;
        unset($this->response['reason']);

        return TRUE;
    }

    private function validateReservationResource($resource_obj)
    {
        $this->response['header'] = '400 Bad Request';
        $this->response['code'] = 400;
        $this->response['reason'] = 'invalid resource';
        if (!$resource_obj) return FALSE;
        if (isset($resource_obj->type)) {
            if ($resource_obj->type !== 'reservation') return FALSE;
        } else {
            return FALSE;
        }
        if (isset($resource_obj->customerUserID)) {
            if (filter_var($resource_obj->customerUserID, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }
        if (isset($resource_obj->customerTimezone) && !in_array($resource_obj->customerTimezone, \DateTimeZone::listIdentifiers())) {
            return FALSE;
        }
        if (isset($resource_obj->datetime->start)) {
            if (filter_var($resource_obj->datetime->start, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }
        if (isset($resource_obj->datetime->end)) {
            if (filter_var($resource_obj->datetime->end, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }
        if (isset($resource_obj->payment->amount)) {
            if (filter_var($resource_obj->payment->amount, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }
        if (isset($resource_obj->tickets)) {
            if (filter_var($resource_obj->tickets, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }
        if (isset($resource_obj->postID)) {
            if (filter_var($resource_obj->postID, FILTER_VALIDATE_INT, array(
                    'options' => array('min_range' => 0)
                )) === FALSE
            ) {
                return FALSE;
            }
        }

        // defaults
        $this->response['header'] = '403 Forbidden';
        $this->response['code'] = 403;
        unset($this->response['reason']);

        return TRUE;
    }

    private function isRequestComplete(array $params)
    {
        foreach ($params as $param) {
            if (!isset($this->params[ $param ])) {
                $this->response['header'] = '400 Bad Request';
                $this->response['code'] = 400;
                $this->response['reason'] = 'incomplete request';

                return FALSE;
            }
        }

        return TRUE;
    }

    private function isTokenValid($write = FALSE)
    {
        $tokens = Functions\getSettings()->getTokens();
        if (isset($tokens[ $this->params['auth_token'] ])) {
            if ($write && !$tokens[ $this->params['auth_token'] ]['write']) {
                $this->response['reason'] = 'read-only token';

                return FALSE;
            }

            return TRUE;
        } else {
            $this->response['reason'] = 'invalid token';

            return FALSE;
        }
    }

    /**
     * @param        $content
     * @param string $page_title
     * @param bool   $is404
     */
    private function integrateWithTheme($content, $page_title = '', $is404 = FALSE)
    {
        wp_enqueue_style('tb-api-css', TEAMBOOKING_URL . 'css/api-response.css', array(), filemtime(TEAMBOOKING_PATH . 'css/api-response.css'));
        $template_file = Functions\getSettings()->getTemplateVPages();
        if (locate_template($template_file) !== '') {
            if (empty($page_title)) {
                $page_title = get_bloginfo('name');
            }
            Functions\registerFrontendResources();
            Functions\enqueueFrontendResources();
            // have to create a dummy post as otherwise many templates
            // don't call the_content filter
            global $wp, $wp_query;
            if ($is404) {
                $wp_query->set_404();
                add_action('wp_title', function () {
                    return '404: Not Found';
                }, 9999);
                status_header(404);
                nocache_headers();
                require get_404_template();
                exit;
            }
            $p = new \stdClass;
            $p->ID = 1;
            $p->post_author = 1;
            $p->post_date = current_time('mysql');
            $p->post_date_gmt = current_time('mysql', $gmt = 1);
            $p->post_content = $content;
            $p->post_title = $page_title;
            $p->post_excerpt = '';
            $p->post_status = 'publish';
            $p->ping_status = 'closed';
            $p->post_password = '';
            $p->post_name = 'tbk-virtual-page'; // slug
            $p->to_ping = '';
            $p->pinged = '';
            $p->modified = $p->post_date;
            $p->modified_gmt = $p->post_date_gmt;
            $p->post_content_filtered = '';
            $p->post_parent = 0;
            $p->guid = get_home_url('/' . $p->post_name);
            $p->menu_order = 0;
            $p->post_type = 'page';
            $p->post_mime_type = '';
            $p->comment_status = 'closed';
            $p->comment_count = 0;
            $p->filter = 'raw';
            $p->ancestors = array();
            $p = new \WP_Post($p);
            wp_cache_add($p->ID, $p, 'posts');
            $wp_query->is_page = TRUE;
            $wp_query->is_singular = TRUE;
            $wp_query->is_single = FALSE;
            $wp_query->is_home = FALSE;
            $wp_query->is_archive = FALSE;
            $wp_query->is_attachment = FALSE;
            $wp_query->is_category = FALSE;
            $wp_query->is_tag = FALSE;
            $wp_query->is_tax = FALSE;
            $wp_query->is_author = FALSE;
            $wp_query->is_date = FALSE;
            $wp_query->is_year = FALSE;
            $wp_query->is_month = FALSE;
            $wp_query->is_day = FALSE;
            $wp_query->is_time = FALSE;
            $wp_query->is_search = FALSE;
            $wp_query->is_feed = FALSE;
            $wp_query->is_comment_feed = FALSE;
            $wp_query->is_trackback = FALSE;
            $wp_query->is_home = FALSE;
            $wp_query->is_embed = FALSE;
            $wp_query->is_404 = FALSE;
            $wp_query->is_paged = FALSE;
            $wp_query->is_admin = FALSE;
            $wp_query->is_preview = FALSE;
            $wp_query->is_robots = FALSE;
            $wp_query->is_posts_page = FALSE;
            $wp_query->is_post_type_archive = FALSE;
            unset($wp_query->query['error']);
            $wp->query = array();
            $wp_query->query_vars['error'] = '';
            $wp_query->current_post = -1;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->comment_count = 0;
            $wp_query->current_comment = NULL;
            $wp_query->post = $p;
            $wp_query->posts = array($p);
            $wp_query->queried_object = $p;
            $wp_query->queried_object_id = $p->ID;
            $wp_query->max_num_pages = 1;
            $GLOBALS['wp_query'] = $wp_query;
            $wp->register_globals();
            add_filter('the_content', function ($old_content) use ($content) {
                if (is_main_query() && in_the_loop()) {
                    return Actions\api_page_main_content('<div class="tbk-api-wrapper">' . $content . '</div>');
                }

                return $old_content;
            });
            get_template_part(substr($template_file, 0, -4));
        } else {
            $title = '<h1>' . $page_title . '</h1>';
            get_header();
            echo Actions\api_page_main_content('<div class="tbk-api-wrapper">' . $title . $content . '</div>');
            get_footer();
        }
    }

}
