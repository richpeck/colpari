<?php

namespace TeamBooking\Widgets;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart;
use TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * Class Calendar
 *
 * @author VonStroheim
 */
class Calendar extends \WP_Widget
{
    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        parent::__construct(
            'teambooking_widget', // Base ID
            __('TeamBooking Calendar', 'team-booking'), // Name
            array(
                'description'                 => __('Shows a booking calendar', 'team-booking'),
                'customize_selective_refresh' => TRUE,
            ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     *
     * @return bool
     * @throws \Exception
     */
    public function widget($args, $instance)
    {
        if (!defined('TBK_WIDGET_SHORTCODE_FOUND') && is_active_widget(FALSE, FALSE, $this->id_base)) {
            define('TBK_WIDGET_SHORTCODE_FOUND', TRUE);
            if (!wp_script_is('tb-frontend-script', 'registered')) {
                Functions\registerFrontendResources();
            }
            Functions\enqueueFrontendResources();
        }

        $settings = Functions\getSettings();
        // Read-only mode is identified by lenght of istance id
        // This is the fastest way to keep things safe from exploits
        if ($instance['read_only'] === 'true') {
            $unique_id = Toolkit\randomNumber(6);
        } else {
            $unique_id = Toolkit\randomNumber(8);
        }
        $filter = FALSE;
        if (isset($instance['no_filter']) && $instance['no_filter'] === 'true') {
            $filter = TRUE;
        }
        $timezone = FALSE;
        if (isset($instance['no_timezone']) && $instance['no_timezone'] === 'true') {
            $timezone = TRUE;
        }
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $all_services = \TeamBooking\Database\Services::get();
        // Remove inactive services
        foreach ($all_services as $key => $service) {
            if (!$service->isActive()) {
                unset($all_services[ $key ]);
            }
        }
        if (!empty($all_services)) {
            $checkboxes = array();
            foreach ($all_services as $service) {
                $checkboxes[ $service->getId() ] = isset($instance[ $service->getId() ]) ? TRUE : FALSE;
            }
        } else {
            echo esc_html__('WARNING: no service(s) found. Please add one first.', 'team-booking');

            return TRUE;
        }
        $widget_service_list = array_keys($checkboxes, TRUE);
        if (empty($widget_service_list)) {
            echo esc_html__('WARNING: no service(s) selected. Please check this widget settings.', 'team-booking');
        }
        $calendar = new \TeamBooking\Calendar();
        $parameters = new \TeamBooking\RenderParameters();
        if (count($widget_service_list) === 1 && \TeamBooking\Database\Services::get(reset($widget_service_list))->getClass() === 'unscheduled') {
            $parameters->setServiceIds($widget_service_list);
        } else {
            $parameters->setServiceIds($settings->getServiceIdList());
        }
        $parameters->setRequestedServiceIds($widget_service_list);
        $parameters->setCoworkerIds(array());
        $parameters->setRequestedCoworkerIds(array());
        $parameters->setTimezone(Toolkit\getTimezone(Functions\parse_timezone_aliases(Cart::getPreference('timezone'))));
        $parameters->setInstance($unique_id);
        $parameters->setIsAjaxCall(FALSE);
        $parameters->setNoFilter($filter);
        $parameters->setNoTimezone($timezone);
        $parameters->setIsWidget(TRUE);
        $parameters->setAltSlotStyle($instance['slots_style']);
        Functions\parse_query_params($parameters);
        $post_id = get_the_ID();
        echo "<div class='ui calendar_widget_container' id='tbk-container-" . $unique_id . "' aria-live='polite' data-postid='" . $post_id . "'>";
        $calendar->getCalendar($parameters);
        echo '</div>';
        echo "<script>if (typeof tbkLoadInstance === 'function') {tbkLoadInstance(jQuery('#tbk-container-" . $unique_id . "'))}</script>";
        echo $args['after_widget'];

        return TRUE;
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array)$instance, array(
            'title'       => esc_html__('Booking calendar', 'team-booking'),
            'read_only'   => 'false',
            'no_filter'   => 'false',
            'no_timezone' => 'false',
            'slots_style' => Functions\getSettings()->getSlotStyle()
        ));
        ?>
        <p>
            <label for="<?= $this->get_field_id('title') ?>"><?= esc_html__('Title:') ?></label>
            <input class="widefat" id="<?= $this->get_field_id('title') ?>" name="<?= $this->get_field_name('title') ?>"
                   type="text" value="<?= esc_attr($instance['title']) ?>">
        </p>
        <p><?= esc_html__('Services:', 'team-booking') ?></p>
        <?php
        foreach (\TeamBooking\Database\Services::get(NULL, 'name') as $service) {
            $id = $this->get_field_id($service->getId());
            ?>
            <label for="<?= $id ?>"></label>
            <input type="checkbox" id="<?= $id ?>" name="<?= $this->get_field_name($service->getId()) ?>" <?php
            if (isset($instance[ $service->getId() ])) {
                checked($instance[ $service->getId() ], 'on');
            }
            ?>><span><?= $service->getName(TRUE) ?></span>
            <br>
        <?php } ?>
        <p>
            <label><?= esc_html__('Choose the slots display style', 'team-booking') ?></label><br>
            <select name="<?= $this->get_field_name('slots_style') ?>" class="widefat">
                <option
                        value="0" <?= selected($instance['slots_style'], 0) ?>><?= esc_html__('Basic', 'team-booking') ?></option>
                <option
                        value="1"<?= selected($instance['slots_style'], 1) ?>><?= esc_html__('Elegant', 'team-booking') ?></option>
            </select>
        </p>
        <p><?= esc_html__('Read-only', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('read_only') ?>" value="true" <?=
    checked($instance['read_only'], 'true')
        ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('read_only') ?>" value="false" <?=
    checked($instance['read_only'], 'false')
        ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Hide filter buttons', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('no_filter') ?>" value="true" <?=
    checked($instance['no_filter'], 'true')
        ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('no_filter') ?>" value="false" <?=
    checked($instance['no_filter'], 'false')
        ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Hide timezone selector', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('no_timezone') ?>" value="true" <?=
    checked($instance['no_timezone'], 'true')
        ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('no_timezone') ?>" value="false" <?=
    checked($instance['no_timezone'], 'false')
        ?>><?= esc_html__('No', 'team-booking') ?>
        <br>
        <br>
        <?php
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        foreach (\TeamBooking\Database\Services::get() as $service) {
            if (isset($new_instance[ $service->getId() ])) {
                $instance[ $service->getId() ] = $new_instance[ $service->getId() ];
            }
        }
        $instance['read_only'] = $new_instance['read_only'];
        $instance['no_filter'] = $new_instance['no_filter'];
        $instance['slots_style'] = $new_instance['slots_style'];
        $instance['no_timezone'] = $new_instance['no_timezone'];

        return $instance;
    }

}
