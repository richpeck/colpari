<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Admin,
    TeamBooking\Functions;

/**
 * Class Core
 *
 * @author VonStroheim
 */
class Core
{
    public $roles_all;
    public $roles_allowed;
    private $redirect_uri;
    private $js_origins;
    private $settings;

    public function __construct()
    {
        $this->settings = Functions\getSettings();
        $this->redirect_uri = admin_url() . 'admin-ajax.php?action=teambooking_oauth_callback';
        $this->js_origins = strtolower(substr(site_url(), 0, strpos(site_url(), '/'))) . '//' . $_SERVER['HTTP_HOST'];
    }

    /**
     * The core settings page
     *
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            $row = new Framework\Row();
            $column = Framework\Column::ofWidth(5);
            $column->addElement($this->getGoogleProjectSettings());
            $column->addElement($this->getQuickGuide());
            $column->addElement($this->getTokenTable());
            $column->appendTo($row);
            $column = Framework\Column::ofWidth(5);
            $column->addElement($this->getSettings());
            $column->appendTo($row);
            $column = Framework\Column::ofWidth(2);
            $column->addElement($this->getAdvanced());
            $column->addElement($this->getDiagnostic());
            $column->appendTo($row);
            $row->render();
            ?>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * The internal settings part
     *
     * @return Framework\PanelWithForm
     */
    private function getSettings()
    {
        $panel = new Framework\PanelWithForm(ucfirst(__('Settings', 'team-booking')));
        $panel->setAction('tbk_save_core');
        $panel->setNonce('team_booking_options_verify');

        // Cookies
        $element = new Framework\PanelSettingRadios(__('Cookies', 'team-booking'));
        $element->addDescription(__('The plugin uses technical cookies (NO profiling cookies are used) to manage some features like multiple slots selection, if active.', 'team-booking'));
        $element->addToDescription(' ' . __('The same cookies can be used to keep track of preferences like the timezone, so the customer only makes the choice once.', 'team-booking'));
        $element->addToDescription('<p>' . esc_html__('While the technical cookies are necessary, the track of preferences is not and you can choose how the plugin should act about it.', 'team-booking'), FALSE);
        $element->addToDescription(' ' . __('Depending on your Privacy Policy, you should make decisions and take actions to be compliant with the current laws and eventually inform your customers.', 'team-booking') . '</p>', FALSE);
        $element->addFieldname('cookie_policy');
        $element->addOption(array(
            'label'   => __('Do not keep track of preferences (technical cookies will still be used)', 'team-booking'),
            'value'   => 0,
            'checked' => $this->settings->getCookiePolicy() === 0
        ));
        $element->addOption(array(
            'label'   => __('Keep track of preferences (you should acquire consent or explain it in your current cookie policy)', 'team-booking'),
            'value'   => 1,
            'checked' => $this->settings->getCookiePolicy() === 1
        ));
        $element->addOption(array(
            'label'   => __('Show a consent screen to the customer and let them decide', 'team-booking'),
            'value'   => 2,
            'checked' => $this->settings->getCookiePolicy() === 2
        ));
        $element->addNotice(__('Logged-in users are excluded and always have their preferences kept (update your TOS if you consider it necessary)', 'team-booking'));
        $element->appendTo($panel);

        // Coworker's roles
        $element = new Framework\PanelSettingCheckboxes(__('Roles allowed to be Coworkers', 'team-booking'));
        $element->addDescription(__('Users with selected roles will be your Coworkers. Those who link a Google Calendar can participate to all service classes. Coworkers without a linked Google Calendar can participate to "Unscheduled services" only.', 'team-booking'));
        $element->addToDescription('<p>' . esc_html__('Administrators are always allowed.', 'team-booking') . '</p>', FALSE);
        foreach ($this->roles_all as $name => $role) {
            if ($name === 'administrator') continue;
            $element->addCheckbox(array(
                'fieldname' => 'roles_allowed' . '[' . $name . ']',
                'label'     => $role['name'],
                'value'     => $role['name'],
                'checked'   => in_array($role['name'], $this->roles_allowed)
            ));
        }
        $element->appendTo($panel);

        // Autofill fields
        $element = new Framework\PanelSettingRadios(__('Autofill reservation form fields for registered users', 'team-booking'));
        $element->addFieldname('autofill');
        $element->addDescription(__('If "yes", a logged-in customer will find some reservation form fields pre-populated based on their WordPress profile data. If "yes, and hide fields", the pre-populated fields will not be shown on the form, but the customer\'s data will still be passed to the booking.', 'team-booking'));
        $element->addOption(array(
            'label'   => __('Yes', 'team-booking'),
            'value'   => 'yes',
            'checked' => $this->settings->getAutofillReservationForm() === TRUE
        ));
        $element->addOption(array(
            'label'   => __('Yes, and hide fields', 'team-booking'),
            'value'   => 'hide',
            'checked' => $this->settings->getAutofillReservationForm() === 'hide'
        ));
        $element->addOption(array(
            'label'   => __('No', 'team-booking'),
            'value'   => 'no',
            'checked' => $this->settings->getAutofillReservationForm() === FALSE
        ));
        $element->appendTo($panel);

        // Load at first month
        $element = new Framework\PanelSettingYesOrNo(__('Load the frontend calendar at the closest month with available slots', 'team-booking'));
        $element->addDescription(__('If yes, the frontend calendar will be automatically loaded at the closest month with at least one free slot. Please note: if yes, the first page loading can be slower.', 'team-booking'));
        $element->addFieldname('first_month_automatic');
        $element->setState($this->settings->isFirstMonthWithFreeSlotShown());
        $element->appendTo($panel);

        // Allow cart
        $element = new Framework\PanelSettingYesOrNo(__('Allow multiple slots selection', 'team-booking'));
        $element->addDescription(__('If yes, customers will be able to select multiple slots and book them in a single order. Please refer to the documentation to know all the implications.', 'team-booking'));
        $element->addFieldname('allow_cart');
        $element->setState($this->settings->allowCart());
        $element->appendTo($panel);

        //Block slots in cart
        $element = new Framework\PanelSettingYesOrNo(__('Block slots in cart', 'team-booking'));
        $element->addDescription(__("If yes and if multiple slots selection is allowed, while the slots are in the cart of a certain customer they can't be reserved by other customers.", 'team-booking'));
        $element->addFieldname('block_slots_in_cart');
        $element->setState($this->settings->blockSlotsInCart());
        $element->appendTo($panel);

        // Slots in cart expiration time
        $element = new Framework\PanelSettingSelector(__('Slots in cart expiration time', 'team-booking'));
        $element->addFieldname('slots_in_cart_timeout');
        $element->addDescription(__('Choose the expiration time (since the first slot addition) of the slots in the cart. After this time, the slots will be removed from the cart. If set to "Forever" the slots will remain in the cart as long as the session cookie is valid.', 'team-booking'));
        $element->setSelected($this->settings->getSlotsInCartExpirationTime());
        $element->addOption(5 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 5, 'team-booking'), 5));
        $element->addOption(10 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 10, 'team-booking'), 10));
        $element->addOption(15 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 15, 'team-booking'), 15));
        $element->addOption(30 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 30, 'team-booking'), 30));
        $element->addOption(45 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 45, 'team-booking'), 45));
        $element->addOption(1 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 1, 'team-booking'), 1));
        $element->addOption(2 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 2, 'team-booking'), 2));
        $element->addOption(3 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 3, 'team-booking'), 3));
        $element->addOption(4 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 4, 'team-booking'), 4));
        $element->addOption(5 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 5, 'team-booking'), 5));
        $element->addOption(6 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 6, 'team-booking'), 6));
        $element->addOption(12 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 12, 'team-booking'), 12));
        $element->addOption(1 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 1, 'team-booking'), 1));
        $element->addOption(0, __('Forever', 'team-booking'));
        $element->addAlert(__('If the option to block the slots while they are in the cart is set to "yes", avoid to setting this to "Forever".', 'team-booking'));
        $element->appendTo($panel);

        // Order redirect
        $element = new Framework\PanelSettingRadios(__('Redirect URL after a multiple slots order', 'team-booking'));
        $element->addFieldname('order_redirect');
        $element->addDescription(__('If active, the customer will be redirected to the specified URL after a successful order. If an offsite payment gateway like PayPal is chosen, then this URL will override the one specified in the gateway settings and will be called after the payment.', 'team-booking'));
        $element->addOption(array(
            'label'   => __('No redirect', 'team-booking'),
            'value'   => 'no',
            'checked' => $this->settings->getOrderRedirectRule() === 'no'
        ));
        $element->addOption(array(
            'label'   => __('Redirect to the service redirect URL if specified and only if all the slots in the order are of the same service, otherwise do not redirect', 'team-booking'),
            'value'   => 'service_specific_when_all',
            'checked' => $this->settings->getOrderRedirectRule() === 'service_specific_when_all'
        ));
        $description_element = new Framework\PanelSettingDescriptionWithTextfield();
        $description_element->addFieldname('order_redirect_url');
        $description_element->setDefaultText($this->settings->getOrderRedirectUrl());
        $description_element->setPlaceholder('http://, https://');
        $description_element->setDisabled($this->settings->getOrderRedirectRule() !== 'yes');
        $element->addOption(array(
            'label'       => __('Redirect to URL', 'team-booking'),
            'value'       => 'yes',
            'checked'     => $this->settings->getOrderRedirectRule() === 'yes',
            'description' => $description_element
        ));
        $element->appendTo($panel);

        // Batch e-mail by service
        $element = new Framework\PanelSettingYesOrNo(__('Batch the e-mail messages per service after a multiple slots order', 'team-booking'));
        $element->addDescription(__('When a multiple slots booking is created you can send only one e-mail per service instead of one per slot, in order to avoid sending too many similar e-mail messages. The "batch" e-mail message will contain the data of each slot (date, time, service provider etc.) grouped according to a repeating pattern that you should configure in the e-mail body using specific delimiters (refer to the documentation to know how).', 'team-booking'));
        $element->addFieldname('batch_email_by_service');
        $element->setState($this->settings->batchEmailByService());
        $element->addAlert(__('Activating this setting without properly preparing the service e-mail messages would be just asking for issues. Given a service, if the sender of the e-mail message is set to be the service provider then the single "batch" e-mail will be per service-coworker combination instead of just per service. Refer to the documentation to fully understand the process with some examples.', 'team-booking'));
        $element->appendTo($panel);

        // ICAL file
        $element = new Framework\PanelSettingYesOrNo(__('Allow customers to download ICAL file after a reservation', 'team-booking'));
        $element->addFieldname('show_ical');
        $element->setState($this->settings->getShowIcal());
        $element->appendTo($panel);

        // Slot override commands
        $element = new Framework\PanelSettingYesOrNo(__('Allow service providers to use "slot commands" to override some service settings', 'team-booking'));
        $element->addDescription(__('Service providers will be able to override some general service settings for specific slots, like the price, by using "slot commands" directly in Google Calendar. Administrators are always allowed.', 'team-booking'));
        $element->addFieldname('allow_slot_commands');
        $element->setState($this->settings->allowSlotCommands());
        $element->appendTo($panel);

        // Login URL
        $element = new Framework\PanelSettingText(__('Login URL', 'team-booking'));
        $element->addDescription(__('Logged-in only services will invite users to login here', 'team-booking'));
        $element->addFieldname('login_url');
        $element->addDefaultValue($this->settings->getLoginUrl());
        $element->appendTo($panel);

        // Registration URL
        $element = new Framework\PanelSettingText(__('Registration URL', 'team-booking'));
        $element->addDescription(__('Logged-in only services will invite users to register here', 'team-booking'));
        $element->addFieldname('registration_url');
        $element->addDefaultValue($this->settings->getRegistrationUrl());
        $element->appendTo($panel);

        // Redirect after login/registration
        $element = new Framework\PanelSettingYesOrNo(__('Redirect the customers back to the calendar page after login/registration', 'team-booking'));
        $element->addFieldname('redirect_back_after_login');
        $element->setState($this->settings->getRedirectBackAfterLogin());
        $element->addAlert(__('An eventual login redirect plugin may interfere with that.', 'team-booking'));
        $element->appendTo($panel);

        // Database retention time
        $element = new Framework\PanelSettingSelector(__('Keep reservations in database for', 'team-booking'));
        $element->addFieldname('database_reservation_timeout');
        $element->addDescription(__("Counting starts from reservation's date", 'team-booking'));
        $element->setSelected($this->settings->getDatabaseReservationTimeout());
        $element->addOption(15 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 15, 'team-booking'), 15));
        if (Functions\cleanReservations(FALSE, TRUE, 15 * DAY_IN_SECONDS)) $element->addWarning(15 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(30 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 30, 'team-booking'), 30));
        if (Functions\cleanReservations(FALSE, TRUE, 30 * DAY_IN_SECONDS)) $element->addWarning(30 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(60 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 60, 'team-booking'), 60));
        if (Functions\cleanReservations(FALSE, TRUE, 60 * DAY_IN_SECONDS)) $element->addWarning(60 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(120 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 120, 'team-booking'), 120));
        if (Functions\cleanReservations(FALSE, TRUE, 120 * DAY_IN_SECONDS)) $element->addWarning(120 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(240 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 240, 'team-booking'), 240));
        if (Functions\cleanReservations(FALSE, TRUE, 240 * DAY_IN_SECONDS)) $element->addWarning(240 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(0, __('Forever', 'team-booking'));
        $element->appendTo($panel);

        // Max pending time
        $element = new Framework\PanelSettingSelector(__('Max pending time', 'team-booking'));
        $element->addFieldname('max_pending_time');
        $element->addDescription(__('If payment is not made within this time, the reservation will be released. It affects only services where payment must be made immediately.', 'team-booking'));
        $element->setSelected($this->settings->getMaxPendingTime());
        $element->addOption(0, __('Never', 'team-booking'));
        $element->addOption(15 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 15, 'team-booking'), 15));
        $element->addOption(30 * MINUTE_IN_SECONDS, sprintf(_n('%d minute', '%d minutes', 30, 'team-booking'), 30));
        $element->addOption(1 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 1, 'team-booking'), 1));
        $element->addOption(2 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 2, 'team-booking'), 2));
        $element->addOption(3 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 3, 'team-booking'), 3));
        $element->addOption(6 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 6, 'team-booking'), 6));
        $element->addOption(12 * HOUR_IN_SECONDS, sprintf(_n('%d hour', '%d hours', 12, 'team-booking'), 12));
        $element->addOption(1 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 1, 'team-booking'), 1));
        $element->addOption(2 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 2, 'team-booking'), 2));
        $element->addOption(3 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 3, 'team-booking'), 3));
        $element->addOption(4 * DAY_IN_SECONDS, sprintf(_n('%d day', '%d days', 4, 'team-booking'), 4));
        $element->addAlert(__('Values too low could, in extreme cases, lead to payments after reservation is released. Also consider that, in order to process IPN confirmation, your server must be up and running. PayPal example: if your server is down, IPN will be resent by PayPal for up to four days, with a maximum of 15 retries. The interval will increase after each fail attempt.', 'team-booking'));
        $element->appendTo($panel);

        // Delete reservation database on uninstall
        $element = new Framework\PanelSettingYesOrNo(__("Delete plugin's database tables when the plugin is uninstalled", 'team-booking'));
        $element->addFieldname('drop_tables');
        $element->setState($this->settings->getDropTablesOnUninstall());
        $element->appendTo($panel);

        // Google Maps API key
        $element = new Framework\PanelSettingText(__('Google Maps API key', 'team-booking'));
        $element->addDescription(__("This is mandatory in order to use Google Maps for installations made after 22nd of June, 2016. If you don't have a Google Maps API key already, please check the documentation in order to know how to obtain it.", 'team-booking'));
        $element->addFieldname('gmaps_api_key');
        $element->addDefaultValue($this->settings->getGmapsApiKey());
        $element->addAlert(sprintf(
            esc_html__('As of 11th of June 2018, in addition to the API key, Google requires the activation of the Google Maps Platform billing account, in order to use Google Maps in your website. According to %stheir announcement%s you will gain access to $200 of free monthly usage, that should richly cover your needs.', 'team-booking'),
            '<a href="https://mapsplatform.googleblog.com/2018/05/introducing-google-maps-platform.html" target="_blank">',
            '</a>'), FALSE);
        $element->appendTo($panel);

        // Skip Google Maps library
        $element = new Framework\PanelSettingYesOrNo(__('Skip Google Maps library loading', 'team-booking'));
        $element->addFieldname('skip_gmaps');
        $element->setState($this->settings->getSkipGmapLibs());
        $element->appendTo($panel);

        // Restrict Timezones
        $element = new Framework\PanelSettingCheckboxes(__('Restrict continents in frontend timezone selectors', 'team-booking'));
        $element->addDescription(__('Unchecked continents will be hidden', 'team-booking'));
        foreach (Functions\continents_list() as $value => $name) {
            $element->addCheckbox(array(
                'fieldname' => 'continents_allowed' . '[' . $value . ']',
                'label'     => $name,
                'value'     => $value,
                'checked'   => $this->settings->getContinentAllowed($value)
            ));
        }
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_core');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The advanced operations part
     *
     * @return Framework\Panel
     */
    private function getAdvanced()
    {
        $panel = new Framework\Panel(ucfirst(__('Advanced', 'team-booking')));
        $element = new Framework\PanelSettingWildcard(NULL);
        ob_start();
        echo Framework\Html::paragraph(array(
            'text'   => Framework\Html::anchor(array(
                'text'  => __('Export current settings', 'team-booking'),
                'id'    => 'team-booking-export-settings',
                'class' => 'button tbk-button-long-text'
            )),
            'escape' => FALSE
        ));
        ?>
        <form id="team-booking-export-settings_form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_settings_backup">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>
        <?php
        echo Framework\Html::paragraph(array(
            'text'   => Framework\Html::anchor(array(
                'text'  => __('Import settings from file', 'team-booking'),
                'id'    => 'team-booking-import-settings',
                'class' => 'button tbk-button-long-text'
            )),
            'escape' => FALSE
        ));

        echo Framework\Html::paragraph(array(
            'text'   => Framework\Html::anchor(array(
                'text'  => __('Repair database', 'team-booking'),
                'id'    => 'team-booking-repair-database',
                'class' => 'button tbk-button-long-text'
            )),
            'escape' => FALSE
        ));

        \TeamBooking\Actions\backend_core_advanced_after_content();

        $element->addContent(ob_get_clean());

        // Import settings modal
        $modal = new Framework\Modal('team-booking-import-settings_modal');
        $modal->setHeaderText(array('main' => __('Import settings from file', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('OK', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $modal->addContent(Framework\Html::paragraph(array(
            'text' => __('Please choose a JSON file that was previously exported from the same plugin version. This function is NOT meant to pass settings from one version to a different one.', 'team-booking')
        )));
        $modal->addContent('<form id="team-booking-import-settings_form" method="POST" action="' . Admin::add_params_to_admin_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">');
        $modal->addContent('<input type="hidden" name="action" value="tbk_import_settings">');
        $modal->addContent(wp_nonce_field('team_booking_options_verify', '_wpnonce', TRUE, FALSE));
        $modal->addContent('<input type="file" name="settings_backup_file">');
        $modal->addContent('</form>');
        $element->addContent($modal);

        // Repair database modal
        $modal = new Framework\Modal('team-booking-repair-database_modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->addContent(Framework\Html::paragraph(__('This may take a while, please be patient.', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('OK', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $element->addContent($modal);

        // Import JSON modal
        $modal = new Framework\Modal('team-booking-import-core-json_modal');
        $modal->setHeaderText(array('main' => __('Import from JSON file', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('OK', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $modal->addContent('<form id="team-booking-import-core-json_form" method="POST" action="">');
        $modal->addContent(wp_nonce_field('team_booking_options_verify', '_wpnonce', TRUE, FALSE));
        $modal->addContent('<input type="file" name="settings_json_file" data-ays-ignore="true">');
        $modal->addContent(Framework\Html::paragraph(array('text' => __('The Authorized redirect URI and/or Authorized Javascript Origins seems to be incorrect, have you correctly pasted the values provided here in your Google Project console? Please double check, and retry with the new JSON file!', 'team-booking'), 'class' => 'tb-json-errors uri_mismatch')));
        $modal->addContent(Framework\Html::paragraph(array('text' => __('Sorry, this is not a JSON Google Project file, or it is not complete.', 'team-booking'), 'class' => 'tb-json-errors invalid_file')));
        $modal->addContent(Framework\Html::paragraph(array('text' => __('Please select a file!', 'team-booking'), 'class' => 'tb-json-errors no_file')));
        $modal->addContent('</form>');
        $element->addContent($modal);

        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The quick guide part
     *
     * @return Framework\Panel
     */
    private function getQuickGuide()
    {
        $panel = new Framework\Panel(ucfirst(__('Are you lost?', 'team-booking')));
        $plugin_data = get_plugin_data(TEAMBOOKING_FILE_PATH);
        $url = 'https://console.developers.google.com/flows/enableapi?apiid=calendar&pli=1';
        $url_doc = $plugin_data['PluginURI'] . '/docs';
        $string1 = esc_html__('To start working with TeamBooking, you need to create a Project in your Google Developer Console', 'team-booking') . ' (<a href="' . $url . '" alt="Google Developer Console Link" target="_blank">link</a>)';
        $string2 = esc_html__('Read the "Getting Started" paragraph of the Team Booking Documentation', 'team-booking') . ' (<a href="' . $url_doc . '" alt="TeamBooking documentation" target="_blank">link</a>)';
        $element = new Framework\PanelSettingWildcard(NULL);
        $element->addDescription(Framework\Html::paragraph(array('text' => $string1, 'escape' => FALSE)), FALSE);
        $element->addToDescription(Framework\Html::paragraph(array('text' => $string2, 'escape' => FALSE)), FALSE);
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The API tokens part
     *
     * @return Framework\Panel
     */
    private function getTokenTable()
    {
        $panel = new Framework\Panel(ucfirst(__('API tokens', 'team-booking')));

        $button = new Framework\PanelTitleAddNewButton(__('New API token (read-only)', 'team-booking'));
        $button->addClass('team-booking-new-token');
        $button->addData(array('write' => '0'));
        $panel->addTitleContent($button);
        $button = new Framework\PanelTitleAddNewButton(__('New API token', 'team-booking'));
        $button->addClass('team-booking-new-token');
        $button->addData(array('write' => '1'));
        $panel->addTitleContent($button);

        $table = new Framework\Table();
        $table->setId('tbk-core-tokens');
        // Preparing the table columns
        $table->addColumns(array(
            esc_html__('Token', 'team-booking'),
            esc_html__('Scope', 'team-booking'),
            esc_html__('Usages', 'team-booking'),
            esc_html__('Actions', 'team-booking')
        ));
        // Preparing the table rows
        foreach ($this->settings->getTokens() as $token => $specs) {
            $button = new Framework\ActionButton('dashicons-trash');
            $button->addClass('tbk-token-action-delete');
            $button->setTitle(__('Delete', 'team-booking'));
            $button->addData('token', $token);
            $table->addRow(array(
                0 => $token,
                1 => $specs['write'] ? esc_html__('read/write', 'team-booking') : esc_html__('read', 'team-booking'),
                2 => $this->settings->getTotalTokenUsages($token),
                3 => $button
            ));
        }

        $panel->addElement($table);

        return $panel;
    }

    /**
     * The Google Project settings part
     *
     * @return Framework\PanelWithForm
     */
    private function getGoogleProjectSettings()
    {
        $panel = new Framework\PanelWithForm(ucfirst(__('Google Project Data', 'team-booking')));
        $json_button = new Framework\PanelTitleAddNewButton(__('Import from JSON file', 'team-booking'));
        $json_button->setId('team-booking-import-core-json');
        $panel->addTitleContent($json_button);
        $panel->setAction('tbk_save_core');
        $panel->setNonce('team_booking_options_verify');

        // Client ID
        $element = new Framework\PanelSettingTextWithLock(__('Client ID', 'team-booking'));
        $element->addDefaultValue($this->settings->getApplicationClientId());
        $element->addFieldname('client_id');
        $element->setReadOnly($this->settings->getApplicationClientId());
        $element->appendTo($panel);

        // Client Secret
        $element = new Framework\PanelSettingTextWithLock(__('Client Secret', 'team-booking'));
        $element->addDefaultValue($this->settings->getApplicationClientSecret());
        $element->addFieldname('client_secret');
        $element->setReadOnly($this->settings->getApplicationClientSecret());
        $element->appendTo($panel);

        // Product name
        $element = new Framework\PanelSettingTextWithLock(__('Product name', 'team-booking'));
        $element->addDefaultValue($this->settings->getApplicationProjectName());
        $element->addFieldname('project_name');
        $element->setReadOnly($this->settings->getApplicationProjectName());
        $element->appendTo($panel);

        // JS origin
        $element = new Framework\PanelSettingWildcard(__('Authorized Javascript Origins', 'team-booking'));
        $element->addDescription($this->js_origins);
        $element->appendTo($panel);

        // Redirect URI
        $element = new Framework\PanelSettingWildcard(__('Authorized redirect URI', 'team-booking'));
        $element->addDescription($this->redirect_uri);
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_core');
        $panel->addElement($element);

        return $panel;
    }

    public function getDiagnostic()
    {
        $arg_separator = ini_get('arg_separator.output'); // it must be & instead of &amp;
        $zip_archive_support = class_exists('ZipArchive'); // for XLSX export
        $panel = new Framework\Panel(ucfirst(__('System check', 'team-booking')));
        $element = new Framework\PanelSettingWildcard(NULL);
        ob_start();
        echo '<p><strong>'
            . esc_html__('PHP version', 'team-booking') . '</strong> '
            . '<span class="' . (PHP_VERSION_ID > 70000 ? 'tbk-confirmed' : 'tbk-pending') . '">' . PHP_VERSION . '</span>'
            . (PHP_VERSION_ID < 70000 ? '<span class="description" style="display: block;">' . esc_html__('You might want to consider upgrading to PHP version 7 or greater to a faster overall experience.') . '</span>' : '')
            . '</p>';
        echo '<p><strong>'
            . esc_html__('XLSX export', 'team-booking') . '</strong> '
            . '<span class="' . ($zip_archive_support ? 'tbk-confirmed' : 'tbk-cancelled') . '">'
            . ($zip_archive_support ? esc_html__('supported', 'team-booking') : esc_html__('not supported', 'team-booking'))
            . '</span>'
            . (!$zip_archive_support ? '<span class="description" style="display: block;">' . esc_html__('PHP needs to have the Zip Extension installed.') . '</span>' : '')
            . '</p>';
        echo '<p><strong>'
            . esc_html__('Arg separator', 'team-booking') . '</strong> '
            . '<span class="' . ($arg_separator === '&' ? 'tbk-confirmed' : 'tbk-cancelled') . '" style="text-transform:lowercase">'
            . htmlentities($arg_separator)
            . '</span>'
            . ($arg_separator !== '&' ? '<span class="description" style="display: block;">' . esc_html__('PHP arg_separator.output must be set to "&" to avoid issues. Please check the PHP ini configuration.') . '</span>' : '')
            . '</p>';

        global $wpdb;
        $results = $wpdb->get_row("SHOW GRANTS", ARRAY_N);
        $db_permissions = strpos(reset($results), 'ALL PRIVILEGES') !== FALSE
            || strpos(reset($results), 'CREATE') !== FALSE;
        echo '<p><strong>'
            . esc_html__('Database permissions', 'team-booking') . '</strong> '
            . '<span class="' . ($db_permissions ? 'tbk-confirmed' : 'tbk-pending') . '">'
            . ($db_permissions ? esc_html__('ok', 'team-booking') : esc_html__('apparently denied', 'team-booking'))
            . '</span>'
            . (!$db_permissions ? '<span class="description" style="display: block;">' . esc_html__('It looks like the current database user lacks of the privileges to create tables. If you do not experience issues then just ignore this check. Otherwise, for example if your events are not showing despite the troubleshooting, please revise your current database permissions policy.') . '</span>' : '')
            . '</p>';


        if (class_exists('SitePress')) {
            echo '<p><strong>'
                . esc_html__('WPML', 'team-booking') . '</strong> '
                . '<span class="' . (\TeamBooking\WPML\is_str_tr_available() ? 'tbk-confirmed' : 'tbk-pending') . '">'
                . esc_html__('active', 'team-booking')
                . '</span>'
                . (!\TeamBooking\WPML\is_str_tr_available() ? '<span class="description" style="display: block;">'
                    . sprintf(esc_html__('You need the %s and %s modules active to be able to translate custom strings such as service names, custom form fields etc.', 'team-booking'), '<a href="https://wpml.org/documentation/wpml-core-and-add-on-plugins/">WPML String Translation</a>', '<a href="https://wpml.org/documentation/wpml-core-and-add-on-plugins/">WPML Translation Management</a>')
                    . '</span>' : '')
                . '</p>';
        }
        $element->addContent(ob_get_clean());
        $element->appendTo($panel);

        return $panel;
    }

}
