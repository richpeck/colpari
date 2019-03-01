<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress_Database_SaveRepository
{
    /** @var object wpdb */
    private $db;

    /** @var string {prefix}nf3_objects */
    private $table;

    /** @var string {prefix}nf3_object_meta */
    private $metatable;

    /**
     * Setup Database Connection and prefix table names.
     */
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'nf3_objects';
        $this->metatable = $this->db->prefix . 'nf3_object_meta';
    }

    /**
     * Get Save Objects from the Ninja Forms Database.
     *      $this->get( [ 'user_id' => 1, 'form_id' => 25 ] );
     * @param array $where
     * @return array [ [ 'form_id' => 25, 'user_id' => 1, 'fields' => [] ] ]
     */
    public function get( $where = array() )
    {
        $sql = "                
            SELECT `parent_id`, `key`, `value` FROM `{$this->metatable}`
            WHERE `parent_id` IN ( SELECT id FROM `{$this->table}` WHERE `type` = 'save' )
        ";

        if( ! empty( $where ) ){

            /*
             * Append addition WHERE clauses to the query.
             * TODO: Maybe extract query building functionality.
             */
            foreach( $where as $key => $value ){
            	if ( ! ctype_digit( (string) $value ) ) {
            		$value = "'$value'";
	            }
	            
                $sql .= "
                    AND `parent_id` IN (
                        SELECT `parent_id` FROM `{$this->metatable}`
                        WHERE `key` = '{$key}' AND `value` = {$value}
                    )
                ";
            }
        }

        $results = $this->db->get_results( $sql, ARRAY_A );

        // TODO: Maybe extract data formatting functionality.
        $saves = array();
        foreach( $results as $result ){

            // Extract result data into easily usable variables.
            extract( $result );

            // Initialize the save data array.
            if( ! isset( $saves[ $parent_id ] ) ){
                // Include the save_id as an item for use by JS.
                $saves[ $parent_id ] = array( 'save_id' => $parent_id );
            }

            // Format Dates from stored timestamp.
            if( 'updated' == $key ) {
                $value = date( 'Y-m-d H:i:s', $value );
                $value = get_date_from_gmt( $value, get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );

                // While not currently being used, adding this new option allows us to later provide an unformatted timestamp.
                $saves[ $parent_id ][ $key ][ 'updated_formatted' ] = $value;
            }

            $saves[ $parent_id ][ $key ] = $value;
        }

        return $saves;
    }

    /**
     * @param array $data Save object meta data [ form_id => 25, user_id => 1, fields => [] ]
     * @return int $parent_id The ID of the new Save object.
     */
    public function insert( $data = array() )
    {
        $data[ 'updated' ] = time();
        $this->db->insert( $this->table, array( 'type' => 'save' ),  array( '%s' ) );
        $parent_id = $this->db->insert_id;

        $sql = "
            INSERT INTO `{$this->metatable}` ( `parent_id`, `key`, `value` )
            VALUES 
        ";

        $args = array();
        foreach( $data as $key => $value ){
            $sql .= "( %d, %s, %s ),";
            array_push( $args, $parent_id, $key, $value );
        }
        $sql = rtrim( $sql, ',' );

        $this->db->query( $this->db->prepare( $sql, $args ) );

        return $parent_id;
    }

    /**
     * @param int $save_id
     * @param array $data Update save object meta data [ form_id => 25, user_id => 1, fields => [] ]
     * @return false|int $wpdb->query()
     */
    public function update_by_id( $save_id, $data = array() )
    {
        $data[ 'updated' ] = time();
        $sql = "UPDATE `{$this->metatable}` SET `value` = CASE `key` ";

        $args = array();
        foreach( $data as $key => $value ){
            $sql .= "WHEN '%s' THEN '%s' ";
            array_push( $args, $key, $value );
        }
        $sql .= " ELSE `value` END WHERE `parent_id` = $save_id";

        $query = $this->db->prepare( $sql, $args );
        return $this->db->query( $query );
    }

    public function delete_by_id( $save_id )
    {
        return $this->db->query( $this->db->prepare("
            DELETE object, objectmeta FROM `{$this->db->prefix}nf3_objects` AS object
            JOIN `{$this->db->prefix}nf3_object_meta` as objectmeta ON objectmeta.parent_id = object.id
            WHERE object.id = %d
        ", $save_id ) );
    }

    public function convert_by_id( $save_id, $key = null, $form_id )
    {
        $result = $this->db->get_var( $this->db->prepare("
            SELECT `value`
            FROM {$this->metatable}
            WHERE `parent_id` = %d AND `key` = 'fields'
        ", $save_id ));

        if( ! $result ) return false;

        try {
            $fields = json_decode( $result, true );
        } catch( Exception $e ){
            return;
        }

        $sub = Ninja_Forms()->form( $form_id )->sub()->get();
        foreach( $fields as $field ){
            $sub->update_field_value( $field[ 'id' ], $field[ 'value' ] );
        }
        $sub->save();

        $this->delete_by_id( $save_id );

        return $sub->get_id();
    }
}
