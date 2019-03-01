
<div class="wpas_cp_list_subtable_support_user" data-company_id="<?php echo $company_id; ?>">
	
	<?php

	foreach( $support_users as $item_id => $item ) {
		include 'support_user_item.php';
	}
	
	?>
	
</div>