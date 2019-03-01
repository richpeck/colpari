<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
* Custom function to make the tickets visible to non-logged in users
*/
function wpas_my_custom_view_function($content = ''){

	global $post;
	$slug 			= 'ticket';
	if( is_singular() )
	$pbtk_flag 		= get_post_meta( $post->ID , '_wpas_pbtk_flag', true );		
	
	/* Don't touch the admin */

	if ( is_admin() ) {
		return $content;
	}

	/* Only apply this on the ticket single. */

	if ( $post && $slug !== $post->post_type ) {
		return $content;
	}

	/* Only apply this on the main query. */

	if ( ! is_main_query() ) {
		return $content;
	}

	/* Only apply this if it's inside of a loop. */

	if ( ! in_the_loop() ) {
		return $content;
	}

	/* Remove the filter to avoid infinite loops. */

	remove_filter( 'the_content', 'wpas_my_custom_view_function' );

	/* Check if the current user can view the ticket */

	if( $pbtk_flag != 'public' ){
		if ( ! wpas_can_view_ticket( $post->ID ) ) {
			if ( is_user_logged_in() ) {
				return wpas_get_notification_markup( 'failure', __( 'You are not allowed to view this ticket.', 'as-public-tickets' ) );
			} else {
				$output = '';
				$output .= wpas_get_notification_markup( 'failure', __( 'You are not allowed to view this ticket.', 'as-public-tickets' ) );
				ob_start();
				wpas_get_template( 'registration' );
				$output .= ob_get_clean();
				return $output;
			}
		}
	}

	/* Get template name */

	$template_path = get_page_template();
	$template      = explode( '/', $template_path );
	$count         = count( $template );
	$template      = $template[ $count - 1 ];
	
	/* Don't apply the modifications on a custom template */

	if ( "single-$slug.php" === $template ) {
		return $content;
	}

	/* Get the ticket content */
	ob_start();
	
	/**	
	* wpas_frontend_plugin_page_top is executed at the top
	* of every plugin page on the front end.
	*/

	do_action( 'wpas_frontend_plugin_page_top', $post->ID, $post );

	/**
	* Get the custom template.
	*/
	
	wpas_get_template( 'details' );

	/**
	 * Finally get the buffer content and return.
	 *
	 * @var string
	 */

	$content = ob_get_clean();
	return $content;
}

/*
* Function to add required filters and actions 
*/
function wpas_my_check_pbtk_settings(){	
	remove_filter( 'the_content', 'wpas_single_ticket' );
	add_filter( 'the_content', 'wpas_my_custom_view_function', 10, 1 );
	add_filter( 'wpas_ticket_reply_controls', 'wpas_ticket_reply_custom_controls', 10, 3 );
	add_action( 'wpas_frontend_reply_content_before', 'wpas_frontend_reply_content_before_custom', 20, 1 );
	add_action( 'wpas_ticket_details_reply_textarea_after', 'wpas_ticket_details_reply_textarea_front_end_after', 10, 0 );
	add_action( 'wpas_admin_after_wysiwyg', 'wpas_ticket_details_reply_textarea_after_custom', 10, 0 );
	add_action( 'wpas_add_reply_public_after', 'wpas_add_reply_public_after_custom', 10, 1 );
	add_action( 'wpas_add_reply_admin_after', 'wpas_add_reply_public_after_custom', 10, 1 );
	add_action( 'pre_get_posts', 'wpas_custom_reply_query' );
	add_filter( 'wpas_fe_template_detail_author_display_name', 'wpas_custom_template_detail_display_name', 20, 2 );
	add_filter( 'wpas_fe_template_detail_reply_display_name', 'wpas_custom_template_detail_display_name', 20, 2 );
	
}

/*
* Function to add pagination to tickets shortcode 
*/
function wpas_custom_pagination( $pages = '', $range = 4 ){
    $showitems = ($range * 2)+1;
    global $paged;
    if( empty( $paged ) ) $paged = 1;
    if( $pages == '' ){
        global $wp_query;
        $pages = $wp_query->max_num_pages;
        if( !$pages ){
            $pages = 1;
        }
    }
    if( 1 != $pages ){
        $pagin = "<div class=\"aspbtk-pagi\"><span>Page ".$paged." of ".$pages."</span>";
        if( $paged > 2 && $paged > $range+1 && $showitems < $pages ) $pagin .= "<a href='".get_pagenum_link(1)."'>&laquo; First</a>";
        if( $paged > 1 && $showitems < $pages ) $pagin .= "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo; Previous</a>";
        for ($i=1; $i <= $pages; $i++){
            if ( 1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
               $pagin .= ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
            }
        }

        if ( $paged < $pages && $showitems < $pages ) $pagin .= "<a href=\"".get_pagenum_link( $paged + 1 )."\">Next &rsaquo;</a>";
        if ( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages ) $pagin .= "<a href='".get_pagenum_link( $pages )."'>Last &raquo;</a>";
        $pagin .= "</div>\n";
		return $pagin;
    }
}

/*
*
* Manage Public/Private flag for replies.
* This function checks for the public/private status of reply.
* If status is public , displays a Mark as private flag and vice-versa .
*
*/
function wpas_ticket_reply_custom_controls( $controls, $ticket_id, $reply ){
	$status = get_post_meta( $reply->ID, "custom_reply_status", true );
	if( $status== "" || $status == "public" ){
		$controls['make_public'] = sprintf( '<a class="%1$s" href="%2$s" id="rep_'.$reply->ID.'" data-replyid="%3$d" title="%4$s" data-status="private">%4$s</a>', 'wpas-mark-private', '#', $reply->ID, esc_html_x( 'Mark as Private', 'Mark a reply as public/private', 'as-public-tickets' ) );
	}else{
		$controls['make_public'] = sprintf( '<a class="%1$s" href="%2$s"  id="rep_'.$reply->ID.'" data-replyid="%3$d" title="%4$s" data-status="public">%4$s</a>', 'wpas-mark-private', '#', $reply->ID, esc_html_x( 'Mark as Public', 'Mark a reply as public/private', 'as-public-tickets' ) );
	}
	return $controls;
}

/*
*
* Display Private replies only to admin and ticket author
* This function checks for the public/private status of reply.
* This checks if the logged in user is super admin or ticket author.
* If super admin or ticket owner is logged in, then display private reply.
* Otherwise hide private reply.
*/
function wpas_frontend_reply_content_before_custom( $postid ){
	global $post,$current_user;
	$author_id	= $post->post_author;	
	$status     = get_post_meta( $postid, "custom_reply_status", true );
	
	if( is_user_logged_in() ){
		if( $current_user->ID == $author_id ){
			if( $status=="" || $status =="public" ){
			$aa = '<a class="wpas-mark-private" id="rep_'.$postid.'" href="#" data-replyid="'.$postid.'" title="Mark as Private" data-status="private">'.esc_html_x( 'Mark as private', 'as-public-tickets' ).'</a>';
			}else{
				$aa = '<a class="wpas-mark-private" id="rep_'.$postid.'" href="#" data-replyid="'.$postid.'" title="Mark as Public" data-status="public">'.esc_html_x( 'Mark as public', 'as-public-tickets' ).'</a>';
			}
			echo "<script>
			jQuery( '#reply-$postid .wpas-reply-meta' ).append('".$aa."');
			</script>";
		}

	}
}
/*
*
* Add 'Mark as private' field to reply Form on backend
* 
*/
function wpas_ticket_details_reply_textarea_after_custom(){ ?>
	<div class="pbtk_checkbox">
		<label for="private_reply" data-toggle="tooltip" data-placement="right" title="">
			<input type="checkbox" name="wpas_private_reply" id="private_reply" value="true"> <?php _e( 'Mark as private', 'as-public-tickets' ); ?>
			<br/><br/>
		</label>		
	</div>
<?php }

/*
*
* Add 'Mark as private' field to reply Form on front-end
* 
*/
function wpas_ticket_details_reply_textarea_front_end_after(){ 
	if ( true == boolval( wpas_get_option( 'pbtk_shw_flag' , false ) ) ) {?>
		<div class="pbtk_checkbox">
			<label for="private_reply" data-toggle="tooltip" data-placement="right" title="">
				<input type="checkbox" name="wpas_private_reply" id="private_reply" value="true"> <?php _e( 'Mark as private', 'as-public-tickets' ); ?>
				<br/><br/>
			</label>		
		</div>
<?php }
 }

/*
*
* Save reply public/ private status when reply is submitted.
*
*/
function wpas_add_reply_public_after_custom( $reply_id ){
	$rstatus = 'public';
	if( isset( $_POST['wpas_private_reply'] ) &&  $_POST['wpas_private_reply'] == true)
	{
		$rstatus = 'private';
	}
	update_post_meta( $reply_id, 'custom_reply_status', $rstatus );
}

/*
*
* Filter WP Query for private replies 
* This function will check if the user is not admin or author of post or no user logged in
* then this will add meta query to show only public replies.
*/
function wpas_custom_reply_query($query)
{
	global $post,$current_user;

	
	if ( $query->get('post_type') == 'ticket_reply' && is_singular('ticket')){

		$meta_query = array(
			  'relation' => 'OR',
			  array( 
				'key'     => 'custom_reply_status',
				'value'   => 'private',
				'compare' => '!=',
			  ),
			  array( 
				'key'     => 'custom_reply_status',				
				'compare' => 'NOT EXISTS',
			  ),
			);
		if( is_user_logged_in() ){
			$uid = $current_user->ID;
			$post_author = get_post_field( 'post_author', $query->get('post_parent') );
			
			if( (!is_super_admin( $uid ) or $post_author != $uid)){	
				$query->set( 'meta_query', $meta_query );
			}
			
		}else{
			$query->set( 'meta_query', $meta_query );
		}	  
    }
}
/*
*
* Show/Hide agent/customer Name based on admin setings.
*
*/
function wpas_custom_template_detail_display_name( $name, $post){
	
	/*If logged in user is agent on the ticket then just return name as is...*/
	$current_user = wp_get_current_user();
	if ( isset( $current_user->ID ) && ( $post->post_author == $current_user->ID ) && (user_can( $current_user->ID, 'edit_other_ticket') ) ) {
		return $name ;
	}
	
	/* If logged in user is admin, return name as is... */
	if ( isset( $current_user->ID ) && (user_can( $current_user->ID, 'administer_awesome_support') ) ) {
		return $name ;
	}
	
	/* If logged in user is the author on the ticket/reply return the name as is */
	if ( isset( $current_user->ID ) && ( $post->post_author == $current_user->ID ) ) {
		return $name ;
	}
	
	 /* Either not logged in or not admin or not author of the ticket/reply so figure out what to return for the display name based on option settings */
	$option1 = wpas_get_option( 'pbtk_customer_name_show' );
    $option2 = wpas_get_option( 'pbtk_agent_name_show' );	
	
	if( (user_can( $post->post_author, 'edit_other_ticket') == true && $option2 == 'noaction') || (user_can( $post->post_author, 'edit_other_ticket') == false && $option1 == 'noaction')){		
		return $name;
	}elseif((user_can( $post->post_author, 'edit_other_ticket') == true && $option2 == 'annoymize') || (user_can( $post->post_author, 'edit_other_ticket') == false && $option1 == 'annoymize')){
		return strtoupper(substr($name,0,1));
	}elseif((user_can( $post->post_author, 'edit_other_ticket') == true && $option2 == 'hide') ){
		return __('Agent Reply', 'as-public-tickets' );
	}elseif ( (user_can( $post->post_author, 'edit_other_ticket') == false && $option1 == 'hide') ) {
		return __('Customer Reply', 'as-public-tickets' );
	}
}
/*
*
* Update reply public/ private status after reply is submitted.
*
*/
function wpas_mark_reply_private(){
	$result 	  = update_post_meta( $_POST['reply_id'], 'custom_reply_status', $_POST['reply_status'] );
	$reply_status = get_post_meta( $_POST['reply_id'], "custom_reply_status", true );
	echo $reply_status;
	die();
}
add_action( 'wp_ajax_wpas_mark_reply_private', 'wpas_mark_reply_private' );
add_action( 'wp_ajax_nopriv_wpas_mark_reply_private', 'wpas_mark_reply_private' );

/*
*
* Declare ajaxurl variable for fornt end to use ajax.
*
*/
function wpas_myplugin_ajaxurl(){
   echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}
add_action( 'wp_head', 'wpas_myplugin_ajaxurl' );

wpas_my_check_pbtk_settings();