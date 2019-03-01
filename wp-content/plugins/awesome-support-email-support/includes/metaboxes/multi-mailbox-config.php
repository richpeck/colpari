<?php
/**
 * This is called via a metabox declaration callback.
 *
 * It will display the contents of a single mailbox-config posttype
 *
 * Incoming params: $post automatically passed and declared by WordPress
 *
 **/
?>
<div id="multi-mailbox-config">
	<?php
	/**
	 * Set action to execute before displaying the rest of the config screen...
	 *
	 * @since  5.0.0
	 */
	do_action( 'wpas_backend_multi_mailbox_config_before', $post->ID, $post );  // note that $post is a variable that is passed in
	?>

	<?php
	// Get data from the server about this mailbox-config post
	$email_server   = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_email_server', true ) );
	$protocol       = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_protocol', true ) );
	$username       = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_username', true ) );
	$password       = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_password', true ) );
	$port           = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_port', true ) );
	$secureportflag = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_secureportflag', true ) );
	$timeout        = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_timeout', true ) );
	$activeflag     = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_active', true ) );

	$defaultassignee = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultassignee', true ) );
	$defaultdept     = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultdept', true ) );
	$defaultproduct  = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultproduct', true ) );
	$defaultpriority = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultpriority', true ) );
	$defaultchannel  = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultchannel', true ) );

	$defaultstatus     = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultstatus', true ) );
	$defaultpublicflag = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultpublicflag', true ) );

	$defaultaddlpartyemail1 = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultaddlpartyemail1', true ) );
	$defaultaddlpartyemail2 = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultaddlpartyemail2', true ) );

	$defaultsecondaryassignee = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaultsecondaryassignee', true ) );
	$defaulttertiaryassignee  = esc_html( get_post_meta( $post->ID, 'wpas_multimailbox_defaulttertiaryassignee', true ) );


	//Handle some data transformations and defaults
	if( strtoupper( $protocol ) <> 'IMAP' && strtoupper( $protocol ) <> 'POP3' ) {
		$protocol = "imap";
	}

	if( empty( $port ) ) {
		$port = 993;
	}

	if( empty( $timeout ) ) {
		$timeout = 120;
	}

	if( empty( $defaultchannel ) ) {
		$defaultchannel = '';  // later we should look up the 'email' slug in the channel taxonomy and get the ID for it and use it as the default here.
	}

	?>

    <table id="wpas-es-config-wrapper" class="form-table">

        <tr valign="top">
            <td colspan="2" class="tf-text section-row">
				<?php _e( 'This screen should only be used to configure ADDITIONAL support e-mail inboxes.  If you have just one inbox, please configure it under the TICKETS->SETTINGS->E-mail Piping tab.', 'as-email-support' ) ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_email_server"> <?php _e( 'E-Mail Server', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text" name="html_multimailbox_email_server" value="<?php echo $email_server ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_protocol"> <?php _e( 'Protocol', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-radio">
                <fieldset>
                <input type="radio"
                       name="html_multimailbox_protocol" id="pop3"
                       value="pop3" <?php echo( $protocol == 'pop3' ? 'checked' : '' ) ?> >
                <label for="pop3"> <?php _e( 'Pop3', 'as-email-support' ) ?> </label><br/>

                <input type="radio"
                       name="html_multimailbox_protocol" id="imap"
                       value="imap" <?php echo( $protocol == 'imap' ? 'checked' : '' ) ?> >
                <label for="imap"> <?php _e( 'IMAP', 'as-email-support' ) ?> </label><br/>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_user_name"> <?php _e( 'Email Account or Username', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_multimailbox_user_name"
                       name="html_multimailbox_user_name"
                       value="<?php echo $username ?>"/>
                <p class="description"> <?php _e( 'This is typically your email address but could be something else - check with your email service provider if you are unsure.', 'as-email-support' ) ?> </p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_password"> <?php _e( 'Password', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="password"
                       id="html_multimailbox_password"
                       name="html_multimailbox_password"
                       value="<?php echo $password ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_port"> <?php _e( 'Port', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="number"
                       id="html_multimailbox_port"
                       name="html_multimailbox_port"
                       value="<?php echo $port ?>"/>
                <p class="description"> <?php _e( 'Usually: <b>995</b> for pop3 and <b>993</b> for imap when used with SSL security. This item should NOT be blank!', 'as-email-support' ) ?> </p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_secureportflag"> <?php _e( 'Secure Protocol', 'as-email-support' ) ?> </label>
            </th>
			
            <td class="second tf-radio">
                <fieldset>
                <input type="radio"
                       name="html_multimailbox_secureportflag" id="ssl"
                       value="ssl" <?php echo( $secureportflag == 'ssl' ? 'checked' : '' ) ?> >
                <label for="ssl"> <?php _e( 'SSL', 'as-email-support' ) ?> </label><br/>

                <input type="radio"
                       name="html_multimailbox_secureportflag" id="tls"
                       value="tls" <?php echo( $secureportflag == 'tls' ? 'checked' : '' ) ?> >
                <label for="tls"> <?php _e( 'TLS', 'as-email-support' ) ?> </label><br/>
				
                <input type="radio"
                       name="html_multimailbox_secureportflag" id="nosecurity"
                       value="none" <?php echo( $secureportflag == 'none' ? 'checked' : '' ) ?> >
                <label for="nosecurity"> <?php _e( 'None', 'as-email-support' ) ?> </label><br/>				
                </fieldset>
            </td>
			
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_timeout"> <?php _e( 'Timeout', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="number"
                       id="html_multimailbox_timeout"
                       name="html_multimailbox_timeout"
                       value="<?php echo $timeout ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_activeflag"> <?php _e( 'Active?', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-checkbox">
                <fieldset>
                <input type="checkbox"
                       id="html_multimailbox_activeflag"
                       name="html_multimailbox_activeflag"
                       value="1" <?php echo( $activeflag == '1' && false === empty( $activeflag ) ? 'checked' : '' ) ?> />
                <p class="description"> <?php _e( 'Check this to activate reading emails from this mailbox', 'as-email-support' ) ?> </p>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_assignee"> <?php _e( 'Default Assignee/Agent', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php wpas_es_show_assignee_dropdown_simple( "html_multimailbox_default_assignee", "", $defaultassignee ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_dept"> <?php _e( 'Default Department', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'department', "html_multimailbox_default_dept", "wpas-multi-inbox-config-item wpas-multi-inbox-config-item-select", $defaultdept ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_product"> <?php _e( 'Default Product', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'product', "html_multimailbox_default_product", "wpas-multi-inbox-config-item wpas-multi-inbox-config-item-select", $defaultproduct ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_priority"> <?php _e( 'Default Priority', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'ticket_priority', "html_multimailbox_default_priority", "wpas-multi-inbox-config-item wpas-multi-inbox-config-item-select", $defaultpriority ); ?>
                <p class="description"></p>
            </td>
        </tr>		
		
        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_channel"> <?php _e( 'Default Channel', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'ticket_channel', "html_multimailbox_default_channel", "wpas-multi-inbox-config-item wpas-multi-inbox-config-item-select", $defaultchannel ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_status"> <?php _e( 'Default status', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_multimailbox_default_status"
                       name="html_multimailbox_default_status"
                       value="<?php echo $defaultstatus ?>"/>
                <p class="description"> <?php _e( 'This is a string that represents the custom status. Generally it is the same as the custom status value converted to lowercase characters but can ocasionally differ.', 'as-email-support' ) ?> </p>
                <p class="description"> <?php _e( 'If there are spaces in your custom status then use a dash in place of the space - eg: my-custom-status.', 'as-email-support' ) ?> </p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_public_flag"> <?php _e( 'Default Public Flag', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-radio">
                <fieldset>
                <input type="radio"
                       name="html_multimailbox_default_public_flag" id="public"
                       value="public" <?php echo( $defaultpublicflag == 'public' ? 'checked' : '' ) ?> >
                <label for="public]"> <?php _e( 'Public', 'as-email-support' ) ?> </label><br/>

                <input type="radio"
                       name="html_multimailbox_default_public_flag" id="private"
                       value="private" <?php echo( $defaultpublicflag == 'private' ? 'checked' : '' ) ?> >
                <label for="private"> <?php _e( 'Private', 'as-email-support' ) ?> </label><br/>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_addl_party_email_1"> <?php _e( 'Default Addl 3rd Party Email #1', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_multimailbox_default_addl_party_email_1"
                       name="html_multimailbox_default_addl_party_email_1"
                       value="<?php echo $defaultaddlpartyemail1 ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_addl_party_email_2"> <?php _e( 'Default Addl 3rd Party Email #2', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_multimailbox_default_addl_party_email_2"
                       name="html_multimailbox_default_addl_party_email_2"
                       value="<?php echo $defaultaddlpartyemail2 ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_secondary_assignee"> <?php _e( 'Default Secondary Assignee/Agent', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php wpas_es_show_assignee_dropdown_simple( "html_multimailbox_default_secondary_assignee", "", $defaultsecondaryassignee ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_multimailbox_default_tertiary_assignee"> <?php _e( 'Default Tertiary Assignee/Agent', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php wpas_es_show_assignee_dropdown_simple( "html_multimailbox_default_tertiary_assignee", "", $defaulttertiaryassignee ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <td colspan="2" class="tf-text section-footer">
				<?php _e( 'Warning! Please keep in mind that the more in-boxes you add, the slower importing emails will be.  Your host might not allow you the time needed to run a long script which could cause importing to timeout and fail when there are many inboxes.', 'as-email-support' ) ?>
            </td>
        </tr>

    </table>

	<?php
	/**
	 * Set action to execute after displaying the the config screen...
	 *
	 * @since  5.0.0
	 */
	do_action( 'wpas_backend_multi_mailbox_config_after', $post->ID, $post );  // note that $post is a variable that is passed in
	?>

</div>