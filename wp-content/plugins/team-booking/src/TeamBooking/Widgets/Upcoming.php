<?php

namespace TeamBooking\Widgets;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Cart;
use TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * Class Upcoming
 *
 * @author VonStroheim
 */
class Upcoming extends \WP_Widget
{
    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        parent::__construct(
            'teambooking_widget_upcoming', // Base ID
            __('TeamBooking Upcoming list', 'team-booking'), // Name
            array(
                'description'                 => __('Shows an upcoming events list', 'team-booking'),
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
        $filter = isset($instance['no_filter']) && $instance['no_filter'] === 'true';
        $timezone = isset($instance['no_timezone']) && $instance['no_timezone'] === 'true';
        $show_more = isset($instance['show_more']) && $instance['show_more'] === 'true';
        $descriptions = isset($instance['descriptions']) && $instance['descriptions'] === 'true';
        $hide_same_days = isset($instance['hide_cal']) && $instance['hide_cal'] === 'true';
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $all_services = \TeamBooking\Database\Services::get();
        // Remove inactive services
        foreach ($all_services as $key => $service) {
            if (!$service->isActive() || $service->getClass() === 'unscheduled') {
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
        $parameters = new \TeamBooking\RenderParameters();
        if (count($widget_service_list) < 1) {
            $parameters->setServiceIds(array_keys($all_services));
        } else {
            $parameters->setServiceIds($widget_service_list);
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
        $parameters->setSlotsShown($instance['how_many']);
        $parameters->setSlotsLimit($instance['max_events']);
        $parameters->setShowMore($show_more);
        $parameters->setHideSameDaysLittleCal($hide_same_days);
        $parameters->setShowServiceDescriptions($descriptions);
        Functions\parse_query_params($parameters);
        $post_id = get_the_ID();
        echo "<div class='ui calendar_widget_container tbk-upcoming' id='tbk-container-" . $unique_id . "' aria-live='polite' data-postid='" . $post_id . "'>";
        echo \TeamBooking\Shortcodes\Upcoming::getView($parameters, $instance['read_only'] === 'true');
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
            'title'        => __('Upcoming events', 'team-booking'),
            'read_only'    => 'false',
            'no_filter'    => 'false',
            'no_timezone'  => 'false',
            'show_more'    => 'false',
            'descriptions' => 'false',
            'hide_cal'     => 'true',
            'how_many'     => 4,
            'max_events'   => 0,
            'slots_style'  => Functions\getSettings()->getSlotStyle()
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
            <label><?= esc_html__('How many events to show', 'team-booking') ?></label><br>
            <input type="number" name="<?= $this->get_field_name('how_many') ?>" value="<?= $instance['how_many'] ?>"
                   min="1" max="40" step="1" class="widefat">
        </p>
        <p>
            <label><?= esc_html__('Max total number of events, 0 for no limit', 'team-booking') ?></label><br>
            <input type="number" name="<?= $this->get_field_name('max_events') ?>"
                   value="<?= $instance['max_events'] ?>" min="0" step="1" class="widefat">
        </p>
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
        <input type="radio" name="<?= $this->get_field_name('read_only') ?>"
               value="true" <?= checked($instance['read_only'], 'true') ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('read_only') ?>"
               value="false" <?= checked($instance['read_only'], 'false') ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Hide filter buttons', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('no_filter') ?>"
               value="true" <?= checked($instance['no_filter'], 'true') ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('no_filter') ?>"
               value="false" <?= checked($instance['no_filter'], 'false') ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Hide timezone selector', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('no_timezone') ?>"
               value="true" <?= checked($instance['no_timezone'], 'true') ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('no_timezone') ?>"
               value="false" <?= checked($instance['no_timezone'], 'false') ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Show more', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('show_more') ?>"
               value="true" <?= checked($instance['show_more'], 'true') ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('show_more') ?>"
               value="false" <?= checked($instance['show_more'], 'false') ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Show service descriptions', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('descriptions') ?>"
               value="true" <?= checked($instance['descriptions'], 'true') ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('descriptions') ?>"
               value="false" <?= checked($instance['descriptions'], 'false') ?>><?= esc_html__('No', 'team-booking') ?>
        <p><?= esc_html__('Show little calendar only once per day', 'team-booking') ?></p>
        <input type="radio" name="<?= $this->get_field_name('hide_cal') ?>"
               value="true" <?= checked($instance['hide_cal'], 'true') ?>><?= esc_html__('Yes', 'team-booking') ?>
        <input type="radio" name="<?= $this->get_field_name('hide_cal') ?>"
               value="false" <?= checked($instance['hide_cal'], 'false') ?>><?= esc_html__('No', 'team-booking') ?>
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
        $instance['no_timezone'] = $new_instance['no_timezone'];
        $instance['show_more'] = $new_instance['show_more'];
        $instance['descriptions'] = $new_instance['descriptions'];
        $instance['hide_cal'] = $new_instance['hide_cal'];
        $instance['slots_style'] = $new_instance['slots_style'];
        $instance['how_many'] = $new_instance['how_many'];
        $instance['max_events'] = $new_instance['max_events'];

        return $instance;
    }

}
