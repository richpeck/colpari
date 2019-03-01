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

add_action( 'init', 'asedd_register_custom_fields' );
/**
 * Register the custom fields used to identify client order
 *
 * @since 0.1.0
 * @return void
 */
function asedd_register_custom_fields() {

	asedd_register_order_number_field();

	if ( true === asedd_is_edd_sl_active() ) {
		asedd_register_product_license_field();
	}

}

/**
 * Get order number custom fields arguments
 *
 * @since 0.1.0
 * @return array
 */
function asedd_cf_param_order_number() {

	$orders      = asedd_get_user_orders();
	
	$options     = array();
	$options[''] = __( 'Please select', 'as-edd' );

	if ( ! empty( $orders ) ) {
		foreach ( $orders as $order ) {
			
			$download_ids = array();
			
			$payment = new EDD_Payment( $order->ID );
			$downloads = $payment->get_meta()['downloads'];
			
			foreach( $downloads as $download ){
				
				$download_ids[] = $download['id'];
				
			}

			$options[$order->ID . '_|_' . implode( ",", $download_ids ) ] = "#$order->ID";
		}
	}

	$order_number_title = wpas_get_option( 'edd_as_order_number_title');
	if ( empty($order_number_title ) ) {
		$order_number_title = 'EDD Order Number';
	}
	$order_number_desc  = wpas_get_option( 'edd_as_order_number_desc');
	if ( empty($order_number_desc ) ) {
		$order_number_desc = 'The number of the order concerned by this support request';
	}	
	
	$args = apply_filters( 'as_edd_order_num_field_args', array(
		'field_type' => 'select',
		'core'       => false,
		'log'        => true,
		'capability' => 'create_ticket',
		'sanitize'   => 'sanitize_text_field',
		'title'      => $order_number_title,
		'desc'       => $order_number_desc,
		'required'   => true,
		'options'    => $options,
	) );

	return $args;

}

/**
 * Get product license custom fields arguments
 *
 * @since 0.1.0
 * @return array
 */
function asedd_cf_param_product_license() {

	$field_type = 'select';

	if ( ! is_admin() && isset( $_GET['wpas_edd_order_num'] ) ) {

		$order_num = filter_input( INPUT_GET, 'wpas_edd_order_num', FILTER_SANITIZE_NUMBER_INT );

		if ( ! empty( $order_num ) && function_exists( 'edd_software_licensing' ) ) {

			$licenses = edd_software_licensing()->get_licenses_of_purchase( $order_num );

			if ( false !== $licenses ) {

				$field_type = 'select';
				$options    = array( '' => _x( 'Please select', 'Prompt to select the download for which support is required', 'as-edd' ) );

				foreach ( $licenses as $license ) {

					$license_key = edd_software_licensing()->get_license_key( $license->ID );
					// add license status for purchase history page
                    $license_key_status = edd_software_licensing()->get_license_status( $license->ID );
					$download_id = edd_software_licensing()->get_download_id_by_license( $license_key );
					$download    = get_post( $download_id );

					if ( ! is_object( $download ) || ! is_a( $download, 'WP_Post' ) ) {
						continue;
					}
					
					// Add Download ID and order number for selection purchase history page
					if ( ! boolval( wpas_get_option( 'edd_as_show_ids_in_license_dropdown' ) ) ) {
						// License Key - Download Name - (Status)						
	                    $options[$license_key . '_|_' . $download->ID . '_|_' . $order_num] = sprintf('%s - %s (%s)', $license_key, $download->post_title, $license_key_status);
					} else {
						// License Key - Download Name - Download ID - Term ID - (Status)
						$term = get_post_meta( $download->ID, '_wpas_product_term', false );						
						$options[$license_key . '_|_' . $download->ID . '_|_' . $order_num] = sprintf('%s - %s - %s - %s - (%s)', $license_key, $download->post_title,$download->ID,@$term[0]['term_id'], $license_key_status);
					}
				}

			}

		}

	}else{
		
		$orders = asedd_get_user_orders();
		
		if ( false !== $orders ) {
			
			$field_type = 'select';
			$options    = array( '' => _x( 'Please select', 'Prompt to select the download for which support is required', 'as-edd' ) );
			
			foreach((array)$orders as $order){
			
				$licenses = edd_software_licensing()->get_licenses_of_purchase( $order->ID );
				
				if ( false !== $licenses ) {
					
					foreach ( $licenses as $license ) {
						
						$license_key = edd_software_licensing()->get_license_key( $license->ID );
						$license_key_status = edd_software_licensing()->get_license_status( $license->ID );
						$download_id = edd_software_licensing()->get_download_id_by_license( $license_key );
						$download    = get_post( $download_id );

						if ( ! is_object( $download ) || ! is_a( $download, 'WP_Post' ) ) {
							continue;
						}

						if ( ! boolval( wpas_get_option( 'edd_as_show_ids_in_license_dropdown' ) ) ) {
							// License Key - Download Name (Status)
							$options[$license_key . '_|_' . $download->ID . '_|_' . $order->ID] = sprintf('%s - %s (%s)', $license_key, $download->post_title, $license_key_status);
						} else {
							$term = get_post_meta( $download->ID, '_wpas_product_term', false );
							// License Key - Download Name - Download ID - Term ID - (Status)
							$options[$license_key . '_|_' . $download->ID . '_|_' . $order->ID] = sprintf('%s - %s - %s - %s - (%s)', $license_key, $download->post_title,$download->ID,@$term[0]['term_id'], $license_key_status);
						}

					}
					
				}
			}
		}
	}

	$license_title = wpas_get_option( 'edd_as_license_title');
	if ( empty($license_title ) ) {
		$license_title = 'Product License';
	}
	$license_desc  = wpas_get_option( 'edd_as_license_desc');
	if ( empty($license_desc ) ) {
		$license_desc = 'License key of the product you\'re requesting support for';
	}
	
	$args = apply_filters( 'as_edd_product_license_field_args', array(
		'field_type' => $field_type,
		'core'       => false,
		'required'   => true,
		'log'        => true,
		'capability' => 'create_ticket',
		'sanitize'   => 'sanitize_key',
		'required'   => true,
		'title'      => $license_title,
		'desc'       => $license_desc,
		'options'    => isset( $options ) ? $options : array()
	) );

	return $args;

}

/**
 * Register the order number custom field
 *
 * @since 0.1.0
 * @return void
 */
function asedd_register_order_number_field() {
	if ( function_exists( 'wpas_add_custom_field' ) ) {
		wpas_add_custom_field( 'edd_order_num', asedd_cf_param_order_number() );
	}
}

/**
 * Register the order number custom field
 *
 * @since 0.1.0
 * @return void
 */
function asedd_register_product_license_field() {
	if ( function_exists( 'wpas_add_custom_field' ) ) {
		wpas_add_custom_field( 'edd_product_license', asedd_cf_param_product_license() );
	}
}

add_filter( 'wpas_cf_wrapper_markup', 'asedd_hide_order_num_field', 10, 4 );
/**
 * Maybe hide the order number field
 *
 * If the order number is pre-selected, we hide the order number dropdown.
 * This is done both to simplify the user experience, but also to prevent changes
 * of the order number as the license field would not be updated upon change.
 *
 * @since 0.1.0
 *
 * @param string $markup Field markup
 * @param array  $field  Field data
 *
 * @return string
 */
function asedd_hide_order_num_field( $markup, $field ) {

	if ( is_admin() ) {
		return $markup;
	}

	if ( $field['name'] !== 'edd_order_num' ) {
		return $markup;
	}

	if ( ! isset( $_GET['wpas_edd_order_num'] ) || empty( $_GET['wpas_edd_order_num'] ) ) {
		return $markup;
	}

	$markup = str_replace( '<div', '<div style="display:none;"', $markup );

	return $markup;

}

add_filter( 'wpas_cf_display_admin_markup', 'asedd_order_num_admin_markup', 10, 3 );
/**
 * Edit the admin markup of the order number field
 *
 * @since 0.1.0
 *
 * @param $markup string Original markup
 * @param $field array Custom field data
 * @param $value string Custom field value
 *
 * @return string
 */
function asedd_order_num_admin_markup( $markup, $field, $value ) {

	if ( $field['name'] !== 'edd_order_num' ) {
		return $markup;
	}

	$field_id   = 'wpas_' . sanitize_text_field( $field['name'] );
	$order_link = esc_url( admin_url( "edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=$value" ) );
	$output     = sprintf( __( 'Related Order: %s', 'as-edd' ), "<a href='$order_link'>#$value</a>" );
	$output     .= sprintf( '<input type="hidden" name="%s" value="%s">', $field_id, $value );

	return $output;

}

add_filter( 'wpas_cf_display_admin_markup', 'asedd_product_license_admin_markup', 10, 3 );
/**
 * Edit the admin markup of the product license field
 *
 * @since 0.1.0
 *
 * @param $markup string Original markup
 * @param $field array Custom field data
 * @param $value string Custom field value
 *
 * @return string
 */
function asedd_product_license_admin_markup( $markup, $field, $value ) {

	if ( $field['name'] !== 'edd_product_license' ) {
		return $markup;
	}

	$field_id   = 'wpas_' . sanitize_text_field( $field['name'] );
	$order_link = esc_url( admin_url( "edit.php?post_type=download&page=edd-licenses&s=$value&action=-1&paged=1&action2=-1" ) );
	$output     = sprintf( __( 'Product License: %s', 'as-edd' ), "<a href='$order_link'>$value</a>" );
	$output     .= sprintf( '<input type="hidden" name="%s" value="%s">', $field_id, $value );

	return $output;

}

add_filter( 'wpas_cf_wrapper_markup', 'as_edd_hide_custom_fields_std_metabox', 10, 2 );
/**
 * Hide both custom fields from the Awesome Support Custom Fields metabox
 *
 * We display those fields in a custom EDD-related metabox
 *
 * @since 0.1.0
 *
 * @param string $markup Wrapper markup
 * @param array  $field  Field data
 *
 * @return string
 */
function as_edd_hide_custom_fields_std_metabox( $markup, $field ) {

	if ( ! is_admin() ) {
		return $markup;
	}

	if ( ! in_array( $field['name'], array( 'edd_product_license', 'edd_order_num' ) ) ) {
		return $markup;
	}

	return isset( $field['args']['fake'] ) ? $markup : '';

}