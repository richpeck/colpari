<?php
/**
 * Import demos for fusion-builder.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Demos importer.
 */
class Fusion_Builder_Demos_Importer {

	/**
	 * The remote API URL.
	 *
	 * @static
	 * @access private
	 * @since 5.0.0
	 * @var string
	 */
	private static $remote_api_url = 'https://updates.theme-fusion.com/avada_demo/?fusion_builder_demos=1&compressed=1';

	/**
	 * The Remote URL of the file containing the demo pages.
	 *
	 * @access private
	 * @since 5.0.0
	 * @var string
	 */
	private $demo_remote_url = '';

	/**
	 * The name to the demo folder.
	 *
	 * @static
	 * @access private
	 * @since 5.0.0
	 * @var string
	 */
	private static $demo_folder_name = 'fusion-builder-avada-pages';

	/**
	 * The path to the demo file locally.
	 *
	 * @access private
	 * @since 5.0.0
	 * @var string
	 */
	private $demo_folder_path = '';

	/**
	 * The filename of the zip containing demo data.
	 *
	 * @access private
	 * @since 5.0.3
	 * @var string
	 */
	private $zip_file_name = 'data.zip';

	/**
	 * The array of demo files that should be imported.
	 *
	 * @static
	 * @access private
	 * @since 5.6.2
	 * @var array
	 */
	private static $demo_files = array();

	/**
	 * Boolean to check if the demo zip is present.
	 *
	 * @access private
	 * @since 5.0.3
	 * @var bool
	 */
	private $is_demo_data_zip_downloaded = false;

	/**
	 * Boolean to check if the demo zip is extractable.
	 *
	 * @access private
	 * @since 5.0.3
	 * @var bool
	 */
	private $is_demo_data_zip_extracted = false;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 5.0.0
	 */
	public function __construct() {

		$this->demo_folder_path            = self::get_demo_folder_path();
		$this->is_demo_folder_writeable    = self::is_demo_folder_writeable();
		$this->is_demo_data_zip_downloaded = $this->import_demo_data_zip();

		if ( $this->is_demo_data_zip_downloaded ) {
			self::$demo_files = $this->get_demo_files();

			// If there are no single demos files, we have to extract the zip.
			if ( empty( self::$demo_files ) ) {
				$this->extract_demo_data_zip();
			}

			$this->include_demo_files();
		}
	}

	/**
	 * Get the local demo path.
	 *
	 * @static
	 * @access private
	 * @since 5.0.0
	 * @return string
	 */
	private static function get_demo_folder_path() {

		$wp_upload_dir              = wp_upload_dir();
		$demo_folder_path['direct'] = wp_normalize_path( $wp_upload_dir['basedir'] . '/' . self::$demo_folder_name . '/' );

		$method = defined( 'FS_METHOD' ) ? FS_METHOD : false;

		if ( 'ftpext' === $method ) {
			// For FS_METHOD ftpext we need to change target paths
			// as FTP root dir might not be server's root dir.
			$wp_filesystem              = Fusion_Helper::init_filesystem();
			$plugins_path               = $wp_filesystem->wp_plugins_dir();
			$demo_folder_path['ftpext'] = wp_normalize_path( str_replace( 'plugins', 'uploads', $plugins_path ) . '/' . self::$demo_folder_name . '/' );
		}

		return $demo_folder_path;

	}

	/**
	 * Checks is the demo folder writable is writeable.
	 * Creates the folder, if it is not already there.
	 *
	 * @static
	 * @access public
	 * @since 5.0.3
	 * @return bool
	 */
	public static function is_demo_folder_writeable() {

		$demo_folder_path = self::get_demo_folder_path();

		// If the folder doesn't exist, attempt to create it.
		if ( ! file_exists( $demo_folder_path['direct'] ) ) {

			$new_folder = wp_mkdir_p( $demo_folder_path['direct'] );

			// Return false if we were unable to create the folder.
			if ( false === $new_folder ) {
				$method = defined( 'FS_METHOD' ) ? FS_METHOD : false;
				if ( 'ftpext' === $method ) {
					$wp_filesystem = Fusion_Helper::init_filesystem();
					$new_folder    = $wp_filesystem->mkdir( $demo_folder_path['ftpext'] );
					return ( false !== $new_folder );
				}
				return false;
			}
		}

		// Return true/false based on the target folder's writability.
		return wp_is_writable( $demo_folder_path['direct'] );

	}

	/**
	 * Get the demo files array from which content is imported.
	 *
	 * @static
	 * @access public
	 * @since 5.6.2
	 * @return array
	 */
	public static function get_demo_files() {
		$demo_folder_path = self::get_demo_folder_path();

		if ( file_exists( $demo_folder_path['direct'] ) && empty( self::$demo_files ) ) {
			return glob( $demo_folder_path['direct'] . '*.php' );
		}

		return self::$demo_files;
	}

	/**
	 * Checks the amount of files in demo data folder.
	 *
	 * @static
	 * @access public
	 * @since 5.0.3
	 * @return int
	 */
	public static function get_number_of_demo_files() {
		return count( self::get_demo_files() );
	}

	/**
	 * Import the demo data zip from our server, if it is not already there.
	 *
	 * @access private
	 * @since 5.0.3
	 * @return bool
	 */
	private function import_demo_data_zip() {

		$zip_file = '';
		// Early exit if we can't write to the destination folder.
		if ( ! $this->is_demo_folder_writeable ) {
			return false;
		}

		$method   = defined( 'FS_METHOD' ) ? FS_METHOD : false;
		$method   = ( 'ftpext' === $method ) ? 'ftpext' : 'direct';
		$zip_file = $this->demo_folder_path[ $method ] . $this->zip_file_name;

		if ( $this->should_import( $zip_file ) ) {
			$response = avada_wp_get_http( self::$remote_api_url, $zip_file );

			if ( false === $response ) {
				return false;
			}
		}

		return true;

	}

	/**
	 * Determine if we need to import demos.
	 *
	 * @access private
	 * @since 5.1
	 * @param string $file The file to check against.
	 * @return bool
	 */
	private function should_import( $file ) {

		$transient_name = 'fusion_builder_demos_import_skip_check';

		// Check if we want to skip the check.
		if ( true === get_site_transient( $transient_name ) ) {
			return false;
		}

		// If the file doesn't exist then we should import.
		if ( ! file_exists( $file ) ) {
			return true;
		}

		// If the file is more than a week old, we should import.
		$lastweek  = time() - WEEK_IN_SECONDS;
		$filemtime = filemtime( $file );
		if ( $filemtime < $lastweek ) {

			// Demos more than a week old.
			// Delete them so that they may be re-imported.
			self::delete_demos();

			return true;
		}

		// If we got this far then we don't need to import.
		// Check again tomorrow.
		set_site_transient( $transient_name, true, DAY_IN_SECONDS );
		return false;

	}

	/**
	 * Extract the demo data zip.
	 *
	 * @access private
	 * @since 5.0.3
	 * @return void
	 */
	private function extract_demo_data_zip() {

		$zip_file  = $this->demo_folder_path['direct'] . $this->zip_file_name;
		$unzipfile = '';

		Avada_Helper::init_filesystem();
		$method    = defined( 'FS_METHOD' ) ? FS_METHOD : false;
		$method    = ( 'ftpext' === $method ) ? 'ftpext' : 'direct';
		$unzipfile = unzip_file( $zip_file, $this->demo_folder_path[ $method ] );

		if ( $unzipfile ) {
			self::$demo_files = $this->get_demo_files();
		}
	}

	/**
	 * Include the demo data files.
	 *
	 * @since 5.0.3
	 * @access private
	 * @return void
	 */
	private function include_demo_files() {

		// Load Fusion Builder demos.
		foreach ( self::$demo_files as $demo_file ) {
			include $demo_file;
		}
	}

	/**
	 * Delete FB demos.
	 * They will be re-downloaded the next time they're needed.
	 *
	 * @static
	 * @access public
	 * @since 5.1
	 */
	public static function delete_demos() {

		$wp_upload_dir = wp_upload_dir();
		$basedir       = $wp_upload_dir['basedir'];
		$dir           = wp_normalize_path( $basedir . '/fusion-builder-avada-pages' );

		// initialize the WordPress Filesystem.
		$filesystem = Avada_Helper::init_filesystem();

		// Recursively delete the folder.
		return $filesystem->delete( $dir, true );

	}
}
