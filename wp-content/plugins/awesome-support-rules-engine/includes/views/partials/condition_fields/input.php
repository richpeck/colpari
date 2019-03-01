<div class="condition <?php echo $slug; ?>">
	<?php
		if ( isset( $condition_operators ) && ! empty( $condition_operators ) ) { ?>

				<select name="<?php echo $slug . '_operator'; ?>" class="condition-operators <?php echo $slug . '_operator'; ?>">

					<?php foreach ( $condition_operators as $operator_slug => $operator ) : ?>
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
	<?php }
		if ( isset( $condition_extra_operators ) && ! empty( $condition_extra_operators ) ) { ?>

		<select name="<?php echo $slug . '_extra_operator'; ?>" class="condition-extra-operators <?php echo $slug . '_extra_operator'; ?>">
			<?php foreach ( $condition_extra_operators as $operator_extra_slug => $operator_extra_value ) : ?>
				<option value="<?php echo $operator_extra_slug; ?>"  <?php  if(isset($condition_field[ 'extra_operator' ])): selected( $condition_field[ 'extra_operator' ], $operator_extra_slug ); endif; ?>><?php echo $operator_extra_value; ?></option>
			<?php endforeach; ?>
		</select>
	<?php } ?>

	<?php if( isset( $condition_regex_operators ) && !empty( $condition_regex_operators )){?>
		<select name="<?php echo $slug . '_regex'; ?>" class="condition-regex <?php echo $slug . '_regex'; ?>">
			<?php foreach( $condition_regex_operators as $operator_regex_slug => $operator_regex_value) : ?>
				<option value="<?php echo $operator_regex_slug; ?>"  <?php  if(isset($condition_field[ 'regex' ])): selected( $condition_field[ 'regex' ], $operator_regex_slug );  endif; ?> ><?php echo $operator_regex_value; ?></option>
			<?php endforeach; ?>
		</select>
	<?php } ?>


	<input class="<?php echo $slug; ?>" name="<?php echo $slug; ?>"  value="<?php if(isset($condition_field['value'])){ echo $condition_field['value']; }?>"  id="<?php echo $slug; ?>"  type="<?php echo $type ?>" <?php echo isset( $min ) ? 'min="' . $min . '"' : '' ; ?> >
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
						if( isset($condition_field[$slug.'_template'])){
							echo "<option value='".$key."' ". selected( $condition_field[$slug.'_template'], $key ).">".$value."</option>";
						}else{
							echo "<option value='".$key."'>".$value."</option>";
						}
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
