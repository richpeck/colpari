<?php
/**
 * HTML View for Admin Edit Ticket screen, showing customer information / detail
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $current_user;

/* Issuer metadata */
$issuer = get_userdata( $post->post_author );

/* Issuer ID */
$issuer_id = intval( $issuer->data->ID );

/* Issuer name */
$issuer_name = $issuer->data->display_name;

/* Issuer tickets link */
$issuer_tickets_url = admin_url( add_query_arg( array( 'post_type' => 'ticket', 'author' => $issuer_id ), 'edit.php' ) );

/* Issuer order */
$current_order 			= wpas_get_cf_value( 'order', $post->ID );
if ( $current_order ) {
	$current_order_object 	= wc_get_order( $current_order );
	if ( $current_order_object ) {
		$current_order_number 	= $current_order_object->get_order_number();
		$current_order_number 	= preg_replace( '/#([\w-]+)/i', '$1', $current_order_number );
	}
}

/**
 * No issuer order - let's try do a customer lookup based on email
 */
if ( ! $current_order ) {
	$orders = aswc_get_customer_orders( $issuer_id );
}

?>

<div class="aswc-metabox-ticket-content">

	<div class="group">

		<div class="user-details">
			<ul>
		        <li class="name"><a href="<?php echo esc_url( admin_url() . 'user-edit.php?user_id=' . $issuer_id ); ?>"><strong><?php echo $issuer_name; ?></strong></a></li>
		        <li class="country"><?php if ( get_user_meta( $issuer_id, 'billing_country', true ) ) { echo WC()->countries->countries[ get_user_meta( $issuer_id, 'billing_country', true ) ]; } ?></li>
		        <li>
		        <?php
		        	if ( $current_order || count( $orders ) > 0 ) {
		        		echo '<span class="is-customer">' . __( 'Customer', 'awesome-support-woocommerce' ) . '</span>';
		        	} else {
		        		echo '<span class="is-customer not">' . __( 'Not Customer', 'awesome-support-woocommerce' ) . '</span>';
		        	}
		        ?>
		        </li>
			</ul>
		</div>

		<div class="user-photo">
			<a href="<?php echo esc_url( admin_url() . 'user-edit.php?user_id=' . $issuer_id ); ?>"><?php echo get_avatar( $issuer_id ); ?></a>
		</div>

	</div>

    <h4>Stats</h4>
    <ul>
        <li>Total Orders: <strong><?php echo number_format( wc_get_customer_order_count( $issuer_id ) ); ?></strong></li>
        <li>Lifetime Value: <strong><?php echo wc_price( wc_get_customer_total_spent( $issuer_id ) ); ?></strong></li>
        <?php
            $last_order = wc_get_customer_last_order( $issuer_id );
            if ($last_order) {
                $order_number = $last_order->get_order_number();
				$order_number = preg_replace( '/#([\w-]+)/i', '$1', $order_number ); ?>
                <li>Last Order: <strong><a href="<?php echo get_edit_post_link( $last_order->id ); ?>">Order #<?php echo $order_number; ?></a></strong></li>
            <?php }
        ?>
    </ul>

	<?php if ( $current_order && $current_order_object ) { ?>

		<h4><?php _e( 'This Order', 'awesome-support-woocommerce' ); ?> (<a href="<?php echo get_edit_post_link( $current_order ); ?>">#<?php echo $current_order_number; ?></a>)</h4>
		
		<?php
			$order = wc_get_order( $current_order );
			if ( sizeof( $order->get_items() ) > 0 ) {

				echo '<ul>';

				foreach( $order->get_items() as $item ) {
					$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
					$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
					?>

					<li>
					
						<td class="product-name">
							<?php
								if ( $_product && ! $_product->is_visible() ) {
									echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );
								} else {
									echo apply_filters( 'woocommerce_order_item_name', sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item );
								}

								echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );
							?>
						</td>
						<td class="product-total">
							<?php echo $order->get_formatted_line_subtotal( $item ); ?>
						</td>
					
					</li>

					<?php
				}

				echo '</ul>';
			}

			$orders = aswc_get_customer_orders( $issuer_id, $current_order );
	}

	if ( $orders ) { ?>
        <h4>
        	<?php if ( $current_order ) {
        		_e( 'Other Orders', 'awesome-support-woocommerce' );
        	} else {
        		_e( 'Orders', 'awesome-support-woocommerce' );
        	} ?>
		</h4>			

		<ul>

			<?php
			foreach ( $orders as $customer_order ) {
				$order      	= wc_get_order( method_exists( $customer_order, 'get_id' ) ? $customer_order->get_id() : $customer_order->ID );
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$order->populate( $customer_order );
				}
				$order_id		= method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				$order_number 	= $order->get_order_number();
				$order_number 	= preg_replace( '/#([\w-]+)/i', '$1', $order_number );
				$order_date     = method_exists( $order, 'get_date_created' ) ? $order->get_date_created()->format( get_option( 'date_format' ) ) : date( get_option( 'date_format' ), strtotime( $order->order_date ) );
				$order_url  	= admin_url( 'post.php?post=' . $order_id . '&action=edit' );

				echo '<li><a href="' . $order_url . '">#' . $order_number . '</a> - ' . $order_date . '</li>';

			} ?>

		</ul>

	<?php } ?>

	<?php
	// Get current ticket to exclude
	$current_ticket = array( $post->ID );

	$args = array(
		'post_type'              => 'ticket',
		'post_status'            => 'any',
		'post__not_in'			 => $current_ticket,
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'         => -1,
		'author__in'             => array( $issuer_id ),
	);

	$wpas_tickets = get_posts( $args );	

	if ( $wpas_tickets ) { ?>

		<h4><?php _e( 'Other Tickets', 'awesome-support-woocommerce' ); ?> (<a href="<?php echo $issuer_tickets_url; ?>"><?php _e( 'View All', 'awesome-support-woocommerce' ); ?></a>)</h4>

		<ul>
			<?php foreach ( $wpas_tickets as $wpas_ticket ) { ?>
				<li><a href="<?php echo get_edit_post_link( $wpas_ticket->ID ); ?>">#<?php echo $wpas_ticket->ID; ?></a> <em>(<?php echo wpas_get_ticket_status( $wpas_ticket->ID ); ?>)</em> - <strong><?php echo $wpas_ticket->post_title; ?></strong></li>
			<?php } ?>
		</ul>

		<?php wp_reset_query(); ?>

	<?php } ?>

</div>