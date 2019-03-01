<?php
/**
 * HTML View for Admin Edit Order screen, showing order tickets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div class="aswc-metabox-order-content">

	<?php

	// Get current order
	$current_order = (int) $post->ID;

	$args = array(
		'post_type'              => 'ticket',
		'post_status'            => 'any',
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'         => -1,
		'no_found_rows'          => false,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'meta_key'				 => '_wpas_order',
		'meta_value'			 => $current_order,
	);

	$wpas_tickets = get_posts( $args );	

	if ( $wpas_tickets ) :

		echo '<ul>';

		foreach ( $wpas_tickets as $wpas_ticket ) { ?>

			<li><a href="<?php echo get_edit_post_link( $wpas_ticket->ID ); ?>">#<?php echo $wpas_ticket->ID; ?></a> <em>(<?php echo wpas_get_ticket_status( $wpas_ticket->ID ); ?>)</em> - <strong><?php echo $wpas_ticket->post_title; ?></strong></li>
		
		<?php }

		echo '</ul>';

		wp_reset_query();

	else:

		_e( 'No order tickets yet. ', 'awesome-support-woocommerce' );

		echo '<a href="' . admin_url( 'post-new.php?post_type=ticket&order_id=' . $current_order ) . '">Create one?</a>';

	endif; ?>

</div>