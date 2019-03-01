<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

function cs_abbua_admin_welcome_page(){
    $plugin_name    = 'ABBUA Admin';
    $plugin_uri     = 'abbua-admin';
    $plugin_version = PLUGIN_NAME_VERSION;
	?>
	<div class="wrap cs-plugin-home">
        <h1><?=$plugin_name?> <?php _e('Welcome Page', 'abbua_admin'); ?></h1>
		<div class="cs-header">
            <div class="cs-header__title">
                <h1><?php echo sprintf( __( 'Welcome to <strong>%s</strong>', 'abbua_admin' ), $plugin_name .' '. $plugin_version ) ?></h1>
            </div>
            <div class="cs-header__content">
                <div class="cs-header__links">
                    <div class="cs-header__link">
                        <i class="fei fei-help-circle"></i>
                        <a href="<?= CS_PLUGIN_URL . '/docs/'; ?>" target="_blank"><?php _e('Online Documentation','abbua_admin') ?></a>
                    </div>
                    <div class="cs-header__link">
                        <i class="fei fei-life-buoy"></i>
                        <a href="<?= CS_SUPPORT_URL; ?>" target="_blank"><?php _e('Support Portal','abbua_admin') ?></a>
                    </div>
                    <div class="cs-header__link">
                        <i class="fei fei-rotate-ccw"></i>
                        <a href="<?= CS_SUPPORT_URL . '/change-log/'; ?>" target="_blank"><?php _e('Plugin Changelog','abbua_admin') ?></a>
                    </div>
                </div>
                <div class="cs-header__about-text">
                    <?php _e("First off all, thanks for considering our work, we love you! <br> We made this plugin with all our heart, taking care of every small element, motion, interaction and customizing capabilities, so you can entirely focus on creating great things. We sincerely hope you'll enjoy it the same as we do! ",'abbua_admin'); ?>
                </div>
            </div>
		</div>

		<div class="cs-features">
            <div class="one-third">
                <h4><i class="dashicons dashicons-admin-appearance"></i><?php _e( 'Customize Appearance','abbua_admin') ?></h4>
                <p><?php _e('We have paid special attention to the possibility of customizing each section in the way you want, so we have put at your disposal a large number of themes and configuration options, that way our theme will fit your needs.','abbua_admin'); ?></p>
            </div>
			<div class="one-third">
				<h4><i class="fei fei-video"></i><?php _e('Video Tutorials','abbua_admin') ?></h4>
				<p><?php _e('We have prepared video tutorials to guide you in the configuration of each aspect of the plugin, so if you need help go ahead and watch them!','abbua_admin'); ?></p>
			</div>
			<div class="one-third">
				<h4><i class="fei fei-life-buoy"></i><?php _e('Get in touch','abbua_admin'); ?></h4>
				<p><?php _e('Do you have questions or suggestions? Feel free to contact us through our official website or via our Envato profile. We are available every day!','abbua_admin'); ?></p>
				<a class="button cs-button" href="<?= CS_SUPPORT_URL . '/contact/'; ?>" target="_blank"><?php _e( 'Go to Contact page','abbua_admin') ?></a>
			</div>
		</div>
	</div>
	<?php
}
