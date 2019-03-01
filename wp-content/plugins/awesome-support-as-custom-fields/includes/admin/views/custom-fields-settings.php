<div class="wrap">
	<div class="options-container">
		<div class="wpas-cf-notification">
			<p class="wpas-cf-notification-content"></p>
		</div>
		<form id="wpas-as-form">
			<table class="form-table wpas-cf-table">
				<tr class="first wpas-cf-heading">
					<th class="first last" colspan="2">
						<h3 id="custom-fields"><?php _e( 'Custom fields', 'wpas-cf' ); ?></h3>
					</th>
				</tr>				
				<?php echo apply_filters( 'ascf_all_fields_markup', 10 ); ?>
			</table>
		<p><?php _e( 'Fields with <span class="required">*</span> are mandatory', 'wpas-cf'); ?></p>
		<button type="button" id="wpas-cf-add-btn" class="button button-primary"><?php _e( 'Add Field', 'wpas-cf' ); ?></button>
		<button type="submit" id="wpas-cf-save" class="button button-secondary"><?php _e( 'Save fields', 'wpas-cf' ); ?></button>
		</form>
	</div><!-- ./options-container -->
</div><!-- ./wrap -->



