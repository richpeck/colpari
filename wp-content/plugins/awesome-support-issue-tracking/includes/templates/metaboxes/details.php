<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $pagenow, $post;




/* Get post status */
$post_status = isset( $post ) ? $post->post_status : '';

/* Get the date */
$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$date        = get_the_date( $date_format );

/* Get time */
if ( isset( $post ) ) {
	$dateago = human_time_diff( get_the_time( 'U', $post->ID ), current_time( 'timestamp' ) );
}


$issue = new WPAS_IT_Issue( $post->ID );

?>

<div class="wpas-it-issue-status submitbox">
		
		<?php wp_nonce_field( 'wpas_it_issue', 'wpas-it-add-issue-nonce', true, true ); ?>
	
	<div class="wpas-row" id="wpas-statusdate">
		<div class="wpas-col">
			<strong><?php _e( 'Status', 'wpas_it' ); ?></strong>
			<?php if ( 'post-new.php' != $pagenow ):
			echo $issue->display_status();
			?>
			<?php else: ?>
				<span><?php echo _x( 'Creating...', 'Issue creation', 'wpas_it' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="wpas-col">
			<?php if ( isset( $post ) ): ?>
				<strong><?php echo $date; ?></strong>
				<em><?php printf( __( '%s ago', 'wpas_it' ), $dateago ); ?></em>
			<?php endif; ?>
		</div>
	</div>
	<?php 
	
	$staff_id = $issue->getPrimaryAgentID();
	$staff_name = $issue->getPrimaryAgentName();
	
	$selected_agent = "";
	if( $staff_id ) {
		$selected_agent = sprintf( '<option value="%s" selected="selected">%s</option>', $staff_id, $staff_name );
	}
?>
	<div id="wpas-stakeholders">

		<label for="wpas-assignee">
				<strong data-hint="<?php esc_html_e( 'The agent currently responsible for this issue', 'wpas_it' ); ?>" class="hint-left hint-anim"><?php _e( 'Support Staff', 'wpas_it' ); ?></strong></label>
		<p>
			<?php

			echo wpas_dropdown( array(
				'name'      => 'wpas_it_primary_agent',
				'id'        => 'wpas-it-primary-agent',
				'disabled'  => ! current_user_can( 'assign_ticket' ) ? true : false,
				'select2'   => true,
				'class'		=> 'it-select2',
				'data_attr' => array( 
					'capability'  => 'edit_ticket',	
					'action'      => 'wpas_get_users',
					'result_id'   => 'user_id',
					'result_text' => 'user_name'
					)
			), $selected_agent );					

			?>
		</p>
	</div>
	
	
	<div class="wpas-it-mb-details-field">
		<label for="wpas-post-status"><strong><?php _e( 'Status', 'wpas_it' ); ?></strong></label>
		<p>
			
		<?php
		
		
			$status_term = $issue->getStatus();
			
			$status_term = $status_term ? $status_term : wpas_it_get_term_by( 'name', 'Open', 'wpas_it_status' );
			
			wp_dropdown_categories( array( 
				'name'					=> 'wpas_it_status',
				'option_none_value'		=> '',
				'show_option_none'		=> 'Select Status...',
				'taxonomy'				=> 'wpas_it_status', 
				'hide_empty'			=> false,
				'selected'				=> ( $status_term ? $status_term->term_id : '' )
			) );
					
		?>
				
		</p>
	</div>
		
		
		
	<div class="wpas-it-mb-details-field">
		<label for="wpas-post-status"><strong><?php _e( 'Priority', 'wpas_it' ); ?></strong></label>
		<p>
					
		<?php
		
		
		
		$priority_term = $issue->getPriority();
		
		$priority_term = $priority_term ? $priority_term : wpas_it_get_term_by( 'name', 'Medium', 'wpas_it_priority' );
		
		wp_dropdown_categories( array( 
			'name'					=> 'wpas_it_priority',
			'option_none_value'		=> '',
			'show_option_none'		=> 'Select Priority...',
			'taxonomy'				=> 'wpas_it_priority', 
			'hide_empty'			=> false,
			'selected'				=> ( $priority_term ? $priority_term->term_id : '' )
		) );
					
		?>
		</p>
	</div>
		
	<div class="wpas-it-mb-details-field">
			
		<label for="wpas-post-status"><strong><?php _e( 'Parent', 'wpas_it' ); ?></strong></label>
		<p>
		<?php
		
		$post_type_object = get_post_type_object( $post->post_type );
		
		if ( $post_type_object->hierarchical ) {
      		wp_dropdown_pages(array(
            		'post_type'			=> $post->post_type, 
            		'selected'			=> $post->post_parent, 
					'name'				=> 'parent_id', 
            		'show_option_none' 	=> __('(no parent)'), 
            		'sort_column'		=> 'menu_order, post_title'
			));
      
    	}
		
		?>
		
		</p>
			
	</div>
		
		
		
	<div id="wpas_it_close_issue_prompt">
		<?php include WPAS_IT_PATH . 'includes/templates/close_issue.php'; ?>
	</div>
		
	
	<div id="major-publishing-actions">
		<?php if ( current_user_can( "delete_ticket", $post->ID ) ): ?>
			<div id="delete-action" class="wpas_it_tb_button">
				<?php
				if( 'closed' !== $issue->getState() ) { ?>
				<a class="submitdelete deletion" title="Close Issue" href="#TB_inline?width=600&height=450&inlineId=wpas_it_close_issue_prompt"><?php _e( 'Close', 'wpas_it' ); ?></a>
					
				<?php
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( current_user_can( 'edit_ticket' ) ): ?>
			<div id="publishing-action">
				<span class="spinner"></span>
				<?php if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Updating', 'wpas_it' ) ?>" />
					<?php submit_button( __( 'Update Issue', 'wpas_it' ), 'primary button-large wpas_it_issue_publish_btn', 'publish', false, array( 'accesskey' => 'u' ) ); ?>
				<?php else:
					if ( current_user_can( 'create_ticket' ) ): ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Creating', 'wpas_it' ) ?>" />
						<?php submit_button( __( 'Open Issue', 'wpas_it' ), 'primary button-large wpas_it_issue_publish_btn', 'publish', false, array( 'accesskey' => 'o' ) ); ?>
						<?php endif;
				endif; ?>
						
				
			</div>
		<?php endif; ?>
		<div class="clear"></div>
		
		<div class="wpas_it_msg"></div>
	</div>
</div>