<?php
add_filter( 'wpas_plugin_settings', 'wpas_pf_custom_css_settings', 5, 1 );
/**
 * Add misc useful settings
 *
 * @param  array $def Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_pf_custom_css_settings( $def ) {

	$settings = array(
		'pfCustomCSS01' => array(
			'name'    => __( 'Custom CSS', 'wpas_productivity' ),
			'options' => array(
			
				array(
					'name'    => __( 'Use Custom CSS', 'wpas_productivity' ),
					'id'      => 'pf_use_custom_css',
					'type'    => 'checkbox',
					'desc'    => __( 'Check this box to enable injecting custom CSS into certain pages as described below.  Custom CSS MUST be well formed!', 'wpas_productivity' ),
					'default' => false
				),			
				array(
					'name'    => __( 'Custom CSS For The SUBMIT-TICKET Page', 'wpas_productivity' ),
					'id'      => 'pf_custom_css_submit_ticket',
					'type'    => 'textarea',
					'desc'    => __( 'Enter the custom CSS to be used on the ticket page', 'wpas_productivity' ),
					'is_code' => true,
				),
				array(
					'name'    => __( 'Custom CSS For Front-End Ticket List', 'wpas_productivity' ),
					'id'      => 'pf_custom_css_front_end_ticket_list',
					'type'    => 'textarea',
					'desc'    => __( 'Enter the custom CSS to be used on the front-end ticket list', 'wpas_productivity' ),
					'is_code' => true,
				),
				array(
					'name'    => __( 'Custom CSS For Back-end Ticket List', 'wpas_productivity' ),
					'id'      => 'pf_custom_css_back_end_ticket_list',
					'type'    => 'textarea',
					'desc'    => __( 'Enter the custom CSS to be used on the main ticket list in admin', 'wpas_productivity' ),
					'is_code' => true,
				),
				array(
					'name'    => __( 'Custom CSS For Back-end Ticket Details Screen', 'wpas_productivity' ),
					'id'      => 'pf_custom_css_back_end_ticket_detail',
					'type'    => 'textarea',
					'desc'    => __( 'Enter the custom CSS to be used on the ticket detail screen in admin', 'wpas_productivity' ),
					'is_code' => true,
				),
				array(
					'name'    => __( 'Custom CSS For Registration Screen', 'wpas_productivity' ),
					'id'      => 'pf_custom_css_registration_screen',
					'type'    => 'textarea',
					'desc'    => __( 'Enter the custom CSS to be used on the standard registration screen provided by Awesome Support', 'wpas_productivity' ),
					'is_code' => true,
				),
				
				array(
					'name' => __( 'CSS Examples', 'wpas_productivity' ),
					'desc' => __( 'Here are some examples of valid and useful CSS', 'wpas_productivity' ),
					'type' => 'heading',
				),
				array(
					'name' => __( 'SUBMIT-TICKET Page Example CSS #1: Hide The Attachments Area', 'wpas_productivity' ),
					'type' => 'note',
					'desc' => ' #wpas_files_wrapper { ' . '<br />' . 'display:none;' . '<br />' . '}' ,
				),
				array(
					'name' => __( 'SUBMIT-TICKET Page Example CSS #2: Hide The MY TICKETS button', 'wpas_productivity' ),
					'type' => 'note',
					'desc' => ' .wpas-link-ticketlist { ' . '<br />' . 'display:none;' . '<br />' . '}' ,
				),				
				array(
					'name' => __( 'Front-end Ticket List Example CSS: Hide The Logout Button', 'wpas_productivity' ),
					'type' => 'note',
					'desc' => ' .wpas-link-logout { ' . '<br />' . 'display:none;' . '<br />' . '}' ,
				),
				array(
					'name' => __( 'Back-end Ticket List Example CSS: Turn the PRIORITY Column Blue', 'wpas_productivity' ),
					'type' => 'note',
					'desc' => ' td.ticket_priority.column-ticket_priority { ' . '<br />' . 'background-color: blue ;' . '<br />' . '}' ,
				),
				array(
					'name' => __( 'Back-end Ticket Detail Example CSS: Hide The Ticket Statistics Metabox', 'wpas_productivity' ),
					'type' => 'note',
					'desc' => ' #wpas-mb-ticket-statistics { ' . '<br />' . 'display:none;' . '<br />' . '}' ,
				),
				array(
					'name' => __( 'Registration/Login Form Example CSS: Hide The remember-me Checkbox', 'wpas_productivity' ),
					'type' => 'note',
					'desc' => ' #wpas_rememberme_wrapper { ' . '<br />' . 'display:none;' . '<br />' . '}' ,
				),								
				
			)
		)
	);

	return array_merge( $def, $settings );

}

