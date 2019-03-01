<div id="icon-edit" class="icon32"><br />
</div>
<div class="rns-report-wrapper">
	<div class="rns-main-heading">
    	<h2>
        	<strong> <?php echo __( 'Reports Settings', 'reports-and-statistics' ); ?></strong>
        </h2>
        
  	</div>
  	<div class="options-container">
		<form action="" method="post" >
			<table class="rns-form-table">
                <tr class="even first rns-tf-heading" valign="top">
                    <th scope="row" class="first last" colspan="2">
                        <h3 id="rns_misc"><?php echo __( 'Select Report Filters', 'reports-and-statistics' ); ?></h3>
                        
                        <br />
                        <h4 id="report-filters-comment"><?php echo __( 'Items selected here will show up as filter options when creating your reports.', 'reports-and-statistics' ); ?></h4>
                        <h5 id="report-filters-comment-2nd-line"><?php echo __( 'Please be careful with the number of items you select. The more items you select, the slower your reports will run!', 'reports-and-statistics' ); ?></h5>

                    </th>
                </tr>
			<?php
			
			 foreach( $data as $key => $val ){
				 $field_options[$key] =  ( isset( $val['args']['title'] ) ? $val['args']['title'] : '' ) . " ( " . $val['name'] . " )  ";
			 }

			 $select_fields = explode( ',' , get_option( ' wpas_reports_statistics_custom_fields', true ) );

			 $tab_html = '<tr class="row-1 odd" valign="top" >
							<th scope="row" class="first">
								<label for="wpas_reports_statistics_custom_fields">'.__( 'Custom Fields', 'reports-and-statistics' ).'</label>
							</th>
							<td>
							{FIELDS}
							</td>
						</tr>';
			$fields = '';
			 
			 foreach( $field_options as $key=>$val ){
				 
				 if( !empty( $select_fields ) && in_array( $key, $select_fields ) ){
					 
					 $fields .= '<input type="checkbox" class="wpas_custom_field_type" name="wpas_custom_field_type[]" checked="checked" id="'.$key.'" value="'.$key.'" ><label for="'.$key.'">'. $val.' </label><br/>';
					 
				 } else {
					 
					$fields .= '<input type="checkbox" class="wpas_custom_field_type" name="wpas_custom_field_type[]" id="'.$key.'" value="'.$key.'" ><label for="'.$key.'">'. $val .' </label><br/>';
					
				 }
				 
			 }
			 
			 $tab_html = str_replace ( '{FIELDS}' , $fields , $tab_html ) ; 
			
			echo $tab_html;		
			
			?>
         
			</table>
			<p class="submit">
				<input type="submit" class="button button-primary" name="submit" value="<?php echo __( 'Save Changes', 'reports-and-statistics' ); ?>" />
				<button class="button button-secondary" name="action" ><?php echo __( 'Reset to Defaults', 'reports-and-statistics' ); ?>  </button>
			</p>
		</form>
	</div>
</div>