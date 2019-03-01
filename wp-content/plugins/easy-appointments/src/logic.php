<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class responsible for App logic
 * reservation, free times...
 */
class EALogic
{

    /**
     * @var EADBModels
     */
    protected $models;

    /**
     * @var EAOptions
     */
    protected $options;

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * EALogic constructor.
     * @param wpdb $wpdb
     * @param EADBModels $models
     * @param EAOptions $options
     */
    function __construct($wpdb, $models, $options)
    {
        $this->wpdb = $wpdb;
        $this->models = $models;
        $this->options = $options;
    }

    /**
     * Get all open slots / times
     *
     * @param  int $location Location
     * @param  int $service Service
     * @param  int $worker Worker
     * @param  datetime $day DateTime
     * @param  int $app_id Previus appointment
     * @param  bool $check_current_day Previus appointment
     * @param  int $block_before
     * @return array Array of free times
     */
    public function get_open_slots(
        $location = null,
        $service = null,
        $worker = null,
        $day = null,
        $app_id = null,
        $check_current_day = true,
        $block_before = 0
    )
    {
        // current day as weekday now (string)
        $day_of_week = date('l', strtotime($day));

        // get current datetime as int
        $time_now = current_time('timestamp', false);

        // add block minutes
        $block_time = $time_now + $block_before * 60;

        // calculate if that is current day that we are looking
        $is_current_day = (date('Y-m-d') == $day);

        $block_date = date('Y-m-d', $block_time);

        $query = $this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}ea_connections WHERE 
			location=%d AND 
			service=%d AND 
			worker=%d AND 
			day_of_week LIKE %s AND 
			is_working = 1 AND 
			(day_from IS NULL OR day_from <= %s) AND 
			(day_to IS NULL OR day_to >= %s)",
            $location, $service, $worker, "%{$day_of_week}%", $day, $day
        );

        $open_days = $this->wpdb->get_results($query);

        $working_hours = array();

        $serviceObj = $this->get_service($service);

        /**
         * Example on Appointment 08:00 - 20:00 today
         */
        foreach ($open_days as $working_day) {
            // upper time 20:00;
            $upper_time = strtotime($working_day->time_to);

            $counter = 0;

            while (true) {
                // 08:00 at first
                $temp_time = strtotime($working_day->time_from);

                // use smaller step
                if (!empty($serviceObj->slot_step)) {
                    $run_time = $serviceObj->slot_step * 60 * $counter++;
                } else {
                    $run_time = $serviceObj->duration * 60 * $counter++;
                }

                // 08:00 at first pass, second 09:00
                $temp_time += $run_time;

                $temp_date_time = strtotime("$day {$working_day->time_from}") + $run_time;

                // is that before upper time limit
                if ($temp_time < $upper_time) {
                    $current_time = date('H:i', $temp_time);

                    // check if current time is greater then slot start time
                    if ($check_current_day && $is_current_day && $time_now > $temp_time) {
                        continue;
                    }

                    // block time - skip if it is under block time
                    if ($block_before > 0 && $check_current_day && $block_time > $temp_date_time) {
                        continue;
                    }

                    if (!array_key_exists($current_time, $working_hours)) {
                        $working_hours[$current_time] = 1;
                    } else {
                        $working_hours[$current_time] += 1;
                    }
                } else {
                    break;
                }
            }
        }

        $service_duration = $serviceObj->duration;

        if (!empty($serviceObj->slot_step)) {
            $service_duration = $serviceObj->slot_step;
        }

        // remove non-working time
        $this->remove_closed_slots($working_hours, $location, $service, $worker, $day, $service_duration);

        // remove already reserved times
        $this->remove_reserved_slots($working_hours, $location, $service, $worker, $day, $service_duration, $app_id);

        // format time
        return $this->format_time($working_hours);
    }

    /**
     * Remove times when is not working
     *
     * @param  array &$slots Free slots
     * @param  int $location Location
     * @param  int $service Service
     * @param  int $worker Worker
     * @param  DateTime $day DateTime
     * @param  time $service_duration Service duration in minuts
     * @return null
     */
    private function remove_closed_slots(&$slots, $location = null, $service = null, $worker = null, $day = null, $service_duration)
    {
        $day_of_week = date('l', strtotime($day));

        $query = $this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}ea_connections WHERE 
			location=%d AND 
			service=%d AND 
			worker=%d AND 
			day_of_week LIKE %s AND 
			is_working = 0 AND 
			(day_from IS NULL OR day_from <= %s) AND 
			(day_to IS NULL OR day_to >= %s)",
            $location, $service, $worker, "%{$day_of_week}%", $day, $day
        );

        $closed_days = $this->wpdb->get_results($query);


        // check all no working times
        foreach ($closed_days as $working_day) {

            $lower_time = strtotime($working_day->time_from);
            $upper_time = strtotime($working_day->time_to);

            $counter = 0;

            // check slots
            foreach ($slots as $temp_time => $value) {
                $current_time = strtotime($temp_time);
//                $current_time_end = strtotime("$temp_time + $service_duration minute");
                $current_time_end = strtotime("$temp_time + $service_duration minute");

                if ($lower_time < $current_time && $upper_time <= $current_time) {
                    // before
                } else if ($lower_time >= $current_time_end && $upper_time > $current_time_end) {
                    // after
                } else {
                    // remove slot
                    $slots[$temp_time] = $value - 1;
                }
            }
        }
    }

    /**
     * Can make reservation for that ip
     *
     * @param $data
     * @return bool Can make reservation
     */
    public function can_make_reservation($data)
    {
        $ip = $data['ip'];

        $result = array(
            'status'  => true,
            'message' => ''
        );

        $query = $this->wpdb->prepare(
            "SELECT id AS no_apps FROM {$this->wpdb->prefix}ea_appointments WHERE 
				ip=%s AND 
				status IN ('abandoned', 'pending') AND
				created >= now() - INTERVAL 1 DAY",
            $ip
        );

        $appIds = $this->wpdb->get_col($query);

        $maxNumber = (int)$this->options->get_option_value('max.appointments', 1);

        if (count($appIds) >= $maxNumber) {
            $result['status'] = false;
            $result['message'] = __('Daily limit of booking request has been reached. Please contact us by email!', 'easy-appointments');
        }

        $result = apply_filters( 'ea_can_make_reservation', $result, $data);

        return $result;
    }

    public function can_update_reservation($appointment, $data)
    {
        $result = array(
            'status'  => true,
            'message' => ''
        );

        $result = apply_filters( 'ea_can_update_reservation', $result, $appointment, $data);

        return $result;
    }

    /**
     * Remove times that are reserved (already booked)
     *
     * @param  array &$slots Free slots
     * @param  int $location Location
     * @param  int $service Service
     * @param  int $worker Worker
     * @param  DateTime $day DateTime
     * @param  time $service_duration Service duration in minuts
     * @param int $app_id
     */
    private function remove_reserved_slots(&$slots, $location, $service, $worker, $day, $service_duration, $app_id = -1)
    {
        if ($app_id == "") {
            $app_id = -1;
        }

        $day_of_week = date('l', strtotime($day));

        $multiple = $this->options->get_option_value('multiple.work', '1');

        $query = $this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}ea_appointments WHERE 
			((location=%d AND service=%d) OR '{$multiple}' = '0') AND 
			worker=%d AND 
			date <= %s AND
			end_date >= %s AND
			id <> %d AND 
			status NOT IN ('abandoned','canceled')",
            $location, $service, $worker, $day, $day, $app_id
        );

        $appointments = $this->wpdb->get_results($query);

        // check all no working times
        foreach ($appointments as $app) {
            $start = ($app->date == $day) ? $app->start : '00:00';
            $end = ($app->end_date == $day) ? $app->end : '23:59';

            $lower_time = strtotime($start);
            $upper_time = strtotime($end);

            if ($app->end === '00:00:00' || $upper_time < $lower_time) {
                $upper_time = strtotime('23:59:59');
            }

            // check slots
            foreach ($slots as $temp_time => $value) {
                $current_time = strtotime($temp_time);
                $current_time_end = strtotime("$temp_time + $service_duration minute");


                if ($lower_time < $current_time && $upper_time <= $current_time) {
                    // before
                } else if ($lower_time >= $current_time_end && $upper_time > $current_time_end) {
                    // after
                } else {

                    // Cross time - remove one slot
                    $slots[$temp_time] = $value - 1;
                }
            }
        }
    }

    /**
     * Return services
     * @param $service_id
     * @return array|null|object|void
     */
    public function get_service($service_id)
    {
        return $this->models->get_row('ea_services', $service_id);
    }

    /**
     * Get all statuses
     *
     * @return array
     */
    public function getStatus()
    {
        return array(
            'pending'     => __('pending', 'easy-appointments'),
            'reservation' => __('reservation', 'easy-appointments'),
            'abandoned'   => __('abandoned', 'easy-appointments'),
            'canceled'    => __('canceled', 'easy-appointments'),
            'confirmed'   => __('confirmed', 'easy-appointments'),
        );
    }

    /**
     * Translation for current statu
     */
    public function get_status_translation($status)
    {
        $statusCollection = $this->getStatus();

        if (array_key_exists($status, $statusCollection)) {
            return $statusCollection[$status];
        }

        return '';
    }

    /**
     * Time format function
     *
     * @param  array &$times Array of slots
     * @return array         Result times array
     */
    public function format_time(&$times)
    {
        $result = array();

        $format = $this->options->get_option_value('time_format');

        foreach ($times as $time => $count) {
            switch ($format) {
                case '00-24':
                    $result[] = array(
                        'count' => $count,
                        'value' => $time,
                        'show'  => $time
                    );
                    break;
                case 'am-pm':
                    $result[] = array(
                        'count' => $count,
                        'value' => $time,
                        'show'  => date("h:i a", strtotime($time))
                    );
                    break;
                default:
                    $result[] = $time;
                    break;
            }
        }

        return $result;
    }
}