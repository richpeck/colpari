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

/**
 * Check whether or not the Software Licensing addon for EDD is active
 *
 * @since 0.1.0
 * @return bool
 */
function asedd_is_edd_sl_active() {
	return defined( 'EDD_SL_VERSION' ) ? true : false;
}

/**
 * Register JavaScript file for handling licence key matching with products on 
 * the ticket view only if the Software Licensing addon for EDD is active.
 */
if( asedd_is_edd_sl_active() ) {
    add_action( 'wp_enqueue_scripts', 'asedd_enqueue_submit_ticket_assets' );
    
    // register the AJAX endpoint
    add_action( 'wp_ajax_asedd_get_product_from_license', 'asedd_get_product_by_license' );
	add_action( 'wp_ajax_asedd_get_license_from_term', 'asedd_get_license_by_term' );
}

/**
 * Register JS files with WordPress for the Submit Ticket page.
 */
function asedd_enqueue_submit_ticket_assets() {
    // limit to which pages the JS files are added to
    $ticketPage = wpas_get_option( 'ticket_submit' );
    if( get_the_ID() == $ticketPage[0] ){
		
		// enqueue style
		wp_enqueue_style( 'asedd_submit_ticket', AS_EDD_URL . 'assets/css/style.css' );
		
        // enqueue scripts
        wp_enqueue_script( 'jquery_throttle_debounce', AS_EDD_URL . 'assets/js/vendor/jquery.ba-throttle-debounce.min.js', array( 'jquery' ), AS_EDD_VERSION );
        wp_enqueue_script( 'asedd_submit_ticket', AS_EDD_URL . 'assets/js/submit-ticket.js', array( 'jquery' ), AS_EDD_VERSION );
        
        // add AJAX endpoint and set a global JS object so they can be accesed from JS
        wp_localize_script( 'asedd_submit_ticket' , 'AS_EDD', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'productFromLicenceAction' => 'asedd_get_product_from_license',
			'LicenceFromTermAction' => 'asedd_get_license_from_term'
        ) );
    }
}

function asedd_get_license_by_term(){
	$term_id = filter_input( INPUT_GET, 'termId', FILTER_SANITIZE_STRING );
	
	$term = get_term( $term_id );
	
	echo json_encode(array(
        'productId' => $term === false ? false : $term->name
    ));
	
	wp_die();
}

/**
 * Returns a product ID depending on the licence key.
 */
function asedd_get_product_by_license() {
    $license_key = filter_input( INPUT_GET, 'licenseKey', FILTER_SANITIZE_STRING );
    
    $download_id = edd_software_licensing()->get_download_id_by_license( $license_key );

    // the form uses AS term ID's to select product, so we get it for this download
    $term = get_post_meta( $download_id, '_wpas_product_term', false );
    
    echo json_encode(array(
        'productId' => $term === false ? false : $term[0]['term_id']
    ));
    
    // gracefull exit WP
    wp_die();
}

add_filter( 'wpas_before_submit_new_ticket_checks', 'asedd_check_license_key', 10, 1 );
/**
 * Check if the license number is filled and if it corresponds to a product
 *
 * @param $go
 *
 * @return bool|WP_Error
 */
function asedd_check_license_key( $go ) {

	// If $go is already blocking the submission process we don't need to run our checks.
	// Let's return $go directly instead in order not to override another addon's potential error message.
	if ( true !== $go ) {
		return $go;
	}

	if ( false === asedd_is_edd_sl_active() ) {
		return $go;
	}

	$product_id  = filter_input( INPUT_POST, 'wpas_product', FILTER_SANITIZE_NUMBER_INT );
	$license_key = filter_input( INPUT_POST, 'wpas_edd_product_license', FILTER_SANITIZE_STRING );
	$purchase_id = filter_input( INPUT_POST, 'wpas_edd_order_num', FILTER_SANITIZE_STRING );
	$license     = edd_software_licensing()->get_license_by_key( trim( $license_key ) );
	
	//Prepare the empty order number error
	if(empty($purchase_id)){
		return asedd_add_error( $go, 'as_edd_invalid_order', __( 'The order number you selected is invalid.', 'as-edd' ) );
	}
	
	// Prepare the "invalid license" error
	$purchase_history_uri  = esc_url( get_permalink( edd_get_option( 'purchase_history_page', '' ) ) );
	$licenses_list_uri     = esc_url( add_query_arg( array(
		'action'     => 'manage_licenses',
		'payment_id' => $purchase_id
	), $purchase_history_uri ) );
	$invalid_license_error = sprintf( "<a href='$licenses_list_uri'>%s</a>", __( 'Click here to see your license keys for this purchase', 'as-edd' ) );

	if ( false === $license ) {
		return asedd_add_error( $go, 'as_edd_invalid_license', __( 'The license key you entered does not exist.', 'as-edd' ) . " $invalid_license_error" );
	}

	// Prepare the license arguments.
	$license_args = array( 
        'key' => trim( $license_key ), 
        // #14331: forces to skip URL validation for licence, as we don't need it for tickets
        'url' => 'localhost' 
    );

	if ( true === (bool) wpas_get_option( 'support_products', false ) ) {
		if ( empty( $product_id ) ) {
			// If AS is in multi-products mode and no product ID is passed then there is a problem somewhere.
			return asedd_add_error( $go, 'as_edd_missing_product_id', __( 'There was no product selected. You must specify the product that matches the license key.', 'as-edd' ) . " $invalid_license_error" );
		} else {

			// Check if the given product ID does, indeed, match a download.
			$product = asedd_is_product_download( $product_id );

			if ( ! is_object( $product ) || ! is_a( $product, 'WP_Post' ) ) {
				// If the given product ID doesn't match an actual download we abort the process.
				return asedd_add_error( $go, 'as_edd_invalid_item_id', __( 'The product ID passed is not an actual download.', 'as-edd' ) . " $invalid_license_error" );
			} else {
				// Set the "real" product ID instead of the term ID.
				$product_id = $product->ID;
			}
		}
	} else {

		// In single product mode we need to fetch a product ID as EDD SL requires it for license check.
		$product_id = edd_software_licensing()->get_download_id_by_license( $license_key );

		// We also query the download WP_Post object as it may be used later on during the checks.
		$product = get_post( $product_id );
	}

	// Finally we add the product ID to the license args for validation.
	$license_args['item_id'] = $product_id;

	// Get the license status.
	$license_status = edd_software_licensing()->check_license( $license_args );

	// If the status is valid no need for additional verifications. Just give the go ahead.
	if ( 'valid' === $license_status ) {
		return $go;
	}
	if ( true === boolval( wpas_get_option( 'edd_as_inactive_license_is_valid') )  && 'inactive' === $license_status ) {
		// We're treating inactive licenses as valid.
		return $go;
	}
	if ( true === boolval( wpas_get_option( 'edd_as_invalid_product_is_valid') )  && 'invalid_item_id' === $license_status ) {
		// We're treating invalid item ids as valid.
		return $go;
	}
	if ( true === boolval( wpas_get_option( 'edd_as_expired_license_is_valid') )  && 'expired' === $license_status ) {
		// We're treating expired licenses as valid.
		return $go;
	}
	

	switch ( $license_status ) {

		case 'invalid':
			$error = asedd_add_error( $go, 'as_edd_invalid_license', __( 'The license key you provided is invalid', 'as-edd' ) . " $invalid_license_error" );
			break;

		case 'invalid_item_id':
			$error = asedd_add_error( $go, 'as_edd_wrong_product', __( 'The license key you provided is for a different product', 'as-edd' ) . " $invalid_license_error" );
			break;

		case 'expired':

			$renewal_link = apply_filters( 'edd_sl_renewal_link', edd_get_checkout_uri( array(
				'edd_license_key' => $license_key,
				'download_id'     => $product->ID
			) ) );

			$error = asedd_add_error( $go, 'as_edd_wrong_product', sprintf( __( 'The license key you provided is expired. <a href="%s">Click here to renew it</a>.', 'as-edd' ), $renewal_link ) );
			break;

		case 'inactive':
			$error = asedd_add_error( $go, 'as_edd_wrong_product', __( 'The license key you provided is inactive', 'as-edd' ) );
			break;

		case 'disabled':
			$error = asedd_add_error( $go, 'as_edd_wrong_product', __( 'The license key you provided was disabled', 'as-edd' ) );
			break;

		default:
			$error = asedd_add_error( $go, 'as_edd_unknown_error', __( 'The license key you provided could not be validated', 'as-edd' ) );
			break;

	}

	return $error;
    
}