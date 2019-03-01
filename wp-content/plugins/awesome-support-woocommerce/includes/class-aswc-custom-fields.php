<?php
/**
 * Awesome Support WooCommerce Custom Fields.
 *
 * @package  WC_Awesome_Support
 * @category Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'init', 'awesome_support_woocommerce_register_field' );
/**
 * Register the order field
 *
 * @since  1.0.0
 * @return void
 */
function awesome_support_woocommerce_register_field() {

	$args = array(
		'core'                  => false,
		'field_type'            => 'select',
		'show_column'           => true,
		'log'                   => true,
		'column_callback'       => 'aswc_field_order_column',
		'label'                 => __( 'Order', 'wpas' ),
		'name'                  => __( 'Order', 'wpas' ),
		'label_plural'          => __( 'Orders', 'wpas' ),
		'taxo_hierarchical'     => true,
		'update_count_callback' => 'wpas_update_ticket_tag_terms_count',
		'rewrite'               => array( 'slug' => 'order' ),
		'options'               => aswc_get_orders_list(),
	);

	wpas_add_custom_field( 'order', $args );

}

add_filter( 'wpas_get_custom_fields', 'aswc_try_prepopulate_order_number' );
/**
 * Pre-populate the order field if an order number is available
 *
 * WooCommerce updates $wp_query after init, but AS triggers the save function on init
 * which makes it impossible to get the order number when registering the field.
 *
 * If the field is registered before wp, we can't access the order number. If the field
 * is registered after init, it won't be saved upon submission. That's why we need to use
 * the custom fields filter instead and modify the already registered field.
 *
 * @since 1.0.6
 *
 * @param array $custom_fields All registered custom fields
 *
 * @return mixed
 */
function aswc_try_prepopulate_order_number( $custom_fields ) {

	global $wp_query;

	$order_id = isset( $wp_query->query['view-order'] ) ? $wp_query->query['view-order'] : '';

	if ( $order_id && isset( $custom_fields['order'] ) ) {
		$custom_fields['order']['args']['field_type'] = 'text';
		$custom_fields['order']['args']['default']    = $order_id;
	}

	return $custom_fields;

}

/**
 * Get the list of all orders for a specific customer.
 * Only get the last 250 of filter with 'awesome_support_woocommerce_order_count'.
 *
 * @since 1.0.8
 * @return array
 */
function aswc_get_orders_list() {
	$orders      = array();
	$customer_id = false;
	
	if ( is_admin() ) {
		$post_id = isset( $_GET['post'] ) && isset( $_GET['action'] ) ? (int) $_GET['post'] : 0;
		if ($post_id) {
			$customer_id = intval( get_post_field( 'post_author', $post_id ) );
		} else {
			/**
			 * If it's the admin and a new ticket, and an order ID has been provided, get that order.
			 */
			if ( isset( $_GET['order_id'] ) && $_GET['order_id'] ) {
				$order_id = (int) $_GET['order_id'];

				// Get order using 3.0 functionality
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$orders[$order_id] = aswc_format_order_label( $order );
				}
			}
		}
	} else {
		$customer_id = intval( get_current_user_id() );
	}

	if ( apply_filters( 'awesome_support_woocommerce_order_general_field', false ) ) {
		$orders['general'] = __( 'General', 'awesome-support-woocommerce' );
	}

	$number_orders = apply_filters( 'awesome_support_woocommerce_order_count', 250 );
	$customer_orders = aswc_get_customer_orders( $customer_id, false, $number_orders );

	$allowed_statuses = apply_filters( 'aswc_help_allowed_statuses', array( 'completed', 'processing', 'on-hold' ) );

	if ( $customer_id ) {
		if ( false === ( $orders = get_transient( 'aswc_customer_orders_' . $customer_id . '_formatted' ) ) ) {
			foreach ( $customer_orders as $customer_order ) {
				$order = wc_get_order( method_exists( $customer_order, 'get_id' ) ? $customer_order->get_id() : $customer_order->ID );

				if ( in_array( $order->get_status(), $allowed_statuses ) ) {
					$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
					$orders[$order_id] = aswc_format_order_label( $order );
				}
			}

			// Cache
			set_transient( 'aswc_customer_orders_' . $customer_id . '_formatted', $orders, 1 * HOUR_IN_SECONDS );
		}
	}

	return $orders;
}

/**
 * Format a label for ASWC to display in the order select.
 */
function aswc_format_order_label( $order ) {
	$order_number = $order->get_order_number();
	$order_number = preg_replace( '/#([\w-]+)/i', '$1', $order_number );
	$order_date   = method_exists( $order, 'get_date_created' ) ? $order->get_date_created()->format( get_option( 'date_format' ) ) : date( get_option( 'date_format' ), strtotime( $order->order_date ) );
	$order_label  = sprintf( __( 'Order #%s - %s', 'awesome-support-woocommerce' ), $order_number, $order_date );

	return $order_label;
}

/**
 * Custom Field - Order - Field Callback
 */

function aswc_field_order( $field ) {

	if ( isset( $post ) ) {
		$post_id = $post->ID;
	} elseif ( isset( $_GET['post'] ) ) {
		$post_id = intval( $_GET['post'] );
	} else {
		$post_id = false;
	}

	/**
	 * Due to the order being displayed through a custom endpoint, we need to get the
	 * ORDER ID by accessing $wp_query. Ideally we will find a better way to do this in
	 * the future. If you have changed the view-order endpoint, this probably won't work.
	 */

	global $wp_query;

	$order_id    = isset( $wp_query->query['view-order'] ) ? $wp_query->query['view-order'] : '';
	$field_id    = 'wpas_' . $field['name'];
	$value       = wpas_get_cf_value( $field_id, $post_id );
	$label       = wpas_get_field_title( $field );
	$field_class = isset( $field['args']['field_class'] ) ? $field['args']['field_class'] : ''; ?>

	<div <?php wpas_get_field_container_class( $field_id ); ?> id="<?php echo $field_id; ?>_container">

		<?php if ( ! is_admin() || current_user_can( $field['args']['capability'] ) ) : ?>
			
			<?php if ( $order_id ) { ?>
				<input type="hidden" name="<?php echo $field_id; ?>" value="<?php echo $order_id; ?>" />
			<?php } else {

				$customer_id = is_admin() ? get_post_field( 'post_author', get_the_ID() ) : get_current_user_id();

				$customer_orders = get_posts( array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => $customer_id,
					'post_type'   => wc_get_order_types( 'view-orders' ),
					'post_status' => array_keys( wc_get_order_statuses() )
				) );

				if ( $customer_orders ) : ?>
					<label style="display:block;">Related Customer Order</label>
					<select id="order_select" name="<?php echo $field_id; ?>">

					<?php
					if ( apply_filters( 'awesome_support_woocommerce_order_general_field', false ) ) {
						echo '<option value="general">' . __( 'General', 'awesome-support-woocommerce' ) . '</option>';
					}
					?>

					<?php
					foreach ( $customer_orders as $customer_order ) {
						$order      = wc_get_order( method_exists( $customer_order, 'get_id' ) ? $customer_order->get_id() : $customer_order->ID );
						if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
							$order->populate( $customer_order );
						}
						$order_id	= method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
						$order_number 	= $order->get_order_number();
						$order_number 	= preg_replace( '/#([\w-]+)/i', '$1', $order_number );
						$order_date     = method_exists( $order, 'get_date_created' ) ? $order->get_date_created()->format( get_option( 'date_format' ) ) : date( get_option( 'date_format' ), strtotime( $order->order_date ) );

						echo '<option value="' . $order_id . '">';
							echo __( 'Order #', 'awesome-support-woocommerce' ) . $order_number . ' - ' . $order_date;
						echo '</option>';

					} ?>

					</select>

				<?php endif;

			}

		endif; ?>

	</div>

<?php }

/**
 * Custom Field - Order - Column Content Callback
 */
function aswc_field_order_column( $name, $post_id ) {

	$value = wpas_get_cf_value( $name, $post_id );
	$value = preg_replace( '/#([\w-]+)/i', '$1', $value );
	$order = wc_get_order( $value );

	if ( $value ) {
		if ( $value == 'general' ) {
			echo __( 'General', 'awesome-support-woocommerce' );
		}
		if ( $order ) {
			echo '<a href="';
			if ( is_admin() ) {
				echo is_null( get_edit_post_link( $value ) ) ? '' : get_edit_post_link( $value );
			} else {
				echo $order->get_view_order_url();
			}
			echo '">#' . $order->get_order_number() . '</a>';
		}
	} else {
		echo '-';
	}

}

add_filter( 'wpas_cf_wrapper_markup', 'aswc_maybe_hide_field_wrapper', 10, 4 );
/**
 * Maybe hide the field by setting the wrapper to display:none
 *
 * If an order number is found then we don't need to ask the client
 * to choose in the list, we pre select it and hide the field
 *
 * @since 1.0.6
 *
 * @param string $wrapper
 * @param array  $field
 * @param string $wrapper_class
 * @param string $wrapper_id
 *
 * @return string
 */
function aswc_maybe_hide_field_wrapper( $wrapper, $field, $wrapper_class, $wrapper_id ) {

	if ( 'order' !== $field['name'] ) {
		return $wrapper;
	}

	global $wp_query;

	$order_id = isset( $wp_query->query['view-order'] ) ? $wp_query->query['view-order'] : '';

	if ( $order_id ) {
		$wrapper = str_replace( '<div', '<div style="display:none;"', $wrapper );
	}

	return $wrapper;

}