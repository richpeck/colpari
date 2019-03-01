<?php
/**
 * Template
 *
 * @package Fusion-White-Label-Branding
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings              = get_option( 'fusion_branding_settings', array() );
$fusion_slider_options = isset( $settings['fusion_branding']['fusion_slider'] ) ? $settings['fusion_branding']['fusion_slider'] : array();

?>
<div class="fusion-white-label-branding-important-notice">
	<?php /* translators: Fusion Slider */ ?>
	<h3><?php printf( esc_html__( '%s Branding Settings', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?></h3>
	<p class="about-description">
		<?php /* translators: Fusion Slider */ ?>
		<?php printf( esc_html__( 'These settings will change items in the %s admin pages.', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?>
	</p>
</div>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Slider */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( esc_html__( 'Replaces all instances of the "%s".', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="admin_menu_label" name="fusion_branding[fusion_slider][admin_menu_label]" class="regular-text" value="<?php echo ( isset( $fusion_slider_options['admin_menu_label'] ) ) ? esc_html( $fusion_slider_options['admin_menu_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Slider */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Fusion Sliders' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( esc_html__( 'Replaces all instances of the "%s".', 'fusion-white-label-branding' ), 'Fusion Sliders' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="sliders_label" name="fusion_branding[fusion_slider][sliders_label]" class="regular-text" value="<?php echo ( isset( $fusion_slider_options['sliders_label'] ) ) ? esc_html( $fusion_slider_options['sliders_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Slider */ ?>
			<h3><?php printf( esc_html__( '%s Menu Icon Image', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( __( 'Controls the %s icon in the admin menu. <strong>Recommended size:</strong> 20px wide x 20px high.', 'fusion-white-label-branding' ), 'Fusion Slider' ); // XSS OK. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="fusion_slider_icon_image_preview branding-image-preview">
				<?php
				if ( ! empty( $fusion_slider_options['fusion_slider_icon_image'] ) ) {
					echo '<img src="' . esc_url( $fusion_slider_options['fusion_slider_icon_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="fusion_slider_icon_image" name="fusion_branding[fusion_slider][fusion_slider_icon_image]" class="image-field" value="<?php echo ( isset( $fusion_slider_options['fusion_slider_icon_image'] ) ) ? esc_html( $fusion_slider_options['fusion_slider_icon_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<?php /* translators: Fusion Slider */ ?>
					<input type="button" data-title="<?php printf( esc_html__( '%s Icon Image', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?>" data-button-title="<?php esc_html_e( 'Use Icon', 'fusion-white-label-branding' ); ?>" data-image-id="fusion_slider_icon_image" class="button button-secondary button-upload-image button-default" value="<?php esc_html_e( 'Upload Icon Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#fusion_slider_icon_image').val('').trigger( 'change' ); jQuery( '.fusion_slider_icon_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $fusion_slider_options['fusion_slider_icon_image'] ) || '' === $fusion_slider_options['fusion_slider_icon_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_html_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php // Translators: Fusion Slider. ?>
			<h3><?php printf( esc_html__( 'Dashicon Name for %s Menu Icon', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?></h3>
			<?php /* translators: Fusion */ ?>
			<p class="description"><?php printf( __( 'Replaces the image icon and default icon of the %1$s logo in admin menu. <a target="_blank" href="%2$s">Click Here</a> to see all dashicons.', 'fusion-white-label-branding' ), 'Fusion Slider', 'https://developer.wordpress.org/resource/dashicons/' ); // XSS: Ok. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="admin_menu_dashicon" placeholder="dashicons-slides" name="fusion_branding[fusion_slider][admin_menu_dashicon]" class="regular-text" value="<?php echo ( isset( $fusion_slider_options['admin_menu_dashicon'] ) ) ? esc_html( $fusion_slider_options['admin_menu_dashicon'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Slider */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Fusion Slider Add or Edit Slides' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( esc_html__( 'Changes the name of "%s".', 'fusion-white-label-branding' ), 'Add or Edit Slides' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="add_edit_slide_label" name="fusion_branding[fusion_slider][add_edit_slide_label]" class="regular-text" value="<?php echo ( isset( $fusion_slider_options['add_edit_slide_label'] ) ) ? esc_html( $fusion_slider_options['add_edit_slide_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Slider */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Fusion Slider Add or Edit Sliders' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( esc_html__( 'Changes the name of "%s".', 'fusion-white-label-branding' ), 'Add or Edit Sliders' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="add_edit_sliders_label" name="fusion_branding[fusion_slider][add_edit_sliders_label]" class="regular-text" value="<?php echo ( isset( $fusion_slider_options['add_edit_sliders_label'] ) ) ? esc_html( $fusion_slider_options['add_edit_sliders_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Slider */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Fusion Slider Export / Import' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( esc_html__( 'Changes the name of "%s".', 'fusion-white-label-branding' ), 'Export / Import' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="import_export_label" name="fusion_branding[fusion_slider][import_export_label]" class="regular-text" value="<?php echo ( isset( $fusion_slider_options['import_export_label'] ) ) ? esc_html( $fusion_slider_options['import_export_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Remove Sub-menus', 'fusion-white-label-branding' ); ?></h3>
			<?php /* translators: Fusion Slider */ ?>
			<p class="description"><?php printf( esc_html__( 'Removes the selected sub-menus under "%s" admin menu.', 'fusion-white-label-branding' ), 'Fusion Slider' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<?php
			$fusion_slider_submenus = array(
				'slider'        => __( 'Add or Edit Slides' ),
				'slide-page'    => __( 'Add or Edit Sliders' ),
				'import-export' => __( 'Export / Import' ),
			);

			$selected_menus = isset( $fusion_slider_options['remove_admin_menu'] ) ? $fusion_slider_options['remove_admin_menu'] : array();
			foreach ( $fusion_slider_submenus as $menu => $title ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				$selected_menu = in_array( $menu, $selected_menus, true ) ? ' checked="checked"' : '';
				?>
				<span>
					<label for="remove_admin_menu_<?php echo esc_html( $menu ); ?>">
						<input type="checkbox" id="remove_admin_menu_<?php echo esc_html( $menu ); ?>" <?php echo esc_html( $selected_menu ); ?> name="fusion_branding[fusion_slider][remove_admin_menu][]" class="regular-checkbox" value="<?php echo esc_html( $menu ); ?>" />
						<?php echo esc_html( $title ); ?>
					</label>
				</span>
				<?php
			}
			?>
		</div>
	</div>

	<input type="hidden" name="action" value="save_fusion_branding_settings">
	<input type="hidden" name="section" value="fusion_slider">
	<?php wp_nonce_field( 'fusion_branding_save_settings', 'fusion_branding_save_settings' ); ?>
	<input type="submit" class="button button-primary fusion-branding-save-settings" value="<?php esc_html_e( 'Save Settings', 'fusion-white-label-branding' ); ?>" />
	<a onclick="return confirm('<?php esc_html_e( 'Are you sure, you want to reset these settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=fusion_slider' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-fusion_slider fusion-branding-reset-settings"><?php esc_html_e( 'Reset Section', 'fusion-white-label-branding' ); ?></a>
	<a onclick="return confirm('<?php esc_html_e( 'Are you sure, you want to reset all settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=all' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-all fusion-branding-reset-settings"><?php esc_html_e( 'Reset All', 'fusion-white-label-branding' ); ?></a>
</form>
