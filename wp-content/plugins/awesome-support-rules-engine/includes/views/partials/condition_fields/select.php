<div class="condition <?php echo $slug; ?>">
	<?php
		if( isset( $condition_operators ) && !empty( $condition_operators )){?>

			<select name="<?php echo $slug . '_operator'; ?>" class="condition-operators <?php echo $slug . ''; ?>">

				<?php foreach( $condition_operators as $operator_slug => $operator) : ?>
					<option value="<?php echo $operator_slug; ?>" <?php  if(isset($condition_field[ 'operator' ])){ selected( $condition_field[ 'operator' ], $operator_slug );  }?>><?php echo $operator; ?></option>
				<?php endforeach; ?>
				
			</select>
		<?php
		}
	?>
	<label for="<?php echo $slug; ?>"><?php echo $name; ?></label>
	<?php 
		$field_selction_type = 'action';
		if(!empty($slug)){
			$section_type = explode('_', $slug);
			if( !empty($section_type[0]) ){
				$field_selction_type = $section_type[0];
			}
		}
		if( 'condition' === $field_selction_type){?>
		<div class='condition_field_right'>
		<?php }?>

		<?php if( $multiple == true ) : ?>
		<select multiple name="<?php echo $slug . '[]'; ?>" class="<?php echo ( $slug == 'condition_client_name' || $slug == 'condition_client_email' ) ? 'jq-select2' :  $slug; ?>" >
		<?php else : ?>
		<select name="<?php echo $slug; ?>" 
			<?php 
				if( $slug == 'condition_client_name' ){
					echo 'id="condition_client_name"';
				}elseif( $slug == 'condition_client_email' ){
					echo 'id="condition_client_email"';
				} ?>
			class="<?php echo ( $slug == 'condition_client_name' || $slug == 'condition_client_email' ) ? 'js-example-basic-single' :  $slug; ?>">
		<?php endif;
			/**
			 * For condition_client_name and condition_client_name
			 * fetch the data from WP_Users
			*/
			if( $slug === 'condition_client_name' ){
				
				if( isset( $condition_field['value'] ) &&  ! empty( $condition_field['value'] ) ){
					/**
					 * Get data from WP_Users
					*/
					foreach ($condition_field['value'] as $key => $client_id) {
						$user = get_user_by( 'ID', $client_id );
						if( ! empty ( $user ) ){
							$get_info = $user->first_name . ' ' . $user->last_name;
							if( ! empty ( $user->first_name ) && ! empty ( $user->last_name ) ){
								echo '<option selected value="' . $user->ID . '">' . $get_info . '</option>';
							}else{
								echo '<option selected value="' . $user->ID . '">' . $user->user_nicename . '</option>';
							}
						}	
					}
				}
			}elseif( $slug == 'condition_client_email' ){
				/**
				 * Get data from WP_Users
				*/
				if( isset( $condition_field['value'] ) && ! empty( $condition_field['value'] ) ){
					/**
					 * Get data from WP_Users
					*/
					foreach ($condition_field['value'] as $key => $client_id ) {
						$user = get_user_by( 'ID', $client_id );
						if( ! empty ( $user ) ){
							echo '<option selected value="' . $user->ID . '">' . $user->user_email . '</option>';
						}
					}
				}
			}elseif( $slug == 'condition_agent_name' ){
				/**
				 * Get data from WP_Users
				*/
				if( isset( $condition_field['value'] ) && ! empty( $condition_field['value'] ) ){
					/**
					 * Get data from WP_Users
					*/
					foreach ($condition_field['value'] as $key => $agent_id ) {
						$user = get_user_by( 'ID', $agent_id );
						if( ! empty ( $user ) ){
							$get_info = $user->first_name . ' ' . $user->last_name;
							if( ! empty ( $user->first_name ) && ! empty ( $user->last_name ) ){
								echo '<option selected value="' . $user->ID . '">' . $get_info . '</option>';
							}else{
								echo '<option selected value="' . $user->ID . '">' . $user->user_nicename . '</option>';
							}
						}
					}
				}
			}else{
				$selected_field_type = '';
				foreach( $options as $id => $option ) :
					$as_field_type = isset( $options_field_type[$id] )? $options_field_type[$id]: '';
					if( ! empty( $condition_field['value'] ) ) :
						// Check if multi select and add the selected options
						if( is_array( $condition_field['value'] ) && in_array( $id, $condition_field['value'] ) ) : 
							$selected_field_type = $as_field_type;
							?>
							<option  selected value="<?php echo $id; ?>" data-field_type="<?php echo $as_field_type; ?>"><?php echo $option ?></option>
						<?php
						// Check if not a multi select and add the selected option
						elseif( $condition_field['value'] == $id ) : 
							$selected_field_type = $as_field_type;
							?>
							<option selected data-field_type="<?php echo $as_field_type; ?>" value="<?php echo $id; ?>"><?php echo $option ?></option>
						<?php else : ?>
							<option value="<?php echo $id; ?>" data-field_type="<?php echo $as_field_type; ?>" ><?php echo $option ?></option>
						<?php endif; ?>
					<?php else : ?>
						<option value="<?php echo $id; ?>" data-field_type="<?php echo $as_field_type; ?>" ><?php echo $option ?></option>
					<?php endif;
				endforeach; 
			}
		?>
		</select>
	<?php if( isset( $condition_extra_operators ) && !empty( $condition_extra_operators )){?>

		<select name="<?php echo $slug . '_extra_operator'; ?>" class="condition-extra-operators <?php echo $slug . '_extra_operator'; ?>">
			<?php foreach( $condition_extra_operators as $operator_extra_slug => $operator_extra_value) : ?>
				<option value="<?php echo $operator_extra_slug; ?>"  <?php  if(isset($condition_field[ 'extra_operator' ])): selected( $condition_field[ 'extra_operator' ], $operator_extra_slug ); endif; ?>><?php echo $operator_extra_value; ?></option>
			<?php endforeach; ?>
		</select>
	<?php } ?>

	<?php 

	if( isset( $condition_regex_operators ) && !empty( $condition_regex_operators )){?>
		<select name="<?php echo $slug . '_regex'; ?>" class="condition-regex <?php echo $slug . '_regex'; ?>">
			<?php foreach( $condition_regex_operators as $operator_regex_slug => $operator_regex_value) : 
				if( 'date-field' == $selected_field_type ){
					if( '>'== $operator_regex_slug || '<' == $operator_regex_slug || 'equals' == $operator_regex_slug ){ ?>
					<option value="<?php echo $operator_regex_slug; ?>"  <?php  if(isset($condition_field[ 'regex' ])): selected( $condition_field[ 'regex' ], $operator_regex_slug );  endif; ?> ><?php echo $operator_regex_value; ?></option>
					<?php 
					} else{?>
						<option value="<?php echo $operator_regex_slug; ?>"  <?php  if(isset($condition_field[ 'regex' ])): selected( $condition_field[ 'regex' ], $operator_regex_slug );  endif; ?> disabled ><?php echo $operator_regex_value; ?></option>
					<?php 
					}
				} else{
					if( '>'== $operator_regex_slug || '<' == $operator_regex_slug ){ ?>
					<option value="<?php echo $operator_regex_slug; ?>"  <?php  if(isset($condition_field[ 'regex' ])): selected( $condition_field[ 'regex' ], $operator_regex_slug );  endif; ?> disabled><?php echo $operator_regex_value; ?></option>
					<?php 
					} else{?>
						<option value="<?php echo $operator_regex_slug; ?>"  <?php  if(isset($condition_field[ 'regex' ])): selected( $condition_field[ 'regex' ], $operator_regex_slug );  endif; ?> ><?php echo $operator_regex_value; ?></option>
					<?php 
					}
				}
				?>
			<?php endforeach; ?>
		</select>
	<?php } ?>

	<?php if( isset( $extra_value_field ) && !empty( $extra_value_field )){ 
		foreach ($extra_value_field as $extra_value_field_key => $extra_value_field_value) {
			if(isset($extra_value_field_value['type']) && !empty($extra_value_field_value['type']) && 'input' === $extra_value_field_value['type']){?>
			<input class="<?php echo $slug . '_value'; ?>" name="<?php echo $slug . '_value'; ?>"  value="<?php if(isset($condition_field['value_txt'])){ echo $condition_field['value_txt']; }?>"  id="<?php echo $slug; ?>  type="<?php echo $extra_value_field_value['type']?>" >
			<?php }
			if(isset($extra_value_field_value['type']) && !empty($extra_value_field_value['type']) && 'button' === $extra_value_field_value['type']){?>
				 <button type="button" class="<?php echo $extra_value_field_value['name']?>">+</button>
			<?php }

			if(isset($extra_value_field_value['type']) && !empty($extra_value_field_value['type']) && 'select' === $extra_value_field_value['type']){
				if( isset( $extra_value_field_value['options'] ) && !empty( $extra_value_field_value['options'] ) ){
					echo "<select name='".$extra_value_field_value['name']."'>";
					foreach ( $extra_value_field_value['options'] as $key => $value ) {
						echo "<option value='".$key."'>".$value."</option>";
					}
					echo "</select>";
				}
			}	
			do_action('add_extra_fields_condition', $extra_value_field_value );
		}
	}
	if( 'condition' === $field_selction_type){?>
	</div> 
	<?php }?>
</div>