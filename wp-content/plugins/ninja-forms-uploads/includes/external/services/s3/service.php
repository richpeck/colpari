<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_External_S3_Service
 */
class NF_FU_External_Services_S3_Service extends NF_FU_External_Abstracts_Service {

	public $name = 'Amazon S3';

	protected $library_class = 'S3';

	protected $library_file = 'tpyo/amazon-s3-php-class/s3.php';

	protected static $clients = array();

	const DEFAULT_REGION = 'us-east-1';
	const AWS_SIGNATURE = 'v4';
	const S3_API_VERSION = '2006-03-01';

	public function get_notices() {
		$notices = array();
		if ( $this->is_aws_plugin_activated() ) {
			return $notices;
		}

		$label = 'install';
		$slug = 'amazon-web-services';
		if ( $this->is_aws_plugin_installed() ) {
			$label = 'activate';
			$slug = 'amazon-web-services/amazon-web-services.php';
		}
		$url = $this->install_link( $label, $slug);

		$message   = sprintf( __( '<strong>Amazon S3</strong> &mdash; To make use of new regions please <a href="%s">%s</a> the free <a href="https://wordpress.org/plugins/amazon-web-services/" target="_blank">Amazon Web Services</a> plugin.', 'ninja-forms-uploads' ), $url, $label );
		$notices[] = array( 'type' => 'notice notice-info', 'message' => $message );

		return $notices;
	}

	protected function install_link( $action, $basename ) {
		$nonce_action = $action . '-plugin_' . $basename;
		$page         = 'plugins';

		if ( in_array( $action, array( 'upgrade', 'install' ) ) ) {
			$page = 'update';
			$action .= '-plugin';
		}

		$url = wp_nonce_url( network_admin_url( $page . '.php?action=' . $action . '&amp;plugin=' . $basename ), $nonce_action );

		return $url;
	}

	protected function is_aws_plugin_activated() {
		return ( class_exists( 'AWS_Compatibility_Check' ) && function_exists( 'amazon_web_services_require_files' ) );
	}

	protected function is_aws_plugin_installed() {
		return file_exists( WP_PLUGIN_DIR . '/amazon-web-services/amazon-web-services.php');
	}

	/**
	 * Wrapper for getting the S3 client
	 *
	 * @param      $amazon_s3_access_key
	 * @param      $amazon_s3_secret_key
	 * @param null $region
	 *
	 * @return NF_FU_Library_S3
	 */
	protected function get_s3client( $amazon_s3_access_key, $amazon_s3_secret_key, $region = null ) {
		if ( ! $this->is_aws_plugin_installed() || ! $this->is_aws_plugin_activated() ) {
			$s3 = new NF_FU_Library_S3( $amazon_s3_access_key, $amazon_s3_secret_key );
			if ( $region && '' !== $region && 'US' !== $region && 'us-east-1' !== $region ) {
				// Use the correct API endpoint for non US standard bucket regions
				$s3->setEndpoint( 's3-' . $region . '.amazonaws.com' );
			}

			$s3->legacy = true;

			return $s3;
		}

		amazon_web_services_require_files();

		$aws_args = array(
			'key'    => $amazon_s3_access_key,
			'secret' => $amazon_s3_secret_key,
		);
		$class = '\Aws\Common\Aws';
		$client = $class::factory( $aws_args );

		$args = array(
			'version' => self::S3_API_VERSION,
		);

		if ( $region ) {
			$args['region']    = $this->translate_region( $region );
			$args['signature'] = self::AWS_SIGNATURE;
		}

		return $client->get( 's3', $args );
	}

	/**
	 * Translate older bucket locations to newer S3 region names
	 * http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region
	 *
	 * @param $region
	 *
	 * @return string
	 */
	protected  function translate_region( $region ) {
		if ( ! is_string( $region ) ) {
			// Don't translate any region errors
			return $region;
		}

		$region = strtolower( $region );

		switch ( $region ) {
			case 'eu':
				$region = 'eu-west-1';
				break;
		}

		return $region;
	}

	/**
	 * Is the service connected?
	 *
	 * @param null|array $settings
	 *
	 * @return bool
	 */
	public function is_connected( $settings = null ) {
		if ( is_null( $settings ) ) {
			$settings = $this->load_settings();
		}

		foreach ( $settings as $key => $value ) {
			if ( 'amazon_s3_file_path' === $key ) {
				continue;
			}

			if ( ! is_array( $value ) && '' === trim( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Load S3 settings and ensure we have the region for the bucket
	 *
	 * @return array
	 */
	public function load_settings() {
		$settings = parent::load_settings();

		if ( ! $this->is_connected( $settings ) ) {
			return $settings;
		}

		$bucket = $settings['amazon_s3_bucket_name'];

		$data = NF_File_Uploads()->controllers->settings->get_settings();

		if ( ( ! isset( $data['amazon_s3_bucket_region'][ $bucket ] ) || empty( $data['amazon_s3_bucket_region'][ $bucket ] ) ) ) {
			// Retrieve the bucket region if we don't have it
			// Or the bucket has changed since we last retrieved it
			$s3     = $this->get_s3client( $settings['amazon_s3_access_key'], $settings['amazon_s3_secret_key'] );
			$region = $this->get_bucket_region( $s3, $bucket );
			if ( false === $region ) {
				return $this->settings;
			}

			$this->settings['amazon_s3_bucket_region'][ $bucket ] = $region;

			$data['amazon_s3_bucket_region'] = $this->settings['amazon_s3_bucket_region'];
			update_option( 'ninja_forms_settings', $data );
		}

		return $this->settings;
	}

	/**
	 * Get the S3 client
	 *
	 * @param string $region
	 *
	 * @return NF_FU_Library_S3
	 */
	protected function get_client( $region = '' ) {
		if ( '' === $region || 'US' === $region) {
			$region = 'us-east-1';
		}

		if ( ! isset( self::$clients[ $region ] ) ) {

			$this->load_settings();

			$s3 = $this->get_s3client( $this->settings['amazon_s3_access_key'], $this->settings['amazon_s3_secret_key'], $region );

			self::$clients[ $region ] = $s3;
		}

		return self::$clients[ $region ];
	}

	protected function get_bucket_region( $s3, $bucket ) {
		if ( isset( $s3->legacy ) ) {
			return $s3->getBucketLocation( $bucket );
		}

		try {
			$region = $s3->getBucketLocation( array( 'Bucket' => $bucket ) );
		} catch ( Exception $e ) {
			error_log( sprintf( __( 'There was an error attempting to get the region of the bucket %s: %s', 'ninja-forms-uploads' ), $bucket, $e->getMessage() ) );


			return false;
		}

		return $this->translate_region( $region['Location'] );
	}

	/**
	 * Get region of configured bucket
	 *
	 *
	 * @return string
	 */
	protected function get_region() {
		$bucket = $this->settings['amazon_s3_bucket_name'];
		$data   = NF_File_Uploads()->controllers->settings->get_settings();
		$region = isset( $data['amazon_s3_bucket_region'][ $bucket ] ) ? $data['amazon_s3_bucket_region'][ $bucket ] : '';

		return $region;
	}

	/**
	 * Get path on S3 to upload to
	 *
	 * @return string
	 */
	protected function get_path_setting() {
		return 'amazon_s3_file_path';
	}

	/**
	 * Upload the file to S3
	 *
	 * @param array $data
	 *
	 * @return array|bool
	 */
	public function upload_file( $data ) {
		$bucket = $this->settings['amazon_s3_bucket_name'];
		$region = $this->get_region();

		$result = $this->upload_file_to_s3( $bucket, $region, $this->upload_file, $this->external_path . $this->external_filename, NF_FU_Library_S3::ACL_PUBLIC_READ );

		if ( false === $result ) {
			return false;
		}

		$data['bucket'] = $bucket;
		$data['region'] = $region;

		return $data;
	}

	/**
	 * Wrapper for uploading to S3
	 *
	 * @param $bucket
	 * @param $region
	 * @param $file
	 * @param $key
	 * @param $acl
	 *
	 * @return bool
	 */
	protected function upload_file_to_s3( $bucket, $region, $file, $key, $acl ) {
		$s3 = $this->get_client( $region );
		if ( isset( $s3->legacy ) ) {
			try {
				$s3->putObjectFile( $file, $bucket, $key, $acl );
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );

				return false;
			}

			return true;
		}

		try {
			$s3->putObject( array(
				'Bucket'     => $bucket,
				'Key'        => $key,
				'SourceFile' => $file,
				'ACL'        => $acl,
			) );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );

			return false;
		}

		return true;
	}

	/**
	 * Get the Amazon S3 URL using bucket and region for the file, falling
	 * back to the settings bucket and region
	 *
	 * @param string $filename
	 * @param string $path
	 * @param array  $data
	 *
	 * @return string
	 */
	public function get_url( $filename, $path = '', $data = array() ) {
		$bucket = ( isset( $data['bucket'] ) ) ? $data['bucket'] : $this->settings['amazon_s3_bucket_name'];
		$region = ( isset( $data['region'] ) ) ? $data['region'] : $this->get_region();

		$s3 = $this->get_client( $region );

		return $this->get_s3_url( $s3, $bucket, $path . $filename, 3600 );
	}

	/**
	 * Wrapper for getting S3 URL
	 *
	 * @param $s3
	 * @param $bucket
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function get_s3_url( $s3, $bucket, $key ) {
		$expires = apply_filters( 'ninja_forms_uploads_s3_expires', 3600 );

		if ( isset( $s3->legacy ) ) {
			return $s3->getAuthenticatedURL( $bucket, $key, 3600 );
		}

		$expires = time() + $expires;

		return $s3->getObjectUrl( $bucket, $key, $expires );
	}
}
