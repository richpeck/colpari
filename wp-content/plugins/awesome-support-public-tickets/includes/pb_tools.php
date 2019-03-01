<?php

/**
 * Add Tools to the TICKETS->TOOLS->CLEANUP Menu
 */

 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'wpas_system_tools_table_after', 'pbtk_add_mark_all_public_tool', 10, 0 );

/**
* Add the button to the TOOLS page that allows for marking all existing tickets public.
*/
function pbtk_add_mark_all_public_tool() { ?>
	<tr>
		<td class="row-title"><label for="tablecell"><?php _e( 'Mark All Existing Tickets Public', 'as-public-tickets' ); ?></label></td>
		<td>
			<a href="<?php echo wpas_tool_link( 'mark_tickets_public' ); ?>" class="button-secondary"><?php _e( 'Mark Public', 'as-public-tickets' ); ?></a>
			<span class="wpas-system-tools-desc"><?php _e( 'Mark all existing tickets public.', 'as-public-tickets' ); ?></span>
		</td>
	</tr>
	
	<tr>
		<td class="row-title"><label for="tablecell"><?php _e( 'Mark All Existing Ticket REPLIES Public', 'as-public-tickets' ); ?></label></td>
		<td>
			<a href="<?php echo wpas_tool_link( 'mark_ticket_replies_public' ); ?>" class="button-secondary"><?php _e( 'Mark Replies Public', 'as-public-tickets' ); ?></a>
			<span class="wpas-system-tools-desc"><?php _e( 'Mark all existing ticket replies public.', 'as-public-tickets' ); ?></span>
		</td>
	</tr>	
	
	<tr>
		<td class="row-title"><label for="tablecell"><?php _e( 'Mark  All Existing Tickets Private', 'as-public-tickets' ); ?></label></td>
		<td>
			<a href="<?php echo wpas_tool_link( 'mark_tickets_private' ); ?>" class="button-secondary"><?php _e( 'Mark Private', 'as-public-tickets' ); ?></a>
			<span class="wpas-system-tools-desc"><?php _e( 'Mark all existing tickets private.', 'as-public-tickets' ); ?></span>
		</td>
	</tr>
	
	<tr>
		<td class="row-title"><label for="tablecell"><?php _e( 'Mark  All Existing Ticket REPLIES Private', 'as-public-tickets' ); ?></label></td>
		<td>
			<a href="<?php echo wpas_tool_link( 'mark_ticket_replies_private' ); ?>" class="button-secondary"><?php _e( 'Mark Replies Private', 'as-public-tickets' ); ?></a>
			<span class="wpas-system-tools-desc"><?php _e( 'Mark all existing ticket replies private.', 'as-public-tickets' ); ?></span>
		</td>
	</tr>	
<?php }


/** 
* Hook into the TOOLS script in the core plugin to execute the choice the user made
*/
add_action('execute_additional_tools', 'pbtk_execute_tools',10,2);
function pbtk_execute_tools ( $thecase ){
	
	switch ( $thecase ) {
		
		/* mark all tickets public */
		case 'mark_tickets_public';
			pbtk_mark_tickets_public_or_private('public');
			break;	
			
		/* mark all ticket relies public */
		case 'mark_ticket_replies_public';
			pbtk_mark_ticket_replies_public_or_private('public');
			break;				
			
		/* mark all tickets private */
		case 'mark_tickets_private';
			pbtk_mark_tickets_public_or_private('private');
			break;				
			
		/* mark all ticket replies private */
		case 'mark_ticket_replies_private';
			pbtk_mark_ticket_replies_public_or_private('private');
			break;							
	}
}

/** 
* Mark all tickets public or private depending on the flag passed.
*/
function pbtk_mark_tickets_public_or_private($public_or_private = 'private') {

	$args = array(
		'post_type'              => 'ticket',
		'post_status'            => 'any',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	);

	$query   = new WP_Query( $args );
	$reset = true;

	if ( 0 == $query->post_count ) {
		return false;
	}

	foreach( $query->posts as $post ) {
		update_post_meta( $post->ID, '_wpas_pbtk_flag', $public_or_private );
	}
	
	return $reset;	
}

/** 
* Mark all ticket REPLIES public or private depending on the flag passed.
*/
function pbtk_mark_ticket_replies_public_or_private($public_or_private = 'private') {
	
	$args = array(
		'post_type'              => 'ticket_reply',
		'post_status'            => 'any',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);

	$query   = new WP_Query( $args );
	$reset = true;

//error_log('The Number Below Is The Number Of Replies Returned By The wp_query call...');
//error_log( (string) $query->post_count );
//error_log('This was the SQL - there is no reason for it to be joining to postmeta here except that the hook in public tickets is doing so indiscrimnately and therefore returning //incorrect results on the 2nd pass of this function... :');
//error_log( $query->request );

	if ( 0 == $query->post_count ) {
		return false;
	}

	foreach( $query->posts as $post ) {
		update_post_meta( $post->ID, 'custom_reply_status', $public_or_private );
	}
				
	return $reset;	
}
	