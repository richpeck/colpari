<?php
/**
 * The search-form template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<form role="search" class="searchform fusion-search-form" method="get" action="<?php echo esc_url_raw( home_url( '/' ) ); ?>">
	<div class="fusion-search-form-content">
		<div class="fusion-search-field search-field">
			<label><span class="screen-reader-text"><?php esc_attr_e( 'Search for:', 'Avada' ); ?></span>
				<input type="text" value="" name="s" class="s" placeholder="<?php esc_html_e( 'Search ...', 'Avada' ); ?>" required aria-required="true" aria-label="<?php esc_html_e( 'Search ...', 'Avada' ); ?>"/>
			</label>
		</div>
		<div class="fusion-search-button search-button">
			<input type="submit" class="fusion-search-submit searchsubmit" value="&#xf002;" />
		</div>
	</div>
</form>
