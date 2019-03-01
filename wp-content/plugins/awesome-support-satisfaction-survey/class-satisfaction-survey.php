<?php

/**
 * @package   Awesome Support: Satisfaction Survey
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 */
class WPAS_Satisfaction_Survey {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 *
	 */
	protected static $instance = null;
	/**
	 * Required version of the core.
	 *
	 * The minimum version of the core that's required
	 * to properly run this addon. If the minimum version
	 * requirement isn't met an error message is displayed
	 * and the addon isn't registered.
	 *
	 * @since  0.1.0
	 *
	 * @var    string
	 *
	 */
	protected $version_required = '3.3.3';
	/**
	 * Required version of PHP.
	 *
	 * Follow WordPress latest requirements and require
	 * PHP version 5.4 at least.
	 *
	 * @var string
	 *
	 */
	protected $php_version_required = '5.4';
	/**
	 * Possible error message.
	 *
	 * @var null|WP_Error
	 */
	protected $error = null;

	protected $ticket_id; // = false;

	/**
	 * Plugin slug.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $slug = 'satisfaction_survey';

	/**
	 *  Satisfaction Survey class Constructor
	 */
	public function __construct() {

		$this->init();

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 *
	 */
	public static function get_instance() {

		/**
		 * If the single instance hasn't been set, set it now.
		 */
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Activate the plugin.
	 *
	 * The activation method just checks if the main plugin
	 * Awesome Support is installed (active or inactive) on the site.
	 * If not, the addon installation is aborted and an error message is displayed.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 *
	 */
	public static function activate() {

		if ( ! class_exists( 'Awesome_Support' ) ) {

			deactivate_plugins( basename( __FILE__ ) );

			wp_die( sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'wpas-ss' ), esc_url( 'http://getawesomesupport.com' ) ) );
		}

		wpas_ss_set_default_options();
	}

	/**
	 * Deactivate the plugin.
	 *
	 * If the requirements aren't met we try to
	 * deactivate the addon completely.
	 *
	 * @return void
	 *
	 */
	public function deactivate() {
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}
	}

	/**
	 * Initialize the addon.
	 *
	 * This method is the one running the checks and
	 * registering the addon to the core.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean Whether or not the addon was registered
	 *
	 */
	public function init() {

		$plugin_name = $this->plugin_data( 'Name' );

		if ( ! $this->is_core_active() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'wpas-ss' ), 'Awesome Support: Satisfaction Survey' ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'wpas-pc' ), $plugin_name, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'wpas-pc' ), $plugin_name, $this->version_required ) );
		}

		// Load the plugin translation.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 15 );

		if ( is_a( $this->error, 'WP_Error' ) ) {
			add_action( 'admin_notices', array( $this, 'display_error' ), 10, 0 );
			add_action( 'admin_init', array( $this, 'deactivate' ), 10, 0 );

			return false;
		}

		/**
		 * Add the addon license field
		 */
		if ( is_admin() ) {

			// Add the license admin notice
			$this->add_license_notice();
			add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ), 10, 1 );
			add_filter( 'plugin_row_meta', array( $this, 'license_notice_meta' ), 10, 4 );
		}

		/**
		 * Register the addon
		 */
		wpas_register_addon( $this->slug, array( $this, 'load' ) );

		return true;
	}

	/**
	 * Add a link to the settings page.
	 *
	 * @since  0.1.2
	 *
	 * @param  string[] $links Plugin links
	 *
	 * @return string[]        Links with the settings
	 *
	 */
	public static function settings_page_link( $links ) {
		$link    = add_query_arg( array(
			'post_type' => 'ticket',
			'page'      => 'wpas-settings',
			'tab'       => 'satisfaction_survey',
		), admin_url( 'edit.php' ) );
		$links[] = "<a href='$link'>" . __( 'Settings', 'wpas-ss' ) . "</a>";

		return $links;
	}

	/**
	 * Add license option.
	 *
	 * @since  0.1.0
	 *
	 * @param  array[] $licenses List of addons licenses
	 *
	 * @return array[]           Updated list of licenses
	 *
	 */
	public function addon_license( $licenses ) {

		$licenses[] = array(
			'name'    => __( 'Satisfaction Survey', 'wpas-ss' ),
			'id'      => 'license_satisfaction_survey',
			'type'    => 'edd-license',
			'default' => '',
			'server'  => esc_url( 'https://getawesomesupport.com' ),
			'item_id' => 683949,
			'file'    => WPASSS_PATH . 'awesome-support-satisfaction-survey.php',
		);

		return $licenses;
	}

	/**
	 * Add error.
	 *
	 * Add a new error to the WP_Error object
	 * and create the object if it doesn't exist yet.
	 *
	 * @since  0.1.0
	 *
	 * @param string $message Error message to add
	 *
	 * @return void
	 *
	 */
	public function add_error( $message ) {

		if ( ! is_object( $this->error ) || ! is_a( $this->error, 'WP_Error' ) ) {
			$this->error = new WP_Error();
		}

		$this->error->add( 'addon_error', $message );
	}

	/**
	 * Display error.
	 *
	 * Get all the error messages and display them
	 * in the admin notices.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 *
	 */
	public function display_error() {

		if ( ! is_a( $this->error, 'WP_Error' ) ) {
			return;
		}

		$message = $this->error->get_error_messages();
		?>
		<div class="error">
			<p>
				<?php
				if ( count( $message ) > 1 ) {

					echo '<ul>';

					foreach ( $message as $msg ) {
						echo "<li>$msg</li>";
					}

					echo '</li>';
				} else {
					echo $message[0];
				}
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Get the plugin data.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $data Plugin data to retrieve
	 *
	 * @return string       Data value
	 *
	 */
	protected function plugin_data( $data ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {

			$site_url = get_site_url() . '/';

			if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN && 'http://' === substr( $site_url, 0, 7 ) ) {
				$site_url = str_replace( 'http://', 'https://', $site_url );
			}

			$admin_path = str_replace( $site_url, ABSPATH, get_admin_url() );			

			/** @noinspection PhpIncludeInspection */

			require_once( $admin_path . 'includes/plugin.php' );
		}

		$plugin = get_plugin_data( __FILE__, false, false );

		if ( array_key_exists( $data, $plugin ) ) {
			return $plugin[ $data ];
		} else {
			return '';
		}
	}


	/**
	 * Check if core is active.
	 *
	 * Checks if the core plugin is listed in the acitve
	 * plugins in the WordPress database.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean Whether or not the core is active
	 *
	 */
	protected function is_core_active() {

		return in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );

	}

	/**
	 * Check if the version of PHP is compatible with this addon.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean
	 *
	 */
	protected function is_php_version_enough() {

		/**
		 * No version set, we assume everything is fine.
		 */
		if ( empty( $this->php_version_required ) ) {
			return true;
		}

		return ! version_compare( phpversion(), $this->php_version_required, '<' );

	}

	/**
	 * Check if the core version is compatible with this addon.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean
	 *
	 */
	protected function is_version_compatible() {

		/**
		 * Return true if the core is not active so that this message won't show.
		 * We already have the error saying the plugin is disabled, no need to add this one.
		 */
		if ( ! $this->is_core_active() ) {
			return true;
		}

		if ( empty( $this->version_required ) ) {
			return true;
		}

		if ( ! defined( 'WPAS_VERSION' ) ) {
			return false;
		}

		return ! version_compare( WPAS_VERSION, $this->version_required, '<' );

	}

	/**
	 * Display notice if user didn't set his Envato license code
	 *
	 * @since 0.1.4
	 *
	 * @return void
	 *
	 */
	public function add_license_notice() {

		/**
		 * We only want to display the notice to the site admin.
		 */
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$slug = 'satisfaction-survey';

		$license = wpas_get_option( "license_{$slug}", '' );

		/**
		 * Do not show the notice if the license key has already been entered.
		 */
		if ( ! empty( $license ) ) {
			return;
		}

		$link = wpas_get_settings_page_url( 'licenses' );
		WPAS()->admin_notices->add_notice( 'error', "license_{$slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of the plugin <strong>will never be updated</strong>.', 'wpas-ss' ), $link ) );
	}

	/**
	 * Add license warning in the plugin meta row
	 *
	 * @since 0.1.0
	 *
	 * @param array  $plugin_meta The current plugin meta row
	 * @param string $plugin_file The plugin file path
	 *
	 * @return array Updated plugin meta
	 */
	public function license_notice_meta( $plugin_meta, $plugin_file ) {

		$license = wpas_get_option( "license_{$this->slug}", '' );

		if( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = wpas_get_settings_page_url( 'licenses' );

		if( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', 'wpas-ss' ), $license_page ) . '</strong>';
		}

		return $plugin_meta;

	}

	/**
	 * Load addon styles.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 *
	 */
	public function admin_enqueue_styles() {

		wp_enqueue_script( 'wpasss-admin-script', WPASSS_URL . 'assets/js/admin.js', array(), WPASSS_VERSION, 'all' );

		wp_enqueue_script( 'wpasss-admin-countdown-script', WPASSS_URL . 'vendor/countdown.js', array(), WPASSS_VERSION, 'all' );

		wp_enqueue_style( 'wpasss-admin-style', WPASSS_URL . 'assets/css/admin.css', array(), WPASSS_VERSION, 'all' );

	}

	/**
	 * Load addon script.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 *
	 */
	public function add_script() {

		wp_enqueue_script( 'wpasss-public-script', WPASSS_URL . 'assets/js/public.js', array(), WPASSS_VERSION, 'all' );

		wp_enqueue_style( 'wpasss-public-style', WPASSS_URL . 'assets/css/public.css', array(), WPASSS_VERSION, 'all' );

	}


	/**
	 * Register the metaboxes.
	 *
	 * The function below registers all the metaboxes used
	 * in the ticket edit screen.
	 *
	 * @since 3.0.0
	 *
	 */
	public function metaboxes() {

		add_meta_box( "satisfaction-survey-meta-box", __( 'Satisfaction Survey', 'wpas-ss' ), array(
			$this,
			'metabox_callback',
		), "ticket", "side", "high", array( 'template' => 'satisfaction-survey' ) );

	}

	/**
	 * Metabox callback function.
	 *
	 * The below function is used to call the metaboxes content.
	 * A template name is given to the function. If the template
	 * does exist, the metabox is loaded. If not, nothing happens.
	 *
	 * @param  integer $post Post ID
	 *
	 * @param  array   $args Additional arguments passed to the function
	 *
	 * @return mixed   False if template doesn't exist, null otherwise
	 *
	 * @since  0.1.0
	 *
	 */
	public function metabox_callback( $post, $args ) {

		if ( ! is_array( $args ) || ! isset( $args['args']['template'] ) ) {
			_e( 'An error occured while registering this metabox. Please contact the support.', 'wpas-ss' );
		}

		$template = $args['args']['template'];

		if ( ! file_exists( WPASSS_PATH . "includes/metaboxes/$template.php" ) ) {
			_e( 'An error occured while loading this metabox. Please contact the support.', 'wpas-ss' );
		}

		/* Include the metabox content */
		/** @noinspection PhpIncludeInspection */
		include_once( WPASSS_PATH . "includes/metaboxes/$template.php" );

		return;

	}


	/**
	 * Tickets List Column: Add Rating column
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function set_custom_ticket_columns( $columns ) {

		if ( wpas_get_option( 'ss_display_rating_column' ) ) {
			$columns['rating'] = __( 'Rating', 'wpas-ss' );
		} else {
			unset( $columns['rating'] );
		}

		return $columns;
	}

	/**
	 * Tickets List Column: Display Rating column value
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

		if ( ! wpas_get_option( 'ss_display_rating_column' ) ) {
			return;
		}

		/* If displaying the 'rating' column. */
		if ( 'rating' == $column ) {

			/* Get the post meta. */
			$rating        = get_post_meta( $ticket_id, '_wpasss_rating', true );
			$bad_threshold = wpas_get_option( 'ss_unsatisfied_reasons_dropdown_trigger' );
			$class         = $rating > $bad_threshold ? 'green' : 'red';

			/* If there is a rating, show it. */
			if ( is_numeric( $rating ) ) {
				printf( __( '<span style="color:%s">%s</span>' ), $class, round( $rating, 0 ) );
			} /* If no rating is found, output a default message. */
			else {
				echo __( '—' );
			}

		}

		return;

	}

	/**
	 * Add Sortable Column
	 *
	 * @since  1.0.6
	 *
	 * @param $sortable_columns
	 *
	 * @return mixed
	 */
	public function add_sortable_column( $sortable_columns ) {

		$sortable_columns[ 'rating' ] = 'rating';

		return $sortable_columns;
	}

	/**
	 * Set $orderby
	 *
	 * @since  1.0.6
	 *
	 * @param $query
	 *
	 * @return void
	 */
	public function set_pre_get_posts_orderby( $query ) {

		if ( !is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'rating' == $orderby ) {
			$query->set( 'meta_key', '_wpasss_rating' );
			$query->set( 'orderby', 'meta_value_num' );
		}

	}


	/**
	 * Ticket Re-opened. Cancel any scheduled email invitation.
	 * Clear existing survey if option set.
	 *
	 * @param int $ticket_id
	 *
	 */
	public function ticket_reopened( $ticket_id ) {

		wp_clear_scheduled_hook( 'wpas_ss_send_reminder_email', array( (int) $ticket_id ) );

		/**
		 * Admin setting: Remove existing satisfaction survey on ticket re-open
		 */
		if ( wpas_get_option( 'ss_delete_existing_survey_on_reopen', true ) ) {
			wpasss_remove_survey( $ticket_id );
		}
		
		/**
		 * Admin setting: Allow survey links in agent replies so lets make sure we have a hash on
		 * the ticket just in case something else removed it in the meantime.
		 * Note that we're only going to add it on ticket open if survey links are allowed in agent replies.
		 * Normally its only added on ticket close when an email is sent out with it.
		 */		
		if ( true === boolval( wpas_get_option( 'ss_enable_survey_links_in_agent_replies' , false ) ) ) {
			
			$this->add_hash_to_ticket( $ticket_id );
			
		}
	}

	/**
	 * Ticket Closed - schedule WP_cron() to send email invitation.
	 *
	 * @param int $ticket_id
	 *
	 */
	public function ticket_closed_schedule_invite( $ticket_id ) {
		
		// Clear schedule email if its already been scheduled...
		wp_clear_scheduled_hook( 'wpas_ss_send_reminder_email', array( (int) $ticket_id ) );
		
		// Set hash for redirect url/validation
		$this->add_hash_to_ticket( $ticket_id ) ;
		
		// Schedule email if option to send via email is turned on...
		if ( true === boolval( wpas_get_option( 'ss_type_send_by_email' , true) ) ) {
		
			/**
			 * Clear email sent status
			 */
			delete_post_meta( $ticket_id, '_wpasss_email_invite' );

			/**
			 * Calculate time to send email invitation
			 */
			$start_time_gmt   = time();
			$time_prior_event = wpas_get_option( 'ss_email_send_delay', 24 * 60 ) * 60;
			$reminder_time    = $start_time_gmt + $time_prior_event;

			/**
			 * Schedule the reminder email.
			 */
			wp_schedule_single_event( $reminder_time, 'wpas_ss_send_reminder_email', array( (int) $ticket_id ) );
		
		}
		
		// If immediate redirect is turned on, then redirect here....
		// Hopefully all other processing is complete by now...
		if ( true === boolval( wpas_get_option( 'ss_type_pop_up_after_close' , true) ) ) {
			if( ! is_admin() ) {
				$thash = '?thash=' . get_post_meta( $ticket_id, '_wpasss_hash', true );
				$survey_url = wpasss_get_survey_link( $ticket_id, $thash );
				wp_redirect($survey_url);
				exit ;
			}
		}
	}
	
	/**
	 * New Ticket Opened - add survey hash to ticket - but only if survey links are allowed in agent replies.
	 * Normally the hash only added on ticket close when an email is sent out with it.
	 *
	 * Action Hook: wpas_open_ticket_before_assigned
	 *
	 * @since 1.0.7
	 *
	 * @param int $ticket_id
	 * @param post $data 
	 * @param array $incoming_data original data sent by webform or email
	 *
	 */	
	public function after_ticket_open( $ticket_id, $data, $incoming_data ) {
		
		if ( true === boolval( wpas_get_option( 'ss_enable_survey_links_in_agent_replies' , false ) ) ) {
			
			$this->add_hash_to_ticket( $ticket_id );
			
		}
	}
	
	/**
	 * New reply from agent - add survey hash to ticket if it doesn't already exist...
	 * This is actually only being done in a "just in case" situation.  
	 * The hash should already exist if certain survey links are allowed in agent replies.
	 * Otherwise the hash is normally only added on ticket close when an email is sent out with it.
	 *
	 * Action Hook: wpas_add_reply_admin_after
	 *
	 * @since 1.0.7
	 *
	 * @param int $reply_id - we'll need to convert this to ticket_id!
	 *
	 */	
	function new_reply_from_agent( $reply_id ) {

		// Are survey links are allowed in agent replies? If so, add the hash to the ticket..
		if ( true === boolval( wpas_get_option( 'ss_enable_survey_links_in_agent_replies' , false ) ) ) {
			
			$ticket_id = wpasss_convert_post_id_to_ticket_id( $reply_id );
			
			if ( $ticket_id <> false && true === is_numeric( $ticket_id ) && ! empty( $ticket_id ) ) {
				$this->add_hash_to_ticket( $ticket_id );
			}
			
		}		
		
	}

	/**
	 * Adds the unique hash value for this ticket and url rewrite endpoint to the ticket.
	 *
	 * @param int $ticket_id
	 *
	 * @return void
	 *
	 */	
	 public function add_hash_to_ticket( $ticket_id ) {

		 if ( empty( get_post_meta( $ticket_id, '_wpasss_hash' ) ) ) {
			 // No hash exists in the ticket for SS so lets add on.
			$thash = wpasss_random_hash() ;
			add_post_meta( $ticket_id, '_wpasss_hash', $thash, true );
		 }
	 }
	 
	/**
	 * Gets the unique hash value for this ticket and url rewrite endpoint to the ticket.
	 *
	 * @param int $ticket_id
	 *
	 * @return string
	 *
	 */	
	 public function get_hash_from_ticket( $ticket_id ) {
		 
		 $thash = get_post_meta( $ticket_id, '_wpasss_hash', true );
		 return $thash ;
	 }
	 

	/**
	 * Send scheduled email invitation when wp_cron() event triggers.
	 *
	 * @param int $ticket_id
	 *
	 * @return void
	 *
	 */
	public function send_event_reminder_email( $ticket_id ) {

		/**
		 *  Remove existing cron event for this post if one exists
		 */
		wp_clear_scheduled_hook( 'wpas_ss_send_reminder_email', array( (int) $ticket_id ) );

		wpasss_remove_survey( $ticket_id );
		
		/**
		* Make sure there is a hash in the ticket
		*/
		$this->add_hash_to_ticket( $ticket_id );

		/**
		 *  Don't send the email more than once.
		 */
		if ( get_post_meta( $ticket_id, '_wpasss_email_invite', true ) === true ) {
			return;
		}

		$this->ticket_id = $ticket_id;

		/** Schedule satisfaction survey invitation email to be sent. */
		if ( wpas_email_notify( $ticket_id, 'satisfaction_survey' ) ) {
			add_post_meta( $ticket_id, '_wpasss_email_invite', true, true );
		}

	}

	/**
	 * Cancel a scheduled wp_cron() single event invitation email.
	 *
	 * @param string $ticket_id
	 *
	 */
	public function cancel_event_reminder_email( $ticket_id ) {

		delete_post_meta( $ticket_id, '_wpasss_hash' );
		delete_post_meta( $ticket_id, '_wpasss_email_invite' );

		/**
		 *  Remove existing cron event for this post if one exists
		 */
		wp_clear_scheduled_hook( 'wpas_ss_send_reminder_email', array( (int) $ticket_id ) );
	}

	/**
	 * Returns rating scale HTML.
	 * Used by admin metabox, frontend survey form and email invitations.
	 *
	 * @param string $disabled
	 *
	 * @param string $ticket_id
	 *
	 * @return string
	 *
	 */
	public function render_survey_field( $disabled = '', $ticket_id = '' ) {

		$html = '<div id="rating_form">';

		if ( $disabled === '' ) {
			$html .= '<div style="margin: 0 0 20px 0;">';
			$html .= __( 'How would you rate the support you received:', 'wpas-ss' );
			$html .= '</div>';
		}

		$scale      = (int) wpas_get_option( 'ss_rating_scale', 2 );
		$scale_step = (float) ( 100 / ( $scale - 1 ) );

		switch ( $scale ) {
			case 2: {
				$scale_step = (float) 50;
				break;
			}
			case 5: {
				$scale_step = (float) 20;
				break;
			}
			case 10: {
				$scale_step = (float) 10;
				break;
			}
		}

		$html .= '<div class="wrapper_rating_choices">';
		$html .= '<div class="rating_choices scale_' . $scale . '">';
		$html .= '<div class="scale_label_bad">' . __( wpas_get_option( 'ss_bad_label', "Bad" ), 'wpas-ss' ) . '</div>';

		for ( $i = 1; $i <= $scale; $i ++ ) {
			$html .= $this->survey_input_radio( $i * $scale_step, $this->is_checked( $ticket_id, $scale_step, $i ), $disabled );
		}

		$html .= '<div class="scale_label_good">' . __( wpas_get_option( 'ss_good_label', "Good" ), 'wpas-ss' ) . '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;

	}

	/**
	 * Create HTML Radio Button
	 *
	 * @param $value
	 *
	 * @param $disabled
	 *
	 * @param $checked
	 *
	 * @return string
	 */
	public function survey_input_radio( $value, $disabled, $checked ) {

		$dropdown_trigger = wpas_get_option( 'ss_unsatisfied_reasons_dropdown_trigger', '50' );

		$html = '<input id="wpasss_rating" class="scale_input_radio" type="radio" name="wpasss_rating" ';
		$html .= 'value="' . $value . '" ';
		$html .= 'data-threshold="' . $dropdown_trigger . '" ';
		$html .= $checked . ' ';
		$html .= $disabled . ' ';
		$html .= ' />';

		return $html;

	}

	/**
	 * Returns input radio checked status
	 *
	 * @param string $ticket_id
	 *
	 * @param float  $scale_step
	 *
	 * @param int    $i
	 *
	 * @return string
	 */
	public function is_checked( $ticket_id, $scale_step, $i ) {

		if ( $ticket_id !== '' ) {
			$rating     = (float) get_post_meta( $ticket_id, '_wpasss_rating', true );
			$rating     = round( $rating, 2 );
			$scale_step = round( $scale_step, 2 );

			$iss = $i * $scale_step;
			if ( $iss > 100 ) {
				$iss = 100;
			}

			if ( $rating == $iss ) {
				return 'checked';
			} elseif ( $rating < ( ( $i + 1 ) * $scale_step ) && $rating > $iss ) {
				return 'checked';
			}
		}

		return '';

	}


	/**
	 * Email Notification: Enable Satisfaction Survey
	 *
	 * @since  0.1.3
	 *
	 * @param $cases
	 *
	 * @return string[]
	 *
	 */
	public function email_notifications_cases( $cases ) {

		if ( false !== $this->ticket_id && ! in_array( 'satisfaction_survey', $cases ) ) {
			$cases[] = 'satisfaction_survey';
		}

		return $cases;

	}

	/**
	 * Returns email notification template status
	 *
	 * @param $option
	 * @param $case
	 *
	 * @return bool
	 */
	public function email_notifications_case_is_active( $option, $case ) {

		if ( 'satisfaction_survey' === $case && false === $option ) {
			$option = true;
		}

		return $option;

	}

	/**
	 * Email Notification: Enable Satisfaction Survey
	 *
	 * @since  0.1.3
	 *
	 * @param $cases
	 *
	 * @return array<string,string>
	 *
	 */
	public function email_notifications_cases_active_option( $cases ) {

		if ( ! array_key_exists( 'satisfaction_survey', $cases ) ) {
			$cases['satisfaction_survey'] = 'enable_satisfaction_survey';
		}

		return $cases;

	}

	/**
	 * Email Notification: Translate template tags
	 *
	 * @param $tags
	 *
	 * @return array[]
	 *
	 */
	public function email_notifications_template_tags( $tags ) {

		$tags[] = array(
			'tag'  => '{satisfaction_survey_url}',
			'desc' => __( 'Displays the URL <strong>only</strong> (not an actual html link) to the survey', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_link}',
			'desc' => __( 'Displays a link to satisfaction survey', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_field}',
			'desc' => __( 'Displays a graphical link to satisfaction survey', 'wpas-ss' ),
		);
		
		$tags[] = array(
			'tag'  => '{satisfaction_survey_thumbs_up_url}',
			'desc' => __( 'Displays the URL <strong>only</strong> (not an actual html link) that, when clicked, will assign a thumbs up 100% satisfaction rating.', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_thumbs_up_link}',
			'desc' => __( 'Displays a link that, when clicked, will assign a 100% satisfaction rating for the survey', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_thumbs_up_field}',
			'desc' => __( 'Displays a graphical link for a 100% satisfaction rating (for future use only!)', 'wpas-ss' ),
		);
		
		$tags[] = array(
			'tag'  => '{satisfaction_survey_thumbs_down_url}',
			'desc' => __( 'Displays the URL <strong>only</strong> (not an actual html link) that, when clicked, will assign a thumbs down 0% satisfaction rating to the ticket.', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_thumbs_down_link}',
			'desc' => __( 'Displays a link  that, when clicked, will assign a thumbs down 0% satisfaction rating to the ticket', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_thumbs_down_field}',
			'desc' => __( 'Displays a graphical link for a 0% satisfaction rating (for future use only!)', 'wpas-ss' ),
		);
		
		$tags[] = array(
			'tag'  => '{satisfaction_survey_close_ticket_with_thumbs_up_url}',
			'desc' => __( 'Displays the URL <strong>only</strong> (not an actual html link) that will close a ticket and assign a 100% satisfaction rating. Suitable for use on ALL emails from agents.', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_close_ticket_with_thumbs_up_link}',
			'desc' => __( 'Displays a link that will close a ticket and assign a 100% satisfaction rating for the survey. Suitable for use on ALL emails from agents.', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_close_ticket_with_thumbs_up_field}',
			'desc' => __( 'Displays a graphical link for a 100% satisfaction rating (for future use only!)', 'wpas-ss' ),
		);
		
		$tags[] = array(
			'tag'  => '{satisfaction_survey_close_ticket_with_thumbs_down_url}',
			'desc' => __( 'Displays the URL <strong>only</strong> (not an actual html link) that will close a ticket and assign a 0% satisfaction rating. Suitable for use on ALL emails from agents.', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_close_ticket_with_thumbs_down_link}',
			'desc' => __( 'Displays a link that will close a ticket and assign a 0% satisfaction rating for the survey. Suitable for use on ALL emails from agents.', 'wpas-ss' ),
		);
		$tags[] = array(
			'tag'  => '{satisfaction_survey_close_ticket_with_thumbs_down_field}',
			'desc' => __( 'Displays a graphical link for a 0% satisfaction rating (for future use only!)', 'wpas-ss' ),
		);		
		

		return $tags;

	}

	/**
	 * Email Notification: Tag Values
	 *
	 * @since  0.1.3
	 *
	 * @param $new
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 *
	 */
	public function email_notifications_tags_values( $new, $post_id ) {
		
		// @TODO
		//@optimization potential - check to see if "satisfaction" of any kind is in the $new array before processing...
		// Extract that and then process the for-each loop below.
		// Prevents a ton of unwanted processing otherwise I think...

		
		$ticket_id = wpasss_convert_post_id_to_ticket_id( $post_id ) ;  // Needed in case the $post_id is a reply instead of a ticket...	
		
		$thash      = '?thash=' . get_post_meta( $ticket_id, '_wpasss_hash', true );
		$survey_url = wpasss_get_survey_link( $ticket_id, $thash );
		$survey_thumbs_up_url = wpasss_get_survey_thumbs_up_link( $ticket_id, $thash );
		$survey_thumbs_down_url = wpasss_get_survey_thumbs_down_link( $ticket_id, $thash );

		$survey_click_to_close_thumbs_up_url = wpasss_get_survey_click_to_close_with_thumbs_up_link( $ticket_id, $thash );
		$survey_click_to_close_thumbs_down_url = wpasss_get_survey_click_to_close_with_thumbs_down_link( $ticket_id, $thash );		
		
		foreach ( $new as $key => $tag ) {

			$name       = trim( $tag['tag'], '{}' );

			switch ( $name ) {

				case 'satisfaction_survey_link':
					$tag['value'] = '<a href="' . $survey_url . '">' . __( 'Satisfaction Survey', 'wpas-ss' ) . '</a>';
					break;

				case 'satisfaction_survey_url':
					$tag['value'] = $survey_url;
					break;

				case 'satisfaction_survey_field':
					$tag['value'] = '<a href="' . $survey_url . '">' . $this->render_survey_field( 'disabled' ) . '</a>';
					break;

					
				case 'satisfaction_survey_thumbs_up_link':
					$tag['value'] = '<a href="' . $survey_thumbs_up_url . '">' . __( 'Thumbs Up - I am 100% satisified!', 'wpas-ss' ) . '</a>';
					break;

				case 'satisfaction_survey_thumbs_up_url':
					$tag['value'] = $survey_thumbs_up_url;
					break;

				case 'satisfaction_survey_thumbs_up_field':
					$tag['value'] = '<a href="' . $survey_url . '">' . $this->render_survey_field( 'disabled' ) . '</a>';
					break;
					
					
				case 'satisfaction_survey_thumbs_down_link':
					$tag['value'] = '<a href="' . $survey_thumbs_down_url . '">' . __( 'Thumbs Down - I am not satisified!', 'wpas-ss' ) . '</a>';
					break;

				case 'satisfaction_survey_thumbs_down_url':
					$tag['value'] = $survey_thumbs_down_url;
					break;

				case 'satisfaction_survey_thumbs_down_field':
					$tag['value'] = '<a href="' . $survey_url . '">' . $this->render_survey_field( 'disabled' ) . '</a>';
					break;
					
					
				case 'satisfaction_survey_close_ticket_with_thumbs_up_link':
					$tag['value'] = '<a href="' . $survey_click_to_close_thumbs_up_url . '">' . __( 'Close ticket - I am 100% satisfied', 'wpas-ss' ) . '</a>';
					break;

				case 'satisfaction_survey_close_ticket_with_thumbs_up_url':
					$tag['value'] = $survey_click_to_close_thumbs_up_url;
					break;

				case 'satisfaction_survey_close_ticket_with_thumbs_up_field':
					$tag['value'] = '<a href="' . $survey_url . '">' . $this->render_survey_field( 'disabled' ) . '</a>';
					break;
					
					
				case 'satisfaction_survey_close_ticket_with_thumbs_down_link':
					$tag['value'] = '<a href="' . $survey_click_to_close_thumbs_down_url . '">' . __( 'Close ticket - I am not satisified with your service', 'wpas-ss' ) . '</a>';
					break;

				case 'satisfaction_survey_close_ticket_with_thumbs_down_url':
					$tag['value'] = $survey_click_to_close_thumbs_down_url;
					break;

				case 'satisfaction_survey_close_ticket_with_thumbs_down_field':
					$tag['value'] = '<a href="' . $survey_url . '">' . $this->render_survey_field( 'disabled' ) . '</a>';
					break;					
					
			}

			$new[ $key ] = $tag;
		}

		return $new;

	}

	/**
	 * Email Notification: Email Message Template
	 *
	 * @param string $value     The e-mail content
	 * @param int    $ticket_id The ticket ID
	 * @param string $case      The current case being executed
	 *
	 * @return string
	 */
	public function email_notifications_pre_fetch_content( $value, $ticket_id, $case ) {

		if ( 'satisfaction_survey' === $case ) {
			$value = wpas_get_option( 'content_satisfaction_survey_email_message' );
		}

		return $value;

	}

	/**
	 * Email Notification: Email Subject
	 *
	 * @since  0.1.3
	 *
	 * @param string $value     The e-mail subject
	 * @param int    $ticket_id The ticket ID
	 * @param string $case      The case being executed
	 *
	 * @return string
	 */
	public function email_notifications_pre_fetch_subject( $value, $ticket_id, $case ) {

		if ( 'satisfaction_survey' === $case ) {
			$value = wpas_get_option( 'subject_satisfaction_survey_email_message' );
		}

		return $value;

	}

	/**
	 * Email Notification: Recipient Email
	 *
	 * @since  0.1.3
	 *
	 * @param $email
	 *
	 * @param $case
	 *
	 * @param $ticket_id
	 *
	 * @return mixed
	 *
	 */
	public function email_notifications_notify_user( $user, $case, $ticket_id ) {

		if ( $case == 'satisfaction_survey' ) {

			if ( 'ticket' !== get_post_type( $ticket_id ) ) {
				return $user;
			}

			$ticket = get_post( $ticket_id );
			$user   = get_user_by( 'id', $ticket->post_author );

		}

		return $user;
	}

	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * With the introduction of plugins language packs in WordPress loading the textdomain is slightly more complex.
	 *
	 * We now have 3 steps:
	 *
	 * 1. Check for the language pack in the WordPress core directory
	 * 2. Check for the translation file in the plugin's language directory
	 * 3. Fallback to loading the textdomain the classic way
	 *
	 * @since   1.0.5
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function load_plugin_textdomain() {

		$lang_dir       = WPASSS_ROOT . 'languages/';
		$lang_path      = WPASSS_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'wpas-ss' );
		$mofile         = "wpas-ss-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/awesome-support-satisfaction-survey/' . $mofile;

		// Look for the GlotPress language pack first of all
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'wpas-ss', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'wpas-ss', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'wpas-ss', false, $lang_dir );
		}

		return $language;

	}

		/**
		 * Load the addon.
		 *
		 * Include all necessary files and instantiate the addon.
		 *
		 * @since  0.1.0
		 * @return void
		 */
	public function load() {

		/** @noinspection PhpIncludeInspection */
		require_once( WPASSS_PATH . 'includes/functions-rewrite.php' );
		/** @noinspection PhpIncludeInspection */
		require_once( WPASSS_PATH . 'includes/functions-survey.php' );

		// Load the plugin translation.
		//add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 15 );

		add_action( 'wpas_ss_send_reminder_email', array( $this, 'send_event_reminder_email' ), 100, 1 );

		add_filter( 'wpas_email_notifications_cases', array( $this, 'email_notifications_cases' ), 100, 1 );

		add_filter( 'wpas_email_notifications_case_is_active', array( $this, 'email_notifications_case_is_active' ), 10, 2 );

		add_filter( 'wpas_email_notifications_cases_active_option', array( $this, 'email_notifications_cases_active_option' ), 10, 1 );

		add_filter( 'wpas_email_notifications_template_tags', array( $this, 'email_notifications_template_tags' ), 10, 1 );

		add_filter( 'wpas_email_notifications_tags_values', array( $this, 'email_notifications_tags_values' ), 10, 2 );

		add_filter( 'wpas_email_notifications_notify_user', array( $this, 'email_notifications_notify_user' ), 10, 3 );

		add_filter( 'wpas_email_notifications_pre_fetch_content', array( $this, 'email_notifications_pre_fetch_content' ), 10, 3 );

		add_filter( 'wpas_email_notifications_pre_fetch_subject', array( $this, 'email_notifications_pre_fetch_subject' ), 10, 3 );


		if( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			add_filter( 'wpas_addons_licenses', array( $this, 'addon_license' ), 10, 1 );

			add_action( 'wpas_after_close_ticket', array( $this, 'ticket_closed_schedule_invite' ), 10, 1 );

			add_action( 'wpas_after_reopen_ticket', array( $this, 'ticket_reopened' ), 90, 1 );
			
			add_action( 'wpas_open_ticket_before_assigned', array( $this, 'after_ticket_open' ), 90, 3 );

			add_action( 'wpas_add_reply_admin_after',	array( $this, 'new_reply_from_agent' ), 90, 2 );    // New reply from the agent			

			if( is_admin() ) {

				/**
				 * Create/Display Rating column
				 */
				add_filter( 'manage_ticket_posts_columns', array( $this, 'set_custom_ticket_columns' ) );

				add_action( 'manage_ticket_posts_custom_column', array( $this, 'get_custom_ticket_column' ), 20, 2 );

				add_filter( 'manage_edit-ticket_sortable_columns', array( $this, 'add_sortable_column' ), 10, 1 );

				add_action( 'pre_get_posts', array( $this, 'set_pre_get_posts_orderby' ), 10, 1 );


				if( isset( $_GET[ 'post' ] ) && 'ticket' === get_post_type( intval( $_GET[ 'post' ] ) ) ) {

					add_action( 'add_meta_boxes', array( $this, 'metaboxes' ), 10, 0 );

					add_action( 'load-post-new.php', array( $this, 'metaboxes' ), 10, 0 );

					add_action( 'load-post.php', array( $this, 'metaboxes' ), 10, 0 );

					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 0 );
				}
			}
			else {

				add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ), 11, 0 );
			}
		}

	}

}
