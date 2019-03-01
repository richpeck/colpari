<?php

$view_type = wpas_ss_category_view_type();

?>


<div class="stages">
	<?php if( wpas_ss_stage_1_enabled() ) { ?>
		<div class="stage" id="stage-1">
			<div class="steps">
				<div class="step" id="stage_1_step_1">
					<div class="field_introduction"><?php echo wpas_ss_get_option( 'ss_smart_submit_text_b4_stage1', true ); ?></div>
					<div class="field_heading"><label><?php echo wpas_ss_get_option( 'ss_smart_submit_text_category', true ); ?></label></div>
					<div class="ss_field">
					<?php 
					
					switch ( $view_type ) {
						case 'radio' :
							echo wpas_ss_categories_radio_buttons();
							break;
						case 'accordion' :
							echo wpas_ss_categories_accordion(); 
							break;
						case 'links' :
							echo wpas_ss_categories_links();
							break;
						default :
							echo wpas_ss_categories_dropdown();
							break;
					}
					?>
					</div>
				</div>
					
					
				<?php
					
				if( 'accordion' != $view_type  ) { ?>
				<div class="step" id="stage_1_step_2">
					<div class="field_heading"><label><?php echo wpas_ss_get_option( 'ss_smart_submit_text_topic', true ); ?></label></div>
					<div class="ss_field">
						<select name="topic">
								<option value=""><?php echo wpas_ss_get_option( 'ss_smart_submit_text_please_select', true ); ?></option>
						</select>
					</div>
				</div>
				<?php
				}
				?>
				
				<div class="answer">
					<div class="answer_content"></div>
					<div class="buttons">
						<a href="<?php echo wpas_get_tickets_list_page_url(); ?>" class="wpas-btn wpas-btn-ss-yes"><?php echo wpas_ss_get_option( 'ss_smart_submit_text_fix_issue', true ); ?></a>
						<a href="#" class="btn_no wpas-btn wpas-btn-ss-no"><?php echo wpas_ss_get_option( 'ss_smart_submit_text_no', true ); ?></a>
					</div>
				</div>
			</div>
		</div>
	
	<?php } ?>
		
		
	<?php if( wpas_ss_stage_2_enabled() ) { ?>
		<div class="stage" id="stage-2">
			<div class="steps">
				<div class="step" id="stage_2_step_1">
					<div class="field_introduction field_introduction_stage2"><?php echo wpas_ss_get_option( 'ss_smart_submit_text_b4_stage2', true ); ?></div>
					<div class="field_heading"><label><?php echo wpas_ss_get_option( 'ss_smart_submit_text_search', true ); ?></label></div>
					<div class="ss_field">
						<input type="text" id="search_field" />
						<button type="button" class="btn_search"><?php echo wpas_ss_get_option( 'ss_smart_submit_text_search_btn', true ); ?></button>
						<div class="ss_msg"></div>
						<div class="clear clearfix"></div>
					</div>
				</div>
				
				<div class="answer search_answer">
					<div class="answer_content"></div>
					<div class="buttons">
						<a href="#" class="open_ticket_btn_2 btn_no wpas-btn wpas-btn-ss-no"><?php echo wpas_ss_get_option( 'ss_smart_submit_text_open_tkt', true ); ?></a>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
	
		
		
</div>