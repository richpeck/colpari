<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>


<div class="wpas_cp_ui_items">
	
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th width="12%"><?php _e( 'Company',	'wpas_cp' ); ?></th>
				<th width="28%"><?php _e( 'Address',	'wpas_cp' ); ?></th>
				<th width="18%"><?php _e( 'Email',		'wpas_cp' ); ?></th>
				<th width="18%"><?php _e( 'Fax',		'wpas_cp' ); ?></th>
				<th width="8%"></th>
			</tr>
		</thead>
	</table>
	
	
			
	<?php
	
	foreach( $companies as $item_id => $company ) {
		
		echo '<div class="wpas_cp_group_support_users__company">';
		
		$company_id = $company->ID;
		
		include WPAS_CP_PATH . 'includes/templates/manage_company/company_item.php';
		
		$support_users = WPAS_Company_Support_User::get_profile_support_users( $company_id );
		
		if( !empty( $support_users ) ) {
			include WPAS_CP_PATH . 'includes/templates/manage_company/company_support_users.php';
		}
		
		echo '</div>';
	}
	?>
		
</div>