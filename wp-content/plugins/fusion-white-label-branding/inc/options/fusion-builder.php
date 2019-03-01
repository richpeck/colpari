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

$settings               = get_option( 'fusion_branding_settings', array() );
$fusion_builder_options = isset( $settings['fusion_branding']['fusion_builder'] ) ? $settings['fusion_branding']['fusion_builder'] : array();
?>
<div class="fusion-white-label-branding-important-notice">
	<?php /* translators: Fusion Builder */ ?>
	<h3><?php printf( esc_html__( '%s Branding Settings', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></h3>
	<p class="about-description">
		<?php /* translators: Fusion Builder */ ?>
		<?php printf( esc_html__( 'These settings will change items in %s admin pages.', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?>
	</p>
</div>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Builder */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></h3>
			<?php /* translators: Fusion Builder */ ?>
			<p class="description"><?php printf( esc_html__( 'Replaces all instances of "%s".', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="admin_menu_label" name="fusion_branding[fusion_builder][admin_menu_label]" class="regular-text" value="<?php echo ( isset( $fusion_builder_options['admin_menu_label'] ) ) ? esc_html( $fusion_builder_options['admin_menu_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Remove Sub-menus', 'fusion-white-label-branding' ); ?></h3>
			<?php /* translators: Fusion Builder */ ?>
			<p class="description"><?php printf( esc_html__( 'Removes the selected sub-menus under "%s" admin menu.', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<?php
			$fusion_builder_submenus = array(
				'options'  => esc_html__( 'Welcome', 'fusion-white-label-branding' ),
				'support'  => esc_html__( 'Support', 'fusion-white-label-branding' ),
				'faq'      => esc_html__( 'FAQ', 'fusion-white-label-branding' ),
				'settings' => esc_html__( 'Settings', 'fusion-white-label-branding' ),
				'library'  => esc_html__( 'Library', 'fusion-white-label-branding' ),
				'addons'   => esc_html__( 'Add-ons', 'fusion-white-label-branding' ),
			);

			$selected_menus = isset( $fusion_builder_options['remove_admin_menu'] ) ? $fusion_builder_options['remove_admin_menu'] : array();
			foreach ( $fusion_builder_submenus as $menu => $title ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				$selected_menu = in_array( $menu, $selected_menus, true ) ? ' checked="checked"' : '';
				?>
				<span>
				<label for="remove_admin_menu_<?php echo esc_html( $menu ); ?>">
				<input type="checkbox" id="remove_admin_menu_<?php echo esc_html( $menu ); ?>" <?php echo esc_html( $selected_menu ); ?> name="fusion_branding[fusion_builder][remove_admin_menu][]" class="regular-checkbox" value="<?php echo esc_html( $menu ); ?>" />
				<?php echo esc_html( $title ); ?></label></span>
				<?php
			}
			?>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Builder */ ?>
			<h3><?php printf( esc_html__( '%s Logo Image', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></h3>
			<?php /* translators: Fusion Builder */ ?>
			<p class="description"><?php printf( __( 'Controls the %1$s logo on the Welcome Screen and the %2$s User Interface (auto resized to 40px max).<br/><strong>Full size of the box:</strong> 150px wide x 130px high.', 'fusion-white-label-branding' ), 'Fusion Builder', 'Fusion Builder' ); // XSS OK. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="fusion_builder_logo_image_preview branding-image-preview">
				<?php
				if ( ! empty( $fusion_builder_options['fusion_builder_logo_image'] ) ) {
					echo '<img src="' . esc_url( $fusion_builder_options['fusion_builder_logo_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="fusion_builder_logo_image" name="fusion_branding[fusion_builder][fusion_builder_logo_image]" class="image-field" value="<?php echo ( isset( $fusion_builder_options['fusion_builder_logo_image'] ) ) ? esc_html( $fusion_builder_options['fusion_builder_logo_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<?php /* translators: Fusion Builder */ ?>
					<input type="button" data-title="<?php printf( esc_html__( '%s Logo Image', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?>" data-button-title="<?php esc_html_e( 'Use Logo', 'fusion-white-label-branding' ); ?>" data-image-id="fusion_builder_logo_image" class="button button-secondary button-upload-image button-default" value="<?php esc_html_e( 'Upload Logo Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#fusion_builder_logo_image').val('').trigger( 'change' ); jQuery( '.fusion_builder_logo_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $fusion_builder_options['fusion_builder_logo_image'] ) || '' === $fusion_builder_options['fusion_builder_logo_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_html_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Version Number Text', 'fusion-white-label-branding' ); ?></h3>
			<?php /* translators: Fusion Builder */ ?>
			<p class="description"><?php printf( esc_html__( 'Replaces the version number text beneath the logo on %s admin screens.', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="version_number_text" name="fusion_branding[fusion_builder][version_number_text]" class="regular-text" value="<?php echo ( isset( $fusion_builder_options['version_number_text'] ) ) ? esc_html( $fusion_builder_options['version_number_text'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Version Number Box Background', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the background color of the version number box.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="version_number_box_background" name="fusion_branding[fusion_builder][version_number_box_background]" class="color-field color-picker" value="<?php echo ( isset( $fusion_builder_options['version_number_box_background'] ) ) ? esc_html( $fusion_builder_options['version_number_box_background'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Version Number Text Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the text color of the version number.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="version_number_box_color" name="fusion_branding[fusion_builder][version_number_box_color]" class="color-field color-picker" value="<?php echo ( isset( $fusion_builder_options['version_number_box_color'] ) ) ? esc_html( $fusion_builder_options['version_number_box_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Builder */ ?>
			<h3><?php printf( esc_html__( '%s Menu Icon Image', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></h3>
			<?php /* translators: Fusion Builder */ ?>
			<p class="description"><?php printf( __( 'Controls the %s icon in the admin menu. <strong>Recommended size:</strong> 20px wide x 20px high.', 'fusion-white-label-branding' ), 'Fusion Builder' ); // XSS OK. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="fusion_builder_icon_image_preview branding-image-preview">
				<?php
				if ( ! empty( $fusion_builder_options['fusion_builder_icon_image'] ) ) {
					echo '<img src="' . esc_url( $fusion_builder_options['fusion_builder_icon_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="fusion_builder_icon_image" name="fusion_branding[fusion_builder][fusion_builder_icon_image]" class="image-field" value="<?php echo ( isset( $fusion_builder_options['fusion_builder_icon_image'] ) ) ? esc_html( $fusion_builder_options['fusion_builder_icon_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<?php /* translators: Fusion Builder */ ?>
					<input type="button" data-title="<?php printf( esc_html__( '%s Icon Image', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?>" data-button-title="<?php esc_html_e( 'Use Icon', 'fusion-white-label-branding' ); ?>" data-image-id="fusion_builder_icon_image" class="button button-secondary button-upload-image button-default" value="<?php esc_html_e( 'Upload Icon Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#fusion_builder_icon_image').val('').trigger( 'change' ); jQuery( '.fusion_builder_icon_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $fusion_builder_options['fusion_builder_icon_image'] ) || '' === $fusion_builder_options['fusion_builder_icon_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_html_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php // Translators: Fusion Builder. ?>
			<h3><?php printf( esc_html__( 'Dashicon Name for %s Menu Icon', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></h3>
			<?php /* translators: Fusion */ ?>
			<p class="description"><?php printf( __( 'Replaces the image icon and default icon of the %1$s logo in admin menu. <a target="_blank" href="%2$s">Click Here</a> to see all dashicons.', 'fusion-white-label-branding' ), 'Fusion Builder', 'https://developer.wordpress.org/resource/dashicons/' ); // XSS: Ok. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="admin_menu_dashicon" placeholder="dashicons-editor-table" name="fusion_branding[fusion_builder][admin_menu_dashicon]" class="regular-text" value="<?php echo ( isset( $fusion_builder_options['admin_menu_dashicon'] ) ) ? esc_html( $fusion_builder_options['admin_menu_dashicon'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Fusion Builder */ ?>
			<h3><?php printf( esc_html__( '%s Welcome Screen', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></h3>
			<?php /* translators: Fusion Builder */ ?>
			<p class="description"><?php printf( esc_html__( 'Controls the content visible on %s welcome screen.', 'fusion-white-label-branding' ), 'Fusion Builder' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="welcome_screen_title" name="fusion_branding[fusion_builder][welcome_screen_title]" class="regular-text" value="<?php echo ( isset( $fusion_builder_options['welcome_screen_title'] ) ) ? esc_html( $fusion_builder_options['welcome_screen_title'] ) : ''; ?>" />
			<p class="description"><?php esc_html_e( 'Welcome screen title.', 'fusion-white-label-branding' ); ?></p>
			<br/>
			<textarea id="welcome_screen_about_text" name="fusion_branding[fusion_builder][welcome_screen_about_text]" class="regular-textarea"><?php echo ( isset( $fusion_builder_options['welcome_screen_about_text'] ) ) ? esc_html( $fusion_builder_options['welcome_screen_about_text'] ) : ''; ?></textarea>
			<p class="description"><?php esc_html_e( 'Welcome screen about text.', 'fusion-white-label-branding' ); ?></p>
			<br/>
			<?php
			$content  = isset( $fusion_builder_options['welcome_screen_content'] ) ? $fusion_builder_options['welcome_screen_content'] : '';
			$settings = array(
				'media_buttons' => true,
				'textarea_name' => 'fusion_branding[fusion_builder][welcome_screen_content]',
				'editor_height' => '200',
			);
			wp_editor( $content, 'welcome_panel_content', $settings );
			?>
			<p class="description"><?php esc_html_e( 'Welcome screen content.', 'fusion-white-label-branding' ); ?></p>
		</div>
	</div>

	<input type="hidden" name="action" value="save_fusion_branding_settings">
	<input type="hidden" name="section" value="fusion_builder">
	<?php wp_nonce_field( 'fusion_branding_save_settings', 'fusion_branding_save_settings' ); ?>
	<input type="submit" class="button button-primary fusion-branding-save-settings" value="<?php esc_html_e( 'Save Settings', 'fusion-white-label-branding' ); ?>" />
	<a onclick="return confirm('<?php esc_html_e( 'Are you sure, you want to reset these settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=fusion_builder' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-fusion_builder fusion-branding-reset-settings"><?php esc_html_e( 'Reset Section', 'fusion-white-label-branding' ); ?></a>
	<a onclick="return confirm('<?php esc_html_e( 'Are you sure, you want to reset all settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=all' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-all fusion-branding-reset-settings"><?php esc_html_e( 'Reset All', 'fusion-white-label-branding' ); ?></a>
</form>
