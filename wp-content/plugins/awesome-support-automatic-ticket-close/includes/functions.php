<?php

/**
 * Log directory
 * @return string
 */
function wpac_log_file_dir() {
    $dir = WPAS_PATH . 'logs' . DIRECTORY_SEPARATOR;
    return $dir;
}

/**
 * Log file path
 * @return string
 */
function wpac_log_file_path() {
    $path = WPAS_PATH . 'logs' . DIRECTORY_SEPARATOR . 'log-autoclose.txt';
    return $path;
}

/**
 * return log file content
 * @return string
 */
function wpac_get_log_content() {
    
    $content = "";
    
    $file_path = wpac_log_file_path();
    if(is_file($file_path)) {
        $content = file_get_contents($file_path);
    }
    
    
    return $content;
}

/**
 * check if debug mode is enabled
 * @return boolean
 */
function wpac_is_debug_mode_active() {
    return wpas_get_option('autoclose_debug_mode_active');
}



/**
* 
* @param int $ticket_id
* @param boolean $update
* @return string
*/
function wpac_generate_cron_process_id($ticket_id, $update = true) {
        
    $id = wp_generate_password(6, false, false) . ".{$ticket_id}";
    
    if($update) {
        update_post_meta($ticket_id, 'autoclose_process_id', $id);
    }
    
    return $id;
}
    
/**
 * clear processed flag
 * @param int $post_id
 */
function wpac_clear_processed_flag($post_id) {
    if($post_id) {
        update_post_meta($post_id, 'last_autoclose_check_date', '');
        wpac_generate_cron_process_id($post_id);
    }
    
}