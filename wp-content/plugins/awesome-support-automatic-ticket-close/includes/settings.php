<?php

add_filter( 'wpas_plugin_settings', 'wp_asac_settings_warningmessages', 10, 1 );

/**
 * Add settings for warning messages.
 * 
 * @param  (array) $def Array of existing settings
 * @return (array)      Updated settings
 */
function wp_asac_settings_warningmessages( $def ) {

	
    $options = array();
    
    
    $cron_recurrences = apply_filters('wp_asac_cron_recurrences_options', array());
    
    $warning_messages = WPAC_WarningMessage::getAll();
    foreach($warning_messages as $wm) {
        
        $options[] = array(
            'id'      => 'autoclose_wanring_msg_field_group_' . $wm->id,
            'type'    => 'warningmessage',
            'wm_data' => $wm
        );
    }
    
    $options[] = array(
                    'id'      => 'autoclose_cron_heading',
                    'name' => __('Cron Setting', WPASS_AUTOCLOSE_TEXT_DOMAIN),
                    'type'    => 'heading'
                );
    
    $options[] = array(
                    'id'      => 'autoclose_cron_recurrence',
                    'name' => __('Cron Recurrence', WPASS_AUTOCLOSE_TEXT_DOMAIN),
                    'type'    => 'select',
                    'options' => $cron_recurrences
                );
    
    $options[] = array(
                    'id'      => 'autoclose_cron_limit',
                    'name' => __('Cron Limit', WPASS_AUTOCLOSE_TEXT_DOMAIN),
                    'type'    => 'text',
                    'default' => '0'
                );
    
    $options[] = array(
                    'id'      => 'autoclose_message_process_limit',
                    'name' => __( 'Message processing limit', WPASS_AUTOCLOSE_TEXT_DOMAIN),
                    'type'    => 'text',
					'desc'	=> __( 'How many messages can be processed per ticket per cron cycle. 0 = all' ),
                    'default' => '1'
                );
    
    $options[] = array(
                    'id'      => 'autoclose_cron_log',
                    'name' => __('Log', WPASS_AUTOCLOSE_TEXT_DOMAIN),
                    'type'    => 'custom',
                    'custom' => wp_asac_settings_log_field_content()
                );
    
    
    $options[] = array(
                    'id'      => 'autoclose_debug_mode_active',
                    'name' => __('Enable Debug Mode', WPASS_AUTOCLOSE_TEXT_DOMAIN),
                    'type'    => 'checkbox',
                    'default' => wpac_is_debug_mode_active()
                );
    
    $options[] = array(
                    'id'      => 'autoclose_clear_in_process_values',
                    'name' => __( 'Clear In-Process Values', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
                    'type'    => 'custom',
					'custom' => sprintf( '<a title="Clear" href="#" class="btn_ac_clear_inprocess_values">%s</a>', __( 'Clear', WPASS_AUTOCLOSE_TEXT_DOMAIN ) )
                );
    
    $options[] = array( 'type' => 'save', 'use_reset'=> false, 'ac_save_btn' => true);
            
            
    
    

    $settings = array(
	'autoclose' => array(
            'name'    => __( 'Auto Close', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
            'options' => $options
	)
    );

    return array_merge( $def, $settings );

}




/**
 * log field
 * @return string
 */
function wp_asac_settings_log_field_content() {
    
    $ajax_url = add_query_arg( 
            array( 
                'action' => 'asac_log_win_content', 
                'height' => '500', 
                '&width' => '700' 
                ), 'admin-ajax.php'); 
    
    $content = '<a title="Log" href="'.$ajax_url.'" class="thickbox">View Log</a>';
    
    return $content;
}