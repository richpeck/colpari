<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Options service
 */
class EAOptions
{
    /**
     * @var wpdb
     */
    protected $wpdb;

    protected $current_options;

    /**
     * EAOptions constructor.
     * @param $wpdb
     */
    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function get_default_options() {
        return array(
            'mail.pending'                  => 'pending',
            'mail.reservation'              => 'reservation',
            'mail.canceled'                 => 'canceled',
            'mail.confirmed'                => 'confirmed',
            'mail.admin'                    => '',
            'trans.service'                 => 'Service',
            'trans.location'                => 'Location',
            'trans.worker'                  => 'Worker',
            'trans.done_message'            => 'Done',
            'time_format'                   => '00-24',
            'trans.currency'                => '$',
            'pending.email'                 => '',
            'price.hide'                    => '0',
            'datepicker'                    => 'en-US',
            'send.user.email'               => '0',
            'custom.css'                    => '',
            'show.iagree'                   => '0',
            'cancel.scroll'                 => 'calendar',
            'multiple.work'                 => '1',
            'compatibility.mode'            => '0',
            'pending.subject.email'         => 'New Reservation #id#',
            'send.from.email'               => '',
            'css.off'                       => '0',
            'submit.redirect'               => '',
            'pending.subject.visitor.email' => 'Reservation #id#',
            'block.time'                    => '0',
            'max.appointments'              => '5',
            'pre.reservation'               => '0',
            'default.status'                => 'pending',
            'send.worker.email'             => '0',
            'currency.before'               => '0',
            'nonce.off'                     => '0',
            'gdpr.on'                       => '0',
            'gdpr.label'                    => 'By using this form you agree with the storage and handling of your data by this website.',
            'gdpr.link'                     => '',
            'gdpr.message'                  => 'You need to accept the privacy checkbox',
            'sort.workers-by'               => 'id',
            'sort.services-by'              => 'id',
            'sort.locations-by'             => 'id',
            'order.workers-by'              => 'DESC',
            'order.services-by'             => 'DESC',
            'order.locations-by'            => 'DESC'
        );
    }

    /**
     * Get data that are going to be inserted to database
     *
     * @return array
     */
    public function get_insert_options()
    {
        $options = $this->get_default_options();
        $output = array();

        foreach ($options as $key => $value) {
            $output[] = array(
                'ea_key'   => $key,
                'ea_value' => $value,
                'type'     => 'default'
            );
        }

        return $output;
    }

    public function get_mixed_options()
    {
        $missing = array();

        $defaults = $this->get_insert_options();
        $current = $this->cache_options();

        foreach ($defaults as $default) {
            $is_missing = true;

            foreach ($current as $option) {
                if ($option['ea_key'] == $default['ea_key']) {
                    $is_missing = false;
                }
            }

            if ($is_missing) {
                $missing[] = $default;
            }
        }

        return array_merge($current, $missing);

    }

    /**
     * Options for cache inline usage on front-end
     *
     * @return array
     */
    public function cache_options()
    {
        $options = $this->get_options();

        $output = array();

        foreach ($options as $key => $value) {
            $output[] = array(
                'ea_key'   => $key,
                'ea_value' => $value,
                'type'     => 'default'
            );
        }

        return $output;
    }

    /**
     * Get option from database
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function get_option_value($key, $default = null)
    {
        // load options if there are not cached
        if (empty($this->current_options)) {
            $this->current_options = $this->get_options_from_db();
        }

        if (!array_key_exists($key, $this->current_options)) {
            return $default;
        }

        return $this->current_options[$key];
    }

    /**
     * Get all EA options [key => value]
     *
     * @return array
     */
    public function get_options()
    {
        // load options if there are not cached
        if (empty($this->current_options)) {
            $this->current_options = $this->get_options_from_db();
        }

        return $this->current_options;
    }

    /**
     * Get options, default options are overwritten by db ones
     *
     * @return array
     */
    protected function get_options_from_db()
    {
        $table_name = $this->wpdb->prefix . 'ea_options';

        $query =
            "SELECT ea_key, ea_value 
             FROM $table_name";

        $output = $this->wpdb->get_results($query, OBJECT_K);

        $db_options = array();

        foreach ($output as $key => $value) {
            $db_options[$key] = $value->ea_value;
        }

        $default = $this->get_default_options();

        // combine options from db and defaults
        return array_merge($default, $db_options);
    }
}
