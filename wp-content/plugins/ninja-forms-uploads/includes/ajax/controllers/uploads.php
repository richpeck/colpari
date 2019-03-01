<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NF_FU_AJAX_Controllers_Uploads extends NF_Abstracts_Controller {

	protected $_blacklist = array();

	protected $field_id;

	public function __construct() {
		parent::__construct();

		$this->_blacklist = apply_filters( 'ninja_forms_uploads_extension_blacklist', NF_File_Uploads()->config( 'extension-blacklist' ) );
	}

	/**
	 * Initialize
	 */
	public function init() {
		add_action( 'wp_ajax_nf_fu_upload', array( $this, 'handle_upload' ) );
		add_action( 'wp_ajax_nopriv_nf_fu_upload', array( $this, 'handle_upload' ) );
		add_action( 'nf_fu_delete_temporary_file', array( $this, 'delete_temporary_file' ), 10, 1 );
	}
	
	/**
	 * Process the upload of files
	 */
	public function handle_upload() {
		$field_id = filter_input( INPUT_POST, 'field_id' );
		if ( empty( $field_id ) ) {
			$this->_errors[] = __( 'No field ID supplied', 'ninja-forms-uploads' );
			$this->_respond();
		}

		$result = check_ajax_referer( 'nf-file-upload-' . $field_id, 'nonce', false );
		if ( false === $result ) {
			$this->_errors[] = __( 'Nonce error', 'ninja-forms-uploads' );
			$this->_respond();
		}

		if ( ! isset( $_FILES['files'] ) ) {
			$this->_errors[] = $this->code_to_message( '' );
			$this->_respond();
		}

		$this->_data['files'] = $this->_prepare( $_FILES['files'] );

		$this->_process();
		$this->_respond();
	}

	/**
	 * Delete temp file
	 *
	 * @param $file_path
	 */
	public function delete_temporary_file( $file_path ) {
		if ( file_exists( $file_path ) ) {
			@unlink( $file_path );
		}
	}

	/**
	 * Prepare the array of files to turn the array into a more useful structure
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	protected function _prepare( $files ) {
		$clean_files = array();
		$file_count  = count( $files['name'] );
		$file_keys   = array_keys( $files );

		if ( ! is_array( $files['name'] ) ) {
			return array( $files );
		}

		for ( $i = 0; $i < $file_count; $i++ ) {
			foreach ( $file_keys as $key ) {
				$clean_files[ $i ][ $key ] = $files[ $key ][ $i ];
			}
		}

		return $clean_files;
	}

	/**
	 * Process each file
	 *
	 * Temporarily store the uploaded files until the form is submitted
	 */
	protected function _process() {
		foreach ( $this->_data['files'] as $key => $file ) {

			if ( false === $this->_validate( $file ) ) {
				unset( $this->_data['files'][ $key ] );
				@unlink( $file['tmp_name'] );
				continue;
			}

			$new_tmp_name = $this->get_temp_filename( $file['name'] );
			$file_path    = NF_File_Uploads()->controllers->uploads->get_path( $new_tmp_name, true );

			$result = move_uploaded_file( $file['tmp_name'], $file_path );
			if ( false === $result ) {
				unset( $this->_data['files'][ $key ] );
				$this->_errors[] = __( 'Unable to move uploaded temp file', 'ninja-forms-uploads' );

				continue;
			}

			// Schedule a clean up of the file if the form doesn't get submitted
			wp_schedule_single_event( apply_filters( 'ninja_forms_uploads_temp_file_delete_time', time() + HOUR_IN_SECONDS ), 'nf_fu_delete_temporary_file', array( $file_path ) );

			$this->_data['files'][ $key ]['tmp_name'] = $new_tmp_name;
		}
	}

	/**
	 * Validate the file for upload
	 *
	 * @param array $file
	 *
	 * @return bool
	 */
	protected function _validate( $file ) {
		// Check for upload errors
		if ( $file['error'] && UPLOAD_ERR_OK !== $file['error'] ) {
			$this->_errors[] = $this->code_to_message( $file['error'] );

			return false;
		}

		$form_id = filter_input( INPUT_POST, 'form_id', FILTER_VALIDATE_INT );
		$field_id = filter_input( INPUT_POST, 'field_id', FILTER_VALIDATE_INT );

		if ( ! isset( $form_id ) || ! isset( $field_id ) ) {
			// Haven't got the data to grab field settings, bail
			return true;
		}

		$field = Ninja_Forms()->form( $form_id )->field( $field_id )->get();

		// Check for max_filesize in the field settings
		$max_file_size_mb = $field->get_setting( 'max_file_size' );
		if ( ! $max_file_size_mb ) {
			// Use the global setting
			$max_file_size_mb = NF_File_Uploads()->controllers->settings->get_max_file_size_mb();
		}

		$max_file_size = NF_File_Uploads()->controllers->settings->max_filesize( $max_file_size_mb );
		if ( $file['size'] > $max_file_size ) {
			$this->_errors[] = __( 'File exceeds maximum file size. File must be under: ' . $max_file_size_mb . 'MB.', 'ninja-forms-uploads' );

			return false;
		}

		$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );

		// Check for blacklisted file types
		if ( $this->blacklisted( $this->_blacklist, $extension ) ) {
			return false;
		}

		$upload_types = $field->get_setting( 'upload_types' );
		if ( empty( $upload_types ) ) {
			// We aren't restricting file types, bail
			return true;
		}

		$types = str_replace( '.', '', strtolower( $upload_types ) );
		$types = array_map( 'trim', explode( ',', $types ) );

		if ( in_array( 'jpg', $types ) && ! in_array( 'jpeg', $types ) ) {
			$types[] = 'jpeg';
		}

		// Check file extension against whitelist of file extensions
		if ( is_array( $types ) && false === $this->whitelisted( $types, $extension ) ) {
			return false;
		}


		return true;
	}

	/**
	 * Check a file extension against a disallowed list of types
	 *
	 * @param array     $types
	 * @param string    $file_type
	 *
	 * @return bool
	 */
	protected function blacklisted( $types, $file_type) {
		// Check for blacklisted file types
		foreach ( $types as $extension ) {
			if ( strtolower( ltrim( $extension, '.' ) ) === strtolower( $file_type ) ) {
				$this->_errors[] = sprintf( __( 'File extension of %s not allowed', 'ninja-forms-uploads' ), $file_type );

				return true;
			}
		}

		return false;
	}

	/**
	 * Check a file extension against an allowed list of types
	 *
	 * @param array     $types
	 * @param string    $file_type
	 *
	 * @return bool
	 */
	protected function whitelisted( $types, $file_type) {
		// Check for whitelisted file types
		foreach ( $types as $extension ) {
			if ( strtolower( $extension ) === strtolower( $file_type ) ) {
				return true;
			}
		}

		$this->_errors[] = sprintf( __( 'File extension of %s not allowed', 'ninja-forms-uploads' ), $file_type );

		return false;
	}

	/**
	 * Generate temporary filename
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function get_temp_filename( $filename ) {
		$temp_filename = 'nftmp-';
		$temp_filename .= NF_FU_Helper::random_string( 5 ) . '-';
		$temp_filename .= strtolower( sanitize_file_name( $filename ) );

		return $temp_filename;
	}

	/**
	 * Convert $_FILES Error Code to Message
	 *
	 * http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @param $code
	 *
	 * @return string
	 */
	private function code_to_message( $code ) {
		switch ( $code ) {
			case UPLOAD_ERR_INI_SIZE:
				$message = __( "The uploaded file exceeds the upload_max_filesize directive in php.ini", 'ninja-forms-uploads' );
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = __( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 'ninja-forms-uploads' );
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = __( "The uploaded file was only partially uploaded", 'ninja-forms-uploads' );
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = __(  "No file was uploaded", 'ninja-forms-uploads' );
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = __( "Missing a temporary folder", 'ninja-forms-uploads' );
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = __( "Failed to write file to disk", 'ninja-forms-uploads' );
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = __( "File upload stopped by extension", 'ninja-forms-uploads' );
				break;

			default:
				$message = __( "Unknown upload error", 'ninja-forms-uploads' );
				break;
		}

		return $message;
	}
}
