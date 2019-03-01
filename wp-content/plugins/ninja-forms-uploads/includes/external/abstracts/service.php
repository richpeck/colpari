<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_External_Abstracts_Service
 */
abstract class NF_FU_External_Abstracts_Service {

	/**
	 * @var array
	 */
	private static $instances = array();

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var array
	 */
	public $settings;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	protected $library_class;

	/**
	 * @var string
	 */
	protected $library_alias;

	/**
	 * @var string
	 */
	protected $library_file;

	/**
	 * @var Path to file being uploaded
	 */
	protected $upload_file;

	/**
	 * @var Path prefix to file on service
	 */
	protected $external_path;

	/**
	 * @var Filename of file on service
	 */
	protected $external_filename;

	/**
	 * @var array Ninja Form settings
	 */
	protected $nf_settings;
	
	/**
	 * Main Plugin Instance
	 *
	 * Insures that only one instance of a service class exists in memory at any one
	 * time.
	 *
	 * @return NF_FU_External_Abstracts_Service
	 */
	final public static function instance() {
		$called_class = get_called_class();

		if ( ! isset( self::$instances[ $called_class ] ) ) {
			self::$instances[ $called_class ] = new $called_class();

			$parts = explode( '_', str_replace( '_Service', '', $called_class ) );
			$slug  = strtolower( array_pop( $parts ) );

			$reflector = new ReflectionClass( $called_class );

			self::$instances[ $called_class ]->file          = $reflector->getFileName();
			self::$instances[ $called_class ]->slug          = $slug;
			self::$instances[ $called_class ]->library_alias = 'NF_FU_Library_' . ucfirst( $slug );
			
			self::$instances[ $called_class ]->init();

			spl_autoload_register( array( self::$instances[ $called_class ], 'maybe_alias_library' ), true, true );
		}

		return self::$instances[ $called_class ];
	}

	/**
	 * Get instance internally
	 *
	 * @return mixed
	 */
	protected static function get_instance() {
		$called_class = get_called_class();

		return self::$instances[ $called_class ];
	}

	/**
	 * Do things on initialization
	 */
	protected function init() {
		// Perfect for hooks and filter
	}

	/**
	 * Maybe alias a library class and autoload it
	 * so we don't get clashes with other plugins using same class names
	 *
	 * @param $class
	 */
	public function maybe_alias_library( $class ) {
		if ( 0 === strcasecmp( $class, self::get_instance()->library_alias ) ) {
			if ( false !== self::get_instance()->library_file ) {
				$this->alias_library();
			}
		}
	}

	/**
	 * Load our library class
	 */
	protected function alias_library() {
		$file = self::get_instance()->library_file;
		if ( ! file_exists( self::get_instance()->library_file ) ) {
			$file = dirname( NF_File_Uploads()->plugin_file_path ) . '/vendor/' . self::get_instance()->library_file;
		}

		require_once $file;

		class_alias( self::get_instance()->library_class, self::get_instance()->library_alias );
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings() {
		$config = dirname( self::get_instance()->file ) . '/config.php';
		if ( file_exists( dirname( self::get_instance()->file ) . '/config.php' ) ) {
			return include $config;
		}

		return array();
	}

	/**
	 * Load the service specific settings
	 *
	 * @return array
	 */
	public function load_settings() {
		if ( is_null( $this->settings ) ) {

			$plugin_settings = NF_File_Uploads()->controllers->settings->get_settings();
			$settings        = $this->get_settings();

			$this->settings = array();
			foreach ( $settings as $key => $setting ) {
				$default = isset( $setting['default'] ) ? $setting['default'] : '';
				$value   = isset( $plugin_settings[ $key ] ) ? $plugin_settings[ $key ] : $default;

				$this->settings[ $key ] = $value;
			}

		}

		return $this->settings;
	}

	/**
	 * Get service name
	 *
	 * @return string
	 */
	public function get_name() {
		$name = self::get_instance()->slug;
		if ( ! is_null( self::get_instance()->name ) ) {
			$name = self::get_instance()->name;
		}

		return $name;
	}

	/**
	 * Get the external filename for a file
	 *
	 * @return string
	 */
	protected function get_filename_external() {
		$original_filename = basename( $this->upload_file );
		$filename          = time() . '-' . $original_filename;

		return apply_filters( 'ninja_forms_uploads_' . self::get_instance()->slug . '_filename', $filename, $original_filename );
	}

	/**
	 * Get path on service
	 *
	 * @return string
	 */
	protected abstract function get_path_setting();

	/**
	 * Get the external file path
	 */
	protected function get_external_path( $custom_path ) {
		$path = $this->settings[ $this->get_path_setting() ];

		$path = untrailingslashit( $path ) . '/' . untrailingslashit( $custom_path );

		return apply_filters( 'ninja_forms_uploads_' . $this->slug . '_path', $this->prepare_path( $path ) );
	}

	/**
	 * Prepare path
	 *
	 * @param string $path
	 * @param string $suffix
	 *
	 * @return string
	 */
	protected function prepare_path( $path, $suffix = '/' ) {
		$path = ltrim( $path, '/' );
		$path = rtrim( $path, '/' );

		if ( ! empty( $path ) ) {
			$path .= $suffix;
		}

		return $path;
	}

	/**
	 * Upload the attachment to the service
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function process_upload( $data ) {
		$this->load_settings();

		$this->upload_file       = $data['file_path'];
		$custom_path = isset( $data['custom_path'] ) ? $data['custom_path'] : '';
		$this->external_path     = $this->get_external_path( $custom_path );
		$this->external_filename = $this->get_filename_external(  );

		$data = $this->upload_file( $data );

		if ( false === $data ) {
			return $data;
		}

		$data['upload_location']   = $this->slug;
		$data['external_path']     = $this->external_path;
		$data['external_filename'] = $this->external_filename;

		// Update uploads table
		NF_File_Uploads()->model->update( $data['upload_id'], $data );

		return $data;
	}

	/**
	 * Is the service compatible with the site?
	 *
	 * @return bool
	 */
	public function is_compatible() {
		$missing_requirements = $this->get_missing_requirements();

		return empty( $missing_requirements );
	}

	/**
	 * Get the missing service requirements
	 *
	 * @return array|bool
	 */
	public function get_missing_requirements() {
		$missing_requirements = array();

		return $missing_requirements;
	}

	/**
	 * Get notices for the service.
	 *
	 * @return array|bool
	 */
	public function get_notices() {
		return array();
	}

	/**
	 * Upload the file to the service
	 *
	 * @param array $data
	 *
	 * @return array|bool
	 */
	protected abstract function upload_file( $data );

	/**
	 * Get the service URL to the file
	 *
	 * @param string $filename
	 * @param string $path
	 * @param array  $data
	 *
	 * @return string
	 */
	public abstract function get_url( $filename, $path = '', $data = array() );

	/**
	 * @param null|array $settings
	 *
	 * @return bool
	 */
	public abstract function is_connected( $settings = null );

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * class via the `new` operator from outside of this class.
	 */
	protected function __construct() {
	}

	/**
	 * As this class is a singleton it should not be clone-able
	 */
	protected function __clone() {
	}

	/**
	 * As this class is a singleton it should not be able to be unserialized
	 */
	protected function __wakeup() {
	}
}