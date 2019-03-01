<?php
/**
 * Template
 *
 * @package Fusion-White-Label-Branding
 */

?>
<div class="wrap about-wrap fusion-white-label-branding-wrap">

	<?php Fusion_White_Label_Branding_Admin::header(); ?>
	<div class="fusion-white-label-branding-faq-content">
		<div class="fusion-white-label-branding-important-notice">
			<p class="about-description">
				<?php /* translators: URL */ ?>
				<?php printf( __( 'These are general Frequently Asked Questions to help you get started. For detailed documentation please <a href="%1$s" target="_blank">click here</a> or if you require support please visit our <a href="%2$s" target="_blank">online support center</a>.', 'fusion-white-label-branding' ), 'https://theme-fusion.com/avada-doc/fusion-white-label-branding-plugin', 'https://theme-fusion.com/support/' ); // WPCS: XSS ok. ?>
			</p>
		</div>
		<div class="fusion-white-label-branding-admin-toggle">
			<div class="fusion-white-label-branding-admin-toggle-heading">
				<h3><?php esc_html_e( 'What Is The Fusion White Label Branding?', 'fusion-white-label-branding' ); ?></h3>
				<span class="fusion-white-label-branding-admin-toggle-icon fusion-plus"></span>
			</div>
			<div class="fusion-white-label-branding-admin-toggle-content">
				<?php /* translators: %1$s: Avada, %2$s: Fusion Builder */ ?>
				<?php printf( esc_html__( 'Fusion White Label Branding is an add-on for %1$s that allows you to alter the default %1$s branding, WordPress menus, user access, %2$s settings and the default WordPress login screen, to make it your own.', 'fusion-white-label-branding' ), 'Avada', 'Fusion Builder' ); ?><br/><br/>
			</div>
		</div>

		<div class="fusion-white-label-branding-admin-toggle">
			<div class="fusion-white-label-branding-admin-toggle-heading">
				<h3><?php esc_html_e( 'How Do I Get Support For The Fusion White Label Branding?', 'fusion-white-label-branding' ); ?></h3>
				<span class="fusion-white-label-branding-admin-toggle-icon fusion-plus"></span>
			</div>
			<div class="fusion-white-label-branding-admin-toggle-content">
				<?php /* translators: %1$s: Avada, %2$s: URL, %3$s: _blank. */ ?>
				<?php printf( __( 'Fusion White Label Branding is an add-on that is purpose-built for the %1$s WordPress theme, version 5.4 and newer. For detailed documentation please <a href="%2$s" target="%3$s">click here</a> or if you require support please visit our online support center.', 'fusion-white-label-branding' ), 'Avada', 'https://theme-fusion.com/avada-doc/getting-started/avada-theme-support/', '_blank' ); // WPCS: XSS ok. ?>
			</div>
		</div>

		<div class="fusion-white-label-branding-admin-toggle">
			<div class="fusion-white-label-branding-admin-toggle-heading">
				<h3><?php esc_html_e( 'Can I Use Fusion White Label Branding To Re-brand Other Themes?', 'fusion-white-label-branding' ); ?></h3>
				<span class="fusion-white-label-branding-admin-toggle-icon fusion-plus"></span>
			</div>
			<div class="fusion-white-label-branding-admin-toggle-content">
				<?php /* translators: Avada */ ?>
				<?php printf( esc_html__( 'Unfortunately, that is not possible. The Fusion White Label Branding add-on is purpose-built for the %s WordPress theme only.', 'fusion-white-label-branding' ), 'Avada' ); ?>
			</div>
		</div>

		<div class="fusion-white-label-branding-admin-toggle">
			<div class="fusion-white-label-branding-admin-toggle-heading">
				<?php /* translators: Avada */ ?>
				<h3><?php printf( esc_html__( 'I Am Running An Old Version of %s, Can I Use The Fusion White Label Branding?', 'fusion-white-label-branding' ), 'Avada' ); ?></h3>
				<span class="fusion-white-label-branding-admin-toggle-icon fusion-plus"></span>
			</div>
			<div class="fusion-white-label-branding-admin-toggle-content">
				<?php /* translators: Avada */ ?>
				<?php printf( esc_html__( 'The Fusion White Label Branding add-on compatibility is %2$s guaranteed for %1$s 5.4 and newer only. Various facets of the add-on will not work as expected with older versions of %1$s.', 'fusion-white-label-branding' ), 'Avada', '100%' ); ?>
			</div>
		</div>
	</div>
	<?php Fusion_White_Label_Branding_Admin::footer(); ?>
</div>
