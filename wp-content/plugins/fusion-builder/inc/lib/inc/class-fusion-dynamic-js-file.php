<?php
/**
 * Dynamic-JS loader - File Method.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handles enqueueing files dynamically.
 */
final class Fusion_Dynamic_JS_File extends Fusion_Dynamic_JS_Compiler {

	/**
	 * The filename.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var false|string
	 */
	protected $filename;

	/**
	 * The Fusion_Filesystem instance of the $filename.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var null|object
	 */
	public $file = null;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param object $dynamic_js An instance of the Fusion_Dynamic_JS object.
	 */
	public function __construct( $dynamic_js ) {

		parent::__construct( $dynamic_js );

		$this->filename = $this->get_filename();
		$this->file     = new Fusion_Filesystem( $this->filename, 'fusion-scripts' );
		$no_file        = false;

		if ( ! file_exists( $this->file->get_path() ) ) {
			$url = $this->write_file();
			if ( ! $url ) {
				$no_file = true;
			}
		}

		if ( $no_file || ! self::js_file_is_readable() ) {
			new Fusion_Dynamic_JS_Separate( $dynamic_js );
			self::disable_dynamic_js();
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	/**
	 * Enqueues the file.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		// Get an array of external dependencies.
		$dependencies = array_unique( $this->get_external_dependencies() );
		// Enqueue the script.
		wp_enqueue_script( 'fusion-scripts', $this->file->get_url(), $dependencies, null, true );
	}

	/**
	 * Check if file is accessable.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function js_file_is_readable() {
		// Get the file-path.
		$file_path = $this->file->get_path();
		// Check if the file is readable in the transient and apply the filter.
		$is_readable = apply_filters( 'fusion_compiler_js_file_is_readable', get_transient( 'fusion_dynamic_js_readable' ) );

		// If not readable, we need to do some extra checks.
		if ( ! $is_readable ) {

			// Check if we can access the file via PHP.
			$is_readable = (bool) ( is_readable( $file_path ) );

			// If we could access the file via PHP, check that we can get the URL.
			if ( $is_readable ) {

				// Check for 403 / 500.
				$response = wp_safe_remote_get(
					$this->file->get_url(),
					array(
						'timeout' => 5,
					)
				);

				$response_code = wp_remote_retrieve_response_code( $response );

				// Check if the response is ok.
				$is_readable = ( 200 === $response_code );

				// Cache readable only. No need to cache unreadable, it's false anyway.
				if ( $is_readable ) {
					set_transient( 'fusion_dynamic_js_readable', true );
				}
			}
		}
		return apply_filters( 'fusion_compiler_js_file_is_readable', $is_readable );
	}

	/**
	 * Disable Dynamic JS compiler.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function disable_dynamic_js() {
		$options                = get_option( Fusion_Settings::get_option_name(), array() );
		$options['js_compiler'] = '0';

		update_option( Fusion_Settings::get_option_name(), $options );
		add_filter( 'fusion_compiler_js_file_is_readable', '__return_false' );
	}

	/**
	 * Writes the styles to a file.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool Whether the file-write was successful or not.
	 */
	public function write_file() {

		// Get the compiled JS.
		$content = $this->get_compiled_js();

		// Attempt to write the file.
		return ( $this->file->write_file( $content ) );

	}

	/**
	 * Gets the filename.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string
	 */
	public function get_filename() {

		$filenames = get_transient( 'fusion_dynamic_js_filenames' );
		if ( ! is_array( $filenames ) ) {
			$filenames = array();
		}
		$fusion = Fusion::get_instance();
		$id     = (int) $fusion->get_page_id();
		if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['PHP_SELF'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			$self = sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) );
			$uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$id  .= md5( $host . $self . $uri );
			if ( isset( $filenames[ $id ] ) ) {
				return $filenames[ $id ] . '.min.js';
			}
		}

		// Do not reorder files here to improve performace.
		$scripts = wp_json_encode( $this->get_scripts( false ) );
		$l10n    = wp_json_encode( $this->dynamic_js->get_localizations() );
		// Create a filename using md5() and combining the scripts array with localizations.
		$filename = md5( $scripts . $l10n );

		$filenames[ $id ] = $filename;
		set_transient( 'fusion_dynamic_js_filenames', $filenames, HOUR_IN_SECONDS );

		return $filename . '.min.js';

	}

	/**
	 * DEPRECATED. Deletes all compiled JS files.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function delete_compiled_js() {
		/* Deprecated. Keeping this around in case someone is using it in a custom implementation. It won't do anything, but it won't throw errors or cause planes to crash either. */
	}

	/**
	 * Resets the cached filenames transient.
	 *
	 * @static
	 * @since 1.0.0
	 * @return bool
	 */
	public static function reset_cached_filenames() {

		return delete_transient( 'fusion_dynamic_js_filenames' );

	}

	/**
	 * Resets JS compiler transient.
	 *
	 * @static
	 * @since 1.0.0
	 * @return bool
	 */
	public static function delete_dynamic_js_transient() {

		return delete_transient( 'fusion_dynamic_js_readable' );

	}
}
