<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AS_Time_Tracking_Validator {
  private $validator;
  private $passed_validation;
	private $success_values;

  public function __construct() {
    $this->validator = array();
    $this->validator['errors'] = array();
    $this->passed_validation = false;
		$this->success_values = array();
  }

  /**
	 * Returns the final validation information.
	 *
	 * @since  0.1.0
	 * @return array
	 */
  public function get_validator() {
    return $this->validator;
  }

  /**
	 * Sets the error message
	 *
	 * @since		0.1.0
	 * @param		string $arr_key  		The identifer in success or failed array for the field being checked
	 * @param		string $message     The error message to be shown
	 * @return	void
	 */
	public function set_error( $arr_key, $message ) {
		$this->validator['errors'][$arr_key] = $message;
	}

  /**
	 * Unsets any error entries and adds successful validation values to the object
	 *
	 * @since		0.1.0
	 * @param		string $arr_key  		The identifer in success or failed array for the field being checked
	 * @param		string $field       The value for the field
	 * @return	void
	 */
	public function set_success_values( $arr_key, $field ) {
    unset( $this->validator['errors'][$arr_key] );
    $this->success_values[$arr_key] = $field;
	}

  /**
	 * Saves the ticket level select box value if proper value.
	 *
	 * @since		0.1.0
	 * @param		string $check_value   The checkbox value
	 * @param		string $arr_key  		   The identifer in success or failed array for the field being checked
	 * @return	void
	 */
  public function check_ticket_level( $check_value, $arr_key ) {
    if( $check_value == 'on' ) {

      $this->set_success_values( $arr_key, $check_value );
    }
  }

	/**
	 * Checks if ticket reply has value when multiple option is set to no, but the ticket level saving is allowed.
	 *
	 * @since		0.1.0
	 * @param		string $ticket_reply       The ticket reply
	 * @param		string $arr_key  		The identifer in success or failed array for the field being checked
	 * @return	void
	 */
  public function check_multiple_disallowed_ticket_level_allowed( $ticket_reply, $arr_key ) {
    if( strlen( $ticket_reply ) != 0 ) {
			$this->set_error( $arr_key, "<p>" . __( "The current settings only allow for saving on the ticket level. No ticket reply can be entered.", "awesome-support-time-tracking" ) . "</p>" );
    } else {
      $this->set_success_values( $arr_key, $ticket_reply );
    }
  }

  /**
	 * Check if field is empty.
	 *
	 * @since		0.1.0
	 * @param		string $field       The field string to check
	 * @param		string $field_name  The name of the field to concatenate an error message if needed
	 * @param		string $arr_key  		The identifer in success or failed array for the field being checked
	 * @return	void
	 */
  public function check_empty( $field, $field_name, $arr_key ) {
    if( strlen( $field ) == 0 ) {
			$this->set_error( $arr_key, "<p>" . __( "The " . $field_name . " cannot be empty!", "awesome-support-time-tracking" ) . "</p>" );
    } else {
      $this->set_success_values( $arr_key, $field );
    }
  }

  /**
	 * Check if field is numeric.
	 *
	 * @since		0.1.0
	 * @param		string $field      	The field string to check
	 * @param		string $field_name  The name of the field to concatenate an error message if needed
   * @param		string $arr_key    	The array key name for the error or success array
	 * @return	void
	 */
  public function check_numeric( $field, $field_name, $arr_key ) {
    if( !is_numeric( $field ) ) {
      $this->set_error( $arr_key, "<p>" . __( "The " . $field_name . " must be a number!", "awesome-support-time-tracking" ) . "</p>" );
    } else {
      $this->set_success_values( $arr_key, $field );
    }
  }

  /**
	 * Check if total time adjustments is numeric and not empty.
	 *
	 * @since		0.1.0
	 * @param		string $field      	The field string to check
	 * @param		string $field_name  The name of the field to concatenate an error message if needed
   * @param		string $arr_key    	The array key name for the error or success array
	 * @return	void
	 */
  public function check_numeric_time_adjustments( $field, $field_name, $arr_key ) {
    if( $field != '' && !is_numeric( $field ) ) {
      $this->set_error( $arr_key, "<p>" . __( "The " . $field_name . " must be a number!", "awesome-support-time-tracking" ) . "</p>" );
    } else {
      $this->set_success_values( $arr_key, $field );
    }
  }

  /**
	 * Check if ticket exists.
	 *
	 * @since		0.1.0
	 * @param		string $ticket_id  	The ticket id to check
   * @param		string $arr_key  	 	The array key name for the error or success array
	 * @return	void
	 */
	public function ticket_exists( $ticket_id, $arr_key ) {
		global $wpdb;
		$db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE ID = " . ( int )$ticket_id . " AND post_type = 'ticket' AND post_status != 'trash'";
		$db_result = $wpdb->get_results( $db_query, OBJECT );

		if(count($db_result) === 0) {
      $this->set_error( $arr_key, "<p>" . __( "The ticket id entered does not belong to an existing ticket!", "awesome-support-time-tracking" ) . "</p>" );
		} else {
      $this->set_success_values( $arr_key, $ticket_id );
		}
	}

  /**
	 * Check if ticket reply belongs to the given ticket id.
	 *
	 * @since		0.1.0
	 * @param		string $ticket_reply_id		The ticket reply id to check
   * @param		string $ticket_id  				The ticket id to cross check
   * @param		string $arr_key						The array key name for the error or success array
	 * @return	void
	 */
  public function check_ticket_reply( $ticket_reply_id, $ticket_id, $arr_key ) {
	 	if( is_numeric( $ticket_reply_id ) ) {
			$ticket_reply_parent = wp_get_post_parent_id( $ticket_reply_id );
			if( $ticket_reply_parent != $ticket_id ) {
        $this->set_error( $arr_key, "<p>" . __( "The ticket reply entered does not belong to the ticket entered!", "awesome-support-time-tracking" ) . "</p>" );
			} else {
        $this->set_success_values( $arr_key, $ticket_reply_id );
			}
		} else {
      $this->set_error( $arr_key, "<p>" . __( "The ticket reply entered does not belong to the ticket entered!", "awesome-support-time-tracking" ) . "</p>" );
		}
  }

	/**
   * Check if a date field is in the correct format.
   *
   * @since		0.1.0
	 * @param		string $date				The date to check
	 * @param		string $field_name	The name of the field to concatenate an error message if needed
	 * @param		string $arr_key			The array key name for the error or success array
	 * @return	void
	 */
	public function check_date_format( $date, $field_name, $arr_key ) {
		$date_object = DateTime::createFromFormat( 'Y-m-d', $date );
		$is_date_valid = $date_object && $date_object->format( 'Y-m-d' ) === $date;

		if( $is_date_valid != 1 ) {
      $this->set_error( $arr_key, "<p>" . __( "The " . $field_name . " must be in a yyyy-mm-dd format!", "awesome-support-time-tracking" ) . "</p>" );
		} else {
      $this->set_success_values( $arr_key, $date );
		}
	}

	/**
	 * Check if end date/time is before start date/time.
	 *
	 * @since		0.1.0
	 * @param		string $start_date_time			The start date/time
	 * @param		string $end_date_time				The end date/time
	 * @param		string $arr_key							The array key name for the error or success array
	 * @return	void
	 */
	public function check_start_end_date( $start_date_time, $end_date_time, $arr_key ) {
		if( $end_date_time < $start_date_time ) {
      $this->set_error( $arr_key, "<p>" . __( "The end date/time cannot be before the start date/time!", "awesome-support-time-tracking" ) . "</p>" );
		} else {
			unset( $this->validator['errors'][$arr_key] );
		}
	}

	/**
	 * Check if a tracked time has already been saved with the entered ticket # and ticket reply #.
	 *
	 * @since		0.1.0
	 * @param 	string $ticket_id						The entered ticket #
	 * @param		string $ticket_reply_id			The entered ticket reply #
	 * @param		string $arr_key							The array key name for the error or success array
	 * @param		string $post_id							The id of the current tracked time entry
	 * @return	void
	 */
	public function check_existing_tracked_time( $ticket_id, $ticket_reply_id, $arr_key, $post_id ) {
			global $wpdb;
			$query = new WP_Query( array(
															'post_type' => 'trackedtimes',
															'post_status' => 'publish',
															'posts_per_page' => -1
														) );

			$time_ids = wp_list_pluck( $query->posts, 'ID' );
			$duplicate_occurs = false;

			foreach( $time_ids as $id ) {
				$tracked_time = get_post_meta( $id, 'as_time_tracking_entry' );

				if( !empty( $tracked_time ) ) {
					if( ( $ticket_id == $tracked_time[0]['ticket_id'] ) && ( $ticket_reply_id == $tracked_time[0]['ticket_reply'] ) && ( $id != $post_id ) ) {
						$duplicate_occurs = true;
						break 1;
					}
				}
			}

			if( $duplicate_occurs === true ) {
        $this->set_error( $arr_key, "<p>" . __( "The ticket # and ticket reply # already belong to an existing tracked time!", "awesome-support-time-tracking" ) . "</p>" );
			} else {
				unset( $this->validator['errors'][$arr_key] );
			}
	}

	/**
	 * Logic to determine if admin message should be an error or success.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function check_final_status() {
		if( count( $this->validator['errors'] ) == 0 ) {
			$this->validator['success'] = __( "The tracked time has been saved!", "awesome-support-time-tracking" );
			$this->validator['notice_class'] = "notice notice-success is-dismissible";
		} else {
			unset( $this->validator['success'] );
			$this->validator['notice_class'] = "notice notice-error";
		}
	}

	/**
	 * Return true or false if validation errors exist.
	 *
	 * @since  0.1.0
	 * @return boolean
	 */
	public function validation_errors_exist() {
		if( count( $this->validator['errors']) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if invoice field is one of three values
	 *
	 * @since		0.1.0
	 * @param		string $field       The field string to check
	 * @param		string $field_name  The name of the field to concatenate an error message if needed
	 * @param		string $arr_key  		The identifer in success or failed array for the field being checked
	 * @return	void
	 */
  public function check_invoiced_values( $field, $field_name, $arr_key ) {
		if( $field == "approved" || $field == "" || $field == "in process" ) {
      $this->set_success_values( $arr_key, $field );
		} else {
      $this->set_error( $arr_key, "<p>" . __( "The " . $field_name . " must be Approved, Not approved or empty!", "awesome-support-time-tracking" ) . "</p>" );
		}
  }

	/**
	 * Returns the successful validated values.
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_validation_success_values() {
		if( count( $this->validator['errors'] ) > 0 ) {
			return $this->success_values;
		} else {
			return false;
		}
	}
}
