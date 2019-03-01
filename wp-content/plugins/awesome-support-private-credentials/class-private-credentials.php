<?php
/*
 * @package   Awesome Support: Private Credentials
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */

// DEFINE our cipher
define( 'AES_256_CBC', 'aes-256-cbc' );

class WPAS_PC {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;
	private          $cryptKey;
	private          $credentials;
	private          $maxCredentials;
	private          $post_id;

	public function __construct() {

		$this->cryptKey       = '';
		$this->credentials    = $this->init_credentials();
		$this->maxCredentials = 5;
		$this->post_id        = false;

		if( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			add_action( 'init', array( $this, 'initialize' ), 40, 0 );
		}
		// ajax run action
		add_action( 'wp_ajax_wpas_pc_action', array( $this, 'ajax_run_action' ) );
		// ajax load view action
		add_action( 'wp_ajax_wpas_pc_load_view', array( $this, 'ajax_load_view' ) );

	}

	/***
	 * Initialize action & filter hooks
	 *
	 * @return bool
	 */
	public function initialize() {

		if( isset( $_GET[ 'post' ] ) && get_post_type( $_GET[ 'post' ] ) !== 'ticket' ) {
			return false;
		}
		else if( ! isset( $_GET[ 'post' ] ) && ! wpas_is_plugin_page() ) {
			//return false;
		}


		if( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			//$post = get_post( $_GET[ 'post' ] );
			//if ( !empty( $post ) && $post->post_type === 'ticket' ) {
			//	if ( 'closed' !== get_post_meta( $post->ID, '_wpas_status', true ) ) {
			add_action( 'add_meta_boxes', array( $this, 'metaboxes' ), 10, 0 );
			add_action( 'load-post-new.php', array( $this, 'metaboxes' ), 10, 0 );
			add_action( 'load-post.php', array( $this, 'metaboxes' ), 10, 0 );
			//	}
			//}

			//$this->init_credentials();
		}

		if( is_admin() ) {

			add_action( 'wpas_after_close_ticket_admin', array( $this, 'ticket_closed' ), 10, 1 );    // Ticket status updated
			add_filter( 'admin_footer', array( $this, 'private_credentials_form' ), 100, 0 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_textdomain' ), 11, 0 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_script' ), 10, 0 );
			add_filter( 'wpas_logs_handles', array( $this, 'register_log_handle' ), 10, 1 );

			/**
			 * Create/Display Credentials Count column
			 */
			add_filter( 'manage_ticket_posts_columns', array( $this, 'set_custom_ticket_columns' ) );
			add_action( 'manage_ticket_posts_custom_column', array( $this, 'get_custom_ticket_column' ), 20, 2 );
			add_filter( 'manage_edit-ticket_sortable_columns', array( $this, 'add_sortable_column' ), 10, 1 );
			add_action( 'pre_get_posts', array( $this, 'set_pre_get_posts_orderby' ), 10, 1 );

		}
		else {

			add_filter( 'wp_footer', array( $this, 'private_credentials_form' ), 100, 0 );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_plugin_textdomain' ), 11, 0 );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_script' ), 10, 0 );
			add_action( 'wpas_after_close_ticket_public', array( $this, 'ticket_closed' ), 10, 1 );    // Ticket status updated
			add_filter( 'wpas_frontend_add_nav_buttons', array( $this, 'wpas_frontend_add_nav_buttons'), 10, 0);
		}


	}


	/**
	 * Load the plugin text domain for translation.
	 *
	 * With the introduction of plugins language packs in WordPress
	 * loading the textdomain is slightly more complex.
	 *
	 * We now have 3 steps:
	 *
	 * 1. Check for the language pack in the WordPress core directory
	 * 2. Check for the translation file in the plugin's language directory
	 * 3. Fallback to loading the textdomain the classic way
	 *
	 * @since    1.0.0
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function load_plugin_textdomain() {

		$lang_dir       = WPAS_PC_ROOT . 'languages/';
		$lang_path      = WPAS_PC_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'wpas-pc' );
		$mofile         = "$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/awesome-support-private-credentials/' . $mofile;

		// Look for the GlotPress language pack first of all
		if( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'wpas-pc', $glotpress_file );
		}
        elseif( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'wpas-pc', $lang_path . $mofile );
		}
		else {
			$language = load_plugin_textdomain( 'wpas-pc', false, $lang_dir );
		}

		return $language;

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     3.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Load addon styles and scripts
	 *
	 */
	public function load_script() {

		wp_enqueue_script( 'wpas-pc-script', WPAS_PC_URL . 'assets/js/private-credentials.js', null, WPAS_PC_VERSION, true );
		wp_enqueue_style( 'wpas-pc-css', WPAS_PC_URL . 'assets/css/private-credentials.css', null, WPAS_PC_VERSION, 'all' );

		$this->textdomain();

	}

	/**
	 * Add HTML form outside of WP post form
	 */
	public function private_credentials_form() {

		if( ! is_user_logged_in() || ! wpas_can_view_ticket( $this->post_id ) ) {
			return;
		}

		// load modal default
		$this->loadTemplate( 'modal' );

	}

	/**
	 * Localizations
	 */
	public function textdomain() {

		$vars = array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'adminurl' => admin_url(),
			'nonce'	   => wp_create_nonce( 'nonce_wpas_private_credentials' )
		);

		wp_localize_script( 'wpas-pc-script', 'txtVars', $vars );

	}

	/**
	 * Register to AS core logging
	 *
	 * @param $handles
	 *
	 * @return mixed
	 *
	 */
	public function register_log_handle( $handles ) {

		array_push( $handles, 'wpas-pc' );

		return $handles;

	}

	/**
	 * Register the metaboxes.
	 *
	 * The function below registers all the metaboxes used
	 * in the ticket edit screen.
	 *
	 * @since 3.0.0
	 */
	public function metaboxes() {

		/**
		 * Don't show Private Credentials on closed tickets.
		 */
		add_meta_box(
			"private-credentials-meta-box",
			__( 'Private Credentials', 'wpas-pc' ),
			array(
				$this,
				'metabox_callback',
			),
			"ticket", "side", "high", array( 'template' => 'private-credentials' )
		);

	}

	/**
	 * Metabox callback function.
	 *
	 * The below function is used to call the metaboxes content.
	 * A template name is given to the function. If the template
	 * does exist, the metabox is loaded. If not, nothing happens.
	 *
	 * @param  integer $post Post ID
	 * @param  array   $args Additional arguments passed to the function
	 *
	 * @return void
	 * @since  0.1.0
	 */
	public function metabox_callback( $post, $args ) {

		if( ! is_array( $args ) || ! isset( $args[ 'args' ][ 'template' ] ) ) {
			_e( 'An error occured while registering this metabox. Please contact the support.', 'wpas-pc' );
		}

		$template = $args[ 'args' ][ 'template' ];

		if( ! file_exists( WPAS_PC_PATH . "includes/metaboxes/$template.php" ) ) {
			_e( 'An error occured while loading this metabox. Please contact the support.', 'wpas-pc' );
		}

		/* Include the metabox content */
		include_once( WPAS_PC_PATH . "includes/metaboxes/$template.php" );

	}

	/**
	 * @param      $data
	 * @param null $key
	 *
	 * @return string
	 *
	 */
	public function encrypt( $data, $key = null ) {

		if( ! $key ) {
			$key = $this->cryptKey;
		}

		return UnsafeCrypto::encrypt( $data, $key, true );

	}

	/**
	 * @param      $data
	 * @param null $key
	 *
	 * @return false|string
	 *
	 */
	public function decrypt( $data, $key = null ) {

		if( ! $key ) {
			$key = $this->cryptKey;
		}

		return UnsafeCrypto::decrypt( $data, $key, true );

	}

	/**
	 * Returns encryption key required to encrypt or decrypt credentials.
	 *
	 * If WPAS_PC_ENCRYPTION_KEY_MASTER is specified in wp-config.php then
	 * the randomly generated encryption key is again encrypted using the
	 * disk based master key.
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_encryption_key( $post_id ) {
		// Get saved encryption key if it exists
		if( get_post_meta( $post_id, '_wpas_pc_encryption_key', true ) ) {
			$key = get_post_meta( $post_id, '_wpas_pc_encryption_key', true );

			if( defined( 'WPAS_PC_ENCRYPTION_KEY_MASTER' ) ) {
				$this->cryptKey = $this->decrypt( $key, WPAS_PC_ENCRYPTION_KEY_MASTER );
			}
			else {
				$this->cryptKey = $key;
			}
		}

		return $this->cryptKey;

	}

	/**
	 * Encrypts and stores encryption key stored in postmeta.
	 *
	 * If WPAS_PC_ENCRYPTION_KEY_MASTER is specified in wp-config.php then
	 * the randomly generated encryption key is again encrypted using the
	 * disk based master key.
	 *
	 * @param int $post_id
	 *
	 * @return boolean
	 */
	public function set_encryption_key( $post_id ) {

		// Default to no Master Key
		$encrypted_key = $this->cryptKey;

		// Master Key detected. Encrypt ticket's encryption key
		if( defined( 'WPAS_PC_ENCRYPTION_KEY_MASTER' ) ) {
			$encrypted_key = $this->encrypt( $this->cryptKey, WPAS_PC_ENCRYPTION_KEY_MASTER );
		}

		// If Key exists, it's encrypted with WP-CONFIG salt
		if( ! add_post_meta( $post_id, '_wpas_pc_encryption_key', $encrypted_key, true ) ) {

			// Meta key exists, check if it's changed
			if( $encrypted_key != get_post_meta( $post_id, '_wpas_pc_encryption_key', true ) ) {

				// Update encryption key.
				if( ! update_post_meta( $post_id, '_wpas_pc_encryption_key', $encrypted_key ) ) {
					return false;
				}
			}
		}

		return true;

	}


	/**
	 * Initialize state of private credentials. Allows getting count
	 * of credentials for specified ticket id.
	 *
	 * @since 1.0.4
	 *
	 * @param string|bool $ticket_id
	 *
	 * @return int Private Credentials count for this ticket id
	 *
	 */
	public function init_credentials( $ticket_id = false ) {

		if( ! $ticket_id ) {
			$this->post_id = get_the_ID();
		}
		else {
			$this->post_id = $ticket_id;
		}

		// Get saved encryption key if it exists
		$this->get_encryption_key( $this->post_id );

		$this->credentials = array();

		// Get private credentials if they exist.
		if( get_post_meta( $this->post_id, '_wpas_pc_credentials', true ) ) {
			$this->credentials = get_post_meta( $this->post_id, '_wpas_pc_credentials', true );
		}

		return count( $this->credentials );

	}

	/**
	 * Get credentials from postmeta
	 *
	 * @param      $post_id
	 * @param bool $decrypt
	 *
	 * @return array|mixed
	 */
	public function get_credentials( $post_id, $decrypt = false ) {

		$cred = array();

		// Get saved encryption key if it exists
		if( ! $this->get_encryption_key( $post_id ) ) {
			$this->set_encryption_key( $post_id );
		}

		// Get private credentials if they exist.
		if( get_post_meta( $post_id, '_wpas_pc_credentials', true ) ) {
			$this->credentials = get_post_meta( $post_id, '_wpas_pc_credentials', true );
		}
		else {
			return $cred;
		}

		if( ! $decrypt ) {
			return $this->credentials;
		}

		// else decrypt
		foreach( $this->credentials as $key => $value ) {
			$cred[ $key ][ "system" ]   = $this->decrypt( $value[ "system" ] );
			$cred[ $key ][ "username" ] = $this->decrypt( $value[ "username" ] );
			$cred[ $key ][ "password" ] = $this->decrypt( $value[ "password" ] );
			$cred[ $key ][ "url" ]      = $this->decrypt( $value[ "url" ] );
			$cred[ $key ][ "note" ]     = $this->decrypt( $value[ "note" ] );
		}

		return $cred;

	}

	/**
	 * Remove invalid credentials from array
	 *
	 * @param      $dirty_credentials
	 * @param bool $encrypt
	 *
	 * @return array
	 *
	 */
	public function clean_credentials( $dirty_credentials, $encrypt = false ) {

		$cleaned = array();

		$cnt = 0;
		foreach( $dirty_credentials as $key => $value ) {

			if( ( ! isset( $value[ 'system' ] ) || $value[ 'system' ] == '' ) ||
			    ( ! isset( $value[ 'username' ] ) || $value[ 'username' ] == '' ) ||
			    ( ! isset( $value[ 'password' ] ) || $value[ 'password' ] == '' ) ||
			    ( ! isset( $value[ 'url' ] ) ) || 
			    ( ! isset( $value[ 'note' ] ) )
			) {
				continue;
			}

			$value = array_map( 'stripslashes', $value );

			$value[ 'system' ]   = $encrypt ? $this->encrypt( $value[ 'system' ] ) : $value[ 'system' ];
			$value[ 'username' ] = $encrypt ? $this->encrypt( $value[ 'username' ] ) : $value[ 'username' ];
			$value[ 'password' ] = $encrypt ? $this->encrypt( $value[ 'password' ] ) : $value[ 'password' ];
			$value[ 'url' ]      = $encrypt ? $this->encrypt( $value[ 'url' ] ) : $value[ 'url' ];
			$value[ 'note' ]     = $encrypt ? $this->encrypt( $value[ 'note' ] ) : $value[ 'note' ];

			$cleaned[ $cnt ] = $value;

			$cnt ++;
		}

		return $cleaned;

	}

	/**
	 * Delete credentials data from postmeta
	 *
	 * @param $post_id
	 *
	 */
	public function delete_credentials( $post_id ) {

		delete_post_meta( $post_id, '_wpas_pc_encryption_key' );
		delete_post_meta( $post_id, '_wpas_pc_credentials' );

	}

	/**
	 * Save credentials to postmeta
	 *
	 * @param $post_id
	 * @param $credentials
	 *
	 * @return bool
	 *
	 */
	public function save_credentials( $post_id, $credentials ) {

		// Ensure encryption key is encrypted and stored successfully in postmeta
		if( $this->set_encryption_key( $post_id ) ) {

			// Try to add encrpytion key
			if( ! add_post_meta( $post_id, '_wpas_pc_credentials', $credentials, true ) ) {

				// Already exists, update it
				if( ! update_post_meta( $post_id, '_wpas_pc_credentials', $credentials ) ) {
					return false;
				}
			}

			return true;

		}

		return false;

	}

	/**
	 * Delete credentials on ticket close
	 *
	 * @param $post_id
	 *
	 */
	public function ticket_closed( $post_id ) {

		$this->delete_credentials( $post_id );

	}

	public function wpas_frontend_add_nav_buttons() {

	    global $post_type;

		if( 'ticket' !== $post_type ) {
			return;
		}

		$count = $this->init_credentials();
		
		echo ' <a href="#" class="wpas-btn wpas-btn-default wpas-pc-load" data-post-id="' . $this->post_id . '" data-view="default" title="' . __( 'Private Credentials', 'wpas-pc' ) . '">' . 
			 __( 'Private Credentials', 'wpas-pc' ) . ' (' . $count . ')' . '</a>';
	}

	/**
	 * Tickets List Column: Add Rating column
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function set_custom_ticket_columns( $columns ) {

		$columns[ 'private_credentials_count' ] = __( 'Credentials', 'wpas-pc' );

		return $columns;
	}

	/**
	 * Tickets List Column: Display Private Credentials Count
	 *
	 * @since  0.1.3
	 *
	 * @param string $column
	 *
	 * @param string $ticket_id
	 *
	 * @return void
	 */
	public function get_custom_ticket_column( $column, $ticket_id ) {

		/* If displaying the 'credentials' column. */
		if( 'private_credentials_count' == $column ) {

			/* Get the post meta. */
			$count = $this->init_credentials( $ticket_id );

			/* If there is are credentials, show count. */
			if( is_numeric( $count ) ) {
				printf( __( '<span>%s</span>' ), round( $count, 0 ) );
			} /* If no credentials found, output a default message. */
			else {
				echo __( '—' );
			}

		}

	}

	/**
	 * Add Sortable Column
	 *
	 * @since  1.2.3
	 *
	 * @param $sortable_columns
	 *
	 * @return mixed
	 */
	public function add_sortable_column( $sortable_columns ) {

		$sortable_columns[ 'private_credentials_count' ] = 'private_credentials_count';

		return $sortable_columns;
	}

	/**
	 * Set $orderby
	 *
	 * @since  1.2.3
	 *
	 * @param $query
	 *
	 * @return void
	 */
	public function set_pre_get_posts_orderby( $query ) {

		if( ! is_admin() ) {
			return;
		}

		if( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if( 'private_credentials_count' == $orderby ) {
			$query->set( 'meta_key', '_wpasss_rating' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		$orderby = $query->get( 'orderby' );

		switch ( $orderby ) {

			// If we're ordering by Credentials count
			case 'private_credentials_count':

				$query->set( 'orderby', 'private_credentials_count' );

				break;

		}

	}

	/**
     * Load template
     *
     * @param string $name template name without extension located in include/views directory
     * @param array $vars
     * @param boolean $echo
     * @return string|false
     */
    public function loadTemplate( $name, $vars = [], $echo = true ) {

        if ( file_exists($template = WPAS_PC_PATH . 'includes/views/' . $name . '.php') ) {

            extract( $vars );

            ob_start();

            include $template;

            $content = ob_get_clean();

            if ( ! $echo ) {
                return $content;
            }

            echo $content;

        } else {

            return false;

        }

	}

	/**
     * Get template
     *
     * @param string $name template name without extension located in include/views directory
     * @param array $vars
     * @return string|false
     */
    public function getTemplate( $name, $vars = [] ) {

        return $this->loadTemplate( $name, $vars, false);

    }


	/**
	 * Run action requested via ajax
	 *
	 * @return void
	 */
	public function ajax_run_action() {

		// check nonce
		check_ajax_referer( 'nonce_wpas_private_credentials', 'nonce' );


		switch ( $_POST['trigger'] ) {

			case 'delete_credentials':

				$status  = $this->remove_credential( $_POST['id'], $_POST['key'] );
				$message = ( ! $status ) ? __( 'Error', 'wpas-pc' ) : '';

				wp_send_json( array(
					'status'  => ( $status == true ) ? 1 : 0,
					'view'    => 'default',
					'message' => $message
				) );

				break;

			case 'save_data':

				$message = '';

				parse_str( $_POST['data'], $data );

				// Clean posted credentials
				$credentials  = $this->clean_credentials( array( $data ) );


				if ( empty( $credentials ) ) {

					$message = __( 'Your credentials could not be saved. System, Password and Username are required fields.', 'wpas-pc' );
					$status  = false;

				} else {

					// remove previous credentials if this is update action
					if ( isset ( $data['key'] ) ) {

						$this->remove_credential( $data['post_id'], $data['key'] );

					}

					// old credentials
					$old_credentials = $this->get_credentials( $data['post_id'], true);

					// reset crypt key
					if( isset( $data[ 'reset_key' ] ) ) {
						// crypt key
						$this->cryptKey = sanitize_text_field( $data[ 'crypt_key' ] );
					}

					// merge data
					$dataArray = array_merge( array($data), $old_credentials );

					// Encrypt fields of each posted credential
					$encryptedCredentials = $this->clean_credentials( $dataArray , true );


					$status = $this->save_credentials( $data['post_id'], $encryptedCredentials );

					if ( ! $status ) {
						$message = __( 'Your credentials could not be saved. System, Password and Username are required fields.', 'wpas-pc' );
					}

				}

				// send response
				wp_send_json( array(
					'status'  => ( $status == true ) ? 1 : 0,
					'view'    => 'default',
					'message' => $message
				) );


				break;

		}
	}
	

	/**
	 * Load view requested via ajax
	 *
	 */
	public function ajax_load_view() {

		// check nonce
		check_ajax_referer( 'nonce_wpas_private_credentials', 'nonce' );

		// load template
		$this->loadTemplate( $_POST['template'], array(
			'id'  => $_POST['id'],
			'key' => isset( $_POST['key'] ) ? $_POST['key'] : false
		) );

		exit;

	}


	/**
	 * Remove post credential
	 *
	 * @param int $post_id
	 * @param string $key
	 * @return boolean
	 */
	public function remove_credential($post_id, $key) {

		$credentials = get_post_meta( $post_id, '_wpas_pc_credentials', true );

		if ( $credentials ) {

			foreach ( $credentials as $i => $credential ) {

				// check key
				if ( $key == $i ) {
					unset( $credentials[ $i ] );
				}
			}

			// update data
			update_post_meta($post_id, '_wpas_pc_credentials', $credentials);

			return true;

		}

		return false;

	}

}
