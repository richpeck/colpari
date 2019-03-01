<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );

?>


<div class="wpas_it_ui_items">
	<?php if( $this->user_can_add() ) : ?>
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	
	
	
	<?php
	
	
	endif;
	
	?>
	
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th width="12%"><?php _e( "ID", "wpas_it" ); ?></th>
				<th width="30%"><?php _e( "Title", "wpas_it" ); ?></th>
				<th width="18%"><?php _e( "Last Reply Date", "wpas_it" ); ?></th>
				<th width="16%"><?php _e( "Status", "wpas_it" ); ?></th>
				<th width="24%"><?php _e( "Activity", "wpas_it" ); ?></th>
			</tr>
		</thead>
	</table>
	
	<?php
	foreach( $items as $item_id => $item ) {
		include 'item.php';
	}
	?>
		
	
</div>