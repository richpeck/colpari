<?php
/**
 * Envato Validation
 *
 * @package   Awesome Support Envato Validation
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

class WPAS_Envato_Validation {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct() {

		$this->username = wpas_get_option( 'envato_username' );
		$this->apikey   = wpas_get_option( 'envato_apikey' );
		$this->multiple = boolval( wpas_get_option( 'support_products', false ) );

		if ( is_admin() ) {

			add_action( 'add_meta_boxes',       array( $this, 'metabox' ),             10, 0 );
			add_action( 'admin_notices',        array( $this, 'check_configuration' ), 10, 0 );
			add_action( 'admin_notices',        array( $this, 'maybe_expired_support' ), 10, 0 );
			add_action( 'admin_notices',        array( $this, 'settings_update_notice' ), 10, 0 );

			if ( true === $this->multiple ) {
				add_action( 'admin_print_scripts',      array( $this, 'load_scripts' ),            10, 0 );
				add_action( 'product_add_form_fields',  array( $this, 'product_metas' ),           10, 2 );
				add_action( 'product_edit_form_fields', array( $this, 'products_meta_edit_page' ), 10, 2 );
				add_action( 'edited_product',           array( $this, 'save_product_meta' ),       10, 2 );  
				add_action( 'create_product',           array( $this, 'save_product_meta' ),       10, 2 );
			}
		}

		if ( !is_admin() ) {
			add_action( 'wpas_open_ticket_after',                     array( $this, 'save_license' ),         10, 2 );
			add_action( 'wpas_submission_form_inside_before_subject', array( $this, 'envato_license_field' ), 10, 0 );
			add_filter( 'wpas_before_submit_new_ticket_checks',       array( $this, 'possibly_abort' ),       10, 1 );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     3.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the scripts
	 *
	 * @return void
	 */
	public function load_scripts() {

		global $pagenow;

		if ( 'edit-tags.php' !== $pagenow ) {
			return;
		}

		add_thickbox();
	}

	/**
	 * Display Envato fields on term details page.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function product_metas() { ?>
		<h3><?php _e( 'Envato Validation', 'as-envato' ); ?></h3>
		<div class="form-field">
			<label for="term_meta[wpas_envato_product_id]"><?php _e( 'License Verification', 'as-envato' ); ?></label>
			<label>
				<input type="checkbox" name="term_meta[wpas_envato_license_verification]" id="term_meta[wpas_envato_license_verification]" value="1"> 
				<?php _e( 'Enable Envato license verification for this product.', 'as-envato' ); ?>
			</label>
		</div>
		<div class="form-field">
			<label for="term_meta[wpas_envato_product_id]"><?php _e( 'Product ID', 'as-envato' ); ?></label>
			<input type="text" name="term_meta[wpas_envato_product_id]" id="term_meta[wpas_envato_product_id]" value="">
			<p class="description"><?php printf( __( 'Enter the product ID used on Envato. <a href="%s" %s>Click here if you don\'t know how to get the product ID</a>.', 'as-envato' ), esc_url( '//www.youtube.com/embed/iUU3FvVQzQ0?TB_iframe=true&height=315&width=560' ), 'class="thickbox" title="' . __( 'Find your Envato purchase code', 'as-envato' ) . '"' ); ?></p>
		</div>
	<?php }

	public function products_meta_edit_page( $term ) {
 
		// put the term ID into a variable
		$t_id = $term->term_id;
	 
		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "taxonomy_$t_id" ); ?>
		<tr class="form-field">
			<td colspan="2"><h3><?php _e( 'Envato Validation', 'as-envato' ); ?></h3></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[wpas_envato_license_verification]"><?php _e( 'Enable License Verification', 'as-envato' ); ?></label></th>
			<td>
				<label>
					<input type="checkbox" name="term_meta[wpas_envato_license_verification]" id="term_meta[wpas_envato_license_verification]" value="1" <?php if ( isset( $term_meta['wpas_envato_license_verification'] ) ): ?>checked="checked"<?php endif; ?>> <?php _e( 'Yes', 'as-envato' ); ?>
				</label>
				<p class="description"><?php _e( 'Enable the Envato license verification for this product.', 'as-envato' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[wpas_envato_product_id]"><?php _e( 'Product ID', 'as-envato' ); ?></label></th>
			<td>
				<input type="text" name="term_meta[wpas_envato_product_id]" id="term_meta[wpas_envato_product_id]" value="<?php echo isset( $term_meta['wpas_envato_product_id'] ) ? esc_attr( $term_meta['wpas_envato_product_id'] ) : ''; ?>">
				<p class="description"><?php printf( __( 'Enter the product ID used on Envato. <a href="%s" %s>Click here if you don\'t know how to get the product ID</a>.', 'as-envato' ), esc_url( '//www.youtube.com/embed/iUU3FvVQzQ0?TB_iframe=true&height=315&width=560' ), 'class="thickbox" title="' . __( 'Find your Envato purchase code', 'as-envato' ) . '"' ); ?></p>
			</td>
		</tr>
	<?php
	}

	public function save_product_meta( $term_id ) {

		if ( isset( $_POST['term_meta'] ) ) {

			$t_id      = $term_id;
			$term_meta = get_option( "taxonomy_$t_id" );
			$cat_keys  = array_keys( $_POST['term_meta'] );

			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}

			if ( !isset( $_POST['term_meta']['wpas_envato_license_verification'] ) ) {
				unset( $term_meta['wpas_envato_license_verification'] );
			}

			update_option( "taxonomy_$t_id", $term_meta );
		}
	}

	public function envato_license_field() {

		$args = array(
			'name' => 'envato_license',
			'args' => array(
				'desc'       => '<a href="#wpasev-get-license" target="_blank" class="wpas-modal-trigger">' . __( 'Click here if you don\'t know how to get your license.', 'as-envato' ) . '</a>',
				'required'   => (bool) wpas_get_option( 'envato_mandatory' ),
				'field_type' => 'text',
				'label'      => __( 'Envato Purchase Code', 'as-envato' )
			)
		);

		/* Backward compatibility for version < 3.2.0 */
		if ( class_exists( 'WPAS_Custom_Fields_Display' ) ) {
			WPAS_Custom_Fields_Display::text( $args );
		} else {
			$field = new WPAS_Custom_Field( 'envato_license', $args );
			echo $field->get_output();
		}

		echo '<div style="display: none;"><div id="wpasev-get-license"><iframe width="640" height="480" src="//www.youtube.com/embed/5RGqrtx8ed4" frameborder="0" allowfullscreen></iframe></div></div>';

	}

	/**
	 * Gives the green light.
	 *
	 * This function is hooked on the "green light"
	 * of the ticket submission process. If false is returned
	 * the entire submission process is aborted.
	 *
	 * @since  0.1.0
	 *
	 * @param  boolean $go Green light
	 *
	 * @return boolean     True if all the checks are passed
	 */
	public function possibly_abort( $go ) {

		if ( false === $go ) {
			return false;
		}

		$license = $_POST['wpas_envato_license'];

		return $this->check_license( $license );
	}

	/**
	 * Checks a product license.
	 *
	 * The function checks weather or not a given product
	 * requires validation and if it does then the license is checked.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $license License code to check
	 * @param  integer $post_id Current post ID
	 *
	 * @return mixed            True when validated, WP_Error otherwise
	 */
	public function check_license( $license = '', $post_id = 0 ) {

		/**
		 * Get the license code if not passed to the function.
		 */
		if ( isset( $_POST['wpas_envato_license'] ) ) {
			$license = sanitize_key( $_POST['wpas_envato_license'] );
		}

		if ( true === $this->multiple ) {

			$term_id = isset( $_POST['wpas_product'] ) ? filter_input( INPUT_POST, 'wpas_product', FILTER_SANITIZE_NUMBER_INT ) : '';

			if ( ! empty( $term_id ) ) {
				$term_meta  = get_option( "taxonomy_$term_id" );
				$to_check   = isset( $term_meta['wpas_envato_license_verification'] ) ? true : false;
				$product_id = isset( $term_meta['wpas_envato_product_id'] ) ? (int) sanitize_key( $term_meta['wpas_envato_product_id'] ) : '';
			}
		}

		/**
		 * Make sure we have a license code to check
		 */
		if ( false === $license || empty( $license ) ) {
			if ( true === boolval( wpas_get_option( 'envato_mandatory' ) ) || isset( $to_check ) && $to_check ) {
				return new WP_Error( 'no_license', __( 'You haven\'t submitted a license code', 'as-envato' ) );
			} else {
				return true;
			}
		}

		$license = trim( sanitize_key( $license ) ); // Sanitize the license code
		$data    = 0 !== $post_id ? get_post_meta( $post_id, '_wpasev_license_data', true ) : $this->ask_envato( $license ); // Get license data

		/**
		 * Check the license validity. If this check is passed
		 * the license is considered valid for the rest of the function.
		 */
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( ! is_array( $data ) || ! isset( $data['item_id'] ) ) {
			return new WP_Error( 'license_failed', __( 'The license you submitted appears to be incorrect', 'as-envato' ) );
		}

		set_transient( 'wpas_envato_license_' . substr( md5( $license ), 0, 6 ), $data, 60*15 );

		if ( true === $this->multiple ) {

			$match = boolval( wpas_get_option( 'envato_match_product' ) );

			if ( true === boolval( wpas_get_option( 'envato_mandatory' ) ) ) {
				if ( true === $match ) {
					/* Compare the product ID from the settings to the one returned by Envato */
					if ( isset( $data['item_id'] ) && $product_id === $data['item_id'] ) {
						return true;
					} else {
						return new WP_Error( 'invalid_product', __( 'The license code you submitted does not match the product you chose', 'as-envato' ) );
					}
				} else {
					/* The license has already been validated so we can return true even if the product IDs don't match */
					return true;
				}
			} else {

				/* Compare the product ID from the settings to the one returned by Envato */
				if ( true === $match && isset( $data['item_id'] ) && isset( $product_id ) && $product_id !== $data['item_id'] ) {
					return new WP_Error( 'invalid_product', __( 'The license code you submitted does not match the product you chose', 'as-envato' ) );
				} else {
					return true;
				}

			}

		} else {

			/* Get the unique product ID from plugin options */
			$product_id = (int) wpas_get_option( 'envato_product_id' );

			/* Compare the product ID from the settings to the one returned by Envato */
			if ( isset( $data['item_id'] ) && $product_id === $data['item_id'] ) {
				return true;
			} else {
				return new WP_Error( 'invalid_product', __( 'The license code you submitted does not match the product you chose', 'as-envato' ) );
			}
		}
	}

	/**
	 * Test a given against the Envato API
	 *
	 * This uses the new Envato API with a personal token.
	 *
	 * @since 0.2.0
	 *
	 * @param string $license License code to check
	 *
	 * @return array|WP_Error
	 */
	public function ask_envato( $license = '' ) {

		global $wp_version;

		$token    = trim( wpas_get_option( 'envato_token', '' ) );
		$code     = trim( $license );
		$url      = esc_url( 'https://api.envato.com/v2/market/author/sale' );
		$endpoint = add_query_arg( 'code', $code, $url );

		$args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_site_url(),
			'headers'     => 'Authorization: Bearer ' . $token,
		);

		$response = wp_remote_request( $endpoint, $args );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$env_err_msg = __( 'We apologize for the inconvenience but the request could not be processed because of an exception from Envato - the response from the Envato API was:', 'as-envato' ) . ' ' . wp_remote_retrieve_response_message( $response ) . ' ' .  wp_remote_retrieve_body( $response );
			return new WP_Error( 'envato_error', $env_err_msg );
		}

		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );

		if ( ! is_object( $body ) ) {
			return new WP_Error( 'envato_response_error', __( 'The Envato response could not be read', 'as-envato' ) );
		}

		if ( isset( $body->error ) ) {
			$error = isset( $body->error_description ) ? sanitize_text_field( $body->error_description ) : sanitize_text_field( $body->description );
			return new WP_Error( 'envato_deny', $error );
		}

		$data = array(
			'item_name'      => sanitize_text_field( $body->item->name ),
			'item_id'        => (int) $body->item->id,
			'item_purchase'  => sanitize_text_field( $body->sold_at ),
			'item_licence'   => sanitize_text_field( $body->license ),
			'item_supported' => sanitize_text_field( $body->supported_until ),
			'item_buyer'     => sanitize_text_field( $body->buyer ),
		);

		return $data;

	}

	/**
	 * Save Envato license.
	 *
	 * Save the license as a post meta after the ticket
	 * has been added.
	 *
	 * @since  0.1.0
	 * @param  integer $post_id ID of the ticket that's just been created
	 * @param  array   $data    Data used for ticket creation
	 * @return mixed            Post meta ID on success, false on failure
	 */
	public function save_license( $post_id, $data ) {
		$license = isset( $_POST['wpas_envato_license'] ) ? trim( sanitize_key( $_POST['wpas_envato_license'] ) ) : '';
		$data    = get_transient( 'wpas_envato_license_' . substr( md5( $license ), 0, 6 ) );

		if ( false === $data ) {
			$data = $this->ask_envato( $license );
		}

		if ( !is_wp_error( $data ) ) {
			add_post_meta( $post_id, '_wpas_envato_license_data', $data, true );
			delete_transient( 'wpas_envato_license_' . substr( md5( $license ), 0, 6 ) );
		}

		return add_post_meta( $post_id, '_wpas_envato_license', $license, true );
	}

	public function metabox() {
		global $post;

		if ( !isset( $post ) ) {
			return;
		}

		add_meta_box( 'wpasev_license_details', __( 'Envato License', 'as-envato' ), array( $this, 'metabox_content' ), 'ticket', 'side', 'low' );
	}

	public function metabox_content() {
		require_once( WPASEV_PATH . 'metabox-detail.php' );
	}

	public function check_configuration() {

		/* Get the product ID */
		$product_id = wpas_get_option( 'envato_product_id', '' );

		if ( false === $this->multiple && empty( $product_id ) ): ?>
			<div class="error top"><p><strong><?php printf( __( 'You haven&#39;t set the prodcut ID for the Envato license validation. <a href="%s">Please do it now</a>.', 'as-envato' ), wpas_get_settings_page_url( 'envato' ) ); ?></strong></p></div>
		<?php endif; ?>
	<?php }

	/**
	 * Display a warning notice on the ticket edit screen is the user is no longer supported
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function maybe_expired_support() {

		if ( ! isset( $_GET['post'] ) ) {
			return;
		}

		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( 'ticket' !== get_post_type( $post_id ) ) {
			return;
		}


		$data = get_post_meta( $post_id, '_wpas_envato_license_data', true );

		if ( $this->is_envato_support_active( $data ) ) {
			return;
		}

		$message = __( 'The user is no longer supported for this purchase', 'as-envato' );

		echo "<div class='error top'><p><strong>$message</strong></p></div>";

	}

	/**
	 * Check if a purchase is still supported by the Envato support package
	 *
	 * @since 0.2.0
	 *
	 * @param array $data License data
	 *
	 * @return bool
	 */
	public function is_envato_support_active( $data ) {

		if ( ! isset( $data['item_supported'] ) || empty( $data['item_supported'] ) ) {
			return false;
		}

		$today = time();
		$until = strtotime( $data['item_supported'] );

		return $until >= $today ? true : false;

	}

	/**
	 * Display notice asking user to update the settings
	 *
	 * This will be removed after version 0.2
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function settings_update_notice() {

		if ( '0.2.0' !== WPASEV_VERSION ) {
			return;
		}

		/**
		 * We only want to display the notice to the site admin.
		 */
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		/**
		 * If the notice has already been dismissed we don't display it again.
		 */
		if ( wpas_is_notice_dismissed( 'wpasev_settings_update' ) ) {
			return;
		}

		$token = wpas_get_option( 'envato_token', '' );

		/**
		 * Do not show the notice if the license key has already been entered.
		 */
		if ( ! empty( $token ) ) {
			return;
		}

		/* Prepare the dismiss URL */
		$args = $_GET;
		$args['wpas-dismiss'] = 'wpasev_settings_update';
		$url = wpas_nonce_url( add_query_arg( $args, '' ) );
		$settings_page = add_query_arg( array(
			'post_type' => 'ticket',
			'page'      => 'settings',
			'tab'       => 'envato'
		), admin_url( 'edit.php' ) ); ?>

		<div class="updated">
			<p><?php printf( __( 'Please <a href="%s">update the Envato Validation addon settings</a> now. If you don\'t, <strong>Envato Validation will break</strong>.', 'as-envato' ), $settings_page ); ?></p>
		</div>

	<?php }

}