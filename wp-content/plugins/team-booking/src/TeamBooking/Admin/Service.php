<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Mappers,
    TeamBooking\Database\Forms;

/**
 * Class Service
 *
 * @since  2.0.6
 * @author VonStroheim
 */
class Service
{
    private $action;

    /** @var \TeamBookingCoworker */
    private $coworker;

    /** @var \TeamBooking\Services\Appointment | \TeamBooking\Services\Event | \TeamBooking\Services\Unscheduled */
    private $service;

    private $STRING_APPLY_TO_ALL;
    private $STRING_APPLY_TO_ALL_WARNING;

    public function __construct($service)
    {
        $this->coworker = Functions\getSettings()->getCoworkerData(get_current_user_id());
        $this->service = $service;
        $this->action = \TeamBooking\Admin::add_params_to_admin_url(admin_url('admin-post.php'));
        $this->STRING_APPLY_TO_ALL = __('Apply to all the service providers', 'team-booking');
        $this->STRING_APPLY_TO_ALL_WARNING = __("Are you sure? The service providers won't be notified of those changes, continue only if you know what you are doing.", 'team-booking');
    }

    /**
     * The service's general settings page
     *
     * @return string
     */
    public function getPostBody()
    {
        $string_1 = strtoupper(__('Class', 'team-booking'))
            . ' '
            . ucfirst($this->service->getClass())
            . ' - ' .
            strtoupper(__('Id', 'team-booking'))
            . ' '
            . $this->service->getId();
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            // Header (service)
            $row = new Framework\Row();
            $column = Framework\Column::fullWidth();
            $column->addElement(Framework\ElementFrom::content($this->getHeaderBlock($string_1)));
            $column->appendTo($row);
            $row->render();

            // Setting panels
            ?>
            <form method="POST" action="<?= $this->action ?>">
                <input type="hidden" name="action" value="tbk_save_service">
                <?php wp_nonce_field('team_booking_options_verify') ?>
                <input type="hidden" name="service_id" value="<?= $this->service->getId() ?>">
                <input type="hidden" name="service_settings" value="general">
                <?php
                $row = new Framework\Row();
                $column = Framework\Column::ofWidth(4);
                $column->addElement($this->getGeneralEdit());
                $column->addElement($this->getGeneralAccess());
                $column->appendTo($row);
                $column = Framework\Column::ofWidth(4);
                $column->addElement($this->getGeneralSlotsAppearance());
                $column->addElement($this->getGeneralPayments());
                $column->addElement($this->getGeneralRedirect());
                $column->appendTo($row);
                $column = Framework\Column::ofWidth(4);
                $column->addElement($this->getGeneralApproveDeny());
                if ($this->service->getClass() !== 'unscheduled') {
                    $column->addElement($this->getCancellationSettings());
                }
                $column->appendTo($row);
                $row->render();
                ?>
            </form>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * The service's e-mail settings page
     *
     * @return string
     */
    public function getPostBodyEmail()
    {
        $string_1 = esc_html__('E-mail settings', 'team-booking');
        $string_2 = esc_html__('active', 'team-booking');
        $string_3 = esc_html__('inactive', 'team-booking');
        $string_4 = esc_html__('confirmation e-mail', 'team-booking');
        $string_5 = esc_html__('reminder e-mail', 'team-booking');
        $hooks = Forms::getActiveHooks($this->service->getForm());
        if ($this->service->getClass() !== 'unscheduled') {
            $hooks[] = 'start_datetime';
            $hooks[] = 'start_date';
            $hooks[] = 'start_time';
            $hooks[] = 'end_datetime';
            $hooks[] = 'end_date';
            $hooks[] = 'end_time';
            $hooks[] = 'timezone';
            if ($this->service->getClass() !== 'appointment') {
                $hooks[] = 'tickets_quantity';
                $hooks[] = 'total_price';
            }
        }
        $hooks[] = 'service_name';
        $hooks[] = 'reservation_id';
        $hooks[] = 'order_id';
        $hooks[] = 'unit_price';
        $hooks[] = 'post_id';
        $hooks[] = 'post_title';
        $hooks[] = 'coworker_name';
        $hooks[] = 'coworker_url';
        $hooks[] = 'hangout_link';
        if ($this->service->getSettingsFor('location') !== 'none') {
            $hooks[] = 'service_location';
        }
        // The summary
        $summary = '';
        if (Functions\isAdmin()) {
            $summary .= $string_4;
            if ($this->service->getEmailToCustomer('send')) {
                $summary .= ' ' . Framework\Html::span(array('text' => $string_2, 'class' => 'active'));
            } else {
                $summary .= ' ' . Framework\Html::span(array('text' => $string_3, 'class' => 'inactive'));
            }
            if ($this->service->getClass() !== 'unscheduled') {
                $summary .= '<br>' . $string_5;
                if ($this->service->getEmailReminder('send')) {
                    $summary .= ' ' . Framework\Html::span(array('text' => $string_2, 'class' => 'active'));
                } else {
                    $summary .= ' ' . Framework\Html::span(array('text' => $string_3, 'class' => 'inactive'));
                }
            }
        }
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            // Header (service)
            $row = new Framework\Row();
            $column = Framework\Column::fullWidth();
            $column->addElement(Framework\ElementFrom::content($this->getHeaderBlock($string_1, $summary)));
            $column->appendTo($row);
            $row->render();

            // Setting panels
            ?>
            <form method="POST" action="<?= $this->action ?>">
                <input type="hidden" name="action" value="tbk_save_service">
                <?php wp_nonce_field('team_booking_options_verify') ?>
                <input type="hidden" name="service_id" value="<?= $this->service->getId() ?>">
                <input type="hidden" name="service_settings" value="email">
                <?php
                $row = new Framework\Row();
                if (Functions\isAdmin()) {
                    $column = Framework\Column::ofWidth(4);
                    $column->addElement($this->getEmailAdminSettings($hooks));
                    $column->appendTo($row);
                    $column = Framework\Column::ofWidth(4);
                    $column->addElement($this->getEmailCustomerSettings($hooks));
                    $column->appendTo($row);
                }
                $column = Framework\Column::ofWidth(4);
                $column->addElement($this->getEmailCoworkerSettings($hooks));
                $column->appendTo($row);
                $row->render();
                ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }


    /**
     * The service's Personal availability settings page
     *
     * @return string
     */
    public function getPostBodyGcalSettings()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            $row = new Framework\Row();
            $column = Framework\Column::fullWidth();
            $column->addElement(Framework\ElementFrom::content($this->getHeaderBlock(
                __('Personal availability settings', 'team-booking'),
                __('Those settings are valid for your Google Account only', 'team-booking') . '<br>' . Framework\Html::span(array('text' => $this->coworker->getAuthAccount(), 'class' => 'active'))
            )));
            $column->appendTo($row);
            $row->render();

            $row = new Framework\Row();
            $column = Framework\Column::ofWidth(8);
            ob_start();
            ?>
            <form method="POST" action="<?= $this->action ?>">
                <input type="hidden" name="action" value="tbk_save_service">
                <input type="hidden" name="service_id" value="<?= $this->service->getId() ?>">
                <?php wp_nonce_field('team_booking_options_verify') ?>
                <?php $this->getGCalSettingsContent()->render() ?>
            </form>
            <?php
            $column->addElement(Framework\ElementFrom::content(ob_get_clean()));
            $column->appendTo($row);
            $column = Framework\Column::ofWidth(4);
            $column->addElement($this->getAvailabilityModes());
            $column->addElement($this->getSlotCommands());
            $column->appendTo($row);

            $row->render();
            ?>
        </div>
        <?php
        return ob_get_clean();
    }


    /**
     * The service's reservation form builder page
     *
     * @return string
     */
    public function getPostBodyReservationForm()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            $row = new Framework\Row();
            $column = Framework\Column::fullWidth();
            $column->addElement(Framework\ElementFrom::content($this->getHeaderBlock(ucfirst(__('Reservation form', 'team-booking')))));
            $column->appendTo($row);
            $row->render();

            $row = new Framework\Row();
            $column = Framework\Column::ofWidth(8);
            $column->addElement($this->getFormFieldsEditor());
            $column->appendTo($row);
            $column = Framework\Column::ofWidth(4);
            $column->addElement($this->getFormElements());
            $column->appendTo($row);
            $row->render();
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * The admin e-mail part
     *
     * @param array $hooks
     *
     * @return Framework\Panel
     */
    private function getEmailAdminSettings(array $hooks)
    {
        $panel = new Framework\Panel(ucfirst(__('Notifications to the Admin', 'team-booking')));

        // Notification e-mail message to admin
        $element = new Framework\PanelSettingEmailEditor(__('Notification e-mail message to the admin', 'team-booking'));
        $element->setServiceId($this->service->getId());
        $element->addDescription('The e-mail message sent to the admin when a reservation is made', 'team-booking');
        $element->setShowSend(TRUE);
        $element->setShowRepeatDelimiters(Functions\getSettings()->allowCart());
        $element->setSendFieldname('event[email_to_email]');
        $element->setSendState($this->service->getEmailToAdmin('send'));
        $element->addFieldname('event[back_end_email]');
        $element->setSubject($this->service->getEmailToAdmin('subject'));
        $element->setBody($this->service->getEmailToAdmin('body'));
        foreach ($hooks as $hook) {
            $element->addPlaceholder($hook);
        }
        if ($this->service->getSettingsFor('approval_rule') !== 'none') {
            $element->addPlaceholder('approve_link', TRUE);
            $element->addPlaceholder('decline_link', TRUE);
        }
        $panel->addElement($element);

        // Send an e-mail message back to admin and coworker when a reservation is cancelled
        $element = new Framework\PanelSettingEmailEditor(__('Cancellation e-mail message to the admin and coworker', 'team-booking'));
        $element->setServiceId($this->service->getId());
        $element->addDescription(__('The e-mail message sent to both the admin and the coworker when a reservation is cancelled', 'team-booking'));
        $element->setShowSend(TRUE);
        $element->setSendFieldname('event[send_cancellation_email_backend]');
        $element->setSendState($this->service->getEmailCancellationToAdmin('send'));
        $element->addFieldname('event[cancellation_email_backend]');
        $element->setSubject($this->service->getEmailCancellationToAdmin('subject'));
        $element->setBody($this->service->getEmailCancellationToAdmin('body'));
        foreach ($hooks as $hook) {
            $element->addPlaceholder($hook);
        }
        $element->addPlaceholder('reason');
        $panel->addElement($element);

        // Admin address for receiving notifications
        $element = new Framework\PanelSettingText(__('Recipient e-mail address of admin notifications', 'team-booking'));
        $element->addDescription(__('Specify the recipient e-mail address of the notifications that are sent to the admin (supposedly, the admin e-mail address)', 'team-booking'));
        $element->addDefaultValue($this->service->getEmailToAdmin('to'));
        $element->addFieldname('event[email_for_notifications]');
        $element->setRequired(TRUE);
        $panel->addElement($element);

        // Include uploaded files as attachment
        $element = new Framework\PanelSettingYesOrNo(__('Include uploaded files as attachment', 'team-booking'));
        $element->addDescription(__('If the reservation form collects one or more file from the customer, chose whether or not to include them as e-mail attachment', 'team-booking'));
        $element->addFieldname('event[include_files_as_attachment]');
        $element->setState($this->service->getEmailToAdmin('attachments'));
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The customer e-mail part
     *
     * @param array $hooks
     *
     * @return Framework\Panel
     */
    private function getEmailCustomerSettings(array $hooks)
    {
        $string_1 = esc_html__("E-mail reminders are sent via WordPress Cron system, which is triggered by site's visits. If your site has low traffic, then reminders can have delays, or not be sent at all.", 'team-booking');
        $panel = new Framework\Panel(ucfirst(__('Notifications to the customer', 'team-booking')));

        // Confirmation e-mail message to customer
        $element = new Framework\PanelSettingEmailEditor(__('Confirmation e-mail message to the customer', 'team-booking'));
        $element->setServiceId($this->service->getId());
        $element->addDescription(__('The e-mail message sent to the customer after the reservation is made or approved', 'team-booking'));
        $element->setShowSend(TRUE);
        $element->setShowRepeatDelimiters(Functions\getSettings()->allowCart());
        $element->setSendFieldname('event[email_to_customer]');
        $element->setSendState($this->service->getEmailToCustomer('send'));
        $element->addFieldname('event[front_end_email]');
        $element->setSubject($this->service->getEmailToCustomer('subject'));
        $element->setBody($this->service->getEmailToCustomer('body'));
        foreach ($hooks as $hook) {
            $element->addPlaceholder($hook);
        }
        if ($this->service->getSettingsFor('customer_cancellation')) {
            $element->addPlaceholder('cancellation_link', TRUE);
        }
        if ($this->service->getPrice() > 0 && $this->service->getSettingsFor('payment') !== 'later') {
            $element->addPlaceholder('pay_link', TRUE);
        }
        $element->addPlaceholder('ics_link', TRUE);
        $panel->addElement($element);

        // Reminder e-mail message to customer
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingEmailEditor(__('Reminder e-mail message to the customer', 'team-booking'));
            $element->setServiceId($this->service->getId());
            $element->setShowSend(TRUE);
            $element->setSendFieldname('event[email_for_reminder]');
            $element->setSendState($this->service->getEmailReminder('send'));
            $element->addFieldname('event[reminder_email]');
            $element->setSubject($this->service->getEmailReminder('subject'));
            $element->setBody($this->service->getEmailReminder('body'));
            foreach ($hooks as $hook) {
                $element->addPlaceholder($hook);
            }
            $element->addDescription(Framework\Html::paragraph($string_1), FALSE);
            ob_start();
            ?>
            <select name="event[email_for_reminder_timeframe]"
                    id="tb-reminder-email-timeframe" <?= $this->service->getEmailReminder('send') ? '' : 'style="display:none;"' ?>>
                <?php for ($i = 1; $i < 6; $i++) {
                    echo '<option value="' . $i . '"';
                    if ($this->service->getEmailReminder('days_before') == $i) {
                        echo ' selected="selected"';
                    }
                    echo '>' . sprintf(esc_html(_n('%d day before', '%d days before', $i, 'team-booking')), $i) . '</option>';
                }
                ?>
            </select>
            <script>
                jQuery("input[name='event[email_for_reminder]']").on('click', function () {
                    jQuery('#tb-reminder-email-timeframe').fadeToggle('fast');
                })
            </script>
            <?php
            $element->addToDescription(ob_get_clean(), FALSE);
            $panel->addElement($element);
        }

        // Send an e-mail message back to customer when a reservation is cancelled
        $element = new Framework\PanelSettingEmailEditor(__('Cancellation e-mail message to the customer', 'team-booking'));
        $element->setServiceId($this->service->getId());
        $element->addDescription(__('The e-mail message sent to customer when a reservation is cancelled', 'team-booking'));
        $element->setShowSend(TRUE);
        $element->setSendFieldname('event[send_cancellation_email]');
        $element->setSendState($this->service->getEmailCancellationToCustomer('send'));
        $element->addFieldname('event[cancellation_email]');
        $element->setSubject($this->service->getEmailCancellationToCustomer('subject'));
        $element->setBody($this->service->getEmailCancellationToCustomer('body'));
        foreach ($hooks as $hook) {
            $element->addPlaceholder($hook);
        }
        $element->addPlaceholder('reason');
        $panel->addElement($element);

        // Who is the sender
        $element = new Framework\PanelSettingRadios(__('Who should figure as the sender of the e-mail messages to the customer?', 'team-booking'));
        $element->addDescription(__('You can set the sender of the e-mail messages to the customer', 'team-booking'));
        $element->addFieldname('event[email_to_customer_sender]');
        $element->addOption(array(
            'label'   => esc_html__('The admin ', 'team-booking')
                . '<p>' . sprintf(esc_html__('e-mail address as specified in the Admin Notification part (currently %s), the sender name will be the site title (currently %s)', 'team-booking'),
                    '<strong><em>' . $this->service->getEmailToAdmin('to') . '</em></strong>',
                    '<strong><em>' . get_option('blogname') . '</em></strong>') . '</p>',
            'value'   => 'admin',
            'checked' => $this->service->getEmailToCustomer('from') === 'admin',
            'escape'  => FALSE
        ));
        $element->addOption(array(
            'label'   => esc_html__('The service provider', 'team-booking')
                . '<p>' . esc_html__('e-mail address and name as set in the WordPress user profile', 'team-booking') . '</p>',
            'value'   => 'coworker',
            'checked' => $this->service->getEmailToCustomer('from') === 'coworker',
            'escape'  => FALSE
        ));
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $element->appendTo($panel);

        return $panel;
    }


    /**
     * The coworker notification e-mail part
     *
     * @param array $hooks
     *
     * @return Framework\Panel
     */
    private function getEmailCoworkerSettings(array $hooks)
    {
        $custom_booking_type_settings = $this->coworker->getCustomEventSettings($this->service->getId());
        $panel = new Framework\Panel(ucfirst(__('Your personal notification', 'team-booking')));

        // Get reservation details by e-mail
        $element = new Framework\PanelSettingYesOrNo(__('Get reservation details by e-mail', 'team-booking'));
        $element->addDescription(__('Send to:', 'team-booking'));
        $element->addToDescription(' ' . $this->coworker->getEmail());
        $element->addToDescription(' (<a href="' . get_edit_user_link() . '">', FALSE);
        $element->addToDescription(__('where can I change this?', 'team-booking'));
        $element->addToDescription('</a>)', FALSE);
        $element->addFieldname('data[get_details_by_email]');
        $element->setState($custom_booking_type_settings->getGetDetailsByEmail());
        $panel->addElement($element);

        // Include uploaded files as attachment
        $element = new Framework\PanelSettingYesOrNo(__('Include uploaded files as attachment', 'team-booking'));
        $element->addDescription(__('If the reservation form collects one or more file from the customer, chose whether or not to include them as e-mail attachment', 'team-booking'));
        $element->addFieldname('data[include_uploaded_files_as_attachment]');
        $element->setState($custom_booking_type_settings->getIncludeFilesAsAttachment());
        $panel->addElement($element);

        // Notification e-mail message
        $element = new Framework\PanelSettingEmailEditor(__('Reservation details e-mail message', 'team-booking'));
        $element->setServiceId($this->service->getId());
        $element->addDescription(__('The e-mail message sent to you as coworker when one of your slots of this service is booked', 'team-booking'));
        $element->addFieldname('data[email][email_text]');
        $element->setShowRepeatDelimiters(Functions\getSettings()->allowCart());
        $element->setSubject($custom_booking_type_settings->getNotificationEmailSubject());
        $element->setBody($custom_booking_type_settings->getNotificationEmailBody());
        $hooks[] = 'gcal_event_link';
        foreach ($hooks as $hook) {
            if ($hook === 'coworker_name') continue;
            $element->addPlaceholder($hook);
        }
        if ($this->service->getSettingsFor('approval_rule') === 'coworker') {
            $element->addPlaceholder('approve_link', TRUE);
            $element->addPlaceholder('decline_link', TRUE);
        }
        $panel->addElement($element);

        // Save changes
        if (Functions\isAdmin()) {
            $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type', array(
                'name'  => esc_attr($this->STRING_APPLY_TO_ALL),
                'value' => 'team_booking_update_booking_type_apply_to_all'
            ), array('data-msg' => esc_attr($this->STRING_APPLY_TO_ALL_WARNING)));
        } else {
            $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        }
        $panel->addElement($element);

        return $panel;
    }

    /**
     * The header block
     *
     * @param      $description
     * @param bool $summary
     *
     * @return string
     */
    private function getHeaderBlock($description, $summary = FALSE)
    {
        ob_start();
        ?>
        <div class="tbk-panel tbk-content">
            <div class="tbk-settings-title">
                <?= Framework\Html::anchor(array(
                    'text'   => Framework\Html::span(array('class' => 'dashicons dashicons-arrow-left-alt')) . esc_html__('Back to list', 'team-booking'),
                    'escape' => FALSE,
                    'class'  => 'button button-hero button-primary',
                    'href'   => admin_url('admin.php?page=team-booking-events')
                )) ?>
                <div style="display: inline-block;">
                    <?= Framework\Html::h3(array('text' => $this->service->getName(TRUE), 'class' => 'tbk-heading')) ?>
                    <?= Framework\Html::paragraph(array('text' => $description, 'class' => 'tbk-excerpt')) ?>
                </div>
                <?php if ($summary) { ?>
                    <div class="tb-panel-summary" style="display: inline-block;">
                        <?= $summary ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * The general service's settings part
     *
     * @return Framework\Panel
     */
    private function getGeneralEdit()
    {
        $panel = new Framework\Panel(ucfirst(__('General settings', 'team-booking')));

        // Service name
        $element = new Framework\PanelSettingText(__('Name', 'team-booking'));
        $element->addDefaultValue($this->service->getName());
        $element->addPlaceholder('e.g. House Surveys');
        $element->addFieldname('event[booking_type_name]');
        $element->setRequired(TRUE);
        $element->setServiceId($this->service->getId());
        $panel->addElement($element);

        // Service description
        $element = new Framework\PanelSettingRichTextarea(__('Service description', 'team-booking'));
        $element->addDescription(__('This description will be shown on reservation modal. You can use html formatting.', 'team-booking'));
        $element->setText($this->service->getDescription());
        $element->addFieldname('event[info]');
        $element->setServiceId($this->service->getId());
        $panel->addElement($element);

        // Service color
        $element = new Framework\PanelSettingColorPicker(__('Service color', 'team-booking'));
        $element->addFieldname('event[service_color]');
        $element->setValue($this->service->getColor());
        $panel->addElement($element);

        // Max total tickets per slot & max tickets per reservation
        if ($this->service->getClass() === 'event') {
            $element = new Framework\PanelSettingNumber(__('Max total tickets per slot', 'team-booking'));
            $element->addFieldname('event[max_attendees]');
            $element->addDescription(__('Max value = 200', 'team-booking'));
            $element->setValue($this->service->getSlotMaxTickets());
            $element->setMin(1);
            $element->setMax(200);
            $panel->addElement($element);

            $element = new Framework\PanelSettingNumber(__('Max user tickets per slot', 'team-booking'));
            $element->addFieldname('event[max_tickets_per_reservation]');
            $element->addDescription(__('By default a customer can book only one ticket per single slot. If you want to allow the customer to book multiple tickets per slot, provide the maximum number here.', 'team-booking'));
            $element->addAlert(__('The limit will not be applied to logged-in Administrators', 'team-booking'));
            $element->setValue($this->service->getSlotMaxUserTickets());
            $element->setMin(1);
            $element->setMax($this->service->getSlotMaxTickets());
            $panel->addElement($element);
        }

        // Slot duration
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Slot duration rule', 'team-booking'));
            $element->addFieldname('event[duration_rule]');
            $element->addOption(array(
                'label'   => __('Let Coworkers decide', 'team-booking'),
                'value'   => 'coworker',
                'checked' => (int)('coworker' === $this->service->getSettingsFor('slot_duration'))
            ));
            $element->addOption(array(
                'label'   => __('Inherited from Google Calendar event', 'team-booking'),
                'value'   => 'inherited',
                'checked' => (int)('inherited' === $this->service->getSettingsFor('slot_duration'))
            ));
            $label_element = new Framework\PanelSettingTimespanSelector(__('Fixed', 'team-booking'));
            $label_element->addFieldname('event[default_duration]');
            $label_element->isNested(TRUE);
            $label_element->setHoursLabel(__('hours', 'team-booking'));
            $label_element->setMinsLabel(__('minutes', 'team-booking'));
            $label_element->setSelectedHours(floor($this->service->getSlotDuration() / HOUR_IN_SECONDS));
            $label_element->setSelectedMins(ceil($this->service->getSlotDuration() - floor($this->service->getSlotDuration() / HOUR_IN_SECONDS) * HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
            $element->addOption(array(
                'label'       => $label_element,
                'label_title' => __('Fixed', 'team-booking'),
                'value'       => 'fixed',
                'checked'     => (int)('fixed' === $this->service->getSettingsFor('slot_duration'))
            ));
            $panel->addElement($element);
        }

        // Service assignment rule
        if ($this->service->getClass() === 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Assignment rule', 'team-booking'));
            $element->addDescription(__('In case of multiple coworkers giving the availability to this service, it needs a rule to decide which of them will be assigned to a new reservation, time after time.', 'team-booking'));
            $element->addFieldname('event[assignment_rule]');
            $element->addOption(array(
                'label'       => __('Equal', 'team-booking'),
                'value'       => 'equal',
                'checked'     => (int)('equal' === $this->service->getSettingsFor('assignment_rule')),
                'description' => __('A new reservation will be assigned to the coworker with less of them assigned (for this service), based on reservation history in the database.', 'team-booking')
            ));
            $description_element = new Framework\PanelSettingDescriptionWithSelector(NULL);
            $description_element->addFieldname('event[direct_coworker]');
            $description_element->addDescription(__('A new reservation will be always assigned to the coworker specified below.', 'team-booking'));
            $description_element->setSelectedOption($this->service->getDirectCoworkerId());
            $description_element->setDisabled($this->service->getSettingsFor('assignment_rule') !== 'direct');
            foreach (Functions\getCoworkersIdList() as $id) {
                $user_data = get_userdata($id);
                $description_element->addOption(
                    $id,
                    $user_data->user_firstname . ' ' . $user_data->user_lastname . ' (' . $user_data->user_email . ')'
                );
            }
            $element->addOption(array(
                'label'       => __('Direct', 'team-booking'),
                'value'       => 'direct',
                'checked'     => (int)('direct' === $this->service->getSettingsFor('assignment_rule')),
                'description' => $description_element
            ));
            $element->addOption(array(
                'label'       => __('Random', 'team-booking'),
                'value'       => 'random',
                'checked'     => (int)('random' === $this->service->getSettingsFor('assignment_rule')),
                'description' => __('Picks a random coworker.', 'team-booking')
            ));
            $element->setDividers(TRUE);
            $panel->addElement($element);
        }

        // location
        $element = new Framework\PanelSettingRadios(__('Location', 'team-booking'));
        $element->addDescription(__("Choose how to set a location for this service. If a location is present, it will be shown in the frontend with directions and map (unless differently specified)", 'team-booking'));
        $element->addFieldname('event[location_setting]');
        $element->addOption(array(
            'label'   => __('No location', 'team-booking'),
            'value'   => 'none',
            'checked' => (int)('none' === $this->service->getSettingsFor('location'))
        ));
        if ($this->service->getClass() !== 'unscheduled') {
            $element->addOption(array(
                'label'   => __('Inherited from the Google Calendar event', 'team-booking'),
                'value'   => 'inherited',
                'checked' => (int)('inherited' === $this->service->getSettingsFor('location'))
            ));
        }
        $description_element = new Framework\PanelSettingDescriptionWithTextfield(NULL);
        $description_element->addFieldname('event[location_address]');
        $description_element->setDefaultText($this->service->getLocation());
        $description_element->setPlaceholder('e.g. 1234, Main Street, Anytown, USA');
        $description_element->setDisabled($this->service->getSettingsFor('location') !== 'fixed');
        $element->addOption(array(
            'label'       => __('Fixed location', 'team-booking'),
            'value'       => 'fixed',
            'checked'     => (int)('fixed' === $this->service->getSettingsFor('location')),
            'description' => $description_element
        ));
        if ($this->service->getClass() === 'appointment') {
            $element->addAlert(__('If set to "no location" and the reservation form has the built-in address field active, then the Google Calendar event will be updated with the address of the customer, if given. Only for Appointment Class.', 'team-booking'));
        }
        $panel->addElement($element);

        // location visibility
        $element = new Framework\PanelSettingRadios(__('Location visibility', 'team-booking'));
        $element->addDescription(__('If a location is present, choose if it must be visible in the frontend. If you choose to hide the location from the frontend, it will still be visible in the backend and usable, for instance, in the e-mail templates.', 'team-booking'));
        $element->addFieldname('event[location_visibility]');
        $element->addOption(array(
            'label'   => __('Visible in the frontend', 'team-booking'),
            'value'   => 'visible',
            'checked' => (int)('visible' === $this->service->getSettingsFor('location_visibility'))
        ));
        $element->addOption(array(
            'label'   => __('Hidden from the frontend', 'team-booking'),
            'value'   => 'hidden',
            'checked' => (int)('hidden' === $this->service->getSettingsFor('location_visibility'))
        ));
        $panel->addElement($element);

        // map
        $element = new Framework\PanelSettingYesOrNo(__('Show map', 'team-booking'));
        $element->addDescription(__('If a location is present, a map will be shown by default. Change this setting to hide the map if it is not needed.', 'team-booking'));
        $element->addFieldname('event[show_map]');
        $element->setState($this->service->getSettingsFor('show_map'));
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }


    /**
     * The service's payment settings part
     *
     * @return Framework\Panel
     */
    private function getGeneralPayments()
    {
        $panel = new Framework\Panel(ucfirst(__('Payments settings', 'team-booking')));

        // Price for reservation
        $element = new Framework\PanelSettingNumber(__('Price for reservation', 'team-booking'));
        $element->addFieldname('event[service_price]');
        $element->addDescription(__("If zero, won't appears. To change the currency, go to Payments Gateway tab.", 'team-booking'));
        $element->setMin(0);
        $element->setStep(0.01);
        $element->setValue($this->service->getPrice());
        $element->setFieldDescription(Functions\currencyCodeToSymbol());
        $panel->addElement($element);

        // How the payment must be done
        $element = new Framework\PanelSettingRadios(__('How the payment must be done', 'team-booking'));
        $element->addFieldname('event[payment_must_be_done]');
        $element->addOption(array(
            'label'       => __('Immediately', 'team-booking'),
            'value'       => 'immediately',
            'checked'     => (int)('immediately' === $this->service->getSettingsFor('payment')),
            'description' => __(
                'The customer is redirect to the payment gateway after the reservation. If no payment will be done within the Max Pending Time specified in Payments gateway section, the reservation will be released.',
                'team-booking'
            )
        ));
        $element->addOption(array(
            'label'       => __('Later', 'team-booking'),
            'value'       => 'later',
            'checked'     => (int)('later' === $this->service->getSettingsFor('payment')),
            'description' => __(
                'The customer will pay on a later time. Team Booking will not handle the payment, and you should manually set the reservation as "Paid", eventually.',
                'team-booking'
            )
        ));
        $element->addOption(array(
            'label'       => __("At the customer's discretion", 'team-booking'),
            'value'       => 'discretional',
            'checked'     => (int)('discretional' === $this->service->getSettingsFor('payment')),
            'description' => __(
                'The customer choose whether redirect to the payment gateway after the reservation, or pay locally/later. If he wants to pay on a later time and/or cancel the payment, the reservation will still be in place, as "Not paid". You should manually set the reservation as "Paid", eventually.',
                'team-booking'
            )
        ));
        $element->addAlert(__('Logged-in Administrators will always skip any payment process', 'team-booking'));
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }


    /**
     * The service's redirect panel
     *
     * @return Framework\Panel
     */
    private function getGeneralRedirect()
    {
        $panel = new Framework\Panel(ucfirst(__('Redirect and conversion tracking', 'team-booking')));
        // Redirect
        $element = new Framework\PanelSettingRadios(__('Redirect', 'team-booking'));
        $element->addFieldname('event[redirect]');
        $element->addDescription(__('If active, the customer will be redirected to the specified URL after a successful reservation for this service. This is meant for tracking conversions. If an offsite payment gateway like PayPal is chosen, then this URL will override the one specified in the gateway settings and will be called after the payment (but if the customer decides not to be redirected, then an eventual conversion code will not be triggered).', 'team-booking'));
        $element->addOption(array(
            'label'   => __('No redirect', 'team-booking'),
            'value'   => 'no',
            'checked' => !$this->service->getSettingsFor('redirect')
        ));
        $description_element = new Framework\PanelSettingDescriptionWithTextfield(NULL);
        $description_element->addFieldname('event[redirect_url]');
        $description_element->setDefaultText($this->service->getRedirectUrl());
        $description_element->setPlaceholder('http://, https://');
        $description_element->setDisabled(!$this->service->getSettingsFor('redirect'));
        $element->addOption(array(
            'label'       => __('Redirect to URL', 'team-booking'),
            'value'       => 'yes',
            'checked'     => $this->service->getSettingsFor('redirect'),
            'description' => $description_element
        ));
        $element->addAlert(__(
            "About payments: when redirect is active, while the immediate and later payment settings will work just fine, the customer's discretional payment setting otherwise won't work if selected, so no payment option will be presented to the customer after the reservation.",
            'team-booking'
        ));
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }


    /**
     * The service's slot appearance panel
     *
     * @return Framework\Panel
     */
    private function getGeneralSlotsAppearance()
    {
        $panel = new Framework\Panel(ucfirst($this->service->getClass() !== 'unscheduled' ? __('Frontend slot settings', 'team-booking') : __('Frontend settings', 'team-booking')));

        // Show reservations left
        if ($this->service->getClass() === 'event') {
            $element = new Framework\PanelSettingRadios(__('Show tickets left', 'team-booking'));
            $element->addFieldname('event[show_reservations_left]');
            $element->addOption(array(
                'label'   => __('Yes', 'team-booking'),
                'value'   => 1,
                'checked' => $this->service->getSettingsFor('show_tickets_left') && $this->service->getSettingsFor('show_tickets_left_threeshold') < 1
            ));
            $element->addOption(array(
                'label'   => __('No', 'team-booking'),
                'value'   => 0,
                'checked' => !$this->service->getSettingsFor('show_tickets_left')
            ));
            $description_element = new Framework\PanelSettingDescriptionWithNumber(NULL);
            $description_element->addFieldname('event[show_tickets_left_threeshold_value]');
            $description_element->setDefaultValue($this->service->getSettingsFor('show_tickets_left_threeshold'));
            $description_element->setMin(0);
            $description_element->setStep(1);
            $description_element->setDisabled(!($this->service->getSettingsFor('show_tickets_left') && $this->service->getSettingsFor('show_tickets_left_threeshold') > 0));
            $element->addOption(array(
                'label'       => __('Yes under this threshold', 'team-booking'),
                'value'       => 'under_threeshold',
                'description' => $description_element,
                'checked'     => $this->service->getSettingsFor('show_tickets_left') && $this->service->getSettingsFor('show_tickets_left_threeshold') > 0
            ));
            $panel->addElement($element);
        }

        // Show sold-out slots
        if ($this->service->getClass() !== 'unscheduled') {
            if ($this->service->getClass() === 'event') {
                $element = new Framework\PanelSettingYesOrNo(__('Show sold-out slots', 'team-booking'));
            } else {
                $element = new Framework\PanelSettingYesOrNo(__('Show booked slots', 'team-booking'));
            }
            $element->addFieldname('event[show_soldout]');
            $element->setState($this->service->getSettingsFor('show_soldout'));
            $panel->addElement($element);
        }

        // Show attendees
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Show slot attendees', 'team-booking'));
            $element->addFieldname('event[show_slot_attendees]');
            $element->addDescription(
                __('Choose to display a list of the attendees or not in the frontend, and which data it should include', 'team-booking')
            );
            $element->addOption(array(
                'label'   => __("Don't show", 'team-booking'),
                'value'   => 'no',
                'checked' => $this->service->getSettingsFor('show_attendees') === 'no'
            ));
            $element->addOption(array(
                'label'   => __('Show the names', 'team-booking'),
                'value'   => 'name',
                'checked' => $this->service->getSettingsFor('show_attendees') === 'name'
            ));
            $element->addOption(array(
                'label'   => __('Show the e-mail addresses', 'team-booking'),
                'value'   => 'email',
                'checked' => $this->service->getSettingsFor('show_attendees') === 'email'
            ));
            $element->addOption(array(
                'label'   => __('Show the names and the e-mail addresses', 'team-booking'),
                'value'   => 'name_email',
                'checked' => $this->service->getSettingsFor('show_attendees') === 'name_email'
            ));
            $element->addAlert(__('Please carefully consider any privacy-related implications', 'team-booking'));
            $panel->addElement($element);
        }
        // How to treat discarded free slots
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('How to treat discarded free slots', 'team-booking'));
            $element->addFieldname('event[treat_discarded_free_slots]');
            $element->addDescription(
                __('Discarded free slots (as a result of overlapping events under certain ovelapping settings, or in the context of a multiple services container) can be simply discarded (i.e. not shown) or shown as booked/sold-out.', 'team-booking')
            );
            $element->addOption(array(
                'label'   => __("Don't show", 'team-booking'),
                'value'   => 'hide',
                'checked' => $this->service->getSettingsFor('treat_discarded_free_slots') === 'hide'
            ));
            $element->addOption(array(
                'label'   => __('Show them as booked/sold-out', 'team-booking'),
                'value'   => 'booked',
                'checked' => $this->service->getSettingsFor('treat_discarded_free_slots') === 'booked'
            ));
            $panel->addElement($element);
        }

        // Show start/end times
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Show start/end times', 'team-booking'));
            $element->addFieldname('event[show_times]');
            $element->addOption(array(
                'label'   => __('Show start/end', 'team-booking'),
                'value'   => 'yes',
                'checked' => $this->service->getSettingsFor('show_times') === 'yes'
            ));
            $element->addOption(array(
                'label'   => __("Don't show", 'team-booking'),
                'value'   => 'no',
                'checked' => $this->service->getSettingsFor('show_times') === 'no'
            ));
            $element->addOption(array(
                'label'   => __('Show start time only', 'team-booking'),
                'value'   => 'start_time_only',
                'checked' => $this->service->getSettingsFor('show_times') === 'start_time_only'
            ));
            $panel->addElement($element);
        }

        // Show coworker's name
        $element = new Framework\PanelSettingYesOrNo(__("Show coworker's name", 'team-booking'));
        $element->addFieldname('event[show_coworker]');
        $element->setState($this->service->getSettingsFor('show_coworker'));
        $panel->addElement($element);

        // Show service's name
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingYesOrNo(__("Show service's name", 'team-booking'));
            $element->addFieldname('event[show_service_name]');
            $element->setState($this->service->getSettingsFor('show_service_name'));
            $panel->addElement($element);
        }

        // Show coworker's profile page link
        $element = new Framework\PanelSettingYesOrNo(__("Show coworker's profile page link", 'team-booking'));
        $element->addDescription(__("If yes, and if Coworker's name is shown, then it becomes a link pointing to Coworker's profile page. You can set the profile page for each of your Coworkers in the Manage Coworkers tab.", 'team-booking'));
        $element->addFieldname('event[show_coworker_url]');
        $element->setState($this->service->getSettingsFor('show_coworker_url'));
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }

    /**
     * The service's approval system panel
     *
     * @return Framework\Panel
     */
    private function getGeneralApproveDeny()
    {
        $panel = new Framework\Panel(ucfirst(__('Approval settings', 'team-booking')));

        // Approval requirement
        $element = new Framework\PanelSettingRadios(__('Approval requirement', 'team-booking'));
        $element->addFieldname('event[approve_rule]');
        $element->addDescription(__(
            'If approval is required, then the reservation will be shown in the overview panel in "approval required" status. No actions like sending confirmation e-mail, or updating Google Calendar will be taken, until the reservation is confirmed.',
            'team-booking'
        ));
        $element->addOption(array(
            'label'   => __('Do not require approval', 'team-booking'),
            'value'   => 'none',
            'checked' => (int)('none' === $this->service->getSettingsFor('approval_rule'))
        ));
        if ($this->service->getSettingsFor('payment') !== 'later') {
            $element->addAlert(__('you can activate approval only if the "payment must be done" setting for this service is set to "later"!', 'team-booking'));
        } else {
            $element->addOption(array(
                'label'   => __('Require Admin approval', 'team-booking'),
                'value'   => 'admin',
                'checked' => (int)('admin' === $this->service->getSettingsFor('approval_rule'))
            ));
            $element->addOption(array(
                'label'   => __('Require Coworker approval', 'team-booking'),
                'value'   => 'coworker',
                'checked' => (int)('coworker' === $this->service->getSettingsFor('approval_rule'))
            ));
        }
        $panel->addElement($element);

        // Until approval
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Until approval:', 'team-booking'));
            $element->addFieldname('event[approve_until]');
            $element->addOption(array(
                'label'   => __('keep the slot/tickets free (could lead to overbooking)', 'team-booking'),
                'value'   => 'yes',
                'checked' => $this->service->getSettingsFor('free_until_approval')
            ));
            $element->addOption(array(
                'label'   => __('set the slot/tickets in a booked status', 'team-booking'),
                'value'   => 'no',
                'checked' => !$this->service->getSettingsFor('free_until_approval')
            ));
            $panel->addElement($element);
        }

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }


    /**
     * The service's cancellation settings panel
     *
     * @return Framework\Panel
     */
    private function getCancellationSettings()
    {
        $panel = new Framework\Panel(ucfirst(__('Cancellation settings', 'team-booking')));

        // Allow customer's cancellation
        $element = new Framework\PanelSettingYesOrNo(__('Allow cancellation by customer', 'team-booking'));
        $element->addDescription(__('Allow the customer to cancel a reservation for this service. If yes, customers will be able to cancel their reservations for this service either by the e-mail cancellation link (if provided) or by the page where [tb-reservations] shortcode is placed (in which case they must be logged in)', 'team-booking'));
        $element->addFieldname('event[allow_customer_cancellation]');
        $element->setState($this->service->getSettingsFor('customer_cancellation'));
        $panel->addElement($element);

        // Allow customer to left a reason for the cancellation
        $element = new Framework\PanelSettingYesOrNo(__('Allow cancellation reason', 'team-booking'));
        $element->addDescription(__('Allow the customer to left a reason for the cancellation.', 'team-booking'));
        $element->addFieldname('event[allow_customer_cancellation_reason]');
        $element->setState($this->service->getSettingsFor('cancellation_reason_allowed'));
        $panel->addElement($element);

        // Cancellation time span
        $element = new Framework\PanelSettingTimespanSelector(__('Cancellation time span', 'team-booking'));
        $element->addFieldname('event[allow_customer_cancellation_timespan]');
        $element->addDescription(__("Choose a time limit, relative to the reservation's start time, after which the cancellation will be not possible anymore by the customer.", 'team-booking'));
        $element->setShowDays(TRUE);
        $element->setDaysLabel(__('days', 'team-booking'));
        $element->setHoursLabel(__('hours', 'team-booking'));
        $element->setMinsLabel(__('minutes', 'team-booking'));
        $selected_days = floor($this->service->getSettingsFor('cancellation_allowed_until') / DAY_IN_SECONDS);
        $element->setSelectedDays($selected_days);
        $selected_hours = floor(($this->service->getSettingsFor('cancellation_allowed_until') - $selected_days * DAY_IN_SECONDS) / HOUR_IN_SECONDS);
        $element->setSelectedHours($selected_hours);
        $element->setSelectedMins(ceil($this->service->getSettingsFor('cancellation_allowed_until') - $selected_hours * HOUR_IN_SECONDS - $selected_days * DAY_IN_SECONDS) / MINUTE_IN_SECONDS);
        $panel->addElement($element);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }


    /**
     * The service's access settings panel
     *
     * @return Framework\Panel
     */
    private function getGeneralAccess()
    {
        $panel = new Framework\Panel(ucfirst(__('Access settings', 'team-booking')));

        // Who can make a reservation
        $element = new Framework\PanelSettingRadios(__('Who can make a reservation', 'team-booking'));
        $element->addFieldname('event[allow_reservation]');
        $element->addDescription(__(
            'If "Logged user only" is selected, then an advice with link to registration page will be shown.
            The default link can be changed in the "Core settings" tab.',
            'team-booking'
        ));
        $element->addOption(array(
            'label'       => __('Everyone', 'team-booking'),
            'label_title' => __('Everyone', 'team-booking'),
            'value'       => 'everyone',
            'checked'     => $this->service->getSettingsFor('bookable') === 'everyone'
        ));
        $element->addOption(array(
            'label'       => __('Logged users only', 'team-booking'),
            'label_title' => __('Logged users only', 'team-booking'),
            'value'       => 'logged_only',
            'checked'     => $this->service->getSettingsFor('bookable') === 'logged_only'
        ));
        $element->addOption(array(
            'label'       => __('Nobody (read-only)', 'team-booking'),
            'label_title' => __('Nobody (read-only)', 'team-booking'),
            'value'       => 'nobody',
            'checked'     => $this->service->getSettingsFor('bookable') === 'nobody'
        ));
        $panel->addElement($element);

        // Max reservations per logged user
        if ($this->service->getClass() === 'unscheduled') {
            $element = new Framework\PanelSettingNumber(__('Max reservations per logged user', 'team-booking'));
            $element->addFieldname('event[max_reservations_logged_user]');
            $element->addDescription(__(
                "If the reservations are allowed to logged users only, then you can also allow a limited number of them. The book now button will show how many are left. If the limit is reached, the logged user can't proceed. The counter decreases if the reservations are deleted.",
                'team-booking'
            ));
            $element->setMin(0);
            $element->setValue($this->service->getMaxReservationsUser());
            $element->setFieldDescription('0 = ' . __('endless', 'team-booking'));
            $panel->addElement($element);
        }

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_update_booking_type');
        $panel->addElement($element);

        return $panel;
    }

    /**
     * The form elements panel
     *
     * @return Framework\Panel
     */
    private function getFormElements()
    {
        $panel = new Framework\Panel(ucfirst(__('Elements', 'team-booking')));
        $element = new Framework\PanelSettingWildcard(NULL);
        ob_start();
        ?>
        <div class="tbk-custom-fields-dock">
            <a class="tb-add-custom-field" data-field="text"
               data-serviceid="<?= $this->service->getId() ?>">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('Text Field', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-textfield-sprite.png');"></span>
            </a>
            <a class="tb-add-custom-field" data-field="select"
               data-serviceid="<?= $this->service->getId() ?>" href="#">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('Select', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-select-sprite.png');"></span>
            </a>
            <a class="tb-add-custom-field" data-field="textarea"
               data-serviceid="<?= $this->service->getId() ?>" href="#">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('Textarea', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-textarea-sprite.png');"></span>
            </a>
            <a class="tb-add-custom-field" data-field="checkbox"
               data-serviceid="<?= $this->service->getId() ?>" href="#">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('Checkbox', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-checkbox-sprite.png');"></span>
            </a>
            <a class="tb-add-custom-field" data-field="radio"
               data-serviceid="<?= $this->service->getId() ?>" href="#">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('Radio Group', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-radio-sprite.png');"></span>
            </a>
            <a class="tb-add-custom-field" data-field="file"
               data-serviceid="<?= $this->service->getId() ?>" href="#">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('File Upload', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-file-sprite.png');"></span>
            </a>
            <a class="tb-add-custom-field" data-field="paragraph"
               data-serviceid="<?= $this->service->getId() ?>" href="#">
                <span class="dashicons dashicons-plus"></span>
                <?= esc_html__('Paragraph', 'team-booking') ?>
                <span class="sprite"
                      style="background-image: url('<?= TEAMBOOKING_URL ?>images/form-paragraph-sprite.png');"></span>
            </a>
        </div>
        <?php

        $element->addContent(ob_get_clean());
        $element->appendTo($panel);

        return $panel;
    }


    /**
     * The form fields editor panel
     *
     * @return Framework\Panel
     */
    private function getFormFieldsEditor()
    {
        $panel = new Framework\Panel(ucfirst(__('Reservation form', 'team-booking')));
        $element = new Framework\PanelSettingWildcard(NULL);
        ob_start();
        echo $this->getMozCheckboxCSSTarget('EF6060');
        $form_fields = Forms::get($this->service->getForm());
        if (empty($form_fields)) {
            ?>
            <div class='tb-no-custom-fields'>
                <?= esc_html__('No custom fields for this service yet.', 'team-booking') ?>
            </div>
            <?php
        }
        ?>
        <ul class="tbk-form-fields-list sortable">
            <?php
            foreach ($form_fields as $field) {
                if (is_object($field)) {
                    ?>
                    <li>
                        <?= Mappers\adminFormFieldsMapper($field, $this->service->getId()) ?>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php
        $element->addContent(ob_get_clean());
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The informative panel about slot commands
     *
     * @return Framework\Panel
     */
    private function getSlotCommands()
    {
        $panel = new Framework\Panel(ucfirst(__('Slot commands', 'team-booking')));

        $element = new Framework\PanelSettingWildcard(NULL);
        $element->addDescription(__('As a service provider, you can write some handy commands directly into the Google Calendar event title to set some properties of that particular slot.', 'team-booking'));
        $element->addToDescription('<br>', FALSE);
        $element->addToDescription(sprintf(esc_html__('Those slot commands must follow the %s delimiter and they must be put at the end of the event title.', 'team-booking'), '<code>>></code>'), FALSE);
        $element->appendTo($panel);

        $element = new Framework\PanelSettingWildcard(__('Set a slot as booked or full', 'team-booking'));
        $element->addDescription(
            esc_html__('Both commands have the effect of put the slot in a booked status', 'team-booking') . '<br><br>'
            . '<code>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . '<strong> >> booked</strong></code>' . '<br><br>'
            . '<code>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . '<strong> >> full</strong></code>'
            , FALSE
        );
        $element->appendTo($panel);

        if (Functions\isAdmin() || Functions\getSettings()->allowSlotCommands()) {
            $element = new Framework\PanelSettingWildcard(__('Override the service price', 'team-booking'));
            $element->addDescription(
                esc_html__('The command will change the service price for that slot only. For instance, if the service price is 20$ and we need a given slot to be 10$ we can use the following (please note that the currency must not be specified)', 'team-booking') . '<br><br>'
                . '<code>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . '<strong> >> price=10</strong></code>'
                , FALSE
            );
            $element->appendTo($panel);

            $element = new Framework\PanelSettingWildcard(__('Set the slot as read-only', 'team-booking'));
            $element->addDescription(
                esc_html__("The command will put the slot in a read-only mode so it can't be booked.", 'team-booking') . '<br><br>'
                . '<code>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . '<strong> >> readonly</strong></code>'
                , FALSE
            );
            $element->appendTo($panel);

            $element = new Framework\PanelSettingWildcard(__('Mixing the commands', 'team-booking'));
            $element->addDescription(
                esc_html__('You can use more than one command, just write them as comma-separated, after the delimiter.', 'team-booking') . '<br><br>'
                . '<code>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . '<strong> >> price=40, readonly</strong></code>'
                , FALSE
            );
            $element->appendTo($panel);

        }

        return $panel;
    }

    /**
     * The informative panel about availability modes
     *
     * @return Framework\Panel
     */
    private function getAvailabilityModes()
    {
        $colors = array(
            0  => '56b378',
            1  => 'a4bdfc',
            2  => '7ae7bf',
            3  => 'dbadff',
            4  => 'ff887c',
            5  => 'fbd75b',
            6  => 'ffb878',
            7  => '46d6db',
            8  => 'e1e1e1',
            9  => '5484ed',
            10 => '51b749',
            11 => 'dc2127'
        );

        $additional_data = $this->coworker->getCustomEventSettings($this->service->getId())->getAdditionalEventTitleData();
        $booked_title = $this->coworker->getCustomEventSettings($this->service->getId())->getAfterBookedTitle();
        $booked_title_2 = $booked_title;
        $to_be_appended = '';
        $to_be_appended_2 = '';
        if ($additional_data['customer']['full_name']) {
            $to_be_appended .= ' John Doe';
            $to_be_appended_2 .= 'Pam Reeves';
        }
        if ($additional_data['customer']['email']) {
            $to_be_appended .= ' john@doe.com';
            $to_be_appended_2 .= ' pam@reeves.com';
        }
        if ($additional_data['customer']['phone']) {
            $to_be_appended .= ' +1-202-555-0197';
            $to_be_appended_2 .= ' +1-617-555-0197';
        }
        if (!empty($to_be_appended)) {
            $booked_title .= ' || ' . trim($to_be_appended);
            $booked_title_2 .= ' || ' . trim($to_be_appended_2);
        }
        $panel = new Framework\Panel(ucfirst(__('Availability modes', 'team-booking')));

        $element = new Framework\PanelSettingWildcard(NULL);
        $element->addDescription(__('The three modes of placing availability in Google Calendar are described below. You can use all of them, but please note that the Container Mode is NOT available if the slot duration is inherited from Google Calendar event.', 'team-booking'));
        $element->appendTo($panel);

        $element = new Framework\PanelSettingWildcard(__('Slot mode', 'team-booking'));
        $element->addDescription(
            esc_html__('Event name', 'team-booking') . ' = '
            . '<code><strong>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . '</strong></code>', FALSE
        );
        $row = new Framework\Row();
        $column = Framework\Column::ofWidth(5);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding:10px;">
                <div style="background: #928686;
                            min-height: 35px;
                            border: 1px solid #5a5a5a;
                            color: white;
                            font-size: 0.6vw;
                            line-height: 0.7vw;
                            padding: 4px;">
                    11:00 - 12:00<br>' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
            . '</div></div>'
        ));
        $column->appendTo($row);
        $column = Framework\Column::ofWidth(2);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding-right: 0;padding-left: 0;text-align: center;">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
             </div>'
        ));
        $column->appendTo($row);
        $column = Framework\Column::ofWidth(5);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding:10px;">
                <div style="background: #' . $colors[ $this->coworker->getCustomEventSettings($this->service->getId())->getBookedEventColor() ] . ';
                            min-height: 35px;
                            border: 1px solid #5a5a5a;
                            color: white;
                            font-size: 0.6vw;
                            line-height: 0.7vw;
                            padding: 4px;">
                    11:00 - 12:00<br>'
            . ($this->service->getClass() === 'event'
                ? $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
                : $booked_title)
            . '</div></div>'
        ));
        $column->appendTo($row);
        $element->addContent($row);
        $element->appendTo($panel);

        $element = new Framework\PanelSettingWildcard(__('Container mode', 'team-booking'));
        $element->addDescription(
            esc_html__('Event name', 'team-booking') . ' = <code><strong>'
            . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
            . '</strong> container</code>', FALSE
        );
        $row = new Framework\Row();
        $column = Framework\Column::ofWidth(5);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding:10px;">
                <div style="background: #928686;
                            min-height: 180px;
                            border: 1px solid #5a5a5a;
                            color: white;
                            font-size: 0.6vw;
                            line-height: 0.7vw;
                            padding: 4px;">
                    14:00 - 18:00<br>'
            . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . ' container'
            . '</div></div>'
        ));
        $column->appendTo($row);
        $column = Framework\Column::ofWidth(2);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding-right: 0;padding-left: 0;text-align: center;">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
             </div>'
        ));
        $column->appendTo($row);
        $column = Framework\Column::ofWidth(5);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding:10px;">
                <div style="background: #928686;
                            min-height: 180px;
                            border: 1px solid #5a5a5a;
                            color: white;
                            font-size: 0.6vw;
                            line-height: 0.7vw;
                            padding: 4px;">
                    14:00 - 18:00<br>'
            . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . ' container'
            . '<div class="tbk-column tbk-span-12" style="position:absolute;">
                        <div class="tbk-content" style="padding:10px;">
                            <div style="background: #' . $colors[ $this->coworker->getCustomEventSettings($this->service->getId())->getBookedEventColor() ] . ';
                                        min-height: 35px;
                                        border: 1px solid #5a5a5a;
                                        color: white;
                                        font-size: 0.6vw;
                                        line-height: 0.7vw;
                                        padding: 4px;">
                                15:00 - 16:00<br>'
            . ($this->service->getClass() === 'event'
                ? $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
                : $booked_title)
            . '</div></div>
                    </div>
                    <div class="tbk-column tbk-span-12" style="position:absolute;top: 100px;">
                        <div class="tbk-content" style="padding:10px;">
                            <div style="background: #' . $colors[ $this->coworker->getCustomEventSettings($this->service->getId())->getBookedEventColor() ] . ';
                                        min-height: 35px;
                                        border: 1px solid #5a5a5a;
                                        color: white;
                                        font-size: 0.6vw;
                                        line-height: 0.7vw;
                                        padding: 4px;">
                                16:15 - 17:15<br>'
            . ($this->service->getClass() === 'event'
                ? $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
                : $booked_title_2)
            . '</div></div>
                    </div>
                </div>
            </div>'
        ));
        $column->appendTo($row);
        $element->addContent($row);
        $element->appendTo($panel);

        $element = new Framework\PanelSettingWildcard(__('Container mode (mutually exclusive multiple services)', 'team-booking'));
        $element->addDescription(
            __('Event name', 'team-booking') . ' = <code>'
            . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
            . ' + '
            . 'Service 2 + ... + container</code>'
            , FALSE
        );
        $row = new Framework\Row();
        $column = Framework\Column::ofWidth(5);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding:10px;">
                <div style="background: #928686;
                            min-height: 180px;
                            border: 1px solid #5a5a5a;
                            color: white;
                            font-size: 0.6vw;
                            line-height: 0.7vw;
                            padding: 4px;">
                    14:00 - 18:00<br>
                    ' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . ' + Service 2 + Service 3 container
                </div>
            </div>'
        ));
        $column->appendTo($row);
        $column = Framework\Column::ofWidth(2);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding-right: 0;padding-left: 0;text-align: center;">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
             </div>'
        ));
        $column->appendTo($row);
        $column = Framework\Column::ofWidth(5);
        $column->addElement(Framework\ElementFrom::content(
            '<div class="tbk-content" style="padding:10px;">
                <div style="background: #928686;
                            min-height: 180px;
                            border: 1px solid #5a5a5a;
                            color: white;
                            font-size: 0.6vw;
                            line-height: 0.7vw;
                            padding: 4px;">
                    14:00 - 18:00<br>
                    ' . $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle() . ' + Service 2 + Service 3 container
                    <div class="tbk-column tbk-span-12" style="position:absolute;">
                        <div class="tbk-content" style="padding:10px;">
                            <div style="background: #' . $colors[ $this->coworker->getCustomEventSettings($this->service->getId())->getBookedEventColor() ] . ';
                                        min-height: 35px;
                                        border: 1px solid #5a5a5a;
                                        color: white;
                                        font-size: 0.6vw;
                                        line-height: 0.7vw;
                                        padding: 4px;">
                                15:00 - 16:00<br>'
            . ($this->service->getClass() === 'event'
                ? $this->coworker->getCustomEventSettings($this->service->getId())->getLinkedEventTitle()
                : $booked_title)
            . '</div>
                        </div>
                    </div>
                    <div class="tbk-column tbk-span-12" style="position:absolute;top: 110px;">
                        <div class="tbk-content" style="padding:10px;">
                            <div style="background: #' . $colors[ 7 === $this->coworker->getCustomEventSettings($this->service->getId())->getBookedEventColor() ? 6 : 7 ] . ';
                                        min-height: 55px;
                                        border: 1px solid #5a5a5a;
                                        color: white;
                                        font-size: 0.6vw;
                                        line-height: 0.7vw;
                                        padding: 4px;">
                                16:15 - 17:15<br>
                                New reservation for Service 3
                            </div>
                        </div>
                    </div>
                </div>
            </div>'
        ));
        $column->appendTo($row);
        $element->addContent($row);
        $element->appendTo($panel);

        return $panel;
    }


    /**
     * The Google Calendar settings panel
     *
     * @return Framework\Panel
     */
    private function getGCalSettingsContent()
    {
        $custom_booking_type_settings = $this->coworker->getCustomEventSettings($this->service->getId());
        $panel = new Framework\Panel(ucfirst(__('Personal availability settings', 'team-booking')));

        // Event title (free slot)
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingText(__('Event title (free slot)', 'team-booking'));
            $element->addFieldname('data[linked_event_title]');
            $element->addDescription(__('Case insensitive. Events in your Google Calendar must have this title in order to become free slots for the service.', 'team-booking'));
            $element->addDefaultValue($custom_booking_type_settings->getLinkedEventTitle());
            $element->appendTo($panel);
        }

        // Event title (booked slot)
        if ($this->service->getClass() === 'appointment') {
            $element = new Framework\PanelSettingText(__('Event title (booked slot)', 'team-booking'));
            $element->addFieldname('data[booked_title]');
            $element->addDescription(__('Case insensitive. A free slot in your Google Calendar will get this title, once booked. If you write this title manually to an event, it will appear as a booked slot.', 'team-booking'));
            $element->addDefaultValue($custom_booking_type_settings->getAfterBookedTitle());
            $element->appendTo($panel);

            $additional_data = $custom_booking_type_settings->getAdditionalEventTitleData();
            $element = new Framework\PanelSettingCheckboxes(__('Event title additional dynamic data (booked slot)', 'team-booking'));
            $element->addDescription(__("Additional customer's data to be appended to the Event title (booked slot). This additional data, if available, will be preceded by a '||' delimiter.", 'team-booking'));
            $element->addCheckbox(array(
                'label'     => __("Customer's name", 'team-booking'),
                'checked'   => $additional_data['customer']['full_name'],
                'fieldname' => 'data[booked_title_additional_data_customer_name]'
            ));
            $element->addCheckbox(array(
                'label'     => __("Customer's e-mail", 'team-booking'),
                'checked'   => $additional_data['customer']['email'],
                'fieldname' => 'data[booked_title_additional_data_customer_email]'
            ));
            $element->addCheckbox(array(
                'label'     => __("Customer's phone number", 'team-booking'),
                'checked'   => $additional_data['customer']['phone'],
                'fieldname' => 'data[booked_title_additional_data_customer_phone]'
            ));
            $element->appendTo($panel);
        }

        // When reservations should be closed?
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingDoubleSelector(__('When reservations should be closed?', 'team-booking'));
            $element->addFieldname2('data[min_time]', 'data[min_time_reference]');
            $element->addDescription(__('Choose the minimum time to book prior to the slot start or end time', 'team-booking'));
            $element->setSelected2($custom_booking_type_settings->getMinTime(), $custom_booking_type_settings->getMinTimeReference());
            $element->addOption2('PT0M', sprintf(__('%d minutes before', 'team-booking'), 0), 'data[min_time]');
            $element->addOption2('PT10M', sprintf(__('%d minutes before', 'team-booking'), 10), 'data[min_time]');
            $element->addOption2('PT30M', sprintf(__('%d minutes before', 'team-booking'), 30), 'data[min_time]');
            $element->addOption2('PT1H', sprintf(_n('%d hour before', '%d hours before', 1, 'team-booking'), 1), 'data[min_time]');
            $element->addOption2('PT2H', sprintf(_n('%d hour before', '%d hours before', 2, 'team-booking'), 2), 'data[min_time]');
            $element->addOption2('PT3H', sprintf(_n('%d hour before', '%d hours before', 3, 'team-booking'), 3), 'data[min_time]');
            $element->addOption2('PT6H', sprintf(_n('%d hour before', '%d hours before', 6, 'team-booking'), 6), 'data[min_time]');
            $element->addOption2('PT12H', sprintf(_n('%d hour before', '%d hours before', 12, 'team-booking'), 12), 'data[min_time]');
            $element->addOption2('P1D', sprintf(_n('%d hour before', '%d hours before', 24, 'team-booking'), 24), 'data[min_time]');
            $element->addOption2('P1Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 1, 'team-booking'), 1), 'data[min_time]');
            $element->addOption2('P2Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 2, 'team-booking'), 2), 'data[min_time]');
            $element->addOption2('P3Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 3, 'team-booking'), 3), 'data[min_time]');
            $element->addOption2('P4Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 4, 'team-booking'), 4), 'data[min_time]');
            $element->addOption2('P5Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 5, 'team-booking'), 5), 'data[min_time]');
            $element->addOption2('P6Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 6, 'team-booking'), 6), 'data[min_time]');
            $element->addOption2('P7Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 7, 'team-booking'), 7), 'data[min_time]');
            $element->addOption2('P10Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 10, 'team-booking'), 10), 'data[min_time]');
            $element->addOption2('P15Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 15, 'team-booking'), 15), 'data[min_time]');
            $element->addOption2('P30Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 30, 'team-booking'), 30), 'data[min_time]');
            $element->addOption2('P60Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 60, 'team-booking'), 60), 'data[min_time]');
            $element->addOption2('P90Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 90, 'team-booking'), 90), 'data[min_time]');
            $element->addOption2('P120Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 120, 'team-booking'), 120), 'data[min_time]');
            $element->addOption2('P150Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 150, 'team-booking'), 150), 'data[min_time]');
            $element->addOption2('P180Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 180, 'team-booking'), 180), 'data[min_time]');
            $element->addOption2('start', __('slot start time', 'team-booking'), 'data[min_time_reference]');
            $element->addOption2('end', __('slot end time', 'team-booking'), 'data[min_time_reference]');
            $element->appendTo($panel);
        }

        // When reservations should be opened?
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingSelector(__('When reservations should be opened?', 'team-booking'));
            $element->addFieldname('data[open_time]');
            $element->addDescription(__('When we are closer than the following time span to the start time of the event, the slots begin to appear. If "Immediately", they appear right after their creation. If not "Immediately", the value must be greater than the previous one!', 'team-booking'));
            $element->setSelected($custom_booking_type_settings->getOpenTime());
            $element->addOption('0', __('Immediately', 'team-booking'));
            $element->addOption('P1D', sprintf(_n('%d hour before', '%d hours before', 24, 'team-booking'), 24));
            $element->addOption('P1Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 1, 'team-booking'), 1));
            $element->addOption('P2Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 2, 'team-booking'), 2));
            $element->addOption('P3Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 3, 'team-booking'), 3));
            $element->addOption('P4Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 4, 'team-booking'), 4));
            $element->addOption('P5Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 5, 'team-booking'), 5));
            $element->addOption('P6Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 6, 'team-booking'), 6));
            $element->addOption('P7Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 7, 'team-booking'), 7));
            $element->addOption('P10Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 10, 'team-booking'), 10));
            $element->addOption('P15Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 15, 'team-booking'), 15));
            $element->addOption('P30Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 30, 'team-booking'), 30));
            $element->addOption('P60Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 60, 'team-booking'), 60));
            $element->addOption('P90Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 90, 'team-booking'), 90));
            $element->addOption('P120Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 120, 'team-booking'), 120));
            $element->addOption('P150Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 150, 'team-booking'), 150));
            $element->addOption('P180Dmid', sprintf(_n('%d day before (until midnight)', '%d days before (until midnight)', 180, 'team-booking'), 180));
            $element->appendTo($panel);
        }

        // Slot duration rule
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Slot duration rule', 'team-booking'));
            $element->addDescription(__('If inherited, the container mode will not be available', 'team-booking'));
            $element->addFieldname('data[duration_rule]');
            if ($this->service->getSettingsFor('slot_duration') !== 'coworker') {
                $service_hours = floor($this->service->getSlotDuration() / HOUR_IN_SECONDS);
                $service_minutes = ceil($this->service->getSlotDuration() - $service_hours * HOUR_IN_SECONDS) / MINUTE_IN_SECONDS;
                if ($this->service->getSettingsFor('slot_duration') === 'fixed') {
                    $rule_string = esc_html__('Fixed', 'team-booking') . ' ' . $service_hours . 'h ' . $service_minutes . 'm';
                }
                if ($this->service->getSettingsFor('slot_duration') === 'inherited') {
                    $rule_string = esc_html__('Inherited from Google Calendar event', 'team-booking');
                }
                ob_start();
                ?>
                <div class="ui mini blue label"><?= $rule_string ?>
                    <div class="detail"><?= '(' . esc_html__('Set by Admin', 'team-booking') . ')' ?></div>
                </div>
                <?php
                $element->addToDescription(ob_get_clean(), FALSE);
            } else {
                $element->addOption(array(
                    'label'       => __('Inherited from Google Calendar event', 'team-booking'),
                    'label_title' => __('Inherited from Google Calendar event', 'team-booking'),
                    'value'       => 'inherited',
                    'checked'     => $custom_booking_type_settings->getDurationRule() === 'inherited'
                ));
                $label_element = new Framework\PanelSettingTimespanSelector(__('Fixed', 'team-booking'));
                $label_element->addFieldname('data[default_duration]');
                $label_element->isNested(TRUE);
                $label_element->setHoursLabel(__('hours', 'team-booking'));
                $label_element->setMinsLabel(__('minutes', 'team-booking'));
                $label_element->setSelectedHours(floor($custom_booking_type_settings->getFixedDuration() / HOUR_IN_SECONDS));
                $label_element->setSelectedMins(ceil($custom_booking_type_settings->getFixedDuration() - floor($custom_booking_type_settings->getFixedDuration() / HOUR_IN_SECONDS) * HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
                $element->addOption(array(
                    'label'       => $label_element,
                    'label_title' => __('Fixed', 'team-booking'),
                    'value'       => 'fixed',
                    'checked'     => (int)('fixed' === $custom_booking_type_settings->getDurationRule())
                ));
            }
            $element->appendTo($panel);
        }

        // Buffer duration between consecutive slots
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingTimespanSelector(__('Buffer between consecutive slots', 'team-booking'));
            $element->addFieldname('data[buffer]');
            $element->addDescription(__('This applies only inside container slots', 'team-booking'));
            $element->setHoursLabel(__('hours', 'team-booking'));
            $element->setMinsLabel(__('minutes', 'team-booking'));
            $element->setSelectedHours(floor($custom_booking_type_settings->getBufferDuration() / HOUR_IN_SECONDS));
            $element->setSelectedMins(ceil($custom_booking_type_settings->getBufferDuration() - floor($custom_booking_type_settings->getBufferDuration() / HOUR_IN_SECONDS) * HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
            $element->appendTo($panel);
        }

        // Buffer rule
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Buffer rule', 'team-booking'));
            $element->addFieldname('data[buffer_rule]');
            $element->addDescription(__('Choose how the buffer should be computed', 'team-booking'));
            $element->addOption(array(
                'label'       => __('Always computed between slots', 'team-booking'),
                'description' => __('The buffer is always computed between both free and booked slots inside a container interval. This is the default option and it leads to predictable slots distribution.', 'team-booking'),
                'value'       => 'always',
                'checked'     => $custom_booking_type_settings->getBufferDurationRule() === 'always'
            ));
            $element->addOption(array(
                'label'       => __('Not computed between free slots, computed in all the other cases', 'team-booking'),
                'description' => __('The buffer is only computed "around" a booked slot. This leads to a re-distribution of the free slots when a new reservation is made.', 'team-booking'),
                'value'       => 'after_reservation',
                'checked'     => $custom_booking_type_settings->getBufferDurationRule() === 'after_reservation'
            ));
            $element->appendTo($panel);
        }

        // Event color after reservation
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingColoredRadios(__('Event color after reservation', 'team-booking'));
            $element->addFieldname('data[booked_color]');
            $element->addDescription(__('Visual aid to distinguish between free and reserved slots in your Google Calendar', 'team-booking'));
            $element->setDefaultRadio(0, __('Calendar default color', 'team-booking'));
            $element->setSelected($custom_booking_type_settings->getBookedEventColor());
            $element->addRadio(1, 'a4bdfc');
            $element->addRadio(2, '7ae7bf');
            $element->addRadio(3, 'dbadff');
            $element->addRadio(4, 'ff887c');
            $element->addRadio(5, 'fbd75b');
            $element->addRadio(6, 'ffb878');
            $element->addRadio(7, '46d6db');
            $element->addRadio(8, 'e1e1e1');
            $element->addRadio(9, '5484ed');
            $element->addRadio(10, '51b749');
            $element->addRadio(11, 'dc2127');
            $element->appendTo($panel);
        }

        // Reminder
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Set a reminder for reserved slot', 'team-booking'));
            $element->addFieldname('data[reminder]');
            $element->addDescription(__('If active, you will receive a reminder by email for any reserved slot for this service.', 'team-booking'));
            $element->addOption(array(
                'label'       => __('No reminder', 'team-booking'),
                'label_title' => __('No reminder', 'team-booking'),
                'value'       => 0,
                'checked'     => $custom_booking_type_settings->getReminder() == 0
            ));
            $element->addOption(array(
                'label'       => sprintf(__('%d minutes before', 'team-booking'), 10),
                'label_title' => sprintf(__('%d minutes before', 'team-booking'), 10),
                'value'       => 10,
                'checked'     => $custom_booking_type_settings->getReminder() == 10
            ));
            $element->addOption(array(
                'label'       => sprintf(__('%d minutes before', 'team-booking'), 30),
                'label_title' => sprintf(__('%d minutes before', 'team-booking'), 30),
                'value'       => 30,
                'checked'     => $custom_booking_type_settings->getReminder() == 30
            ));
            $element->addOption(array(
                'label'       => sprintf(_n('%d hour before', '%d hours before', 1, 'team-booking'), 1),
                'label_title' => sprintf(_n('%d hour before', '%d hours before', 1, 'team-booking'), 1),
                'value'       => 60,
                'checked'     => $custom_booking_type_settings->getReminder() == 60
            ));
            $element->appendTo($panel);
        }

        // Add customer as guest
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingYesOrNo(__('Add customers as guests of the Google Calendar event', 'team-booking'));
            $element->addFieldname('data[add_customer_as_guest]');
            $element->addDescription(__(
                'If yes, the customer will be added as guest of the Google Calendar event. It will receive Google notifications according to your Google Calendar settings, a copy of the event will be created in his Google Calendar (if any) and he will be able to read the event description. He will not be able to see the guests list.',
                'team-booking'
            ));
            $element->setState($custom_booking_type_settings->addCustomerAsGuest());
            $element->addWarningDropcap(_('Very important'));
            $element->addAlert(__('According to personal data regulations you may be required to provide a full disclosure to the customer about where their data will be stored. You should be also able to erase such data at any time if requested. Please consult the site administrator before set this option in a way that customer personal data could be stored outside this site.', 'team-booking'));
            $element->appendTo($panel);
        }

        // Event description content
        if ($this->service->getClass() === 'event') {
            $element = new Framework\PanelSettingRadios(__('Google Calendar event description content', 'team-booking'));
            $element->addFieldname('data[event_description_content]');
            $element->addDescription(__(
                'Specify the content of the Google Calendar event description. If privacy is your concern, then keep in mind: this content will be visible by guests. If customers are being added as guests, then it will be visible by customers.',
                'team-booking'
            ));
            $element->addOption(array(
                'label'       => __('Leave it blank', 'team-booking'),
                'label_title' => __('Leave it blank', 'team-booking'),
                'value'       => 0,
                'checked'     => $custom_booking_type_settings->getEventDescriptionContent() === 0
            ));
            $element->addOption(array(
                'label'       => __('Leave it as is', 'team-booking'),
                'label_title' => __('Leave it as is', 'team-booking'),
                'value'       => 2,
                'checked'     => $custom_booking_type_settings->getEventDescriptionContent() === 2
            ));
            $element->addOption(array(
                'label'       => __("Customer's names, tickets, e-mail addresses and phone numbers if available", 'team-booking'),
                'label_title' => __("Customer's names, tickets, e-mail addresses and phone numbers if available", 'team-booking'),
                'value'       => 1,
                'checked'     => $custom_booking_type_settings->getEventDescriptionContent() === 1
            ));
            $element->addWarningDropcap(_('Very important'));
            $element->addAlert(__('According to personal data regulations you may be required to provide a full disclosure to the customer about where their data will be stored. You should be also able to erase such data at any time if requested. Please consult the site administrator before set this option in a way that customer personal data could be stored outside this site.', 'team-booking'));
            $element->appendTo($panel);
        }

        // Deal with unrelated events
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Overlapping with personal events', 'team-booking'));
            $element->addFieldname('data[deal_with_unrelated_events]');
            $element->addDescription(__(
                'If you have personal events in your Google Calendar(s), choose what to do with a free slot of this service, in case one of them overlaps.',
                'team-booking'
            ));
            $element->addOption(array(
                'label'       => __('Keep the free slot', 'team-booking'),
                'label_title' => __('Keep the free slot', 'team-booking'),
                'value'       => 0,
                'checked'     => $custom_booking_type_settings->dealWithUnrelatedEvents() == 0
            ));
            $element->addOption(array(
                'label'       => __('Discard the free slot', 'team-booking'),
                'label_title' => __('Discard the free slot', 'team-booking'),
                'value'       => 1,
                'checked'     => $custom_booking_type_settings->dealWithUnrelatedEvents() == 1
            ));
            $element->appendTo($panel);
        }

        // Deal with booked slots of the same service
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Overlapping with booked slots of the same service', 'team-booking'));
            $element->addFieldname('data[deal_with_booked_same]');
            $element->addDescription(__(
                'If your availability plan envisages overlapping availabilities, choose what to do with a free slot of this service when a booked slot of the same service overlaps.',
                'team-booking'
            ));
            $element->addOption(array(
                'label'       => __('Keep the free slot', 'team-booking'),
                'label_title' => __('Keep the free slot', 'team-booking'),
                'value'       => 0,
                'checked'     => $custom_booking_type_settings->dealWithBookedOfSameService() == 0
            ));
            $element->addOption(array(
                'label'       => __('Discard the free slot', 'team-booking'),
                'label_title' => __('Discard the free slot', 'team-booking'),
                'value'       => 1,
                'checked'     => $custom_booking_type_settings->dealWithBookedOfSameService() == 1
            ));
            $element->appendTo($panel);
        }

        // Deal with booked slots of the other services
        if ($this->service->getClass() !== 'unscheduled') {
            $element = new Framework\PanelSettingRadios(__('Overlapping with booked slots of other services', 'team-booking'));
            $element->addFieldname('data[deal_with_booked_others]');
            $element->addDescription(__(
                'If your availability plan envisages overlapping availabilities, choose what to do with a free slot of this service when a booked slot of any other service overlaps.',
                'team-booking'
            ));
            $element->addOption(array(
                'label'       => __('Keep the free slot', 'team-booking'),
                'label_title' => __('Keep the free slot', 'team-booking'),
                'value'       => 0,
                'checked'     => $custom_booking_type_settings->dealWithBookedOfOtherServices() == 0
            ));
            $element->addOption(array(
                'label'       => __('Discard the free slot', 'team-booking'),
                'label_title' => __('Discard the free slot', 'team-booking'),
                'value'       => 1,
                'checked'     => $custom_booking_type_settings->dealWithBookedOfOtherServices() == 1
            ));
            $element->appendTo($panel);
        }


        // Save changes
        if (Functions\isAdmin()) {
            $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_save_personal_event_settings', array(
                'name'  => esc_attr($this->STRING_APPLY_TO_ALL),
                'value' => 'team_booking_save_personal_event_settings_apply_to_all'
            ), array('data-msg' => esc_attr($this->STRING_APPLY_TO_ALL_WARNING)));
        } else {
            $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'team_booking_save_personal_event_settings');
        }
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @param $hex_color
     *
     * @return string
     */
    private function getMozCheckboxCSSTarget($hex_color)
    {
        ob_start();
        ?>
        <style type="text/css">
            @-moz-document url-prefix() {
            <?= "input[type='checkbox']._" . $hex_color ?> {
                outline: 2px solid<?= ' #'.$hex_color ?>;
            }
            }
        </style>
        <?php
        return ob_get_clean();
    }

}
