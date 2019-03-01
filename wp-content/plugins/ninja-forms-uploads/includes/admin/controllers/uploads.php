<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NF_FU_Admin_Controllers_Uploads {

	/**
	 * @var string
	 */
	protected static $base_dir;

	/**
	 * @var string
	 */
	protected static $base_url;

	/**
	 * @var string
	 */
	protected static $tmp_dir;

	/**
	 * NF_FU_Admin_Controllers_Uploads constructor.
	 */
	public function __construct() {
		add_action( 'ninja_forms_after_submission', array( $this, 'remove_files_from_server') );
	}

	/**
	 * Remove the file from the server if the field is not to be saved to the server.
	 * This happens on the latest hook possible after actions have been run.
	 *
	 * @param array $data
	 */
	public function remove_files_from_server( $data ) {
		if ( ! isset( $data['fields'] ) || ! is_array( $data['fields'] ) ) {
			return;
		}

		foreach( $data['fields'] as $field ) {
			if ( NF_FU_File_Uploads::TYPE !== $field['type'] ) {
				continue;
			}

			if ( ! isset( $field['save_to_server'] ) ) {
				continue;
			}

			if ( "1" == $field['save_to_server'] ) {
				continue;
			}

			if ( empty( $field['value'] ) ) {
				continue;
			}

			foreach ( $field['value'] as $upload_id => $url ) {
				$upload = $this->get( $upload_id );

				$file_path = $upload->file_path;

				if ( file_exists( $file_path ) ) {
					// Delete local file
					unlink( $file_path );
				}
			}
		}
	}

	/**
	 * Get the base upload directory
	 *
	 * @return string
	 */
	public function get_base_dir() {
		if ( is_null( self::$base_dir ) ) {
			$base_upload_dir = wp_upload_dir();
			$base_upload_dir = $base_upload_dir['basedir'] . '/ninja-forms';

			wp_mkdir_p( $base_upload_dir );

			self::$base_dir = $base_upload_dir;
		}

		return self::$base_dir;
	}

	/**
	 * Get the URL base upload directory
	 *
	 * @return string
	 */
	public function get_base_url() {
		if ( is_null( self::$base_url ) ) {
			$base_upload_url = wp_upload_dir();
			$base_upload_url = $base_upload_url['baseurl'] . '/ninja-forms';

			self::$base_url = $base_upload_url;
		}

		return self::$base_url;

	}

	/**
	 * Get the temp upload directory
	 *
	 * @return string
	 */
	public function get_temp_dir() {
		if ( is_null( self::$tmp_dir ) ) {
			$base_upload_dir = $this->get_base_dir();
			$tmp_upload_dir  = $base_upload_dir . '/tmp';

			wp_mkdir_p( $tmp_upload_dir );
			$this->maybe_create_tmp_htaccess( $tmp_upload_dir );

			self::$tmp_dir = $tmp_upload_dir;
		}

		return self::$tmp_dir;
	}

	/**
	 * Copy .htaccess file to tmp directory for security
	 * https://github.com/blueimp/jQuery-File-Upload/wiki/Security#php
	 *
	 * @param string $tmp_upload_dir
	 */
	protected function maybe_create_tmp_htaccess( $tmp_upload_dir ) {
		$dest = $tmp_upload_dir . '/.htaccess';
		if ( file_exists( $dest ) ) {
			return;
		}

		$source = dirname( NF_File_Uploads()->plugin_file_path ) . '/includes/.htaccess.txt';

		@copy( $source, $dest );
	}

	/**
	 * Get the file path for the temp file
	 *
	 * @param string $filename
	 * @param bool   $temp Use temp path
	 *
	 * @return string
	 */
	public function get_path( $filename = '', $temp = false ) {
		$file_path = $temp ? $this->get_temp_dir() : $this->get_base_dir();

		$field_id  = isset( $this->field_id ) ? $this->field_id : null;
		$file_path = apply_filters( 'ninja_forms_uploads_dir', $file_path, $field_id );

		return trailingslashit( $file_path ) . $filename;
	}

	/**
	 * Get the URL of a file
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	public function get_url( $filename ) {
		$field_id = isset( $this->field_id ) ? $this->field_id : null;
		$file_url = apply_filters( 'ninja_forms_uploads_url', $this->get_base_url(), $field_id );

		return trailingslashit( $file_url ) . $filename;
	}

	/**
	 * Get a file upload from the table
	 *
	 * @param int $id
	 *
	 * @return object|false
	 */
	public function get( $id ) {
		$upload = NF_File_Uploads()->model->get( $id );

		if ( is_null( $upload ) ) {
			return false;
		}

		$data = unserialize( $upload->data );

		foreach ( $data as $key => $value ) {
			$upload->$key = $value;
		}

		$upload->data = $data;

		return $upload;
	}

	/**
	 * Get the file URL for an upload
	 * 
	 * @param string $url
	 * @param array $data
	 *
	 * @return string
	 */
	public function get_file_url( $url, $data ) {
		return apply_filters( 'ninja_forms_uploads_file_url', $url, $data );
	}

	/**
	 * Create attachment in media library from the file
	 *
	 * @param string      $file
	 * @param null|string $file_name
	 *
	 * @return array
	 */
	public function create_attachment( $file, $file_name = null ) {
		if ( is_null( $file_name ) ) {
			$file_name = basename( $file );
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$wp_filetype = wp_check_filetype( $file_name );
		$attachment  = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $file );

		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		$attach_data['ninja_forms_upload_field'] = true;

		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}
}