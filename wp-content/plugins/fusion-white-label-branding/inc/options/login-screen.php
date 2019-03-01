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

$settings             = get_option( 'fusion_branding_settings', array() );
$login_screen_options = isset( $settings['fusion_branding']['login_screen'] ) ? $settings['fusion_branding']['login_screen'] : array();
?>
<div class="fusion-white-label-branding-important-notice">
	<h3><?php esc_html_e( 'WordPress Login Screen Branding Settings', 'fusion-white-label-branding' ); ?></h3>
	<p class="about-description">
		<?php esc_html_e( 'These settings will change items in the WordPress login area.', 'fusion-white-label-branding' ); ?>
	</p>
</div>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Background Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login screen background color. If an image is used below, this color will overlay it allowing you to adjust the opacity from 0 to 100%.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_background_color" name="fusion_branding[login_screen][login_background_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_background_color'] ) ) ? esc_html( $login_screen_options['login_background_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Background Image', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login screen background image. If a background color is set above, it overlays the image. If you only want the image to show, set the above background color to 0%.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="login_background_image_preview branding-image-preview">
				<?php
				if ( ! empty( $login_screen_options['login_background_image'] ) ) {
					echo '<img src="' . esc_url( $login_screen_options['login_background_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="login_background_image" name="fusion_branding[login_screen][login_background_image]" class="image-field" value="<?php echo ( isset( $login_screen_options['login_background_image'] ) ) ? esc_html( $login_screen_options['login_background_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<input type="button" data-title="<?php esc_html_e( 'Login Screen Background Image', 'fusion-white-label-branding' ); ?>" data-button-title="<?php esc_html_e( 'Use Image', 'fusion-white-label-branding' ); ?>" data-image-id="login_background_image" class="button button-secondary button-upload-image button-default" value="<?php esc_html_e( 'Upload Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#login_background_image').val('').trigger( 'change' ); jQuery( '.login_background_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $login_screen_options['login_background_image'] ) || '' === $login_screen_options['login_background_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_html_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Logo Image', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login screen logo image.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="login_logo_image_preview branding-image-preview">
				<?php
				if ( ! empty( $login_screen_options['login_logo_image'] ) ) {
					echo '<img src="' . esc_url( $login_screen_options['login_logo_image'] ) . '" />';
				}
				?>
			</div>
			<div class="image-upload-container">
				<div class="upload-field-input">
					<input type="text" id="login_logo_image" name="fusion_branding[login_screen][login_logo_image]" class="image-field" value="<?php echo ( isset( $login_screen_options['login_logo_image'] ) ) ? esc_html( $login_screen_options['login_logo_image'] ) : ''; ?>" />
				</div>
				<div class="upload-field-buttons">
					<input type="button" data-title="<?php esc_html_e( 'Login Screen Logo Image', 'fusion-white-label-branding' ); ?>" data-button-title="<?php esc_html_e( 'Use Logo', 'fusion-white-label-branding' ); ?>" data-image-id="login_logo_image" class="button button-secondary button-upload-image button-default" value="<?php esc_html_e( 'Upload Image', 'fusion-white-label-branding' ); ?>" />
					<input type="button" onclick="jQuery('#login_logo_image').val('').trigger( 'change' ); jQuery( '.login_logo_image_preview').html(''); jQuery( this ).hide();" class="button button-secondary button-remove-image button-default <?php echo ( ! isset( $login_screen_options['login_logo_image'] ) || '' === $login_screen_options['login_logo_image'] ) ? 'hidden' : ''; ?>" value="<?php esc_html_e( 'Remove Image', 'fusion-white-label-branding' ); ?> " />
				</div>
			</div>
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Login Box Background Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login box background color.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_box_background_color" name="fusion_branding[login_screen][login_box_background_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_box_background_color'] ) ) ? esc_html( $login_screen_options['login_box_background_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Login Box Text Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login form label color.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_box_text_color" name="fusion_branding[login_screen][login_box_text_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_box_text_color'] ) ) ? esc_html( $login_screen_options['login_box_text_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Login Screen Link Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login screen link color.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_box_link_color" name="fusion_branding[login_screen][login_box_link_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_box_link_color'] ) ) ? esc_html( $login_screen_options['login_box_link_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Login Screen Link Hover Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the login screen link hover color.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_box_link_hover_color" name="fusion_branding[login_screen][login_box_link_hover_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_box_link_hover_color'] ) ) ? esc_html( $login_screen_options['login_box_link_hover_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Default Button Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the default color of the button.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_button_background_color" name="fusion_branding[login_screen][login_button_background_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_button_background_color'] ) ) ? esc_html( $login_screen_options['login_button_background_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Hover Button Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the hover color of the button.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_button_background_color_hover" name="fusion_branding[login_screen][login_button_background_color_hover]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_button_background_color_hover'] ) ) ? esc_html( $login_screen_options['login_button_background_color_hover'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Button Default Text Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the text color of the button.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_button_accent_color" name="fusion_branding[login_screen][login_button_accent_color]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_button_accent_color'] ) ) ? esc_html( $login_screen_options['login_button_accent_color'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Button Hover Text Color', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Controls the hover text color of the button.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<input type="text" data-alpha="true" id="login_button_accent_color_hover" name="fusion_branding[login_screen][login_button_accent_color_hover]" class="color-field color-picker" value="<?php echo ( isset( $login_screen_options['login_button_accent_color_hover'] ) ) ? esc_html( $login_screen_options['login_button_accent_color_hover'] ) : ''; ?>" />
		</div>
	</div>
	<div class="fusion-white-label-option">
		<div class="fusion-white-label-option-title">
			<h3><?php esc_html_e( 'Remove Lost Password Link', 'fusion-white-label-branding' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Remove the lost password link from login page.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<div class="fusion-white-label-option-input">
			<div class="fusion-branding-option-field">
				<div class="fusion-form-radio-button-set ui-buttonset">
					<?php
					$remove_lost_password = '0';
					if ( isset( $login_screen_options['remove_lost_password'] ) ) {
						$remove_lost_password = $login_screen_options['remove_lost_password'];
					}
					?>
					<input type="hidden" class="button-set-value" value="<?php echo esc_html( $remove_lost_password ); ?>" name="fusion_branding[login_screen][remove_lost_password]" id="remove_lost_password" />
					<a data-value="1" class="ui-button buttonset-item<?php echo ( $remove_lost_password ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Yes', 'fusion-white-label-branding' ); ?></a>
					<a data-value="0" class="ui-button buttonset-item<?php echo ( ! $remove_lost_password ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'No', 'fusion-white-label-branding' ); ?></a>
				</div>
			</div>
		</div>
	</div>

<input type="hidden" name="action" value="save_fusion_branding_settings">
<input type="hidden" name="section" value="login_screen">
<?php wp_nonce_field( 'fusion_branding_save_settings', 'fusion_branding_save_settings' ); ?>
<input type="submit" class="button button-primary fusion-branding-save-settings" value="<?php esc_html_e( 'Save Settings', 'fusion-white-label-branding' ); ?>" />
<a onclick="return confirm('<?php esc_html_e( 'Are you sure, you want to reset these settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=login_screen' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-login_screen fusion-branding-reset-settings"><?php esc_html_e( 'Reset Section', 'fusion-white-label-branding' ); ?></a>
<a onclick="return confirm('<?php esc_html_e( 'Are you sure, you want to reset all settings?\n\nThis action can not be undone.', 'fusion-white-label-branding' ); ?>');" href="<?php echo esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php?action=reset-branding-settings&section_id=all' ) ) ); ?>" class="button button-secondary fusion-branding-reset-section-all fusion-branding-reset-settings"><?php esc_html_e( 'Reset All', 'fusion-white-label-branding' ); ?></a>
</form>
