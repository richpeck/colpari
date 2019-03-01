<?php
/**
 * Clone slide button template.
 *
 * @package Fusion-Slider
 * @subpackage Templates
 * @since 1.0.0
 */

?>
<div id="fusion-slide-clone">
	<?php
	$post_id = 0; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	if ( isset( $_GET['post'] ) ) {
		// @codingStandardsIgnoreLine WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$post_id = wp_unslash( $_GET['post'] ); // WPCS: sanitization ok.
	}
	?>
	<a href="<?php echo esc_url_raw( $this->get_slide_clone_link( $post_id ) ); ?>" class="button">
		<?php esc_attr_e( 'Clone this slide', 'fusion-core' ); ?>
	</a>
</div>
