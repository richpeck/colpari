<?php 

if ( ! defined( 'WPINC' ) ) {
	die;
} 

?>

<form class="wpas-form" id="wpas_form_guest_login" method="post" action="<?php echo esc_attr( get_permalink( $post->ID ) ); ?>">

    <input type="hidden" name="form-action" value="register" />

    <h3><?php _e( 'Guest Log in', 'as-guest-tickets' ); ?></h3>

    <?php
    // Email.
    $email = new \WPAS_Custom_Field( 'gt_email', array(
        'name' => 'email',
        'args' => array(
            'required'      => true,
            'field_type'    => 'email',
            'label'         => __( 'E-mail Address', 'as-guest-tickets' ),
            'sanitize'      => 'sanitize_email',
            'save_callback' => '__return_true', // Skip saving on submit.
            'desc'			=> wpas_get_option( 'gt_reg_email_desc', '' ),
            'placeholder'   => __( 'E-mail address', 'as-guest-tickets' ),
        )
    ) );

    echo $email->get_output();

    ?>

    
    <?php 
        // Privacy Notice Checkbox and description
        if ( ! empty( $short_desc = wpas_get_option( 'gt_privacy_notice_short_desc_01', '' ) ) ): ?>

        <div class="wpas_gt_privacy_div">

            <input type="checkbox" name="privacy_notice" value="1"> 

            <a href="#" class="wpas-gt-show-notice"><?php echo $short_desc; ?></a> 

            <div class="wpas-gt-notice-box">

                <a href="#" class="wpas-gt-close-icon wpas-gt-close">x</a>

                <div class="wpas-gt-notice-box-content">
                    
                    <h3><?php echo $short_desc ?></h3>

                    <div class="wpas-gt-notice-content">
                        <?php echo wpas_get_option( 'gt_privacy_notice_long_desc_01', '' ); ?>
                    </div>

                    <a href="#" class="wpas-btn wpas-btn-default wpas-gt-accept"><?php _e( 'Accept', 'as-guest-tickets'  ); ?></a>
                    <a href="#" class="wpas-btn wpas-gt-close"><?php _e( 'Cancel', 'as-guest-tickets'  ); ?></a>
                </div>

            </div>

        </div>

    <?php endif; ?>
    
    <div id="wpas_gt_info_div"></div>
    
    <?php

        $password = new \WPAS_Custom_Field( 'gt_pass', array(
            'name' => 'pass',
            'args' => array(
                'required'    => false,
                'field_type'  => 'password',
                'label'       => __( 'Password', 'as-guest-tickets' ),
                'placeholder' => __( 'Password', 'as-guest-tickets' ),
                'sanitize'    => 'sanitize_text_field',
            )
        ) );

    ?>
    
    <div class="wpas_gt_password_div" style="display:none;">
        <?php echo $password->get_output(); ?>
    </div>
    
    <?php

        /**
         * Fires immediate after the fields in the Guest Log In are output.
         *
         * @since 1.0.0
         *
         * @param WP_Post $post Global post object.
         */
        do_action( 'wpas_after_guest_login_fields', $post );

        // Login button, hidden by default.
    ?>
   
    
    <div class="wpas_gt_guest_div">
        <button type="submit" class="wpas-btn wpas-btn-default" name="guest-register" id="wpas-gt-guest-login"><?php _e( 'Continue as a Guest', 'as-guest-tickets' ); ?></button>
    </div>

    <div class="wpas_gt_login_div" style="display:none;">
        <button type="submit" class="wpas-btn wpas-btn-default" name="guest-login" id="wpas-gt-guest-register"><?php _e( 'Log In', 'as-guest-tickets' ); ?></button>
    </div>

    
    <?php if ( wpas_get_option( 'gt_use_recaptcha', false ) ): ?>

        <script type="text/javascript">
            var gt_onload_callback = function() {

                var ids = ['wpas-gt-guest-login', 'wpas-gt-guest-register'];

                for ( var i in ids ) {
                    grecaptcha.render( ids[i], {
                        'sitekey' : '<?php echo wpas_get_option( 'gt_recaptcha_site_key' ); ?>',
                        'callback' : 'gt_recaptcha_callback',
                    });
                }

            };
        </script>

    <?php endif; ?>


    <div id="wpas_gt_status_div"></div>

</form>