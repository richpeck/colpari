<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'current_screen', 'as_time_tracking_productivity_editor_check' );

/**
 * Check if the productivity plugin is active. If it is, there is a conflict with the "Visual" tab of the
 * editor being incorrectly disabled on the 'edit_ticket_with_full_editor'. So if the capability on the current
 * user is true then add a hidden field which JavaScript can pick up and set the mode of the editor to 'design'.
 *
 * @since 	0.1.0
 * @param   $current_screen		Current screen that's been loaded
 * @return 	void
 */
function as_time_tracking_productivity_editor_check( $current_screen ) {
  if ( is_plugin_active( 'awesome-support-productivity/awesome-support-productivity-functions.php' ) && $current_screen->id == 'ticket' ) {
    $user = wp_get_current_user();
    if( $user->has_cap( 'edit_ticket_with_full_editor' ) ) {
      echo "<input type='hidden' name='as_time_tracking_productivity_active'>";
    }
  }
}

/**
 * Create new metabox on tickets page to hold the timer.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_custom_ticket_metabox() {
    add_meta_box(
        'as_time_tracking_timer_container',
        __( 'Time Tracking Timer', 'awesome-support-time-tracking' ),
        'as_time_tracking_display_metabox_timer',
        'ticket',
        'side',
        'default'
    );
}

add_action( 'add_meta_boxes_ticket', 'as_time_tracking_custom_ticket_metabox' );

/**
 * Timer content for the timer metabox.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_display_metabox_timer() {
	$timer_type = wpas_get_option( 'time_tracking_timer_type', 'automatic' );
  $rounding_val = wpas_get_option( 'time_tracking_section_min_rounding', '1' );
  ( $rounding_val > 60 ? $rounding_val = 1 : '' );
  $rounding_text = __( '*The current rounding level is ' . $rounding_val . ' minute(s).', 'awesome-support-time-tracking' );

	if( $timer_type == 'automatic' ) {
	?>
		<div id="as_time_tracking_automatic_timer_field_wrapper" class="as_time_tracking_record_time_wrapper">
			<input id="as_time_tracking_tickets_hours_field" type="text" name="as_time_tracking_tickets_hours_field">:
			<input id="as_time_tracking_tickets_minutes_field" type="text" name="as_time_tracking_tickets_minutes_field">:
			<input id="as_time_tracking_tickets_seconds_field" type="text" name="as_time_tracking_tickets_seconds_field"><br>
			<input type="hidden" id="as_time_tracking_tickets_start_date_time" name="as_time_tracking_tickets_start_date_time" value="<?php echo date('Y-m-d H:i:s', time()); ?>">
      <input type="hidden" id="as_time_tracking_ticket_level_end_date_time" name="as_time_tracking_ticket_level_end_date_time" value="">
			<a id="as_time_tracking_automatic_pause_start" class="button-primary" href="#"><?php _e( 'Pause', 'awesome-support-time-tracking' ); ?></a>
			<span class="as-time-tracking-notes"><?php _e( $rounding_text, 'awesome-support-time-tracking' ); ?></span>
		</div>
	<?php
	} else {
	?>
		<div class="as_time_tracking_record_time_wrapper">
			<div class="as_time_tracking_field_set">
				<input id="as_time_tracking_tickets_hours_field" type="text" name="as_time_tracking_tickets_hours_field"><label for="as_time_tracking_hours_field">:</label>
				<input id="as_time_tracking_tickets_minutes_field" type="text" name="as_time_tracking_tickets_minutes_field"><label for="as_time_tracking_minutes_field">:</label>
				<input id="as_time_tracking_tickets_seconds_field" type="text" name="as_time_tracking_tickets_seconds_field"><label for="as_time_tracking_seconds_field"></label><br>
				<a id="as_time_tracking_manual_pause_start" class="button-primary" href="#"><?php _e( 'Start', 'awesome-support-time-tracking' ); ?></a>
				<span class="as-time-tracking-notes"><?php _e( $rounding_text, 'awesome-support-time-tracking' ); ?></span>
			</div>
		</div>
	<?php
	}
	$allow_ticket_level = wpas_get_option( 'time_tracking_ticket_allow_ticket_level', 'no' );
  global $current_screen;

  if( $allow_ticket_level == 'yes' && $current_screen->id == 'ticket' ) {
    echo "<div class='as_time_tracking_ticket_level_option'>";

    $default_ticket_level_value = wpas_get_option( 'time_tracking_ticket_save_ticket_level_time_checkbox', 'no' );
    if( $default_ticket_level_value == 'yes' ) {
      echo "<input type='checkbox' name='as_time_tracking_allow_ticket_level_option' checked>";
    } else {
      echo "<input type='checkbox' name='as_time_tracking_allow_ticket_level_option'>";
    }

    echo "<label for='as_time_tracking_allow_ticket_level_option'> " . __( 'Save time on the ticket level', 'awesome-support-time-tracking' ) . "</label>";
    echo "</div>";
    echo "<div class='as_time_tracking_ticket_level_save_container'>";
    echo "<a class='button-primary' href='#'>" . __( 'Create Time Entry', 'awesome-support-time-tracking' ) . "</a>";
    echo "</div>";
    ?>
    <!-- Overlay and content -->
    <div id='as_time_tracking_custom_overlay_ticket_level' style='display: none;'>
      <div class='as_time_tracking_custom_background_ticket_level'>
        <a href='#' class='cancel'>&times;</a>
        <h1>
          <?php
          _e( 'Save Tracked Time', 'awesome-support-time-tracking' );
          ?>
        </h1>

        <div class="as_time_tracking_custom_content">
          <label for="as_time_tracking_ticket_level_description"><?php _e( 'Description', 'awesome-support-time-tracking' ); ?></label>
          <br>
          <textarea id="as_time_tracking_ticket_level_description" name="as_time_tracking_ticket_level_description"></textarea>
          <br>
          <a id="as-time-tracking-ticket-level-description-save" class="button-primary" href="#"><?php _e( 'Save', 'awesome-support-time-tracking' ); ?></a>
          <a id="as-time-tracking-ticket-level-close-btn" class="button-secondary" href="#"><?php _e( 'Close', 'awesome-support-time-tracking' ); ?></a>
          <div id="as-time-tracking-ticket-level-overlay-result"></div>
        </div>
      </div>
    </div>
    <?php
  }
}

/**
 * Display the time field on the ticket replies. Will determine the manual or
 * automatic field to show.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_log_time() {
	$background_colour = wpas_get_option( 'time_tracking_section_background', '#f1f1f1' );
	$style = "background: " . $background_colour . "; background-color: " . $background_colour;
?>
  <div style="<?php echo $style; ?>" class="as_time_tracking_ticket_reply_time">
    <h3><?php _e( 'Time Tracking', 'awesome-support-time-tracking' ); ?></h3>

      <?php
			$timer_type = wpas_get_option( 'time_tracking_timer_type', 'automatic' );
      ?>

      <input type="hidden" id="as_time_tracking_tickets_timer_type" name="as_time_tracking_tickets_timer_type" value="<?php echo $timer_type; ?>">

      <?php
      if( $timer_type == 'manual' ) {
      ?>

        <span id="as_time_tracking_manual_timer_text"></span>
        <a id="as_time_tracking_link_start_end" href="#"><?php _e( 'Click here to add start/end times', 'awesome-support-time-tracking' ) ?></a>

        <div class="as_time_tracking_record_time_wrapper">
					<?php
					as_time_tracking_ticket_output_manual_start_fields();
					as_time_tracking_ticket_output_manual_end_fields();
					?>
        </div>
      <?php
      }
    ?>
  </div>
<?php
}

add_action( 'wpas_admin_after_wysiwyg', 'as_time_tracking_log_time', 11 );

/**
 * Used as a helper function. Outputs select options and prepends a 0 if single digit.
 *
 * @param $unit_val		The maximum unit to use for creating the options
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_create_select_options( $unit_val ) {
  for( $i = 0; $i < $unit_val; $i++ ) {
    if( $i < 10 ) {
      echo "<option value='" . sprintf( '%02d', $i ) . "'>" . sprintf( '%02d', $i ) . "</option>";
    } else {
      echo "<option value='" . $i . "'>" . $i . "</option>";
    }
  }
}

/**
 * Used as a helper function. Outputs manual start fields.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_output_manual_start_fields() {
?>
<div class="as_time_tracking_field_set">
  <label for="as_time_tracking_tickets_start_date"><?php _e( 'Start Date:', 'awesome-support-time-tracking' ); ?></label><br>
  <input id="as_time_tracking_tickets_start_date" type="text" name="as_time_tracking_tickets_start_date">
</div>

<div class="as_time_tracking_field_set">
  <label><?php _e( 'Start Time:', 'awesome-support-time-tracking' ); ?></label><br>
  <select id="as_time_tracking_tickets_start_date_hours" name="as_time_tracking_tickets_start_date_hours">
    <?php
    as_time_tracking_ticket_create_select_options( 24 );
    ?>
  </select>
  <label class="as_time_tracking_date_label" for="as_time_tracking_tickets_start_date_hours"><?php _e( 'hour(s)', 'awesome-support-time-tracking' ); ?></label>
  <select id="as_time_tracking_tickets_start_date_minutes" name="as_time_tracking_tickets_start_date_minutes">
    <?php
    as_time_tracking_ticket_create_select_options( 60 );
    ?>
  </select>
  <label class="as_time_tracking_date_label" for="as_time_tracking_tickets_start_date_minutes"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
  <select id="as_time_tracking_tickets_start_date_seconds" name="as_time_tracking_tickets_start_date_seconds">
    <?php
    as_time_tracking_ticket_create_select_options( 60 );
    ?>
  </select>
  <label class="as_time_tracking_date_label" for="as_time_tracking_tickets_start_date_seconds"><?php _e( 'second(s)', 'awesome-support-time-tracking' ); ?></label>
</div>
<?php
}

/**
 * Used as a helper function. Outputs manual end fields.
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_output_manual_end_fields() {
?>
<div class="as_time_tracking_field_set">
  <label for="as_time_tracking_tickets_end_date"><?php _e( 'End Date:', 'awesome-support-time-tracking' ); ?></label><br>
  <input id="as_time_tracking_tickets_end_date" type="text" name="as_time_tracking_tickets_end_date">
</div>

<div class="as_time_tracking_field_set">
  <label><?php _e( 'End Time:', 'awesome-support-time-tracking' ); ?></label><br>
  <select id="as_time_tracking_tickets_end_date_hours" name="as_time_tracking_tickets_end_date_hours">
    <?php
    as_time_tracking_ticket_create_select_options( 24 );
    ?>
  </select>
  <label class="as_time_tracking_date_label" for="as_time_tracking_tickets_end_date_hours"><?php _e( 'hour(s)', 'awesome-support-time-tracking' ); ?></label>
  <select id="as_time_tracking_tickets_end_date_minutes" name="as_time_tracking_tickets_end_date_minutes">
    <?php
    as_time_tracking_ticket_create_select_options( 60 );
    ?>
  </select>
  <label class="as_time_tracking_date_label" for="as_time_tracking_tickets_end_date_minutes"><?php _e( 'minute(s)', 'awesome-support-time-tracking' ); ?></label>
  <select id="as_time_tracking_tickets_end_date_seconds" name="as_time_tracking_tickets_end_date_seconds">
    <?php
    as_time_tracking_ticket_create_select_options( 60 );
    ?>
  </select>
  <label class="as_time_tracking_date_label" for="as_time_tracking_tickets_end_date_seconds"><?php _e( 'second(s)', 'awesome-support-time-tracking' ); ?></label>
</div>
<?php
}

/**
 * Display the time information in the metabox, along with the button for the popup.
 *
 * @since 0.1.0
 * @return void
 */
function as_time_tracking_show_statistics() {
?>
  <div class="as_time_tracking_metabox_content_wrapper">
    <?php
    $ticket_billing_rate = get_post_meta( get_the_ID(), 'as_time_tracking_ticket_number_rate', true );
    ?>

    <label for="as_time_tracking_ticket_number_rate"><?php _e( 'Ticket Billing Rate:', 'awesome-support-time-tracking' ); ?></label><br>
    <?php _e( '$', 'awesome-support-time-tracking' ); ?><input type="text" name="as_time_tracking_ticket_number_rate" value="<?php echo $ticket_billing_rate; ?>">

    <?php
    $calculated_time = get_post_meta( get_the_ID(), '_wpas_ttl_calculated_time_spent_on_ticket', true );
		as_time_tracking_ticket_output_metabox_calculated_time( $calculated_time );

    $adjustment_time = get_post_meta( get_the_ID(), '_wpas_ttl_adjustments_to_time_spent_on_ticket', true );
		as_time_tracking_ticket_output_metabox_adjusted_time( $calculated_time, $adjustment_time );

    $final_time = get_post_meta( get_the_ID(), '_wpas_final_time_spent_on_ticket', true );
		as_time_tracking_ticket_output_metabox_final_time( $final_time );
    ?>

    <button id="as_time_tracking_all_recorded_time_button" class="button-primary"><?php _e( 'Display detailed time information', 'awesome-support-time-tracking' ); ?></button>
  </div>

  <!-- Overlay and content -->
  <div id='as_time_tracking_custom_overlay' style='display: none;'>
    <div class='as_time_tracking_custom_background'>
      <a href='#' class='cancel'>&times;</a>
			<h1>
				<?php
				printf(
				    __( 'All times recorded for Ticket # %s.', 'awesome-support-time-tracking' ),
				    get_the_ID()
				);
				?>
			</h1>

        <div class='as_time_tracking_custom_content'>
          <?php
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

          $ticket_time_data = as_time_tracking_get_ticket_time_data( $tracked_time_data, get_the_ID() );

          $calculated_time = 0;
          $time_adjustments = 0;

          foreach( $ticket_time_data as $data ) {
              $individual_time = $data['individual_time'];
            ( $individual_time != '' ?  $calculated_time += $individual_time : '' );

            $adjusted_time = $data['adjusted_time'];
            $adjusted_time = (int)$adjusted_time;
            ( $adjusted_time < 0 ? $time_adjustments -= substr( $adjusted_time, 1 ) : $time_adjustments += $adjusted_time );

            //if( $individual_time != '' ) {
              if( $data['is_ticket_reply'] == true ) {
                echo "<p class='as_time_tracking_overlay_reply_title'><strong>" . get_the_title( $data['ticket_reply'] ) . "</strong> (" . __( 'Ticket reply # ', 'awesome-support-time-tracking' ) . $data['ticket_reply'] . ")</p>";
              } elseif( $data['is_ticket_level'] == true ) {
                echo "<p class='as_time_tracking_overlay_reply_title'><strong>" . __( 'This time entry was saved on the ticket level', 'awesome-support-time-tracking' ) . "</strong></p>";
                echo "<strong>" . __( 'Description: ', 'awesome-support-time-tracking' ) . "</strong>" . $data['notes'] . "<br><br>";

              }
              echo "<strong>" . __( 'Time recorded: ', 'awesome-support-time-tracking' ) . "</strong>" . $individual_time . " minute(s)<br>";
              echo "<strong>" . __( 'Adjusted time: ', 'awesome-support-time-tracking' ) . "</strong>" . $adjusted_time . " minute(s)<br>";
              echo "<hr>";
            //}
          }

          $final_calculated_hours = intval( $calculated_time / 60 );
          $final_calculated_minutes = $calculated_time - ( $final_calculated_hours * 60 );

          $final_time = $time_adjustments + $calculated_time;
          $final_hours = intval( $final_time / 60 );
          $final_minutes = $final_time - ( $final_hours * 60 );

          echo "<div class='as_time_tracking_totals_wrapper'>";
          echo "<p><strong>" . __( 'Total adjusted time: ', 'awesome-support-time-tracking' ) . "</strong>" . $time_adjustments . " minute(s)</p>";
          echo "<p><strong>" . __( 'Total calculated time: ', 'awesome-support-time-tracking' ) . "</strong>" . $final_calculated_hours . " hour(s) " . $final_calculated_minutes . " minute(s)</p>";
          echo "<p><strong>" . __( 'Total final calculated time: ', 'awesome-support-time-tracking' ) . "</strong>" . $final_hours . " hour(s) " . $final_minutes . " minute(s)</p>";
          echo "</div>";
          ?>
        </div>
    </div>
  </div>
<?php
}

add_action( 'wpas_mb_details_after_time_tracking_statistics', 'as_time_tracking_show_statistics' );

/**
 * Used as a helper function. Loops through tracked times and returns ones for a specific ticket.
 *
 * @param 	array    $tracked_time_data		The tracked time data
 * @param 	integer  $ticket_id		        The ticket's post id
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_get_ticket_time_data( $tracked_time_data, $ticket_id ) {
  $return_arr = array();

  foreach( $tracked_time_data as $key => $data ) {
    if( $data['serialized_data']['ticket_id'] == $ticket_id ) {
      $return_arr[] = $data['serialized_data'];
    }
  }

  return $return_arr;
}

/**
 * Used as a helper function. Outputs the caclulated time html to the metabox.
 *
 * @param 	string $calculated_time		The calculated time
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_output_metabox_calculated_time( $calculated_time ) {
  if( is_numeric( $calculated_time ) ) {
    $calculated_hours = (int)$calculated_time / 60;
    $calculated_hours = floor( $calculated_hours );
    $calculated_minutes = $calculated_time - ( (int)$calculated_hours * 60 );

    echo "<p><strong>" . __( 'Calculated Time Spent On Ticket:', 'awesome-support-time-tracking' ) . "</strong><br>";
    echo $calculated_hours . __( ' hour(s) ', 'awesome-support-time-tracking' ) . $calculated_minutes . __( ' minutes(s) ', 'awesome-support-time-tracking' ) . "</p>";
  } else {
    echo "<p><strong>" . __( 'Calculated Time Spent On Ticket:', 'awesome-support-time-tracking' ) . "</strong><br>";
    echo __( '0 hour(s) 0 minute(s)', 'awesome-support-time-tracking' ) . "</p>";
  }
}

/**
 * Used as a helper function. Outputs the adjusted time html to the metabox.
 *
 * @param 	string $calculated_time		The calculated time
 * @param 	string $adjustment_time		The adjusted time
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_output_metabox_adjusted_time( $calculated_time, $adjustment_time ) {
  if( is_numeric( $calculated_time ) ) {
    echo "<p><strong>" . __( 'Total Time Adjustments On Ticket:', 'awesome-support-time-tracking' ) . "</strong><br>";
    echo intval( $adjustment_time ) . " minute(s)</p>";
  } else {
    echo "<p><strong>" . __( 'Total Time Adjustments On Ticket:', 'awesome-support-time-tracking' ) . "</strong><br>";
    echo __( '0 minute(s)', 'awesome-support-time-tracking' ) . "</p>";
  }
}

/**
 * Used as a helper function. Outputs the adjusted time html to the metabox.
 *
 * @param 	string $final_time		The final time
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_output_metabox_final_time( $final_time ) {
  if( is_numeric( $final_time ) ) {
    $final_hours = (int)$final_time / 60 ;
    $final_hours = floor( $final_hours );
    $final_minutes = $final_time - ( (int)$final_hours * 60 );

    echo "<p><strong>" . __( 'Final Time Spent On Ticket:', 'awesome-support-time-tracking' ) . "</strong><br>";
    echo $final_hours . __( ' hour(s) ', 'awesome-support-time-tracking' ) . $final_minutes . __( ' minute(s)', 'awesome-support-time-tracking' ).  "</p>";
  } else {
    echo "<p><strong>" . __( 'Final Time Spent On Ticket:', 'awesome-support-time-tracking' ) . "</strong><br>";
    echo __( '0 hour(s) 0 minute(s)', 'awesome-support-time-tracking' ) . "</p>";
  }
}

/**
 * Save billing rate field when ticket is saved.
 *
 * @param		integer $post_id		The post id
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_save_ticket_billing_field( $post_id ) {
  //Validate each of the fields then save if valid
	if( isset( $_POST['as_time_tracking_ticket_number_rate'] ) ) {
	  $number_rate = sanitize_text_field( $_POST['as_time_tracking_ticket_number_rate'] );
	  $final_rate = validate_billing_field( $number_rate );

	  if( $final_rate !== false ) {
	    update_post_meta( $post_id, 'as_time_tracking_ticket_number_rate', $final_rate );
	  }
	}
}

add_action( 'save_post_ticket', 'as_time_tracking_save_ticket_billing_field' );

/**
 * Used as a helper function. Calculates the initial saved time to minutes.
 *
 * @param 	object $post    The posted values
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_caluclate_individual_time( $post ) {
  $time_hours_minutes = array();

  //Setting a value if not set so PHP notice doesn't show in log
  ( !isset( $post['as_time_tracking_tickets_hours_field'] ) ? $post['as_time_tracking_tickets_hours_field'] = '' : '' );
  ( !isset( $post['as_time_tracking_tickets_minutes_field'] ) ? $post['as_time_tracking_tickets_minutes_field'] = '' : '' );
  ( !isset( $post['as_time_tracking_tickets_seconds_field'] ) ? $post['as_time_tracking_tickets_seconds_field'] = '' : '' );

  ( is_numeric( $post['as_time_tracking_tickets_hours_field'] ) ? $time_hours_minutes['individual_time_hours'] = sanitize_text_field( round( $post['as_time_tracking_tickets_hours_field'] ) ) : $time_hours_minutes['individual_time_hours'] = '' );
  ( $time_hours_minutes['individual_time_hours'] == '' ? $time_hours_minutes['individual_time_hours'] = 0 : $time_hours_minutes['individual_time_hours'] = $time_hours_minutes['individual_time_hours'] * 60 );

  ( is_numeric( $post['as_time_tracking_tickets_minutes_field'] ) ? $time_hours_minutes['individual_time_minutes'] = sanitize_text_field( round( $post['as_time_tracking_tickets_minutes_field'] ) ) : $time_hours_minutes['individual_time_minutes'] = '' );
  ( $time_hours_minutes['individual_time_minutes'] == '' ? $time_hours_minutes['individual_time_minutes'] = 0 : $time_hours_minutes['individual_time_minutes'] = $time_hours_minutes['individual_time_minutes'] );

  ( is_numeric( $post['as_time_tracking_tickets_seconds_field'] ) ? $time_hours_minutes['individual_time_seconds'] = sanitize_text_field( $post['as_time_tracking_tickets_seconds_field'] ) : $time_hours_minutes['individual_time_seconds'] = '' );
  ( $time_hours_minutes['individual_time_seconds'] == '' ? $time_hours_minutes['individual_time_seconds'] = 0 : $time_hours_minutes['individual_time_seconds'] = $time_hours_minutes['individual_time_seconds'] );
  ( $time_hours_minutes['individual_time_seconds'] > 0 ? $time_hours_minutes['individual_time_minutes'] += 1 : $time_hours_minutes['individual_time_minutes'] += 0 );

  return $time_hours_minutes;
}

/**
 * Helper function for as_time_tracking_ticket_reply_save. Updates time recorded based on rounding setting.
 * If time entered set a boolean to true which will be checked later.
 *
 * @param 	string $final_individual_time    The individual time
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_calculate_time_entered_rounding( $final_individual_time ) {
  $rounding_final_time_recorded = array();

  $rounding_val = wpas_get_option( 'time_tracking_section_min_rounding', '1' );
  ( !is_numeric( $rounding_val ) || $rounding_val > 60 ? $rounding_val = 1 : "" );
  $mult_rounding_vals = as_time_tracking_get_rounding_vals( $rounding_val );

  //( $final_individual_time != 0 ? $rounding_final_time_recorded['time_recorded'] = true : $rounding_final_time_recorded['time_recorded'] = false );
	$rounding_final_time_recorded['time_recorded'] = true;

  $rounding_final_time_recorded['hours'] = intval( $final_individual_time / 60 );
  $minutes = $final_individual_time - ( $rounding_final_time_recorded['hours'] * 60 );
  $rounding_final_time_recorded['rounded_amount'] = get_closest_rounding_val( $final_individual_time, $minutes, $mult_rounding_vals, $rounding_final_time_recorded['hours'] );

  return $rounding_final_time_recorded;
}

/**
 * Helper function for as_time_tracking_ticket_reply_save. Validates values to determine if time should be saved or not.
 *
 * @param 	string $start_date_time          Start date time posted
 * @param 	string $end_date_time            End date time posted
 * @param 	string $final_individual_time    Individual time posted
 *
 * @since 	0.1.0
 * @return 	boolean
 */
function as_time_tracking_ticket_check_if_validation_passed( $start_date_time, $end_date_time, $final_individual_time ) {
  $start_date_check = DateTime::createFromFormat( "Y-m-d H:i:s", $start_date_time );
  $val_passed = as_time_tracking_ticket_check_dates_validation_passed( $start_date_check, $end_date_time, $start_date_time );

  if( $val_passed === true ) {
    if( !is_numeric( $final_individual_time ) ) {
      $val_passed = false;
    }
  }

  return $val_passed;
}

/**
 * Helper function for as_time_tracking_ticket_reply_save. Validates values to determine if time should be saved or not.
 *
 * @param 	boolean $start_date_check     The indiciator if the start date was set
 * @param 	string 	$end_date_time        The end date time
 * @param 	string 	$start_date_time    	The individual time
 *
 * @since 	0.1.0
 * @return 	boolean
 */
function as_time_tracking_ticket_check_dates_validation_passed( $start_date_check, $end_date_time, $start_date_time ) {
  $val_passed = true;

  if( $start_date_check === false ) {
    $val_passed = false;
  }

  if( $val_passed === true ) {
    $end_date_check = DateTime::createFromFormat( "Y-m-d H:i:s", $end_date_time );

    if( $end_date_check === false ) {
      $val_passed = false;
    }
  }

  if( $val_passed === true ) {
    if( strtotime( $end_date_time ) < strtotime( $start_date_time ) ) {
      $val_passed = false;
    }
  }

  return $val_passed;
}

/**
* Save post information when submitted. Also happens when creating new entry so empty check on post is done.
*
* @param 	integer 	$post_id     The post id
* @param 	array 		$data        The post parent data
* @param 	integer 	$reply    	 The ticket replt id
*
* @since 	0.1.0
* @return boolean
*/
function as_time_tracking_ticket_reply_save( $post_id, $data, $reply ) {
  //Checks new settings if the ticket is being saved on the ticket level then we don't need to save the data here
  if( isset( $_POST['as_time_tracking_allow_ticket_level_option'] ) ) {
    $ticket_level_allowed = $_POST['as_time_tracking_allow_ticket_level_option'];
  } else {
    $ticket_level_allowed = "";
  }

  if( !empty( $reply ) ) {

    //Calculate time values with rounding and see if a time was recorded by the user
    $time_hours_minutes = as_time_tracking_caluclate_individual_time( $_POST );
		$final_individual_time = $time_hours_minutes['individual_time_hours'] + $time_hours_minutes['individual_time_minutes'];
    $rounding_final_time_recorded = as_time_tracking_calculate_time_entered_rounding( $final_individual_time );
    $final_individual_time = $rounding_final_time_recorded['hours'] * 60 + $rounding_final_time_recorded['rounded_amount'];

		//Values based on timer type
    if( $_POST['as_time_tracking_tickets_timer_type'] == 'automatic' ) {
      $start_date_time = sanitize_text_field( $_POST['as_time_tracking_tickets_start_date_time'] );
			$end_date_time = date( 'Y-m-d H:i:s', time() );
    } else {
      $start_date = sanitize_text_field( $_POST['as_time_tracking_tickets_start_date'] );
      $start_date_hours = sanitize_text_field( $_POST['as_time_tracking_tickets_start_date_hours'] );
      $start_date_minutes = sanitize_text_field( $_POST['as_time_tracking_tickets_start_date_minutes'] );
      $start_date_seconds = sanitize_text_field( $_POST['as_time_tracking_tickets_start_date_seconds'] );
      $start_date_time = $start_date . " " . $start_date_hours . ":" . $start_date_minutes . ":" . $start_date_seconds;

      $end_date = sanitize_text_field( $_POST['as_time_tracking_tickets_end_date'] );
      $end_date_hours = sanitize_text_field( $_POST['as_time_tracking_tickets_end_date_hours'] );
      $end_date_minutes = sanitize_text_field( $_POST['as_time_tracking_tickets_end_date_minutes'] );
      $end_date_seconds = sanitize_text_field( $_POST['as_time_tracking_tickets_end_date_seconds'] );
      $end_date_time = $end_date . " " . $end_date_hours . ":" . $end_date_minutes . ":" . $end_date_seconds;
    }

    if( $ticket_level_allowed == 'on' ) {
      $ticket_level_allowed = true;
      $ticket_reply_level = false;
      $title = __( 'Ticket #', 'awesome-support-time-tracking' ) . $data['post_parent'];
      $reply = "";
      if( isset( $_POST['as_time_tracking_ticket_level_description'] ) ) {
        $description = sanitize_text_field( $_POST['as_time_tracking_ticket_level_description'] );
      } else {
        $description = "";
      }
    } else {
      $ticket_level_allowed = false;
      $ticket_reply_level = true;
      $title = __( 'Ticket #', 'awesome-support-time-tracking' ) . $data['post_parent'] . __( ', Ticket reply #', 'awesome-support-time-tracking' ) . $reply;
      $description = "";
    }

    if( $rounding_final_time_recorded['time_recorded'] === true ) {
      //Final validation checks if js ones failed
      $val_passed = as_time_tracking_ticket_check_if_validation_passed( $start_date_time, $end_date_time, $final_individual_time );
      //Save everything if the validation is passed
      if( $val_passed === true ) {
				$agent = get_current_user_id();

        $cpt_id = wp_insert_post( array(
          'post_title'=> $title,
          'post_type'=> 'trackedtimes',
          'post_content' => '',
          'post_status' => 'publish' )
        );

        $allow_multiple_replies = wpas_get_option( 'time_tracking_ticket_allow_multiple_entries', 'no' );
        if( $allow_multiple_replies == 'yes' ) {
          $allow_replies = true;
        } else {
          $allow_replies = false;
        }

        $data_to_save = array(
            'ticket_id' => $data['post_parent'],
            'ticket_reply' => $reply,
            'entry_date_time' => date( 'Y-m-d H:i:s', time() ),
            'start_date_time' => $start_date_time,
            'end_date_time' => $end_date_time,
            'notes' => $description,
            'agent' => $agent,
						'invoiced' => '',
						'invoice_number' => '',
            'individual_time' => $final_individual_time,
            'adjusted_time' => 0,
            'is_ticket_reply_multiple' => $allow_replies,
            'is_ticket_reply' => $ticket_reply_level,
            'is_ticket_level' => $ticket_level_allowed
        );

        update_post_meta( $cpt_id, 'as_time_tracking_entry', $data_to_save );

				//Recalculate the total time and update
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

        $ticket_time_data = array();

        foreach( $tracked_time_data as $time_data ) {
          if( $time_data['serialized_data']['ticket_id'] == $data['post_parent'] ) {
            $ticket_time_data[] = $time_data['serialized_data'];
          }
        }

    		$calculated_time = 0;
    		$time_adjustments = 0;

        if( isset( $ticket_time_data ) ) {
      		foreach( $ticket_time_data as $time_data ) {
      			$individual_time = $time_data['individual_time'];
      			( $individual_time != '' ?  $calculated_time += $individual_time : '' );
      			$adjusted_time = $time_data['adjusted_time'];
            $adjusted_time = (int)$adjusted_time;
      			( $adjusted_time < 0 ? $time_adjustments -= substr( $adjusted_time, 1 ) : $time_adjustments += $adjusted_time );
      		}
        }

        update_post_meta($data['post_parent'], '_wpas_ttl_adjustments_to_time_spent_on_ticket', $time_adjustments);

    		//Update calculated time
        update_post_meta($data['post_parent'], '_wpas_ttl_calculated_time_spent_on_ticket', $calculated_time);

    		//Update adjustment time
        $final_time = $time_adjustments + $calculated_time;
        update_post_meta($data['post_parent'], '_wpas_final_time_spent_on_ticket', $final_time);
      }
    }
  }
}

add_action( 'wpas_post_reply_admin_after', 'as_time_tracking_ticket_reply_save', 11, 3 );

/**
 * Helper function for as_time_tracking_ticket_reply_save. Gets the multiples of rounding based on the setting.
 *
 * @param 	integer $multiple    The rounding value saved
 *
 * @since 	0.1.0
 * @return 	array
 */
function as_time_tracking_get_rounding_vals( $multiple ) {
  $vals = array();
	$vals[] = 0;

  for( $i = 1; $i < 61; $i++ ) {
    if( $i % $multiple == 0 ) {
      $vals[] = $i;
    }
  }

  return $vals;
}

/**
 * Helper function for as_time_tracking_ticket_reply_save. Gets the closest rounding value.
 *
 * @param 	integer $search   The minutes to search for the cloest rounding value
 * @param 	array $arr       	The multiple rounding values
 *
 * @since 	0.1.0
 * @return 	integer
 */
function get_closest_rounding_val( $final_individual_time, $search, $arr, $hours ) {
	$closest = null;

  //If time is zero return 0, otherwise remove the "0" unit as anytime over 0 should be rounded to closest largest rounding value
  if( (int)$final_individual_time === 0 ) {
    return 0;
  } elseif( $hours === 0 ) {
    unset( $arr[0] );
  }

  foreach ( $arr as $item ) {
  	if ( $closest === null || abs( $search - $closest ) > abs( $item - $search ) ) {
    	$closest = $item;
    }
  }

   return $closest;
}

/**
 * Enter time information after each ticket reply entry. Uses core plugin's custom hook.
 *
 * @param 	integer $row	The id to get post meta from
 *
 * @since 	0.1.0
 * @return 	void
 */
function as_time_tracking_ticket_reply_content_show_time( $row ) {
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

  $individual_time = '';

  foreach( $tracked_time_data as $key => $data ) {
    if( $data['serialized_data']['ticket_reply'] == $row ) {
      $individual_time = $data['serialized_data']['individual_time'];
      break;
    }
  }

  if( $individual_time != '' ) {
    echo "<p>" . __( 'Tracked Time:', 'awesome-support-time-tracking' ) . " <strong>" . $individual_time . " " . __( 'minute(s)', 'awesome-support-time-tracking' ) . "</strong></p>";
  }
}

add_action( "wpas_backend_reply_content_after", "as_time_tracking_ticket_reply_content_show_time" );
