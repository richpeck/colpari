<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Link to text domain languages directory for the plugin.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_load_textdomain() {
	load_plugin_textdomain( 'awesome-support-time-tracking-textdomain', false, AS_TT_PATH . 'languages/' );
}

add_action( 'plugins_loaded', 'as_time_tracking_load_textdomain' );

/**
 * Register the Time Tracking post type.
 *
 * @since 0.1.0
 * @return void
 */
function as_tracked_time_register_post_type() {
	$labels = array(
		'name'               	=> _x( 'Time Tracking', 'post type general name', 'awesome-support-time-tracking' ),
		'singular_name'     	=> _x( 'Tracked Time', 'post type singular name', 'awesome-support-time-tracking' ),
		'menu_name'           => _x( 'Time Tracking', 'admin menu', 'awesome-support-time-tracking' ),
		'name_admin_bar'     	=> _x( 'Tracked Time', 'add new on admin bar', 'awesome-support-time-tracking' ),
		'add_new'            	=> _x( 'Add Tracked Time', 'book', 'awesome-support-time-tracking' ),
		'add_new_item'				=> __( 'Add New Tracked Time', 'awesome-support-time-tracking' ),
		'edit_item'						=> __( 'Edit Tracked Time', 'awesome-support-time-tracking' ),
		'new_item'						=> __( 'New Tracked Time', 'awesome-support-time-tracking' ),
		'view_item'						=> __( 'View Tracked Time', 'awesome-support-time-tracking' ),
		'view_items'					=> __( 'View Tracked Times', 'awesome-support-time-tracking' ),
		'search_items'				=> __( 'Search Tracked Times', 'awesome-support-time-tracking' ),
		'not_found'						=> __( 'No Tracked Times found', 'awesome-support-time-tracking' ),
		'not_found_in_trash'	=> __( 'No Tracked Times found in trash', 'awesome-support-time-tracking' ),
		'all_items'						=> __( 'Time Tracking', 'awesome-support-time-tracking' ),
		'archives'						=> __( 'Tracked Time Archives', 'awesome-support-time-tracking' ),
		'attributes'					=> __( 'Tracked Time Attributes', 'awesome-support-time-tracking' )
	);

	$args = array(
		'labels'            	 => $labels,
    'description'       	 => __( 'Description.', 'awesome-support-time-tracking' ),
		'public'            	 => false,
		'publicly_queryable' 	=> false,
		'show_ui'           	 => true,
		'show_in_menu'      	=> true,
		'query_var'          	=> true,
		'rewrite'           	 => array( 'slug' => 'time_tracking' ),
		'capability_type'   	 => 'post',
		'has_archive'       	 => false,
		'hierarchical'       	=> false,
		'menu_position'     	 => null,
		'menu_icon'          	=> 'dashicons-clock',
		'exclude_from_search'	=>	true,
		'supports'           	=> array( 'title' )
	);

	register_post_type( 'trackedtimes', $args );
}

add_action( 'init', 'as_tracked_time_register_post_type' );

/**
 * Remove quick edit action link.
 *
 * @param 	array $actions	The links available
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_remove_unwanted_actions( $actions, $post ) {
	global $current_screen;

	if( $current_screen->post_type != 'trackedtimes' ) {
		return $actions;
	}

	unset( $actions['inline hide-if-no-js'] );

  //Remove edit links based on capabilities
  if( !current_user_can( 'edit_other_tracked_time' ) && !current_user_can( 'edit_own_tracked_time' ) ) {
    unset( $actions['edit'] );
  }

  if( !current_user_can( 'delete_own_tracked_time' ) && !current_user_can( 'delete_other_tracked_time' ) ) {
    unset( $actions['trash'] );
  }

  //Allow duplicate/copy link to be added if multiple entry setting is enabled
  $allow_multiple_entries = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );

  if( $allow_multiple_entries === 'yes' ) {
  	$actions['clone_copy'] = "<a class='as_time_tracking_clone_copy' href='" . admin_url('post-new.php?post_type=trackedtimes&post_id_copy=' . $post->ID) . "'>" . __( 'Clone/Copy', 'awesome-support-time-tracking' ) . "</a>";
  }

  return $actions;
}

add_filter( 'post_row_actions', 'as_time_tracking_remove_unwanted_actions', 10, 2 );

/**
 * Remove the publish post message notice, a custom one will display instead based on validation.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_post_published( $messages ) {
	global $current_screen;

	if( $current_screen->id == 'trackedtimes' ) {
		unset( $messages['post'][6] );
    return $messages;
	}
}

add_filter( 'post_updated_messages', 'as_time_tracking_post_published' );

/**
 * Remove publish metabox, a custom one is used instead.
 *
 * @since  0.1.0
 * @return void
 */
function as_time_tracking_remove_publish_box() {
    remove_meta_box( 'submitdiv', 'trackedtimes', 'side' );
}

add_action( 'admin_menu', 'as_time_tracking_remove_publish_box' );

/**
 * Add the new publish metabox.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_track_time_add_custom_publish_metabox() {
	add_meta_box(
		'as_time_tracking_publish_meta',
		__( 'Time Tracking', 'awesome-support-time-tracking' ),
		'as_time_tracking_publish_meta_callback',
		'trackedtimes',
		'side'
	);
}

add_action( 'add_meta_boxes_trackedtimes', 'as_track_time_add_custom_publish_metabox' );

/**
 * Create new metabox on tickets page to hold the timer.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_cpt_timer_metabox() {
    add_meta_box(
        'as_time_tracking_cpt_timer_container',
        __( 'Time Tracking Timer', 'awesome-support-time-tracking' ),
        'as_time_tracking_display_metabox_timer', //The output is called from the ticket timer as the HTML is the same
        'trackedtimes',
        'side',
        'default'
    );
}

add_action( 'add_meta_boxes_trackedtimes', 'as_time_tracking_cpt_timer_metabox' );

/**
 * Output of the custom publish metabox.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_publish_meta_callback() {
?>
	<!-- Button wrapped around this div to remove confirm message on submit in FireFox -->
	<div class="submitbox" id="submitpost">
		<?php
		$saved_data = get_post_meta( get_the_ID(), 'as_time_tracking_entry' );

		//Only show publish button when the post doesn't have an "approved" invoice status
		if( empty( $saved_data ) ) {
			submit_button( __( 'Save Changes', 'awesome-support-time-tracking' ), 'primary', 'publish', false );
			echo "<br>";
		} elseif ( !empty( $saved_data ) && $saved_data[0]['invoiced'] != "approved" ) {
			submit_button( __( 'Save Changes', 'awesome-support-time-tracking' ), 'primary', 'publish', false );
			echo "<br>";
		}
		?>
			<a class="as-time-tracking-trash-link" href="<?php echo get_delete_post_link(); ?>"><?php _e( 'Move To Trash', 'awesome-support-time-tracking' ); ?></a>
	</div>
<?php
}

/**
 *  Used as a helper function. Outputs title if valid, which js will handle.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_set_js_title_valid( $field_values ) {
  if( $field_values['ticket_id_val'] != '' && $field_values['ticket_reply_val'] != '' ) {
    echo "<div id='as_time_tracking_title_validation'>";

    printf( __( 'Ticket #: %1$s, Ticket reply #: %2$s', 'awesome-support-time-tracking' ),
        $field_values['ticket_id_val'],
        $field_values['ticket_reply_val']
    );

    echo "</div>";
  }
}

/**
 *  Used as a helper function. Determines values for ticket and ticket reply.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_ticket_values( $field_values ) {
  $values = $field_values;

	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if( isset( $wp_session['ticket_level_enabled'] ) ) {
    $values['ticket_level_enabled'] = $wp_session['ticket_level_enabled'];
  } else {
      $values['ticket_level_enabled'] = '';
  }

  if(
    isset( $wp_session['ticket_id_empty'] ) &&
    isset( $wp_session['ticket_id_numeric'] ) &&
    isset( $wp_session['ticket_id_exists'] )
  ) {
    $values['ticket_id_val'] = $wp_session['ticket_id_empty'];
  }

	//To fix bug where on multiple setting the ticket reply wouldn't save
	$allow_multiple_entries = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );

  if( $allow_multiple_entries == 'no' ) {
		if(
	    isset( $wp_session['ticket_reply_id_empty'] ) &&
	    isset( $wp_session['ticket_reply_id_numeric'] ) &&
	    isset( $wp_session['ticket_reply_id_matches_parent'] )
	  ) {
	    $values['ticket_reply_val'] = $wp_session['ticket_reply_id_empty'];
	  }
	} elseif( $wp_session['multiple_disallowed_ticket_allowed'] ) {
    //Might not be needed in future
    $values['ticket_reply_val'] = $wp_session['multiple_disallowed_ticket_allowed'];
  } else {
		if( isset( $wp_session['ticket_reply_id_numeric'] ) ) {
			$values['ticket_reply_val'] = $wp_session['ticket_reply_id_numeric'];
		}
	}

  return $values;
}

/**
 *  Used as a helper function. Determines values for entry date.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_entry_date_values( $field_values ) {
  $values = $field_values;
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['entry_date_empty'] ) &&
    isset( $wp_session['entry_date_format'] )
  ) {
    $field_values['entry_date_val'] = $wp_session['entry_date_empty'];
  }

  return $values;
}

/**
 *  Used as a helper function. Determines values for entry times.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_entry_time_values( $field_values ) {
  $values = $field_values;
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['entry_date_hours_numeric'] ) &&
    isset( $wp_session['entry_date_hours_empty'] )
  ) {
    $field_values['entry_date_hours_val'] = $wp_session['entry_date_hours_empty'];
  }

  if(
    isset( $wp_session['entry_date_minutes_numeric'] ) &&
    isset( $wp_session['entry_date_minutes_empty'] )
  ) {
    $field_values['entry_date_minutes_val'] = $wp_session['entry_date_minutes_empty'];
  }

  if(
    isset( $wp_session['entry_date_seconds_numeric'] ) &&
    isset( $wp_session['entry_date_seconds_empty'] )
  ) {
    $field_values['entry_date_seconds_val'] = $wp_session['entry_date_seconds_empty'];
  }

  return $values;
}

/**
 *  Used as a helper function. Determines values for start date.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_start_date_values( $field_values ) {
  $values = $field_values;
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['start_date_empty'] ) &&
    isset( $wp_session['start_date_format'] )
  ) {
    $values['start_date_val'] = $wp_session['start_date_empty'];
  }

  return $values;
}

/**
 *  Used as a helper function. Determines values for start times.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_start_time_values( $field_values ) {
  $values = $field_values;
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['start_date_hours_numeric'] ) &&
    isset( $wp_session['start_date_hours_empty'] )
  ) {
    $values['start_date_hours_val'] = $wp_session['start_date_hours_empty'];
  }

  if(
    isset( $wp_session['start_date_minutes_numeric'] ) &&
    isset( $wp_session['start_date_minutes_empty'] )
  ) {
    $values['start_date_minutes_val'] = $wp_session['start_date_minutes_empty'];
  }

  if(
    isset( $wp_session['start_date_seconds_numeric'] ) &&
    isset( $wp_session['start_date_seconds_empty'] )
  ) {
    $values['start_date_seconds_val'] = $wp_session['start_date_seconds_empty'];
  }

  return $values;
}

/**
 *  Used as a helper function. Determines values for end date.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_end_date_values( $field_values ) {
  $values = $field_values;
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['end_date_empty'] ) &&
    isset( $wp_session['end_date_format'] )
  ) {
    $values['end_date_val'] = $wp_session['end_date_empty'];
  }

  return $values;
}

/**
 *  Used as a helper function. Determines values for end times.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_end_time_values( $field_values ) {
  $values = $field_values;
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['end_date_hours_numeric'] ) &&
    isset( $wp_session['end_date_hours_empty'] )
  ) {
    $values['end_date_hours_val'] = $wp_session['end_date_hours_empty'];
  }

  if(
    isset( $wp_session['end_date_minutes_numeric'] ) &&
    isset( $wp_session['end_date_minutes_empty'] )
  ) {
    $values['end_date_minutes_val'] = $wp_session['end_date_minutes_empty'];
  }

  if(
    isset( $wp_session['end_date_seconds_numeric'] ) &&
    isset( $wp_session['end_date_seconds_empty'] )
  ) {
    $values['end_date_seconds_val'] = $wp_session['end_date_seconds_empty'];
  }

  return $values;
}

/**
*  Used as a helper function. Determines values for total time if automatic timer.
*  This is because if validation fails the previous timer is overridden by the automatic running timer.
 *
 * @param		array  $automatic_values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_automatic_time_values() {
  $values = array();
  $timer_type = wpas_get_option( 'time_tracking_timer_type', 'automatic' );
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if( $timer_type = 'automatic' ) {
    if(
      isset( $wp_session['total_time_hours_not_empty'] )
    ) {
      $values['hours'] = $wp_session['total_time_hours_not_empty'];
    }

    //Total time minutes field checks
    if(
      isset( $wp_session['total_time_minutes_empty'] ) &&
      isset( $wp_session['total_time_minutes_numeric'] )
    ) {
      $values['minutes'] = $wp_session['total_time_minutes_empty'];
    }
  }

  return $values;
}

/**
 *  Used as a helper function. Determines values for total time if automatic timer.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_validation_determine_total_time_values( $field_values ) {
  $values = $field_values;
  $timer_type = wpas_get_option( 'time_tracking_timer_type', 'automatic' );
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  if(
    isset( $wp_session['total_time_hours_not_empty'] )
  ) {
    $values['total_time_hours_val'] = $wp_session['total_time_hours_not_empty'];
  }

  //Total time minutes field checks
  if(
    isset( $wp_session['total_time_minutes_empty'] ) &&
    isset( $wp_session['total_time_minutes_numeric'] )
  ) {
    $values['total_time_minutes_val'] = $wp_session['total_time_minutes_empty'];
  }

  if(
    isset( $wp_session['total_time_adjustments_numeric'] )
  ) {
    $values['total_time_adjustments_val'] = $wp_session['total_time_adjustments_numeric'];
  }

  return $values;
}

/**
 *  Used as a helper function. Check if validation errors exist and set up the
 *	correct values from the previous attempt, then unset the validation session
 *	storing the values. The validator stores inputted array keys so we use that to
 *	identify the input fields. We can use any array key to set a value as all of them
 *	if passed will store a correct value.
 *
 * @param		array  $field_values   The current field values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_determine_validation_values( $field_values ) {
	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

  $field_values = as_time_tracking_validation_determine_ticket_values( $field_values );

  //Set title text for js to handle if ticket # and reply # are valid
  as_time_tracking_set_js_title_valid( $field_values );

  //Entry date field checks
  $field_values = as_time_tracking_validation_determine_entry_date_values( $field_values );
  $field_values = as_time_tracking_validation_determine_entry_time_values( $field_values );

  //Start date field checks
  $field_values = as_time_tracking_validation_determine_start_date_values( $field_values );
  $field_values = as_time_tracking_validation_determine_start_time_values( $field_values );

  //End date field checks
  $field_values = as_time_tracking_validation_determine_end_date_values( $field_values );
  $field_values = as_time_tracking_validation_determine_end_time_values( $field_values );

  //Total time field checks
  $field_values = as_time_tracking_validation_determine_total_time_values( $field_values );

  //Notes field check
  ( isset( $wp_session['notes_not_empty'] ) ? $field_values['notes_val'] = $wp_session['notes_not_empty'] : '' );

  //Agent field checks
  if(
    isset( $wp_session['agent_id_numeric'] ) ||
    isset( $wp_session['agent_empty'] )
  ) {
    $field_values['agent_val'] = $wp_session['agent_empty'];
  }

  //Invoiced field checks
  if(
    isset( $wp_session['invoiced_values'] )
  ) {
    $field_values['invoiced_val'] = $wp_session['invoiced_values'];
  }

  WPAS()->session->clean( 'as_time_tracking_validation_passed_values' );

	return $field_values;
}

/**
 * Add metabox with fields to the custom post type content area.
 *
 * @since 	0.1.0
 * @return	void
 */
function as_track_time_add_custom_metabox() {
	add_meta_box(
		'as_time_tracking_meta',
		__( 'Time Tracking Fields', 'awesome-support-time-tracking' ),
		'as_time_tracking_meta_callback',
		'trackedtimes'
	);
}

add_action( 'add_meta_boxes', 'as_track_time_add_custom_metabox' );

/**
 * Outputs the html for the tracked times new/edit screen.
 *
 * @since 	0.1.0
 * @return	void
 */
function as_time_tracking_meta_callback() {
	if(  WPAS()->session->get( 'as_time_tracking_validation_passed_values' ) == false ) {
		 WPAS()->session->add( 'as_time_tracking_validation_passed_values', array() );
	}

	$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );

	/** Set field values. This will either stay empty, enter successful values on a
	 *	failed submission, or show values saved from the database. */
  $automatic_values = array();
  $field_values = array();
	$field_values['ticket_id_val'] = "";
	$field_values['ticket_reply_val'] = "";
	$field_values['entry_date_val'] = "";
	$field_values['entry_date_hours_val'] = "";
	$field_values['entry_date_minutes_val'] = "";
	$field_values['entry_date_seconds_val'] = "";
	$field_values['start_date_val'] = "";
	$field_values['start_date_hours_val'] = "";
	$field_values['start_date_minutes_val'] = "";
	$field_values['start_date_seconds_val'] = "";
	$field_values['end_date_val'] = "";
	$field_values['end_date_hours_val'] = "";
	$field_values['end_date_minutes_val'] = "";
	$field_values['end_date_seconds_val'] = "";
	$field_values['total_time_hours_val'] = "";
	$field_values['total_time_minutes_val'] = "";
	$field_values['total_time_adjustments_val'] = "";
	$field_values['notes_val'] = "";
	$field_values['agent_val'] = "";
	$field_values['invoiced_val'] = "";
	$field_values['invoice_number_val'] = "";
  $field_values['ticket_level_enabled'] = "";

	//Data if exists for the post
	$saved_data = get_post_meta( get_the_ID(), 'as_time_tracking_entry' );

  if( empty( $saved_data ) ) {
    echo "<input id='as_time_tracking_new_or_saved_post' type='hidden' value='new'>";
  } else {
    echo "<input id='as_time_tracking_new_or_saved_post' type='hidden' value='saved'>";
  }

	if( !empty( $saved_data ) ) {
		$entry_date_time = new DateTime( $saved_data[0]['entry_date_time'] );
		$entry_date = $entry_date_time->format( "Y-m-d" );
		$entry_time = explode( ":", $entry_date_time->format( "H:i:s" ) );

		$start_date_time = new DateTime( $saved_data[0]['start_date_time'] );
		$start_date = $start_date_time->format( "Y-m-d" );
		$start_time = explode( ":", $start_date_time->format( "H:i:s" ));

		$end_date_time = new DateTime( $saved_data[0]['end_date_time'] );
		$end_date = $end_date_time->format( "Y-m-d" );
		$end_time = explode( ":", $end_date_time->format( "H:i:s" ) );
		$calculated_time = $saved_data[0]['individual_time'];

		$calculated_hours = intval( $calculated_time / 60 );
		$calculated_minutes = $calculated_time - ( $calculated_hours * 60 );

		$adjusted_time = $saved_data[0]['adjusted_time'];

		$field_values['ticket_id_val'] = $saved_data[0]['ticket_id'];
		$field_values['ticket_reply_val'] = $saved_data[0]['ticket_reply'];
		$field_values['start_date_val'] = $start_date;
		$field_values['start_date_hours_val'] = $start_time[0];
		$field_values['start_date_minutes_val'] = $start_time[1];
		$field_values['start_date_seconds_val'] = $start_time[2];
		$field_values['end_date_val'] = $end_date;
		$field_values['end_date_hours_val'] = $end_time[0];
		$field_values['end_date_minutes_val'] = $end_time[1];
		$field_values['end_date_seconds_val'] = $end_time[2];
		$field_values['total_time_hours_val'] = $calculated_hours;
		$field_values['total_time_minutes_val'] = $calculated_minutes;
		$field_values['total_time_adjustments_val'] = $adjusted_time;
		$field_values['notes_val'] = $saved_data[0]['notes'];
		$field_values['agent_val'] = $saved_data[0]['agent'];
		$field_values['entry_date_val'] = $entry_date;
		$field_values['entry_date_hours_val'] = $entry_time[0];
		$field_values['entry_date_minutes_val'] = $entry_time[1];
		$field_values['entry_date_seconds_val'] = $entry_time[2];
		$field_values['invoiced_val'] = $saved_data[0]['invoiced'];
		$field_values['invoice_number_val'] = $saved_data[0]['invoice_number'];

    if( $saved_data[0]['is_ticket_level'] != '' ) {
      $field_values['ticket_level_enabled'] = 'on';
    }
	}

  //Set values if validation errors occured
	if( !empty( $wp_session ) ) {

    $automatic_values = as_time_tracking_validation_automatic_time_values();
		$field_values = as_time_tracking_determine_validation_values( $field_values );
	}
?>
	<div class="as-time-tracking-button-container">
		<input id="as_time_tracking_invoice_hidden_status" type="hidden" value="<?php echo $field_values['invoiced_val']; ?>">
		<?php

		//If approved invoice status hide
		if( $field_values['invoiced_val'] != "approved" ) {
		?>
			<br>
			<a id="as-time-tracking-default-entry-end-dates" class="button button-primary" href="#"><?php _e( 'Default start/end dates', 'awesome-support-time-tracking' ); ?></a>
		<?php
		}
		$allow_ticket_level = wpas_get_option( 'time_tracking_ticket_allow_ticket_level', 'no' );
		if( $allow_ticket_level == 'yes' ) {
		?>
			<br>
			<br>
			<input id="as_time_tracking_ticket_level" type="checkbox" name="as_time_tracking_ticket_level" <?php echo ( $field_values['ticket_level_enabled'] == 'on' ? 'checked' : '' ); ?>><span id="as_time_tracking_ticket_level_label"><?php _e( 'Save time at the ticket level (A Ticket Reply Id will not be required if this is turned on.)', 'awesome-support-time-tracking' ); ?></span>
			<br>
			<br>
			<hr />
		<?php
		}
		?>
	</div>

  <?php
  as_time_tracking_output_cpt_form( $field_values, $automatic_values );
}

/**
 * Used as a helper function. Outputs the main cpt form html.
 *
 * @param 	$field_values      The current saved/success validated values for fields
 * @param 	$automatic_values  The total times if automatic timer is set
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_output_cpt_form( $field_values, $automatic_values ) {
  ?>
  <form action="" method="POST">
		<table class="form-table">
	    <tbody>
				<tr>
					<td><strong><?php _e( 'Ticket #:', 'awesome-support-time-tracking' ); ?></strong></td>
					<td>
						<?php
						as_time_tracking_form_output_ticket_field( $field_values );
						?>
					</td>
					<td><span class="as-time-tracking-notes"><?php _e( '*the ticket id must belong to an existing ticket', 'awesome-support-time-tracking' ); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php _e( 'Ticket Reply #:', 'awesome-support-time-tracking' ); ?></strong></td>
					<td>
						<?php
						as_time_tracking_form_output_ticket_reply_field( $field_values );
						?>
					</td>
					<td><span class="as-time-tracking-notes"><?php _e( '*the ticket reply must belong to an existing ticket', 'awesome-support-time-tracking' ); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php _e( 'Start Date:', 'awesome-support-time-tracking' ); ?></strong></td>
					<td><input id="as-time-tracking-start-date" type="text" name="as-time-tracking-start-date" value="<?php echo $field_values['start_date_val']; ?>"></td>
				</tr>
				<tr>
				  <td><strong><?php _e( 'Start Time:', 'awesome-support-time-tracking' ); ?></strong></td>
				  <td>
				    <select id="as-time-tracking-start-time-hours" name="as-time-tracking-start-time-hours">
				    <?php
							as_time_tracking_create_select_options( 24, $field_values['start_date_hours_val'] );
				    ?>
				    </select>
				    <label class="as_time_tracking_date_label" for="as-time-tracking-start-time-hours"><?php _e( 'hour(s)', 'awesome-support-time-tracking' ); ?></label>
				    <select id="as-time-tracking-start-time-minutes" name="as-time-tracking-start-time-minutes">
				    <?php
							as_time_tracking_create_select_options( 60, $field_values['start_date_minutes_val'] );
				    ?>
				    </select>
				    <label class="as_time_tracking_date_label" for="as-time-tracking-start-time-minutes"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
				    <select id="as-time-tracking-start-time-seconds" name="as-time-tracking-start-time-seconds">
				    <?php
							as_time_tracking_create_select_options( 60, $field_values['start_date_seconds_val'] );
				    ?>
				    </select>
				    <label class="as_time_tracking_date_label" for="as-time-tracking-start-time-seconds"><?php _e( 'second(s)', 'awesome-support-time-tracking' ); ?></label>
				  </tr>
					<tr>
					  <td><strong><?php _e( 'End Date:', 'awesome-support-time-tracking' ); ?></strong></td>
					  <td><input id="as-time-tracking-end-date" type="text" name="as-time-tracking-end-date" value="<?php echo $field_values['end_date_val']; ?>"></td>
					</tr>
					<tr>
					  <td><strong><?php _e( 'End Time:', 'awesome-support-time-tracking' ); ?></strong></td>
					  <td>
					    <select id="as-time-tracking-end-time-hours" name="as-time-tracking-end-time-hours">
					    <?php
								as_time_tracking_create_select_options( 24, $field_values['end_date_hours_val'] );
					    ?>
					    </select>
					    <label class="as_time_tracking_date_label" for="as-time-tracking-end-time-hours"><?php _e( 'hour(s)', 'awesome-support-time-tracking' ); ?></label>
					    <select id="as-time-tracking-end-time-minutes" name="as-time-tracking-end-time-minutes">
					    <?php
								as_time_tracking_create_select_options( 60, $field_values['end_date_minutes_val'] );
					    ?>
					    </select>
					    <label class="as_time_tracking_date_label" for="as-time-tracking-end-time-minutes"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
					    <select id="as-time-tracking-end-time-seconds" name="as-time-tracking-end-time-seconds">
					    <?php
								as_time_tracking_create_select_options( 60, $field_values['end_date_seconds_val'] );
					    ?>
					    </select>
					    <label class="as_time_tracking_date_label" for="as-time-tracking-end-time-seconds"><?php _e( 'second(s)', 'awesome-support-time-tracking' ); ?></label>
					  </tr>
						<tr>
							<td><strong><?php _e( 'Total Time Recorded:', 'awesome-support-time-tracking' ); ?></strong></td>
							<td>
								<input id="as-time-tracking-total-time-hours" type="text" name="as-time-tracking-total-time-hours" value="<?php echo $field_values['total_time_hours_val']; ?>">
								<label class="as_time_tracking_date_label" for="as-time-tracking-total-time-hours"><?php _e( 'hour(s)', 'awesome-support-time-tracking' ); ?></label>
								<select id="as-time-tracking-total-time-minutes" name="as-time-tracking-total-time-minutes">
						      <?php
									as_time_tracking_create_select_options( 60, $field_values['total_time_minutes_val'] );
						      ?>
						    </select>
						    <label class="as_time_tracking_date_label" for="as-time-tracking-total-time-minutes"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
							</td>
              <?php
              $timer_type = wpas_get_option( 'time_tracking_timer_type', 'automatic' );
              if( $timer_type === 'automatic' ) {
              ?>
              <td>
                <?php
                if( isset( $automatic_values['hours'] ) && isset( $automatic_values['minutes'] ) ) {
                  echo "<a id='as-time-tracking-automatic-timer-validation' href='#'>" . __( 'Use previously submitted time (this will pause the automatic timer)', 'awesome-support-time-tracking' ) . "</a>";
                  echo "<input id='as-time-tracking-automatic-timer-validation-hours' type='hidden' name='as-time-tracking-automatic-timer-validation-hours' value='" . $automatic_values['hours'] . "'>";
                  echo "<input id='as-time-tracking-automatic-timer-validation-minutes' type='hidden' name='as-time-tracking-automatic-timer-validation-minutes' value='" . $automatic_values['minutes'] . "'>";
                }
                ?>
              </td>
              <?php
              }
              ?>
						</tr>
						<tr>
							<td><strong><?php _e( 'Total Time Adjustments:', 'awesome-support-time-tracking' ); ?></strong></td>
							<td>
								<input id="as-time-tracking-total-time-adjustments" type="text" name="as-time-tracking-total-time-adjustments" value="<?php echo $field_values['total_time_adjustments_val']; ?>">
								<label for="as-time-tracking-total-time-adjustments"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
							</td>
							<td><span class="as-time-tracking-notes"><?php _e( '*Enter a negative number for a time credit.', 'awesome-support-time-tracking' ); ?></span></td>
						</tr>
						<tr>
					    <td><strong><?php _e( 'Notes:', 'awesome-support-time-tracking' ); ?></strong></td>
					    <td class="as-time-tracking-notes-field">
								<?php
								as_time_tracking_form_output_notes_field( $field_values );
								?>
							</td>
					  </tr>
					  <tr>
					    <td><strong><?php _e( 'Agent:', 'awesome-support-time-tracking' ); ?></strong></td>
					    <td>
								<?php
								as_time_tracking_form_output_agents_field( $field_values );
								?>
					    </td>
					    <td><span class="as-time-tracking-notes"><?php _e( '*this field will update automatically when a correct ticket id has been entered', 'awesome-support-time-tracking' ); ?></span></td>
					  </tr>
						<tr>
							<td><strong><?php _e( 'Entry Date:', 'awesome-support-time-tracking' ); ?></strong></td>
							<td><input id="as-time-tracking-entry-date" type="text" name="as-time-tracking-entry-date" value="<?php echo $field_values['entry_date_val']; ?>"></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Entry Time:', 'awesome-support-time-tracking' ); ?></strong></td>
							<td>
								<select id="as-time-tracking-entry-date-hours" name="as-time-tracking-entry-date-hours">
									<?php
									as_time_tracking_create_select_options( 24, $field_values['entry_date_hours_val'] );
									?>
								</select>
								<label class="as_time_tracking_date_label" for="as-time-tracking-entry-date-hours"><?php _e( 'hour(s)', 'awesome-support-time-tracking' ); ?></label>
								<select id="as-time-tracking-entry-date-minutes" name="as-time-tracking-entry-date-minutes">
									<?php
									as_time_tracking_create_select_options( 60, $field_values['entry_date_minutes_val'] );
									?>
								</select>
								<label class="as_time_tracking_date_label" for="as-time-tracking-entry-date-minutes"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
								<select id="as-time-tracking-entry-date-seconds" name="as-time-tracking-entry-date-seconds">
									<?php
									as_time_tracking_create_select_options( 60, $field_values['entry_date_seconds_val'] );
									?>
								</select>
								<label class="as_time_tracking_date_label" for="as-time-tracking-entry-date-seconds"><?php _e( 'second(s)', 'awesome-support-time-tracking' ); ?></label>
							</td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Invoiced:', 'awesome-support-time-tracking' ); ?></strong></td>
							<td>
								<select id="as-time-tracking-invoiced-field" name="as-time-tracking-invoiced-field">
									<option value="" <?php echo ( $field_values['invoiced_val'] == "" ? "selected" : "" ); ?>></option>
									<option value="in process" <?php echo ( $field_values['invoiced_val'] == "in process" ? "selected" : "" ); ?>><?php _e( 'In progress', 'awesome-support-time-tracking' ); ?></option>
									<option value="approved" <?php echo ( $field_values['invoiced_val'] == "approved" ? "selected" : "" ); ?>><?php _e( 'Approved', 'awesome-support-time-tracking' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Invoice Number:', 'awesome-support-time-tracking' ); ?></strong></td>
							<td>
								<input id="as-time-tracking-invoice-number" type="text" name="as-time-tracking-invoice-number" value="<?php echo $field_values['invoice_number_val']; ?>" readonly>
							</td>
							<td><span class="as-time-tracking-notes"><?php _e( '*the invoice number is automatically generated and assigned through the invoicing functionality', 'awesome-support-time-tracking' ); ?></span></td>
						</tr>
		    </tbody>
		</table>
	</form>
  <?php
}

/**
 * Used as a helper function. Outputs ticket field.
 *
 * @param 	$field_values   The current saved/success validated values for fields
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_form_output_ticket_field( $field_values ) {
  ?>
  <input id="as-time-tracking-ticket-id" type="text" name="as-time-tracking-ticket-id" value="<?php echo $field_values['ticket_id_val']; ?>">
  <?php
  if( $field_values['invoiced_val'] != "approved" ) {
  ?>
    <a id="as_time_tracking_ticket_lookup_btn" href="#" class="as-time-tracking-lookup-btn button button-primary"><?php _e( 'Lookup ticket #', 'awesome-support-time-tracking' ); ?></a>
    <div id="as_time_tracking_lookup_ticket_id_content">
      <select id="as_time_tracking_ticket_lookup_search" name="as_time_tracking_ticket_lookup_search"></select>
    </div>
  <?php
  }
}

/**
 * Used as a helper function. Outputs ticket reply field.
 *
 * @param 	$field_values   The current saved/success validated values for fields
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_form_output_ticket_reply_field( $field_values ) {
  ?>
  <input id="as-time-tracking-ticket-reply" type="text" name="as-time-tracking-ticket-reply" value="<?php echo $field_values['ticket_reply_val']; ?>">

  <?php
  if( $field_values['invoiced_val'] != "approved" ) {
  ?>
    <a id="as_time_tracking_ticket_reply_lookup_btn" href="#" class="as-time-tracking-lookup-btn button button-primary"><?php _e( 'Lookup ticket reply #', 'awesome-support-time-tracking' ); ?></a>
    <div id="as_time_tracking_lookup_ticket_reply_id_content">
      <div class="table-result-data"></div>
    </div>
  <?php
  }
}

/**
 * Used as a helper function. Outputs notes field.
 *
 * @param 	$field_values   The current saved/success validated values for fields
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_form_output_notes_field( $field_values ) {
  $editor_id = 'astimetrackingnotes';

  if( $field_values['invoiced_val'] == "approved" ) {
    echo "<textarea id='" . $editor_id . "' readonly>" . $field_values['notes_val'] . "</textarea>";
  } else {
    wp_editor( $field_values['notes_val'], $editor_id );
  }
}

/**
 * Used as a helper function. Outputs agent field.
 *
 * @param 	$field_values   The current saved/success validated values for fields
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_form_output_agents_field( $field_values ) {
  $agents = get_users( array(
                      'fields' => array( 'ID', 'display_name' ),
                      'role__in' => array( 'wpas_agent', 'wpas_support_manager', 'wpas_manager', 'administrator' )
                    ) );

  if( !current_user_can( "edit_other_tracked_time" ) && current_user_can( "edit_own_tracked_time" ) ) {
    $agents_to_remove = as_time_tracking_determine_agents_removed( $agents );
  } elseif( !current_user_can( "edit_own_tracked_time" ) ) {
    $agents = array();
  }
?>

  <select id="as-time-tracking-agent" name="as-time-tracking-agent">
    <option value=""></option>
    <?php
    foreach( $agents as $agent ) {
      echo "<option data-id='" . $agent->ID . "' value='" . $agent->ID . "' " . ( $field_values['agent_val'] == $agent->ID ? 'selected' : '' ) . ">" . $agent->display_name . "</option>";
    }
    ?>
  </select>
  <input type="hidden" id="as-time-tracking-agent-value" name="as-time-tracking-agent-value" value="<?php echo $field_values['agent_val']; ?>">
<?php
}

/**
 * Used as a helper function. Gets the agents to remove for the agents field and returns the data.
 *
 * @param 	$agents   The agent data
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_determine_agents_removed( $agents ) {
  $agents_to_remove = array();

  foreach( $agents as $key => $value ) {
    if( $value->ID != get_current_user_id() ) {
      $agents_to_remove[] = $key;
    }
  }

  foreach( $agents_to_remove as $agent ) {
    unset( $agents[$agent] );
  }

  return $agents_to_remove;
}

/**
 * Used as a helper function. Print select option values, along with selected attribute if required.
 *
 * @param $unit_val				The maximum unit number
 * @param $selected_val		Check if the field is to be selected
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_create_select_options( $unit_val, $selected_val ) {
  for( $i = 0; $i < $unit_val; $i++ ) {
    if( $i < 10 ) {
      echo "<option value='" . sprintf( '%02d', $i ) . "' " . ( $selected_val == $i ? 'selected' : '' ) . ">" . sprintf( '%02d', $i ) . "</option>";
    } else {
      echo "<option value='" . $i . "' " . ( $selected_val == $i ? 'selected' : '' ) . ">" . $i . "</option>";
    }
  }
}

/**
 * Used as a helper function. Sanaitize saved fields exluding final time.
 *
 * @param 	array		$post		The posted values
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_sanitize_save_post_fields( $post ) {
  $fields = array();

  if( isset( $post['as_time_tracking_ticket_level'] ) ) {
    $fields['as_time_tracking_ticket_level'] = sanitize_text_field( $post['as_time_tracking_ticket_level'] );
  } else {
    $fields['as_time_tracking_ticket_level'] = '';
  }

	$fields['ticket_id'] = sanitize_text_field( $post['as-time-tracking-ticket-id'] );
	$fields['ticket_reply_id'] = sanitize_text_field( $post['as-time-tracking-ticket-reply'] );
	$fields['entry_date'] = sanitize_text_field( $post['as-time-tracking-entry-date'] );
	$fields['entry_date_hours'] = sanitize_text_field( $post['as-time-tracking-entry-date-hours'] );
	$fields['entry_date_minutes'] = sanitize_text_field( $post['as-time-tracking-entry-date-minutes'] );
	$fields['entry_date_seconds'] = sanitize_text_field( $post['as-time-tracking-entry-date-seconds'] );
	$fields['start_date'] = sanitize_text_field( $post['as-time-tracking-start-date'] );
	$fields['start_date_hours'] = sanitize_text_field( $post['as-time-tracking-start-time-hours'] );
	$fields['start_date_minutes'] = sanitize_text_field( $post['as-time-tracking-start-time-minutes'] );
	$fields['start_date_seconds'] = sanitize_text_field( $post['as-time-tracking-start-time-seconds'] );
	$fields['start_date_time_str'] = strtotime( $fields['start_date'] . " " . $fields['start_date_hours'] . ":" . $fields['start_date_minutes'] . ":" . $fields['start_date_seconds'] );
	$fields['end_date'] = sanitize_text_field( $post['as-time-tracking-end-date'] );
	$fields['end_date_hours'] = sanitize_text_field( $post['as-time-tracking-end-time-hours'] );
	$fields['end_date_minutes'] = sanitize_text_field( $post['as-time-tracking-end-time-minutes'] );
	$fields['end_date_seconds'] = sanitize_text_field( $post['as-time-tracking-end-time-seconds'] );
	$fields['end_date_time_str'] = strtotime( $fields['end_date'] . " " . $fields['end_date_hours'] . ":" . $fields['end_date_minutes'] . ":" . $fields['end_date_seconds'] );
	$fields['invoiced'] = sanitize_text_field( $post['as-time-tracking-invoiced-field'] );
	$fields['sucess_total_hours'] = sanitize_text_field( $post['as-time-tracking-total-time-hours'] );
	$fields['total_time_adjustments'] = sanitize_text_field( $post['as-time-tracking-total-time-adjustments'] );
	( $fields['total_time_adjustments'] == '' ? $fields['total_time_adjustments'] = 0: $fields['total_time_adjustments'] = $fields['total_time_adjustments'] );
	$fields['notes'] = sanitize_text_field( $post['astimetrackingnotes'] );
	$fields['agent'] = sanitize_text_field( $post['as-time-tracking-agent-value'] );

	return $fields;
}

/**
 * Used as a helper function. Sanaitize saved time fields and do time calculation logic, then returns the data.
 *
 * @param 	array		$post		The posted values
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_sanitize_save_post_time_fields( $post ) {
  $fields = array();

	( is_numeric( $post['as-time-tracking-total-time-hours'] ) ? $fields['total_time_hours'] = sanitize_text_field( $post['as-time-tracking-total-time-hours'] ) : $fields['total_time_hours'] = '' );
	( $fields['total_time_hours'] == '' ? $fields['total_time_hours'] = 0 : $fields['total_time_hours'] = $fields['total_time_hours'] );
	( $fields['total_time_hours'] !== 0 ? $fields['total_time_hours'] = $fields['total_time_hours'] * 60 : $fields['total_time_hours'] = $fields['total_time_hours'] );
	( is_numeric( $post['as-time-tracking-total-time-minutes'] ) ? $fields['total_time_minutes'] = sanitize_text_field( $post['as-time-tracking-total-time-minutes'] ) : $fields['total_time_minutes'] = '' );
	( $fields['total_time_minutes'] == '' ? $fields['total_time_minutes'] = 0 : $fields['total_time_minutes'] = $fields['total_time_minutes'] );
	$fields['final_total_time'] = $fields['total_time_hours'] + $fields['total_time_minutes'];

	return $fields;
}

/**
 * Used as a helper function. Validates each posted field.
 *
 * @param 	array			$post												The posted values
 * @param 	array			$posted_final_time_fields		The posted time values
 * @param 	integer		$post_id										The post id
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_validate_saved_fields( $posted_fields, $posted_final_time_fields, $post_id ) {
	$validator = new AS_Time_Tracking_Validator();
  //On ticket level
  $validator->check_ticket_level( $posted_fields['as_time_tracking_ticket_level'], 'ticket_level_enabled' );

  //Ticket id
	$validator->check_empty( $posted_fields['ticket_id'], 'ticket id', 'ticket_id_empty' );
	$validator->check_numeric( $posted_fields['ticket_id'], 'ticket id', 'ticket_id_numeric' );
	$validator->ticket_exists( $posted_fields['ticket_id'], 'ticket_id_exists' );

	//Ticket reply id
  //If multiple entries for ticket replies are allowed then we don't do the following check
  $allow_multiple_entries = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );

  if( $allow_multiple_entries == 'no' && $posted_fields['as_time_tracking_ticket_level'] == '' ) {
    $validator->check_ticket_reply( $posted_fields['ticket_reply_id'], $posted_fields['ticket_id'], 'ticket_reply_id_matches_parent' );
    $validator->check_empty( $posted_fields['ticket_reply_id'], 'ticket reply', 'ticket_reply_id_empty' );
    $validator->check_numeric( $posted_fields['ticket_reply_id'], 'ticket reply id', 'ticket_reply_id_numeric' );
    $validator->check_existing_tracked_time( $posted_fields['ticket_id'], $posted_fields['ticket_reply_id'], 'ticket_ticket_reply_exists', $post_id );
  } elseif( $allow_multiple_entries == 'no' && $posted_fields['as_time_tracking_ticket_level'] == 'on' ) {
    $validator->check_multiple_disallowed_ticket_level_allowed( $posted_fields['ticket_reply_id'], 'multiple_disallowed_ticket_allowed' );
  } elseif( $allow_multiple_entries == 'yes' && $posted_fields['as_time_tracking_ticket_level'] == '' ) {
    $validator->check_numeric( $posted_fields['ticket_reply_id'], 'ticket reply id', 'ticket_reply_id_numeric' );
  }

	//Entry date
	$validator->check_empty( $posted_fields['entry_date'], 'entry date', 'entry_date_empty' );
	$validator->check_date_format( $posted_fields['entry_date'], 'entry date', 'entry_date_format' );

	//Entry date hours
	$validator->check_numeric( $posted_fields['entry_date_hours'], 'entry date hour(s)', 'entry_date_hours_numeric' );
	$validator->check_empty( $posted_fields['entry_date_hours'], 'entry date hour(s)', 'entry_date_hours_empty' );

	//Entry date minutes
	$validator->check_numeric( $posted_fields['entry_date_minutes'], 'entry date minute(s)', 'entry_date_minutes_numeric' );
	$validator->check_empty( $posted_fields['entry_date_minutes'], 'entry date minute(s)', 'entry_date_minutes_empty' );

	//Entry date seconds
	$validator->check_numeric( $posted_fields['entry_date_seconds'], 'entry date second(s)', 'entry_date_seconds_numeric' );
	$validator->check_empty( $posted_fields['entry_date_seconds'], 'entry date second(s)', 'entry_date_seconds_empty' );

	//Start date
	$validator->check_empty( $posted_fields['start_date'], 'start date', 'start_date_empty' );
	$validator->check_date_format( $posted_fields['start_date'], 'start date', 'start_date_format' );

	//Start date hours
	$validator->check_numeric( $posted_fields['start_date_hours'], 'start date hour(s)', 'start_date_hours_numeric' );
	$validator->check_empty( $posted_fields['start_date_hours'], 'start date hour(s)', 'start_date_hours_empty' );

	//Start date minutes
	$validator->check_numeric( $posted_fields['start_date_minutes'], 'start date minute(s)', 'start_date_minutes_numeric' );
	$validator->check_empty( $posted_fields['start_date_minutes'], 'start date minute(s)', 'start_date_minutes_empty' );

	//Start date seconds
	$validator->check_numeric( $posted_fields['start_date_seconds'], 'start date second(s)', 'start_date_seconds_numeric' );
	$validator->check_empty( $posted_fields['start_date_seconds'], 'start date second(s)', 'start_date_seconds_empty' );

	//End date
	$validator->check_empty( $posted_fields['end_date'], 'end date', 'end_date_empty' );
	$validator->check_date_format( $posted_fields['end_date'], 'end date', 'end_date_format' );

	//End date hours
	$validator->check_numeric( $posted_fields['end_date_hours'], 'end date hour(s)', 'end_date_hours_numeric' );
	$validator->check_empty( $posted_fields['end_date_hours'], 'end date hour(s)', 'end_date_hours_empty' );

	//End date minutes
	$validator->check_numeric( $posted_fields['end_date_minutes'], 'end date minute(s)', 'end_date_minutes_numeric' );
	$validator->check_empty( $posted_fields['end_date_minutes'], 'end date minute(s)', 'end_date_minutes_empty' );

	//End date seconds
	$validator->check_numeric( $posted_fields['end_date_seconds'], 'end date second(s)', 'end_date_seconds_numeric' );
	$validator->check_empty( $posted_fields['end_date_seconds'], 'end date second(s)', 'end_date_seconds_empty' );

	//Although the total time value has been calculated above we will do some validation for error messages
	$validator->check_empty( $_POST['as-time-tracking-total-time-minutes'], 'total time minute(s)', 'total_time_minutes_empty' );
	$validator->check_numeric( $_POST['as-time-tracking-total-time-minutes'], 'total time minute(s)', 'total_time_minutes_numeric' );

	//Total time adjustments
	$validator->check_numeric_time_adjustments( $posted_fields['total_time_adjustments'], 'total time adjustments', 'total_time_adjustments_numeric' );

	//Agent
	$validator->check_numeric( $posted_fields['agent'], 'agent', 'agent_id_numeric' );
	$validator->check_empty( $posted_fields['agent'], 'agent', 'agent_empty' );

	//Invoiced
	$validator->check_invoiced_values( $posted_fields['invoiced'], 'invoiced field', 'invoiced_values' );

	//Check that the end date is not before the start date
	$validator->check_start_end_date( $posted_fields['start_date_time_str'], $posted_fields['end_date_time_str'], 'end_before_start' );

	/** Check if the ticket # and ticket reply # has been saved to an existing tracked time.
	 * As the post has been created we check dates in the database to determine status. */
	$current_post = get_post( $post_id );

	$validator->check_final_status();

	//Get values which passed validation
	$passed_values = $validator->get_validation_success_values();

	if( !empty( $passed_values ) ) {
		WPAS()->session->add( 'as_time_tracking_validation_passed_values', $passed_values );
	}

	if( !empty( $passed_values ) ) {
			$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );
			$wp_session['notes_not_empty'] = $posted_fields['notes'];
			WPAS()->session->add( 'as_time_tracking_validation_passed_values', $wp_session );
	}

	if( !empty( $passed_values ) ) {
		$wp_session = WPAS()->session->get( 'as_time_tracking_validation_passed_values' );
		$wp_session['total_time_hours_not_empty'] = $posted_fields['sucess_total_hours'];
		WPAS()->session->add( 'as_time_tracking_validation_passed_values', $wp_session );
	}

	$session_vals = $validator->get_validator();
	WPAS()->session->add( 'as_time_tracking_validation', $session_vals );
	$errors_exist = $validator->validation_errors_exist();

	return $errors_exist;
}

/**
 * Used as a helper function. Change post status if errors exist during validation.
 *
 * @param 	integer		$post_id		The id of the post
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_save_post_change_status( $post_id ) {
	$post_exists = get_post_meta( $post_id, 'as_time_tracking_entry' );

	if( count( $post_exists ) === 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "posts";
		$wpdb->update( $table_name, array( 'post_status' => 'draft', 'post_title' => 'Auto Draft' ), array( 'ID' => get_the_ID() ) );
	}
}

/**
 * Used as a helper function. Determines invoice number if it exists on saved data and returns it.
 *
 * @param 	integer		$post_id		The id of the post
 *
 * @since 	0.1.0
 * @return 	integer
 */
function as_time_tracking_save_post_determine_invoice_number( $post_id ) {
	$saved_data = get_post_meta( $post_id, 'as_time_tracking_entry' );
	if( !empty( $saved_data[0] ) ) {
		$invoice_number = ( $saved_data[0]['invoice_number'] != "" ? $saved_data[0]['invoice_number'] : "" );
	} else {
		$invoice_number = "";
	}

	return $invoice_number;
}

/**
 * Save post information when submitted. Also happens when creating new entry so empty check on post is done.
 *
 * @since 	0.1.0
 * @return 	void
 */
function save_as_time_tracking_fields( $post_id ) {
	//Autosave, do nothing. Or if revision, do nothing.
	if( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( false !== wp_is_post_revision( $post_id ) ) ) {
	  return;
	}

	if( WPAS()->session->get( 'as_time_tracking_validation_passed_values' ) == false ) {
		$wp_session = WPAS()->session->add( 'as_time_tracking_validation_passed_values', array() );
	}

	/** Check post for error messages.
	 *	We also check the screen as wp_insert_post fires this action when we save the ticket reply which we don't want. */

   //Count the post fields. When saving on the ticket level from the ticket screen somehow the js values passed to ajax
   //End up getting here so we check, otherwise errors will show.
   if(
     array_key_exists( "timer_type", $_POST ) &&
     array_key_exists( "action", $_POST ) &&
     array_key_exists( "ticket_id", $_POST ) &&
     array_key_exists( "start_date_time", $_POST ) &&
     array_key_exists( "end_date_time", $_POST ) &&
     array_key_exists( "description", $_POST ) &&
     array_key_exists( "individual_hours", $_POST ) &&
     array_key_exists( "individual_minutes", $_POST ) &&
     array_key_exists( "individual_seconds", $_POST )
   ) {
    $ticket_level_save = true;
  } else {
    $ticket_level_save = false;
  }

	global $current_screen;

	//When saving through ajax on the ticket screen from the overlay this is empty so we will just set it to 'ticket'
	if( empty( $current_screen ) ) {
		$current_screen = new stdClass();
		$current_screen->id = 'ticket';
	}

   if(
     !empty( $_POST ) &&
     $current_screen->id != 'ticket' &&
     $ticket_level_save == false
   ) {
		//Sanitize fields before validating
    $posted_fields = as_time_tracking_sanitize_save_post_fields( $_POST );
		//Calculate the overall time logic here, but check the validation errors later
    $posted_final_time_fields = as_time_tracking_sanitize_save_post_time_fields( $_POST );
		//Validate each field
    $errors_exist = as_time_tracking_validate_saved_fields( $posted_fields, $posted_final_time_fields, $post_id );

		if( $errors_exist === false ) {
			$final_entry_date_time = $posted_fields['entry_date'] . " " . $posted_fields['entry_date_hours'] . ":" . $posted_fields['entry_date_minutes'] . ":" . $posted_fields['entry_date_seconds'];
			$final_start_date_time = $posted_fields['start_date'] . " " . $posted_fields['start_date_hours'] . ":" . $posted_fields['start_date_minutes'] . ":" . $posted_fields['start_date_seconds'];
			$final_end_date_time = $posted_fields['end_date'] . " " . $posted_fields['end_date_hours'] . ":" . $posted_fields['end_date_minutes'] . ":" . $posted_fields['end_date_seconds'];

			//As the invoice number can't be changed in this screen we just get the old value and resave it
      //We also check capabilities to see if the user can make an edit
      if( (get_current_user_id() == $posted_fields['agent'] && current_user_can( 'edit_own_tracked_time' )) || (get_current_user_id() != $posted_fields['agent'] && current_user_can( 'edit_other_tracked_time' )) ) {
        $invoice_number = as_time_tracking_save_post_determine_invoice_number( get_the_ID() );
        $save_fields_booleans = as_time_tracking_determine_boolean_save_values( $posted_fields );

  			$data_to_save = array(
  		    'ticket_id' => $posted_fields['ticket_id'],
  				'ticket_reply' => $posted_fields['ticket_reply_id'],
  				'entry_date_time' => $final_entry_date_time,
  				'start_date_time' => $final_start_date_time,
  				'end_date_time' => $final_end_date_time,
  				'notes' => $posted_fields['notes'],
  				'agent' => $posted_fields['agent'],
  				'invoiced' => $posted_fields['invoiced'],
  				'invoice_number' => $invoice_number,
          'individual_time' => $posted_final_time_fields['final_total_time'],
          'adjusted_time' => $posted_fields['total_time_adjustments'],
          'is_ticket_reply_multiple' => $save_fields_booleans['multiple'],
          'is_ticket_reply' => $save_fields_booleans['reply'],
          'is_ticket_level' => $save_fields_booleans['ticket']
  			);

  			//update_post_meta( $posted_fields['ticket_reply_id'], '_wpas_ttl_adjustments_to_time_spent_on_ticket', $posted_fields['total_time_adjustments'] );
  			//update_post_meta( $posted_fields['ticket_reply_id'], '_wpas_individual_reply_ticket_time', $posted_final_time_fields['final_total_time'] );
  			update_post_meta( $post_id, 'as_time_tracking_entry', $data_to_save );

        $tracked_time_data = array();
        global $wpdb;
        $db_query = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id, " . $wpdb->prefix . "postmeta.meta_value FROM " . $wpdb->prefix . "postmeta INNER JOIN " . $wpdb->prefix . "posts ON " . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID AND " . $wpdb->prefix . "posts.post_type = 'trackedtimes' AND " . $wpdb->prefix . "postmeta.meta_key = 'as_time_tracking_entry'";
        $db_result = $wpdb->get_results( $db_query, OBJECT );

        $tracked_time_data = array();

        foreach( $db_result as $result ) {
          $serialized_data = array(
              'id' => $result->post_id,
              'serialized_data' => maybe_unserialize( $result->meta_value )
            );
          $tracked_time_data[] = $serialized_data;
        }

        $ticket_time_data = as_time_tracking_get_ticket_time_data( $tracked_time_data, $posted_fields['ticket_id'] );
    		$time_adjustments = as_time_tracking_adjusted_time( $ticket_time_data );
        $calculated_time = as_time_tracking_calculated_time( $ticket_time_data );
  			update_post_meta( $posted_fields['ticket_id'], '_wpas_ttl_adjustments_to_time_spent_on_ticket', $time_adjustments );
        update_post_meta( $posted_fields['ticket_id'], '_wpas_ttl_calculated_time_spent_on_ticket', $calculated_time );
        $final_time = $time_adjustments + $calculated_time;
        update_post_meta( $posted_fields['ticket_id'], '_wpas_final_time_spent_on_ticket', $final_time );
      }
		} else {
			/** By the time this hook launches WordPress will have saved the post already, so
			 *  we change the status to draft instead of published. But first we check if
			 *  the post already exists, otherwise we don't need to set it to a draft status
			 *  and change the post title to "Auto Draft" */
       as_time_tracking_save_post_change_status( $post_id );
		}
	}
}

add_action( 'save_post_trackedtimes', 'save_as_time_tracking_fields' );

/**
 * On save_post, determines the boolean valies for multiple reply, is reply, on ticket level.
 *
 * @since 	0.1.0
 * @return 	Array
 */
function as_time_tracking_determine_boolean_save_values( $posted_fields ) {
  $return_arr = array();
  $allow_multiple_entries = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );

  if( $allow_multiple_entries === 'yes' ) {
    $return_arr['multiple'] = true;
  } else {
    $return_arr['multiple'] = false;
  }

  if( $posted_fields['ticket_reply_id'] == '' ) {
    $return_arr['reply'] = false;
  } else {
    $return_arr['reply'] = true;
  }

  if( $posted_fields['as_time_tracking_ticket_level'] == 'on' ) {
    $return_arr['ticket'] = true;
  } else {
    $return_arr['ticket'] = false;
  }

  return $return_arr;
}

/**
 * Display error or success message after updating the post, based on the validation results stored in the session.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_validation() {
	global $current_screen;
  $wp_session_val = WPAS()->session->get( 'as_time_tracking_validation' );

	if( $current_screen->id == 'trackedtimes' && $wp_session_val != false ) {
		echo "<div class='" . $wp_session_val['notice_class'] . "'>";

		if( !empty( $wp_session_val['success'] ) ) {
			echo "<p>" . $wp_session_val['success'] . "</p>";
		} else {
			foreach( $wp_session_val['errors'] as $error_message ) {
				echo "<p>" . $error_message . "</p>";
			}
		}

		echo "</div>";

		WPAS()->session->clean( 'as_time_tracking_validation' );
	}

	$wp_session_del = WPAS()->session->get( 'as_time_tracking_ticket_delete' );
	$wp_session_del_ids = WPAS()->session->get( 'as_time_tracking_ticket_delete_ids' );

	if( $current_screen->id == 'edit-ticket' && $wp_session_del != false ) {
		foreach( $wp_session_del_ids as $id ) {
			$wp_session_del .= " " . $id;
		}
		$wp_session_del .= "</p>";

		echo "<div class='notice notice-error'>";
		echo $wp_session_del;
		echo "</div>";

		WPAS()->session->clean( 'as_time_tracking_ticket_delete' );
		$wp_session_del_ids = array();
		WPAS()->session->add( 'as_time_tracking_ticket_delete_ids', array() );
	}
}

add_action( 'admin_notices', 'as_time_tracking_validation' );

/**
 * Deletes the core field values when permanently deleting a tracked time.
 * Also updates the final time values for the ticket after deleting the extra post meta.
 * There is a first check for delete capabilities.
 *
 * @param 	integer $post_id	The post id
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_delete_extra_meta( $post_id ) {
  $meta_info = get_post_meta( $post_id, "as_time_tracking_entry" );

  if( isset( $meta_info[0] ) ) {
    if( !current_user_can( 'delete_own_tracked_time' ) && $meta_info[0]['agent'] == get_current_user_id() ) {
      wp_redirect( admin_url( 'edit.php?post_type=trackedtimes' ) );
      exit();
    } elseif( !current_user_can( 'delete_other_tracked_time' ) && $meta_info[0]['agent'] != get_current_user_id() ) {
      wp_redirect( admin_url( 'edit.php?post_type=trackedtimes' ) );
      exit();
    } elseif(
        current_user_can( 'delete_own_tracked_time' ) && $meta_info[0]['agent'] == get_current_user_id() ||
        current_user_can( 'delete_other_tracked_time' ) && $meta_info[0]['agent'] != get_current_user_id()
    ) {
      // We check if the global post type isn't ours and just return
      global $post_type;

    	if ( $post_type == 'trackedtimes' ) {
    		$post_meta = get_post_meta( $post_id, 'as_time_tracking_entry' );
    		if( !empty( $post_meta ) ) {
    			$ticket_id = $post_meta[0]['ticket_id'];
    			$ticket_reply = $post_meta[0]['ticket_reply'];

    			//Delete core plugin entries
    			delete_post_meta( $ticket_reply, '_wpas_ttl_adjustments_to_time_spent_on_ticket' );
    			delete_post_meta( $ticket_reply, '_wpas_individual_reply_ticket_time' );

    			//Delete tracked time entries and recalculate time values
    			global $wpdb;
    			$query = new WP_Query( array(
    															'post_parent' => $ticket_id,
    															'post_type' => 'ticket_reply',
    															'post_status' => 'any',
																	'posts_per_page' => -1
    														));

    			$reply_ids = wp_list_pluck( $query->posts, 'ID' );
          $reply_postmeta_entries = as_time_tracking_convert_reply_ids_to_postmeta( $reply_ids );
					$calculated_time = as_time_tracking_calculated_time( $reply_postmeta_entries );
	    		$time_adjustments = as_time_tracking_adjusted_time( $reply_postmeta_entries );

    			update_post_meta( $ticket_id, '_wpas_ttl_adjustments_to_time_spent_on_ticket', $time_adjustments );
    			update_post_meta( $ticket_id, '_wpas_ttl_calculated_time_spent_on_ticket', $calculated_time );
    			$final_time = $time_adjustments + $calculated_time;
    			update_post_meta( $ticket_id, '_wpas_final_time_spent_on_ticket', $final_time );
    		}
    	}
    }
  }

  //If post is a ticket, check action when ticket deleted and if it is delete, then delete all tracked times
  $ticket_delete_option = wpas_get_option( 'time_tracking_ticket_delete_action', 'nothing' );
  $post_type = get_post_type( $post_id );

  if( $post_type == 'ticket' ) {
    if( $ticket_delete_option == "delete" ) {
      global $wpdb;
      $query = new WP_Query( array(
                              'post_type' => 'trackedtimes',
                              'post_status' => 'any',
															'posts_per_page' => -1
                            ) );

			$posts_to_delete = process_query_posts( $query, $post_id );

      if( gettype( $posts_to_delete ) == "array" ) {
        foreach( $posts_to_delete as $post ) {
          wp_delete_post( $post, true );
        }
      }
    }
  }
}

add_action( 'before_delete_post', 'as_time_tracking_delete_extra_meta' );

/**
 * Helper function to get format for as_time_tracking_calculated_time function when
 * post is deleted.
 *
 * @param		array   $reply_ids      Tracked time entries
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_convert_reply_ids_to_postmeta( $reply_ids ) {
  $reply_post_meta = array();

  foreach( $reply_ids as $reply_id ) {
    global $wpdb;
    $db_query = "SELECT meta_value FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'as_time_tracking_entry'";
  	$db_result = $wpdb->get_results( $db_query, OBJECT );

    foreach( $db_result as $key => $result ) {
      $meta_data = maybe_unserialize( $result->meta_value );
      if( $meta_data['ticket_reply'] == $reply_id ) {
        $reply_post_meta[] = $meta_data;
      }
    }
  }
  return $reply_post_meta;
}

/**
 * Helper function when ticket is deleted. Sets the tracked times to be deleted.
 *
 * @param		object   $query      Tracked time entries
 * @param		integer  $post_id    ID of the deleted ticket
 *
 * @since   0.1.0
 * @return  array
 */
function process_query_posts( $query, $post_id ) {
  $posts_to_delete = array();

  foreach( $query->posts as $posts ) {
    $meta_info = get_post_meta( $posts->ID, "as_time_tracking_entry" );
    if( !empty( $meta_info ) ) {
        if( $meta_info[0]['ticket_id'] == $post_id ) {
          $posts_to_delete[] = $posts->ID;
        }
    }
  }

    return $posts_to_delete;
}

/**
 * Helper function to sum the calculated time.
 *
 * @param 	array $reply_ids	The IDs of ticket replies
 *
 * @since 	0.1.0
 * @return 	string
 */
function as_time_tracking_calculated_time ( $ticket_time_data ) {
  $calculated_time = 0;

  foreach( $ticket_time_data as $data ) {
    $individual_time = $data['individual_time'];
    ( $individual_time != '' ?  $calculated_time += $individual_time : '' );
  }

  return $calculated_time;
}

/**
 * Helper function to sum the adjusted time.
 *
 * @param 	array $reply_ids	The IDs of ticket replies
 *
 * @since 	0.1.0
 * @return 	string
 */
function as_time_tracking_adjusted_time ( $ticket_time_data ) {
  $time_adjustments = 0;

  foreach( $ticket_time_data as $data ) {
    $adjusted_time = $data['adjusted_time'];

    if( $adjusted_time < 0 ) {
      $time_adjustments -= substr( $adjusted_time, 1 );
    } else {
      $time_adjustments += $adjusted_time;
    }
  }

  return $time_adjustments;
}

/**
 * Checks when a ticket is deleted if it should be prevented from being deleted based on the setting value.
 * Uses this hook so the plugin can make use of admin notices and change the post status back if needed.
 *
 * @param 	string $new_status	The new status of the post
 * @param 	string $old_status	The previoud status of the post
 * @param 	object $post				The post entries
 *
 * @since 	0.1.0
 * @return 	void
 */
function on_all_status_transitions( $new_status, $old_status, $post ) {
  $post_type = get_post_type( $post );

  if(
    $post_type === 'ticket' &&
    $new_status != $old_status &&
    $new_status === 'trash'
  ) {
    $ticket_delete_option = wpas_get_option( 'time_tracking_ticket_delete_action', 'nothing' );
    $time_tracking_entries_exist = false;

    if( $post_type == 'ticket' && $ticket_delete_option == 'prevent' ) {
      global $wpdb;
      $query = new WP_Query( array(
                              'post_type' => 'trackedtimes',
                              'post_status' => 'any',
															'posts_per_page' => -1
                            ) );

      foreach( $query->posts as $posts ) {
        $meta_info = get_post_meta( $posts->ID, "as_time_tracking_entry" );
        if( !empty( $meta_info ) ) {
            if( $meta_info[0]['ticket_id'] == $post->ID ) {
              $time_tracking_entries_exist = true;
              break;
            }
        }
      }

      if( $time_tracking_entries_exist === true ) {
        $wp_session_del = WPAS()->session->get( 'as_time_tracking_ticket_delete' );
				$wp_session_del_ids = WPAS()->session->get( 'as_time_tracking_ticket_delete_ids' );

        wp_update_post( array(
            'ID'    =>  $post->ID,
            'post_status'   =>  $old_status
            ));
        $wp_session_del_ids[] = "#" . $post->ID;
        $wp_session_del = "<p id='as_time_tracking_admin_notice_ticket'>" . __( 'Due to the "Action when ticket deleted" setting the following ticket(s) were unable to be binned:', 'awesome-support-time-tracking' );

				WPAS()->session->add( 'as_time_tracking_ticket_delete', $wp_session_del );
				WPAS()->session->add( 'as_time_tracking_ticket_delete_ids', $wp_session_del_ids );
      }
    }
  }
}

add_action(  'transition_post_status', 'on_all_status_transitions', 10, 3 );

/**
 * Adds custom columns to the tracked times post type when viewing all entries.
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_custom_columns_edit() {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Title', 'awesome-support-time-tracking' ),
		'ticket_title' =>__( 'Ticket Title', 'awesome-support-time-tracking' ),
		'ticket_id' => __( 'Ticket #', 'awesome-support-time-tracking' ),
		'ticket_status' =>__( 'Current Ticket Status', 'awesome-support-time-tracking' ),
		'entry_date' => __( 'Entry Date', 'awesome-support-time-tracking' ),
		'total_time' => __( 'Total Time', 'awesome-support-time-tracking' ),
		'agent' => __( 'Agent', 'awesome-support-time-tracking' ),
		'client_customer' => __( 'Client/Customer', 'awesome-support-time-tracking' ),
		'invoiced' => __( 'Invoice Status', 'awesome-support-time-tracking' ),
		'invoice_number' => __( 'Invoice Number', 'awesome-support-time-tracking' )
	);

	return $columns;
}

add_filter( 'manage_edit-trackedtimes_columns', 'as_time_tracking_custom_columns_edit' ) ;

/**
 * Add extra column values to edit view.
 *
 * @param		integer $column   The column id
 * @param		integer $post_id  The post id
 *
 * @since   0.1.0
 * @return 	void
 */
function as_time_tracking_custom_columns_content( $column, $post_id ) {
	$meta_info = get_post_meta( $post_id, 'as_time_tracking_entry' );

	if( !empty( $meta_info ) ) {
		$ticket_obj = get_post( $meta_info[0]['ticket_id'] );
    ( isset( $ticket_obj->post_author ) ? $client = get_userdata( $ticket_obj->post_author ) : $client = "" );

		switch( $column ) {
			case 'ticket_id':
			echo $meta_info[0]['ticket_id'];
			break;

			case 'ticket_title':
			echo get_the_title( $meta_info[0]['ticket_id'] );
			break;

			case 'ticket_status':
			process_ticket_status_column( $meta_info[0]['ticket_id'] );
			break;

			case 'entry_date':
			$date_time = new DateTime( $meta_info[0]['entry_date_time'] );
			$date = $date_time->format( 'Y-m-d' );
			echo $date;
			break;

			case 'total_time':
			echo $meta_info[0]['individual_time'] . " minute(s)";
			break;

			case 'agent':
			$agent_info = get_userdata( $meta_info[0]['agent'] );
      $agent_profile = get_edit_user_link( $agent_info->ID );
      echo "<a href='$agent_profile'>";
			echo get_avatar( $agent_info->ID, 50 );
			echo "<span class='as-time-tracking-column-avatar'>" . $agent_info->display_name . "</span>";
      echo "</a>";
			break;

			case 'client_customer':
			process_client_customer_column( $client );
			break;

			case 'invoiced':
			process_invoice_column($meta_info);
			break;

			case 'invoice_number':
			process_invoice_number_column($meta_info);
			break;

			default :
			break;
		}
	}
}

add_action( 'manage_trackedtimes_posts_custom_column', 'as_time_tracking_custom_columns_content', 10, 2 );

/**
 * Helper function to display ticket status from core plugin functionality.
 *
 * @param		integer $ticket_id   The ticket id
 *
 * @since   0.1.0
 * @return 	void
 */
function process_ticket_status_column( $ticket_id ) {
  $post_status = get_post_status( $ticket_id );

  if( $post_status !== false ) {
    echo "<div class='as_time_tracking_column_status'>";
    wpas_cf_display_status( '', $ticket_id );
    echo "</div>";
  }
}

/**
 * Helper function to display client information in the column area of edit page.
 *
 * @param		object $client   The client information
 *
 * @since   0.1.0
 * @return 	void
 */
function process_client_customer_column( $client ) {
  if( isset( $client->data->ID ) ) {
    $client_profile = get_edit_user_link( $client->data->ID );
    echo "<a href='$client_profile'>";
    echo get_avatar( $client->data->ID, 50 );
    echo "<span class='as-time-tracking-column-avatar'>" . $client->data->display_name . "</span>";
    echo "</a>";
  }
}

/**
 * Helper function to display invoiced status column value.
 *
 * @param		object $meta_info   Meta information taken from postmeta.
 *
 * @since   0.1.0
 * @return 	void
 */
function process_invoice_column( $meta_info ) {
  if( !empty( $meta_info ) ) {
    if( $meta_info[0]['invoiced'] == "in process" ) {
      echo ucwords( $meta_info[0]['invoiced'] );
    } elseif( $meta_info[0]['invoiced'] == "approved" ) {
      echo ucfirst( $meta_info[0]['invoiced'] );
    } else {
      echo $meta_info[0]['invoiced'];
    }
  }
}

/**
 * Helper function to display invoice number column value.
 *
 * @param		object $meta_info   Meta information taken from postmeta.
 *
 * @since   0.1.0
 * @return 	void
 */
function process_invoice_number_column( $meta_info ) {
  if( !empty( $meta_info ) ) {
    echo $meta_info[0]['invoice_number'];
  }
}

/**
 * Third level submenus don't work so we create a custom effect by manipulating the original
 * submenu array and adding styling classes.
 *
 * @param   array $menu_order   The current menu order
 *
 * @since   0.1.0
 * @return  array
 */
  function as_time_tracking_ticket_menu_order( $menu_order ) {
    if( current_user_can( 'add_own_tracked_time' ) ) {
      $tickets_link = "edit.php?post_type=ticket";
      $time_link = "edit.php?post_type=trackedtimes";

      //Get keys from current order, make a copy and remove the time tracking from original order
      $tickets_key = array_search( $tickets_link, $menu_order );
      $time_key = array_search( $time_link, $menu_order );
      $time_menu_ord = $menu_order[$time_key];

      unset( $menu_order[$time_key] );

      //Reorder menu order for easier insertion and then insert the time tracking menu node
      $menu_order = array_values($menu_order);
      array_splice( $menu_order, $tickets_key + 1, 0, $time_menu_ord );
    }

    return $menu_order;
  }

  add_filter( 'custom_menu_order', '__return_true' );
  add_filter( 'menu_order', 'as_time_tracking_ticket_menu_order' );

/**
 * Used as a helper function. Outputs html for post status field.
 *
 * @param   array 	$get	The get values
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_filter_html_post_status( $get ) {
	if ( !isset( $get[ 'time_post_status' ] )
		|| isset( $get[ 'time_post_status' ] ) && 'trash' !== $get[ 'time_post_status' ]
	) {
		$this_sort = isset( $get[ 'time_post_status' ] ) ? filter_input( INPUT_GET, 'time_post_status', FILTER_SANITIZE_STRING ) : 'any';
		$all_selected = ( 'any' === $this_sort ) ? 'selected="selected"' : '';

		$dropdown = '<select id="time_post_status" name="time_post_status" >';
		$dropdown .= "<option value='any' $all_selected>" . __( 'All Status', 'awesome-support-time-tracking' ) . "</option>";

		$custom_statuses = wpas_get_post_status();

		foreach ( $custom_statuses as $_status_id => $_status_value ) {
			$custom_status_selected = ( isset( $_GET[ 'time_post_status' ] ) && $_status_id === $this_sort ) ? 'selected="selected"' : '';
			$dropdown .= "<option value='" . $_status_id . "' " . $custom_status_selected . " >" . __( $_status_value, 'awesome-support-time-tracking' ) . "</option>";
		}

		$dropdown .= '</select>';

		echo $dropdown;
	}
}

/**
 * Used as a helper function. Outputs html for the status field.
 *
 * @param   array 	$get	The get values
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_filter_html_status( $get ) {
	if( isset( $_GET['status'] ) ) {
		$status_selected = sanitize_text_field( $_GET['status'] );
		$all_selected = "";
		$open_selected = "";
		$closed_selected = "";

		if( $status_selected == "open" ) {
			$open_selected = "selected='selected'";
		} elseif( $status_selected == "any" ) {
			$all_selected = "selected='selected'";
		} elseif( $status_selected == "closed" ) {
			$closed_selected = "selected='selected'";
		}
	} else {
		$all_selected = "selected='selected'";
		$open_selected = "";
		$closed_selected = "";
	}

	$dropdown = '<select id="status" name="status">';
	$dropdown .= "<option value='any' $all_selected>" . __( 'All States', 'awesome-support-time-tracking' ) . "</option>";
	$dropdown .= "<option value='open' $open_selected>" . __( 'Open', 'awesome-support-time-tracking' ) . "</option>";
	$dropdown .= "<option value='closed' $closed_selected>" . __( 'Closed', 'awesome-support-time-tracking' ) . "</option>";
	$dropdown .= '</select>';

	echo $dropdown;
}

/**
 * Used as a helper function. Outputs html for the entry date field.
 *
 * @param   array 	$get	The get values
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_filter_html_entry_date( $get ) {
	$entry_html = "<select id='entry_date' name='entry_date'>";

	if( isset( $get['entry_date'] ) ) {
		$entry_selected = sanitize_text_field( $get['entry_date'] );
	} else {
		$entry_selected = "";
	}

	$entry_html .= "<option " . ( $entry_selected == 'all' ? 'selected="selected"' : '' ) . " value='all'>" . __( 'All Entry Dates', 'awesome-support-time-tracking' ) . "</option>";
	$entry_options = determine_entry_date_filter_options();

	foreach( $entry_options as $key => $value ) {
		$entry_html .= "<option " . ( $entry_selected == $value ? 'selected="selected"' : '' ) . " value='" . $value . "'>" . $key . "</option>";
	}

	$entry_html .= "</select>";

	echo $entry_html;
}

/**
 * Helper function for as_time_tracking_custom_list_filter. Outputs html for the agent field.
 *
 * @param   array 	$selected_agent		The previously selected agent
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_filter_html_agent( $selected_agent ) {
	$agent_html = '<div class="as_time_tracking_select_container"><select id="as_time_tracking_filter_agents" name="as_time_tracking_filter_agents" class="as_time_tracking_filter_dropdowns">';

	if( !empty( $selected_agent ) ) {
		$agent_html .= '<option value=' . $selected_agent[0] . '>' . $selected_agent[1] . '</option>';
	}

	$agent_html .= '</select>';

	echo $agent_html;
}

/**
 * Helper function for as_time_tracking_custom_list_filter. Outputs html for the customer field.
 *
 * @param   array 	$selected_customer		The previously selected customer
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_filter_html_customer( $selected_customer ) {
	$customer_html = '<select id="as_time_tracking_filter_customers" name="as_time_tracking_filter_customers" class="as_time_tracking_filter_dropdowns">';

	if( !empty( $selected_customer ) ) {
		$customer_html .= '<option value=' . $selected_customer[0] . '>' . $selected_customer[1] . '</option>';
	}

	$customer_html .= '</select></div>';

	echo $customer_html;
}

/**
 * Used as a helper function. Outputs html for the invoice field.
 *
 * @param   array 	$get	The get values
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_filter_html_invoice( $get ) {
	$selected_value = '';

	if ( isset( $get[ 'invoice_id' ] ) && !empty( $get[ 'invoice_id' ] ) ) {
		$invoice_id = sanitize_text_field( $get[ 'invoice_id' ] );
		$selected_value = $invoice_id;
	}

	echo '<input type="text" placeholder="Invoice #" name="invoice_id" id="as_time_tracking_filter_invoice_id" value="' . $selected_value . '" /><br>';
}

/**
 * Adds custom filters to time tracking post list.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_custom_list_filter() {
    global $typenow;

    if( $typenow == "trackedtimes" ) {
      //Ticket ID
      $selected_value = '';

      if (
        isset( $_GET[ 'ticket_id' ] ) &&
        isset( $_GET[ 'invoice_id' ] ) &&
        isset( $_GET[ 'status' ] ) &&
        isset( $_GET[ 'time_post_status' ] ) &&
				isset( $_GET[ 'entry_date' ] )
       ) {
        $ticket_id = sanitize_text_field( $_GET[ 'ticket_id' ] );
        $selected_value = $ticket_id;
      }

      //Output erach field's HTML
      as_time_tracking_filter_html_post_status( $_GET );
      as_time_tracking_filter_html_status( $_GET );
      as_time_tracking_filter_html_entry_date( $_GET );

      //Ticket field
      echo '<input type="text" placeholder="Ticket #" name="ticket_id" id="as_time_tracking_filter_ticket_id" value="' . $selected_value . '" />';

      //Invoice field
      as_time_tracking_filter_html_invoice( $_GET );

      //Add a clear div so the select2 fields position properly
      echo "<div style='clear: both;'></div>";

      //Agents
			$selected_agent = set_filter_agent_customer_input_values( "as_time_tracking_filter_agents" );
      as_time_tracking_filter_html_agent( $selected_agent );

      //Customers
			$selected_customer = set_filter_agent_customer_input_values( "as_time_tracking_filter_customers" );
      as_time_tracking_filter_html_customer( $selected_customer );
    }
}

add_action( "restrict_manage_posts", "as_time_tracking_custom_list_filter" );

/**
 * Helper function to get client/agent information for the edit page filter.
 *
 * @param		string $input_name   The name of the input
 *
 * @since   0.1.0
 * @return  array
 */
function set_filter_agent_customer_input_values( $input_name ) {
    if ( isset( $_GET[ $input_name ] ) && !empty( $_GET[ $input_name ] ) ) {
      $info = get_userdata( $_GET[ $input_name ] );
      ( $info !== false ? $selected_info = array( $_GET[ $input_name ], $info->display_name ) : $selected_info = array() );
    } else {
      $selected_info = array();
    }

    return $selected_info;
}

/**
 * Adds custom filters to time tracking post list.
 *
 * @param   object $query   The filter query
 *
 * @since   0.1.0
 * @return  void
 */
 function as_time_tracking_posts_filter( $query ) {
   global $wpdb;
   global $pagenow;

   if (
     is_admin() &&
     $pagenow == 'edit.php' &&
     isset( $_GET['ticket_id'] ) &&
     isset( $_GET['invoice_id'] ) &&
     isset( $_GET['status'] ) &&
     isset( $_GET['entry_date'] ) &&
     $_GET['post_type'] == 'trackedtimes'
   ) {
       $posts_to_include = array();
       $db_query = "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'as_time_tracking_entry'";
       $db_result = $wpdb->get_results( $db_query, OBJECT );

       //Loop through each tracked time values and check each filter condition
       foreach( $db_result as $meta ) {
         $posts_to_include[] = $meta->post_id;
       }

       //Filter checks
       $filter_values = as_time_tracking_filter_determine_get_values( $_GET );
       $final_posts = as_time_tracking_filter_validation_checks( $posts_to_include, $filter_values );
       $query->query_vars['post__in'] = $final_posts;

   } elseif( isset( $_GET['post_type'] ) ) {
     if(
         is_admin() &&
         $pagenow == 'edit.php' &&
         !isset( $_GET['ticket_id'] ) &&
         !isset( $_GET['invoice_id'] ) &&
         !isset( $_GET['status'] ) &&
         !isset( $_GET['entry_date'] ) &&
         $_GET['post_type'] == 'trackedtimes'
     ) {
       //For default loading view. Checks capabilities to see if agent can view other tracked times which are not their own.
       $posts_to_include = array();

       if( isset( $_GET['post_status'] ) && $_GET['post_status'] == 'draft' ) {
         $db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes'";
       } elseif( isset( $_GET['post_status'] ) && $_GET['post_status'] == 'trash' ) {
         $db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes' AND post_status = 'trash'";
       } else {
         $db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes' AND post_status = 'publish'";
       }

       $db_result = $wpdb->get_results( $db_query, OBJECT );

       //Loop through each tracked time values and check each filter condition
       foreach( $db_result as $meta ) {
         $posts_to_include[] = $meta->ID;
       }

       $final_posts = as_time_tracking_filter_no_value_validation_checks( $posts_to_include );
       $query->query_vars['post__in'] = $final_posts;
     }
   }
 }

add_filter( "parse_query", "as_time_tracking_posts_filter" );

/**
 * Used as a helper function. Sanitizes and returns get values.
 *
 * @param   object $get   The get values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_filter_determine_get_values( $get ) {
  $filter_values = array();
  $filter_values['ticket_id'] = sanitize_text_field( $get['ticket_id'] );
  $filter_values['invoice_id'] = sanitize_text_field( $get['invoice_id'] );
  $filter_values['status'] = sanitize_text_field( $get['status'] );
  $filter_values['statuses'] = sanitize_text_field( $get['time_post_status'] );
  ( isset( $get['as_time_tracking_filter_agents'] ) ? $filter_values['agent'] = sanitize_text_field( $get['as_time_tracking_filter_agents'] ) : $filter_values['agent'] = "" );
  ( isset( $get['as_time_tracking_filter_customers'] ) ? $filter_values['customer'] = sanitize_text_field( $get['as_time_tracking_filter_customers'] ) : $filter_values['customer'] = "" );
  $filter_values['filter_date'] = sanitize_text_field( $get['entry_date'] );

  return $filter_values;
}

/**
 * Used as a helper function. Checks the filter values to determine if the entry should be shown later.
 *
 * @param   object $posts_to_include    The time tracked entries
 * @param   array  $filter_values       The filter values
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_filter_validation_checks( $posts_to_include, $filter_values ) {
  $final_posts = array();

  foreach( $posts_to_include as $key => $value ) {

    $meta_info = get_post_meta( $value, "as_time_tracking_entry" );
    $ticket_obj = get_post( $meta_info[0]['ticket_id'] );

    if( isset( $ticket_obj->post_author ) ) {
      $client = get_userdata( $ticket_obj->post_author );
      $client = $client->data->ID;
    } else {
      $client = "";
    }

    //Ticket # check
    $filter_passed = check_filter_ticket_id( $filter_values['ticket_id'], $meta_info );
    $filter_passed = check_filter_invoice_id( $filter_values['invoice_id'], $meta_info, $filter_passed );
    $filter_passed = as_time_tracking_check_filter_fields( $filter_values['status'], "", $meta_info[0]['ticket_id'], $filter_passed, 'status' );
    $filter_passed = as_time_tracking_check_filter_fields( $filter_values['agent'], $meta_info[0]['agent'], 0, $filter_passed, 'agent' );
    $filter_passed = as_time_tracking_check_filter_fields( $filter_values['customer'], $client, 0, $filter_passed, 'customer' );
    $filter_passed = check_filter_entry_date( $filter_passed, $meta_info, $filter_values['filter_date'] );
    $filter_passed = check_capabilities_view( $filter_passed, $meta_info );
    $filter_passed = as_time_tracking_check_filter_fields( $filter_values['statuses'], "", $meta_info[0]['ticket_id'], $filter_passed, 'statuses' );

    if( $filter_passed === true ) {
      $final_posts[] = $value;
    }

    //If empty force empty entry otherwise wp_query returns everything
    if( empty( $final_posts ) ) {
      $final_posts[] = "";
    }

  }

  return $final_posts;
}

/**
 * Used as a helper function. Checks the filter values to determine if the entry should
 * be shown later if no get values were passed.
 *
 * @param   object $posts_to_include    The time tracked entries
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_filter_no_value_validation_checks( $posts_to_include ) {
  $final_posts = array();

  foreach( $posts_to_include as $key => $value ) {
    $meta_info = get_post_meta( $value, "as_time_tracking_entry" );
    if( current_user_can( 'add_other_tracked_time' ) ) {
      $final_posts[] = $value;
    } elseif( !current_user_can( 'add_other_tracked_time' ) && $meta_info[0]['agent'] == get_current_user_id() ) {
      $final_posts[] = $value;
    }
  }

  //If empty force empty entry otherwise wp_query returns everything
  if( empty( $final_posts ) ) {
    $final_posts[] = "";
  }

  return $final_posts;
}

/**
 * Helper function to get month/year input values for the edit page filter.
 *
 * @since   0.1.0
 * @return  array
 */
function determine_entry_date_filter_options() {
  global $wpdb;
	$db_query = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_type = 'trackedtimes' AND post_status NOT LIKE '%auto-draft%' AND post_status != 'trash'";
	$db_result = $wpdb->get_results( $db_query, OBJECT );
  $month_year_list = array();

	foreach( $db_result as $id ) {
		$meta_info = get_post_meta( $id->ID, 'as_time_tracking_entry' );
    if( !empty( $meta_info ) ) {
      $month = date( "m", strtotime( $meta_info[0]['entry_date_time'] ) );
      $year = date( "Y", strtotime( $meta_info[0]['entry_date_time'] ) );
      $month_year_val = $year . $month;

      if( !in_array( $month_year_val, $month_year_list ) ) {
        $month_year_list[] = $month_year_val;
      }
    }
  }

  //Get the option name and save back to the array
  foreach( $month_year_list as $key => $value ) {
    $month = substr( $value, -2 );
    $year = substr( $value, 0, 4 );
		$month_year_list = as_time_tracking_filter_date_array_check( $month_year_list, $year, $month, $key, $value );
  }

  return $month_year_list;
}

/**
 * Helper function to get the month/year list for the custom filter entry date values.
 *
 * @param   array   $month_year_list    The list of month/years which has time tracking entries
 * @param   string  $year               The year which has time tracking entries
 * @param   string  $month              The month which has time tracking entries
 * @param   string  $key                The key passed from the main function as its in a foreach loop
 * @param   string  $value              The value passed from the main function as its in a foreach loop
 *
 * @since   0.1.0
 * @return  array
 */
function as_time_tracking_filter_date_array_check( $month_year_list, $year, $month, $key, $value ) {
  $month_year_list = $month_year_list;

	switch( $month ) {
		case '01':
		$month_year_list['January' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '02':
		$month_year_list['February' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '03':
		$month_year_list['March' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '04':
		$month_year_list['April' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '05':
		$month_year_list['May' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '06':
		$month_year_list['June' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '07':
		$month_year_list['July' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '08':
		$month_year_list['August' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '09':
		$month_year_list['September' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '10':
		$month_year_list['October' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '11':
		$month_year_list['November' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;

		case '12':
		$month_year_list['December' . ' ' . $year] = $value;
		unset( $month_year_list[$key] );
		break;
	}

  return $month_year_list;
}

/**
 * Helper function to check if a ticket id used in the filter exists in an time entry or not.
 *
 * @param		integer $ticket_id_check   The ticket id to be checked
 * @param		object  $meta_info         Object of postmeta data
 *
 * @since   0.1.0
 * @return  boolean
 */
function check_filter_ticket_id( $ticket_id_check, $meta_info ) {
  $filter_passed = true;

  if( $ticket_id_check != "" ) {
    if( $meta_info[0]['ticket_id'] == $ticket_id_check ) {
      $filter_passed = true;
    } else {
      $filter_passed = false;
    }
  }

  return $filter_passed;
}

/**
 * Helper function to check if an invoice id used in the filter exists in an time entry or not.
 *
 * @param		integer  $invoice_id_check   The invoice id to be checked
 * @param		object   $meta_info          Object of postmeta data
 * @param		boolean  $filter             Filter check variable
 *
 * @since   0.1.0
 * @return  boolean
 */
function check_filter_invoice_id( $invoice_id_check, $meta_info, $filter ) {
  if( $filter === false ) {
    return false;
  }

  $filter_passed = true;

  if( $invoice_id_check != "" ) {
    if( $meta_info[0]['invoice_number'] == $invoice_id_check ) {
      $filter_passed = true;
    } else {
      $filter_passed = false;
    }
  }

  return $filter_passed;
}

/**
 * Helper function to check edit page filter fields. Uses other helper functions to check input values.
 *
 * @param		string   $val_to_check   The value to be checked
 * @param		string   $saved_val      The value saved in postmeta
 * @param		integer  $post_id        The post id
 * @param		boolean  $filter         The true/false indicator if the filter will pass or not
 * @param		string   $field          The field being checked
 *
 * @since   0.1.0
 * @return  boolean
 */
function as_time_tracking_check_filter_fields( $val_to_check, $saved_val, $post_id, $filter, $field ) {
  if( $filter === false ) {
    return false;
  }

  $filter_passed = true;
  $field_val = as_time_tracking_determine_field_val( $field, $post_id, $saved_val );

  if( $val_to_check != "" ) {
    $filter_passed = as_time_tracking_determine_filter_val( $field, $val_to_check, $field_val, $saved_val );
  }

  return $filter_passed;
}

/**
 * Helper function to get field value which will be used later for final filter value check.
 *
 * @param		string   $field          The field being checked
 * @param		integer  $post_id        The post id
 * @param		string   $saved_val      The value saved in postmeta
 *
 * @since   0.1.0
 * @return  string
 */
function as_time_tracking_determine_field_val( $field, $post_id, $saved_val ) {
	if( $field == 'statuses' ) {
		$field_val = get_post_status( $post_id );
	} elseif( $field == 'status' ) {
		$field_val = get_post_meta( $post_id, "_wpas_status", true );
	} else {
		$field_val = $saved_val;
	}

	return $field_val;
}

/**
 * Helper function to get field value which will be used later for final filter value check.
 * Used as a helper in the as_time_tracking_check_filter_fields function.
 *
 * @param		string   $field          The field being checked
 * @param		string   $val_to_check   The value to be checked
 * @param		string   $field_val      The value of the field
 * @param		string   $saved_val      The value saved in postmeta
 *
 * @since   0.1.0
 * @return  boolean
 */
function as_time_tracking_determine_filter_val( $field, $val_to_check, $field_val, $saved_val ) {
  if( $field == 'status' || $field == 'statuses' ) {
    if( $val_to_check == $field_val || $val_to_check == "any" ) {
      $filter_passed = true;
    } else {
      $filter_passed = false;
    }
  } else {
    if( $val_to_check == $saved_val ) {
      $filter_passed = true;
    } else {
      $filter_passed = false;
    }
  }

  return $filter_passed;
}

/**
 * Helper function to get field value which will be used later for final filter value check.
 * Used as a helper in the as_time_tracking_check_filter_fields function.
 *
 * @param		boolean  $filter          The true/false to indicate if the entry has passed the filter
 * @param		object   $meta_info       Object of postmeta data
 * @param		string   $entry_date      The entry date to check
 *
 * @since   0.1.0
 * @return  boolean
 */
function check_filter_entry_date( $filter, $meta_info, $entry_date ) {
  if( $filter === false ) {
    return false;
  }

  $entry_year = date( "Y", strtotime( $meta_info[0]['entry_date_time'] ) );
  $entry_month = date( "m", strtotime( $meta_info[0]['entry_date_time'] ) );
  $filter_year = substr( $entry_date, 0, 4 );
  $filter_month = substr( $entry_date, -2 );

  if( ( $entry_year == $filter_year && $entry_month == $filter_month ) || $entry_date == 'all' ) {
    $filter_passed = true;
  } else {
    $filter_passed = false;
  }

  return $filter_passed;
}

/**
 * Helper function to check if the current user has custom capabilities for the edit page filter.
 *
 * @param		boolean  $filter          The true/false to indicate if the entry has passed the filter
 * @param		object   $meta_info       Object of postmeta data
 *
 * @since   0.1.0
 * @return  boolean
 */
function check_capabilities_view( $filter, $meta_info ) {
  if( $filter === false ) {
    return false;
  }

  if( current_user_can( 'add_other_tracked_time' ) ) {
    $filter_passed = true;
  } elseif( !current_user_can( 'add_other_tracked_time' ) && $meta_info[0]['agent'] == get_current_user_id() ) {
    $filter_passed = true;
  } else {
    $filter_passed = false;
  }

  return $filter_passed;
}

/**
 * Hides add post button if user doesn't have the custom capabilities for it.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_disable_add_post() {
  if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'trackedtimes' && !current_user_can( 'add_own_tracked_time' ) ) {
      echo '<style type="text/css">
      .wrap .page-title-action { display:none; }
      </style>';
  }
}

add_action( "admin_menu", "as_time_tracking_disable_add_post" );

//Redirect if user doesn't have create new capabilities
global $pagenow;

if( $pagenow == 'post-new.php' && !current_user_can( 'add_own_tracked_time' ) ) {
  wp_redirect( admin_url() );
  exit;
}
