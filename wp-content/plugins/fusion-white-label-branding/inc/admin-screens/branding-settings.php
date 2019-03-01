<?php
/**
 * Template
 *
 * @package Fusion-White-Label-Branding
 */

?>
<div class="wrap about-wrap fusion-white-label-branding-wrap fusion-white-label-branding-settings">

	<?php Fusion_White_Label_Branding_Admin::header(); ?>
	<div class="fusion-white-label-branding-settings-content">
		<div class="fusion-white-label-branding-settings">
			<?php Fusion_White_Label_Branding_Admin::branding_links(); ?>
			<?php
			// @codingStandardsIgnoreLine WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
			$section = ( isset( $_GET['section'] ) ) ? wp_unslash( $_GET['section'] ) : 'wp_admin';
			switch ( $section ) {
				case 'login_screen':
					require_once FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/options/login-screen.php';
					break;
				case 'avada':
					require_once FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/options/avada.php';
					break;
				case 'fusion_builder':
					require_once FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/options/fusion-builder.php';
					break;
				case 'fusion_slider':
					require_once FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/options/fusion-slider.php';
					break;
				case 'wp_admin':
					require_once FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/options/wordpress-admin.php';
					break;
			}
			?>
		</div>
	</div>
	<?php Fusion_White_Label_Branding_Admin::footer(); ?>
</div>
