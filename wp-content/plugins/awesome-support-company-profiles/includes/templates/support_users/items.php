<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );

?>


<div class="wpas_cp_ui_items" data-type="table">
	<?php if( $this->user_can_add() ) : ?>
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	
	
	
	<?php
	
	endif;
	
	?>
	
	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th width="24%"><?php _e( 'User',				'wpas_cp' ); ?></th>
				<th width="20%"><?php _e( 'Divisions',			'wpas_cp' ); ?></th>
				<th width="20%"><?php _e( 'Reporting Group',	'wpas_cp' ); ?></th>
				<th width="20%"><?php _e( 'Privileges',			'wpas_cp' ); ?></th>
				<th width="16%"></th>
				
			</tr>
		</thead>
		<tbody>
			
			<?php
	
			foreach( $items as $item_id => $item ) {
				include 'item.php';
			}
			?>
			
		</tbody>
	</table>
	
	
	
</div>