<div class="wpas-envato-license-details">
	<?php
	global $post;

	$data = get_post_meta( $post->ID, '_wpas_envato_license_data', true );

	if ( is_array( $data ) ):

		$license = trim( get_post_meta( $post->ID, '_wpas_envato_license', true ) );
		$item               = sprintf( '%s (#%d)', sanitize_text_field( $data['item_name'] ), (int) $data['item_id'] );
		$purchase_timestamp = strtotime( $data['item_purchase'] );
		$purchase           = date( get_option( 'date_format' ), $purchase_timestamp ) . ' ' . date( get_option( 'time_format' ), $purchase_timestamp );
		$supported          = 'N/C';
		$buyer              = '';

		if ( isset( $data['item_buyer'] ) && ! empty( $data['item_buyer'] ) ) {
			$buyer     = sanitize_text_field( $data['item_buyer'] );
			$buyer_url = 'http://themeforest.net/user/' . $buyer;
		}

		if ( isset( $data['item_supported'] ) && ! empty( $data['item_supported'] ) ) {

			$today     = time();
			$until     = strtotime( $data['item_supported'] );
			$still     = $until >= $today ? true : false;
			$supported = date( get_option( 'date_format' ), $until );

			if ( $still ) {
				$supported .= sprintf( ' <em>(%s)</em>', _x( 'Active support', 'Envato item support is still valid', 'as-envato' ) );
			} else {
				$supported .= sprintf( ' <em>(%S)</em>', _x( 'Support expired', 'Envato item support is expired', 'as-envato' ) );
			}

		} ?>

		<ul>
			<li><strong><?php _e( 'Item', 'as-envato' ); ?></strong><br><?php echo $item; ?></li>
			<li><strong><?php _e( 'Purchase Date', 'as-envato' ); ?></strong><br><?php echo $purchase; ?></li>
			<li>
				<strong><?php _e( 'License Type', 'as-envato' ); ?></strong><br><?php echo sanitize_text_field( $data['item_licence'] ); ?>
			</li>
			<?php if ( ! empty( $buyer ) ): ?>
				<li>
				<strong><?php _e( 'Buyer', 'as-envato' ); ?></strong><br><a href="<?php echo $buyer_url ?>" target="_blank"><?php echo $buyer; ?></a>
				</li><?php endif; ?>
			<li><strong><?php _e( 'License Code', 'as-envato' ); ?></strong><br><?php echo $license; ?></li>
			<li><strong><?php _e( 'Supported Until', 'as-envato' ); ?></strong><br><?php echo $supported; ?></li>
		</ul>

	<?php else:
		esc_html_e( 'No license information available.', 'as-envato' );
	endif;
	?>
</div>