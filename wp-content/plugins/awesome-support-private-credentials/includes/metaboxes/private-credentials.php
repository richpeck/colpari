<?php
/*
 * @package   Awesome Support: Private Credentials
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */

$count = $this->init_credentials();

?>

<div>

	<a href="#wpas-pc-modal"
		class="wpas-btn wpas-btn-default  wpas-pc-load" data-view="default" data-post-id="<?php echo $this->post_id; ?>" title="<?php _e( 'Private Credentials', 'wpas-pc' ) ?>">

		<?php
		if ( $count > 0 ) {
			_e( 'View Private Credentials', 'wpas-pc' );
			echo sprintf( ' (%d)', $count );
		} else {
			_e( 'Enter Private Credentials', 'wpas-pc' );
		}

		?>
	</a>

</div>