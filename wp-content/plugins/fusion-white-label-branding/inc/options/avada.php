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

$settings      = get_option( 'fusion_branding_settings', array() );
$avada_options = isset( $settings['fusion_branding']['avada'] ) ? $settings['fusion_branding']['avada'] : array();
?>
<div class="fusion-white-label-branding-important-notice">
	<?php /* translators: Avada */ ?>
	<h3><?php printf( esc_html__( '%s Branding Settings', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
	<p class="about-description">
		<?php /* translators: Avada */ ?>
		<?php printf( esc_html__( 'These settings will change items in %s admin pages.', 'fusion-white-label-branding' ), 'Avada' ); ?>
	</p>
</div>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Avada */ ?>
			<h3><?php printf( esc_html__( '%s Label', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
			<?php /* translators: Avada */ ?>
			<p class="description"><?php printf( esc_html__( 'Replaces all instances of "%s".', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="admin_menu_label" name="fusion_branding[avada][admin_menu_label]" class="regular-text" value="<?php echo ( isset( $avada_options['admin_menu_label'] ) ) ? esc_attr( $avada_options['admin_menu_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Theme Options Menu Label', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the theme options menu label.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="theme_options_menu_label" name="fusion_branding[avada][theme_options_menu_label]" class="regular-text" value="<?php echo ( isset( $avada_options['theme_options_menu_label'] ) ) ? esc_attr( $avada_options['theme_options_menu_label'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Avada */ ?>
			<h3><?php printf( esc_html__( 'Remove %s Admin Bar Menu', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
			<?php /* translators: Avada */ ?>
			<p class="description"><?php printf( esc_html__( 'Removes the %s menu item from admin bar on frontend.', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="fusion-branding-option-field">
				<div class="fusion-form-radio-button-set ui-buttonset">
					<?php
					$remove_admin_bar_menu = '0';
					if ( isset( $avada_options['remove_admin_bar_menu'] ) ) {
						$remove_admin_bar_menu = $avada_options['remove_admin_bar_menu'];
					}
					?>
					<input type="hidden" class="button-set-value" value="<?php echo esc_attr( $remove_admin_bar_menu ); ?>" name="fusion_branding[avada][remove_admin_bar_menu]" id="remove_admin_bar_menu" />
					<a data-value="1" class="ui-button buttonset-item<?php echo ( $remove_admin_bar_menu ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Yes', 'fusion-white-label-branding' ); ?></a>
					<a data-value="0" class="ui-button buttonset-item<?php echo ( ! $remove_admin_bar_menu ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'No', 'fusion-white-label-branding' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Remove Sub-Menus', 'fusion-white-label-branding' ); ?></h3>
			<?php /* translators: Avada */ ?>
			<p class="description"><?php printf( esc_html__( 'Removes the selected sub-menus under "%s" admin menu.', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<?php
			$avada_submenus = array(
				'avada'          => __( 'Welcome', 'fusion-white-label-branding' ),
				'registration'   => __( 'Registration', 'fusion-white-label-branding' ),
				'support'        => __( 'Support', 'fusion-white-label-branding' ),
				'faq'            => __( 'FAQ', 'fusion-white-label-branding' ),
				'demos'          => __( 'Demos', 'fusion-white-label-branding' ),
				'plugins'        => __( 'Plugins', 'fusion-white-label-branding' ),
				'system-status'  => __( 'System Status', 'fusion-white-label-branding' ),
				'fusion-patcher' => __( 'Patcher', 'fusion-white-label-branding' ),
				'theme_options'  => __( 'Theme Options', 'fusion-white-label-branding' ),
			);
			$selected_menus = isset( $avada_options['remove_admin_menu'] ) ? $avada_options['remove_admin_menu'] : array();
			?>
			<?php foreach ( $avada_submenus as $menu => $title ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited ?>
				<?php $selected_menu = in_array( $menu, $selected_menus, true ) ? ' checked="checked"' : ''; ?>
				<span>
					<label for="remove_admin_menu_<?php echo esc_attr( $menu ); ?>">
						<input type="checkbox" id="remove_admin_menu_<?php echo esc_attr( $menu ); ?>" <?php echo esc_html( $selected_menu ); ?> name="fusion_branding[avada][remove_admin_menu][]" class="regular-checkbox" value="<?php echo esc_attr( $menu ); ?>" />
						<?php echo esc_html( $title ); ?>
					</label>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if ( class_exists( 'FusionCore_Plugin' ) ) : ?>
		<div class="fusion-white-label-option">
			<div class="fusion-white-label-option-title">
				<?php /* translators: Avada */ ?>
				<h3><?php printf( esc_attr__( 'Rename %s Post Type Menus', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
				<?php /* translators: Avada */ ?>
				<p class="description"><?php printf( esc_html__( 'Renames the admin menu labels for %s post types.', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
			</div>
			<div class="fusion-white-label-option-input">
				<?php
				$admin_menus = array(
					'faq'       => esc_html__( 'FAQs', 'fusion-white-label-branding' ),
					'portfolio' => esc_html__( 'Portfolio', 'fusion-white-label-branding' ),
					'skills'    => esc_html__( 'Skills', 'fusion-white-label-branding' ),
					'tags'      => esc_html__( 'Tags', 'fusion-white-label-branding' ),
				);

				$saved_menus = isset( $avada_options['rename_admin_menu'] ) ? $avada_options['rename_admin_menu'] : array();
				foreach ( $admin_menus as $menu => $title ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
					$value = ( isset( $saved_menus[ $menu ] ) && '' !== $saved_menus[ $menu ] ) ? $saved_menus[ $menu ] : '';
					?>
					<p>
					<label for="rename_admin_menu_<?php echo esc_attr( $menu ); ?>">
					<input type="text" id="rename_admin_menu_<?php echo esc_attr( $menu ); ?>" placeholder="<?php echo esc_attr( $title ); ?>" name="fusion_branding[avada][rename_admin_menu][<?php echo esc_attr( $menu ); ?>]" class="regular-text" value="<?php echo esc_attr( $value ); ?>" />
					<span><?php echo esc_html( $title ); ?></span>
					</label></p>
					<?php
				}
				?>
			</div>
		</div>
		<div class="fusion-white-label-option">
			<div class="fusion-white-label-option-title">
				<h3><?php esc_html_e( 'Remove Post Type Menus', 'fusion-white-label-branding' ); ?></h3>
				<?php /* translators: Avada */ ?>
				<p class="description"><?php printf( esc_html__( 'Removes the selected admin menus for post types registered for "%s".', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
			</div>
			<div class="fusion-white-label-option-input">
				<?php
				$avada_post_types = array(
					'avada_portfolio'     => __( 'Portfolio', 'fusion-white-label-branding' ),
					'avada_faq'           => __( 'FAQ', 'fusion-white-label-branding' ),
					'themefusion_elastic' => __( 'Elastic Slider', 'fusion-white-label-branding' ),
				);

				$selected_menus = isset( $avada_options['remove_post_type_menu'] ) ? $avada_options['remove_post_type_menu'] : array();
				foreach ( $avada_post_types as $menu => $title ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
					$selected_menu = in_array( $menu, $selected_menus, true ) ? ' checked="checked"' : '';
					?>
					<span>
					<label for="remove_post_type_menu_<?php echo esc_attr( $menu ); ?>">
					<input type="checkbox" id="remove_post_type_menu_<?php echo esc_attr( $menu ); ?>" <?php echo esc_html( $selected_menu ); ?> name="fusion_branding[avada][remove_post_type_menu][]" class="regular-checkbox" value="<?php echo esc_attr( $menu ); ?>" />
					<?php echo esc_html( $title ); ?></label></span>
					<?php
				}
				?>
			</div>
		</div>
	<?php endif; ?>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3>
				<?php
				// translators: Avada.
				printf( esc_html__( '%s Logo Image', 'fusion-white-label-branding' ), 'Avada' );
				?>
			</h3>
			<p class="description">
				<?php
				// translators: Avada.
				printf( __( 'Controls the logo image on %s admin pages and replaces the ThemeFusion logo. <br/><strong>Full size of the box:</strong> 150px wide x 130px high.', 'fusion-white-label-branding' ), 'Avada' ); // XSS OK.
				?>
			</p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="avada_logo_image_preview branding-image-preview">
				<?php
				if ( ! empty( $avada_options['avada_logo_image'] ) ) {
					echo '<img src="' . esc_url( $avada_options['avada_logo_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="avada_logo_image" name="fusion_branding[avada][avada_logo_image]" class="image-field" value="<?php echo ( isset( $avada_options['avada_logo_image'] ) ) ? esc_attr( $avada_options['avada_logo_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<?php // translators: Avada. ?>
					<input type="button" data-title="<?php printf( esc_attr__( '%s Logo Image', 'fusion-white-label-branding' ), 'Avada' ); ?>" data-button-title="<?php esc_attr_e( 'Use Logo', 'fusion-white-label-branding' ); ?>" data-image-id="avada_logo_image" class="button button-secondary button-upload-image button-default" value="<?php esc_attr_e( 'Upload Logo Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#avada_logo_image').val('').trigger( 'change' ); jQuery( '.avada_logo_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $avada_options['avada_logo_image'] ) || '' === $avada_options['avada_logo_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_attr_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Version Number Text', 'fusion-white-label-branding' ); ?></h3>
			<?php /* translators: Avada */ ?>
			<p class="description"><?php printf( esc_html__( 'Replaces version number text beneath the logo on %s admin screens.', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="version_number_text" name="fusion_branding[avada][version_number_text]" class="regular-text" value="<?php echo ( isset( $avada_options['version_number_text'] ) ) ? esc_attr( $avada_options['version_number_text'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Version Number Box Background', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the background color of the version number box.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="version_number_box_background" name="fusion_branding[avada][version_number_box_background]" class="color-field color-picker" value="<?php echo ( isset( $avada_options['version_number_box_background'] ) ) ? esc_attr( $avada_options['version_number_box_background'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Version Number Text Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the text color of the version number.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="version_number_box_color" name="fusion_branding[avada][version_number_box_color]" class="color-field color-picker" value="<?php echo ( isset( $avada_options['version_number_box_color'] ) ) ? esc_attr( $avada_options['version_number_box_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3>
				<?php
				// translators: Avada.
				printf( esc_html__( '%s Menu Icon Image', 'fusion-white-label-branding' ), 'Avada' );
				?>
			</h3>
			<p class="description">
				<?php
				// translators: Avada.
				printf( __( 'Controls the menu icon image on %s admin menu and replaces the ThemeFusion logo. <br/><strong>Recommended size:</strong> 20px wide x 20px high.', 'fusion-white-label-branding' ), 'Avada' ); // XSS OK.
				?>
			</p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="avada_icon_image_preview branding-image-preview">
				<?php
				if ( ! empty( $avada_options['avada_icon_image'] ) ) {
					echo '<img src="' . esc_url( $avada_options['avada_icon_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="avada_icon_image" name="fusion_branding[avada][avada_icon_image]" class="image-field" value="<?php echo ( isset( $avada_options['avada_icon_image'] ) ) ? esc_attr( $avada_options['avada_icon_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<?php // translators: Avada. ?>
					<input type="button" data-title="<?php printf( esc_attr__( '%s Menu Icon Image', 'fusion-white-label-branding' ), 'Avada' ); ?>" data-button-title="<?php esc_attr_e( 'Use Menu Icon', 'fusion-white-label-branding' ); ?>" data-image-id="avada_icon_image" class="button button-secondary button-upload-image button-default" value="<?php esc_attr_e( 'Upload Menu Icon Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#avada_icon_image').val('').trigger( 'change' ); jQuery( '.avada_icon_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $avada_options['avada_icon_image'] ) || '' === $avada_options['avada_icon_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_attr_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php // Translators: Avada. ?>
			<h3><?php printf( esc_html__( 'Dashicon Name for %s Menu Icon', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
			<?php /* translators: Fusion */ ?>
			<p class="description"><?php printf( __( 'Replaces the image icon and default icon for %1$s logo in admin menu. <a target="_blank" href="%2$s">Click Here</a> to see all dashicons.', 'fusion-white-label-branding' ), 'Avada', 'https://developer.wordpress.org/resource/dashicons/' ); // XSS: Ok. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="admin_menu_dashicon" placeholder="dashicons-awards" name="fusion_branding[avada][admin_menu_dashicon]" class="regular-text" value="<?php echo ( isset( $avada_options['admin_menu_dashicon'] ) ) ? esc_attr( $avada_options['admin_menu_dashicon'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3>
				<?php
				// translators: Fusion White Label Branding.
				printf( esc_html__( '%s Menu Icon Image', 'fusion-white-label-branding' ), 'Fusion White Label Branding' );
				?>
			</h3>
			<p class="description">
				<?php
				// translators: Fusion White Label Branding.
				printf( __( 'Controls the menu icon image on %s admin menu and replaces the ThemeFusion logo. <br/><strong>Recommended size:</strong> 20px wide x 20px high.', 'fusion-white-label-branding' ), 'Fusion White Label Branding' ); // XSS OK.
				?>
			</p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="fusion_white_label_branding_icon_image_preview branding-image-preview">
				<?php
				if ( ! empty( $avada_options['fusion_white_label_branding_icon_image'] ) ) {
					echo '<img src="' . esc_url( $avada_options['fusion_white_label_branding_icon_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="fusion_white_label_branding_icon_image" name="fusion_branding[avada][fusion_white_label_branding_icon_image]" class="image-field" value="<?php echo ( isset( $avada_options['fusion_white_label_branding_icon_image'] ) ) ? esc_attr( $avada_options['fusion_white_label_branding_icon_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<?php // translators: Fusion White Label Branding. ?>
					<input type="button" data-title="<?php printf( esc_attr__( '%s Menu Icon Image', 'fusion-white-label-branding' ), 'Fusion White Label Branding' ); ?>" data-button-title="<?php esc_attr_e( 'Use Menu Icon', 'fusion-white-label-branding' ); ?>" data-image-id="fusion_white_label_branding_icon_image" class="button button-secondary button-upload-image button-default" value="<?php esc_attr_e( 'Upload Menu Icon Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#fusion_white_label_branding_icon_image').val('').trigger( 'change' ); jQuery( '.fusion_white_label_branding_icon_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $avada_options['fusion_white_label_branding_icon_image'] ) || '' === $avada_options['fusion_white_label_branding_icon_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_attr_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php // Translators: Fusion White Label Branding. ?>
			<h3><?php printf( esc_html__( 'Dashicon Name for %s Menu Icon', 'fusion-white-label-branding' ), 'Fusion White Label Branding' ); ?></h3>
			<?php /* translators: Fusion White Label Branding */ ?>
			<p class="description"><?php printf( __( 'Replaces the image icon and default icon of the %1$s logo in admin menu.<br/> <a target="_blank" href="%2$s">Click Here</a> to see all dashicons.', 'fusion-white-label-branding' ), 'Fusion White Label Branding', 'https://developer.wordpress.org/resource/dashicons/' ); // XSS: Ok. ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="fusion_white_label_branding_menu_dashicon" placeholder="dashicons-nametag" name="fusion_branding[avada][fusion_white_label_branding_menu_dashicon]" class="regular-text" value="<?php echo ( isset( $avada_options['fusion_white_label_branding_menu_dashicon'] ) ) ? esc_attr( $avada_options['fusion_white_label_branding_menu_dashicon'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<?php /* translators: Avada */ ?>
			<h3><?php printf( esc_html__( '%s Welcome Screen', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
			<?php /* translators: Avada */ ?>
			<p class="description"><?php printf( esc_html__( 'Controls the content visible on the %s welcome screen.', 'fusion-white-label-branding' ), 'Avada' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" id="welcome_screen_title" name="fusion_branding[avada][welcome_screen_title]" class="regular-text" value="<?php echo ( isset( $avada_options['welcome_screen_title'] ) ) ? esc_attr( $avada_options['welcome_screen_title'] ) : ''; ?>" />
			<p class="description"><?php esc_html_e( 'Welcome screen title.', 'fusion-white-label-branding' ); ?></p>
			<br/>
			<textarea id="welcome_screen_about_text" name="fusion_branding[avada][welcome_screen_about_text]" class="regular-textarea"><?php echo ( isset( $avada_options['welcome_screen_about_text'] ) ) ? esc_attr( $avada_options['welcome_screen_about_text'] ) : ''; ?></textarea>
			<p class="description"><?php esc_html_e( 'Welcome screen about text.', 'fusion-white-label-branding' ); ?></p>
			<br/>
			<?php
			$content  = isset( $avada_options['welcome_screen_content'] ) ? $avada_options['welcome_screen_content'] : '';
			$settings = array(
				'media_buttons' => true,
				'textarea_name' => 'fusion_branding[avada][welcome_screen_content]',
				'editor_height' => '200',
			);
			wp_editor( $content, 'welcome_panel_content', $settings );
			?>
			<p class="description"><?php esc_html_e( 'Welcome screen content.', 'fusion-white-label-branding' ); ?></p>
		</div>
	</div>

<input type="hidden" name="action" value="save_fusion_branding_settings">
<input type="hidden" name="section" value="avada">
<?php wp_nonce_field( 'fusion_branding_save_settings', 'fusion_branding_save_settings' ); ?>
<input type="submit" class="button button-primary fusion-branding-save-settings" value="<?php esc_attr_e( 'Save Settings', 'fusion-white-label-branding' ); ?>" />
<a onclick="return confirm('<?php esc_attr_e( 'Are you sure, you want to reset these settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=avada' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-avada fusion-branding-reset-settings"><?php esc_attr_e( 'Reset Section', 'fusion-white-label-branding' ); ?></a>
<a onclick="return confirm('<?php esc_attr_e( 'Are you sure, you want to reset all settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=all' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-all fusion-branding-reset-settings"><?php esc_attr_e( 'Reset All', 'fusion-white-label-branding' ); ?></a>
</form>
