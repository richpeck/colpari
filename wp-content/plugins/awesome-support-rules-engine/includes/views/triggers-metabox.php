<section class="triggers-wrapper">
<?php
if( ! empty ( $triggers ) && ! empty( $operators ) && is_array( $post_custom ) ) :
	foreach( $triggers as $slug => $trigger ) :
		$cb_slug = $slug . '-cb';
		$checked = isset( $post_custom[$cb_slug][0] ) ? $post_custom[$cb_slug][0] : '';

		?>
			<div class="trigger">
				<label for="<?php echo $slug; ?>"><?php echo $trigger; ?></label>
				<input type="checkbox" name="<?php echo $cb_slug; ?>" class="trigger-operators" <?php checked( $checked, "on" ); ?>/>
			</div>
		<?php

	endforeach;

	// add-in the cron option values here...
	$cron_intervals = array(
				'every1min'    => array(
					'interval' => 1 * 60,
					'display'  => __( 'Every 1 Minute (use for testing only!)', 'as-rules-engine' ),
				),
				'every5min'    => array(
					'interval' => 5 * 60,
					'display'  => __( 'Every 5 Minutes', 'as-rules-engine' ),
				),
				'every10min'   => array(
					'interval' => 10 * 60,
					'display'  => __( 'Every 10 Minutes', 'as-rules-engine' ),
				),
				'every20min'   => array(
					'interval' => 20 * 60,
					'display'  => __( 'Every 20 Minutes', 'as-rules-engine' ),
				),
				'every30min'   => array(
					'interval' => 30 * 60,
					'display'  => __( 'Every 30 Minutes', 'as-rules-engine' ),
				),
				'hourly'       => array(
					'interval' => 3600,
					'display'  => __( 'Once Hourly', 'as-rules-engine' ),
				),
				'every2ndhour' => array(
					'interval' => 2 * 3600,
					'display'  => __( 'Every 2nd Hour', 'as-rules-engine' ),
				),
				'every4thhour' => array(
					'interval' => 4 * 3600,
					'display'  => __( 'Every 4th Hour', 'as-rules-engine' ),
				),
				'every6thhour' => array(
					'interval' => 6 * 3600,
					'display'  => __( 'Every 6th Hour', 'as-rules-engine' ),
				),
				'twicedaily'   => array(
					'interval' => 12 * 3600,
					'display'  => __( 'Twice Daily', 'as-rules-engine' ),
				),
				'daily'        => array(
					'interval' => 1 * 86400,
					'display'  => __( 'Once Daily', 'as-rules-engine' ),
				),
			);

	$rb_slug = AS_RE_TRIGGER_META_PREFIX . "cron_intervals-rb";
	$checked = isset( $post_custom[$rb_slug][0] ) ? $post_custom[$rb_slug][0] : '';

	foreach( $cron_intervals as $key => $interval ) :
    $rb_display = $interval['display'];
		?>
			<div class="trigger trigger-rb">
				<label for="<?php echo $rb_slug; ?>"><?php echo $rb_display; ?></label>
        <?php
        if($checked == $key) {
        ?>
				  <input type="radio" name="<?php echo AS_RE_TRIGGER_META_PREFIX; ?>cron_intervals-rb" id="<?php echo $rb_slug; ?>" class="trigger-operators" value="<?php echo $key; ?>" checked />
        <?php
        } else {
        ?>
          <input type="radio" name="<?php echo AS_RE_TRIGGER_META_PREFIX; ?>cron_intervals-rb" id="<?php echo $rb_slug; ?>" class="trigger-operators" value="<?php echo $key; ?>"/>
        <?php
        }
        ?>
			</div>
		<?php
	endforeach;

endif; ?>
</section>
