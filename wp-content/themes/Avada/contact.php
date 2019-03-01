<?php
/**
 * Template Name: Contact
 * This template file is used for contact pages.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

get_header();

/**
 * Instantiate the Avada_Contact class.
 */
$avada_contact = new Avada_Contact();
?>
<section id="content" <?php Avada()->layout->add_style( 'content_style' ); ?>>
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php echo fusion_render_rich_snippets_for_pages(); // WPCS: XSS ok. ?>
			<?php avada_featured_images_for_pages(); ?>
			<div class="post-content">
				<?php the_content(); ?>

				<?php if ( ! Avada()->settings->get( 'email_address' ) ) : // Email address not set. ?>
					<?php if ( shortcode_exists( 'fusion_alert' ) ) : ?>
						<?php echo do_shortcode( '[fusion_alert type="error"]' . esc_html__( 'Form email address is not set in Theme Options. Please fill in a valid address to make contact form work.', 'Avada' ) . '[/fusion_alert]' ); ?>
					<?php else : ?>
						<h3 style="color:#b94a48;"><?php esc_html_e( 'Form email address is not set in Theme Options. Please fill in a valid address to make contact form work.', 'Avada' ); ?></h3>
					<?php endif; ?>
					<br />
				<?php endif; ?>

				<?php if ( $avada_contact->has_error ) : // If errors are found. ?>
					<?php if ( shortcode_exists( 'fusion_alert' ) ) : ?>
						<?php echo do_shortcode( '[fusion_alert type="error"]' . esc_html( $avada_contact->error_message ) . '[/fusion_alert]' ); ?>
					<?php else : ?>
						<h3 style="color:#b94a48;"><?php echo esc_html( $avada_contact->error_message ); ?></h3>
					<?php endif; ?>
					<br />
				<?php endif; ?>

				<?php if ( $avada_contact->email_sent && Avada()->settings->get( 'email_address' ) ) : // If email is sent. ?>
					<?php if ( shortcode_exists( 'fusion_alert' ) ) : ?>
						<?php /* translators: The name from the contact form. */ ?>
						<?php echo do_shortcode( '[fusion_alert type="success"]' . sprintf( __( 'Thank you %s for using our contact form! Your email was successfully sent!', 'Avada' ), '<strong>' . $avada_contact->name . '</strong>' ) . '[/fusion_alert]' ); ?>
					<?php else : ?>
						<?php /* translators: The name from the contact form. */ ?>
						<h3 style="color:#468847;"><?php printf( esc_html__( 'Thank you %s for using our contact form! Your email was successfully sent!', 'Avada' ), '<strong>' . esc_html( $avada_contact->name ) . '</strong>' ); ?></h3>
					<?php endif; ?>
					<br />
				<?php endif; ?>
			</div>

			<form action="" method="post" class="avada-contact-form">
				<?php if ( 'above' === Avada()->settings->get( 'contact_comment_position' ) ) : ?>
					<div id="comment-textarea">
						<textarea name="msg" id="comment" cols="39" rows="4" tabindex="4" class="textarea-comment" placeholder="<?php esc_attr_e( 'Message', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Message', 'Avada' ); ?>"><?php echo esc_textarea( $avada_contact->message ); // WPCS: CSRF ok. ?></textarea>
					</div>
				<?php endif; ?>

				<div id="comment-input">
					<input type="text" name="contact_name" id="author" value="<?php echo esc_attr( $avada_contact->name ); ?>" placeholder="<?php esc_attr_e( 'Name (required)', 'Avada' ); ?>" size="22" required aria-required="true" aria-label="<?php esc_attr_e( 'Name (required)', 'Avada' ); ?>" class="input-name">
					<input type="email" name="email" id="email" value="<?php echo esc_attr( $avada_contact->email ); ?>" placeholder="<?php esc_attr_e( 'Email (required)', 'Avada' ); ?>" size="22" required aria-required="true" aria-label="<?php esc_attr_e( 'Email (required)', 'Avada' ); ?>" class="input-email">
					<input type="text" name="url" id="url" value="<?php echo esc_attr( $avada_contact->subject ); ?>" placeholder="<?php esc_attr_e( 'Subject', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Subject', 'Avada' ); ?>" size="22" class="input-website">
				</div>

				<?php if ( 'above' !== Avada()->settings->get( 'contact_comment_position' ) ) : ?>
					<div id="comment-textarea" class="fusion-contact-comment-below">
						<textarea name="msg" id="comment" cols="39" rows="4" class="textarea-comment" placeholder="<?php esc_attr_e( 'Message', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Message', 'Avada' ); ?>"><?php echo esc_textarea( $avada_contact->message ); // WPCS: CSRF ok. ?></textarea>
					</div>
				<?php endif; ?>

				<?php if ( Avada()->settings->get( 'contact_form_privacy_checkbox' ) ) : ?>
					<div id="comment-privacy-checkbox-wrapper" class="fusion-comment-privacy-checkbox-wrapper">
						<input type="checkbox" value="1" <?php checked( $avada_contact->data_privacy_confirmation, 1 ); ?> required aria-required="true" id="data-privacy-confirmation" name="data_privacy_confirmation" class="fusion-comment-privacy-checkbox" />
						<label for="data-privacy-confirmation"><?php echo Avada()->settings->get( 'contact_form_privacy_label' ); // WPCS: XSS ok. ?></label>
					</div>
				<?php endif; ?>

				<?php if ( Avada()->settings->get( 'recaptcha_public' ) && Avada()->settings->get( 'recaptcha_private' ) ) : ?>
					<div id="comment-recaptcha">
						<?php if ( 'v2' === Avada()->settings->get( 'recaptcha_version' ) ) : ?>
							<div class="g-recaptcha" data-type="audio" data-theme="<?php echo esc_attr( Avada()->settings->get( 'recaptcha_color_scheme' ) ); ?>" data-sitekey="<?php echo esc_attr( Avada()->settings->get( 'recaptcha_public' ) ); ?>"></div>
						<?php else : ?>
							<?php $hide_badge_class   = 'hide' === Avada()->settings->get( 'recaptcha_badge_position' ) ? ' fusion-hide-recaptcha-badge' : ''; ?>
							<div id="recaptcha-container" class="recaptcha-container<?php echo esc_attr( $hide_badge_class ); ?>"></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div id="comment-submit-container">
					<?php
					global $fusion_settings;
					if ( ! $fusion_settings ) {
						$fusion_settings = Fusion_Settings::get_instance();
					}

					$button_shape = $fusion_settings->get( 'button_shape' );
					$button_size  = $fusion_settings->get( 'button_size' );
					$button_type  = $fusion_settings->get( 'button_type' );
					?>
					<?php if ( 'v3' === Avada()->settings->get( 'recaptcha_version' ) ) : ?>
						<input type="hidden" name="fusion-recaptcha-response" id="fusion-recaptcha-response" value="">
					<?php endif; ?>

					<input name="submit" type="submit" id="submit" value="<?php esc_html_e( 'Submit Form', 'Avada' ); ?>" class="comment-submit fusion-button fusion-button-default fusion-button-default-size fusion-button-<?php echo esc_attr( strtolower( $button_size ) ); ?> fusion-button-<?php echo esc_attr( strtolower( $button_shape ) ); ?> fusion-button-<?php echo esc_attr( strtolower( $button_type ) ); ?>">
				</div>
			</form>
		</div>
	<?php endwhile; ?>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
