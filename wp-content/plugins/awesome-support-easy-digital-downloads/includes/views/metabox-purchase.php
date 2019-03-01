<?php
/**
 * @package   Awesome Support Easy Digital Downloads
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $post;
global $pagenow;

if ( empty( $post ) ) {
	return;
}
/* @var $customer EDD_Customer */
$customer = new EDD_Customer( $post->post_author, true );

/**
 * Display the custom fields
 */
?><div class="wpas-custom-fields"><?php

$order_num_args         = asedd_cf_param_order_number();
$order_num_args['fake'] = true;
$order_num              = new WPAS_Custom_Field( 'edd_order_num', array(
	'name' => 'edd_order_num',
	'args' => $order_num_args
) );

echo $order_num->get_output();

if ( asedd_is_edd_sl_active() ) {
	$license_key_args         = asedd_cf_param_product_license();
	$license_key_args['fake'] = true;
	$license_key              = new WPAS_Custom_Field( 'edd_product_license', array(
		'name' => 'edd_product_license',
		'args' => $license_key_args
	) );

	echo $license_key->get_output();
}

asedd_refund_policy_status();

// only show on view/edit ticket page
if( $pagenow != 'post-new.php' ){
    /**
     * Display a link to the customers EDD profile.
     */ ?>
    <div class="wpas-form-group"><?php
        echo __( 'Customer Profile:', 'as-edd' ); ?> 
        <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id='.$customer->id ); ?>"
           class="as-edd-customer-profile-link">
            <?php echo $customer->name; ?>
        </a>
    </div>

    <?php 
    /**
     * Display customer lifetime value.
     */ ?>
    <div class="wpas-form-group"><?php
        echo __( 'Customer Liefetime Value:', 'as-edd' ); ?> 
        <?php echo edd_currency_filter( edd_format_amount( $customer->purchase_value ) ); ?>
    </div><?php
} ?>
</div>

<?php

$purchase_id  = (int) get_post_meta( $post->ID, '_wpas_edd_order_num', true );
$cart_details = edd_get_payment_meta_cart_details( $purchase_id );

if ( empty( $cart_details ) ) {
	return;
}

printf( '<hr><h4>%s</h4>', __( 'Purchased Products', 'as-edd' ) );
echo '<ul>';

foreach ( $cart_details as $item ) {
	$price_id = isset( $item['item_number']['options']['price_id'] ) ? (int) $item['item_number']['options']['price_id'] : false;
	printf( '<li><a href="%s">%s</a> x %d (%s) </li>', esc_url( get_permalink( $item['id'] ) ), $item['name'], (int) $item['quantity'], edd_price( (int) $item['id'], false, $price_id ) );
}

printf( '<li><strong>%s: %s</strong></li>', esc_html_x( 'Order Total', 'Total amount of the EDD order', 'as-edd' ), edd_payment_amount( $purchase_id ) );
echo '</ul>';