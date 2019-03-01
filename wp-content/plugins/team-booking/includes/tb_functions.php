<?php

namespace TeamBooking\Functions;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts\FormElement,
    TeamBooking\Abstracts\Service,
    TeamBooking\Cache,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Slot,
    TeamBooking\Cart;

include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_ajax_calls.php';
include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_file_generators.php';
include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_toolkit.php';
include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_actions.php';

/**
 * Returns the plugin settings object
 *
 * @return \TeamBookingSettings Plugin settings object
 */
function getSettings()
{
    if (NULL === Cache::get('settings')) {
        Cache::add(get_option('team_booking'), 'settings');
    }

    return Cache::get('settings');
}

/**
 * Get the Coworker ID list
 *
 * @return array Coworkers ID list
 */
function getCoworkersIdList()
{
    $all_users = get_users();
    $coworkers = array();
    foreach ($all_users as $user) {
        if ($user->has_cap('tb_can_sync_calendar')) {
            $coworkers[] = $user->ID;
        }
    }
    if (NULL !== $coworkers) {
        return array_values($coworkers);
    } else {
        return array();
    }
}

/**
 * Get the authorized Coworker ID list
 *
 * @return array Coworkers ID list
 */
function getAuthCoworkersIdList()
{
    $coworkers_data = getSettings()->getCoworkersData();
    $list = array();
    if (NULL !== $coworkers_data) {
        foreach ($coworkers_data as $coworker_id => $data) {
            $token = $data->getAccessToken();
            if (!empty($token)) {
                $list[] = $coworker_id;
            }
        }
    }

    return $list;
}

/**
 * Get the authorized Coworkers list
 *
 * @return array
 */
function getAuthCoworkersList()
{
    $results = array();
    $coworkers_data = getSettings()->getCoworkersData();
    foreach ($coworkers_data as $id => $data) {
        /* @var $data \TeamBookingCoworker */
        if ($data->getAccessToken()) {
            $results[ $id ]['name'] = $data->getDisplayName();
            $results[ $id ]['email'] = $data->getEmail();
            $results[ $id ]['roles'] = $data->getRoles();
            $results[ $id ]['tokens'] = $data->getAccessToken();
            $results[ $id ]['calendars'] = $data->getCalendars();
            $results[ $id ]['auth_account'] = $data->getAuthAccount();
        }
    }

    return $results;
}

/**
 * Get the Coworkers list
 *
 * @return array
 */

function getAllCoworkersList()
{
    $results = array();
    // Get the ids of users with TB capability right now
    $ids = getCoworkersIdList();
    // Get the eventual list of coworkers already present
    $present_data = getSettings()->getCoworkersData();
    foreach ($ids as $id) {
        if (isset($present_data[ $id ])) {
            $coworker = $present_data[ $id ];
            unset($present_data[ $id ]);
        } else {
            $coworker = new \TeamBookingCoworker($id);
        }
        $results[ $id ]['name'] = $coworker->getDisplayName();
        $results[ $id ]['email'] = $coworker->getEmail();
        $results[ $id ]['calendars'] = $coworker->getCalendars();
        $results[ $id ]['roles'] = $coworker->getRoles();
        $results[ $id ]['services_allowed'] = $coworker->getAllowedServices();
        if (isset(json_decode($coworker->getAccessToken())->refresh_token)) {
            $results[ $id ]['token'] = 'refresh';
        } elseif (isset(json_decode($coworker->getAccessToken())->access_token)) {
            $results[ $id ]['token'] = 'access';
        } else {
            $results[ $id ]['token'] = '';
        }
    }
    // There are coworkers without TB capability left?
    if (!empty($present_data)) {
        foreach ($present_data as $id => $data) {
            if (get_userdata($id) == FALSE) {
                // User not exists anymore
                $settings = getSettings();
                $settings->dropCoworkerData($id);
                $settings->save();
                continue;
            }
            $results[ $id ]['name'] = $data->getDisplayName();
            $results[ $id ]['email'] = $data->getEmail();
            $results[ $id ]['calendar'] = $data->getCalendars();
            $results[ $id ]['roles'] = $data->getRoles();
            $results[ $id ]['services_allowed'] = $data->getAllowedServices();
            if (isset(json_decode($data->getAccessToken())->refresh_token)) {
                $results[ $id ]['token'] = 'refresh';
            } elseif (isset(json_decode($data->getAccessToken())->access_token)) {
                $results[ $id ]['token'] = 'access';
            } else {
                $results[ $id ]['token'] = '';
            }
            $results[ $id ]['allowed_no_more'] = TRUE;
        }
    }

    return $results;
}

/**
 * Renders the field validation modal in service settings tab
 *
 * @param FormElement $field
 * @param string      $fieldname
 *
 * @return string
 */
function getValidationModal(FormElement $field, $fieldname)
{
    $validation_settings = $field->getData('validation');
    ob_start();
    ?>
    <div class="ui small modal" id="tb-field-regex-modal-<?= $field->getHook() ?>">
        <i class="close tb-icon"></i>

        <div class="content" style="display:block;width: initial;">
            <div style="font-style: italic;font-weight: 300;">
                <?= __('Validation rule', 'team-booking') ?>
            </div>
            <select style="margin-bottom:10px;width:99%;" name="<?= $fieldname ?>[<?= $field->getHook() ?>_validation]">
                <option
                        value="none" <?php selected('none', $validation_settings['validate'], TRUE); ?>><?= __('No validation', 'team-booking') ?></option>
                <option
                        value="email" <?php selected('email', $validation_settings['validate'], TRUE); ?>><?= __('Email', 'team-booking') ?></option>
                <option
                        value="alphanumeric" <?php selected('alphanumeric', $validation_settings['validate'], TRUE); ?>><?= __('Alphanumeric', 'team-booking') ?></option>
                <option
                        value="phone" <?php selected('phone', $validation_settings['validate'], TRUE); ?>><?= __('Phone number (US only)', 'team-booking') ?></option>
                <option
                        value="custom" <?php selected('custom', $validation_settings['validate'], TRUE); ?>><?= __('Custom', 'team-booking') ?></option>
            </select>

            <div style="font-style: italic;font-weight: 300;">
                <?= __('Custom validation regex (works if "custom" is selected)', 'team-booking') ?>
            </div>
            <input type="text" class="large-text" style="margin-bottom:10px;"
                   name="<?= $fieldname ?>[<?= $field->getHook() ?>_regex]"
                   value="<?= $validation_settings['validation_regex']['custom'] ?>">

            <div class="ui button tb-close" style="height:auto">
                <?= __('Close', 'team-booking') ?>
            </div>
        </div>
    </div>
    <script>
        jQuery('#tb-field-regex-open-<?= $field->getHook() ?>').on('click', function (e) {
            e.preventDefault();
            jQuery('#tb-field-regex-modal-<?= $field->getHook() ?>')
                .uiModal({detachable: false})
                .uiModal('attach events', '.tb-close', 'hide')
                .uiModal('show')
            ;
        });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Checks if there is a booking with that ID already
 *
 * @param string $id
 *
 * @return boolean TRUE if exists
 */
function checkServiceIdExistance($id)
{
    try {
        Database\Services::get($id);

        return TRUE;
    } catch (\Exception $e) {
        return FALSE;
    }
}

/**
 * Checks if there is a booking with that name already
 * or if the service's name contains invalid strings
 *
 * @param string $name
 *
 * @return bool
 * @throws \Exception
 */
function checkServiceNameExistance($name)
{
    $services = Database\Services::get();
    $response = FALSE;
    foreach ($services as $service) {
        if (strpos('>>', $name) !== FALSE
            || strpos('||', $name) !== FALSE
            || strtolower($service->getName()) === strtolower($name)
        ) {
            $response = TRUE;
            break;
        }
    }

    return $response;
}

/**
 * Returns appropriate text color
 *
 * @param string     $color (hexadecimal color value)
 * @param bool|FALSE $prefer_white
 *
 * @return string
 */
function getRightTextColor($color, $prefer_white = FALSE)
{
    $brightness_limit = $prefer_white ? 185 : 145;
    $rgb = Toolkit\hex2RGB($color);
    if (!$rgb) {
        return 'inherit';
    }
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114);
    if ($brightness < $brightness_limit) {
        return '#FFFFFF';
    } else {
        return '#414141';
    }
}

/**
 * Returns appropriate hover color
 *
 * @param string $color (hexadecimal color value)
 *
 * @return string
 */
function getRightHoverColor($color)
{
    $rgb = Toolkit\hex2RGB($color);
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114);
    if ($brightness < 145) {
        return 'rgba(255, 255, 255, 0.15)';
    } else {
        return 'rgba(0, 0, 0, 0.15)';
    }
}

/**
 * Returns appropriate background label color
 *
 * @param string $color (hexadecimal color value)
 *
 * @return string
 */
function getRightBackgroundColor($color)
{
    $rgb = Toolkit\hex2RGB($color);
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114);
    if ($brightness < 145) {
        return '#2B2B2B';
    } else {
        return '#F4F4F4';
    }
}

function currencyCodeToSymbol($amount = NULL, $cc = NULL, $keep_code = FALSE, $only_position = FALSE)
{
    if (NULL === $cc) {
        $cc = getSettings()->getCurrencyCode();
    }
    $currencies = Toolkit\getCurrencies();
    $decimals = 2;
    if (!isset($currencies[ $cc ])) {
        $symbol = "$";
        $position = 'before';
    } else {
        if ($keep_code) {
            $symbol = $cc;
        } else {
            $symbol = $currencies[ $cc ]['symbol'];
        }
        $position = $currencies[ $cc ]['format'];
        $decimals = $currencies[ $cc ]['decimal'] ? 2 : 0;
    }

    if ($only_position) {
        return $position;
    }

    if (NULL === $amount) {
        return $symbol;
    } else {
        if ($position === 'after') {
            return priceFormat($amount, $decimals) . $symbol;
        } else {
            return $symbol . priceFormat($amount, $decimals);
        }
    }
}

function priceFormat($value, $decimals = NULL)
{
    if (NULL === $decimals) {
        $currencies = Toolkit\getCurrencies();
        $decimals = $currencies[ getSettings()->getCurrencyCode() ]['decimal'] === TRUE ? 2 : 0;
    }

    return number_format((float)$value, $decimals);
}

function cleanReservations($all = FALSE, $just_check = FALSE, $timeout_override = FALSE)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'teambooking_reservations';
    if ($timeout_override) {
        $timeout = $timeout_override;
    } else {
        $timeout = getSettings()->getDatabaseReservationTimeout();
    }
    if ($all) {
        $wpdb->query("TRUNCATE TABLE $table_name");
    } else {
        $now = Toolkit\getNowInSecondsUTC(); // UTC, seconds
        $reservations = Database\Reservations::getAll();
        $count = 0;
        foreach ($reservations as $id => $reservation) {
            if ($reservation instanceof \TeamBooking_ReservationData) {
                $age = $now - $reservation->getStart(); // UTC, seconds
                if ($age > $timeout && $timeout) {
                    if (!$just_check) {
                        $reservation->removeFiles();
                        Database\Reservations::delete($id);
                    } else {
                        $count++;
                    }
                }
            }
        }
        if ($count) {
            return $count;
        } else {
            return FALSE;
        }
    }
}

function cleanRoutine()
{
    cleanReservations();
    Cart::clean_sessions();

    return TRUE;
}

function isReservationTimedOut(\TeamBooking_ReservationData $reservation)
{
    $timeout = getSettings()->getMaxPendingTime(); // seconds
    if ($timeout <= 0) return FALSE;
    $now = Toolkit\getNowInSecondsUTC(); // UTC, seconds
    $reservation_time = $reservation->getCreationInstant(); // UTC, seconds
    if (($now - $reservation_time) > $timeout) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function isReservationPastInTime(\TeamBooking_ReservationData $reservation)
{
    $now = Toolkit\getNowInSecondsUTC(); // UTC, seconds
    $reservation_time = $reservation->getStart(); // UTC, seconds
    if (!$reservation_time) return FALSE; // it is unscheduled
    if ($now > $reservation_time) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Handler for uploaded files
 *
 * Returns associative array:
 * $movefile[file] The local path to the uploaded file.
 * $movefile[url] The public URL for the uploaded file.
 * $movefile[type] The MIME type.
 *
 * @param $file
 *
 * @return array|bool
 */
function handleFileUpload($file)
{
    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $upload_overrides = array('test_form' => FALSE);
    $movefile = wp_handle_upload($file, $upload_overrides);
    if ($movefile) {
        return $movefile;
    } else {
        return FALSE;
    }
}

function date_i18n_tb($format, $value)
{
    $prev = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $return = date_i18n($format, $value);
    date_default_timezone_set($prev);

    return $return;
}

function strtotime_tb($value)
{
    $prev = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $return = strtotime($value);
    date_default_timezone_set($prev);

    return $return;
}

/**
 * @param array $file_reference
 *
 * @return array
 */
function safe_file_copy(array $file_reference)
{
    $file_name = basename($file_reference['file']);
    $dir = wp_upload_dir();
    $new_name = wp_unique_filename($dir['path'], $file_name);
    $new_path = $dir['path'] . DIRECTORY_SEPARATOR . $new_name;
    $new_url = $dir['url'] . '/' . $new_name;
    copy($file_reference['file'], $new_path);
    $file_reference['file'] = $new_path;
    $file_reference['url'] = $new_url;

    return $file_reference;
}

/**
 * @param  int                      $unix_time
 * @param bool                      $all_day
 * @param null|string|\DateTimeZone $timezone
 *
 * @return \stdClass
 */
function dateFormatter($unix_time, $all_day = FALSE, $timezone = NULL)
{
    if (NULL === $timezone) $timezone = Toolkit\getTimezone();
    if (is_string($timezone)) $timezone = Toolkit\getTimezone($timezone);
    $date_time_object = \DateTime::createFromFormat('U', $unix_time);
    $date_time_object->setTimezone($timezone);
    $time = $all_day ? $date_time_object->getTimestamp() : ($date_time_object->getTimestamp() + $date_time_object->getOffset());
    $return = new \stdClass();
    $return->date = date_i18n_tb(get_option('date_format'), $time);
    $return->time = date_i18n_tb(get_option('time_format'), $time);

    return $return;
}

/**
 * @param null|string $service_id
 *
 * @return bool
 */
function isThereOneCouponAtLeast($service_id = NULL)
{
    if (NULL === $service_id) {
        $promotions = Database\Promotions::getByClass('coupon', TRUE, TRUE);
    } else {
        $promotions = Database\Promotions::getByClassAndService('coupon', $service_id, TRUE, TRUE);
    }
    if (empty($promotions)) {
        return FALSE;
    }
    foreach ($promotions as $db_id => $promotion) {
        if ($promotion->getLimit() > 0) {
            $used_discounts = count_used_discounts();
            if (isset($used_discounts[ $db_id ]) && $used_discounts[ $db_id ] >= $promotion->getLimit()) {
                unset($promotions[ $db_id ]);
            }
        }
    }

    return !empty($promotions);
}

/**
 * @param Service $service
 * @param         $code
 * @param         $base_price_override
 * @param         $discounted_price
 *
 *
 * @return array|boolean
 */
function getPriceWithCoupon($service, $code, $base_price_override = NULL, $discounted_price = NULL)
{
    $base_price = NULL === $base_price_override ? $service->getPrice() : $base_price_override;
    $promotions = Database\Promotions::getByClassAndService('coupon', $service->getId(), TRUE, TRUE);
    foreach ($promotions as $id => $promotion) {
        /** @var $promotion \TeamBooking_Promotions_Coupon */
        if ($promotion->validateCode($code)) {
            if (count($promotion->getList()) > 0) {
                if (check_used_listed_coupon(Database\Reservations::getByServices($promotion->getServices()), $id, $code)) {
                    return FALSE;
                }
                $coupons_in_cart = Cart::getAllSessionsCoupons(TRUE);
                foreach ($coupons_in_cart as $coupon_in_cart) {
                    if ($coupon_in_cart['code'] === $code && $coupon_in_cart['promotion_id'] === $id) return FALSE;
                }

            } elseif ($promotion->getLimit() > 0) {
                $used_discounts = count_used_discounts();
                // conting the coupons that are in sessions to avoid limit exceeding
                $coupons_in_cart = Cart::getAllSessionsCoupons(TRUE);
                $count = isset($used_discounts[ $id ]) ? $used_discounts[ $id ] : 0;
                foreach ($coupons_in_cart as $coupon_in_cart) {
                    if ($coupon_in_cart['code'] === $code && $coupon_in_cart['promotion_id'] === $id) $count++;
                }
                if ($count >= $promotion->getLimit()) {
                    return FALSE;
                }
            }
            $service_price = NULL === $discounted_price ? $base_price_override : $discounted_price;
            $discount_used = array(
                'name'   => $promotion->getName(),
                'value'  => $promotion->getDiscount(),
                'type'   => $promotion->getDiscountType(),
                'id'     => $id,
                'coupon' => $code
            );
            if ($promotion->getDiscountType() === 'percentage') {
                $value = $service_price - ($base_price - Toolkit\applyPercentage($base_price, $promotion->getDiscount()));
            } else {
                $value = $service_price - $promotion->getDiscount();
            }
            if ($value < 0) {
                $value = 0;
            }

            return array(
                'discounted' => $value,
                'promotion'  => $discount_used
            );
        }
    }

    return FALSE; // nothing to return? The coupon is invalid or expired
}

/**
 * Only for campaigns!
 *
 * @param Service $service
 * @param null    $slot_start
 * @param null    $slot_end
 * @param null    $base_price_override
 * @param Slot    $slot
 *
 * @return int|mixed
 */
function getDiscountedPrice($service, $slot_start = NULL, $slot_end = NULL, $base_price_override = NULL, $slot = NULL)
{
    $promotions = Database\Promotions::getByService($service->getId(), TRUE, TRUE);
    $base_price = NULL === $base_price_override ? $service->getPrice() : $base_price_override;
    $discounted_price = $base_price;
    foreach ($promotions as $db_id => $promotion) {
        if (!($promotion instanceof \TeamBooking_Promotions_Campaign)) {
            continue;
        }
        if ($promotion->getLimit() > 0) {
            $used_discounts = count_used_discounts();
            // conting the slots that are in cart to avoid limit exceeding
            $count = isset($used_discounts[ $db_id ]) ? $used_discounts[ $db_id ] : 0;
            if (NULL !== $slot) {
                $slots_in_cart = Cart::getAllSessionsSlots();
                foreach ($slots_in_cart as $slot_id => $slot_in_cart) {
                    if ($slot_in_cart->isPromotionApplied($promotion->getName())) $count++;
                }
            }
            if ($count >= $promotion->getLimit()) {
                continue;
            }
        }
        if ($service->getClass() !== 'unscheduled') {
            if (NULL !== $promotion->getStartBound()) {
                if (NULL === $slot_start || NULL === $slot_end) continue;
                $time_obj = \DateTime::createFromFormat('U', $promotion->getStartBound());
                $time_obj->setTimezone(Toolkit\getTimezone());
                if ($slot_start < $time_obj->getTimestamp() - $time_obj->getOffset()) {
                    continue;
                }
            }
            if (NULL !== $promotion->getEndBound()) {
                if (NULL === $slot_start || NULL === $slot_end) continue;
                $time_obj = \DateTime::createFromFormat('U', $promotion->getEndBound());
                $time_obj->setTimezone(Toolkit\getTimezone());
                if ($slot_end > $time_obj->getTimestamp() - $time_obj->getOffset()) {
                    continue;
                }
            }
        }

        if ($promotion->getDiscountType() === 'percentage') {
            $discount = $discounted_price - Toolkit\applyPercentage($base_price, $promotion->getDiscount());
        } else {
            $discount = $promotion->getDiscount();
        }
        // promotions is applied
        if (NULL !== $slot) {
            $slot->addPromotionApplied($promotion->getName(), $discount);
        }
        $discounted_price -= $discount;
    }
    if ($discounted_price < 0) {
        $discounted_price = 0;
    }

    return $discounted_price;
}

/**
 * Check if the (current|given) user is
 * either an Admin or a Coworker
 *
 * @param null $user_id
 *
 * @return bool
 */
function isAdminOrCoworker($user_id = NULL)
{
    if (NULL === $user_id) {
        if (current_user_can('manage_options')
            || current_user_can('tb_can_sync_calendar')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return FALSE;
        }
        if ($user->has_cap('manage_options')
            || $user->has_cap('tb_can_sync_calendar')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

/**
 * Check if the (current|given) user is an Admin
 *
 * @param null $user_id
 *
 * @return bool
 */
function isAdmin($user_id = NULL)
{
    if (NULL === $user_id) {
        return current_user_can('manage_options');
    } else {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return FALSE;
        }

        return $user->has_cap('manage_options');
    }
}

/**
 * Sends the reminder e-mail messages to customers
 *
 * @return bool
 */
function tbSendEmailReminder()
{
    $reservations = Database\Reservations::getAll();
    $local_time = Toolkit\getNowInSecondsUTC();
    foreach ($reservations as $db_id => $data) {
        if (!$data->isConfirmed()) continue;
        if ($data->getServiceClass() === 'unscheduled') continue;
        try {
            $service = Database\Services::get($data->getServiceId());
        } catch (\Exception $e) {
            continue;
        }
        if ($service->getEmailReminder('send')) {
            $reference_time = $data->getStart(); // Unix
            $creation_time = $data->getCreationInstant(); // Unix
            $timeframe = $service->getEmailReminder('days_before') * DAY_IN_SECONDS; // Seconds
            if ($reference_time - $local_time < $timeframe
                && $reference_time - $local_time > 0
                && !$data->isEmailReminderSent()
            ) {
                if ($reference_time - $creation_time < DAY_IN_SECONDS
                    && $service->getEmailToCustomer('send')
                ) {
                    continue; // too early
                }
                \TeamBooking\Actions\email_send_begin($service, $data, 'reminder');
                $email = new \TeamBooking\EmailHandler();
                $subject = Toolkit\findAndReplaceHooks(
                    \TeamBooking\Actions\email_before_hook_replacement(
                        $service->getEmailReminder('subject'),
                        $data,
                        'reminder_subject'
                    ),
                    $data->getHooksArray(TRUE)
                );
                $body = Toolkit\findAndReplaceHooks(
                    \TeamBooking\Actions\email_before_hook_replacement(
                        $service->getEmailReminder('body'),
                        $data,
                        'reminder_body'
                    ),
                    $data->getHooksArray(TRUE)
                );
                $email->setSubject($subject);
                $email->setBody($body);
                if ($service->getEmailReminder('from') === 'coworker') {
                    $coworker = getSettings()->getCoworkerData($data->getCoworker());
                    $email->setFrom($coworker->getEmail(), $coworker->getDisplayName());
                } else {
                    $email->setFrom($service->getEmailToAdmin('to'), get_option('blogname'));
                }
                $email->setTo($data->getCustomerEmail());
                $email->send();
                $data->setEmailReminderSent(TRUE);
                Database\Reservations::update($data);
                \TeamBooking\Actions\email_send_end($service, $data, 'reminder');
            }
        }
    }

    return TRUE;
}

/**
 * Sends the reminder e-mail message to customer (manual)
 *
 * @param $reservation_id
 *
 * @return bool
 */
function sendEmailReminderManually($reservation_id)
{
    $reservation = Database\Reservations::getById($reservation_id);
    if (!$reservation->isEmailReminderSent()
        && $reservation->isConfirmed()
    ) {
        try {
            $service = Database\Services::get($reservation->getServiceId());
        } catch (\Exception $e) {
            return FALSE;
        }
        if ($service->getEmailReminder('send')) {
            if (!isReservationPastInTime($reservation)) {
                $email = new \TeamBooking\EmailHandler();
                $subject = Toolkit\findAndReplaceHooks(
                    \TeamBooking\Actions\email_before_hook_replacement(
                        $service->getEmailReminder('subject'),
                        $reservation,
                        'reminder_subject'
                    ),
                    $reservation->getHooksArray(TRUE)
                );
                $body = Toolkit\findAndReplaceHooks(
                    \TeamBooking\Actions\email_before_hook_replacement(
                        $service->getEmailReminder('body'),
                        $reservation,
                        'reminder_body'
                    ),
                    $reservation->getHooksArray(TRUE)
                );
                $email->setSubject($subject);
                $email->setBody($body);
                $email->setFrom($service->getEmailToAdmin('to'), get_bloginfo('name'));
                $email->setTo($reservation->getCustomerEmail());
                $email->send();
                $reservation->setEmailReminderSent(TRUE);
                Database\Reservations::update($reservation);

                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    return FALSE;
}

/**
 * Retrieve a Coworker by his API token
 *
 * @param $ctoken
 *
 * @return bool|\TeamBookingCoworker
 */
function getCoworkerFromApiToken($ctoken)
{
    foreach (getSettings()->getCoworkersData() as $coworker) {
        if ($coworker->getApiToken() == $ctoken) return $coworker;
    }

    return FALSE;
}

function registerFrontendResources()
{
    if (getSettings()->getFix62dot5()) {
        wp_register_style('semantic-style', TEAMBOOKING_URL . 'libs/semantic/semantic-fix-min.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/semantic/semantic-fix-min.css'));
    } else {
        wp_register_style('semantic-style', TEAMBOOKING_URL . 'libs/semantic/semantic-min.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/semantic/semantic-min.css'));
    }
    wp_register_style('teambooking-style-modal', TEAMBOOKING_URL . 'libs/remodal/remodal.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/remodal/remodal.css'));
    wp_register_style('teambooking-style-modal-theme', TEAMBOOKING_URL . 'libs/remodal/remodal-default-theme.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/remodal/remodal-default-theme.css'));
    wp_register_style(
        'teambooking-style-frontend',
        TEAMBOOKING_URL . (is_rtl() ? 'css/frontend_rtl.css' : 'css/frontend.css'),
        array(),
        filemtime(TEAMBOOKING_PATH . (is_rtl() ? 'css/frontend_rtl.css' : 'css/frontend.css'))
    );
    wp_register_style('teambooking_fonts', '//fonts.googleapis.com/css?family=Oswald|Open+Sans:300,300i,400,700|Source+Serif+Pro:400,700|Merriweather:400,700&amp;subset=latin-ext', array(), '1.0.0');
    wp_register_style('teambooking_fonts_arrows', '//fonts.googleapis.com/css?family=Merriweather:400,700&amp;text=%E2%86%92', array(), '1.0.0');
    wp_enqueue_style('dashicons');
    wp_register_script('tb-base64-decoder', TEAMBOOKING_URL . 'libs/base64/base64decode.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/base64/base64decode.js'), TRUE);
    wp_register_script('tb-modal-script', TEAMBOOKING_URL . 'libs/remodal/remodal.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/remodal/remodal.js'), TRUE);
    wp_register_script('tb-frontend-script', TEAMBOOKING_URL . 'js/frontend.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/frontend.min.js'), TRUE);
    wp_register_script('tb-jquery-actual', TEAMBOOKING_URL . 'js/assets/jquery.actual.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/assets/jquery.actual.min.js'), TRUE);
    // in javascript, object properties are accessed as ajax_object.some_value
    wp_localize_script('tb-frontend-script', 'TB_Ajax', array(
        'ajax_url'  => admin_url('admin-ajax.php'),
        'lang_code' => apply_filters('wpml_current_language', NULL)
    ));

    if (!getSettings()->getGmapsApiKey()) {
        wp_register_script('google-places-script', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places', array('jquery'));
    } else {
        wp_register_script('google-places-script', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . getSettings()->getGmapsApiKey(), array('jquery'));
    }

    wp_register_script('tb-geocomplete-script', TEAMBOOKING_URL . 'js/assets/jquery.geocomplete.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/assets/jquery.geocomplete.min.js'), TRUE);
    wp_register_script('tb-gmap3-script', TEAMBOOKING_URL . 'libs/gmap3/gmap3.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/gmap3/gmap3.min.js'), TRUE);

    if (getSettings()->getPaymentGatewaySettingObject('stripe') instanceof \TeamBooking_PaymentGateways_Stripe_Settings) {
        if (getSettings()->getPaymentGatewaySettingObject('stripe')->isActive()) {
            wp_register_script('tb-jquery-payment', TEAMBOOKING_URL . 'libs/jquery-payment/jquery.payment.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/jquery-payment/jquery.payment.min.js'), TRUE);
            if (getSettings()->getPaymentGatewaySettingObject('stripe')->isLoadLibrary()) {
                wp_register_script('stripejs', 'https://js.stripe.com/v2/');
            }
        }
    }
}

function enqueueFrontendResources()
{
    if (!defined('TBK_COMMON_RSC_ENQUEUED')) {
        if (!getSettings()->getSkipGmapLibs()) {
            wp_enqueue_script('google-places-script');
        }

        if (getSettings()->getPaymentGatewaySettingObject('stripe')->isActive()) {
            wp_enqueue_script('tb-jquery-payment');
            if (getSettings()->getPaymentGatewaySettingObject('stripe')->isLoadLibrary()) {
                wp_enqueue_script('stripejs');
            }
        }

        wp_enqueue_script('tb-base64-decoder');
        wp_enqueue_script('tb-geocomplete-script');
        wp_enqueue_script('tb-gmap3-script');
        wp_enqueue_script('tb-frontend-script');
        wp_enqueue_script('tb-jquery-actual');
        wp_enqueue_style('teambooking_fonts');
        wp_enqueue_style('teambooking_fonts_arrows');
        wp_enqueue_style('semantic-style');
        wp_enqueue_style('teambooking-style-frontend');
        define('TBK_COMMON_RSC_ENQUEUED', TRUE);
    }

    if (!defined('TBK_RESERVATIONS_RSC_ENQUEUED')
        && defined('TBK_RESERV_SHORTCODE_FOUND')
    ) {
        wp_enqueue_script('tb-modal-script');
        wp_enqueue_style('teambooking-style-modal');
        wp_enqueue_style('teambooking-style-modal-theme');
        define('TBK_RESERVATIONS_RSC_ENQUEUED', TRUE);
    }

    if (!defined('TBK_CALENDAR_RSC_ENQUEUED')
        && (defined('TBK_CALENDAR_SHORTCODE_FOUND') || defined('TBK_WIDGET_SHORTCODE_FOUND'))
    ) {
        wp_enqueue_script('semantic-script');
        define('TBK_CALENDAR_RSC_ENQUEUED', TRUE);
    }
}

/**
 * @param null $locale
 *
 * @return array
 */
function continents_list($locale = NULL)
{
    static $mo_loaded = FALSE, $locale_loaded = NULL;
    $continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
    if (!$mo_loaded || $locale !== $locale_loaded) {
        if ($locale) {
            $locale_loaded = $locale;
        } else {
            $locale_loaded = get_locale();
        }
        $mofile = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
        unload_textdomain('continents-cities');
        load_textdomain('continents-cities', $mofile);
        $mo_loaded = TRUE;
    }
    $return = array();
    foreach ($continents as $continent) {
        $return[ $continent ] = translate($continent, 'continents-cities');
    }

    return $return;
}

/**
 * @param string $selected_zone
 * @param bool   $widget
 * @param null   $locale
 *
 * @return mixed
 */
function timezone_list($selected_zone, $widget = FALSE, $locale = NULL)
{
    static $mo_loaded = FALSE, $locale_loaded = NULL;
    $continents = array();
    foreach (getSettings()->getContinentsAllowed() as $continent => $allowed) {
        if ($allowed) $continents[] = $continent;
    }

    if (!$mo_loaded || $locale !== $locale_loaded) {
        if ($locale) {
            $locale_loaded = $locale;
        } else {
            $locale_loaded = get_locale();
        }
        $mofile = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
        unload_textdomain('continents-cities');
        load_textdomain('continents-cities', $mofile);
        $mo_loaded = TRUE;
    }

    $zonen = array();
    foreach (timezone_identifiers_list() as $zone) {
        $zone = explode('/', $zone);
        if (!in_array($zone[0], $continents)) {
            continue;
        }
        $exists = array(
            0 => isset($zone[0]) && $zone[0],
            1 => isset($zone[1]) && $zone[1],
            2 => isset($zone[2]) && $zone[2],
        );
        $exists[3] = ($exists[0] && 'Etc' !== $zone[0]);
        $exists[4] = ($exists[1] && $exists[3]);
        $exists[5] = ($exists[2] && $exists[3]);

        $zonen[] = array(
            'continent'   => $exists[0] ? $zone[0] : '',
            'city'        => $exists[1] ? $zone[1] : '',
            'subcity'     => $exists[2] ? $zone[2] : '',
            't_continent' => $exists[3] ? translate(str_replace('_', ' ', $zone[0]), 'continents-cities') : '',
            't_city'      => $exists[4] ? translate(str_replace('_', ' ', $zone[1]), 'continents-cities') : '',
            't_subcity'   => $exists[5] ? translate(str_replace('_', ' ', $zone[2]), 'continents-cities') : ''
        );
    }

    $structure = array();
    $structure[1] = '<div class="' . ($widget ? 'mini' : 'tiny') . ' tbk-menu">'
        . '<div class="tbk-menu-search"><input type="text"><i class="search tb-icon"></i></div>';
    $i = 2;
    foreach ($zonen as $key => $zone) {
        $value = array($zone['continent']);

        if (empty($zone['city'])) {
            $display = $zone['t_continent'];
        } else {
            $value[] = $zone['city'];
            $display = '<span class="tbk-timezone-list-item-continent">' . $zone['t_continent'] . '</span>' . ' ' . $zone['t_city'];
            if (!empty($zone['subcity'])) {
                $value[] = $zone['subcity'];
                $display .= ' - ' . $zone['t_subcity'];
            }
        }
        $value = implode('/', $value);
        $selected = '';
        if ($value === $selected_zone) {
            $selected = ' tbk-selected';
            $structure[0] = '<span class="tbk-text">' . $display . '</span>';
        }
        $structure[ $i ] = '<div class="tbk-menu-item' . $selected . '" data-timezone="' . esc_attr($value) . '">' . $display . '</div>';
        $i++;
    }
    $selected = '';
    if ('UTC' === $selected_zone) {
        $selected = ' tbk-selected';
        $structure[0] = '<span class="tbk-text">UTC</span>';
    }
    $structure[] = '<div class="tbk-menu-item' . $selected . '" data-timezone="UTC">UTC</div>';

    $selected = '';
    if (in_array('America', $continents)) {
        foreach (get_timezone_aliases() as $timezone_alias => $equivalent) {
            if ($timezone_alias === $selected_zone) {
                $selected = ' tbk-selected';
                $structure[0] = '<span class="tbk-text">' . $timezone_alias . '</span>';
            }
            $structure[] = '<div class="tbk-menu-item' . $selected . '" data-timezone="' . $timezone_alias . '" data-alias="alias">' . $timezone_alias . '</div>';
        }
    }

    $structure[] = '</div>';
    ksort($structure);

    return implode("\n", $structure);
}

/**
 * @param string $string
 *
 * @return string
 */
function parse_timezone_aliases($string)
{
    if (in_array($string, timezone_identifiers_list())) {
        return $string;
    }
    $known_aliases = get_timezone_aliases();
    if (array_key_exists($string, $known_aliases)) {
        return $known_aliases[ $string ];
    }
    if (defined('WP_DEBUG') && WP_DEBUG && NULL !== $string) {
        trigger_error("{$string} is not a valid timezone identifier nor a valid alias. Falling back to UTC.");

        return 'UTC';
    }

    return $string;
}

/**
 * @return array
 */
function get_timezone_aliases()
{
    return array(
        'Hawaii-Aleutian Standard Time (HAST)'              => 'Pacific/Honolulu',
        'Hawaii-Aleutian with Daylight Savings Time (HADT)' => 'US/Aleutian',
        'Alaska Standard Time (AKST)'                       => 'Etc/GMT+9',
        'Alaska with Daylight Savings Time (AKDT)'          => 'America/Anchorage',
        'Pacific Standard Time (PST)'                       => 'America/Dawson_Creek',
        'Pacific with Daylight Savings Time (PDT)'          => 'PST8PDT',
        'Mountain Standard Time (MST)'                      => 'MST',
        'Mountain with Daylight Savings Time (MDT)'         => 'MST7MDT',
        'Central Standard Time (CST)'                       => 'Canada/Saskatchewan',
        'Central with Daylight Savings Time (CDT)'          => 'CST6CDT',
        'Eastern Standard Time (EST)'                       => 'EST',
        'Eastern with Daylight Savings Time (EDT)'          => 'EST5EDT',
        'Atlantic Standard Time (AST)'                      => 'America/Puerto_Rico',
        'Atlantic with Daylight Savings Time (ADT)'         => 'America/Halifax'
    );
}

/**
 * @param \TeamBooking\RenderParameters $parameters
 */
function parse_query_params(\TeamBooking\RenderParameters $parameters)
{
    if (isset($_GET['tbk_timezone'])) {
        try {
            $timezone = new \DateTimeZone(urldecode(parse_timezone_aliases($_GET['tbk_timezone'])));
            $parameters->setTimezone($timezone);
        } catch (\Exception $ex) {
        }
    }
    if (isset($_GET['tbk_month'])) {
        $month = FALSE;
        if ($month === FALSE) $month = Toolkit\validateDateFormat(tb_mb_ucwords(tb_mb_strtolower($_GET['tbk_month'])), 'F', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
        if ($month === FALSE) $month = Toolkit\validateDateFormat(tb_mb_ucwords(tb_mb_strtolower($_GET['tbk_month'])), 'M', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
        if ($month === FALSE) $month = Toolkit\validateDateFormat($_GET['tbk_month'], 'm', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
        if ($month === FALSE) $month = Toolkit\validateDateFormat($_GET['tbk_month'], 'n', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
    }
    if (isset($_GET['tbk_year'])) {
        $year = FALSE;
        if ($year === FALSE) $year = Toolkit\validateDateFormat($_GET['tbk_year'], 'Y', 'Y');
        if ($year !== FALSE) $parameters->setYear($year);
        if ($year === FALSE) $year = Toolkit\validateDateFormat($_GET['tbk_year'], 'y', 'Y');
        if ($year !== FALSE) $parameters->setYear($year);
    }
}

/**
 * @param $string
 *
 * @return mixed
 */
function tb_mb_strtoupper($string)
{
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($string, 'UTF-8');
    }

    return strtoupper($string);
}

/**
 * @param $string
 * @param $to
 * @param $charset
 *
 * @return string
 */
function tb_mb_convert_encoding($string, $to, $charset)
{
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($string, $to, $charset);
    }
    if (defined('WP_DEBUG') && WP_DEBUG) {
        trigger_error('Multibyte Extensions not installed, this may cause issues -- thrown when processing PayPAl IPN --', E_USER_NOTICE);
    }

    return $string;
}

/**
 * @param $string
 *
 * @return mixed
 */
function tb_mb_strtolower($string)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($string, 'UTF-8');
    }

    return strtolower($string);
}

/**
 * @param $string
 *
 * @return mixed
 */
function tb_mb_ucwords($string)
{
    if (function_exists('mb_strtolower')) {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords($string);
}

/**
 * @return array
 */
function count_used_discounts()
{
    if (NULL !== Cache::get('discounts_used_in_all_reservations')) {
        $discount_counts = Cache::get('discounts_used_in_all_reservations');
    } else {
        $discount_counts = array();
        foreach (Database\Reservations::getAll() as $reservation) {
            $discount_array = $reservation->getDiscount();
            foreach ($discount_array as $discount) {
                if (!isset($discount['id'])) {
                    $promotion = Database\Promotions::getByName($discount['name']);
                    reset($promotion);
                    $discount['id'] = key($promotion);
                }
                if (isset($discount_counts[ $discount['id'] ])) {
                    $discount_counts[ $discount['id'] ]++;
                } else {
                    $discount_counts[ $discount['id'] ] = 1;
                }
            }
        }
        Cache::add($discount_counts, 'discounts_used_in_all_reservations');
    }

    return $discount_counts;
}

/**
 * @param \TeamBooking_ReservationData[] $reservations
 * @param string                         $promotion_id
 * @param string                         $code
 *
 * @return bool
 */
function check_used_listed_coupon($reservations, $promotion_id, $code)
{
    foreach ($reservations as $reservation) {
        $discount_array = $reservation->getDiscount();
        foreach ($discount_array as $discount) {
            if (!isset($discount['id'])) {
                $promotion = Database\Promotions::getByName($discount['name']);
                reset($promotion);
                $discount['id'] = key($promotion);
            }
            if (isset($discount['coupon']) && $discount['id'] === $promotion_id && tb_mb_strtolower($code) === tb_mb_strtolower($discount['coupon'])) {
                return TRUE;
            }
        }
    }

    return FALSE;
}

/**
 * @return bool
 */
function generateCallTrace()
{
    session_start();
    $e = new \Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $caller = array_pop($trace);
    $length = count($trace);
    $result = array();

    for ($i = 0; $i < $length; $i++) {
        $result[] = ($i + 1) . ')' . substr($trace[ $i ], strpos($trace[ $i ], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }
    $_SESSION['tbk-backtrace'][ $caller ][] = $result;
    file_put_contents(dirname(TEAMBOOKING_FILE_PATH) . '\tbk-trace.txt', print_r($_SESSION['tbk-backtrace'], TRUE), LOCK_EX);

    return TRUE;
}

/**
 * @param $str1
 * @param $str2
 *
 * @return bool
 */
function tb_hash_equals($str1, $str2)
{
    if (!function_exists('hash_equals')) {
        if (strlen($str1) != strlen($str2)) {
            return FALSE;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for ($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[ $i ]);

            return !$ret;
        }
    } else {
        return hash_equals($str1, $str2);
    }
}

/**
 * @param string $url
 * @param string $order_id
 * @param array  $outcomes
 *
 * @return null|string
 */
function prepare_order_redirect_url($url, $order_id, array $outcomes)
{
    if (!empty($url)) return $url; // already filtered
    switch (getSettings()->getOrderRedirectRule()) {
        case 'no':
            return '';
        case 'yes':
            return getSettings()->getOrderRedirectUrl(array('order_id' => rawurlencode($order_id)));
        case 'service_specific_when_all':
            $url_return = array();
            foreach ($outcomes as $outcome) {
                if ($outcome['status'] === 'error' || empty($outcome['redirect'])) continue;
                $url_return[] = add_query_arg(array(
                    'reservation_database_id' => FALSE,
                    'order_id'                => rawurlencode($order_id)
                ), $outcome['redirect']);
            }
            if (count(array_unique($url_return)) === 1) return reset($url_return);
    }

    return '';
}

function fix_ajax_translations()
{
    $domain = 'team-booking';
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    add_action('change_locale', function () use ($locale, $domain) {
        add_filter('plugin_locale', 'TeamBooking\\Functions\\force_site_locale_once');
        load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, plugin_basename(TEAMBOOKING_PATH) . '/languages/');
    });
    switch_to_locale($locale);
}

function force_site_locale_once()
{
    remove_filter('plugin_locale', 'TeamBooking\\Functions\\force_site_locale_once');

    return get_locale();
}

/**
 * @param $user_id
 *
 * @return string
 */
function get_admin_edit_user_link($user_id)
{
    if (get_current_user_id() == $user_id) {
        $edit_link = get_edit_profile_url($user_id);
    } else {
        $edit_link = add_query_arg('user_id', $user_id, self_admin_url('user-edit.php'));
    }

    return $edit_link;
}