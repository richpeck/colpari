<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<h2 id="start-conversation"><?php _e( 'Need Help?', 'awesome-support-woocommerce' ); ?></h2>

<p><?php echo apply_filters( 'awesome_support_woocommerce_conversation_form_description', __( 'Do you have a query about your order, or need a hand with getting your products set up? If so, please fill in the form below.', 'awesome-support-woocommerce' ) ); ?></p>

<?php do_action( 'awesome_support_woocommerce_conversation_form_start' ); ?>

<?php echo do_shortcode( '[ticket-submit]' ); ?>

<?php do_action( 'awesome_support_woocommerce_conversation_form_end' ); ?>