<div class="condition">

	<label for="<?php echo $slug; ?>"><?php echo $name; ?></label>

	<?php if( ! empty( $condition_field['value'] ) ) : ?>

		<input checked name="<?php echo $slug; ?>" type="<?php echo $type ?>">

	<?php else : ?>

		<input name="<?php echo $slug; ?>" type="<?php echo $type ?>">

	<?php endif;

	if( ! empty( $operators ) ) : ?>

	<select name="<?php echo $slug . '_operator'; ?>" class="condition-operators">

		<?php
		foreach( $operators as $operator_slug => $operator) :
			if( ! empty( $condition_field ) && ! empty( $condition_field['operator'] && $condition_field['operator'] == $operator_slug ) ) :
		?>

			<option selected value="<?php echo $operator_slug; ?>"><?php echo $operator; ?></option>

		<?php else : ?>

			<option value="<?php echo $operator_slug; ?>"><?php echo $operator; ?></option>

		<?php endif;
		endforeach; ?>

	</select>

	<?php endif; ?>

</div>
