<?php

// Set the author data (if author is known)
if ( $row->post_author != 0 ) {
	$user_data = get_userdata( $row->post_author );
	$user_id   = $user_data->data->ID;
	$user_name = $user_data->data->display_name;
}

// In case the post author is unknown, we set this as an anonymous post
else {
	$user_name = __( 'Anonymous', 'wpas_it' );
	$user_id   = 0;
}

$user_avatar     = get_avatar( $user_id, '64', get_option( 'avatar_default' ) );
$date            = human_time_diff( get_the_time( 'U', $row->ID ), current_time( 'timestamp' ) );
$date_full		 = get_the_time('F j, Y g:i a', $row->ID);
$days_since_open = wpas_get_date_diff_string( $post->post_date, $row->post_date) ;  // This is a string showing the number of dates/hours/mins that this comment arrived compared to the date the issue was opened
$post_type       = $row->post_type;
$post_type_class = "wpas-it-issue-comment wpas-it-issue-comment-status-{$row->post_status}";


$comment = new WPAS_IT_Comment( $row->ID );









?>

<tr valign="top" class="wpas-table-row <?php echo $post_type_class ?>" id="wpas-post-<?php echo $row->ID; ?>">

	<?php
	
	if ( 'trash' == $row->post_status ) { ?>
		
		
		<td colspan="3">
			<?php printf( __( 'This comment has been deleted by %s <em class="wpas-time">%s ago.</em>', 'wpas_it' ), "<strong>$user_name</strong>", human_time_diff( strtotime( $row->post_modified ), current_time( 'timestamp' ) ) ); ?>
		</td><?php
			
	} else {
		
		$content = apply_filters( 'the_content', $row->post_content );
		
		$delete_link = wpas_do_url( admin_url( 'post.php' ), 'admin_trash_issue_comment', array( 'post' => $post->ID, 'action' => 'edit', 'comment_id' => $row->ID ) );
		?>
		
		
		<td class="col1" style="width: 64px;">
			<?php echo $user_avatar; ?>
		</td>
		<td class="col2">

		<?php if ( 'unread' === $row->post_status ): ?>
			<div id="wpas-unread-<?php echo $row->ID; ?>" class="wpas-unread-badge"><?php _e( 'Unread', 'wpas_it' ); ?></div>
		<?php endif; ?>
		
			<div>
				<div class="wpas-it-comment-meta">
					<div class="wpas-reply-user">
						<strong class="wpas-profilename"><?php echo $user_name; ?></strong> <span class="wpas-profilerole">(<?php echo wpas_get_user_nice_role( $user_data->roles[0] ); ?>)</span>
					</div>
				</div>

				
				<div class="wpas-it-comment-meta-right">
						
					<time class="wpas-timestamp" datetime="<?php echo get_the_date( 'Y-m-d\TH:i:s' ) . wpas_get_offset_html5(); ?>">
						<span class="wpas-human-date"><?php echo date( get_option( 'date_format' ), strtotime( $row->post_date ) ); ?> |</span>
						<?php printf( __( '%s ago', 'wpas_it' ), $date ); ?>
					</time>
						
						<span title="<?php echo $comment->getTypeName(); ?>" class="comment_item_type <?php echo $comment->getType(); ?> ">&nbsp;</span>
						
					<span class="wpas-label" style="background: <?php echo $comment->getStatusColor(); ?>">
							<?php echo $comment->getStatusName(); ?>
					</span>
					
					
					
					<div class="wpas-it-comment-controls">
						<a class="wpas-delete" href="<?php echo $delete_link; ?>" title="Delete" data-confirm="<?php _e( "Are you sure you want to delete this comment?", 'wpas_it' ) ?>">Delete</a>
					</div>
						
				</div>
					
					
					
				<div class="clearfix clear"></div>
			</div>
	
			<div class="wpas-it-issue-comment-content">
				<?php echo wp_kses( $content, wp_kses_allowed_html( 'post' ) ); ?>
			</div>
		</td>

		
		<?php
	}

	?>

</tr>