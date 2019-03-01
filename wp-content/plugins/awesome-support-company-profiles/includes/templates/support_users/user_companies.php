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
	
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th width="12%"><?php _e( 'Company',		'wpas_cp' ); ?></th>
				<th width="18%"><?php _e( 'Address',		'wpas_cp' ); ?></th>
				<th width="12%"><?php _e( 'Email',			'wpas_cp' ); ?></th>
				<th width="12%"><?php _e( 'Fax',			'wpas_cp' ); ?></th>
				<th width="10%"><?php _e( 'User Type',		'wpas_cp' ); ?></th>
				<th width="12%"><?php _e( 'Privileges',		'wpas_cp' ); ?></th>
				<th width="8%"></th>
			</tr>
		</thead>
		<tbody>
			<?php
	
			foreach( $items as $item_id => $item ) {
				include 'company_item.php';
			}
			?>
		</tbody>
		
	</table>
	
	
	
</div>