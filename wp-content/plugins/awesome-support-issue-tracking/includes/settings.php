<?php

/**
 * All email notices
 * 
 * @return array
 */
function wpas_it_notification_actions() {
	return array(
		'issue_private_comment_added'				=> __( 'New Private Comment (sent only to the primary agent on the issue)', 'wpas_it' ),
		'issue_semi_private_comment_added'			=> __( 'New Semi-private Comment (sent to primary and associated agents on the issue)', 'wpas_it' ),
		'issue_regular_comment_added'				=> __( 'New Regular Comment (sent to primary and associated agents as well as all other interested parties on the issue)', 'wpas_it' ),
		'issue_closed'								=> __( 'Issue Closed (sent to primary and associated agents on the issue)', 'wpas_it' ),
		'ticket_issue_private_comment_added'		=> __( 'New Private Comment - For tickets attached to issue (sent to primary agent on associated tickets)', 'wpas_it' ),
		'ticket_issue_semi_private_comment_added'	=> __( 'New Semi-private Comment - For tickets attached to issue (sent to all agents on associated tickets)', 'wpas_it' ),
		'ticket_issue_regular_comment_added'		=> __( 'New Regular Comment - For tickets attached to issue (sent to agents, customer and other interested parties on tickets)', 'wpas_it' ),
		'ticket_issue_closed'						=> __( 'Issue Closed - For tickets attached to issue (sent to agents, customer and other interested parties on tickets)', 'wpas_it' ),
	);
}

/**
 * return all settings
 * 
 * @return array
 */
function wpas_it_get_settings() {
	return apply_filters( 'wpas_it_plugin_settings', array() );
}


add_action('wp_loaded', 'wpas_it_create_options', 200, 0 );

/**
 * prepare settings page
 */
function wpas_it_create_options() {
	
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
	$titan = TitanFramework::getInstance( 'wpasit' );
	
	
	$settings = $titan->createContainer( array(
						'type'       => 'admin-page',
						'name'       => __( 'Settings', 'wpas_it' ),
						'title'      => __( 'IT Settings', 'wpas_it' ),
						'id'         => 'wpas-it-settings',
						'parent'     => 'edit.php?post_type=wpas_issue_tracking',
						'capability' => 'administer_awesome_support',
						'position' => 20
				)
		);
	
	
	$options = wpas_it_get_settings();
	
	
	

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

add_filter( 'wpas_it_plugin_settings', 'wpas_it_settings', 100, 1 );

/**
 * 
 * Add settings tab
 * 
 * @param array $def
 * 
 * @return array
 */
function wpas_it_settings( $def ) {
	
	// adding Email options dynamically
        $email_sections = array();
        $actions_list = wpas_it_notification_actions();

        foreach( $actions_list as $action_key => $action_name ) {

                $heading = "Email : {$action_name}";

                $email_sections[] = array(
                        'name'    => __( $heading, 'wpas_it' ),
                        'type'    => 'heading',
                        'id'      => $heading . rand(1, 99999)
                );

                $email_sections[] = array(
                        'name'    => __( 'Subject', 'wpas_it' ),
                        'id'      => "{$action_key}__subject",
                        'type'    => 'text',
                        'default' => ''
                );

                $email_sections[] = array(
                        'name'    => __( 'Content', 'wpas_it' ),
                        'id'      => "{$action_key}__content",
                        'type'    => 'editor',
                        'default' => '',
                        'desc'    => __( 'Email Content', 'wpas_it' )
                );
						
        }
	
	$settings = array(
		
		'general' => array(
			'name'    => __( 'General', 'wpas_it' ),
			'options' =>  array(
				array(
                        'name'    => __( 'Use SELECT2 For Issue Drop-downs', 'wpas_it' ),
                        'id'      => "issue_dropdown_select2",
                        'type'    => 'checkbox',
                        'default' => false,
                        'desc'    => __( 'On ticket screen turn the issues dropdown into select2 box.', 'wpas_it' )
                )
			)
		),
		'email_alerts' => array(
			'name'    => __( 'Email Alerts', 'wpas_it' ),
			'options' =>  $email_sections
		)
	);
	
	return array_merge( $def, $settings );
}