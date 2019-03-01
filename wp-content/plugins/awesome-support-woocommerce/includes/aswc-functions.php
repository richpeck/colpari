<?php
/**
 * Helper functions for Awesome Support WooCommerce.
 */

function aswc_get_customer_orders( $id, $not_id = false, $number_orders = 10 ) {
	if ( false === ( $orders = get_transient( 'aswc_customer_orders_' . $id ) ) ) {
		// WC 2.7 CRUD compat
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			// get_posts args
			$args = array(
				'numberposts' => $number_orders,
				'meta_key'    => '_customer_user',
				'meta_value'  => $id,
				'post_type'   => 'shop_order',
			);

			// Any order to exclude?
			if ( $not_id ) {
				$args['post__not_in'] = array( intval( $not_id ) );
			}

			// Get orders with get_posts
			$orders = get_posts( $args );
		} else {
			// Get orders using 3.0 functionality
			$orders = wc_get_orders( array( 
				'customer' => $id, 
				'limit'    => $number_orders,
				'status'   => 'any',
			) );
		}

		// Cache
		set_transient( 'aswc_customer_orders_' . $id, $orders, 1 * HOUR_IN_SECONDS );
	}

	// Return orders
	return $orders;
}