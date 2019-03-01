<?php
class AS_Filepicker extends WPAS_File_Upload {
	
	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Filepicker API Key.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $api_key = '';

	/**
	 * Filepicker API Secret.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $api_secret = '';

	public function __construct() {

		/**
		 * Get API key and secret
		 */
		$this->api_key    = wpas_get_option( 'filepicker_api_key', '' );
		$this->api_secret = wpas_get_option( 'filepicker_api_secret', '' );

		if ( empty( $this->api_key ) ) {

			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'api_key_warning' ), 10, 0 );
			}

			return false;
		}

		$wp_file_upload = WPAS_File_Upload::get_instance();

		/**
		 * First of all we remove the WordPress uploader features.
		 */
		remove_action( 'wpas_open_ticket_after',                    array( $wp_file_upload, 'new_ticket_attachment' ),        10 );
		remove_action( 'wpas_add_reply_public_after',               array( $wp_file_upload, 'new_reply_attachment' ),         10 );
		remove_action( 'wpas_submission_form_inside_before_submit', array( $wp_file_upload, 'upload_field' ) );
		remove_action( 'wpas_ticket_details_reply_textarea_after',  array( $wp_file_upload, 'upload_field' ) );
		remove_action( 'wpas_add_reply_admin_after',                array( $wp_file_upload, 'new_reply_backend_attachment' ), 10 );
		remove_action( 'post_edit_form_tag',                        array( $wp_file_upload, 'add_form_enctype' ),             10 );
		remove_action( 'before_delete_post',                        array( $wp_file_upload, 'delete_attachments' ),           10 );
		remove_filter( 'wpas_admin_tabs_after_reply_wysiwyg',	    array( $wp_file_upload, 'upload_field_add_tab' ),	      11 );
		
		/**
		 * Now we add the Filepicker actions, starting with all the Ajax hooks
		 */
		add_action( 'wp_ajax_filepicker_settings',               array( $this, 'get_settings_ajax' ),    10, 0 );
		add_action( 'wp_ajax_nopriv_filepicker_settings',        array( $this, 'get_settings_ajax' ),    10, 0 );
		add_action( 'wp_ajax_filepicker_get_secured_url',        array( $this, 'get_secured_url_ajax' ), 10, 0 ); 
		add_action( 'wp_ajax_nopriv_filepicker_get_secured_url', array( $this, 'get_secured_url_ajax' ), 10, 0 ); 

		add_filter( 'wpas_wordpress_file_upload_disabled', '__return_true',                       10, 1 );
		add_filter( 'wp_get_attachment_url',               array( $this, 'get_file_remote_url' ), 10, 2 );

		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			add_action( 'wp_print_scripts',                          array( $this, 'load_scripts' ), 10, 0 );
			add_action( 'wpas_submission_form_inside_before_submit', array( $this, 'upload_field' ), 10, 0 );
			add_action( 'wpas_ticket_details_reply_textarea_after',  array( $this, 'upload_field' ), 10, 0 );
			add_filter( 'wpas_admin_tabs_after_reply_wysiwyg',			array( $this, 'add_tab' ) , 10, 1 ); // Add attachments tab
			add_filter( 'wpas_admin_tabs_after_reply_wysiwyg_filepicker_content',	array( $this, 'filepicker_tab_content' ) , 11, 1 ); // Add attachments tab content
			
			/**
			 * This is where we save the attachments
			 */
			add_action( 'wpas_open_ticket_after',      array( $this, 'fp_new_ticket_attachment' ),        10, 2 );
			add_action( 'wpas_add_reply_public_after', array( $this, 'fp_new_reply_attachment' ),         10, 2 );
			add_action( 'wpas_add_reply_admin_after',  array( $this, 'fp_new_reply_backend_attachment' ), 10, 2 );

			/**
			 * Delete a Filepicker file
			 */
			// add_action( 'delete_attachment', array( $this, 'delete_attachment' ), 10, 1 ); TO BE FINALZED
		}

		/**
		 * Display the attachments
		 */
		add_action( 'wpas_attachment_display_filepicker', array( $this, 'show_attachment' ), 10, 4 );

	}

	/**
	 * Add attachments tab
	 * 
	 * @param array $tabs
	 * 
	 * @return array
	 */
	public function add_tab( $tabs ) {
		$tabs['filepicker'] = __( 'Attachments', 'as-filepicker' );
		return $tabs;
	}

	/**
	 * Return content attachments tab
	 * 
	 * @return string
	 */
	public function filepicker_tab_content( $content ) {
		ob_start();
		$this->upload_field();
		return ob_get_clean();
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
	 * Add a link to the settings page.
	 *
	 * @since  0.1.0
	 * @param  array $links Plugin links
	 * @return array        Links with the settings
	 */
	public static function settings_page_link( $links ) {

		$link    = add_query_arg( array( 'post_type' => 'ticket', 'page' => 'settings', 'tab' => 'file_upload' ), admin_url( 'edit.php' ) );
		$links[] = "<a href='$link'>" . __( 'Settings', 'as-filepicker' ) . "</a>";

		return $links;

	}

	/**
	 * Display a warning if the API key is not filled-in.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function api_key_warning() {

		if ( empty( $this->api_key ) ) { ?>

			<div class="error">
				<p><?php printf( __( 'You have enabled Filepicker for uploading attachments but you didn&#39;t specify your API key. Please <a href="%s">fill-in your API key</a> or disable the add-on.', 'as-filepicker' ), wpas_get_settings_page_url( 'file_upload' ) ); ?></p>
			</div>

		<?php }

	}

	/**
	 * Load the plugin scripts.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function load_scripts() {

		global $post;

		$is_submission = false;
		$post_type     = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );

		if ( function_exists( 'wpas_get_submission_pages' ) ) {

			$submission_pages = wpas_get_submission_pages();

			if ( isset( $post ) && in_array( $post->ID, $submission_pages ) ) {
				$is_submission = true;
			}

		} else {

			$submission = (int) wpas_get_option( 'ticket_submit' );

			if ( $submission === $post->ID ) {
				$is_submission = true;
			}

		}

		/**
		 * On the front-end we only want the scripts to load
		 * on the submission page or on a ticket details page.
		 */
		if ( ! is_admin() ) {
			if ( isset( $post ) && 'ticket' !== $post->post_type && ! $is_submission ) {
				return;
			}
		}

		/**
		 * In the admin we only want the scripts to load on the ticket creation screen
		 * or on the ticket edit screen.
		 */
		if ( is_admin() ) {

			if ( ! isset( $post ) && empty( $post_type ) ) {
				return;
			}

			if ( isset( $post ) && 'ticket' !== $post->post_type ) {
				return;
			}

			if ( ! empty( $post_type ) && 'ticket' !== $post_type ) {
				return;
			}

			wp_enqueue_style( 'wpas-filepicker', AS_FP_URL . 'assets/css/as-filepicker.css', null, AS_FP_VERSION, 'screen' );

		}

		wp_enqueue_script( 'wpas-filepicker-api', '//api.filestackapi.com/filestack.js', null, AS_FP_VERSION, true );
		wp_enqueue_script( 'wpas-filepicker', AS_FP_URL . 'assets/js/as-filepicker.js', array( 'jquery', 'wpas-filepicker-api' ), AS_FP_VERSION, true );

	}

	/**
	 * Add dots to a file extension.
	 *
	 * @since  0.1.0
	 * @param  string $extension File extension
	 * @return string            Dotted file extension
	 */
	protected function dot_extension( $extension ) {
		return ".$extension";
	}

	/**
	 * Get file size in Kb
	 *
	 * @since 0.1.5
	 * @return int
	 */
	protected function get_file_size_bytes() {
		
		$size = (int) wpas_get_option( 'filesize_max', 2 );

		return $size * 1024 * 1024;
	}

	/**
	 * Get the Filepicker settings.
	 *
	 * @since  0.1.0
	 * @return array  Settings
	 */
	public function get_settings() {

		$extensions = array_map( array( $this, 'dot_extension' ), explode( ',', $this->get_allowed_filetypes() ) );

		$settings = array(
			'extensions' => $extensions,
			'max_size'   => $this->get_file_size_bytes(), // We need the size in Kb
			'max_files'  => intval( wpas_get_option( 'attachments_max', 1 ) ),
			'services'   => maybe_unserialize( wpas_get_option( 'filepicker_services', array() ) ),
			'debug'      => defined( 'WP_DEBUG' ) ? WP_DEBUG : false,
			'api_key'    => $this->api_key,
		);

		if ( ! empty( $this->api_secret ) ) {
			$settings['policy']    = $this->generate_policy( 'upload' );
			$settings['signature'] = $this->sign_policy( $settings['policy'] );
		}

		return $settings;

	}

	/**
	 * Echo the settings for the Ajax call.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function get_settings_ajax() {
		echo json_encode( $this->get_settings() );
		die();
	}

	/**
	 * Get a secured URL.
	 *
	 * Takes a Filepicker file URL and returns a URL
	 * with a policy and signature.
	 *
	 * @since  0.1.0
	 * @param  string $url         URL ot secure
	 * @param  string $policy_type Type of policy required
	 * @return string              Secured URL
	 */
	public function get_secured_url( $url, $policy_type = 'read' ) {

		$found = false;

		foreach ( $this->get_filestack_urls() as $furl ) {
			if ( false !== strpos( $url, $furl ) ) {
				$found = true;
				break;
			}
		}

		if ( false === $found ) {
			return '';
		}

		if ( empty( $this->api_secret ) ) {
			return '';
		}

		if ( empty( $policy_type ) ) {
			$policy_type = 'read';
		}

		$handle    = $this->get_attachment_handle( $url );
		$policy    = $this->generate_policy( $policy_type, $handle );
		$signature = $this->sign_policy( $policy );
		$url       = add_query_arg( array( 'signature' => $signature, 'policy' => $policy ), $url );

		return $url;

	}

	/**
	 * Ajax wrapper for get_secured_url()
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function get_secured_url_ajax() {

		$url  = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );
		$type = filter_input( INPUT_POST, 'policy_type', FILTER_SANITIZE_STRING );

		echo $this->get_secured_url( $url, $type );
		die();

	}

	/**
	 * Generate a Filepicker policy.
	 * 
	 * @param  string $type   Type of policy required
	 * @param  string $handle File handle
	 * @return string         Policy
	 */
	protected function generate_policy( $type = 'read', $handle = '' ) {

		if ( ! in_array( $type, array( 'upload', 'read', 'delete' ) ) ) {
			$type = 'read';
		}

		$policy = array(
			'expiry'  => apply_filters( 'wpas_filepicker_policy_expiry_default', time() + 3600 ), // Valid for 1 hour by default
			'handle'  => $handle,
			'call'    => array(),
			'maxsize' => 0,
		);

		switch ( $type ) {

			case 'read':
				$policy['expiry'] = apply_filters( 'wpas_filepicker_policy_expiry_read', time() + 3600 );
				$policy['call'][] = 'pick';
				$policy['call'][] = 'read';
				break;

			case 'upload':

				if ( $this->can_attach_files() ) {
					$policy['expiry']  = apply_filters( 'wpas_filepicker_policy_expiry_upload', time() + 1800 );
					$policy['maxsize'] = wpas_get_option( 'filesize_max', 2 );
					$policy['call'][]  = 'pick';
					$policy['call'][]  = 'read';
					$policy['call'][]  = 'stat';
					$policy['call'][]  = 'write';
					$policy['call'][]  = 'writeUrl';
					$policy['call'][]  = 'store';
					$policy['call'][]  = 'convert';
				}

				break;

			case 'delete':
				$policy['expiry'] = apply_filters( 'wpas_filepicker_policy_expiry_delete', time() + 600 );
				$policy['call'][] = 'remove';
				$policy['call'][] = 'pick';
				$policy['call'][] = 'read';
				$policy['call'][] = 'stat';
				$policy['call'][] = 'write';
				$policy['call'][] = 'writeUrl';
				$policy['call'][] = 'store';
				$policy['call'][] = 'convert';
				break;

		}

		$policy = base64_encode( json_encode( $policy ) );

		return $policy;

	}

	/**
	 * Sign a policy.
	 *
	 * @since  0.1.0
	 * @param  string $policy Policy ot sign
	 * @return string         Signature
	 */
	protected function sign_policy( $policy ) {
		return hash_hmac( 'sha256', $policy, strtoupper( trim( $this->api_secret ) ) );
	}

	/**
	 * Upload button.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function upload_field() {

		$filetypes = $this->get_allowed_filetypes();
		$filetypes = explode( ',', $filetypes );
		foreach ( $filetypes as $key => $type ) { $filetypes[$key] = "<code>.$type</code>"; }
		$filetypes = implode( ', ', $filetypes ); ?>

		<div class="wpas-form-group wpas-attachment-container">
			<label for="wpas-file-upload"><?php _e( 'Attachments', 'wpas' ); ?></label>
			<br>
			<input type="hidden" id="wpas-filepicker-data" name="wpas-filepicker-data">
			<a id="wpas-filepicker-upload" href="javascript:void(0);" data-maxfiles="<?php _e( 'This site only allows you to upload 2 files at a time', 'as-filepicker' ); ?>"><?php _e( 'Click to upload file(s)', 'as-filepicker' ); ?></a>
			<p class="wpas-help-block"><?php printf( __( ' You can upload up to %s files of the following types: %s', 'as-filepicker' ), wpas_get_option( 'attachments_max' ), $filetypes ); ?></p>
		</div>

	<?php }

	/**
	 * Process the upload.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	protected function save_attachments() {

		$attachments = json_decode( urldecode( filter_input( INPUT_POST, 'wpas-filepicker-data', FILTER_SANITIZE_STRING ) ) );

		if ( ! empty( $attachments ) ) {

			$admin_path = wpas_get_admin_path_from_url();
			require_once( $admin_path . 'includes/image.php' );

			foreach ( $attachments as $attachment ) {

				$filename = preg_replace( '/\.[^.]+$/', '', $attachment->filename );

				$file = array(
					'guid'           => $attachment->url, 
					'post_mime_type' => $attachment->mimetype,
					'post_title'     => $filename,
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				$resource_id = wp_insert_attachment( $file, $attachment->url, $this->post_id );
				$attach_data = wp_generate_attachment_metadata( $resource_id, $filename );

				/**
				 * Add the upload source to the metadata
				 */
				$attach_data['wpas_upload_source'] = 'filepicker';
				$attach_data['file_size']          = $attachment->size;
				$attach_data['file_name']          = $attachment->filename;

				wp_update_attachment_metadata( $resource_id, $attach_data );

			}

		}

	}

	/**
	 * Process uploads when user creates a new ticket.
	 *
	 * @since  0.1.0
	 * @param  integer $ticket_id ID of the ticket that was just opened
	 * @param  array   $data      Ticket data
	 * @return void
	 */
	public function fp_new_ticket_attachment( $ticket_id, $data ) {
		if ( isset( $_POST['wpas-filepicker-data'] ) ) {
			$this->post_id = intval( $ticket_id );
			$this->save_attachments();
		}
	}

	/**
	 * Process upload on new reply creation.
	 *
	 * @since  3.0.0
	 *
	 * @param  integer $reply_id New reply ID
	 * @param  array   $data     The newly created reply's data
	 *
	 * @return void
	 */
	public function fp_new_reply_attachment( $reply_id, $data ) {

		if ( isset( $_POST['wpas-filepicker-data'] ) ) {

			$this->post_id   = intval( $reply_id );
			$this->parent_id = filter_input( INPUT_POST, 'ticket_id', FILTER_SANITIZE_NUMBER_INT );

			$this->save_attachments();

		}
	}

	/**
	 * Process upload on new reply creation.
	 *
	 * @since  0.1.0
	 *
	 * @param  integer $reply_id New reply ID
	 *
	 * @return void
	 */
	public function fp_new_reply_backend_attachment( $reply_id ) {

		$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

		/* Are we in the right post type? */
		if ( empty( $post_type ) || 'ticket' !== $post_type ) {
			return;
		}

		if ( ! $this->can_attach_files() ) {
			return;
		}

		$this->post_id   = intval( $reply_id );
		$this->parent_id = filter_input( INPUT_POST, 'wpas_post_parent', FILTER_SANITIZE_NUMBER_INT );

		$this->save_attachments();

	}

	/**
	 * Delete a Filepicker attachment.
	 *
	 * @since  0.1.0
	 *
	 * @param  integer $post_id Attachment ID
	 *
	 * @return void|object Server response if attachment is deleted from Filepicker
	 */
	public function delete_attachment( $post_id ) {

		$metadata = wp_get_attachment_metadata( $post_id );

		if ( isset( $metadata['wpas_upload_source'] ) && 'filepicker' === $metadata['wpas_upload_source'] ) {

			$url        = get_post_meta( '_wp_attached_file', $post_id, true );
			$handle     = $this->get_attachment_handle( $url );
			$query_args = array( 'api_key' => $this->api_key );

			if ( ! empty( $this->api_secret ) ) {
				$query_args['policy']    = $this->generate_policy( 'delete' );
				$query_args['signature'] = $this->sign_policy( $query_args['policy'] );
			}

			$endpoint = add_query_arg( $query_args, $this->api_key, $url );

			$args = array(
				'method'      => 'DELETE',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
			);

			$response = wp_remote_request( $endpoint, $args );

			return $response;

		}

	}

	/**
	 * Convert wrong local URL.
	 *
	 * WordPress considers all medias to be locally stored.
	 * We hook into the function that gets a media URL and
	 * if the URL relates to Filepicker we remove the local URL part
	 * to leave only the filepicker.io domain.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $url Attachment URL
	 *
	 * @return string            Correct URL for the attachment
	 */
	public function get_file_remote_url( $url ) {

		foreach ( $this->get_filestack_urls() as $furl ) {

			if ( false !== $first = strpos( $url, $furl ) ) {
				$url = $this->get_filestack_url( substr( $url, $first ) );
			}

		}

		return $url;

	}

	/**
	 * Retrieve file handle from URL.
	 *
	 * @since  0.1.0
	 * @param  string $url Filepicker file URL
	 * @return string      File handle
	 */
	protected function get_attachment_handle( $url ) {

		/**
		 * Get the file handle.
		 */
		$handle = explode( '/', $url );
		$handle = $handle[ count( $handle ) - 1 ];

		return $handle;

	}

	/**
	 * Show the Filepicker attachment.
	 *
	 * @since  0.1.0
	 *
	 * @param  integer $attachment_id Attachment ID
	 * @param  array   $attachment    Attachment data
	 * @param  array   $metadata      Attachment metadata
	 * @param  integer $post_id       Parent post ID
	 *
	 * @return void
	 */
	public function show_attachment( $attachment_id, $attachment, $metadata, $post_id ) {

		$name = isset( $metadata['file_name'] ) ? $metadata['file_name'] : $attachment['name'];
		$url  = $this->get_filestack_url( $attachment['url'] );

		printf( '<li><a href="%1$s" target="_blank">%2$s</a> %3$s</li>', $url, $name, $this->human_filesize( $metadata['file_size'] ) );

	}

	/**
	 * Get the full Filestack URL including policy and possibly signature
	 *
	 * @since 0.1.6
	 *
	 * @param string $url         File URL
	 * @param string $policy_type Type of policy requested
	 *
	 * @return string
	 */
	public function get_filestack_url( $url, $policy_type = 'read' ) {

		$handle         = $this->get_attachment_handle( $url );
		$args['policy'] = $this->generate_policy( $policy_type, $handle );

		if ( ! empty( $this->api_secret ) ) {
			$args['signature'] = $this->sign_policy( $args['policy'] );
		}

		$args['policy'] = urlencode( $this->generate_policy( $policy_type, $handle ) ); // We need to return a URL-safe version of the policy, otherwise add_query_arg() may fuck up with stuff like the = signs

		return esc_url( add_query_arg( $args, $url ) );

	}

	/**
	 * Return a list of URLs used by Filestack or Filepicker (for the previous version)
	 *
	 * @since 0.1.6
	 * @return array
	 */
	public function get_filestack_urls() {
		return array(
			'https://www.filepicker.io',
			'https://cdn.filestackcontent.com'
		);
	}

}