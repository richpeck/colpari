<?php
/**
 * Deactivation code for the addon. Will reset basic time tracking options and capabilities.
 *
 * @since   0.1.0
 * @return  void
 */
function as_time_tracking_deactivate() {
  $old_options = unserialize( get_option( 'as_time_tracking_old_options') );
  $options = unserialize( get_option( 'wpas_options', array() ) );
  $options['allow_agents_to_enter_time'] = $old_options['allow_agents_to_enter_time'];
  $options['show_basic_time_tracking_fields'] = $old_options['show_basic_time_tracking_fields'];
  $options['recalculate_final_time_on_save'] = $old_options['recalculate_final_time_on_save'];
  update_option( 'wpas_options', serialize( $options ) );

  //Delete old option
  delete_option( "as_time_tracking_old_options" );

  global $wp_roles;

  $wp_roles->remove_cap( 'administrator', 'add_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'add_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'add_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'add_other_tracked_time' );

  $wp_roles->remove_cap( 'administrator', 'edit_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'edit_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'edit_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'edit_other_tracked_time' );

  $wp_roles->remove_cap( 'administrator', 'delete_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'delete_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'delete_other_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'delete_other_tracked_time' );

  $wp_roles->remove_cap( 'administrator', 'view_other_time_reports' );
  $wp_roles->remove_cap( 'wpas_manager', 'view_other_time_reports' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'view_other_time_reports' );
  $wp_roles->remove_cap( 'wpas_agent', 'view_other_time_reports' );

  $wp_roles->remove_cap( 'administrator', 'add_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'add_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'add_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'add_own_tracked_time' );

  $wp_roles->remove_cap( 'administrator', 'edit_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'edit_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'edit_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'edit_own_tracked_time' );

  $wp_roles->remove_cap( 'administrator', 'delete_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'delete_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'delete_own_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'delete_own_tracked_time' );

  $wp_roles->remove_cap( 'administrator', 'view_own_time_reports' );
  $wp_roles->remove_cap( 'wpas_manager', 'view_own_time_reports' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'view_own_time_reports' );
  $wp_roles->remove_cap( 'wpas_agent', 'view_own_time_reports' );

  $wp_roles->remove_cap( 'administrator', 'manage_tracked_time' );
  $wp_roles->remove_cap( 'wpas_manager', 'manage_tracked_time' );
  $wp_roles->remove_cap( 'wpas_support_manager', 'manage_tracked_time' );
  $wp_roles->remove_cap( 'wpas_agent', 'manage_tracked_time' );
}

register_deactivation_hook( AS_TT_PATH . 'time-tracking.php', 'as_time_tracking_deactivate' );
