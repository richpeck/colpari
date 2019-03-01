<?php
/**
 * @package   Awesome Support Easy Digital Downloads
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'user_has_cap', 'asedd_allow_fes_agents', 0, 3 );
/**
 * Virtually give FES Vendors the AS client capabilities
 *
 * @since 0.1.0
 * @todo  Agent capabilities should not be hardcoded but should be retrieved from a core function instead
 *
 * @param array $allcaps User full capabilities
 * @param array $cap     Required capability
 * @param array $args    Argument list
 *
 * @return array User full capabilities with maybe ours in addition
 */
function asedd_allow_fes_agents( $allcaps, $cap, $args ) {

	global $post;

	$agent_cap = apply_filters( 'wpas_user_capabilities_fes_vendor', array(
		'view_ticket',
		'view_private_ticket',
		'edit_ticket',
		'edit_other_ticket',
		'edit_private_ticket',
		//		'assign_ticket',
		'close_ticket',
		'reply_ticket',
		//		'create_ticket',
		'delete_reply',
		'attach_files',
	) );

	/**
	 * If the current user is a vendor, we need to check that he is actually checking
	 * either the tickets list or a ticket that's assigned to him before giving him
	 * the agent capabilities.
	 *
	 * We don't want to use current_user_can() to check the user capabilities to avoid
	 * creating an infinite loop.
	 */
	$current_user = wp_get_current_user();

	if ( array_key_exists( 'frontend_vendor', $current_user->allcaps ) ) {

		if ( ! isset( $_GET['post_type'] ) && ! isset( $_GET['post'] ) && ! isset( $post ) ) {
			return $allcaps;
		}

		if ( isset( $_GET['post_type'] ) && 'ticket' !== $_GET['post_type'] ) {
			return $allcaps;
		}

		if ( isset( $_GET['post'] ) ) {

			if ( 'ticket' !== get_post_type( $_GET['post'] ) ) {
				return $allcaps;
			}

			$support_staff = (int) get_post_meta( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ), '_wpas_assignee', true );

			if ( $support_staff !== $current_user->ID ) {
				return $allcaps;
			}

		}

		if ( isset( $post ) && is_object( $post ) && 'ticket' !== get_post_type( $post->ID ) ) {
			return $allcaps;
		}

	}

	/**
	 * If the current user is not a vendor, it is an admin or an agent that is generating
	 * a list of AS-allowed users.
	 */
	if ( isset( $cap[0] ) && in_array( $cap[0], $agent_cap ) ) {

		if ( empty( $allcaps[ $cap[0] ] ) && array_key_exists( 'frontend_vendor', $allcaps ) ) {
			foreach ( $agent_cap as $c ) {
				$allcaps[ $c ] = true;
			}
		}

	}

	return $allcaps;

}

add_filter( 'wpas_find_agent_get_users_args', 'asedd_exclude_fes_vendor_find_agent', 10, 1 );
/**
 * Exclude EDD FES vendors from the find agent function
 *
 * @since 0.1.0
 *
 * @param array $args Arguments used for wpas_get_users
 *
 * @return array
 */
function asedd_exclude_fes_vendor_find_agent( $args ) {

	$args['cap_exclude'] = 'frontend_vendor';

	return $args;
}

add_action( 'pre_get_posts', 'asedd_vendor_hide_other_tickets', 999, 1 );
/**
 * Only show vendors their own tickets
 *
 * @since 0.1.0
 *
 * @param WP_Query $query
 *
 * @return bool True if the query was modified, false otherwise
 */
function asedd_vendor_hide_other_tickets( $query ) {

	/* Make sure this is the main query */
	if ( ! $query->is_main_query() ) {
		return false;
	}

	/* Make sure this is the admin screen */
	if ( ! is_admin() ) {
		return false;
	}

	/* Make sure we only alter our post type */
	if ( ! isset( $_GET['post_type'] ) || 'ticket' !== $_GET['post_type'] ) {
		return false;
	}

	global $current_user;

	if ( ! in_array( 'frontend_vendor', $current_user->roles ) ) {
		return false;
	}

	$query->set( 'meta_key', '_wpas_assignee' );
	$query->set( 'meta_value', get_current_user_id() );

	return true;

}

add_filter( 'edd_get_option_fes-allow-backend-access', 'asedd_allow_vendors_as_admin', 10, 3 );
/**
 * Allow EDD Frontend Vendors to see the Awesome Support admin screens (only)
 *
 * @since 0.1.0
 *
 * @param bool $value EDD FES option value
 *
 * @return bool
 */
function asedd_allow_vendors_as_admin( $value ) {

	if ( isset( $_GET['post_type'] ) && 'ticket' === $_GET['post_type'] ) {
		return true;
	}

	if ( isset( $_GET['post'] ) && 'ticket' === get_post_type( $_GET['post'] ) ) {
		return true;
	}

	if ( isset( $_POST['wpas_do'] ) && 'reply' === $_POST['wpas_do'] && isset( $_POST['post_type'] ) && 'ticket' === $_POST['post_type'] ) {
		return true;
	}

	return $value;

}

add_filter( 'wpas_new_ticket_agent_id', 'asedd_vendor_auto_assign_ticket', 10, 3 );
/**
 * Automatically assign tickets related to a vendor's product to the vendor
 *
 * @since 0.1.0
 *
 * @param int $agent_id  ID of the selected agent to assign to this ticket
 * @param int $ticket_id ID if the ticket that needs to be assigned
 *
 * @return int Support staff ID
 */
function asedd_vendor_auto_assign_ticket( $agent_id, $ticket_id ) {

	if ( false === (bool) wpas_get_option( 'support_products' ) ) {
		return $agent_id;
	}

	$product = new WPAS_Custom_Field( 'product', array(
		'name' => 'product',
		'args' => array( 'field_type' => 'taxonomy', 'required'   => true )
	) );

	$value = $product->get_field_value( '', $ticket_id );

	if ( empty( $value ) ) {
		return $agent_id;
	}

	$download = asedd_get_download_by_slug( $value );

	if ( empty( $download ) || ! isset( $download[0] ) ) {
		return $agent_id;
	}

	$post_author = (int) $download[0]->post_author;
	$autoassign  = esc_attr( get_the_author_meta( 'wpas_edd_fes_vendor_autoassign', $post_author ) );

	if ( empty( $autoassign ) ) {
		return $agent_id;
	}

	return $post_author;

}

/**
 * Get a download by slug
 *
 * @since 0.1.0
 *
 * @param $slug
 *
 * @return array
 */
function asedd_get_download_by_slug( $slug ) {

	global $wpdb;

	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_type = '%s' AND post_name = '%s'", 'download', $slug ) );

}

add_action( 'wpas_user_profile_fields', 'asedd_profile_field_vendor_autoassign', 10, 1 );
/**
 * User profile field "can be assigned"
 *
 * @since 3.1.5
 *
 * @param WP_User $user
 *
 * @return void
 */
function asedd_profile_field_vendor_autoassign( $user ) {

	if ( ! is_user_logged_in() || ! is_admin() ) {
		return;
	}

	if ( ! user_can( $user->ID, 'edit_ticket' ) ) {
		return;
	}

	if ( ! user_can( $user->ID, 'frontend_vendor' ) ) {
		return;
	} ?>

	<tr class="wpas-edd-fes-vendor-autoassign-wrap">
		<th><label><?php _e( 'Auto-Assign Own Products', 'as-edd' ); ?></label></th>
		<td>
			<?php $autoassign = esc_attr( get_the_author_meta( 'wpas_edd_fes_vendor_autoassign', $user->ID ) ); ?>
			<label for="wpas_edd_fes_vendor_autoassign"><input type="checkbox" name="wpas_edd_fes_vendor_autoassign" id="wpas_edd_fes_vendor_autoassign" value="yes" <?php if ( ! empty( $autoassign ) ) { echo 'checked'; } ?>> <?php _e( 'Yes', 'as-edd' ); ?></label>
			<p class="description"><?php _e( 'Auto-assign tickets about this vendor&#039;s products to him?', 'as-edd' ); ?></p>
		</td>
	</tr>

<?php }

add_action( 'edit_user_profile_update', 'asedd_save_user_vendor_profile_fields', 10, 1 );
/**
 * Save vendor profile fields
 *
 * @since 0.1.0
 *
 * @param  integer $user_id ID of the user to modify
 *
 * @return void
 */
function asedd_save_user_vendor_profile_fields( $user_id ) {

	if ( ! is_user_logged_in() || ! is_admin() ) {
		return;
	}

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	$autoassign = filter_input( INPUT_POST, 'wpas_edd_fes_vendor_autoassign' );

	if ( $autoassign ) {
		update_user_meta( $user_id, 'wpas_edd_fes_vendor_autoassign', $autoassign );
	} else {
		delete_user_meta( $user_id, 'wpas_edd_fes_vendor_autoassign' );
	}

}

add_action( 'pre_get_posts', 'asedd_remove_author_query_var_replies', 99, 1 );
/**
 * Remove the author query var from the WP_Query so that vendors can see client's replies
 *
 * @param WP_Query $query
 *
 * @return bool
 */
function asedd_remove_author_query_var_replies( $query ) {

	/* Make sure this is the admin screen */
	if ( ! is_admin() ) {
		return false;
	}

	if ( ! isset( $query->query_vars['post_type'] ) ) {
		return false;
	}

	$post_type = $query->query_vars['post_type'];

	if ( ! is_array( $post_type ) ) {
		$post_type = (array) $post_type;
	}

	if ( ! in_array( 'ticket_reply', $post_type ) ) {
		return false;
	}

	global $current_user;

	if ( ! in_array( 'frontend_vendor', $current_user->roles ) ) {
		return false;
	}

	$query->query_vars['author'] = '';

	return true;

}