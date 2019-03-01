<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NF_FU_Admin_Controllers_Settings {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Get settings for the plugin
	 *
	 * @return array
	 */
	public function get_settings() {
		if ( is_null( $this->settings ) || empty( $this->settings ) ) {
			$this->settings = Ninja_Forms()->get_settings();
		}

		return $this->settings;
	}

	/**
	 * Update settings for the plugin
	 */
	public function update_settings() {
		update_option( 'ninja_forms_settings', $this->settings );
	}

	/**
	 * Set a setting
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set_setting( $key, $value ) {
		$this->settings[ $key ] = $value;
	}

	/**
	 * Remove a setting
	 *
	 * @param string $key
	 */
	public function remove_setting( $key ) {
		unset( $this->settings[ $key ] );
	}

	/**
	 * Get a setting
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return mixed|null
	 */
	public function get_setting( $key, $default = null ) {
		$settings = $this->get_settings();
		if ( ! isset( $settings[ $key ] ) ) {
			$config = NF_File_Uploads()->config( 'settings-upload', array( 'raw' => true ) );

			if ( is_null( $default ) && isset( $config[ $key ]['default'] ) ) {
				$default = $config[ $key ]['default'];
			} else {
				$default = '';
			}

			return $default;
		}

		$value = $settings[ $key ];

		if ( 'max_filesize' === $key && empty( $value ) ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Get the custom upload directory formatted for use.
	 *
	 * @return mixed|null|string
	 */
	public function custom_upload_dir() {
		$value = $this->get_setting( __FUNCTION__, '' );

		if ( empty( $value ) ) {
			return $value;
		}

		return '/' . trailingslashit( rtrim( $value, '/\\' ) );
	}

	/**
	 * Get the maximum size per file
	 *
	 * @return mixed|null
	 */
	public function max_filesize( $value = null ) {
		if ( is_null( $value ) ) {
			$value = $this->get_setting( __FUNCTION__, 2 );
		}

		return $value * 1048576;
	}

	/**
	 * Get max file size in MB
	 * 
	 * @return int|mixed|null
	 */
	public function get_max_file_size_mb() {
		$max_file_size_mb = $this->get_setting( 'max_filesize' );
		if ( empty( $max_file_size_mb ) ) {
			// If setting not saved use the server max
			$max_file_size_mb = NF_FU_Helper::format_mb( ini_get( 'upload_max_filesize' ), true );
		}

		return $max_file_size_mb;
	}
}