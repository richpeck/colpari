<?php
/**
 * This is called via a metabox declaration callback.
 *
 * It will display the contents of a single inbox rule CPT.
 *
 * Incoming params: $post automatically passed and declared by WordPress
 *
 **/
?>
<div id="inbox-rules-config">
	<?php
	/**
	 * Set action to execute before displaying the rest of the inbox rules config screen...
	 *
	 * @since  5.0.0
	 */
	do_action( 'wpas_backend_inbox_rules_config_before', $post->ID, $post );  // note that $post is a variable that is passed in
	?>

	<?php
	// Get data from the server about this inbox rule
	$rule_type     = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_type', true ) );    // can be 'regex' or 'normal'
	$rule_contents = get_post_meta( $post->ID, 'wpas_inboxrules_rule_contents', true );            // either a regex rule or a series of characters to be matched.  Do not escape this value otherwise key characters will be lost from a regex expression!
	$rule_area     = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_area', true ) );    // What area does a rule apply? "subject", "body", "both"
	$rule_active   = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_active', true ) );  // can be 1 / 0 (checkbox) 1=active 0=inactive
	$rule_action   = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_action', true ) );  // can be 1 = drop completely; 2 = add to unassigned inbox
	$rule_notes    = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_notes', true ) );    // user defined notes to clarify what the rule is trying to accomplish

	// Get additional data for fields that might need to be updated when this rule is executed.
	$new_assignee = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_assignee', true ) );
	$new_dept     = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_dept', true ) );
	$new_product  = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_product', true ) );
	$new_priority = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_priority', true ) );
	$new_channel  = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_channel', true ) );

	$new_status      = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_status', true ) );
	$new_public_flag = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_public_flag', true ) );

	$new_addlparty_email1 = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_addlparty_email1', true ) );
	$new_addlparty_email2 = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_addlparty_email2', true ) );

	$new_secondary_assignee = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_secondary_assignee', true ) );
	$new_tertiary_assignee  = esc_html( get_post_meta( $post->ID, 'wpas_inboxrules_rule_new_tertiary_assignee', true ) );

	// Set some defaults...
	if( true === empty( $rule_type ) ) {
		$rule_type = 'normal';
	}

	if( true === empty( $rule_area ) ) {
		$rule_area = 'subject';
	}

	if( true === empty( $rule_action ) ) {
		$rule_action = 'unassigned';
	}

	?>

    <table id="wpas-es-config-wrapper" class="form-table">

        <tr valign="top">
            <td colspan="2" class="tf-text section-row">
				<?php _e( 'This screen is used to configure rules for incoming emails. Rules that are matched to incoming messages will have the specified ACTION applied to the email/ticket.', 'as-email-support' ) ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inbox_rules_type"> <?php _e( 'Rule Type', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <fieldset>
                <input type="radio"
                       name="html_inboxrules_rule_type" id="normal"
                       value="normal" <?php echo( $rule_type == 'normal' ? 'checked' : '' ) ?> >
                <label for="normal"> <?php _e( 'Normal', 'as-email-support' ) ?> </label><br/>
                <input type="radio"
                       name="html_inboxrules_rule_type" id="regex"
                       value="regex" <?php echo( $rule_type == 'regex' ? 'checked' : '' ) ?> >
                <label for="regex"> <?php _e( 'Regex', 'as-email-support' ) ?> </label>
                <p class="description"><?php _e( 'A normal rule will do a straight non-case sensitive string match. A regex rule will use PHP regular expressions to search for a match.', 'as-email-support' ) ?></p>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inbox_rules_type"> <?php _e( 'Rule', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_inboxrules_rule_contents"
                       name="html_inboxrules_rule_contents"
                       value="<?php echo $rule_contents ?>"/>
                <p class="description"><?php _e( 'Enter the rule criteria here.  Either a regex rule or a series of characters to be matched. Normal rules are NOT case-sensitive.', 'as-email-support' ) ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_area"> <?php _e( 'Where Should This Rule Apply?', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-radio">
                <fieldset>
                <input type="radio"
                       name="html_inboxrules_rule_area" id="subject"
                       value="subject" <?php echo( $rule_area == 'subject' ? 'checked' : '' ) ?> >
                <label for="subject"> <?php _e( 'Email Subject Line', 'as-email-support' ) ?> </label><br/>

                <input type="radio"
                       name="html_inboxrules_rule_area" id="body"
                       value="body" <?php echo( $rule_area == 'body' ? 'checked' : '' ) ?> >
                <label for="body"> <?php _e( 'Email Body', 'as-email-support' ) ?> </label><br/>

                <input type="radio"
                       name="html_inboxrules_rule_area" id="header"
                       value="header" <?php echo( $rule_area == 'header' ? 'checked' : '' ) ?> >
                <label for="header"> <?php _e( 'Senders email address', 'as-email-support' ) ?> </label><br/>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_active"> <?php _e( 'Is Rule Active?', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-checkbox">
                <fieldset>
                <input type="checkbox"
                       id="html_inboxrules_rule_active"
                       name="html_inboxrules_rule_active"
                       value="1" <?php echo( $rule_active == '1' && false === empty( $rule_active ) ? 'checked' : '' ) ?>
                <p class="description"></p>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_action"> <?php _e( 'Action', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-radio">
                <fieldset>
                <input type="radio"
                       name="html_inboxrules_rule_action" id="drop"
                       value="drop" <?php echo( $rule_action == 'drop' ? 'checked' : '' ) ?> >
                <label for="drop"> <?php _e( 'Delete the email without keeping a record of it', 'as-email-support' ) ?> </label><br/>
                <input type="radio"
                       name="html_inboxrules_rule_action" id="unassigned"
                       value="unassigned" <?php echo( $rule_action == 'unassigned' ? 'checked' : '' ) ?> >
                <label for="unassigned"> <?php _e( 'Place the email in the UNASSIGNED tickets area', 'as-email-support' ) ?> </label><br/>
                <input type="radio"
                       name="html_inboxrules_rule_action" id="update"
                       value="update" <?php echo( $rule_action == 'update' ? 'checked' : '' ) ?> >
                <label for="update"> <?php _e( 'Update the ticket with the values in the update section below', 'as-email-support' ) ?> </label><br/>
                <input type="radio"
                       name="html_inboxrules_rule_action" id="update_and_close"
                       value="update_and_close" <?php echo( $rule_action == 'update_and_close' ? 'checked' : '' ) ?> >
                <label for="update"> <?php _e( 'Update the ticket with the values in the update section below and then close the ticket (reply will be added to ticket)', 'as-email-support' ) ?> </label><br/>				
                <input type="radio"
                       name="html_inboxrules_rule_action" id="close"
                       value="close" <?php echo( $rule_action == 'close' ? 'checked' : '' ) ?> >
                <label for="close"> <?php _e( 'Close the ticket (reply will not be added to ticket)', 'as-email-support' ) ?> </label><br/>
                <input type="radio"
                       name="html_inboxrules_rule_action" id="skip"
                       value="skip" <?php echo( $rule_action == 'skip' ? 'checked' : '' ) ?> >
                <label for="skip"> <?php _e( 'Skip the message completely - do not process it and leave it in the inbox', 'as-email-support' ) ?> </label><br/>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_notes"> <?php _e( 'Notes', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_inboxrules_rule_notes"
                       name="html_inboxrules_rule_notes"
                       value="<?php echo $rule_notes ?>"/>
                <p class="description"> <?php _e( 'Remind yourself what this rule is supposed to do - especially if this is a REGEX based rule.', 'as-email-support' ) ?> </p>
            </td>
        </tr>

        <tr valign="top" class="subsection">
            <td colspan="2" class="second tf-text section-highlight">
                <?php _e( 'Update Fields: If you choose the option to update the ticket above then use this section to configure the fields to be updated.', 'as-email-support' ) ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_assignee"> <?php _e( 'New Agent', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php wpas_es_show_assignee_dropdown_simple( "html_inboxrules_rule_new_assignee", "", $new_assignee ); ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_dept"> <?php _e( 'New Department', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'department', "html_inboxrules_rule_new_dept", "", $new_dept ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_product"> <?php _e( 'New Product', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'product', "html_inboxrules_rule_new_product", "", $new_product ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_priority"> <?php _e( 'New Priority', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'ticket_priority', "html_inboxrules_rule_new_priority", "", $new_priority ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_channel"> <?php _e( 'New Channel', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php echo wpas_es_show_taxonomy_terms_dropdown( 'ticket_channel', "html_inboxrules_rule_new_channel", "", $new_channel ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_status"> <?php _e( 'New status', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_inboxrules_rule_new_status"
                       name="html_inboxrules_rule_new_status"
                       value="<?php echo $new_status ?>"/>
                <p class="description"> <?php _e( 'This is a string that represents the custom status. Generally it is the same as the custom status value converted to lowercase characters but can ocasionally differ.', 'as-email-support' ) ?> </p>
                <p class="description"> <?php _e( 'If there are spaces in your custom status then use a dash in place of the space - eg: my-custom-status.', 'as-email-support' ) ?> </p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_public_flag"> <?php _e( 'New Public/private Flag', 'as-email-support' ) ?>  </label>
            </th>
            <td class="second tf-radio">
                <fieldset>
                <input type="radio"
                       name="html_inboxrules_rule_new_public_flag" id="public"
                       value="public" <?php echo( $new_public_flag == 'public' ? 'checked' : '' ) ?> >
                <label for="public"> <?php _e( 'Public', 'as-email-support' ) ?> </label><br/>

                <input type="radio"
                       name="html_inboxrules_rule_new_public_flag" id="private"
                       value="private" <?php echo( $new_public_flag == 'private' ? 'checked' : '' ) ?> >
                <label for="private"> <?php _e( 'Private', 'as-email-support' ) ?> </label><br/>

                <p class="description"> <?php _e( 'This is a string that represents the custom status. Generally it is the same as the custom status value converted to lowercase characters but can ocasionally differ.', 'as-email-support' ) ?> </p>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_addlparty_email1"> <?php _e( 'New Addl 3rd Party Email #1', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_inboxrules_rule_new_addlparty_email1"
                       name="html_inboxrules_rule_new_addlparty_email1"
                       value="<?php echo $new_addlparty_email1 ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_addlparty_email2"> <?php _e( 'New Addl 3rd Party Email #2', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
                <input type="text"
                       id="html_inboxrules_rule_new_addlparty_email2"
                       name="html_inboxrules_rule_new_addlparty_email2"
                       value="<?php echo $new_addlparty_email2 ?>"/>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_secondary_assignee"> <?php _e( 'New Secondary Assignee/Agent', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php wpas_es_show_assignee_dropdown_simple( "html_inboxrules_rule_new_secondary_assignee", "", $new_secondary_assignee ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="first">
                <label for="html_inboxrules_rule_new_tertiary_assignee"> <?php _e( 'New Tertiary Assignee/Agent', 'as-email-support' ) ?> </label>
            </th>
            <td class="second tf-text">
				<?php wpas_es_show_assignee_dropdown_simple( "html_inboxrules_rule_new_tertiary_assignee", "", $new_tertiary_assignee ); ?>
                <p class="description"></p>
            </td>
        </tr>

        <tr valign="top">
            <td colspan="2" class="tf-text section-footer">
				<?php _e( 'Warning! Please keep in mind that the more rules you add, the slower importing emails will be.  Your host might not allow you the time needed to run a long script which could cause importing to timeout and fail when there are many rules.', 'as-email-support' ) ?>
            </td>
        </tr>

    </table>


	<?php
	/**
	 * Set action to execute after displaying the inbox rules config screen...
	 *
	 * @since  5.0.0
	 */
	do_action( 'wpas_backend_inbox_rules_config_after', $post->ID, $post );  // note that $post is a variable that is passed in
	?>

</div>	