<?php



class WPAC_WarningMessage extends WPAC_Object {
    
    private static $table = 'ac_warning_messages';
    
    public $id,
            $status,
            $age,
            $message,
            $subject,
            $close;
    
    
    
    /**
     * 
     * @global object $wpdb
     * @param type $output
     * @return array
     */
    public static function getAll($output = OBJECT) {
        global $wpdb;
        
        
        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . self::$table, ARRAY_A);
        
        
        if($output == ARRAY_A) {
            $messages = $results;
        }
        
        else {
            $messages = array();
            foreach($results as $r) {
                $messages[] = new WPAC_WarningMessage($r);
            }
        } 
        
        return $messages;
    }
    
    /**
     * 
     * @global object $wpdb
     * @return array
     */
    public static function getAllIds() {
        
        $all = self::getAll();
        
        $message_ids = array();
        foreach($all as $m) {
            $message_ids[] = $m->id;
        }
        
        return $message_ids;
    }
    
    
    
    
    /**
     * 
     * @param array $messages
     */
    public static function save_warnings($messages) {
        
        // current messages in database
        $current = self::getAllIds();
        
        $new_data_ids = array();
        
        foreach ($messages as $m) {
            self::update($m);
            $new_data_ids[] = $m['id'];
        }
        
        $deleted_ids = array_diff($current, $new_data_ids);
        
        foreach($deleted_ids as $d_id) {
            self::delete($d_id);
        }
    }
    
    /**
     * 
     * @global object $wpdb
     * @param array $data
     * @return int|boolean
     */
    public static function update($data) {
        global $wpdb;
        
        $id = $data['id'];
        $status = $data['status'];
        $age = $data['age'];
        $message = $data['msg'];
        $subject = $data['subject'];
        $close = isset($data['close']) && $data['close'] ? 1 : 0;
        
        
        
        
        return
        $wpdb->update(
                $wpdb->prefix . self::$table, 
                array('status' => $status,  'age' => $age, 'subject' => $subject ,'message' => $message, 'close' => $close), 
                array('id' => $id),
                array('%s', '%d', '%s' , '%s', '%d'),
                array('%d')
                );
            
        
    }
    
    
    /**
     * 
     * @global type $wpdb
     * @param int $id
     * @return int|boolean
     */
    public static function delete($id) {
        global $wpdb;
        return $wpdb->delete($wpdb->prefix . self::$table, array('id' => $id), array('%d'));
        
    }
    
    /**
     * 
     * @global type $wpdb
     * @param string $status
     * @param int $age
     * @param string $subject
     * @param string $message
     * @param int $close
     * @return int
     */
    public static function add($status, $age, $subject , $message, $close) {
        global $wpdb;
        
        if($wpdb->insert($wpdb->prefix . self::$table, 
                array(
                    'status' => $status,  
                    'age' => $age, 
                    'subject' => $subject ,
                    'message' => $message, 
                    'close' => $close
                ), array('%s', '%d', '%s' ,'%s', '%d'))) {
            return $wpdb->insert_id;
        }
    }
    
}