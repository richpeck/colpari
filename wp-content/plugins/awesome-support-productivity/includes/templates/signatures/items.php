<?php

$del_action = $this->actionName( 'delete' );
$del_nonce = $this->nonce( $del_action );

$dup_action = $this->actionName( 'duplicate' );
$dup_nonce = $this->nonce( $dup_action );

$def_action = $this->actionName( 'default' );
$def_nonce = $this->nonce( $def_action );



?>

<div class="wpas_pf_ui_items">
	<?php if( $this->user_can_add_signature() ) : ?>
	<input type="hidden" class="delete-nonce" data-name="<?php echo $del_nonce['name']; ?>" value="<?php echo wp_create_nonce( $del_nonce['action'] ); ?>" />
	<input type="hidden" class="duplicate-nonce" data-name="<?php echo $dup_nonce['name']; ?>" value="<?php echo wp_create_nonce( $dup_nonce['action'] ); ?>" />
	<input type="hidden" class="default-nonce" data-name="<?php echo $def_nonce['name']; ?>" value="<?php echo wp_create_nonce( $def_nonce['action'] ); ?>" />
	
	<?php
	
	endif;
	
	foreach( $items as $item_id => $item ) {
		include 'item.php';
	}
?>
</div>