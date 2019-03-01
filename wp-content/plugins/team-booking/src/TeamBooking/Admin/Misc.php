<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Misc
 *
 * @author VonStroheim
 */
class Misc
{
    /**
     * @var Misc
     */
    private static $_instance;

    /**
     * @return Misc
     */
    public static function render()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Generate the tab wrapper for admin page
     *
     * @param string $active_tab
     */
    public function getTabWrapper($active_tab)
    {
        $header = new Framework\Header();
        $header->setPluginData(TEAMBOOKING_FILE_PATH);
        $header->setMainText('Hi, ' . wp_get_current_user()->user_firstname);
        $header->addTab('overview', __('Overview', 'team-booking'), 'dashicons-chart-area', $active_tab === 'overview');
        $header->addTab('slots', __('Slots', 'team-booking'), 'dashicons-calendar-alt', $active_tab === 'slots');
        $header->addTab('events', __('Services', 'team-booking'), 'dashicons-clipboard', $active_tab === 'events');
        $header->addTab('coworkers', __('Coworkers', 'team-booking'), 'dashicons-groups', $active_tab === 'coworkers');
        $header->addTab('customers', __('Customers', 'team-booking'), 'dashicons-id', $active_tab === 'customers');
        $header->addTab('personal', __('Personal', 'team-booking'), 'dashicons-businessman', $active_tab === 'personal');
        $header->addTab('aspect', __('Frontend style', 'team-booking'), 'dashicons-art', $active_tab === 'aspect');
        $header->addTab('general', __('Core settings', 'team-booking'), 'dashicons-admin-generic', $active_tab === 'general');
        $header->addTab('payments', __('Payment gateways', 'team-booking'), 'dashicons-cart', $active_tab === 'payments');
        $header->addTab('pricing', __('Promotions', 'team-booking'), 'dashicons-money', $active_tab === 'pricing');
        $header->render();
    }

    /**
     * Generate the tab wrapper for admin page (coworker)
     *
     * @param string $active_tab
     */
    public function getTabWrapperCoworker($active_tab)
    {
        $header = new Framework\Header();
        $header->setPluginData(TEAMBOOKING_FILE_PATH);
        $header->setMainText('Hi, ' . wp_get_current_user()->user_firstname);
        $header->addTab('overview', __('Overview', 'team-booking'), 'dashicons-chart-area', $active_tab === 'overview');
        $header->addTab('slots', __('Slots', 'team-booking'), 'dashicons-calendar-alt', $active_tab === 'slots');
        $header->addTab('events', __('Services', 'team-booking'), 'dashicons-clipboard', $active_tab === 'events');
        $header->addTab('personal', __('Personal', 'team-booking'), 'dashicons-businessman', $active_tab === 'personal');
        $header->render();
    }

    public function getWhatsnewPage()
    {
        ?>
        <div class="wrap about-wrap">
        <h1><?= sprintf(esc_html__('Welcome to TeamBooking %s', 'team-booking'), substr(TEAMBOOKING_VERSION, 0, 3)) ?></h1>
        <p class="about-text">
            <?= esc_html__('Thank you for updating to the latest version.', 'team-booking') ?>
        </p>
        <div class="wp-badge"
             style="    background: url(<?= TEAMBOOKING_URL . '/images/logo-white.png' ?>) center 45px no-repeat #0073aa;">
            Ver. <?= TEAMBOOKING_VERSION ?>
        </div>

        <h2><?= esc_html__('New features', 'team-booking') ?></h2>
        <div class="feature-section two-col">
            <div class="col">
                <img src="<?= TEAMBOOKING_URL . '/images/feature-1.png' ?>" alt="Multiple slots selection"
                     style="border: 5px solid lightgrey;box-sizing: border-box;">
                <h3><?= esc_html__('Multiple slots selection', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('Customers can select multiple slots, even from different services, and book them in a single action. Like a Cart!', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/feature-2.png' ?>" alt="Full WPML compatibility"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Full WPML compatibility', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('Thanks to the WPML String Translation module, you can provide translations to any custom string like service names, custom form fields and e-mail content!', 'team-booking') ?>
                </p>
            </div>
        </div>

        <h2><?= esc_html__('Improvements', 'team-booking') ?></h2>
        <div class="feature-section three-col">
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/improvement-1.png' ?>" alt="E-mail templates"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('E-mail templates', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__("Store your e-mail bodies as templates to quickly retrieve them where needed!", 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/improvement-2.png' ?>" alt="Frontend attendees"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Frontend attendees', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('Show the list of the customers directly into the frontend slots!', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <img src="<?= TEAMBOOKING_URL . '/images/improvement-3.png' ?>" alt="Slot commands"
                     style="border: 5px solid lightgrey;box-sizing: border-box;">
                <h3><?= esc_html__('Slot commands', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__("Thanks to this feature, you are now able to define or override some properties for a given slot directly via Google Calendar!", 'team-booking') ?>
                </p>
            </div>
        </div>

        <h2><?= esc_html__('In case you missed it', 'team-booking') ?></h2>
        <div class="feature-section three-col">
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/feature-backend-1.png' ?>" alt="Visual Composer"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Visual Composer elements.', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('The popular Page Builder is fully supported by TeamBooking. You can add all the shortcodes even via frontend live editor.', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/feature-backend-2.png' ?>" alt="Read-only services"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Read-only services', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('Services can be set as read-only, no reservation form is shown when a slot is selected, only the service description.', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <img src="<?= TEAMBOOKING_URL . '/images/feature-backend-3.png' ?>" alt="Dynamic booked slot titles"
                     style="border: 5px solid lightgrey;box-sizing: border-box;">
                <h3><?= esc_html__('Dynamic booked slot titles.', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__("For your Appointment services, now you can set a dynamic booked event title. That means you can add customer's data (e-mail and name) directly to the booked slot title in Google Calendar, to check who reserved them without even opening the slots.", 'team-booking') ?>
                </p>
            </div>
        </div>

        <div class="changelog">
            <h2><?= esc_html__('Under the hood', 'team-booking') ?></h2>

            <div class="under-the-hood three-col">
                <div class="col">
                    <h3><?= esc_html__('Query string parameters', 'team-booking') ?></h3>
                    <p>
                        <?= esc_html__('A useful way to a further customization of your frontend calendar pages. Check the documentation for all the details.', 'team-booking') ?>
                    </p>
                </div>
                <div class="col">
                    <h3><?= esc_html__('Past slots', 'team-booking') ?></h3>
                    <p>
                        <?= esc_html__('Past slots can be displayed in the backend.', 'team-booking') ?>
                    </p>
                </div>
                <div class="col">
                    <h3><?= esc_html__('Persistent customer preferences', 'team-booking') ?></h3>
                    <p>
                        <?= esc_html__('Some preferences are persistent, the timezone for instance.', 'team-booking') ?>
                    </p>
                </div>
            </div>
        </div>

        <?php
    }

}
