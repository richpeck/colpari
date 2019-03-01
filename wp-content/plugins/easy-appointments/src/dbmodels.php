<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * DataBase models
 */
class EADBModels
{
    /**
     * WPDB
     *
     * @var wpdb $wpdb
     **/
    protected $wpdb;

    /**
     * @var EATableColumns
     */
    protected $table_columns;

    /**
     * @var EAOptions
     */
    protected $options;

    function __construct($wpdb, $table_columns, $options)
    {
        $this->wpdb = $wpdb;
        $this->table_columns = $table_columns;
        $this->options = $options;
    }

    /**
     * @param string $table_name
     * @param array $data
     * @param array $order
     * @return array|null|object
     */
    public function get_all_rows($table_name, $data = array(), $order = array('id' => 'DESC'))
    {

        $ignore = array('action');

        $where = '';

        $params = array();

        foreach ($data as $key => $value) {
            if (!in_array($key, $ignore)) {

                $helper = '=';

                // if equal or greater
                if (strpos($value, '+') === 0) {
                    $helper = '>=';
                    $value = substr($value, 1);

                    // if equal or smaller
                } else if (strpos($value, '-') === 0) {
                    $helper = '<=';
                    $value = substr($value, 1);
                }

                if (in_array($key, array('from', 'to'))) {
                    $key = 'date';
                }

                if (is_numeric($value)) {
                    $where .= " AND {$key}{$helper}%d";
                } else {
                    $where .= " AND {$key}{$helper}%s";
                }

                $params[] = $value;
            }
        }

        if ($where === '') {
            $where = ' AND 1=%d';
            $params[] = 1;
        }

        $order_part = array();

        foreach ($order as $key => $value) {
            $order_part[] = $key . ' ' . $value;
        }

        $order_part = implode(',', $order_part);

        $query = $this->wpdb->prepare("SELECT * 
			FROM {$this->wpdb->prefix}{$table_name} 
			WHERE 1$where 
			ORDER BY {$order_part}",
            $params
        );

        return $this->wpdb->get_results($query);
    }

    public function get_all_appointments($data)
    {
        $tableName = $this->wpdb->prefix . 'ea_appointments';

        $params = array(
            $data['from'],
            $data['to']
        );

        $location = '';
        $service = '';
        $worker = '';
        $status = '';

        if (array_key_exists('location', $data)) {
            $location = ' AND location = %d';
            $params[] = $data['location'];
        }

        if (array_key_exists('service', $data)) {
            $service = ' AND service = %d';
            $params[] = $data['service'];
        }

        if (array_key_exists('worker', $data)) {
            $worker = ' AND worker = %d';
            $params[] = $data['worker'];
        }

        if (array_key_exists('status', $data)) {
            $status = ' AND status = %s';
            $params[] = $data['status'];
        }

        $query = "SELECT * 
			FROM $tableName
			WHERE 1 AND date >= %s AND date <= %s {$location}{$service}{$worker}{$status}
			ORDER BY id DESC";

        $apps = $this->wpdb->get_results($this->wpdb->prepare($query, $params), OBJECT_K);

        $ids = array_keys($apps);

        if (!empty($ids)) {
            $fields = $this->get_fields_for_apps($ids);

            foreach ($fields as $f) {
                if (array_key_exists($f->app_id, $apps)) {
                    $apps[$f->app_id]->{$f->slug} = $f->value;
                }
            }
        }

        return array_values($apps);
    }

    /**
     * List of custom fields for appointments
     *
     * @param array $ids
     * @return array|null|object
     */
    public function get_fields_for_apps($ids = array())
    {
        $meta = $this->wpdb->prefix . 'ea_meta_fields';
        $fields = $this->wpdb->prefix . 'ea_fields';

        $apps = implode(',', $ids);

        $query = "SELECT f.app_id, m.slug, f.value FROM {$meta} m JOIN {$fields} f ON (m.id = f.field_id) WHERE f.app_id IN ($apps)";
        $result = $this->wpdb->get_results($query);

        return $result;
    }

    /**
     * @param $table_name
     * @param array $order
     * @return mixed|string|void
     */
    public function get_pre_cache_json($table_name, $order = array('id' => 'DESC'))
    {
        $tmp = array();

        foreach ($order as $key => $value) {
            $tmp[] = "{$key} {$value}";
        }

        $order = implode(',', $tmp);

        $query = "SELECT * 
			FROM {$this->wpdb->prefix}{$table_name} 
			ORDER BY {$order}";

        return json_encode($this->wpdb->get_results($query));
    }

    /**
     * @param $table_name
     * @param $id
     * @param string $output_type
     * @return array|null|object|void
     */
    public function get_row($table_name, $id, $output_type = OBJECT)
    {

        $query = $this->wpdb->prepare("SELECT * 
			FROM {$this->wpdb->prefix}{$table_name}
			WHERE id=%d",
            $id
        );

        return $this->wpdb->get_row($query, $output_type);
    }

    /**
     * @param $table_name
     * @param $data
     * @param bool $json
     * @param bool $forceStrings
     * @return bool|int|stdClass
     */
    public function replace($table_name, $data, $json = false, $forceStrings = false)
    {

        // strip out fields that are not mapped inside table
        $this->table_columns->clear_data($table_name, $data);

        // full table name
        $table_name = $this->wpdb->prefix . $table_name;

        $types = array();

        foreach ($data as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                // remove key->value
                unset($data[$key]);

                continue;
            }

            if (strlen($value) > 0 && substr($value, 0, 1) == '0') {
                $types[] = '%s';
            } else {
                if (is_float($value) && !$forceStrings) {
                    // float type
                    $types[] = '%f';

                } else if (is_integer($value) && !$forceStrings) {
                    // integer type
                    $types[] = '%d';

                } else {
                    // string as default
                    $types[] = '%s';
                }
            }
        }

        $insert_id = -1;

        // check if there is id set, if true just update
        if (array_key_exists('id', $data) && $data['id'] != '-1' && !empty($data['id'])) {
            $return = $this->wpdb->update(
                $table_name,
                $data,
                array('id' => $data['id']),
                $types
            );

            $insert_id = $data['id'];
        } else {
            // clone - new
            if (array_key_exists('id', $data)) {
                unset($data['id']);
                unset($types[0]);
            }

            $return = $this->wpdb->insert(
                $table_name,
                $data,
                $types
            );

            $insert_id = $this->wpdb->insert_id;
        }

        if ($return === false) {
            return false;
        }

        if ($json) {
            $output = new stdClass;
            $output->id = "{$insert_id}";
            return $output;
        }

        return $this->wpdb->insert_id;
    }

    /**
     * @param $table
     * @param $data
     * @param bool $json
     * @return false|int
     */
    public function delete($table, $data, $json = false)
    {

        $table_name = $this->wpdb->prefix . $table;

        if ($table == 'ea_fields') {
            return $this->wpdb->delete($table_name, array('app_id' => (int)$data['app_id']), array('%d'));
        }

        return $this->wpdb->delete($table_name, array('id' => (int)$data['id']), array('%d'));
    }

    /**
     * @param $options
     * @param string $order
     * @return array|null|object
     */
    public function get_next($options, $order = '')
    {
        $table_name = $this->wpdb->prefix . 'ea_connections';

        $vars = '';
        $values = array();

        foreach ($options as $key => $value) {
            if ($key === 'next') {
                continue;
            }

            if (is_numeric($value)) {
                $vars .= " AND $key=%d";
            } else {
                $vars .= " AND $key=%s";
            }

            $values[] = $value;
        }

        $query = $this->wpdb->prepare(
            "SELECT DISTINCT {$options['next']} FROM $table_name WHERE 1=1$vars",
            $values
        );

        $next_rows_raw = $this->wpdb->get_results($query, ARRAY_N);

        $next_rows = array();

        foreach ($next_rows_raw as $value) {
            $next_rows[] = $value[0];
        }

        $ids = implode(',', $next_rows);

        if ($options['next'] == 'worker') {
            $entity_table = 'staff';
        } else {
            $entity_table = $options['next'] . 's';
        }

        $next_table = $this->wpdb->prefix . "ea_{$entity_table}";

        $query = "SELECT * FROM $next_table WHERE id IN ({$ids})";

        if ($order != '') {
            $query .= $order;
        }

        return $this->wpdb->get_results($query);
    }

    /**
     * Check table name
     * @param  [type] $table_name [description]
     * @return bool [type]
     */
    private static function check_table_name($table_name)
    {
        $tables = array(
            'appointments',
            'connections',
            'locations',
            'options',
            'services',
            'staff',
            'fields',
            'meta_fields'
        );

        return in_array($table_name, $tables);
    }

    /**
     * Retrive all data for single appointment
     */
    public function get_appintment_by_id($id)
    {

        $table_app = $this->wpdb->prefix . 'ea_appointments';
        $table_services = $this->wpdb->prefix . 'ea_services';
        $table_workers = $this->wpdb->prefix . 'ea_staff';
        $table_locations = $this->wpdb->prefix . 'ea_locations';
        $table_meta = $this->wpdb->prefix . 'ea_meta_fields';
        $table_fields = $this->wpdb->prefix . 'ea_fields';

        $query = $this->wpdb->prepare("SELECT 
				a.*,
				s.name AS service_name,
				s.duration AS service_duration,
				s.price AS service_price,
				w.name AS worker_name,
				w.email AS worker_email,
				w.phone AS worker_phone,
				l.name AS location_name,
				l.address AS location_address,
				l.location AS location_location
			FROM 
				{$table_app} a 
			JOIN 
				{$table_services} s
				ON(a.service = s.id)
			JOIN 
				{$table_locations} l
				ON(a.location = l.id)
			JOIN 
				{$table_workers} w
				ON(a.worker = w.id)
			WHERE a.id = %d", $id);

        $results = $this->wpdb->get_results($query, ARRAY_A);

        $f_query = $this->wpdb->prepare("SELECT m.slug, f.value FROM {$table_meta} m JOIN $table_fields f ON (m.id = f.field_id) WHERE f.app_id = %d", $id);

        $fields = $this->wpdb->get_results($f_query);

        if (count($results) == 1) {
            foreach ($fields as $f) {
                $results[0][$f->slug] = $f->value;
            }

            return $results[0];
        }

        return array();
    }

    /**
     * Removes reservation older then 6 minutes
     */
    public function delete_reservations()
    {
        $table_app = $this->wpdb->prefix . 'ea_appointments';

        $query = "DELETE FROM $table_app WHERE status = 'reservation' AND created < (NOW() - INTERVAL 6 MINUTE)";

        $this->wpdb->query($query);
    }

    /**
     * @param $table_name
     * @param null $location_id
     * @param null $service_id
     * @param null $worker_id
     * @return array|null|object
     */
    public function get_frontend_select_options($table_name, $location_id = null, $service_id = null, $worker_id = null)
    {
        $table = $this->wpdb->prefix . $table_name;
        $connections = $this->wpdb->prefix . 'ea_connections';

        $query = '';

        switch ($table_name) {
            case 'ea_locations':
                $query  = "SELECT DISTINCT l.* FROM {$table} l INNER JOIN $connections c ON (l.id = c.location) WHERE c.is_working=1";

                if (!empty($service_id) && is_numeric($service_id)) {
                    $query .= ' AND c.service=' . $service_id;
                }

                if (!empty($worker_id) && is_numeric($worker_id)) {
                    $query .= ' AND c.worker=' . $worker_id;
                }

                $query .= $this->get_order_by_part('ea_locations', true);

                break;
            case 'ea_services':
                $query  = "SELECT DISTINCT s.* FROM {$table} s INNER JOIN $connections c ON (s.id = c.service) WHERE c.is_working=1";

                if (!empty($location_id) && is_numeric($location_id)) {
                    $query .= ' AND c.location=' . $location_id;
                }

                if (!empty($worker_id) && is_numeric($worker_id)) {
                    $query .= ' AND c.worker=' . $worker_id;
                }

                $query .= $this->get_order_by_part('ea_services', true);

                break;
            case 'ea_staff':
                $query  = "SELECT DISTINCT w.* FROM {$table} w INNER JOIN $connections c ON (w.id = c.worker) WHERE c.is_working=1";

                if (!empty($location_id) && is_numeric($location_id)) {
                    $query .= ' AND c.location=' . $location_id;
                }

                if (!empty($service_id) && is_numeric($service_id)) {
                    $query .= ' AND c.service=' . $service_id;
                }

                $query .= $this->get_order_by_part('ea_workers', true);

                break;
        };

        return $this->wpdb->get_results($query);
    }

    public function clear_options()
    {
        $table = $this->wpdb->prefix . 'ea_options';

        $this->wpdb->query("DELETE FROM $table");
    }

    /**
     *
     */
    public function get_connections_combinations()
    {
        $connections = $this->wpdb->prefix . 'ea_connections';

        $query = "SELECT location, service, worker FROM $connections WHERE is_working=1";

        return $this->wpdb->get_results($query);
    }

    /**
     * @return array
     */
    public function get_all_tags_for_template()
    {
        $fields = json_decode($this->get_pre_cache_json('ea_meta_fields', array('position' => 'ASC')), true);

        // default tags
        $default = array(
            'id', 'location', 'service', 'worker', 'date', 'start', 'end', 'end_date', 'status', 'user', 'price', 'ip', 'session'
        );

        $mapped = array_map(function($element) {
            return $element['slug'];
        }, $fields);

        return array_merge($default, $mapped);
    }

    /**
     * @return int
     */
    public function get_next_meta_field_id() {
        $meta = $this->wpdb->prefix . 'meta_fields';

        $query = "SELECT MAX(id) FROM $meta";

        $max = (int)$this->wpdb->get_var($query);

        return $max + 1;
    }


    public function update_option($option)
    {
        $table_name = $this->wpdb->prefix . 'ea_options';
        $key = $option['ea_key'];
        $query = $this->wpdb->prepare("DELETE FROM $table_name WHERE ea_key=%s", $key);

        $this->wpdb->query($query);

        return $this->wpdb->insert($table_name, $option);
    }

    /**
     * @param string $table_name
     * @param bool $as_string
     * @return string|array
     */
    public function get_order_by_part($table_name, $as_string = false)
    {
        /**
         *
         */
        $mapping = array(
            'ea_locations' => array(
                'sort'  => 'sort.locations-by',
                'order' => 'order.locations-by'
            ),
            'ea_workers'   => array(
                'sort'  => 'sort.workers-by',
                'order' => 'order.workers-by'
            ),
            'ea_services'  => array(
                'sort'  => 'sort.services-by',
                'order' => 'order.services-by'
            ),
        );

        if (!array_key_exists($table_name, $mapping)) {
            if ($as_string) {
                return " ORDER BY `id` DESC";
            }

            return array('id' => 'DESC');
        }

        $column = $this->options->get_option_value($mapping[$table_name]['sort'], 'id');
        $order = $this->options->get_option_value($mapping[$table_name]['order'], 'DESC');

        if (!in_array($order, array('ASC', 'DESC'))) {
            if ($as_string) {
                return " ORDER BY `id` DESC";
            }

            return array('id' => 'DESC');
        }

        $this->wpdb->escape_by_ref($column);
        $this->wpdb->escape_by_ref($order);

        if ($as_string) {

            return " ORDER BY `$column` $order";
        }

        return array($column => $order);
    }
}
