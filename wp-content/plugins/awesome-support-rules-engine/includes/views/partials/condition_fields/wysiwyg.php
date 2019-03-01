<div class="condition">

	<label for="<?php echo $slug; ?>"><?php echo $name; ?></label>

	<?php if( ! empty( $condition_field['value'] ) ) : ?>

        <?php wp_editor( $condition_field['value'], $slug); ?>


	<?php else : ?>

        <?php wp_editor( '' , $slug); ?>

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
