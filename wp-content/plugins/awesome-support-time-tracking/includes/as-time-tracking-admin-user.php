<?php
/**
 * Adds user billing field if they have the agent role.
 *
 * @since   0.1.0
 * @param   object $user    The user information
 * @return  void
 */
function as_time_tracking_user_billing_rate_field( $user ) {
  $options = unserialize( get_option( 'wpas_options', array() ) );
  $heading_title = "";
  $field_heading = "";
  $dollar = __( '$', 'awesome-support-time-tracking' );
  $input_id = "";
  $num_rate = "";

  if( isset( $options['time_tracking_section_billing_rate_access'] ) ) {
    $billing_rate_options = $options['time_tracking_section_billing_rate_access'];
  } else {
    $billing_rate_options = as_time_tracking_get_empty_billing_options();
  }

  settype( $billing_rate_options, 'array' ); //On plugin load sometimes is set as a string

  //Check user role against allowed roles in the settings
  if( !empty( array_intersect( $billing_rate_options, $user->roles ) ) ) {
    $heading_title = __( 'Awesome Support Agent Billing Rate', 'awesome-support-time-tracking' );
    $field_heading = __( 'Agent billing hourly rate', 'awesome-support-time-tracking' );
  }

  if( in_array( "wpas_user", $user->roles ) ) {
    $heading_title = __( 'Awesome Support Client Billing Rate', 'awesome-support-time-tracking' );
    $field_heading = __( 'Client hourly billing rate', 'awesome-support-time-tracking' );
  }
?>

<h3><?php echo $heading_title; ?></h3>

<table class="form-table">
  <tbody>
    <tr>
        <th><label><?php echo $field_heading; ?></label></th>
        <td>
          <?php
          if( !empty( array_intersect( $billing_rate_options, $user->roles ) ) ) {
            $usermeta = get_user_option( 'as_time_tracking_agent_billing_rate', $user->ID );
            ( !empty( $usermeta ) ? $num_rate = $usermeta : $num_rate = "" );
            $input_id = "as_time_tracking_number_rate";
          }

          if( in_array( "wpas_user", $user->roles ) ) {
            $usermeta = get_user_option( 'as_time_tracking_client_billing_rate', $user->ID );
            ( !empty( $usermeta ) ? $num_rate = $usermeta : $num_rate = "" );
            $input_id = "as_time_tracking_client_number_rate";
          }

          $allowed_billing_roles = wpas_get_option( 'time_tracking_section_billing_rate_access', array() );
          $matching_roles = array_intersect( $allowed_billing_roles, $user->roles );

          if( !empty( $matching_roles ) ) {
            echo $dollar;
          ?>
            <input type="text" name="<?php echo $input_id; ?>" value="<?php echo $num_rate; ?>">
          <?php
          }
          ?>
        </td>
    </tr>
  </tbody>
</table>
<?php
}

add_action('show_user_profile', 'as_time_tracking_user_billing_rate_field');
add_action('edit_user_profile', 'as_time_tracking_user_billing_rate_field');

/**
 * Used as a helper function. Checks Awesome Support roles exist before adding them to an array.
 * These four roles are the default which are allowed to have the agent billing field.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_get_empty_billing_options() {
  global $wp_roles;
  $all_roles = $wp_roles->roles;
  $role_arr = array();
  $billing_rate_options = array();

  foreach( $all_roles as $key => $value ) {
    $role_arr[$key] = $key;
  }

  if( in_array( 'administrator', $role_arr ) ) {
    $billing_rate_options[] = array_search( "administrator", $role_arr );
  }

  if( in_array( 'wpas_manager', $role_arr ) ) {
    $billing_rate_options[] = array_search( "wpas_manager", $role_arr );
  }

  if( in_array( 'wpas_support_manager', $role_arr ) ) {
    $billing_rate_options[] = array_search( "wpas_support_manager", $role_arr );
  }

  if( in_array( 'wpas_agent', $role_arr ) ) {
    $billing_rate_options[] = array_search( "wpas_agent", $role_arr );
  }

  return $billing_rate_options;
}

/**
 * Adds user billing field if they have the agent role.
 *
 * @since   0.1.0
 * @param   integer $user_id    The user id
 * @return  void
 */
function as_time_tracking_save_extra_profile_fields( $user_id ) {
    if( !current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    $billing_id = "";
    $final_rate = "";

    //Validate each of the fields then save if valid
    if( isset( $_POST['as_time_tracking_number_rate'] ) ) {
      $number_rate = sanitize_text_field( $_POST['as_time_tracking_number_rate'] );
      $final_rate = validate_billing_field( $number_rate );
      $billing_id = "as_time_tracking_agent_billing_rate";
    }

    //Validate each of the fields then save if valid
    if( isset( $_POST['as_time_tracking_client_number_rate'] ) ) {
      $number_rate = sanitize_text_field( $_POST['as_time_tracking_client_number_rate'] );
      $final_rate = validate_billing_field( $number_rate );
      $billing_id = "as_time_tracking_client_billing_rate";
    }

    if( $final_rate !== false ) {
      update_user_option( absint( $user_id ), $billing_id, $final_rate );
    }
}

add_action( 'personal_options_update', 'as_time_tracking_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'as_time_tracking_save_extra_profile_fields' );

/**
 * Validates the custom agent billing fields. Used as a helper function.
 *
 * @since   0.1.0
 * @param   string $number_rate   The number rate to separate
 * @return  array
 */
 function validate_billing_field( $number_rate ) {
   $full_rate = "";
   $rate_fields = explode( ".", $number_rate  );
   ( empty( $rate_fields[0] ) ? $rate_fields[0] = 0 : "" );
   ( empty( $rate_fields[1] ) ? $rate_fields[1] = 0 : "" );

   if( !is_numeric( $rate_fields[0] ) || !is_numeric( $rate_fields[1] ) ) {
     $val_passed = false;
   } else {
     $val_passed = true;
   }

   //Join the numbers and round decimals
   if( $val_passed === true ) {
     $full_rate = $rate_fields[0] . "." . $rate_fields[1];
     $full_rate = number_format( ( float )$full_rate, 2, '.', '');
   }

   if( empty( $full_rate ) ) {
     return false;
   } else {
     ( $full_rate == "0.00" ? $full_rate = "" : "" );
     return $full_rate;
   }
 }
