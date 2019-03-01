<?php
namespace AS_Custom_Status\Admin;

/**
 * Implements the custom status settings panel and settings.
 *
 * @since 1.1.0
 */
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
		add_filter( 'wpas_plugin_settings', array( $this, 'general_settings' ), 100 );
	}

	/**
	 * Registers 'General' settings for the Custom Status panel.
	 *
	 * @access public
	 * @since  1.1.0
	 *
	 * @param array $settings Array of existing settings.
	 * @return array<string,array> Filtered Awesome Support settings.
	 */
	public function general_settings( $settings ) {
		$settings['custom-status'] = array(
			'name'    => __( 'Custom Status', 'wpass_statuses' ),
			'options' => array(
			
				array(
					'name'    => __( 'Custom Status General Settings', 'wpass_statuses' ),
					'id'      => 'ascs_general',
					'type'    => 'heading',
					'desc'	  => __( 'You can define your custom statuses under TICKETS->SETTINGS->STATUS AND LABELS. <br /> This settings screen allow you to control how the statuses will appear in your lists.', 'wpass_statuses' ),
				),			
				array(
					'name'    => __( 'Sort By', 'wpass_statuses' ),
					'desc'    => __( 'Select the attribute by which the status list will be sorted', 'wpass_statuses' ),
					'id'      => 'ascs_sort_by',
					'type'    => 'radio',
					'options' => array( 'title' => __('Title','wpass_statuses'), 'ID' => __('ID','wpass_statuses') ),
					'default' => 'ID',
				),
				array(
					'name'    => __( 'Sort Direction', 'wpass_statuses' ),
					'desc'    => __( 'Sort the status list in ascending or descending order', 'wpass_statuses' ),
					'id'      => 'ascs_sort_direction',
					'type'    => 'radio',
					'options' => array( 'ASC' => __('Ascending','wpass_statuses'), 'DESC' => __('Descending','wpass_statuses') ),
					'default' => 'ASC',
				),				
			),
		);

		return $settings;
	}

}
