<?php 
//intialize variable for future use
$stateValues 		 = $report_detail = array();

//get values from url 
$action 			 = sanitize_text_field ( isset( $_GET['action'] ) ? $_GET['action'] : 'basic_report' );
$report_id 			 = sanitize_text_field ( isset( $_GET['report_id'] ) ? $_GET['report_id'] : '' );
$second_dimension	 = sanitize_text_field ( isset( $_GET['second'] ) ? $_GET['second'] : 'none' );
$drop_zero_rows		 = sanitize_text_field ( isset( $_GET['drop_zero_rows'] ) ? $_GET['drop_zero_rows'] : '' );
$drop_zero_columns		 = sanitize_text_field ( isset( $_GET['drop_zero_columns'] ) ? $_GET['drop_zero_columns'] : '' );

if( !empty( $report_id ) ) {
	 $report_detail  = get_post( $report_id );
}
?>

<div id="icon-edit" class="icon32">
</div>

<div class="rns-report-wrapper">

	<div class="rns-main-heading">
    	<strong> 
		<?php 
			// display title of report 			
			echo rns_get_report_name_by_action( $action ) ." ". __( 'Report' ,'reports-and-statistics' );
		?> 
        </strong> |
       
        <a href="javascript:void(0);" class="toggle_sidefilter"><?php echo __( 'Toggle Filters', 'reports-and-statistics' ); ?></a>   |  
         <a href="javascript:void(0);" class="toggle_helptext"><?php echo __( 'About this report', 'reports-and-statistics' ); ?></a>
         <?php echo  ( isset( $report_detail->post_title ) ? " | <b> ". $report_detail->post_title ."</b>" :'' );   ?>
  	</div>
    
    
  	
        <?php
		/***************************************************
			This form is used for filtering the report
		****************************************************/
		?>
        
    	<form name="report_filter" id="report_filter" action="" >
        
        	<input type="hidden" name="report_id" id="report_id" value="<?php echo $report_id; ?>" />
    		<input type="hidden" name="page" id="rns_page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
      		<input type="hidden" name="action" id="rns_action" value="<?php echo esc_attr( $_GET['action'] ); ?>" />
      		<input type="hidden" name="search_filter" id="search-filter" value="1" />
            
      		<div class="rns-right-container" <?php if( isset( $_GET['second'] ) ) { ?> style="width:99.5%;"  <?php  }  ?> id="rns_graph_view">
            
            	
                
                <?php
				/***************************************************
					Top bar reports filter area  start here
				****************************************************/
				?>
                
        		<div class="rns-right-filters">
          			<ul>
                    	<?php 
						// if report is not tred analysis
						if( $action != "trend_report" ) {
						?>
            			<li>
              				<label><?php echo __( 'Select Date Range', 'reports-and-statistics' ); ?>:</label>
              				<?php $daySelected = ( isset( $_GET['days'] ) ) ? esc_attr($_GET['days']) : ''; ?>
              				<select name="days" id="filter_days">
                                <option value=""></option>
                                <option value="lmonth" <?php echo ( $daySelected == 'lmonth' ) ? 'selected="selected"' : ''; ?> ><?php echo __( 'Last Month', 'reports-and-statistics' ); ?></option>
                                <option value="tmonth" <?php echo ( $daySelected == 'tmonth' ) ? 'selected="selected"' : ''; ?> ><?php echo __( 'This Month', 'reports-and-statistics' ); ?></option>
                                <option value="lweek" <?php echo ( $daySelected == 'lweek' ) ? 'selected="selected"' : ''; ?> ><?php echo __( 'Last Week', 'reports-and-statistics' ); ?></option>
                                <option value="tweek" <?php echo ( $daySelected == 'tweek' ) ? 'selected="selected"' : ''; ?> ><?php echo __( 'This Week', 'reports-and-statistics' ); ?></option>
                                <option value="yesterday" <?php echo ( $daySelected == 'yesterday' ) ? 'selected="selected"' : ''; ?> ><?php echo __( 'Yesterday', 'reports-and-statistics' ); ?></option>
                                <option value="today"  <?php echo ( $daySelected =='today' ) ? 'selected="selected"' : ''; ?> ><?php echo __( 'Today', 'reports-and-statistics' ); ?></option>
              				</select>
            			</li>
                        
            			<li>
              				<label><?php echo __( 'Start Date', 'reports-and-statistics' ); ?>:</label>
              				&nbsp;
              				<input type="text" name="sDate" id="sdate" class="date" value="<?php echo ( isset( $_GET['sDate'] ) ) ? esc_attr( $_GET['sDate'] ) : ''; ?>" readonly="readonly" placeholder="<?php echo __( 'Start Date', 'reports-and-statistics' ); ?>" />
            			</li>
                        
            			<li>
              				<label><?php echo __( 'End Date', 'reports-and-statistics' ); ?>:</label>
              				&nbsp;
              				<input type="text" name="eDate" id="edate" class="date" value="<?php echo ( isset( $_GET['eDate'] ) ) ? esc_attr( $_GET['eDate'] ) : ''; ?>" readonly="readonly" placeholder="<?php echo __( 'End Date', 'reports-and-statistics' ); ?>" />
            			</li>
                        
                        <?php } else { ?>
                        
                        	<input type="hidden" value=""  name="days" id="filter_days"   />
                            <input type="hidden" value=""  name="sDate" id="sdate" />
                            <input type="hidden" value="" name="eDate" id="edate"   />
                            
                       <?php } ?>
                       
                          <li>
                        	<label><?php echo __( 'Interval Value', 'reports-and-statistics' ); ?>:</label>
                            <input type="text" name="rns_filter_interval" id="rns_filter_interval" class="rns-filter-textbox" value="<?php echo ( isset( $_GET['rns_filter_interval'] ) ) ? esc_attr( $_GET['rns_filter_interval'] ) : ''; ?>" />
                        </li> 
                         
            			<li>
              				<label><?php echo __( 'Type of chart', 'reports-and-statistics' ); ?>:</label>
              				&nbsp;
              				<select name="type_of_chart" id="type-of-chart">
                            
                            	<option value="bar" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='bar') ? 'selected="selected"' : ''; ?> >		 <?php echo __( 'Bar Charts', 'reports-and-statistics' ); ?>
                                </option>
                                
                				<option value="column" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='column' ) ?'selected="selected"' : ''; ?> >		 			<?php echo __( 'Column Chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <option value="line" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='line' ) ?'selected="selected"' : ''; ?> >		 		<?php echo __( 'Line Chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <option value="spline" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='spline' ) ?'selected="selected"' : ''; ?> >		 	<?php echo __( 'Spline Charts', 'reports-and-statistics' ); ?>
                                </option>
                                
                				<?php 
								 // if secon dimenssion equal to none and report is not a distribution analysis and trend analysis   
								 if( $action != "distribution_report" && $action != "trend_report" && $second_dimension=="none"  ) { ?>
                                <option value="pie" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='pie') ? 'selected="selected"' : ''; ?> >		 				<?php echo __( 'Pie Charts', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <option value="doughnut" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='doughnut') ? 'selected="selected"' : ''; ?> >		 <?php echo __( 'Doughnut Charts', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <?php 
								}                
								 // if second dimension is not none from filter
								 if($second_dimension!="none"  ) { 
								 ?>
                                 
                                <option value="area" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='area') ? 'selected="selected"' : ''; ?> >		 	<?php echo __( 'Area chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                 <option value="splineArea" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='splineArea') ? 'selected="selected"' : ''; ?> >	<?php echo __( 'Area Spline Chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <option value="stackedColumn" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='stackedColumn') ? 'selected="selected"' : ''; ?> >		 <?php echo __( 'Stacked Column Chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <option value="stackedArea" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='stackedArea') ? 'selected="selected"' : ''; ?> >		<?php echo __( 'Stacked Area Chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <option value="stackedBar" <?php echo ( isset( $_GET['type_of_chart'] ) && $_GET['type_of_chart'] =='stackedBar') ? 'selected="selected"' : ''; ?> >		<?php echo __( 'Stacked Bar Chart', 'reports-and-statistics' ); ?>
                                </option>
                                
                                <?php } ?>
                                
              				</select>

            			</li>
                   </ul>
                   <ul class="rns-checkbox">
                        <li class="rns_drop_outer <?php if($action == "trend_report" ) { echo "rns_trends_vcenter" ; } ?> ">
                        	<input type="checkbox" name="rns_drop_zero_rows" id="rns_drop_zero_rows" value="1" <?php if( $drop_zero_rows == 1 ) { echo "checked='checked'"; } ?> /> 
                            <label for="rns_drop_zero_rows"><?php echo __( 'Drop zero rows', 'reports-and-statistics' ); ?>  </label>
                        	
                        </li>
                         <li class="rns_drop_outer <?php if($action == "trend_report" ) { echo "rns_trends_vcenter" ; } ?> ">
                        	<input type="checkbox" name="rns_drop_zero_columns" id="rns_drop_zero_columns" value="1" <?php if( $drop_zero_columns == 1 ) { echo "checked='checked'"; } ?> />
                        	<label for="rns_drop_zero_columns"><?php echo __( 'Drop zero columns', 'reports-and-statistics' ); ?>  </label> 
                        </li>
                       
          			</ul>
                   <?php
					/***************************************************
						Top bar reports filter area  end here
					****************************************************/
				   ?>
                    
					<?php
                    /***************************************************
                    	Buttons area  start here
                    ****************************************************/
                    ?>
          			<div class="rns-fltr-wrapper">
                    
            			<ul class="rns-filter-btn">
                        
              				<li>
                				<input type="button" value="<?php echo __( 'Run Report', 'reports-and-statistics' ); ?>" name="apply" onclick="applyFilters();" />
              				</li>
                            
                         <?php
						 //If report id is not empty and current user have permission to update report 
						 if( !empty( $report_id ) &&  rns_check_save_report_author_id( $report_id ) ) {
							  
							 $rns_button_text =  __( 'Update Report', 'reports-and-statistics' );
							
						 	// if current user have permission to delete the report
						 	if( rns_check_role_save_delete_permission( "rns_delete_roles" ) ) {
						?>
              					<li>
                					<input type="button" value="<?php echo  __( 'Delete Report', 'reports-and-statistics' ) ; ?>" name="delete" class="rns_delete_report" />
              					</li>
                       <?php 
							} 
							 
						} else {
							  
							 $rns_button_text =  __( 'Save Report', 'reports-and-statistics' );
							 
					    }
						 
					   ?>
                            
                          
                         <?php
						 	// if user role have permission to save the file
						 	if( rns_check_role_save_delete_permission( "rns_save_roles" ) ) {
						?>
              				<li>
                				<input type="button" value="<?php echo $rns_button_text ; ?>" name="save" class="rns_open_save" />
              				</li>
                        <?php } ?>
                        
                        
                        
                       
                      
            			</ul>
          			</div>
        		</div>
                
                <?php
				/***************************************************
 					Buttons area  end here
				****************************************************/
				?>
        	<?php 
				// add graph and table view  file by report name 
				include_once $action.'.php'; 
			?>
      		</div>
            
            
      		<?php
			
				if ( isset ( $_GET['ticket_author'] ) && $_GET['ticket_author'] != '' ) {
		  			$ticket_author_get		= rns_get_array_values_from_comma_separated_string( $_GET['ticket_author'] );
				} else {
					$ticket_author_get		= '';
				} 		
				
				//If state is selected from filter
				if ( isset ( $_GET['state'] ) && $_GET['state'] != '' ) {
		  			$stateValues		= rns_get_array_values_from_comma_separated_string( $_GET['state'] );
				} else {
					$stateValues		= '';
				}
				
				//If statuses are selected from filter
				if ( isset ( $_GET['status'] ) && $_GET['status'] != '' ) {
					$statusValues		= rns_get_array_values_from_comma_separated_string( $_GET['status'] );
				} else {
					$statusValues		= '';
				}
				
				//If agents are selected from filter
				if ( isset ( $_GET['staff'] ) && $_GET['staff'] != '' ) {
					$agentValues		= rns_get_array_values_from_comma_separated_string( $_GET['staff'] );
				} else {
					$agentValues		= '';
				}
				
				//If department are selected from filter
				if ( isset ( $_GET['department'] ) && $_GET['department'] != '' ) {
					$departmentValues	= rns_get_array_values_from_comma_separated_string( $_GET['department'] );
				} else {
					$departmentValues		= '';
				}
				
				//If tags are selected from filter
				if ( isset ( $_GET['tag'] ) && $_GET['tag'] != '' ) {
					$tagValues			= rns_get_array_values_from_comma_separated_string( $_GET['tag'] );
				} else {
					$tagValues			= '';
				}
				
				//If products are selected from filter
				if ( isset ( $_GET['product'] ) && $_GET['product'] != '' ) {
					$productValues		= rns_get_array_values_from_comma_separated_string( $_GET['product'] );
				} else {
					$productValues		= '';
				}
				
				
				// if state value empty 
				if ( empty ( $stateValues ) ) {
					$stateValues	=	array( 'open' );
				}
	  		?>
            
            
            
            <?php
            /***************************************************
             left bar filters area start here
            ****************************************************/
            ?>
            
      		<div class="rns-left-filters" <?php /* if second dimension is  selected */  if( isset( $_GET['second'] ) ) { ?> style="width:0%;display:none;"  <?php  }  ?> >
        		<h3><?php echo __( 'Apply Filters', 'reports-and-statistics' ); ?></h3>
                <div class="rns-filtet-list">
          			<h3><?php echo __( 'Second Dimension', 'reports-and-statistics' ); ?></h3>
          			<ul>
                    	
            			<li>
              				<input type="radio" value="none" name="second"  id="none" <?php echo ( $second_dimension == 'none' ) ? 'checked="checked"' : ''; ?>  class="filter-checkbox second_dimension" />
              				<label for="none"><?php echo __( 'None', 'reports-and-statistics' ); ?></label>
            			</li>
                        <li>
                        	<input type="radio" value="clients" name="second"  id="clients" <?php echo ( $second_dimension == 'clients' ) ? 'checked="checked"' : ''; ?>  class="filter-checkbox second_dimension" />
              				<label for="clients"><?php echo __( 'Clients', 'reports-and-statistics' ); ?></label>
                        </li>
                        <?php
                            // if custom field array not empty
							if( isset( $stat_custom_fields ) && ( count( $stat_custom_fields )>0 ) ) {
								foreach( $stat_custom_fields as $key=>$value ) {
										// if filter option for custom filed is enable from report settings page
										if( rns_show_second_dimenssion_filter_option_by_report( $action , $value) ) {
						?>
                        <li>
              				<input type="radio" value="<?php echo $value; ?>" name="second"  id="<?php echo $value; ?>" <?php echo ( $second_dimension ==  $value  ) ? 'checked="checked"' : ''; ?>  class="filter-checkbox second_dimension" />
              				<label for="<?php echo $value; ?>"><?php echo rn_get_custom_fields_title_by_name($value)." ( ". $value .")"; ?></label>
            			</li>
                       <?php } } } ?>

          			</ul>
        		</div>
        		<div class="rns-filtet-list">
                  <?php
				  	// if state option  is enable from report settings page
				  	if( rns_show_second_dimenssion_filter_option_by_report( $action , 'state' ) ) {
				  ?>
          			<h3><?php echo __( 'State', 'reports-and-statistics' ); ?></h3>
          			<ul>
            			<li>
              				<input type="radio" value="open" name="state"  id="open" <?php echo ( ! empty( $stateValues ) && in_array( 'open', $stateValues ) ) ? 'checked="checked"' : ''; ?>  class="filter-checkbox" onchange="OnChangeStatus(this.value);" />
              				<label for="open"><?php echo __( 'Open', 'reports-and-statistics' ); ?></label>
            			</li>
            			<li>
              				<input type="radio" value="closed" name="state" id="closed" <?php echo ( ! empty( $stateValues ) && in_array( 'closed', $stateValues ) ) ? 'checked="checked"' : ''; ?>  class="filter-checkbox" onchange="OnChangeStatus(this.value);" />
              				<label for="closed"><?php echo __( 'Closed', 'reports-and-statistics' ); ?></label>
            			</li>
                        <li>
              				<input type="radio" value="both" name="state"  id="both" <?php echo ( ! empty( $stateValues ) && in_array( 'both', $stateValues ) ) ? 'checked="checked"' : ''; ?>  class="filter-checkbox" onchange="OnChangeStatus(this.value);" />
              				<label for="both"><?php echo __( 'Both', 'reports-and-statistics' ); ?></label>
            			</li>
          			</ul>
                   <?php } ?> 
        		</div>
                
                
                
                <?php $ticket_author = rns_get_agents_list( "authors" );  ?>
                <div class="rns-filtet-list rns-cust-filter-list">
          				<h3><?php echo __( 'Clients', 'reports-and-statistics' ); ?></h3>
          				<ul> 				
            					<li>		
                                    <?php
										// clients dropdown list
										rns_get_client_dropdown( $ticket_author , $ticket_author_get );
									?>
              							
            					 </li>
           
          				</ul>
                      
                        <a href="javascript:void(0)" onclick="rns_set_client_empty()">Clear</a>
        	    </div>
                
                 <?php
				 	// if status option  is enable from report settings page
				 	if( in_array( 'status', $stat_custom_fields ) ) {
				
					 // if status array is not empty
					 if ( ! empty( $statuses ) ) { 
				?>
        			<div class="rns-filtet-list">
          				<h3><?php echo __( 'Status', 'reports-and-statistics' ); ?></h3>
          				<ul>
            				<?php foreach ( $statuses as $status => $label ) { ?>
            					<li>
              						<input type="checkbox" value="<?php echo $status; ?>" name="status[]" id="<?php echo $status; ?>" class="filter-checkbox status-filter-check" <?php echo ( ! empty( $statusValues ) && in_array( $status, $statusValues ) ) ? 'checked="checked"' : ''; ?> />
              						<label for="<?php echo $status; ?>"><?php echo  $label; ?></label>
            					</li>
            				<?php } ?>
          				</ul>
        			</div>
        		<?php } }  ?>
                
                
                
                
                
                <?php 
					// if assignee option  is enable from report settings page 
					if( in_array( 'assignee', $stat_custom_fields ) ) { 
				?>
        		<div class="rns-filtet-list">
          			<h3><?php echo __( 'Agents', 'reports-and-statistics' ); ?></h3>
         			<ul>
            			<?php 
							// if agentlist array is not empty
							if ( ! empty( $agentsList ) ) {
								foreach ( $agentsList as $agent )  {
						?>
                                    <li>
                                        <input type="checkbox" value="<?php echo $agent->ID;?>" name="staff[]" id="agent-<?php echo $agent->ID;?>" class="filter-checkbox" <?php echo ( ! empty( $agentValues ) && in_array( $agent->ID, $agentValues ) ) ? 'checked="checked"' : ''; ?> />
                                        <label for="agent-<?php echo $agent->ID;?>"><?php echo  ucwords( $agent->display_name ); ?></label>
                                    </li>
            			<?php
								}
							}
							else {
						?>
            						<li><?php echo __( 'No agent found', 'reports-and-statistics' ); ?>.</li>
            			<?php
							}
						?>
          			</ul>
        		</div>
        		<?php } ?>
                

            	<?php
					//get all custom fields 
					$fields = WPAS()->custom_fields->get_custom_fields();
					
					// if fields array is not empty
					if( ! empty( $fields ) ) {
						
						foreach ( $fields as $field ) {
							
							$field_type = $field['args']['field_type'];
							$cfpostValues = isset($_GET['cpf_'.$field['name']])?rns_get_array_values_from_comma_separated_string($_GET['cpf_'.$field['name']]):'';
				?>
                	<?php
							/*if custom field type is not taxonomy , assignee , status and enable from settings and field name is not empty */
							if( $field_type != "taxonomy" && in_array( $field['name'] , $stat_custom_fields ) && !empty( $field['name'] ) && $field['name'] != 'assignee' && $field['name'] != 'status'   ) {
								
					?>
                    
                    <div class="rns-filtet-list">
                        <h3><?php echo  ucfirst( $field['args']['title'] ); ?></h3>
                        <ul>
                	`		<?php
							// if field type is text
							if( $field_type == "text" ) {
							?>
                                   <li>
                                           <input type="text" class="rns-filter-textbox" value="<?php echo (isset($_GET["cpf_".$field['name']]))? esc_attr( $_GET["cpf_".$field['name']] ) : ''; ?>" name="cpf_<?php echo $field['name'] ;?>" id="cpf_<?php echo $field['name'] ;?>"  />
                                   </li>
						   <?php 
							}  // if field type is select or checkbor or radio 
							else if( $field_type == "select" || $field_type == "checkbox" || $field_type == "radio"  ) {

        					   		$cs_options =  rn_get_custom_fields_option( $field['name'] );
									foreach( $cs_options as $cs_key =>$cs_option ) {
						   ?>
                                    <li>
                                        <input type="checkbox" value="<?php echo $cs_key; ?>" name="cpf_<?php echo $field['name'] ;?>[]" id="cf_<?php echo $cs_option ;?>" class="filter-checkbox" <?php echo ( ! empty( $cfpostValues ) && in_array( strtolower($cs_option), $cfpostValues ) ) ? 'checked="checked"' : ''; ?>  />
                                        <label for="cf_<?php echo $cs_option ;?>"><?php echo ucwords( $cs_option ); ?></label>
                                    </li>  
                                    
                                  <?php  
								  } 
                            
							} 
							// if field type is date 
							else if( $field_type == "date-field" ) {
							?>
                                 <li>
                                 	<input type="text" class="date rns-filter-textbox"  name="cpf_<?php echo $field['name'] ;?>" id="cpf_<?php echo $field['name'] ;?>"  value="<?php echo (isset($_GET["cpf_".$field['name']]))? esc_attr ($_GET["cpf_".$field['name']] ) : ''; ?>" />
                                 </li>
                            <?php } ?>
                             </ul>
                          </div>
                          <?php
							}
						}
					}
				
				?>

                <?php
				//get taxonomies list
				$taxonomy_objects = get_object_taxonomies( 'ticket', 'objects' );

				foreach( $taxonomy_objects as  $key=>$res ) {

							$terms_data = get_terms($key);
						// if taxonomy is enable from report settings	
						if( in_array( $key, $stat_custom_fields ) ) {
				?>
                <div class="rns-filtet-list">
          			<h3><?php echo  $res->labels->name; ?></h3>
          			<ul>
            			<?php
							//if terms data is explty
							if( ! empty( $terms_data ) )  {
								foreach( $terms_data as $tag )  {
								$tagValues = isset($_GET['tx_'.$key ])?rns_get_array_values_from_comma_separated_string($_GET['tx_'.$key ]):'';
						?>
                                    <li>
                                        <input type="checkbox" value="<?php echo $tag->term_id; ?>" name="tx_<?php echo $key ;?>[]" id="<?php echo $key ;?>-<?php echo $tag->term_id; ?>" class="filter-checkbox" <?php echo ( ! empty( $tagValues ) && in_array( $tag->term_id, $tagValues ) ) ? 'checked="checked"' : ''; ?> />
                                        <label for="<?php echo $key ;?>-<?php echo $tag->term_id; ?>"><?php echo  ucwords( $tag->name ); ?></label>
                                    </li>
            			<?php
								}
							} else {
						?>
            						<li><?php echo __( 'No Tag found', 'reports-and-statistics' ); ?>.</li>
            			<?php
							}
						?>
          			</ul>
        		</div>
                		<?php }  ?>
                <?php } ?>
      		</div>

            <?php
            /***************************************************
             left bar filters area end here
            ****************************************************/
            ?>
             
            
			<?php
            /***************************************************
             Help text of each report start here
            ****************************************************/
            ?>

            
            <div class="rns-help-right-filters"  >
            		<div class="rns-filtet-list">	
            		<h3><?php echo __( 'About This Report', 'reports-and-statistics' ); ?></h3>
						<?php 
                            /* Get chart description by report */
                            echo rns_get_description_of_chart_by_report( $action ); 
                        ?>
                    </div>
            </div>
            
            <?php
            /***************************************************
             Help text of each report end here
            ****************************************************/
            ?>
            
    	</form>
       
       <?php
	   /***************************************************
	   	Report save form area start here 
	   ****************************************************/
	   ?>
       
        <div class="rns_saveform_view" id="rns_saveform_view" >
			<div class="rns-save-report-header-title-wrap">
				<h1><?php echo __( 'Save Report' , 'reports-and-statistics' ); ?></h1>
			</div>
			<form action=""	 method="post" id="rns_save_form" >
				<ul>
                 
					<li>
						<label class="rns-save-form-field-label"><?php echo __( 'Short Name:' , 'reports-and-statistics' ); ?></label>
						<br />
						<input class="rns-save-form-field-text" type="text" name="sname" id="sname" value=" <?php echo  ( isset( $report_detail->post_title ) ? $report_detail->post_title :'' );   ?>" />
						<span class="rns-field_err"></span>
					</li>
                    
					<li>
						<label class="rns-save-form-field-label"><?php echo __( 'Long Name:' , 'reports-and-statistics' ); ?></label>
						<br />
						<input class="rns-save-form-field-text" type="text" name="lname" id="lname" value=" <?php echo  get_post_meta(  $report_id,  '_rns_long_name' , true );   ?> " />
						<span class="rns-field_err"></span>
					</li>
                    
					<li>
						<label class="rns-save-form-field-label"><?php echo __( 'Description:' , 'reports-and-statistics' ); ?></label>
						<br />
						<textarea class="rns-save-form-field-textarea"  name="desc" id="desc" rows="4"><?php echo  ( isset( $report_detail->post_content ) ? $report_detail->post_content :'' );   ?></textarea>
						<span class="rns-field_err"></span>
					</li>
                    
					<li>
						<label class="rns-save-form-field-label"><?php echo __( 'Sort Order:' , 'reports-and-statistics' ); ?> </label>
						<br />
						<input class="rns-save-form-field-number" type="number" name="report_order" id="report_order" value="<?php echo  get_post_meta(  $report_id,  '_rns_sort_order' , true );   ?>" />
						<span class="rns-field_err"></span>
					</li>
                    
					<li>
					
						<table>
						<?php 
							$assignedroles  = get_post_meta(  $report_id,  '_rns_assign_roles' , true );
							$assignedRoles  = explode( "," , $assignedroles );
							$roles 			=	new WP_Roles();
							$tabHTML		=	'<tr class="row-1 odd" valign="top"><th scope="row" class="first">
									<label for="rns_delete_role_type">'.__( 'Which roles can see reports' , 'reports-and-statistics' ).' : </label>
								</th>
								<td class="second tf-select">
								{FIELDS}	
								</td></tr>';
							$Fields			=	'';
							
							foreach( $roles->get_names() as $key=>$val ) {
									
									//if assigned roles array is not empty and key of roles exists in assigned roles array 
									if( !empty( $assignedRoles ) && ( in_array( $key , $assignedRoles ) || $key==rns_get_current_user_role() ) ) {
										$Fields	.=	'<input type="checkbox" class="rns_role_type" name="rns_role_type[]" checked="checked" id="' . $key . '" value="' . $key . '" ><label for="' . $key . '">' . __( $val , 'reports-and-statistics' ) . '</label><br/>';
									}
									else {
										$Fields	.=	'<input type="checkbox" class="rns_role_type" name="rns_role_type[]" id="' . $key . '" value="' . $key . '" ><label for="' . $key . '">' . __( $val , 'reports-and-statistics' ) . '</label><br/>';
									}
										
							}
							
							$tabHTML	=	str_replace( '{FIELDS}' , $Fields , $tabHTML );	
							
							echo  $tabHTML;
						?>
						</table>
					</li>
                    
					<li>
						<span id="rns_role_type_err" class="rns-field_err"></span>
					</li>
					
				</ul>

				<?php
					
					//@TODO: Replace this section of code with a call to wpas_filter_input_server( 'REQUEST_URI' ) once AS 4.3.3 has been released.
				
					//This filtered input might not work in some versions of PHP - see this issue: https://github.com/xwp/stream/issues/254		
					$request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI',FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);

					// Therefore we have a fallback...
					if ( empty( $request_uri ) && isset( $_SERVER['REQUEST_URI'] ) ) {
						$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE ) ;
					}

					$request_str = str_replace("&report_id=".$report_id, "", $request_uri);
					
					//This filtered input might not work in some versions of PHP - see this issue: https://github.com/xwp/stream/issues/254		
					$the_server = filter_input(INPUT_SERVER, 'HTTP_HOST',FILTER_SANITIZE_STRING);
					
					// Therefore we have a fallback...
					if ( empty( $the_server ) && isset( $_SERVER['HTTP_HOST'] ) ) {
						$the_server = filter_var( $_SERVER['HTTP_HOST'], FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE ) ;
					}					

				 ?>   
			
				<div class="rns-fltr-wrapper">
					<ul class="rns-filter-btn">
						
						<li>
							<input type="hidden" name="rns_report_id" id="rns_report_id" value="<?php echo $report_id;  ?>"  />
							<input type="hidden" name="plugin_url" id="plugin_url" value="<?php echo plugin_dir_url(__DIR__); ?>"  />
							<input type="hidden" name="report_link" id="report_link" value='<?php echo htmlentities( '//' . $the_server . '' . $request_str, ENT_QUOTES ) ; ?>' />
							<input type="button" value="<?php echo $rns_button_text; ?>" name="save" id="rns_save_button" />
                            <input type="button" value="<?php echo __( 'Cancel', 'reports-and-statistics' ) ; ?>" name="cancel" class="rns_close_report_view" />
						</li>
                        
						<li>
							<span id="rns_save_message"></span>
						</li>
                        
					</ul>
				</div>
			</form>
		</div>
        
        <?php
	   /***************************************************
	   	Report save form area end here
	   ****************************************************/
	   ?>
       
  	</div>
