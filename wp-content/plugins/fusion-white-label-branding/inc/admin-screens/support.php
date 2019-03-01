<?php
/**
 * Template
 *
 * @package Fusion-White-Label-Branding
 */

?>
<div class="wrap about-wrap fusion-white-label-branding-wrap">

	<?php Fusion_White_Label_Branding_Admin::header(); ?>
	<div class="fusion-white-label-branding-support-content">
		<?php
		$theme_fusion_url = 'https://theme-fusion.com/';
		?>
		<div class="fusion-white-label-branding-important-notice">
			<?php /* translators: %1$s: Avada, %2$s: link, %3%s: link */ ?>
			<p class="about-description"><?php printf( __( 'Fusion White Label Branding is bundled with %1$s and is therefore covered by the 6 months of free support for every theme license you purchase. Theme support can be <a %2$s>extended through subscriptions</a> via ThemeForest. All support for Fusion White Label Branding is handled via the the support center on our company site. To access it, you must first setup an account by <a %3$s>following these steps</a>. If you purchased using Envato\'s guest checkout <a href="%4$s" target="_blank">please view this link</a> to create an Envato account before receiving item support. Below are all the resources we offer in our support center and community.', 'fusion-white-label-branding' ), 'Avada', 'a href="https://help.market.envato.com/hc/en-us/articles/207886473-Extending-and-Renewing-Item-Support" target="_blank"', 'href="https://theme-fusion.com/avada-doc/getting-started/avada-theme-support/" target="_blank"', 'https://help.market.envato.com/hc/en-us/articles/217397206-A-Guide-to-Using-Guest-Checkout' ); // WPCS: XSS ok. ?></p>
			<p><a href="https://theme-fusion.com/avada-doc/getting-started/avada-theme-support/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Create A Support Account', 'fusion-white-label-branding' ); ?></a></p>
		</div>

		<div class="fusion-white-label-branding-registration-steps">

			<div class="col three-col">
				<div class="col">
					<h3 class="title"><span class="dashicons dashicons-sos"></span><?php echo esc_html__( 'Submit A Ticket', 'fusion-white-label-branding' ); ?></h3>
					<p><?php esc_html_e( 'We offer excellent support through our advanced ticket system. Make sure to register your purchase first to access our support services and other resources.', 'fusion-white-label-branding' ); ?></p>
					<a href="<?php echo esc_url( $theme_fusion_url ); ?>support-ticket/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Submit A Ticket', 'fusion-white-label-branding' ); ?></a>
				</div>
				<div class="col">
					<h3 class="title"><span class="dashicons dashicons-book"></span><?php esc_html_e( 'Documentation', 'fusion-white-label-branding' ); ?></h3>
					<p><?php esc_html_e( 'This is the place to go to reference different aspects of Fusion White Label Branding. Our online documentaiton is organized and provides the information to get you started.', 'fusion-white-label-branding' ); ?></p>
					<a href="<?php echo esc_url( $theme_fusion_url ); ?>avada-doc/fusion-white-label-branding-plugin/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'fusion-white-label-branding' ); ?></a>
				</div>
				<div class="col last-feature">
					<h3 class="title"><span class="dashicons dashicons-portfolio"></span><?php esc_html_e( 'Knowledgebase', 'fusion-white-label-branding' ); ?></h3>
					<p><?php esc_html_e( 'Our knowledgebase contains additional content that is not inside of our documentation. This information is more specific and unique to various versions or aspects of Fusion White Label Branding.', 'fusion-white-label-branding' ); ?></p>
					<a href="<?php echo esc_url( $theme_fusion_url ); ?>support/knowledgebase/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank"><?php esc_html_e( 'Knowledgebase', 'fusion-white-label-branding' ); ?></a>
				</div>
				<div class="col">
					<h3 class="title"><span class="dashicons dashicons-format-video"></span><?php esc_html_e( 'Video Tutorials', 'fusion-white-label-branding' ); ?></h3>
					<p><?php esc_html_e( 'Nothing is better than watching a video to learn. We have a growing library of high-definititon, narrated video tutorials to help teach you the different aspects of using Fusion White Label Branding.', 'fusion-white-label-branding' ); ?></p>
					<a href="<?php echo esc_url( $theme_fusion_url ); ?>support/video-tutorials/fusion-builder-videos/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Watch Videos', 'fusion-white-label-branding' ); ?></a>
				</div>
				<div class="col">
					<h3 class="title"><span class="dashicons dashicons-groups"></span><?php esc_html_e( 'Community Forum', 'fusion-white-label-branding' ); ?></h3>
					<p><?php esc_html_e( 'We have a community forum for user to user interactions. Ask and share information with other Fusion White Label Branding users. Please note that ThemeFusion does not provide product support here.', 'fusion-white-label-branding' ); ?></p>
					<a href="<?php echo esc_url( $theme_fusion_url ); ?>forums/forum/fusion-builder-community-forum/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Community Forum', 'fusion-white-label-branding' ); ?></a>
				</div>
				<div class="col last-feature">
					<h3 class="title"><span class="dashicons dashicons-facebook"></span><?php esc_html_e( 'Facebook Group', 'fusion-white-label-branding' ); ?></h3>
					<p><?php esc_html_e( 'There is a Facebook Group to help build a community of mutual users willing to help one another for Fusion White Label Branding! Come, share and help grow the community!', 'fusion-white-label-branding' ); ?></p>
					<a href="https://www.facebook.com/groups/AvadaUsers/" class="button button-large button-primary fusion-white-label-branding-large-button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook Group', 'fusion-white-label-branding' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<?php Fusion_White_Label_Branding_Admin::footer(); ?>
</div>
