<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );

?>

<div class="wpas_pf_ui_items">
	
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	
	<?php
	foreach( $items as $item_id => $item ) {
		include 'item.php';
	}
?>
</div>