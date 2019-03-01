<?php
/**
 * Plugins Admin page.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

if ( ! function_exists( 'get_plugins' ) ) {
	require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugins                         = Avada_TGM_Plugin_Activation::$instance->plugins; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
$installed_plugins               = get_plugins();
$wp_api_plugins                  = get_site_transient( 'fusion_wordpress_org_plugins' );
$required_and_recommened_plugins = avada_get_required_and_recommened_plugins();

if ( ! function_exists( 'plugins_api' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // For plugins_api.
}
if ( ! $wp_api_plugins ) {
	$wp_org_plugins = array(
		'woocommerce'         => 'woocommerce/woocommerce.php',
		'wordpress-seo'       => 'wordpress-seo/wp-seo.php',
		'bbpress'             => 'bbpress/bbpress.php',
		'the-events-calendar' => 'the-events-calendar/the-events-calendar.php',
		'contact-form-7'      => 'contact-form-7/wp-contact-form-7',
	);
	$wp_api_plugins = array();
	foreach ( $wp_org_plugins as $slug => $path ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$wp_api_plugins[ $slug ] = (array) plugins_api(
			'plugin_information',
			array(
				'slug' => $slug,
			)
		);
	}
	set_site_transient( 'fusion_wordpress_org_plugins', $wp_api_plugins, 15 * MINUTE_IN_SECONDS );
}
?>
<div class="wrap about-wrap avada-wrap">
	<?php $this->get_admin_screens_header( 'plugins' ); ?>
	<?php add_thickbox(); ?>
	 <div class="avada-important-notice">
		<p class="about-description">
			<?php /* translators: link attributes. */ ?>
			<?php $premium_plugins_string = sprintf( __( 'Fusion White Label Branding, Convert Plus, ACF Pro, Slider Revolution & Layer Slider are premium plugins that can be installed once your <a %1$s>product is registered</a>.', 'Avada' ), 'href="' . esc_url_raw( admin_url( 'admin.php?page=avada-registration' ) ) . '"' ); ?>
			<?php if ( defined( 'ENVATO_HOSTED_SITE' ) && ENVATO_HOSTED_SITE ) : ?>
				<?php $premium_plugins_string = __( 'Fusion White Label Branding, Convert Plus, ACF Pro, Slider Revolution & Layer Slider are premium plugins included in Avada.', 'Avada' ); ?>
			<?php endif; ?>
			<?php if ( false !== get_option( 'avada_previous_version' ) ) : ?>
				<?php /* translators: %1$s: Premium plugins info. %2$s: URL. */ ?>
				<?php printf( __( 'Fusion Core and Fusion Builder are our premium plugins required to use Avada. Fusion Builder can only be installed after Fusion Core is updated to version 3.0 or higher. %1$s Before updating premium plugins, please always make sure Avada is on the latest available version. The other plugins below offer design integration with Avada. You can manage the plugins from this tab. <a href="%2$s" target="_blank"> Subscribe to our newsletter</a> to be notified about new products coming in the future!', 'Avada' ), $premium_plugins_string, 'http://theme-fusion.us2.list-manage2.com/subscribe?u=4345c7e8c4f2826cc52bb84cd&id=af30829ace' ); // WPCS: XSS ok. ?>
			<?php else : ?>
				<?php /* translators: %1$s: Premium plugins info. %2$s: URL. */ ?>
				<?php printf( __( 'Fusion Core and Fusion Builder are our premium plugins required to use Avada. %1$s Before updating premium plugins, please always make sure Avada is on the latest available version. The other plugins below offer design integration with Avada. You can manage the plugins from this tab. <a href="%2$s" target="_blank">Subscribe to our newsletter</a> to be notified about new products coming in the future!', 'Avada' ), $premium_plugins_string, 'http://theme-fusion.us2.list-manage2.com/subscribe?u=4345c7e8c4f2826cc52bb84cd&id=af30829ace' ); // WPCS: XSS ok. ?>
			<?php endif; ?>
		</p>
	</div>
	<?php if ( ! Avada()->registration->is_registered() ) : ?>
		<div class="avada-important-notice" style="border-left: 4px solid #dc3232;">
			<h3 style="color: #dc3232; margin-top: 0;"><?php esc_html_e( 'Premium Plugins Can Only Be Installed and Updated With A Valid Token Registration', 'Avada' ); ?></h3>
			<?php /* translators: The "Product Registration" link. */ ?>
			<p><?php printf( esc_html__( 'Please visit the %s page and enter a valid token to install or update the premium plugins: Convert Plus, ACF Pro, Slider Revolution & Layer Slider.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada-registration' ) ) . '">' . esc_html__( 'Product Registration', 'Avada' ) . '</a>' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $plugins ) ) : ?>
		<div class="avada-important-notice" style="border-left: 4px solid #dc3232;">
			<h3 style="color: #dc3232; margin-top: 0;"><?php esc_html_e( 'The Plugin Server Could Not Be Reached', 'Avada' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %1$s = System Status text & link. %2$s: Plugin Installation text & link. %3$s: Support Dashboard text & link. */
					esc_attr__( 'Please check on the %1$s page if wp_remote_get() is working. For more information you can check our documentation of the %2$s. If the issue persists, you can also get the plugins through our alternate method directly from the %3$s.', 'Avada' ),
					'<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada-system-status' ) ) . '" target="_blank">' . esc_attr__( 'System Status', 'Avada' ) . '</a>',
					'<a href="https://theme-fusion.com/documentation/avada/install-update/plugin-installation/" target="_blank">' . esc_attr__( 'Plugin Installation', 'Avada' ) . '</a>',
					'<a href="https://theme-fusion.com/documentation/avada/getting-started/support-desk/" target="_blank">' . esc_attr__( 'Support Dashboard', 'Avada' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<div id="avada-install-plugins" class="avada-demo-themes avada-install-plugins">
		<div class="feature-section theme-browser rendered">

			<?php foreach ( $plugins as $plugin ) : ?>
				<?php
				if ( ! isset( $plugin['AuthorURI'] ) ) {
					$plugin['AuthorURI'] = '#';
				}
				if ( ! isset( $plugin['Author'] ) ) {
					$plugin['Author'] = '';
				}
				if ( ! array_key_exists( $plugin['slug'], $required_and_recommened_plugins ) ) {
					continue;
				}

				$class         = '';
				$plugin_status = '';
				$file_path     = $plugin['file_path'];
				$plugin_action = $this->plugin_link( $plugin );

				// We have a repo plugin.
				if ( ! $plugin['version'] ) {
					$plugin['version'] = Avada_TGM_Plugin_Activation::$instance->does_plugin_have_update( $plugin['slug'] );
				}

				if ( is_plugin_active( $file_path ) ) {
					$plugin_status = 'active';
					$class         = 'active';
				}

				if ( isset( $plugin_action['update'] ) && $plugin_action['update'] ) {
					$class .= ' update';
				}

				$required_premium = '';
				?>
				<div class="fusion-admin-box">
					<div class="theme <?php echo esc_attr( $class ); ?>">
						<div class="theme-wrapper">
							<div class="theme-screenshot">
								<img src="<?php echo esc_url_raw( $plugin['image'] ); ?>" alt="" />
							</div>
							<?php if ( isset( $plugin_action['update'] ) && $plugin_action['update'] ) : ?>
								<div class="update-message notice inline notice-warning notice-alt">
									<?php /* translators: Version number. */ ?>
									<p><?php printf( esc_html__( 'New Version Available: %s', 'Avada' ), esc_html( $plugin['version'] ) ); ?></p>
								</div>
							<?php endif; ?>
							<h3 class="theme-name">
								<?php if ( 'active' === $plugin_status ) : ?>
									<?php /* translators: plugin name. */ ?>
									<span><?php printf( esc_html__( 'Active: %s', 'Avada' ), esc_html( $plugin['name'] ) ); ?></span>
								<?php else : ?>
									<?php echo esc_html( $plugin['name'] ); ?>
								<?php endif; ?>
								<div class="plugin-info">
									<?php if ( isset( $installed_plugins[ $plugin['file_path'] ] ) ) : ?>
										<?php /* translators: %1$s: Plugin version. %2$s: Author URL. %3$s: Author Name. */ ?>
										<?php printf( __( 'v%1$s | <a href="%2$s" target="_blank">%3$s</a>', 'Avada' ), esc_html( $installed_plugins[ $plugin['file_path'] ]['Version'] ), esc_url_raw( $installed_plugins[ $plugin['file_path'] ]['AuthorURI'] ), esc_html( $installed_plugins[ $plugin['file_path'] ]['Author'] ) ); // WPCS: XSS ok. ?>
									<?php elseif ( 'fusion-builder' === $plugin['slug'] || 'fusion-core' === $plugin['slug'] ) : ?>
										<?php /* translators: Version number. */ ?>
										<?php printf( esc_html__( 'Available Version: %s', 'Avada' ), esc_html( $plugin['version'] ) ); // WPCS: XSS ok. ?>
									<?php else : ?>
										<?php
										$version = ( isset( $plugin['version'] ) ) ? $plugin['version'] : false;
										$version = ( isset( $wp_api_plugins[ $plugin['slug'] ] ) && isset( $wp_api_plugins[ $plugin['slug'] ]['version'] ) ) ? $wp_api_plugins[ $plugin['slug'] ]['version'] : $version;
										$author  = ( $plugin['Author'] && $plugin['AuthorURI'] ) ? "<a href='{$plugin['AuthorURI']}' target='_blank'>{$plugin['Author']}</a>" : false;
										$author  = ( isset( $wp_api_plugins[ $plugin['slug'] ] ) && isset( $wp_api_plugins[ $plugin['slug'] ]['author'] ) ) ? $wp_api_plugins[ $plugin['slug'] ]['author'] : $author;
										?>
										<?php if ( $version && $author ) : ?>
											<?php /* translators: %1$s: Version. $2%s: Author. */ ?>
											<?php printf( __( 'v%1$s | %2$s', 'Avada' ), $version, $author ); // WPCS: XSS ok. ?>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</h3>
							<div class="theme-actions">
								<?php foreach ( $plugin_action as $action ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited ?>
									<?php
									// Sanitization is already taken care of in Avada_Admin class.
									// No need to re-sanitize it...
									echo $action; // WPCS: XSS ok.
									?>
								<?php endforeach; ?>
							</div>
							<?php if ( $plugin['required'] ) : ?>
								<?php $required_premium = ' plugin-required-premium'; ?>
								<div class="plugin-required">
									<?php esc_html_e( 'Required', 'Avada' ); ?>
								</div>
							<?php endif; ?>
							<?php if ( $plugin['premium'] && ( ! isset( $plugin['slug'] ) || 'pwa' !== $plugin['slug'] ) ) : ?>
								<div class="plugin-premium<?php echo esc_attr( $required_premium ); ?>">
									<?php esc_html_e( 'Premium', 'Avada' ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div id="dialog-plugin-confirm" title="<?php esc_attr_e( 'Error ', 'Avada' ); ?>">
	</div>
	<div class="avada-thanks">
		<p class="description"><?php esc_html_e( 'Thank you for choosing Avada. We are honored and are fully dedicated to making your experience perfect.', 'Avada' ); ?></p>
	</div>
</div>
<div class="fusion-clearfix" style="clear: both;"></div>
