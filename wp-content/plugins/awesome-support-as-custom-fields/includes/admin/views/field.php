<?php 
if( ! empty( $field) ) : 
extract( $field );

$hide = ! empty ( $hide ) ? $hide : $this->defaults['hide'] ;
$order = isset ( $order ) && ( ! empty ( $order ) || 0 == $order ) ? (int)$order : $this->defaults['order'] ;

?>
<tbody class="wpas-cf-wrapper-object">
	<!-- Title -->
	<tr class="wpas-cf-title">
		<th class="first">
			<label>
				<?php _e( 'Title', 'wpas-cf' ) ?> 
				<span class="required">*</span>
			</label>
		</th>
		<td class="second wpas-cf-input wpas-cf-title-field">
			<p class="description"><?php _e( 'The “human readable” name of your custom field. That’s the name that’ll be shown in the submission form', 'wpas-cf' ) ?></p>
			<?php if( ! empty( $title ) ) : ?>
				<input type="text" name="title" placeholder="<?php _e( 'Title', 'wpas-cf' ) ?>" value="<?php echo $title ?>" required>
			<?php else : ?>
				<input type="text" name="title" placeholder="<?php _e( 'Title', 'wpas-cf' ) ?>" required>
			<?php endif; ?>
		</td>
	</tr>

	<!-- Name -->
	<tr class="wpas-cf-name">
		<th class="first">
			<label>
				<?php _e( 'Name', 'wpas-cf' ) ?> 
				<span class="required">*</span>
			</label>
		</th>
		<td class="second wpas-cf-input wpas-cf-name-field">
			<p class="description"><?php _e( 'A text string identifying your custom field. It should consist of only lowercase letters, numbers and underscore, for instance my_custom_field', 'wpas-cf' ) ?></p>
			<?php if( ! empty( $name ) ) : ?>
				<input type="text" name="name" placeholder="<?php _e( 'Name', 'wpas-cf' ) ?>" value="<?php echo $name ?>" pattern="^[_a-z0-9]*$" title="<?php _e( 'The name should consist of only lowercase letters, numbers and underscore', 'wpas-cf' ); ?>" required >
			<?php else : ?>
				<input type="text" name="name" placeholder="<?php _e( 'Name', 'wpas-cf' ) ?>" pattern="^[_a-z0-9]*$" title="<?php _e( 'The name should consist of only lowercase letters, numbers and underscore', 'wpas-cf' ); ?>" required >
			<?php endif; ?>
		</td>
	</tr>

	<!-- Field type -->
	<tr class="wpas-cf-field-type">
		<th class="first">
			<label>
				<?php _e( 'Field type', 'wpas-cf' ) ?> 
				<span class="required">*</span>
			</label>
		</th>
		<td class="second wpas-cf-input wpas-cf-field-type-field">
			<p class="description"><?php _e( 'The type of field that you want to register', 'wpas-cf' ) ?></p>
			<select required>
				<?php
				if( ! empty( $field_type ) ) :

					// Check if text field
					if( $field_type == 'text' ) :
				?>
					<option value="text" selected><?php _e( 'Text', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="text"><?php _e( 'Text', 'wpas-cf') ?></option>
				<?php		
					endif;

					// Check if url field
					if( $field_type == 'url' ) :
				?>
					<option value="url" selected><?php _e( 'URL', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="url"><?php _e( 'URL', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if email field
					if( $field_type == 'email' ) :
				?>
					<option value="email" selected><?php _e( 'Email', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="email"><?php _e( 'Email', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if email field
					if( $field_type == 'number' ) :
				?>
					<option value="number" selected><?php _e( 'Number', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="number"><?php _e( 'Number', 'wpas-cf') ?></option>
				<?php		
					endif;

						//Check if date field
					if( $field_type == 'date-field' ) :
				?>
					<option value="date-field" selected><?php _e( 'Date', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="date-field"><?php _e( 'Date', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if password field
					if( $field_type == 'password' ) :
				?>
					<option value="password" selected><?php _e( 'Password', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="password"><?php _e( 'Password', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if upload field
					if( $field_type == 'upload' ) :
				?>
					<option value="upload" selected><?php _e( 'Upload', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="upload"><?php _e( 'Upload', 'wpas-cf') ?></option>
				<?php		
					endif;

						//Check if select field
					if( $field_type == 'select' ) :
				?>
					<option value="select" selected><?php _e( 'Select', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="select"><?php _e( 'Select', 'wpas-cf') ?></option>
				<?php		
					endif;

						//Check if checkbox field
					if( $field_type == 'checkbox' ) :
				?>
					<option value="checkbox" selected><?php _e( 'Checkbox', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="checkbox"><?php _e( 'Checkbox', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if radio field
					if( $field_type == 'radio' ) :
				?>
					<option value="radio" selected><?php _e( 'Radio', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="radio"><?php _e( 'Radio', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if textarea field
					if( $field_type == 'textarea' ) :
				?>
					<option value="textarea" selected><?php _e( 'Textarea', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="textarea"><?php _e( 'Textarea', 'wpas-cf') ?></option>
				<?php		
					endif;

					//Check if wysiwyg field
					if( $field_type == 'wysiwyg' ) :
				?>
					<option value="wysiwyg" selected><?php _e( 'WYSIWYG', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="wysiwyg"><?php _e( 'WYSIWYG', 'wpas-cf') ?></option>
				<?php		
					endif;

						//Check if taxonomy field
					if( $field_type == 'taxonomy' ) :
				?>
					<option value="taxonomy" selected><?php _e( 'Taxonomy', 'wpas-cf') ?></option>
				<?php
					else :
				?>
					<option value="taxonomy"><?php _e( 'Taxonomy', 'wpas-cf') ?></option>
				<?php		
					endif;

				endif; ?>
			</select>
		</td>
	</tr>
	
	<!-- hide option -->
	<tr class="wpas-cf-options-hide">
		<th class="first">
			<label><?php _e( 'Hide Field', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-options-hide">
			<p class="description"><?php _e( 'Turn off this field completely - when this item is checked this field will not show up on the back end or the front end.', 'wpas-cf' ) ?></p>		
			<div class="wpas-cf-options-hide-hide-wrapper">
				<input type="checkbox" value="yes" <?php checked( $hide, 'yes' ) ?>><?php _e( 'Hide', 'wpas-cf' ); ?>				
			</div>
		</td>
	</tr>
	<!-- fields order -->
	<tr class="wpas-cf-options-field-order">
		<th class="first">
			<label><?php _e( 'Sort Order', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-options-field-order">
			<p class="description"><?php _e( 'The order in which this field appears on the front end ticket screen', 'wpas-cf' ) ?></p>
			<div class="wpas-cf-field-order-wrapper">
				<input type="number" class="wpas-cf-field-order" value="<?php echo $order ?>" >
			</div>
		</td>
	</tr>
	
	<!-- Options for checkbox/radio/select -->
	<?php if( $field_type == 'checkbox' || $field_type == 'radio' || $field_type == 'select' ) : ?>
	<tr class="wpas-cf-options">
	<?php else : ?>
	<tr class="wpas-cf-options" style="display: none;">
	<?php endif; ?>
		<th class="first">
			<label><?php _e( 'Options ', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-field-type-options">
			<div class="wpas-cf-options-wrapper">
				<?php 
				$field_type = $field_type;
				if( $field_type == 'select' || $field_type == 'checkbox' || $field_type == 'radio' ) :

					if( ! empty( $field['options'] ) ) :
						
						foreach ($field['options'] as $option_id => $option_label) :
						?>
						
						<div class="wpas-cf-option">
							<input class="wpas-cf-option-id" value="<?php echo $option_id ?>" type="text" placeholder="<?php _e( 'Option ID', 'wpas-cf' ); ?>">
							<input class="wpas-cf-option-label" value="<?php echo $option_label ?>" type="text" placeholder="<?php _e( 'Option Label', 'wpas-cf' ); ?>">
							<button id="wpas-cf-remove-option" class="button-secondary"><?php _e( 'Remove', 'wpas-cf'); ?></button>
						</div>

						<?php				
						endforeach;
					endif;
				
				endif;
				?>
			</div>
			<button id="wpas-cf-add-option" type="button" class="button-secondary"><?php _e( 'Add option', 'wpas-cf' ) ?></button>
		</td>
	</tr>

	<!-- Options for textarea field-->
	<?php if( $field_type == 'textarea' ) : ?>
	<tr class="wpas-cf-options-textarea">
	<?php else : ?>
	<tr class="wpas-cf-options-textarea" style="display: none;">
	<?php endif; ?>
		<th class="first">
			<label><?php _e( 'Options', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-textarea-options">
			<div class="wpas-cf-textarea-options-wrapper">
				<?php if( $field_type == 'textarea' && ! empty( $rows ) && ! empty( $cols ) ) : ?>
					<input type="text" class="wpas-cf-textarea-rows" value="<?php echo $rows ?>" placeholder="<?php _e('Rows', 'wpas-cf') ?>">
					<input type="text" class="wpas-cf-textarea-cols" value="<?php echo $cols ?>" placeholder="<?php _e('Cols', 'wpas-cf') ?>">
				<?php else : ?>
					<input type="text" class="wpas-cf-textarea-rows" placeholder="<?php _e('Rows', 'wpas-cf') ?>">
					<input type="text" class="wpas-cf-textarea-cols" placeholder="<?php _e('Cols', 'wpas-cf') ?>">
				<?php endif; ?>
			</div>
		</td>
	</tr>

	<!-- Options for upload field -->
	<?php if( $field_type == 'upload' ) : ?>
	<tr class="wpas-cf-options-upload">
	<?php else : ?>
	<tr class="wpas-cf-options-upload" style="display: none;">
	<?php endif; ?>
		<th class="first">
			<label><?php _e( 'Options', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-upload-options">
			<div class="wpas-cf-textarea-upload-wrapper">
				<?php if( ! empty ( $multiple ) && $multiple == 'true' ) : ?>
					 <input type="checkbox" checked><?php _e( 'Multiple', 'wpas-cf' ); ?>
				<?php else : ?>
					 <input type="checkbox"><?php _e( 'Multiple', 'wpas-cf' ); ?>				
				<?php endif; ?>
			</div>
		</td>
	</tr>

	<!-- Options for WYSIWYG field -->
	<?php if( $field_type == 'wysiwyg' ) : ?>
	<tr class="wpas-cf-options-wysiwyg">
	<?php else : ?>
	<tr class="wpas-cf-options-wysiwyg" style="display: none;">
	<?php endif; ?>
		<th class="first">
			<label><?php _e( 'Options', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-wysiwyg-options">
			<div class="wpas-cf-wysiwyg-options-wrapper">
				<?php 
				$field_type = $field_type;
				if( $field_type == 'wysiwyg' ) :

					if( ! empty( $field['settings'] ) ) :
						
						foreach ($field['settings'] as $setting_id => $setting_label) :
						?>
						
						<div class="wpas-cf-option">
							<input class="wpas-cf-option-id" value="<?php echo $setting_id ?>" type="text" placeholder="<?php _e( 'Setting ID', 'wpas-cf' ); ?>">
							<input class="wpas-cf-option-label" value="<?php echo $setting_label ?>" type="text" placeholder="<?php _e( 'Setting Label', 'wpas-cf' ); ?>">
							<button id="wpas-cf-remove-option" class="button-secondary"><?php _e( 'Remove', 'wpas-cf'); ?></button>
						</div>

						<?php				
						endforeach;
					endif;
				
				endif;
				?>
			</div>
			<button id="wpas-cf-add-wysiwyg-option" type="button" class="button-secondary"><?php _e( 'Add option', 'wpas-cf' ) ?></button>
		</td>
	</tr>

	<!-- Options for taxonomy field -->
	<?php if( $field_type == 'taxonomy' ) : ?>
	<tr class="wpas-cf-options-taxonomy">
	<?php else : ?>
	<tr class="wpas-cf-options-taxonomy" style="display: none;">
	<?php endif; ?>
		<th class="first">
			<label><?php _e( 'Options', 'wpas-cf' ); ?></label>
		</th>
		<td class="second wpas-cf-taxonomy-options">
			<div class="wpas-cf-taxonomy-wrapper">
				<div>
					<p class="description"><?php _e( 'Whether or not this taxonomy should act as a standard WordPress taxonomy. If set to false it will be used as a “fake” taxonomy and displayed as a basic select input. Please note that if it is set to false and you want to display the values in the tickets list screen you need to set $show_column to true', 'wpas-cf' ) ?>
					</p>

					<label><?php _e( 'Standard taxonomy', 'wpas-cf' ); ?></label>
					<select class="wpas-cf-taxonomy-taxo-std">
						<?php if( ! empty( $taxo_std ) && $taxo_std == true ) : ?>
							<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
							<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
						<?php else : ?>
							<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
							<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
						<?php endif; ?>
					</select>
				</div>

				<div>
					<label><?php _e( 'Taxnomony singular name', 'wpas-cf' ); ?></label>
					<?php if( ! empty( $label ) ) : ?>
						<input class="wpas-cf-taxonomy-label" type="text" name="label" placeholder="<?php _e( 'Taxonomy singular name', 'wpas-cf' ) ?>" value="<?php echo $label ?>">
					<?php else : ?>
						<input class="wpas-cf-taxonomy-label" type="text" name="label" placeholder="<?php _e( 'Taxonomy singular name', 'wpas-cf' ) ?>">
					<?php endif; ?>
				</div>
			
				<div>
					<label><?php _e( 'Taxonomy plural name', 'wpas-cf' ); ?></label>
					<?php if( ! empty( $label_plural ) ) : ?>
						<input class="wpas-cf-taxonomy-label-plural" type="text" name="label_plural" placeholder="<?php _e( 'Taxonomy plural name', 'wpas-cf' ) ?>" value="<?php echo $label_plural ?>">
					<?php else : ?>
						<input class="wpas-cf-taxonomy-label-plural" type="text" name="label_plural" placeholder="<?php _e( 'Taxonomy plural name', 'wpas-cf' ) ?>">
					<?php endif; ?>
				</div>

				<div>
					<label><?php _e( 'Hierarchical', 'wpas-cf' ); ?></label>
					<select class="wpas-cf-taxonomy-taxo-hierarchical">
						<?php if( ! empty( $taxo_hierarchical ) && $taxo_hierarchical == true ) : ?>
							<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
							<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
						<?php else : ?>
							<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
							<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>
		</td>
	</tr>

	<!-- Placeholder -->
	<tr class="wpas-cf-placeholder">
		<th class="first">
			<label><?php _e( 'Placeholder', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-placeholder-field">
			<p class="description"><?php _e( 'Placeholder to use with input fields', 'wpas-cf' ) ?></p>
			<?php if( ! empty ( $placeholder ) ) : ?>
				<input type="text" name="placeholder" placeholder="<?php _e('Placeholder', 'wpas-cf'); ?>" value="<?php echo $placeholder ?>">
			<?php else : ?>
				<input type="text" name="placeholder" placeholder="<?php _e('Placeholder', 'wpas-cf'); ?>">
			<?php endif; ?>
		</td>
	</tr>

	<!-- Default -->
	<tr class="wpas-cf-default">
		<th class="first">
			<label><?php _e( 'Default', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-default-field">
			<p class="description"><?php _e( 'The default value for your custom field', 'wpas-cf' ) ?></p>
			<?php if( ! empty ( $default ) ) : ?>
				<input type="text" name="default" placeholder="<?php _e('Default Value', 'wpas-cf'); ?>" value="<?php echo $default ?>">
			<?php else : ?>
				<input type="text" name="default" placeholder="<?php _e('Default Value', 'wpas-cf'); ?>">
			<?php endif; ?>
		</td>
	</tr>

	<!-- Required field -->
	<tr class="wpas-cf-required">
		<th class="first">
			<label><?php _e( 'Required', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-required-field">
			<p class="description"><?php _e( 'Whether or not this field is required for submission. If set to false a ticket can be submitted even if this field is not filled-in', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $required ) && $required == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>
	
	<!-- Backend Only field -->
	<tr class="wpas-cf-backend-only">
		<th class="first">
			<label><?php _e( 'Backend Only', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-backendonly-field">
			<p class="description"><?php _e( 'When set to true, this field will only show in wp-admin when viewing a ticket. It will NOT show up on the front-end at all! (Applies to version 4.0.0 and later of Awesome Support)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $backend_only ) && $backend_only == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>
	
	<!-- Show In Frontend List field -->
	<tr class="wpas-cf-show-frontend-list">
		<th class="first">
			<label><?php _e( 'Show In Front-end List?', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-show-frontend-list-field">
			<p class="description"><?php _e( 'When set to true, this field will show up in the front-end ticket list screen for existing tickets. This setting has no effect if the Backend Only option is set to true above. (Applies to version 4.1.0 and later of Awesome Support)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $show_frontend_list ) && $show_frontend_list == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>
	
	<!-- Show In Frontend Detail field -->
	<tr class="wpas-cf-show-frontend-detail">
		<th class="first">
			<label><?php _e( 'Show In Front-end Detail Screen?', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-show-frontend-detail-field">
			<p class="description"><?php _e( 'When set to true, this field will show up in the front-end detail screen for existing tickets. This setting has no effect if the Backend Only option is set to true above. (Applies to version 4.1.0 and later of Awesome Support)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $show_frontend_detail ) && $show_frontend_detail == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>	
	
	<!-- Readonly flag -->
	<tr class="wpas-cf-readonly">
		<th class="first">
			<label><?php _e( 'Read Only?', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-readonly-field">
			<p class="description"><?php _e( 'Should the agent/admin be able to edit this field? (Applies to version 4.0.0 and later of Awesome Support)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $readonly ) && $readonly == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>	

	<!-- Log field -->
	<tr class="wpas-cf-log">
		<th class="first">
			<label><?php _e( 'Log', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-log-field">
			<p class="description"><?php _e( 'Whether or not to log the changes of values to this field. The log is shown in the ticket history in the back-end (seen by admins and agents)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $log ) && $log == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>


	<!-- Show column field -->
	<tr class="wpas-cf-show-column">
		<th class="first">
			<label><?php _e( 'Show column', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-show-column-field">
			<p class="description"><?php _e( 'Whether or not to show the field’s value in the tickets list screen on the backend (wp-admin)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $show_column ) && $show_column == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>

	<!-- Sortable column field -->
	<tr class="wpas-cf-sortable-column">
		<th class="first">
			<label><?php _e( 'Sortable column', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-sortable-column-field">
			<p class="description"><?php _e( 'Whether or not to make the column sortable (not compatible with taxonomy fields)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $sortable_column ) && $sortable_column == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>

	<!-- Filterable field -->
	<tr class="wpas-cf-filterable">
		<th class="first">
			<label><?php _e( 'Filterable', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-filterable-field">
			<p class="description"><?php _e( 'Whether or not to add a filter based on the field (for taxonomies only)', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $filterable ) && $filterable == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>

	<!-- Capability field -->
	<tr class="wpas-cf-capability">
		<th class="first">
			<label><?php _e( 'Capability', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-capability-field">
			<p class="description"><?php _e( 'Required capability for editing the field value in the admin. If current user doesn’t have the required capability the field will be “read-only”', 'wpas-cf' ) ?></p>
			<?php if( ! empty( $capability ) ) : ?>
				<input type="text" name="capability" placeholder="<?php _e( 'Capability', 'wpas-cf' ) ?>" value="<?php echo $capability ?>">
			<?php else : ?>
				<input type="text" name="capability" placeholder="<?php _e( 'Capability', 'wpas-cf' ) ?>">
			<?php endif; ?>
		</td>
	</tr>

	<!-- Description field (Used to be called Admin Description -->
	<tr class="wpas-cf-desc">
		<th class="first">
			<label><?php _e( 'Description', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-desc-field">
			<p class="description"><?php _e( 'Description shown in the ticket edit screen (admin) as well as under the field when user is entering a new ticket', 'wpas-cf' ) ?></p>
			<?php if( ! empty( $desc ) ) : ?>
				<input type="text" name="admin-description" placeholder="<?php _e( 'Description', 'wpas-cf' ) ?>" value="<?php echo $desc ?>">
			<?php else : ?>
				<input type="text" name="admin-description" placeholder="<?php _e( 'Description', 'wpas-cf' ) ?>">
			<?php endif; ?>
		</td>
	</tr>

	<!-- html5_pattern field -->
	<tr class="wpas-cf-html5-pattern">
		<th class="first">
			<label><?php _e( 'HTML5 Pattern', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-html5-pattern-field">
			<p class="description"><?php _e( 'Use this to declare an HTML5 validation pattern to control what is allowed', 'wpas-cf' ) ?></p>
			<?php if( ! empty( $html5_pattern ) ) : ?>
				<input type="text" name="html5-pattern" placeholder="<?php _e( 'HTML5 Pattern', 'wpas-cf' ) ?>" value="<?php echo $html5_pattern ?>">
			<?php else : ?>
				<input type="text" name="html5-pattern" placeholder="<?php _e( 'HTML5 Pattern', 'wpas-cf' ) ?>">
			<?php endif; ?>
		</td>
	</tr>

	<!-- Select2 field -->
	<tr class="wpas-cf-select2">
		<th class="first">
			<label><?php _e( 'Select2', 'wpas-cf' ) ?></label>
		</th>
		<td class="second wpas-cf-input wpas-cf-select2-field">
			<p class="description"><?php _e( 'Make any select or taxonomy field searchable using jQuery select2', 'wpas-cf' ) ?></p>
			<select>
				<?php if( ! empty( $select2 ) && $select2 == true ) : ?>
					<option value="1" selected><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0"><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php else : ?>
					<option value="1"><?php _e( 'True', 'wpas-cf' ) ?></option>
					<option value="0" selected><?php _e( 'False', 'wpas-cf' ) ?></option>
				<?php endif; ?>
			</select>
		</td>
	</tr>


	<tr class="wpas-cf-remove">
		<th class="first">
			<label><?php _e( 'Remove field', 'wpas-cf' ) ?></label>
		</th>
		<td class="second">
			<button id="wpas-cf-remove" class="button-secondary"><?php _e('Remove field', 'wpas-cf' ); ?></button>
		</td>
	</tr>
</tbody><!-- ./wpas-cf-wrapper-object -->
<?php endif; ?>

