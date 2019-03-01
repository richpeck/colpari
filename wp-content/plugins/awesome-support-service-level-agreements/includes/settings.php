<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_action( 'wp_loaded', 'wpas_sla_create_options' );
/**
 * Add settings tabs and options
 */
function wpas_sla_create_options() {
	
	$useEmbeddedFramework = true;
		$activePlugins = get_option('active_plugins');
		if ( is_array( $activePlugins ) ) {
		    foreach ( $activePlugins as $plugin ) {
		        if ( is_string( $plugin ) ) {
		            if ( stripos( $plugin, '/titan-framework.php' ) !== false ) {
		                $useEmbeddedFramework = false;
		                break;
		            }
		        }
		    }
		}
	
	// Use the embedded Titan Framework
	if ( $useEmbeddedFramework && ! class_exists( 'TitanFramework' ) ) {
	    require_once( WPAS_PATH . 'vendor/gambitph/titan-framework/titan-framework.php' );
	}
	
	require_once( WPAS_PATH . 'vendor/gambitph/titan-framework/titan-framework.php' );
		
	$titan = TitanFramework::getInstance( 'wpassla' );
	
	$settings = $titan->createContainer( array(
		'type'       => 'admin-page',
		'name'       => __( 'Settings', 'awesome-support' ),
		'title'      => __( 'SLA Settings', 'awesome-support' ),
		'id'         => 'wpas-sla-settings',
		'parent'     => 'edit.php?post_type=wpas_sla',
		'capability' => 'ticket_sla_admin'
		));
	
	$general_settings = wpas_sla_general_settings();
	
	$workday_options = wpas_sla_workdays_settings();
	
	$cron_options =  wpas_sla_cron_settings();
	
	$options = array_merge( $general_settings,  $workday_options, $cron_options );

	/* Parse options */
	foreach ( $options as $tab => $content ) {

		/* Add a new tab */
		$tab = $settings->createTab( array(
			'name'  => $content['name'],
			'title' => isset( $content['title'] ) ? $content['title'] : $content['name'],
			'id'    => $tab
			)
		);

		/* Add all options to current tab */
		foreach( $content['options'] as $option ) {

			$tab->createOption( $option );

			if ( isset( $option['type'] ) && 'heading' === $option['type'] && isset( $option['options'] ) && is_array( $option['options'] ) ) {

				foreach ( $option['options'] as $opt ) {
					$tab->createOption( $opt );
				}

			}


		}

		$tab->createOption( array( 'type' => 'save', ) );
		
	}
	
}

/**
 * 
 * Add general settings
 * 
 * @return array
 */
function wpas_sla_general_settings() {
	
	$settings = array(
	'general' => array(
			'name'    => __( 'General', 'wpas_sla' ),
			'options' =>  array(
				array(
                        'name'    => __( 'Use SELECT2 Search For SLA Policy Drop-downs', 'wpas_sla' ),
                        'id'      => "sla_id_dropdown_select2",
                        'type'    => 'checkbox',
                        'default' => false,
                        'desc'    => __( 'Use a select2 search box for the SLA dropdowns - useful if you have a large number of SLAs.', 'wpas_sla' )
                ),
				
				array(
                        'name'    => __( 'Tickets', 'wpas_sla' ),
                        'type'    => 'heading',
                ),				
				array(
                        'name'    => __( 'Show SLA Policy in the Ticket List', 'wpas_sla' ),
                        'id'      => "sla_ticket_listing_column_enabled",
                        'type'    => 'checkbox',
                        'default' => false,
                        'desc'    => __( 'This will add a new column that displays the SLA name in the ticket listing page', 'wpas_sla' )
                ),
				
				array(
                        'name'    => __( 'SLA Categories', 'wpas_sla' ),
                        'type'    => 'heading',
                ),				
				array(
                        'name'    => __( 'Enable SLA Categories', 'wpas_sla' ),
                        'id'      => "sla_category_enabled",
                        'type'    => 'checkbox',
                        'default' => true,
                        'desc'    => __( 'Enable a SLA category taxonomy that can be used to determine which SLA Policy is applied to a ticket', 'wpas_sla' )
                ),
				array(
                        'name'    => __( 'Allow Users To Select SLA Categories', 'wpas_sla' ),
                        'id'      => "sla_category_enabled_fe",
                        'type'    => 'checkbox',
                        'default' => true,
                        'desc'    => __( 'Allow end users to submit tickets with a category that can determine which SLA Policy is used for the ticket', 'wpas_sla' )
                )
				
			)
		)
	);
	
	return $settings;
}

/**
 * 
 * Add workday settings
 * 
 * @return array
 */
function wpas_sla_workdays_settings() {
	
	$options = array();
	$days = wpas_week_days();
	
	$work_times = wpas_sla_default_work_times();
		
    foreach( $days as $day_id => $day_name ) {
		
		$active = wpas_sla_get_day_default_setting( $day_id, 'active' );
		$start_time = wpas_sla_get_day_default_setting( $day_id, 'start_time' );
		$end_time = wpas_sla_get_day_default_setting( $day_id, 'end_time' );
		
		
		$id_prefix = "workday_{$day_id}_";
		
		$hide_no_cutoff_time_option = !wpas_sla_get_option( "{$id_prefix}__active_full_day" ) ? true : false;

        $options[] = array(
                'name'    => __( $day_name, 'wpas_sla' ),
                'type'    => 'heading',
                'id'      => $day_name . rand(1, 99999)
        );
		
		$options[] = array(
                'name'    => __( 'Is Work Day', 'wpas_sla' ),
                'id'      => "{$id_prefix}__active",
                'type'    => 'enable',
                'default' => $active,
				'enabled' => __( 'Workday', 'wpas_sla' ),
				'disabled' => __( 'Holiday', 'wpas_sla' )
        );
				
				
		$options[] = array(
                'name'    => __( 'All day is work day', 'wpas_sla' ),
                'id'      => "{$id_prefix}__active_full_day",
                'type'    => 'checkbox',
                'default' => '',
				'desc' => __( 'Check this if the work day is all day - 24 hours; if checked we don\'t use the start and end times.', 'wpas_sla' )
        );
				
		$options[] = array(
                'name'    => __( 'No cutoff time', 'wpas_sla' ),
                'id'      => "{$id_prefix}__active_no_cutoff_time",
                'type'    => 'checkbox',
                'default' => '',
				'hidden' => $hide_no_cutoff_time_option,
				'desc' => __( 'Check this if tickets can be accepted all day.', 'wpas_sla' )
        );

        $options[] = array(
                'name'    => __( 'Start Time', 'wpas_sla' ),
                'id'      => "{$id_prefix}__start_time",
                'type'    => 'date',
                'default' => $start_time,
				'date' => false,
				'time' => true
        );

        $options[] = array(
                'name'    => __( 'End Time', 'wpas_sla' ),
                'id'      => "{$id_prefix}__end_time",
                'type'    => 'date',
				'date' => false,
				'time' => true,
                'default' => $end_time
        );

        $options[] = array(
                'name'    => __( 'Ticket Cutoff Time', 'wpas_sla' ),
                'id'      => "{$id_prefix}__cutoff_time",
                'type'    => 'date',
				'date' => false,
				'time' => true
        );
				
    }
	
	$settings = array(
		'workdays' => array(
			'name'    => __( 'WorkDays', 'wpas_sla' ),
			'options' =>  $options
		    )
	);
	
	return $settings;
}


/**
 * 
 * Add workday settings
 * 
 * @param array $def
 * 
 * @return array
 */
function wpas_sla_cron_settings() {
	
	$options = array();
	$days = wpas_week_days();
	
	$cron_recurrences = wpas_sla_cron_recurrences();
	$cron_recurrence_options = array();
	
	foreach( $cron_recurrences as $rec_key => $rec ) {
		$cron_recurrence_options[ $rec_key ] = $rec['display'];
	}
	
	$options[] = array(
                    'id'      => 'cron_heading',
                    'name' => __( 'Cron Setting', 'wpas_sla' ),
                    'type'    => 'heading'
                );
    
    $options[] = array(
                    'id'      => 'cron_recurrence',
                    'name' => __( 'Cron Recurrence', 'wpas_sla' ),
                    'type'    => 'select',
					'default' => 'hourly',
                    'options' => $cron_recurrence_options
                );
    
	$options[] = array(
					'name'    => __( 'Enable Log', 'wpas_sla' ),
					'id'      => 'enable_cron_log',
					'type'    => 'checkbox',
					'default' => false ,
					'desc'    => __( 'Enable cron logs. ', 'wpas_sla' ) . wpas_sla_log_button()
				);
	
	$settings = array(
		'cron' => array(
			'name'    => __( 'Cron', 'wpas_sla' ),
			'options' =>  $options
		    )
	);
	
	return $settings;
}


/**
 * Generate view log button for settings page
 * 
 * @return string
 */
function wpas_sla_log_button() {
    
    $ajax_url = add_query_arg( 
            array( 
                'action' => 'wpas_sla_log_win_content', 
                'height' => '500', 
                'width' => '700' 
                ), 'admin-ajax.php'); 
    
    $content = '<a title="Log" href="'.$ajax_url.'" class="thickbox">View Log</a>';
    
    return $content;
}