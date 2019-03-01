<?php

namespace AS_Public_Tickets\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Settings {

	/**
	 * Starts up the Settings instance.
	 *
	 * @access public
	 * @since  1.0
	 */

	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes the Guest Tickets settings panel.
	 *
	 * @access public
	 * @since  1.0
	 */

	public function init() {
		add_filter( 'wpas_plugin_settings', array( $this, 'public_ticket_addon_setting_callback' ), 98, 1 );
	}

	function public_ticket_addon_setting_callback($settings) {
	$settings['public-tickets'] = array(
			'name'    => __( 'Public Tickets', 'as-public-tickets' ),
			'options' => array(
				array(
					'name'    => __( 'Make all NEW tickets Public By Default', 'as-public-tickets' ),
					'id'      => 'pbtk_public',
					'type'    => 'checkbox',
					'desc'    => __( 'Existing tickets will not be changed when this option is changed - it only applies to NEW tickets.', 'as-public-tickets' )
				),

				array(
					'name'    => __( 'Show Public/Private Flag To End Users', 'as-public-tickets' ),
					'id'      => 'pbtk_shw_flag',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Turning this option on allows end users to determine which tickets or replies are private.', 'as-public-tickets' )
					
				),

				array(
					'name'    => __( 'Show Public/Private Flag In Ticket List', 'as-public-tickets' ),
					'id'      => 'pbtk_shw_flag_ticketlist',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Show the public/private flag in the main ticket list.', 'as-public-tickets' )
				),				
				
				array(
					'name'    => __( 'Tickets Per Page', 'as-public-tickets' ),
					'id'      => 'pbtk_tickets_per_page',
					'type'    => 'text',
					'default' => 12,
					'desc'    => __( 'How many Tickets should be displayed per page on the public tickets screen?', 'as-public-tickets' )
				),
				
				array(
					'name'    => __( 'Customer Name Show/hide', 'as-public-tickets' ),
					'id'      => 'pbtk_customer_name_show',
					'type'    => 'radio',
					'desc'    => __( 'Allow admin to Hide or Annoymize customer names from ticket page', 'as-public-tickets' ),
					'default' => 'noaction',
					'options' => array(
						'noaction'  => __( 'No Action', 'as-public-tickets' ),
						'hide'      => __( 'Hide customer name', 'as-public-tickets' ),
						'annoymize' => __( 'Annoymize Customer Name With Initials', 'as-public-tickets' ),
					)
				),
				
				array(
					'name'    => __( 'Agent Name Show/hide', 'as-public-tickets' ),
					'id'      => 'pbtk_agent_name_show',
					'type'    => 'radio',
					'desc'    => __( 'Allow admin to Hide or Annoymize Agent Name from ticket page', 'as-public-tickets' ),
					'default' => 'noaction',
					'options' => array(
						'noaction'  => __( 'No Action', 'as-public-tickets' ),
						'hide'      => __( 'Hide Agent name', 'as-public-tickets' ),
						'annoymize' => __( 'Annoymize Agent Name With Initials', 'as-public-tickets' ),
					)
				),
				
				array(
					'name'    => __( 'Shortcodes', 'as-public-tickets' ),
					'type'    => 'heading',
				),

				array(
					'type'    => 'note',
					'desc' 	  => __( 'Use the following shortcode to list all the public tickets on a page.', 'as-public-tickets' )
				),

				array(
					'type' 	  => 'note',
					'desc'	  => __( '<span class="bg_code">[pbtk_tickets]</span><p>Admins can use the follow parameters to filter the list of tickets based on type, tag or product.</p><ol>
		<li>To list all the tickets (public, private, open and closed) - <span class="bg_code">[pbtk_tickets]</span></li>
		<li>To list all the public or private tickets use the TYPE parameter. - <span class="bg_code">[pbtk_tickets type="private"]</span> or <span class="bg_code">[pbtk_tickets type="public"]</span> </li>
		<li>To list all the tickets with a particular product - <span class="bg_code">[pbtk_tickets product="product-slug-here"]</span> </li>
		<li>To list all the tickets with a particular tag - <span class="bg_code">[pbtk_tickets tag="tag here"]</span> </li>
		<li>To list tickets in different views - <span class="bg_code">[pbtk_tickets display="grid"]</span>. Valid options for this parameter are : grid , list , accordion. Default is list. </li>
		<li>To list open or closed tickets - <span class="bg_code">[pbtk_tickets status="closed"]</span> The default will show only open tickets unless this parameter is included. </li>
		<li>To show the search field - <span class="bg_code">[pbtk_tickets search="yes"]</span>. </li>
		<li>To show the display options (grid, list, accordion) - <span class="bg_code">[pbtk_tickets show_filter="no"]</span>. Default is yes </li>
		</ol>', 'as-public-tickets' )

				),
			),
		);
	return $settings;
	}
}