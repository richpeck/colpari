<?php


if ( ! defined( 'WPINC' ) ) {
    die;
}


/**
 * Class to handle company related logs
 */
class WPAS_CP_Log  {
	
	private static $table = 'company_logs';
    
    public  $id,
			$company_id,
			$log_type,
			$date,
			$content,
			$status
			;

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
		
	}
	
	
	/**
	 * return logs table name
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
	 * Return all logs based on company id
	 * 
	 * @global object $wpdb
	 * 
	 * @param int $company_id
	 * 
	 * @return array
	 */
	public static function getCompanyLogs( $company_id ) {
		global $wpdb;
		
		$table = self::table_name();
		
		$query = "SELECT * FROM {$table} WHERE company_id=%s";
		
		return $wpdb->get_results( $wpdb->prepare( $query, $company_id ) );
	}
	
	
    
    /**
     * Add new log
	 * 
     * @global object $wpdb
	 * 
     * @return int
     */
    public static function add( $args ) {
        global $wpdb;
		
		$company_id = isset( $args['company_id'] ) ? $args['company_id'] : '';
		$log_type	= isset( $args['log_type'] ) ? $args['log_type'] : '';
		$content	= isset( $args['content'] ) ? $args['content'] : '';
		
		$date		= current_time( 'mysql' );
		$status		= isset( $args['status'] ) ? $args['status'] : 'published';

		
		$content = wp_kses( $content, wp_kses_allowed_html( 'post' ) );
		

		if( '' === $content || !$company_id || !$log_type ) {
			return false;
		}
		
		
		$data = array(
			'company_id'		=> $company_id,
			'log_type'			=> $log_type,
			'date'				=> $date,
			'content'			=> $content,
			'status'			=> $status
		);
		
		$format = array( '%d', '%s', '%s', '%s', '%s' );
		
		if( $wpdb->insert( $wpdb->prefix . self::$table, $data, $format ) ) {
			return $wpdb->insert_id;
		}
		
		return '';
    }
	
	
	
	public static function add_log( $type, $user_id, $profile_id, $support_user ) {
	
	
		$log_args = array(
					'company_id'		=> $profile_id,
					'log_type'			=> $type,
				);



		$user = get_user_by( 'id', $user_id );

		$display_name = $user ? $user->display_name : "";
		
		
		$current_user = wp_get_current_user();
		
		
		

		switch ( $type ) {
			case "support_user_deleted" :

				$log_args['content'] = sprintf( 'Support user %s deleted', $display_name );

				break;
			case "support_user_added":

				$log_args['content'] = sprintf( 'A new support user %s added', $display_name );

				break;
			case "support_user_updated" :

				$content = sprintf( _x( 'Support user %s updated', 'Support user was updated', 'wpas_cp' ) , $display_name );


				$content .= self::support_user_updated_log_content( $support_user );

				$log_args['content'] = $content;

				break;
		}
		
		
		if( $current_user && isset( $log_args['content'] ) ) {
			$log_args['content'] = $log_args['content'] . " by {$current_user->display_name}";
		}
		
		if( isset( $log_args['content'] ) ) {
			$log_args['content'] .= '.';
		}
		
		

		self::add( $log_args );

	}


	public static function support_user_updated_log_content( $old_su ) {

		$support_user = WPAS_Company_Support_User::getByItemID( $old_su->id );



		$logs = array();


		$fields = array(

			'divisions'  => array(
				'name' => 'Division',
				'type' => 'taxonomy',
				'tax' => 'wpas_cp_su_division'
				),
			'reporting_group' => array(
				'name' => 'Reporting Group',
				'type' => 'taxonomy',
				'tax' => 'wpas_cp_su_reporting_group'
				),
			'primary' => array(
				'name' => 'Primary',
				'type' => 'checkbox'
				),
			'can_reply_ticket' => array(
				'name' => 'Can Reply Ticket',
				'type' => 'checkbox'
				),
			'can_close_ticket' => array(
				'name' => 'Can Close Ticket',
				'type' => 'checkbox'
				),
			'can_open_ticket' => array(
				'name' => 'Can Open Ticket',
				'type' => 'checkbox'
				),
			'can_manage_profile' => array(
				'name' => 'Can Manage Profile',
				'type' => 'checkbox'
				),		
		);


		foreach( $fields as $f => $args ) {

			$result = 0;
			if( $old_su->{$f} && !$support_user->{$f} ) {
				$result = 3;
			} elseif ( $old_su->{$f} && $support_user->{$f} && $old_su->{$f} !== $support_user->{$f} ) {
				$result = 2;
			} elseif ( !$old_su->{$f} && $support_user->{$f} ) {
				$result = 1;
			} 


			$value = $support_user->{$f};
			$label = $args['name'];

			if ( 'taxonomy' === $args['type'] && $result ) {

				$_val = !is_array( $value ) ? array( $value ) : $value;
				$term_names = array();
				foreach( $_val as $v ) {
					$term  = get_term( (int) $v, $args['tax'] );

					$term_names[] = $term->name;
				}

				$value = implode(', ', $term_names );
			}




			switch ( (int) $result ) {
				case 2:
					$logs[] = '<li>' . sprintf( __( 'Updated %s to %s', 'Support user value was updated', 'wpas_cp' ), $label, $value ) . '</li>';
					break;

				case 3:

					if( 'checkbox' === $args['type'] ) {
						$logs[] = '<li>' . sprintf( __( '%s unchecked', 'Support user value was deleted', 'wpas_cp' ), $label ) . '</li>';
					} else {
						$logs[] = '<li>' . sprintf( __( 'deleted %s', 'Support user value was deleted', 'wpas_cp' ), $label ) . '</li>';
					}


					break;

				case 1:

					if( 'checkbox' === $args['type'] ) {
						$logs[] = '<li>' . sprintf( __( '%s checked', 'Support user value was added', 'wpas_cp' ), $label ) . '</li>';
					} else {
						$logs[] = '<li>' . sprintf( __( 'added %s to %s', 'Support user value was added', 'wpas_cp' ), $value, $label ) . '</li>';
					}



					break;

			}



		}




		$content = "";

			if( !empty( $logs ) ) {

				$content .= '<ul class="wpas-log-list">';
				$content .= implode('', $logs);
				$content . '<ul>';
			}

		return $content;
		
	}
}