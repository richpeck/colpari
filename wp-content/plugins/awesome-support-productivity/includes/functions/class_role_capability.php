<?php

/**
 * Handle Role Capabilities
 */
class WPAS_PF_Role_Capability {
	
	protected static $instance = null;
	
	public function __construct() {
		
		
		add_action( 'wp_ajax_wpas_pf_settings_caps',			array( $this, 'get_capabilities'),	11, 0 );
		add_action( 'wp_ajax_wpas_pf_settings_caps_update',		array( $this, 'update_capabilities'),	11, 0 );
		add_action( 'wp_ajax_wpas_pf_settings_caps_update_preset',	array( $this, 'update_capabilities'),	11, 0 );
		add_filter( 'wpas_system_tabls',				array( $this, 'add_system_tab'),	11, 1 );
		
	}
	
	
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	
	/**
	 * Check if have access to capabilities feature
	 * 
	 * @return boolean
	 */
	public function have_access() {
		
		// Do not allow access if AS is running as an SAAS
		// Note that this will remove access to admin as well.  
		// We ended up having to it this way because for some reason 
		// current_user_can( 'manage_options' ) or has_cap( 'manage_options' ) 
		// always returns true for all users.  
		if ( defined( 'WPAS_SAAS' ) && true === WPAS_SAAS ) {
			return false ;
		}		
		
		$user = wp_get_current_user();
		
		if( $user->has_cap( 'administer_awesome_support' ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Add capabilities system tab
	 * 
	 * @param array $tabs
	 * 
	 * @return array
	 */
	public function add_system_tab( $tabs ) {
		
		if( $this->have_access() ) {
		
			$tabs['caps'] = array(
			    'name' => __( 'Capabilities', 'wpas_productivity' ),
			    'view_path' => WPAS_PF_PATH . 'includes/templates/system-capabilities.php'
			);
		}
		
		return $tabs;
	}
	
	/**
	 * Return all role capabilities without modifications checking
	 * 
	 * @param string $role_name
	 * 
	 * @return array
	 */
	public static function role_capabilities( $role_name ) {
		$role = get_role( $role_name );

		if( isset( $role->capabilities ) && !empty( $role->capabilities ) ) {
			return $role->capabilities;
		}
	}
	
	
	/**
	 * Return only granted role capabilities
	 * 
	 * @param string $role_name
	 * 
	 * @return array
	 */
	public function get_role_granted_capabilities( $role_name ) {
		
		$role = get_role( $role_name );
		$capabilities = array();
		
		if( $role && isset( $role->capabilities ) ) {
			
			foreach( $role->capabilities as $cap => $granted ) {
				
				if( $role->has_cap( $cap ) ) {
					$capabilities[] = $cap;
				}
			}
		}
		
		return $capabilities;
	}
	
	
	/**
	 * Response capabilities ajax request
	 */
	public function get_capabilities() {
		
		$role = filter_input( INPUT_POST, 'role' );
		
		$capabilities = $this->role_capabilities( $role );
		$capabilities = array_keys( $capabilities );
				
		wp_send_json_success( array( 'caps' => $capabilities ) );
		die();
	}
	
	/**
	 * Save role capabilities modification
	 */
	public function update_capabilities() {
		
		
		
		if( !$this->have_access() ) {
			wp_send_json_error( array( 'msg' => __( 'Sorry, you are not allowed to update capabilities.', 'wpas_productivity' ) ) );
		} else {
			
			$role_name = filter_input( INPUT_POST, 'role' );
			$action = filter_input( INPUT_POST, 'action' );
			
			if( 'wpas_pf_settings_caps_update_preset' === $action ) {
				
				$preset = filter_input( INPUT_POST, 'preset' );
				$caps = wpas_pf_preset_capabilities( $preset );
				
				if( null === $caps ) {
					wp_send_json_error( array( 'msg' => __( 'Sorry, Some thing went wront, try again later.', 'wpas_productivity' ) ) );
					die();
				}
				
			} else {
				$caps = filter_input( INPUT_POST, 'cap', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY );
			}

			$capabilities = $this->role_capabilities( $role_name );
			$capabilities = array_keys( $capabilities );
			
			
			$added = array_diff( $caps, $capabilities );
			$removed = array_diff( $capabilities, $caps );
			
			
			$role = get_role( $role_name );
                        
                        if( !empty( $added ) ) {
				foreach( $added as $added_cap ) {
					$role->add_cap( $added_cap );
				}
                        }
			
			
			if( !empty( $removed ) ) {
				foreach( $removed as $removed_cap ) {
					$role->remove_cap( $removed_cap );
				}
                        }
			
			
			wp_send_json_success( array( 'msg' => __( 'Capabilities successfully saved.', 'wpas_productivity' ), 'caps' => $caps ) );
		}
		
		
		die();
		
	}
	
	/**
	 * Return list of all roles capabilities
	 * 
	 * @return array
	 */
	public static function all_role_capabilities() {
		$roles = get_editable_roles();

		$all_caps = array();

		foreach( $roles as $role_name => $role ) {
			$caps = self::role_capabilities( $role_name );
			$all_caps = array_merge($all_caps, $caps);
		}
		
		$capabilities = array_keys( $all_caps );
		sort( $capabilities );
		
		return $capabilities;
	}
	
	
}