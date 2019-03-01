<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


/**
 * Company support user entity class
 */
class WPAS_Company_Support_User  {
	
	private static $table = 'company_support_users';
    
    public  $id,
			$user_id,
			$profile_id,
			$user_type,
			$divisions,
			$reporting_group,
			$primary,
			$can_reply_ticket,
			$can_close_ticket,
			$can_open_ticket,
			$can_manage_profile;

    /**
	 * construction method
	 * 
	 * @param type $args
	 */
    public function __construct( $args = array() ) {
		
		foreach( $args as $key => $val ) {
            if( property_exists( $this, $key ) ) {
                $this->{$key} = $val;
            }
        }
		
		if( !$this->divisions ) {
			$this->divisions = array();
		}
		
		$this->divisions = !is_array( $this->divisions ) ? explode(',', $this->divisions ) : $this->divisions;

	}
	
	
	/**
	 * Return support user divisions for display
	 * 
	 * @return string
	 */
	public function getDivisions() {
		
		
		$labels = array();
		
		if( !empty( $this->divisions ) ) {
			
			foreach( $this->divisions as $division ) {
				$term = get_term_by( 'id', $division, 'wpas_cp_su_division' );
				
				$labels[] = '<span class="wpas_cp_division wpas-label">'.$term->name.'</span>';
				
			}
		}
		
		
		return implode( '', $labels );
		
	}
	
	
	
	/**
	 * Return reporting group name
	 * 
	 * @return string
	 */
	public function getReportingGroup() {
		$group = '';
		
		if( !empty( $this->reporting_group ) ) {
			
				$term = get_term_by( 'id', $this->reporting_group, 'wpas_cp_su_reporting_group' );
				$group = $term->name;
		}
		
		return $group;
		
	}
	
    
    /**
     * Delete support user
	 * 
     * @global object $wpdb
	 * 
     * @return int|boolean
     */
    public function delete() {
        global $wpdb;
        $deleted = $wpdb->delete( self::table_name(), array( 'id' => $this->id ), array( '%d' ) );
		
		
		if( $deleted ) {
			do_action( 'wpas_cp_company_profile_updated', 'support_user_deleted', $this->user_id, $this->profile_id, $this );
		}
		
		return $deleted;
    }
    
    /**
     * Add new support user
	 * 
     * @global object $wpdb
	 * 
     * @return int
     */
    public function add(  $update_primary = true ) {
        global $wpdb;
		
		
		$data = array(
			
			'user_id'			 => $this->user_id,
			'profile_id'		 => $this->profile_id,
			'user_type'			 => $this->user_type,
			'divisions'			 => implode( ',', $this->divisions ),
			'reporting_group'	 => $this->reporting_group,
			'primary'			 => $this->primary ? '1' : '0',
			'can_reply_ticket'   => $this->can_reply_ticket ? '1' : '0',
			'can_close_ticket'   => $this->can_close_ticket ? '1' : '0',
			'can_open_ticket'    => $this->can_open_ticket ? '1' : '0',
			'can_manage_profile' => $this->can_manage_profile ? '1' : '0',
		);
		
		$format = array( '%d', '%d', '%s' ,'%s', '%d', '%d', '%d', '%d', '%d', '%d' );
		
		if( $wpdb->insert( self::table_name(), $data, $format ) ) {
			$this->id = $wpdb->insert_id;
			
			if( $this->primary ) {
				$this->removePrimaryStatus();
			}
			
			do_action( 'wpas_cp_company_profile_updated', 'support_user_added', $this->user_id, $this->profile_id, $this );
			
			return $this->id;
		}
		
		return '';
    }
	
	
	/**
     * Update existing support user
	 * 
     * @global object $wpdb
	 * 
     * @return int|boolean
     */
    public function update( $update_primary = true ) {
		global $wpdb;
		
		
		$data = array(
			
			'user_id'			 => $this->user_id,
			'user_type'			 => $this->user_type,
			'divisions'			 => implode( ',', $this->divisions ),
			'reporting_group'	 => $this->reporting_group,
			'primary'			 => $this->primary ? '1' : '0',
			'can_reply_ticket'   => $this->can_reply_ticket ? '1' : '0',
			'can_close_ticket'   => $this->can_close_ticket ? '1' : '0',
			'can_open_ticket'    => $this->can_open_ticket ? '1' : '0',
			'can_manage_profile' => $this->can_manage_profile ? '1' : '0',
		);
		
		$old_data = self::getByItemID( $this->id );
		
		$format = array( '%d', '%s' ,'%s', '%d', '%d', '%d', '%d', '%d', '%d' );
		
		$where = array( 'id' => $this->id );
		$where_format = array( '%d' );
		
		
		
		$updated =  $wpdb->update( self::table_name(), $data, $where , $format, $where_format );
		
		if( $updated ) {
			
			if( !$old_data->primary && $this->primary ) {
				$this->removePrimaryStatus();
			}
			
			do_action( 'wpas_cp_company_profile_updated', 'support_user_updated', $this->user_id, $this->profile_id, $old_data );
		}
		
    }
	
	
	/**
	 * Remove primary flag from all support users for a company except this support user
	 * 
	 * @global object $wpdb
	 */
	function removePrimaryStatus() {
		global $wpdb;
		
		$tbl_name = self::table_name();
		
		$q = "UPDATE $tbl_name SET `primary`=%d WHERE profile_id=%d AND `primary`=%d AND id != %d";
		
		$wpdb->query( $wpdb->prepare( $q, 0, $this->profile_id, 1, $this->id ) );
	}
	
	/**
	 * Return table name of this support users table
	 * 
	 * @global object $wpdb
	 * 
	 * @return string
	 */
	static function table_name() {
		global $wpdb;
		
		return $wpdb->prefix . self::$table;
	}


	/**
	 * Return all companies a user is associated with
	 * 
	 * @param int $user_id
	 * 
	 * @return array
	 */
	static function getCompaniesByUser( $user_id ) {
		
		$support_users = self::getByUserID( $user_id );
		
		
		
		$companies = array();
		
		foreach( $support_users as $su ) {
			
			$company = null;
			if( $su->profile_id ) {
				$company = get_post( $su->profile_id );
				
				if( !$company || 'wpas_company_profile' !== $company->post_type ) {
					$company = null;
				}
			}
			
			if( $company ) {
				$companies[] = array(
					'Company' => $company,
					'SupportUser' => $su
				);
			}
			
		}
		
		return $companies;
		
	}
	
	/**
	 * Return all support users based on user id
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $user_id
	 * 
	 * @return array
	 */
	static function getByUserID( $user_id  ) {
		global $wpdb;
		
		$table = self::table_name();
		
		$q = "SELECT * FROM {$table} WHERE user_id=%d";
		
		$results = $wpdb->get_results( $wpdb->prepare( $q, $user_id ) );
		
		$users = array();
		
		foreach( $results as $res ) {
			$users[] = new self( $res );
		}
		
		return $users;
		
	}
		
		
	/**
	 * Return all support users of a company
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $profile_id
	 * 
	 * @return array
	 */
	static function get_profile_support_users( $profile_id ) {
		global $wpdb;
		
		
		$table = self::table_name();
		
		$q = "SELECT * FROM {$table} WHERE profile_id = %d";
		
		
		$results = $wpdb->get_results( $wpdb->prepare( $q, $profile_id ) );
		
		$support_users = array();
		
		foreach ( $results as $res ) {
			
			$support_users[ $res->id ] = new self( $res );
		}
		
		
		return $support_users;
		
	}
	
	/**
	 * Get support user based on company id and user id
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $company_id
	 * @param int $user_id
	 * 
	 * @return \self|null
	 */
	static function getCompanySupportUser( $company_id, $user_id ) {
		global $wpdb;
		
		$table = self::table_name();
		
		
		$q = "SELECT * FROM {$table} WHERE profile_id = %d AND user_id = %s";
		
		$row = $wpdb->get_row( $wpdb->prepare( $q, $company_id, $user_id ) );
		
		
		if( $row ) {
			return new self( $row );
		}
		
		return null;
	}
	
	/**
	 * Check if a support user can reply
	 * 
	 * @param int $company_id
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	static function supportUserCanReply( $company_id, $user_id ) {
		$company_support_user = self::getCompanySupportUser( $company_id, $user_id );
			
		$can_reply = false;
			
		if( $company_support_user && $company_support_user->can_reply_ticket ) {
			$can_reply = true;
		} 
		
		return $can_reply;
	}
	
	/**
	 * Check if support user can close ticket
	 * 
	 * @param int $company_id
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	static function supportUserCanCloseTicket( $company_id, $user_id ) {
		$company_support_user = self::getCompanySupportUser( $company_id, $user_id );
			
		$can_close = false;
			
		if( $company_support_user && $company_support_user->can_close_ticket ) {
			$can_close = true;
		} 
		
		return $can_close;
	}
	
	/**
	 * Check if a support user associated with a company have a permission
	 * 
	 * @param int $company_id
	 * @param int $user_id
	 * @param string $permission
	 * 
	 * @return boolean
	 */
	static function supportUserHavePermission( $company_id, $user_id , $permission ) {
		
		$company_support_user = self::getCompanySupportUser( $company_id, $user_id );
		
		$can = false;
		
		$permission = "can_{$permission}";
			
		if( $company_support_user && $company_support_user->{$permission} ) {
			$can = true;
		}
		
		return $can;
	}
	
	/**
	 * Check if a user is associated with a comapany
	 * 
	 * @param int $company_id
	 * @param int $user_id
	 * 
	 * @return boolean
	 */
	static function user_exist_in_profile( $company_id, $user_id ) {
		
		$exist = false;
		if( self::getCompanySupportUser( $company_id, $user_id ) ) {
			
			$exist = true;
		}
		
		return $exist;
		
	}
	
	/**
	 * Return support user by id
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $item_id
	 * 
	 * @return \self|null
	 */
	static function getByItemID( $item_id ) {
		global $wpdb;
		
		$table = self::table_name();
		
		$q = "SELECT * FROM {$table} WHERE id = %d";
		
		
		$data = $wpdb->get_row( $wpdb->prepare( $q, $item_id ) );
		
		$item = null;
		
		if( $data ) {
			$item = new self( $data );
		}
	
		return $item;
	}
	
	
	static function all_permissions() {
		
		return 
		array(
			 'can_reply_ticket'		=> __( 'Reply Ticket' , 'wpas_cp' ), 
			 'can_close_ticket'		=> __( 'Close Ticket' , 'wpas_cp' ), 
			 'can_open_ticket'		=> __( 'Open Ticket'  , 'wpas_cp' ), 
			 'can_manage_profile'	=> __( 'Manage Profile', 'wpas_cp' )
		);
		
	}
	
	
	/**
	 * Return allowed support user permissions 
	 * 
	 * @return array
	 */
	public function permissions() {
		$permissions = array();
		foreach ( self::all_permissions() as $k => $v ) {
			if( $this->{$k} ) {
				$permissions[ $k ] = $v;
			}
		}
		
		return $permissions;
		
	}
	
	
	/**
	 * Display support user permissions
	 * 
	 * @return string
	 */
	public function display_permissions() {
		$permissions = $this->permissions();
		
		return '<ul class="permissions"><li>' . implode( '</li><li>', $permissions ) . '</li></ul>';
	}
	
}