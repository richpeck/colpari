<?php

class EATableColumns
{
    public function __construct()
    {
    }

    /**
     * @param string $table_name
     * @return array
     */
    public function get_columns($table_name) {

        $columns = array(
            'ea_appointments' => array(
                'id',
                'location',
                'service',
                'worker',
                'name',
                'email',
                'phone',
                'date',
                'start',
                'end',
                'end_date',
                'description',
                'status',
                'user',
                'created',
                'price',
                'ip',
                'session'
            ),
            'ea_connections' => array(
                'id',
                'group_id',
                'location',
                'service',
                'worker',
                'day_of_week',
                'time_from',
                'time_to',
                'day_from',
                'day_to',
                'is_working'
            ),
            'ea_meta_fields' => array(
                'id',
                'type',
                'slug',
                'label',
                'mixed',
                'default_value',
                'visible',
                'required',
                'validation',
                'position'
            ),
            'ea_locations' => array(
                'id',
                'name',
                'address',
                'location',
                'cord'
            ),
            'ea_services' => array(
                'id',
                'name',
                'duration',
                'slot_step',
                'price'
            ),
            'ea_options' => array(
                'id',
                'ea_key',
                'ea_value',
                'type'
            ),
            'ea_staff' => array(
                'id',
                'name',
                'description',
                'email',
                'phone'
            )
        );


        return $columns[$table_name];
    }

    /**
     * @param string $table_name
     * @param array $params
     */
    public function clear_data($table_name, &$params) {
        $columns = $this->get_columns($table_name);

        if (empty($columns)) {
            return;
        }

        foreach ($params as $key => $param) {
            if (!in_array($key, $columns)) {
                unset($params[$key]);
            }
        }
    }
}