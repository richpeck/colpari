<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_External_Loader
 */
class NF_FU_External_Loader {

	protected $class_prefix = 'NF_FU_External_Services_';

	static $min_php_version = '5.3.1';

	/**
	 * NF_FU_External_Loader constructor.
	 */
	public function __construct() {
		add_filter( 'ninja_forms_uploads_tabs', array( $this, 'add_menu_tab' ) );

		if ( ! self::is_compatible() ) {
			return;
		}

		require_once dirname( NF_File_Uploads()->plugin_file_path ) . '/vendor/polevaultweb/wp-oauth2/wp-oauth2.php';
		require_once dirname( NF_File_Uploads()->plugin_file_path ) . '/vendor/polevaultweb/wp-oauth2/access-token.php';
		require_once dirname( NF_File_Uploads()->plugin_file_path ) . '/vendor/polevaultweb/wp-oauth2/admin-handler.php';
		require_once dirname( NF_File_Uploads()->plugin_file_path ) . '/vendor/polevaultweb/wp-oauth2/oauth2-client.php';
		require_once dirname( NF_File_Uploads()->plugin_file_path ) . '/vendor/polevaultweb/wp-oauth2/dropbox-client.php';

		add_filter( 'ninja_forms_register_actions', array( $this, 'register_actions' ) );
		add_filter( 'ninja_forms_file_uploads_settings_whitelist', array( $this, 'add_settings_to_whitelist' ) );
		add_action( 'ninja_forms_uploads_external_template', array( $this, 'render_services_settings' ) );
		add_filter( 'ninja_forms_uploads_file_url', array( $this, 'generate_url' ), 10, 2);
		add_filter( 'ninja_forms_uploads_file_location', array( $this, 'get_service_display_name' ) );
		add_action( 'ninja_forms_loaded', array( $this, 'init' ) );

		add_action( 'admin_init', array( $this, 'handle_external_url' ) );
		add_action( 'template_redirect', array( $this, 'handle_external_url_public' ) );
	}

	public function init() {
		$class         = 'Polevaultweb\\WP_OAuth2\\Admin_Handler';
		$admin_handler = new $class( NF_File_Uploads()->page->get_url( 'external', array(), false ) );
		$admin_handler->init();
	}

	/**
	 * Is the site compatible for external uploads
	 *
	 * @return bool
	 */
	public static function is_compatible() {
		return version_compare( PHP_VERSION, self::$min_php_version, '>=' );
	}

	/**
	 * Get the external services we can upload files to
	 *
	 * @return array
	 */
	public function get_services() {
		$default_services = glob( dirname( NF_File_Uploads()->plugin_file_path ) . '/includes/external/services/*', GLOB_ONLYDIR );
		$default_services = array_map( 'basename', $default_services );

		$services = array();
		foreach ( $default_services as $service ) {
			$file = dirname( NF_File_Uploads()->plugin_file_path ) . '/includes/external/services/' . $service . '/service.php';

			$services[ $file ] = $service;
		}

		/**
		 * External services
		 *
		 * Array of external services, key is the path to service file, value is service name slug
		 *
		 * @return array $services
		 */
		return apply_filters( 'ninja_forms_uploads_external_service', $services );
	}

	/**
	 * Get external service
	 *
	 * @param string     $service
	 * @param null|array $services
	 *
	 * @return bool|NF_FU_External_Service
	 */
    public function get( $service, $services = null ) {
        $service = $this->get_upload_location( $service );

	    if ( is_null( $services ) ) {
		    $services = $this->get_services();
	    }

	    if ( ! in_array( $service, $services ) ) {
		    return false;
	    }

	    $file = array_search( $service, $services );
	    if ( ! file_exists( $file ) ) {
		    return false;
	    }

		$service = ucfirst( $service );
		$class   = $this->class_prefix . $service . '_Service';

	    if ( ! method_exists( $class, 'instance') ) {
	    	return false;
	    }

		$external = call_user_func( array( $class, 'instance' ) );

		return $external;
	}

	/**
	 * Are there any services that are ready to use?
	 *
	 * @return bool
	 */
	protected function is_connected_services() {
		$services = $this->get_services();
		foreach ( $services as $service ) {
			if ( ! ( $instance = $this->get( $service, $services ) ) ) {
				continue;
			}

			if ( $instance->is_compatible() && $instance->is_connected() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register actions for external services
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function register_actions( $actions ) {
		if ( $this->is_connected_services() ) {
			$actions['file-upload-external'] = new NF_FU_External_Action();
		}

		return $actions;
	}

	/**
	 * Add the external tab to the settings page
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function add_menu_tab( $tabs ) {
		$tabs['external'] = __( 'External Settings', 'ninja-forms-uploads' );

		return $tabs;
	}

	/**
	 * Add external settings to the whitelist for saving from settings page
	 *
	 * @param array $whitelist
	 *
	 * @return array
	 */
	public function add_settings_to_whitelist( $whitelist ) {
		$services = $this->get_services();
		foreach ( $services as $service ) {
			if ( ! ( $instance = $this->get( $service, $services ) ) ) {
				continue;
			}
			$settings  = $instance->get_settings();
			$whitelist = array_merge( $whitelist, $settings );
		}

		return $whitelist;
	}

	/**
	 * Render the settings for the services on the External settings page
	 */
	public function render_services_settings() {
		$services = $this->get_services();

		foreach ( $services as $service ) {
			if ( ! ( $instance = $this->get( $service, $services ) ) ) {
				continue;
			}

			$requirements = $instance->get_missing_requirements();
			if ( $requirements ) {
				$message = sprintf( __( '%s requires %s', 'ninja-forms-uploads' ), $instance->get_name(), implode( ', ', $requirements ) );
				printf( '<div class="error"><p>%s</p></div>', $message );

				continue;
			}

			$notices = $instance->get_notices();
			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					printf( '<div class="%s"><p>%s</p></div>', $notice['type'], $notice['message'] );
				}
			}

			$args = array(
				'group'       => $service,
				'group_label' => sprintf( __( '%s Settings', 'ninja-forms-uploads' ), $instance->get_name() ),
				'settings'    => $instance->get_settings(),
			);

			NF_File_Uploads()->template( 'admin-menu-meta-box.html', $args );
		}
	}

	/**
	 * Get the external URL for the file upload
	 *
	 * @param string $file_url
	 * @param array  $data
	 *
	 * @return string
	 */
	public function generate_url( $file_url, $data ) {
		if ( ! isset( $data['upload_id'] ) ) {
			return $file_url;
		}

		if ( isset( $data['upload_location'] ) && 'server' === $data['upload_location'] ) {
			return $file_url;
		}

		if ( ! ( $instance = NF_File_Uploads()->externals->get( $data['upload_location'] ) ) ) {
			return $file_url;
		}

		if ( ! $instance->is_compatible() || ! $instance->is_connected() ) {
			return $file_url;
		}

		$url_path = '?nf-upload=' . $data['upload_id'];
		if ( defined( 'NINJA_FORMS_UPLOADS_USE_PUBLIC_URL' ) && NINJA_FORMS_UPLOADS_USE_PUBLIC_URL ) {
			$file_url = home_url( $url_path );
		} else {
			$file_url = admin_url( $url_path );
		}

		return $file_url;
	}

	/**
	 * Listen for external service file URLs and redirect to the service URL with a public URL
	 */
	public function handle_external_url_public() {
		if ( ! defined( 'NINJA_FORMS_UPLOADS_USE_PUBLIC_URL' ) || ! NINJA_FORMS_UPLOADS_USE_PUBLIC_URL ) {
			return;
		}

		$this->handle_external_url();
	}

	/**
	 * Listen for external service file URLs and redirect to the service URL
	 */
	public function handle_external_url() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$upload_id = filter_input( INPUT_GET, 'nf-upload', FILTER_VALIDATE_INT );

		if ( ! isset( $upload_id ) ) {
			return;
		}

		$upload = NF_File_Uploads()->controllers->uploads->get( $upload_id );

		if ( ! ( $instance = $this->get( $upload->upload_location ) ) ) {
			return;
		}

		$path     = ( isset( $upload->external_path ) ) ? $upload->external_path : '';
		$filename = ( isset( $upload->external_filename ) ) ? $upload->external_filename : $upload->file_name;
		$file_url = $instance->get_url( $filename, $path, $upload->data );

		wp_redirect( $file_url );
		die();
	}

	/**
	 * Backwards compatible upload location
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	protected function get_upload_location( $location ) {
		if ( 'amazon' === $location ) {
			return 's3';
		}

		return $location;
	}

	/**
	 * Get the display name for a service by slug
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function get_service_display_name( $slug ) {
		$slug = $this->get_upload_location( $slug );
		$slug = str_replace( '-', ' ', $slug );
		$slug = ucwords( $slug );

		return $slug;
	}

}