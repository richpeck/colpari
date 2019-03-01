<?php

namespace TeamBooking\Frontend;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Abstracts\FormElement,
    TeamBooking\Functions,
    TeamBooking\Actions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Frontend\Components,
    TeamBooking\Cart;

/**
 * Class Form
 *
 * @author VonStroheim
 */
class Form
{
    public $instance;
    public $start_end_times;
    public $coworker_string;
    public $service_location;
    /** @var \TeamBooking\Services\Appointment | \TeamBooking\Services\Event | \TeamBooking\Services\Unscheduled */
    public $service;
    private $coworker_id;
    private $service_id;
    private $is_checkout = FALSE;
    /** @var \TeamBookingSettings */
    private $settings;
    private $timezone;
    /** @var \TeamBooking\Slot|NULL */
    private $slot;

    /**
     * @param \TeamBooking\Slot $slot
     */
    private function scheduled(\TeamBooking\Slot $slot)
    {
        $this->settings = Functions\getSettings();
        try {
            if (Database\Services::get($slot->getServiceId())->getClass() !== 'unscheduled') {
                $this->service_id = $slot->getServiceId();
                $this->coworker_id = $slot->getCoworkerId();
                $this->timezone = $slot->getTimezone();
                $this->service = Database\Services::get($slot->getServiceId());
                $this->service_location = $slot->getLocation();
                $this->start_end_times = $slot->getTimesString();
                $this->coworker_string = $slot->getCoworkerDisplayString();
                $this->slot = $slot;
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @param $service_id
     */
    private function unscheduled($service_id)
    {
        $this->settings = Functions\getSettings();
        $this->service_id = $service_id;
        try {
            $this->service = Database\Services::get($service_id);
            $this->service_location = $this->service->getLocation();
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @param string $service_id
     * @param bool   $is_checkout
     *
     * @return Form
     */
    public static function fromService($service_id, $is_checkout = FALSE)
    {
        $form = new self();
        $form->unscheduled($service_id);
        $form->is_checkout = $is_checkout;

        return $form;
    }

    /**
     * @param \TeamBooking\Slot $slot
     *
     * @return Form
     */
    public static function fromSlot(\TeamBooking\Slot $slot)
    {
        $form = new self();
        $form->scheduled($slot);

        return $form;
    }

    /**
     * @param bool $is_editing
     *
     * @return string
     */
    public function getContent($is_editing = FALSE)
    {
        $string_total_tickets = __('Tickets', 'team-booking');
        $string_book_now = __('Book now', 'team-booking');
        $string_book_and_pay = __('Book and pay', 'team-booking');

        if (did_action('tbk_reservation_form_header') < 1) {
            add_action('tbk_reservation_form_header', array(
                $this,
                'form_header',
            ));
        }
        if (did_action('tbk_reservation_form_map') < 1) {
            add_action('tbk_reservation_form_map', array(
                $this,
                'form_map',
            ));
        }
        if (did_action('tbk_reservation_form_description') < 1) {
            add_action('tbk_reservation_form_description', array(
                $this,
                'form_description',
            ));
        }

        if (is_user_logged_in()) {
            // TODO: use only wp methods
            if (!$this->is_checkout
                && $this->service->getClass() === 'event'
                && $this->slot->getTicketsLeft(get_current_user_id()) < 1) {
                return Components\NavigationHeader::basicBack(__('Whoops', 'team-booking'))
                    . Components\Form::ticketsLimitReached($this->service->getName(TRUE));
            }
            $user = $this->settings->getCoworkerData(get_current_user_id());
            // TODO: better logic
            if ($this->service->getClass() === 'unscheduled' && $this->service->getMaxReservationsUser()) {
                $customer = new \TeamBooking\Customer(get_user_by('id', get_current_user_id()), Database\Reservations::getAll());
                $customer_reservations_left = $this->service->getMaxReservationsUser() - $customer->getEnumerableReservations($this->service_id);
                if ($customer_reservations_left <= 0) {
                    return Components\NavigationHeader::basicBack(__('Whoops', 'team-booking'))
                        . Components\Form::reservationsLimitReached($this->service->getName(TRUE));

                }
            }
        }
        $form_fields = Database\Forms::get($this->service->getForm(), TRUE);
        $form_fields = Actions\manipulate_frontend_form_fields($form_fields, $this->service, $this->slot);
        $there_are_files = FALSE;
        $form_fields_markup = array();
        $form_fields_hidden_markup = array();

        // fields pre-processing
        $provided_values = Cart::getFormFieldsValues();
        foreach ($form_fields as $field) {
            if (!($field instanceof FormElement)) continue;
            // Check for file upload fields
            if ($field->getType() === 'file_upload') {
                $there_are_files = TRUE;
            }
            switch ($field->getHook()) {
                case 'first_name':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getFirstName() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                case 'second_name':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getLastName() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                case 'email':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getEmail() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                case 'url':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getUrl() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                default:
                    $default_value = (
                    isset($user)
                    && $this->settings->getAutofillReservationForm()
                    && $field->getData('prefill')
                        ? get_user_meta($user->getId(), $field->getData('prefill'), TRUE)
                        : $field->getData('value')
                    );
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
            }

            if ($field->getType() === 'checkbox') {
                $field->setData('value', __('Selected', 'team-booking'));
            } else {
                $field->setData('value', $default_value);
                if (!$field->isHidden() && isset($provided_values[ $field->getHook() ])) {
                    if (is_array($provided_values[ $field->getHook() ])) {
                        if ($field->getType() === 'file_upload') {
                            $extensions = array_map('trim', explode(',', $field->getData('file_extensions')));
                            if (in_array(strtolower($provided_values[ $field->getHook() ]['ext']), array_map('strtolower', $extensions))) {
                                $field->setData('value', $provided_values[ $field->getHook() ]['path']);
                            }
                        }
                    } else {
                        $field->setData('value', $provided_values[ $field->getHook() ]);
                    }
                }
            }

            if ($field->isHidden()) {
                $form_fields_hidden_markup[] = $field;
            } else {
                $form_fields_markup[] = $field;
            }
        }

        ob_start();
        ?>
        <?php
        if (!$this->is_checkout && $this->service->getClass() !== 'unscheduled') {
            $date = Functions\dateFormatter(Functions\strtotime_tb($this->slot->getStartTime()), $this->slot->isAllDay(), $this->timezone)->date;
            echo Components\NavigationHeader::InReservationForm($date);
        }
        ?>
        <div class="tbk-reservation-form-container">
            <?php if (!$this->is_checkout) Actions\reservation_form_header($this) ?>
            <div class="tbk-content">
                <?php Actions\reservation_form_description($this) ?>
                <?php Actions\reservation_form_map($this); ?>

                <?php if ($this->service->getSettingsFor('bookable') !== 'nobody') { ?>
                    <!-- form section -->
                    <div class="ui horizontal tbk-divider">
                        <i class="info letter tb-icon"></i>
                    </div>

                    <?php $form_enctype = $there_are_files ? 'enctype="multipart/form-data"' : ''; ?>

                    <form class="tbk-reservation-form" method="POST" action="" <?= $form_enctype ?>>
                        <?php wp_nonce_field('teambooking_submit_reservation', 'nonce') ?>
                        <input type="hidden" name="tickets" value="1">
                        <input type="hidden" name="service" value="<?= esc_attr($this->service_id) ?>">
                        <input type="hidden" name="post_id" value="">
                        <input type="hidden" name="service_location" value="<?= $this->service_location ?>">
                        <input type="hidden" name="customer_wp_id"
                               value="<?= isset($user) ? esc_attr($user->getId()) : '' ?>">
                        <?php if (!$this->is_checkout && $this->service->getClass() !== 'unscheduled') { ?>
                            <input type="hidden" name="slot"
                                   value="<?= Toolkit\objEncode($this->slot, TRUE, $this->slot->getUniqueId()) ?>">
                            <input type="hidden" name="owner" value="<?= esc_attr($this->coworker_id) ?>">
                        <?php }

                        //Let's render the hidden fields (pre-filled user data) if any
                        foreach ($form_fields_hidden_markup as $field) {
                            /** @var $field FormElement */
                            echo $field->getMarkup(TRUE);
                        }

                        // Start grouping (max two, for now)
                        $group_limit = NULL;
                        $number_of_fields = count($form_fields_markup);
                        if ($number_of_fields > 3 && $number_of_fields < 700) {
                            $group_limit = 2;
                            $group_limit_textual = 'tbk-two';
                        } else {
                            $group_limit = 3;
                            $group_limit_textual = 'three';
                        }
                        $i = 1;
                        $j = 0;
                        foreach ($form_fields_markup as $field) {
                            $j++;

                            if ($field->getType() === 'paragraph') {
                                if ($i === 1) {
                                    echo "<div class='tbk-one tbk-fields'>";
                                } else {
                                    echo "</div><div class='tbk-one tbk-fields'>";
                                }
                            } else {
                                if ($i === 1) echo "<div class='$group_limit_textual tbk-fields'>";
                            }

                            /** @var $field FormElement */
                            $field->setServiceId($this->service_id);
                            echo $field->getMarkup();
                            // Close grouping
                            if ($i === $group_limit || $j === $number_of_fields || $field->getType() === 'paragraph') {
                                $i = 1; // reset
                                echo '</div>';
                            } else {
                                $i++;
                            }
                        }
                        ?>
                    </form>
                <?php } ?>
            </div>

            <?php if ($this->service->getSettingsFor('bookable') !== 'nobody') {
                $currency_array = Toolkit\getCurrencies($this->settings->getCurrencyCode());
                ?>
                <!-- form footer -->
                <div class="tbk-reservation-form-footer">
                    <?php if (!$this->is_checkout && $this->service->getPotentialPrice($this->slot) > 0 && Functions\isThereOneCouponAtLeast()) {
                        echo self::coupon_line();
                    } ?>
                    <!-- tickets and price section -->
                    <?php if (!$this->is_checkout
                        && ($this->service->getClass() === 'event' || $this->service->getPotentialPrice($this->slot) > 0)
                    ) {
                        if ($this->service->getClass() === 'unscheduled') {
                            $discounted_price = Functions\getDiscountedPrice($this->service);
                            $base_price = $this->service->getPrice();
                        } else {
                            $discounted_price = $this->slot->getPriceDiscounted();
                            $base_price = $this->slot->getPriceBase();
                        }
                        if ($base_price != $discounted_price) {
                            $price_string = Functions\currencyCodeToSymbol()
                                . '<del>' . Functions\priceFormat($base_price) . '</del><span class="tbk-discounted-price"> '
                                . Functions\priceFormat($discounted_price) . '</span>';
                        } else {
                            $price_string = Functions\currencyCodeToSymbol($base_price);
                        }
                        $id = 'tbk-tickets-price-section-' . Toolkit\randomNumber(10);
                        ?>
                        <div class="tbk-tickets-price-section" id="<?= $id ?>">
                            <table style="width: auto;margin: 0;">
                                <tr>
                                    <?php
                                    // Tickets number
                                    if ($this->service->getClass() === 'event') { ?>
                                        <td class="tbk-tickets-span">
                                            <?= esc_html($string_total_tickets) ?>
                                            <?php if ($this->service->getPotentialPrice($this->slot) > 0) { ?>
                                                <span class="tbk-total-price-line"
                                                      style="display: <?= $base_price > 0 ? 'inline-block' : 'none' ?>;">
                                                @
                                                <span class="tbk-total-price-line-price-unit"
                                                      style="font-style: italic;">
                                                <?= $price_string ?>
                                                </span>
                                                <span class="tbk-tickets-span-each">
                                                    /<?= esc_html__('each', 'team-booking') ?>
                                                </span>
                                            </span>
                                            <?php } ?>
                                        </td>
                                        <td class="tbk-ticket-value-cell">
                                            <?php
                                            if (!Functions\isAdmin() && $this->slot->getTicketsLeft(get_current_user_id()) <= 1) {
                                                echo '1';
                                                ?>
                                                <input class="tbk-ticket-value" type="hidden" value="1"/>
                                                <?php
                                            } else {
                                                ?>
                                                <input class="tbk-ticket-value" required type="number" min="1" value="1"
                                                       max="<?= $this->slot->getTicketsLeft(get_current_user_id()) ?>"
                                                       pattern="[0-9]" step="1"
                                                       style="padding: 6px;font-weight: 400;"/>
                                            <?php } ?>
                                        </td>
                                    <?php } else {
                                        ?>
                                        <td>
                                            <input class="tbk-ticket-value" type="hidden" value="1"/>
                                        </td>
                                        <?php
                                    } ?>
                                    <?php if ($this->service->getPotentialPrice($this->slot) > 0) { ?>
                                        <td <?= ($this->service->getClass() === 'event') ? 'style="text-align: right;"' : '' ?>>
                                            <div style="
                                     font-weight: 300;
                                     font-style: italic;
                                     display: inline-block;
                                     ">
                                                <div class="tbk-total-price-line"
                                                     style="display: <?= $base_price > 0 ? 'inline-block' : 'none' ?>;">
                                                </div>
                                            </div>
                                        </td>
                                    <?php } ?>
                                </tr>

                                <?php Actions\frontend_form_add_ticket_row($this->service, $this->slot); ?>

                            </table>
                        </div>

                        <script>
                            jQuery(document).ready(function ($) {
                                $('#<?=$id?>').tbkTicketLine({
                                    defaultAmountUnit    : <?= $base_price ?>,
                                    defaultAmountUnitDisc: <?= $discounted_price ?>,
                                    currencyFormat       : '<?= $currency_array['format'] ?>',
                                    currencySymbol       : '<?= $currency_array['symbol'] ?>',
                                    decimals             : '<?=$currency_array['decimal'] === TRUE ? 2 : 0 ?>'
                                });
                            });
                        </script>

                    <?php } ?>
                    <div class="tbk-book-now">
                        <!-- confirm button -->
                        <?php if ($this->is_checkout) {
                            if ($is_editing) {
                                echo Components\Form::editFormFooterActions($there_are_files);
                            } else {
                                echo Components\Form::checkoutFooterActions($there_are_files);
                            }
                        } else {
                            $button_text = (count($this->settings->getPaymentGatewaysActive()) == 1
                                && $this->service->getSettingsFor('payment') === 'immediately'
                                && (isset($base_price) ? $base_price : 0) > 0)
                                ? esc_html($string_book_and_pay)
                                : esc_html($string_book_now);
                            $id = 'tbk-book-now-button' . Toolkit\randomNumber(10);
                            ?>
                            <button class="tbk-book-now-button" id="<?= $id ?>" type="submit"
                                    data-files="<?= $there_are_files ? 1 : 0 ?>">
                                <?= $button_text ?>

                                <?php if ($this->service->getPotentialPrice($this->slot) > 0) { ?>
                                    <span class="tbk-total-price-line-price"
                                          data-base="<?= htmlentities($price_string) ?>"
                                          data-decimals="<?= $currency_array['decimal'] === TRUE ? 2 : 0 ?>">
                                <?= $price_string ?>
                            </span>
                                <?php } ?>

                                <?= isset($customer_reservations_left) ? '<span class="tbk-services-left">(' . $customer_reservations_left . ' ' . esc_html__('left', 'team-booking') . ')</span>' : '' ?>
                            </button>
                            <script>
                                jQuery(document).ready(function ($) {
                                    $('#<?=$id?>').tbkAmountButton({
                                        buttonText            : '<?=$button_text?>',
                                        buttonClass           : 'tbk-amount-button',
                                        toPayNowText          : 'To pay now',
                                        confirmAndPayText     : 'Confirm and pay',
                                        currencySymbol        : '<?= html_entity_decode($currency_array['symbol']) ?>',
                                        currencyFormat        : '<?= $currency_array['format'] ?>',
                                        defaultAmountTotal    : <?= isset($base_price) ? $base_price : 0?>,
                                        defaultAmountTotalDisc: <?= isset($discounted_price) ? $discounted_price : 0?>,
                                        decimals              : <?= $currency_array['decimal'] === TRUE ? 2 : 0 ?>,
                                        amountTotalClass      : '.tbk-total-price-line-price',
                                        amountTotalDiscClass  : '.tbk-discounted-price',
                                        isCheckout            : false
                                    });
                                });
                            </script>
                        <?php } ?>
                    </div>
                </div>
                <?php if (!$this->is_checkout
                    && $this->service->getClass() === 'event'
                    && Functions\isAdmin()
                    && $this->service->getSlotMaxUserTickets() < $this->slot->getMaxTickets()
                ) {
                    echo Components\Form::adminNoLimitsAdvice();
                } ?>

            <?php } else {
                if (Functions\isAdminOrCoworker()) {
                    echo Components\Form::adminReadOnlyAdvice();
                }
            } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function coupon_line()
    {
        $id = 'tbk-coupon-form-' . \TeamBooking\Toolkit\randomNumber(10);
        ob_start();
        ?>
        <div class="tbk-summary-coupon" id="<?= $id ?>">
            <input class="tbk-summary-coupon-input" type="text"
                   placeholder="<?= esc_html(__('Coupon?', 'team-booking')) ?>">
            <span class="tbk-summary-coupon-applied"><span>TEST</span></span>
            <button class="tbk-button tbk-summary-apply-coupon"></button>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                $('#<?= $id ?>').tbkCouponInput({
                    applyButtonText : '<?= esc_html(__('Apply', 'team-booking')) ?>',
                    removeButtonText: '<?= esc_html(__('Remove', 'team-booking')) ?>',
                    wrongCouponText : '<?= esc_html(__('Wrong or expired code!', 'team-booking')) ?>'
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * @param bool   $direct
     * @param string $event_id
     * @param string $service_id
     * @param string $coworker_id
     * @param mixed  $post_id
     *
     * @return mixed
     */
    public static function getContentRegisterAdvice($direct = FALSE, $event_id = '', $service_id = '', $coworker_id = '', $post_id = NULL)
    {
        if (Functions\getSettings()->getRedirectBackAfterLogin()) {
            $redirect_url = get_permalink($post_id);
            if (!empty($event_id)) {
                $events = Database\Events::getByEventId($event_id);
                if (empty($events)) {
                    $broken_id = explode('_', $event_id, 2);
                    if (count($broken_id) === 2) {
                        $events = Database\Events::getByEventId($broken_id[0], strtotime($broken_id[1]));
                    }
                }
                if (!empty($events)) {
                    if (isset($events[ $coworker_id ])) {
                        $events = reset($events[ $coworker_id ]);
                    }
                }
                $event = NULL;
                if (is_array($events) && isset($events[ $event_id ])) {
                    $event = $events[ $event_id ];
                }
                if ($event instanceof Database\eventObject) {
                    $event->id = $event_id;
                    $slot = \TeamBooking\Slot::getFromEvent($event, $service_id, $coworker_id);
                    $redirect_url = add_query_arg('tbk_date', $slot->getDateString(TRUE), $redirect_url);
                }
            }
            $registration_url = add_query_arg('redirect_to', urlencode($redirect_url), Functions\getSettings()->getRegistrationUrl());
            $login_url = add_query_arg('redirect_to', urlencode($redirect_url), Functions\getSettings()->getLoginUrl());
        } else {
            $registration_url = Functions\getSettings()->getRegistrationUrl();
            $login_url = Functions\getSettings()->getLoginUrl();
        }
        ob_start();
        ?>
        <?php if ($direct) echo '<div style="text-align:center;">' ?>
        <div style="margin:10px 0 20px 0;padding: 0 20px;text-align: center;">
            <?= esc_html__('You must be logged-in to book this!', 'team-booking') ?>
        </div>
        <div>
            <a href="<?= esc_url($registration_url) ?>"
               class="tbk-button tbk-red"><?= esc_html__("I'm not registered...", 'team-booking') ?></a>
        </div>
        <div>
            <a href="<?= esc_url($login_url) ?>"
               class="tbk-button tbk-green"><?= esc_html__('Login', 'team-booking') ?></a>
        </div>
        <?php
        if (!$direct) { ?>
            <div>
                <div class="tbk-button tbk-dimmer-off" tabindex="0">
                    <?= esc_html__('Go back', 'team-booking') ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($direct) echo '</div>' ?>
        <?php

        return ob_get_clean();
    }

    /**
     * @param \TeamBooking\Frontend\Form $form
     */
    public function form_header($form)
    {
        echo Components\Form::header($form->service->getName(TRUE), $form->start_end_times, $form->coworker_string);
    }

    /**
     * @param \TeamBooking\Frontend\Form $form
     */
    public function form_map($form)
    {
        if (!empty($form->service_location) && $this->service->getSettingsFor('location_visibility') === 'visible') {
            echo Components\Form::map($form->service_location, $this->service->getSettingsFor('show_map'));
        }
    }

    /**
     * @param \TeamBooking\Frontend\Form $form
     */
    public function form_description($form)
    {
        if ($form->service->getDescription()) echo Components\Form::serviceDescription($form->service->getDescription(TRUE));
    }

}
