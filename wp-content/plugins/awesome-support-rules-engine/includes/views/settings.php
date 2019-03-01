<h1 class="wp-heading-inline"><?php echo $title; ?></h1>
<div class="wrap">
<div class="welcome-panel">
	<div class="options-container">
		<form method="POST">
			<?php wp_nonce_field( 'update_ruleset_settings', 'update_ruleset_settings' ); ?>
			<table class="form-table">
				<tbody>
					<?php $i=0; ?>
					<?php foreach ( $fields as $key => $value ) : ?>
						<?php $class = ($i%2 == 0) ? 'even': 'odd'; ?>
						<?php if ( in_array($key, $headers) ): ?>
							<tr valign="top" class="<?php echo $class; ?> first tf-heading">
								<th scope="row" style="background: #f1f1f1;padding: 15px;" class="first last" colspan="2">
									<h3 style="margin:0;" id="<?php echo $key; ?>"><?php echo $value; ?></h3>
								</th>
							</tr>
						<?php else: ?>
							<tr valign="top" style="border-bottom: 1px solid #ccc;" class="<?php echo $class; ?>">
								<th scope="row" style="padding-left: 15px;" class="first">
									<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
								</th>
								<td>
									
									<select name="<?php echo $key; ?>[]" multiple="" style="height:140px;padding: 5px;border-radius: 5px;">
										<?php foreach ($values as $kval => $name ) {
											$selected = "";
											if( isset($options[$key]) && !empty($options[$key])){
												$selected = ( in_array($kval, $options[$key]) ) ? ' selected':'';

												// set wpas_manager as the default selected item
												if ( count( $options[$key] ) == 0 && $kval == 'wpas_manager' ) {
													$selected = ' selected';
												}
											}

											echo '<option value="'.$kval.'"'.$selected.'>'.$name.' </option>';

										} ?>
									</select>
										
								</td>
							</tr>
						<?php endif; ?>
						<?php $i++; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<?php submit_button(); ?>
			</p>
		</form>

	</div>
</div>
</div>