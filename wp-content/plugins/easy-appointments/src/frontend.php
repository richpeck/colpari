<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Home of the front end short codes
 */
class EAFrontend
{

    /**
     * @var boolean
     */
    protected $generate_next_option = true;

    /**
     * @var EAOptions
     */
    protected $options;

    /**
     * @var EADBModels
     */
    protected $models;

    /**
     * @var EADateTime
     */
    protected $datetime;

    /**
     * @param EADBModels $models
     * @param EAOptions $options
     * @param $datetime
     */
    function __construct($models, $options, $datetime)
    {
        $this->options  = $options;
        $this->models   = $models;
        $this->datetime = $datetime;
    }

    public function init()
    {
        // register JS
        add_action('wp_enqueue_scripts', array($this, 'init_scripts'));
        // add_action( 'admin_enqueue_scripts', array( $this, 'init' ) );

        // add shortcode standard
        add_shortcode('ea_standard', array($this, 'standard_app'));

        // bootstrap form
        add_shortcode('ea_bootstrap', array($this, 'ea_bootstrap'));
    }

    /**
     * Front end init
     */
    public function init_scripts()
    {
        // start session
        if (!headers_sent() && !session_id()) {
            session_start();
        }

        // bootstrap script
        wp_register_script(
            'ea-momentjs',
            EA_PLUGIN_URL . 'js/libs/moment.min.js',
            array(),
            false,
            true
        );

        wp_register_script(
            'ea-validator',
            EA_PLUGIN_URL . 'js/libs/jquery.validate.min.js',
            array('jquery'),
            false,
            true
        );

        wp_register_script(
            'ea-datepicker-localization',
            EA_PLUGIN_URL . 'js/libs/jquery-ui-i18n.min.js',
            array('jquery', 'jquery-ui-datepicker'),
            false,
            true
        );

        // frontend standard script
        wp_register_script(
            'ea-front-end',
            EA_PLUGIN_URL . 'js/frontend.js',
            array('jquery', 'jquery-ui-datepicker', 'ea-datepicker-localization', 'ea-momentjs'),
            false,
            true
        );

        // bootstrap script
        wp_register_script(
            'ea-bootstrap',
            EA_PLUGIN_URL . 'components/bootstrap/js/bootstrap.js',
            array(),
            false,
            true
        );

        // frontend standard script
        wp_register_script(
            'ea-front-bootstrap',
            EA_PLUGIN_URL . 'js/frontend-bootstrap.js',
            array('jquery', 'jquery-ui-datepicker', 'ea-datepicker-localization', 'ea-momentjs'),
            false,
            true
        );

        wp_register_style(
            'jquery-style',
            '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'
        );

        wp_register_style(
            'ea-bootstrap',
            EA_PLUGIN_URL . 'components/bootstrap/ea-css/bootstrap.css'
        );

        wp_register_style(
            'ea-bootstrap-select',
            EA_PLUGIN_URL . 'components/bootstrap-select/css/bootstrap-select.css'
        );

        wp_register_style(
            'ea-frontend-style',
            EA_PLUGIN_URL . 'css/eafront.css'
        );

        wp_register_style(
            'ea-frontend-bootstrap',
            EA_PLUGIN_URL . 'css/eafront-bootstrap.css'
        );

        // admin style
        wp_register_style(
            'ea-admin-awesome-css',
            EA_PLUGIN_URL . 'css/font-awesome.css'
        );
    }

    /**
     * SHORTCODE
     *
     * Standard widget
     */
    public function standard_app($atts)
    {
        $code_params = shortcode_atts(array(
            'scroll_off'           => false,
            'save_form_content'    => true,
            'start_of_week'        => get_option('start_of_week', 0),
            'default_date'         => date('Y-m-d'),
            'min_date'             => null,
            'max_date'             => null,
            'show_remaining_slots' => '0',
            'show_week'            => '0'
        ), $atts);

        $settings = $this->options->get_options();

        $settings['check'] = wp_create_nonce('ea-bootstrap-form');

        $settings['scroll_off']           = $code_params['scroll_off'];
        $settings['start_of_week']        = $code_params['start_of_week'];
        $settings['default_date']         = $code_params['default_date'];
        $settings['min_date']             = $code_params['min_date'];
        $settings['max_date']             = $code_params['max_date'];
        $settings['show_remaining_slots'] = $code_params['show_remaining_slots'];
        $settings['save_form_content']    = $code_params['save_form_content'];
        $settings['show_week']            = $code_params['show_week'];

        $settings['trans.please-select-new-date'] = __('Please select another day', 'easy-appointments');
        $settings['trans.date-time'] = __('Date & time', 'easy-appointments');
        $settings['trans.price'] = __('Price', 'easy-appointments');

        // datetime format
        $settings['time_format'] = $this->datetime->convert_to_moment_format(get_option('time_format', 'H:i'));
        $settings['date_format'] = $this->datetime->convert_to_moment_format(get_option('date_format', 'F j, Y'));
        $settings['default_datetime_format'] = $this->datetime->convert_to_moment_format($this->datetime->default_format());

        $settings['trans.nonce-expired'] = __('Form validation code expired. Please refresh page in order to continue.', 'easy-appointments');
        $settings['trans.internal-error'] = __('Internal error. Please try again later.', 'easy-appointments');
        $settings['trans.ajax-call-not-available'] = __('Unable to make ajax request. Please try again later.', 'easy-appointments');

        $customCss = $settings['custom.css'];
        $customCss = strip_tags($customCss);
        $customCss = str_replace(array('<?php', '?>', "\t"), array('', '', ''), $customCss);

        wp_localize_script('ea-front-end', 'ea_settings', $settings);

        wp_enqueue_script('underscore');
        wp_enqueue_script('ea-validator');
        wp_enqueue_script('ea-front-end');
        //wp_enqueue_script( 'ea-datepicker-localization' );

        if (empty($settings['css.off'])) {
            wp_enqueue_style('jquery-style');
            wp_enqueue_style('ea-frontend-style');
            wp_enqueue_style('ea-admin-awesome-css');
        }

        $meta = $this->models->get_all_rows("ea_meta_fields", array(), array('position' => 'ASC'));
        $custom_form = $this->generate_custom_fields($meta);

        // add custom CSS
        wp_add_inline_style( 'ea-frontend-style', $customCss );

        ob_start();

        // require tempalte
        require EA_SRC_DIR . 'templates/booking.overview.tpl.php';

        ?>
        <script type="text/javascript">
            var ea_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        </script>
        <div class="ea-standard">
            <form>
                <div class="step">
                    <div class="block"></div>
                    <label class="ea-label"><?php _e($this->options->get_option_value("trans.location"), 'easy-appointments') ?></label><select
                        name="location" data-c="location"
                        class="filter"><?php $this->get_options("locations") ?></select>
                </div>
                <div class="step">
                    <div class="block"></div>
                    <label class="ea-label"><?php _e($this->options->get_option_value("trans.service"), 'easy-appointments') ?></label><select
                        name="service" data-c="service" class="filter"
                        data-currency="<?php echo $this->options->get_option_value("trans.currency") ?>"><?php $this->get_options("services") ?></select>
                </div>
                <div class="step">
                    <div class="block"></div>
                    <label class="ea-label"><?php _e($this->options->get_option_value("trans.worker"), 'easy-appointments') ?></label><select
                        name="worker" data-c="worker" class="filter"><?php $this->get_options("staff") ?></select>
                </div>
                <div class="step calendar" class="filter">
                    <div class="block"></div>
                    <div class="date"></div>
                </div>
                <div class="step" class="filter">
                    <div class="block"></div>
                    <div class="time"></div>
                </div>
                <div class="step final">
                    <div class="block"></div>
                    <p class="section"><?php _e('Personal information', 'easy-appointments'); ?></p>
                    <small><?php _e('Fields with * are required', 'easy-appointments'); ?></small>
                    <br>
                    <?php echo $custom_form; ?>
                    <br>
                    <p class="section"><?php _e('Booking overview', 'easy-appointments'); ?></p>
                    <div id="booking-overview"></div>
                    <?php if (!empty($settings['show.iagree'])) : ?>
                        <p>
                            <label
                                style="font-size: 65%; width: 80%;" class="i-agree"><?php _e('I agree with terms and conditions', 'easy-appointments'); ?>
                                * : </label><input style="width: 15%;" type="checkbox" name="iagree"
                                                   data-rule-required="true"
                                                   data-msg-required="<?php _e('You must agree with terms and conditions', 'easy-appointments'); ?>">
                        </p>
                        <br>
                    <?php endif; ?>
                    <?php if (!empty($settings['gdpr.on'])) : ?>
                        <p>
                            <label
                                    style="font-size: 65%; width: 80%;" class="gdpr"><?php echo $settings['gdpr.label'];?>
                                * : </label><input style="width: 15%;" type="checkbox" name="iagree"
                                                   data-rule-required="true"
                                                   data-msg-required="<?php echo $settings['gdpr.message'];?>">
                        </p>
                        <br>
                    <?php endif; ?>
                    <div style="display: inline-flex;">
                        <?php echo apply_filters('ea_checkout_button', '<button class="ea-btn ea-submit">' . __('Submit', 'easy-appointments') . '</button>'); ?>
                        <button class="ea-btn ea-cancel"><?php _e('Cancel', 'easy-appointments'); ?></button>
                    </div>
                </div>
            </form>
            <div id="ea-loader"></div>
        </div>
        <?php

        apply_filters('ea_checkout_script', '');

        return ob_get_clean();
    }

    /**
     * Generate custom fields inside standard form
     *
     * @param $meta
     * @return string
     */
    public function generate_custom_fields($meta)
    {
        $html = '';

        // TODO add phone field

        foreach ($meta as $item) {

            if (empty($item->visible)) {
                continue;
            }

            $r = !empty($item->required);

            $star = ($r) ? ' * ' : ' ';

            $html .= '<p>';
            $html .= '<label>' . __($item->label, 'easy-appointments') . $star . ': </label>';

            if ($item->type == 'INPUT') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'easy-appointments') . '"' : '';
                $email = ($item->validation == 'email') ? 'data-msg-email="' . __('Please enter a valid email address', 'easy-appointments') . '" data-rule-email="true"' : '';

                $html .= '<input class="custom-field" type="text" name="' . $item->slug . '" ' . $msg . ' ' . $email . ' />';
            } else if ($item->type == 'SELECT') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'easy-appointments') . '"' : '';

                $html .= '<select class="form-control custom-field" name="' . $item->slug . '" ' . $msg . '>';
                $options = explode(',', $item->mixed);

                foreach ($options as $o) {
                    if ($o == '-') {
                        $html .= '<option value="">-</option>';
                    } else {
                        $html .= '<option value="' . $o . '" >' . $o . '</option>';
                    }
                }

                $html .= '</select>';

            } else if ($item->type == 'TEXTAREA') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'easy-appointments') . '"' : '';
                $html .= '<textarea class="form-control custom-field" rows="3" style="height: auto;" name="' . $item->slug . '" ' . $msg . '></textarea>';
            }

            $html .= '</p>';
        }

        return $html;
    }

    /**
     * SHORTCODE
     *
     * Bootstrap
     * @param array $atts
     * @return string
     */
    public function ea_bootstrap($atts)
    {

        $code_params = shortcode_atts(array(
            'location'             => null,
            'service'              => null,
            'worker'               => null,
            'width'                => '400px',
            'scroll_off'           => false,
            'save_form_content'    => true,
            'layout_cols'          => '1',
            'start_of_week'        => get_option('start_of_week', 0),
            'rtl'                  => '0',
            'default_date'         => date('Y-m-d'),
            'min_date'             => null,
            'max_date'             => null,
            'show_remaining_slots' => '0',
            'show_week'            => '0'
        ), $atts);

        // check params
        apply_filters('ea_bootstrap_shortcode_params', $atts);

        // used inside template ea_bootstrap.tpl.php
        $location_id = $code_params['location'];
        $service_id  = $code_params['service'];
        $worker_id   = $code_params['worker'];

        $settings = $this->options->get_options();

        $settings['check'] = wp_create_nonce('ea-bootstrap-form');

        $settings['width']                 = $code_params['width'];
        $settings['scroll_off']            = $code_params['scroll_off'];
        $settings['layout_cols']           = $code_params['layout_cols'];
        $settings['start_of_week']         = $code_params['start_of_week'];
        $settings['rtl']                   = $code_params['rtl'];
        $settings['default_date']          = $code_params['default_date'];
        $settings['min_date']              = $code_params['min_date'];
        $settings['max_date']              = $code_params['max_date'];
        $settings['show_remaining_slots']  = $code_params['show_remaining_slots'];
        $settings['show_week']             = $code_params['show_week'];
        $settings['save_form_content']     = $code_params['save_form_content'];

        // LOCALIZATION
        $settings['trans.please-select-new-date'] = __('Please select another day', 'easy-appointments');
        $settings['trans.personal-informations'] = __('Personal information', 'easy-appointments');
        $settings['trans.field-required'] = __('This field is required.', 'easy-appointments');
        $settings['trans.error-email'] = __('Please enter a valid email address', 'easy-appointments');
        $settings['trans.error-name'] = __('Please enter at least 3 characters.', 'easy-appointments');
        $settings['trans.error-phone'] = __('Please enter at least 3 digits.', 'easy-appointments');
        $settings['trans.fields'] = __('Fields with * are required', 'easy-appointments');
        $settings['trans.email'] = __('Email', 'easy-appointments');
        $settings['trans.name'] = __('Name', 'easy-appointments');
        $settings['trans.phone'] = __('Phone', 'easy-appointments');
        $settings['trans.comment'] = __('Comment', 'easy-appointments');
        $settings['trans.overview-message'] = __('Please check your appointment details below and confirm:', 'easy-appointments');
        $settings['trans.booking-overview'] = __('Booking overview', 'easy-appointments');
        $settings['trans.date-time'] = __('Date & time', 'easy-appointments');
        $settings['trans.submit'] = __('Submit', 'easy-appointments');
        $settings['trans.cancel'] = __('Cancel', 'easy-appointments');
        $settings['trans.price'] = __('Price', 'easy-appointments');
        $settings['trans.iagree'] = __('I agree with terms and conditions', 'easy-appointments');
        $settings['trans.field-iagree'] = __('You must agree with terms and conditions', 'easy-appointments');
        $settings['trans.slot-not-selectable'] = __('You can\'t select this time slot!\'', 'easy-appointments');

        $settings['trans.nonce-expired'] = __('Form validation code expired. Please refresh page in order to continue.', 'easy-appointments');
        $settings['trans.internal-error'] = __('Internal error. Please try again later.', 'easy-appointments');
        $settings['trans.ajax-call-not-available'] = __('Unable to make ajax request. Please try again later.', 'easy-appointments');

        // datetime format
        $settings['time_format'] = $this->datetime->convert_to_moment_format(get_option('time_format', 'H:i'));
        $settings['date_format'] = $this->datetime->convert_to_moment_format(get_option('date_format', 'F j, Y'));
        $settings['default_datetime_format'] = $this->datetime->convert_to_moment_format($this->datetime->default_format());

        // CUSTOM CSS
        $customCss = $settings['custom.css'];
        $customCss = strip_tags($customCss);
        $customCss = str_replace(array('<?php', '?>', "\t"), array('', '', ''), $customCss);

        unset($settings['custom.css']);

        $rows = $this->models->get_all_rows("ea_meta_fields", array(), array('position' => 'ASC'));

        foreach ($rows as $key => $row) {
            $rows[$key]->label = __($row->label, 'easy-appointments');
        }

        $settings['MetaFields'] = $rows;

        wp_localize_script('ea-front-bootstrap', 'ea_settings', $settings);

        wp_enqueue_script('underscore');
        wp_enqueue_script('ea-validator');
        wp_enqueue_script('ea-bootstrap');
        // wp_enqueue_script( 'ea-datepicker-localization' );
        // wp_enqueue_script( 'ea-bootstrap-select' );
        wp_enqueue_script('ea-front-bootstrap');

        if (empty($settings['css.off'])) {
            wp_enqueue_style('ea-bootstrap');
            // wp_enqueue_style( 'ea-bootstrap-select' );
            // wp_enqueue_style( 'ea-frontend-style' );
            wp_enqueue_style('ea-admin-awesome-css');
            wp_enqueue_style('ea-frontend-bootstrap');
        }

        // add custom CSS
        wp_add_inline_style( 'ea-bootstrap', $customCss );

        ob_start();

        // echo "<style type='text/css'>{$customCss}</style>";

        // FORM TEMPLATE
        if ($settings['rtl'] == '1') {
            require EA_SRC_DIR . 'templates/ea_bootstrap_rtl.tpl.php';
        } else {
            require EA_SRC_DIR . 'templates/ea_bootstrap.tpl.php';
        }

        // OVERVIEW TEMPLATE
        require EA_SRC_DIR . 'templates/booking.overview.tpl.php';

        ?>
        <div class="ea-bootstrap bootstrap"></div><?php

        // load scripts if there are some
        apply_filters('ea_checkout_script', '');

        return ob_get_clean();
    }

    /**
     * Get options for select fields
     *
     * @param $type
     * @param null $location_id
     * @param null $service_id
     * @param null $worker_id
     */
    private function get_options($type, $location_id = null, $service_id = null, $worker_id = null)
    {
        if (!$this->generate_next_option) {
            return;
        }

        $hide_price = $this->options->get_option_value('price.hide', '0');
        $before = $this->options->get_option_value('currency.before', '0');
        $currency = $this->options->get_option_value('trans.currency', '$');

//        $rows = $this->models->get_all_rows("ea_$type");
        $rows = $this->models->get_frontend_select_options("ea_$type", $location_id, $service_id, $worker_id);

        // If there is only one result, like one worker in whole system or one location etc
        if (count($rows) == 1) {
            $price = !empty($rows[0]->price) ? " data-price='{$rows[0]->price}'" : '';
            if ($type === 'services') {
                echo "<option data-duration='{$rows[0]->duration}' data-slot_step='{$rows[0]->slot_step}' value='{$rows[0]->id}' selected='selected'$price>{$rows[0]->name}</option>";
            } else {
                echo "<option value='{$rows[0]->id}' selected='selected'$price>{$rows[0]->name}</option>";
            }
            return;
        }

        // if there is only one preselected option, like personal calendar for one worker
        if ($type === 'services' && $service_id !== null) {
            foreach ($rows as $row) {
                if ($row->id == $service_id) {
                    $price = !empty($row->price) ? " data-price='{$row->price}'" : '';
                    echo "<option value='{$row->id}' data-duration='{$row->duration}' data-slot_step='{$row->slot_step}' selected='selected'$price>{$row->name}</option>";
                    return;
                }
            }
        }

        if ($type === 'locations' && $location_id !== null) {
            foreach ($rows as $row) {
                if ($row->id == $location_id) {
                    $price = !empty($row->price) ? " data-price='{$row->price}'" : '';
                    echo "<option value='{$row->id}' selected='selected'$price>{$row->name}</option>";
                    return;
                }
            }
        }

        if ($type === 'staff' && $worker_id !== null) {
            foreach ($rows as $row) {
                if ($row->id == $worker_id) {
                    $price = !empty($row->price) ? " data-price='{$row->price}'" : '';
                    echo "<option value='{$row->id}' selected='selected'$price>{$row->name}</option>";
                    return;
                }
            }
        }

        echo "<option value='' selected='selected'>-</option>";

        foreach ($rows as $row) {
            $price = !empty($row->price) ? " data-price='{$row->price}'" : '';

            // case when we are hiding price
            if ($hide_price == '1') {

                // for all other types
                if ($type != 'services') {
                    echo "<option value='{$row->id}'>{$row->name}</option>";
                } else if ($type == 'services') {
                    // for service
                    echo "<option data-duration='{$row->duration}' data-slot_step='{$row->slot_step}' value='{$row->id}'>{$row->name}</option>";
                }

            } else if ($type == 'services') {
                $name = $row->name;
                $name_price = ($before == '1') ? $name . ' ' . $currency . $row->price : $name . ' ' . $row->price . $currency;
                echo "<option data-duration='{$row->duration}' data-slot_step='{$row->slot_step}' value='{$row->id}'$price>{$name_price}</option>";
            } else {
                echo "<option value='{$row->id}'>{$row->name}</option>";
            }
        }
    }
}
