<?php
/**
 * @package   Awesome Support Auto Close
 * @author    AwesomeSupport
 * @link      http://www.getawesomesupport.com
 *
 * @wordpress-plugin
 * Plugin Name:       Awesome Support: Auto Close
 * Description:       Send warning messages and auto close tickets.
 * Version:           1.0.4
 * Author:            AwesomeSupport
 * Text Domain:       wpascr
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Instantiate the plugin
 *----------------------------------------------------------------------------*/
/**
 * Register the activation hook
 */

register_activation_hook( __FILE__, array( 'WPASAC_AutoClose_Loader', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPASAC_AutoClose_Loader', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'WPASAC_AutoClose_Loader', 'get_instance' ) );
/**
 * Instanciate the addon.
 *
 * This method runs a few checks to make sure that the addon
 * can indeed be used in the current context, after what it
 * registers the addon to the core plugin for it to be loaded
 * when the entire core is ready.
 *
 * @since  0.1.0
 * @return void
 */
class WPASAC_AutoClose_Loader {

	/**
	 * ID of the item.
	 *
	 * The item ID must match teh post ID on the e-commerce site.
	 * Using the item ID instead of its name has the huge advantage of
	 * allowing changes in the item name.
	 *
	 * If the ID is not set the class will fall back on the plugin name instead.
	 *
	 * @since 0.1.3
	 * @var int
	 */
	protected $item_id = 690191;

	/**
	 * Required version of the core.
	 *
	 * The minimum version of the core that's required
	 * to properly run this addon. If the minimum version
	 * requirement isn't met an error message is displayed
	 * and the addon isn't registered.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $version_required = '4.1.0';

	/**
	 * Required version of PHP.
	 *
	 * @var string
	 */
	protected $php_version_required = '5.6';

	/**
	 * Plugin slug.
	 *
	 * @since  0.1.0
	 * @var    string
	 */
	protected $slug = 'wpass_autoclose';

	/**
	 * Instance of this loader class.
	 *
	 * @since    0.1.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of the addon itself.
	 *
	 * @since  0.1.0
	 * @var    object
	 */
	public $addon = null;

	/**
	 * Possible error message.
	 * 
	 * @var null|WP_Error
	 */
	protected $error = null;

	/**
	 * Holds the custom WP Cron recurrences
	 *
	 * @since 1.0
	 * @var array
	 */
	public $cron_recurrences = array();

	/**
	 * Holds the addon settings page TF instance
	 *
	 * @since 1.0
	 * @var null|TitanFrameworkAdminPage
	 */
	public $container = null;

	public function __construct() {

		$this->declare_constants();

		$this->cron_recurrences = array(
			'every5min'    => array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 5 Minutes', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'every10min'   => array(
				'interval' => 10 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 10 Minutes', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'every20min'   => array(
				'interval' => 20 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 20 Minutes', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'every30min'   => array(
				'interval' => 30 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 30 Minutes', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'hourly'       => array(
				'interval' => HOUR_IN_SECONDS,
				'display'  => __( 'Once Hourly', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'every2ndhour' => array(
				'interval' => 2 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 2nd Hour', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'every4thhour' => array(
				'interval' => 4 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 4th Hour', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'every6thhour' => array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display'  => __( 'Every 6th Hour', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'twicedaily'   => array(
				'interval' => 12 * HOUR_IN_SECONDS,
				'display'  => __( 'Twice Daily', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
			'daily'        => array(
				'interval' => DAY_IN_SECONDS,
				'display'  => __( 'Once Daily', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
			),
		);

		$this->init();

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
	 * Return an instance of the addon.
	 *
	 * @since  0.1.0
	 * @return object
	 */
	public function scope() {
		return $this->addon;
	}

	/**
	 * Declare the addon constants
	 *
	 * @since 1.0
	 * @return void
	 */
	public function declare_constants() {
		define( 'WPASS_AUTOCLOSE_VERSION', '1.0.4' );
		define( 'WPASS_AUTOCLOSE_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'WPASS_AUTOCLOSE_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'WPASS_AUTOCLOSE_ROOT', trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
		define( 'WPASS_AUTOCLOSE_TEXT_DOMAIN', $this->slug );
	}

	/**
	 * Activate the plugin.
	 *
	 * The activation method just checks if the main plugin
	 * Awesome Support is installed (active or inactive) on the site.
	 * If not, the addon installation is aborted and an error message is displayed.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public static function activate() {

		if ( ! class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', WPASS_AUTOCLOSE_TEXT_DOMAIN ), esc_url( 'http://getawesomesupport.com/?utm_source=internal&utm_medium=addon_loader&utm_campaign=Addons' ) )
			);
		}

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . "ac_warning_messages";

		$table = "CREATE TABLE IF NOT EXISTS {$table_name} (
                    `id` int(11) AUTO_INCREMENT primary key NOT NULL,
                    `status` varchar(100) NOT NULL,
                    `age` int(11) NOT NULL,
                    `message` text,
                    `close` int(11) NOT NULL DEFAULT '0'
                ) ENGINE=InnoDB;";
		dbDelta( $table );

		$existing_columns = $wpdb->get_col( "DESC $table_name", 0 );
		if ( ! in_array( 'subject', $existing_columns ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD `subject` varchar(100) NULL" );
		}

	}

	/**
	 * Initialize the addon.
	 *
	 * This method is the one running the checks and
	 * registering the addon to the core.
	 *
	 * @since  0.1.0
	 * @return boolean Whether or not the addon was registered
	 */
	public function init() {

		if ( ! $this->is_core_active() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support to be active. Please activate the core plugin first.', 'wpas-boilerplate' ), WPASS_AUTOCLOSE_TEXT_DOMAIN ) );
		}

		if ( ! $this->is_php_version_enough() ) {
			$this->add_error( sprintf( __( 'Unfortunately, %s can not run on PHP versions older than %s. Read more information about <a href="%s" target="_blank">how you can update</a>.', 'wpas-boilerplate' ), WPASS_AUTOCLOSE_TEXT_DOMAIN, $this->php_version_required, esc_url( 'http://www.wpupdatephp.com/update/' ) ) );
		}

		if ( ! $this->is_version_compatible() ) {
			$this->add_error( sprintf( __( '%s requires Awesome Support version %s or greater. Please update the core plugin first.', 'wpas-boilerplate' ), WPASS_AUTOCLOSE_TEXT_DOMAIN, $this->version_required ) );
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

		$this->register_custom_fields();

		return true;

	}

	/**
	 * Get the plugin data.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $data Plugin data to retrieve
	 *
	 * @return string       Data value
	 */
	protected function plugin_data( $data ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			$site_url = get_site_url() . '/';

			if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN && 'http://' === substr( $site_url, 0, 7 ) ) {
				$site_url = str_replace( 'http://', 'https://', $site_url );
			}

			$admin_path = str_replace( $site_url, ABSPATH, get_admin_url() );
			
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
	 * Checks if the core plugin is listed in the active
	 * plugins in the WordPress database.
	 *
	 * @since  0.1.0
	 * @return boolean Whether or not the core is active
	 */
	protected function is_core_active() {
		if ( in_array( 'awesome-support/awesome-support.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the core version is compatible with this addon.
	 *
	 * @since  0.1.0
	 * @return boolean
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
		if ( version_compare( WPAS_VERSION, $this->version_required, '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the version of PHP is compatible with this addon.
	 *
	 * @since  0.1.0
	 * @return boolean
	 */
	protected function is_php_version_enough() {

		/**
		 * No version set, we assume everything is fine.
		 */
		if ( empty( $this->php_version_required ) ) {
			return true;
		}

		if ( version_compare( phpversion(), $this->php_version_required, '<' ) ) {
			return false;
		}

		return true;
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
	 * @return void
	 */
	public function display_error() {
		if ( ! is_a( $this->error, 'WP_Error' ) ) {
			return;
		}
		$message = $this->error->get_error_messages(); ?>
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
	 * Deactivate the addon.
	 *
	 * If the requirements aren't met we try to
	 * deactivate the addon completely.
	 *
	 * @return void
	 */
	public static function deactivate() {

		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}

		wp_clear_scheduled_hook( 'wp_asac_process_warning_messages' );

	}

	/**
	 * Add license option.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $licenses List of addons licenses
	 *
	 * @return array           Updated list of licenses
	 */
	public function addon_license( $licenses ) {

		$plugin_name = $this->plugin_data( 'Name' );
		$plugin_name = trim( str_replace( 'Awesome Support:', '', $plugin_name ) ); // Remove the Awesome Support prefix from the addon name
		$licenses[]  = array(
			'name'      => $plugin_name,
			'id'        => "license_{$this->slug}",
			'type'      => 'edd-license',
			'default'   => '',
			'server'    => esc_url( 'https://getawesomesupport.com' ),
			'item_name' => $plugin_name,
			'item_id'   => $this->item_id,
			'file'      => __FILE__,
		);

		return $licenses;

	}

	/**
	 * Display notice if user didn't set his Envato license code
	 *
	 * @since 0.1.4
	 * @return void
	 */
	public function add_license_notice() {

		/**
		 * We only want to display the notice to the site admin.
		 */
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		$license = wpas_get_option( "license_{$this->slug}", '' );

		/**
		 * Do not show the notice if the license key has already been entered.
		 */
		if ( ! empty( $license ) ) {
			return;
		}

		$link = wpas_get_settings_page_url( 'licenses' );

		WPAS()->admin_notices->add_notice( 'error', "lincense_{$this->slug}", sprintf( __( 'Please <a href="%s">fill-in your product license</a> now. If you don\'t, your copy of Awesome Support: Automatic Ticket Close <strong>will never be updated</strong>.', WPASS_AUTOCLOSE_TEXT_DOMAIN ), $link ) );

	}

	/**
	 * Add license warning in the plugin meta row
	 *
	 * @since 0.1.0
	 *
	 * @param array $plugin_meta  The current plugin meta row
	 * @param string $plugin_file The plugin file path
	 *
	 * @return array Updated plugin meta
	 */
	public function license_notice_meta( $plugin_meta, $plugin_file ) {

		$license = wpas_get_option( "license_{$this->slug}", '' );

		if ( ! empty( $license ) ) {
			return $plugin_meta;
		}

		$license_page = add_query_arg( array(
			'post_type' => 'ticket',
			'page'      => 'settings',
			'tab'       => 'licenses',
		), admin_url( 'edit.php' ) );

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[] = '<strong>' . sprintf( __( 'You must fill-in your product license in order to get future plugin updates. <a href="%s">Click here to do it</a>.', WPASS_AUTOCLOSE_TEXT_DOMAIN ), $license_page ) . '</strong>';
		}

		return $plugin_meta;

	}

	/**
	 * Include all necessary files and instanciate the addon.
	 */
	public function load() {

		require_once WPASS_AUTOCLOSE_PATH . 'includes/functions.php';
		require_once WPASS_AUTOCLOSE_PATH . 'includes/class.object.php';
		require_once WPASS_AUTOCLOSE_PATH . 'includes/class.warning_message.php';
		require_once WPASS_AUTOCLOSE_PATH . 'includes/class.cron.php';
		require_once WPASS_AUTOCLOSE_PATH . 'includes/settings.php';


		add_action( 'tf_admin_page_created_wpas', array( $this, 'admin_page' ), 11, 1 );

		add_action( 'tf_save_admin_wpas', array( $this, 'save_admin' ), 11, 3 );
		add_action( 'tf_pre_save_admin_wpas', array( $this, 'pre_save_admin' ), 11, 3 );

		add_action( 'tf_admin_page_end', array( $this, 'page_end_autoclose' ) );

		add_action( 'init', array( $this, '_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( 'wpas_logs_handles', array( $this, 'register_log_handle' ), 10, 1 );
		add_filter( 'cron_schedules', array( $this, 'register_cron_schedules' ) );

		// Cron Hook
		add_action( 'wp_asac_process_warning_messages', array( $this, 'process_warning_messages' ) );

		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_action( 'save_post_ticket', array( $this, 'save_autoclose_metabox' ), 11, 1 );

		add_action( 'tf_init', array( $this, 'tf_init' ) );

		add_filter( 'wp_asac_cron_recurrences_options', array( $this, 'cron_recurrences_options' ) );
		add_action( 'tf_admin_page_table_start', array( $this, 'admin_page_table_start' ) );

		add_action( 'wpas_add_reply_after', array( $this, 'add_reply_after' ) , 11, 2 );

		add_action( 'wp_ajax_asac_log_win_content', array( $this, 'log_win_content' ) );
                
                add_filter( 'wpas_email_notifications_cases', array($this, 'register_notification_case'));
                
                add_filter( 'wpas_option_' . $this->slug, array($this, 'notification_activate_option'));
                
                add_action( 'wpas_after_reopen_ticket', array($this, 'after_reopen_ticket') , 9, 2 );
		
		add_action( 'wp_ajax_wpas_ac_clear_inprocess_values', array( $this, 'clear_inprocess_values') );

	}
        
	
	public function clear_inprocess_values() {
		
		$args = array(
		    'post_type' => 'ticket',
		    'meta_query' => array(
			'relation' => 'AND',
			
			array(
			    'relation' => 'OR',
			    array(
				'key' => 'autoclose_course_completed',
				'compare' => 'NOT EXISTS',
				'value' => ''
				),
			    array(
				'key' => 'autoclose_course_completed',
				'value' => 'no'
				)
			    ),
			array(
			    'key' => 'ac_sent_wmsgs',
			    'value' => '',
			    'compare' => '!='
			    )
			)
		    );
		
		
		$data = new WP_Query( $args );
		
		
		$tickets = $data->posts;
		
		
		$cron = new WPAC_Cron();
		
		foreach ( $tickets as $ticket ) {
			
			$clear = true;
			
			$cource_completed = get_post_meta( $ticket->ID, 'autoclose_course_completed', true );
			
			if( 'no' !=  $cource_completed ) {
				
				$process_id = get_post_meta( $ticket->ID, 'autoclose_process_id', true );
				$messages = $cron->get_wm_templates_by_status( $ticket->post_status );
				$processed_messages = $cron->process_messages_dates( $ticket->ID, $process_id );
				
				if( count( $processed_messages ) === count( $messages ) ) {
					update_post_meta( $ticket->ID, 'autoclose_course_completed', 'yes' );
					$clear = false;
				}
			}
			
			if( $clear ) {
				delete_post_meta( $ticket->ID, 'ac_sent_wmsgs' );
			}
		}
		
		wp_send_json_success(array('msg' => '<p>Data successfully cleared</p>'));
		die();
	
	}
	
        /**
         * 
         * @param string $is_active
         * @return boolean
         */
        public function notification_activate_option($is_active) {
            $is_active = '1';
            return $is_active;
        }

        /**
         * 
         * @param array $cases
         * @return array
         */
        public function register_notification_case($cases = array()) {
            
            if(!in_array($this->slug, $cases)) {
                $cases[$this->slug] = $this->slug;
                if(!wpas_get_option( $this->slug )) {
                    wpas_update_option($this->slug, $this->slug);
                }
            }
            
            return $cases;
        }
	/**
	 * Print log window content
	 *
	 * @since 1.0
	 * @return void
	 */
	public function log_win_content() {

		$log = wpac_get_log_content();
		?>
		<div id="asac_cron_log_win">
			<textarea rows="40" style="width:100%;height:380px;margin-top:30px;"><?php echo $log; ?></textarea>
		</div>

		<?php
		die();
	}

	/**
	 * clear processed flag after a reply
	 *
	 * @param int   $reply_id ID of the reply being processed
	 * @param array $data     Reply data that just got inserted
	 */
	public function add_reply_after( $reply_id, $data ) {

		$reply = get_post( $reply_id );
		if ( $reply ) {
                    wpac_clear_processed_flag($reply->post_parent);
		}

	}
        
        /**
	 * clear processed flag on ticket reopen
	 *
	 * @param int   $ticket_id
	 * @param boolean $reopened
	 */
	public function after_reopen_ticket( $ticket_id, $reopened ) {
            if ( $reopened ) {
                wpac_clear_processed_flag($ticket_id);
            }
	}
        
        

	/**
	 * Remove the default Titan Framework Save and Reset buttons
	 *
	 * Because this option page is very different and uses a lot of custom features, the default save button that Titan
	 * Framework adds won't really fit in our case. For that reason, we remove the default buttons and add our custom
	 * ones.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function admin_page_table_start() {

		$tab = $this->container->getActiveTab();
		if ( $tab && $tab->settings['id'] == 'autoclose' ) {
			foreach ( $tab->options as $k => $opt ) {
				if ( $opt->settings['type'] == 'save' && ! isset( $opt->settings['ac_save_btn'] ) ) {
					unset( $tab->options[ $k ] );
				}
			}
		}
	}

	/**
	 * Making recurrences options available from outside of this class
	 *
	 * @since 1.0
	 * @return array
	 */
	public function cron_recurrences_options() {

		$cron_recurrences = array();

		foreach ( $this->cron_recurrences as $cr_opt_key => $cr_opt ) {
			$cron_recurrences[ $cr_opt_key ] = $cr_opt['display'];
		}

		return $cron_recurrences;

	}

	/**
	 * Include our custom Titan Framework option
	 *
	 * @since 1.0
	 * @return void
	 */
	public function tf_init() {
		require_once 'includes/warning-msg-opt.php';
	}

	/**
	 * Main cron handler
	 *
	 * This method is the one responsible for processing e-mail notifications and for automatically closing tickets
	 * that need to be.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function process_warning_messages() {
		$cron = new WPAC_Cron();
		$cron->process();
	}
        
        /**
         * 
         * @param int $post_id
         * @return void
         */
        public function save_autoclose_metabox($post_id) {
            /* We should already being avoiding Ajax, but let's make sure */
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $post_id ) ) {
                return;
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
               return;
            }

            /* Now we check the nonce */
            if ( ! isset( $_POST['wpas_cf'] ) || ! wp_verify_nonce( $_POST['wpas_cf'], 'wpas_update_cf' ) ) {
                return;
            }

            /* Does the current user has permission? */
            if ( ! current_user_can( 'edit_ticket', $post_id ) ) {
                return;
            }
            
            if(isset($_POST['asac_clear_processed_flag']) && $_POST['asac_clear_processed_flag'] == 1) {
                wpac_clear_processed_flag($post_id);
            }
        }
        
        
        /**
         * Register metaboxes
         */
        public function register_metaboxes() {
            add_meta_box( 'wp-asac-mb-autoclose', __( 'Auto close' , WPASS_AUTOCLOSE_TEXT_DOMAIN), array($this, 'autoclose_metabox'), 'ticket', 'side', 'high');
        }

        /**
         * Settings metabox for tickets
         */
        public function autoclose_metabox() {
            global $post_id;
            
            $date = '';
            

            $_date = get_post_meta($post_id, 'last_autoclose_process_date', true);
            
            if($_date) {
                $date = date('F j, Y, g:i a', $_date);
            }
            
            ?>

            <div class="wpas-ticket-status submitbox">
                <label><strong>
                    <input type="checkbox" name="asac_clear_processed_flag" value="1" />
                    <?php _e( 'Clear Processed Flag', WPASS_AUTOCLOSE_TEXT_DOMAIN); ?></strong>
                </label>
            </div>

            <?php if($date) : ?>

            <div class="wpas-ticket-status submitbox" style="margin-top: 12px;">
                <strong><?php _e( 'AC Conditions Last Checked', WPASS_AUTOCLOSE_TEXT_DOMAIN); ?> : </strong>
                <span><?php echo $date; ?></span>
            </div>

            <?php
            endif;
        }
        
        /**
         * Register custom awesome support field
         */
        public function register_custom_fields() {
            if ( function_exists( 'wpas_add_custom_field' ) ) {
                wpas_add_custom_field( 'warningmessage',  array( 'field_type' => '', 'title' => __( 'Warning Message', WPASS_AUTOCLOSE_TEXT_DOMAIN ), 'show_frontend_list' => false, 'show_frontend_detail' => false ) );
            }
        }
        
        /**
         * Register new cron schedules
         * @param array $schedules
         * @return array
         */
        public function register_cron_schedules($schedules) {
            
            foreach ( $this->cron_recurrences as $cr_opt_key => $cr_opt ) {
                
                if(!in_array($cr_opt_key, array_keys($schedules))) {
                    $schedules[ $cr_opt_key ] = $cr_opt;
                }
            }
            return $schedules;
        }

	/**
	 * Register new log handle for loggin cron process
	 *
	 * @since 1.0
	 *
	 * @param array $handles List of available handles
	 *
	 * @return array
	 */
	public function register_log_handle( $handles ) {
		array_push( $handles, 'autoclose' );

		return $handles;
	}
        
        
        /**
         * 
         * @global object $wpdb
         */
        public function _init() {
            global $wpdb;
            
            
            add_action('wp_ajax_asac_save_new_warning_msg', array($this, 'save_new_warning_msg'));
            
            if (!wp_next_scheduled('wp_asac_process_warning_messages')) {
                $cron_recurrence = wpas_get_option('autoclose_cron_recurrence',  'every5min');
                wp_schedule_event(time(), $cron_recurrence, 'wp_asac_process_warning_messages');
            }
            
            
        }
        
        
        /**
         * Save new warning message
         */
        public function save_new_warning_msg() {
            $error = "";
            if(!(isset($_POST['wpas_age']) && $_POST['wpas_age'] && is_numeric($_POST['wpas_age']) && $_POST['wpas_age'] > 0)) {
                $error = "Age is not valid";
            } elseif(!(isset($_POST['wpas_msg']) && trim($_POST['wpas_msg']))) {
                $error = "Message is required";
            } elseif(!(isset($_POST['wpas_subject']) && trim($_POST['wpas_subject']))) {
                $error = "Subject is required";
            }
            
            if($error) {
                wp_send_json_error(array('error' => __($error, WPASS_AUTOCLOSE_TEXT_DOMAIN)));
            }
            
            
            
            $status = $_POST['wpas_status'];
            $age = $_POST['wpas_age'];
            $subject = trim($_POST['wpas_subject']);
            $message = trim($_POST['wpas_msg']);
            $close = isset($_POST['wpas_close']) ? 1 : 0;
            
            if(WPAC_WarningMessage::add($status, $age, $subject , $message, $close)) {
                wp_send_json_success(array('msg' => '<p><strong>' . __('Settings saved', WPASS_AUTOCLOSE_TEXT_DOMAIN) . '</strong></p>'));
            } else {
                wp_send_json_error(array('error' => __('Error while adding warning message', WPASS_AUTOCLOSE_TEXT_DOMAIN)));
            }
        }
        
        
        /**
         * print new warning message section
         * 
         */
        
        public function page_end_autoclose() {
            
            
            $tab = $this->container->getActiveTab();
            if(!($tab && $tab->settings['id'] == 'autoclose')) {
                return;
            }
            
            $cusHeading = new TitanFrameworkOptionCustom(array(
                    'id'      => 'autoclose_wanring_msg_field_group_new_custom',
                    'type'    => 'custom',
                    'custom' => "<h3>" . __('Add new Warning Message', WPASS_AUTOCLOSE_TEXT_DOMAIN) . "</h3>"
                ), $tab);
            
            $newWarn = new TitanFrameworkOptionWarningMessage(array(
                    'id'      => 'autoclose_wanring_msg_field_group_new_warningmessage',
                    'type'    => 'warningmessage',
                    'wm_data' => array(),
                    'is_new' => true
                ), $tab);
            
            ?>
            
            <form class="asac_new_warning_msg_form" method="post" action="<?=admin_url('admin-ajax.php')?>">

                <?php wp_nonce_field( 'asac_save_new_warning_msg',   'as_autoclose_nonce' ); ?>

                <input type="hidden" name="action" value="asac_save_new_warning_msg" />
                <table class="form-table">
                <?php
                $cusHeading->display();
                $newWarn->display();
                ?>
                </table>


                <p class="submit">
                    <button type="button" class="button button-primary btn_save_new_warning_msg">Add Warning</button>
                    <span class="loading"></span>
                    <span class="new_wanring_msg_error"></span>
                </p>
                <div class="updated asac_save_success_msg"></div>
            </form>
            
            
            
            <?php
            
        }

        /**
         * Runs before saving changes on auto close tab
         * @param object $admin
         * @param object $activeTab
         * @param array $options
         */
        public function pre_save_admin($admin, $activeTab, $options) {
            if($activeTab->settings['id'] == 'autoclose') {
                $old_cron_recurrence = wpas_get_option('autoclose_cron_recurrence',  'hourly');
                $new_cron_recurrence = isset($_POST['wpas_autoclose_cron_recurrence']) && $_POST['wpas_autoclose_cron_recurrence'] ? $_POST['wpas_autoclose_cron_recurrence'] : 'hourly';
                
                if($old_cron_recurrence != $new_cron_recurrence) {
                    wp_clear_scheduled_hook( 'wp_asac_process_warning_messages' );
                    
                    wp_schedule_event(time(), $new_cron_recurrence, 'wp_asac_process_warning_messages');
                }
            }
                
        }
        
        /**
         * Save changes on auto close tab
         * @param object $admin
         * @param object $activeTab
         * @param array $options
         */
        public function save_admin($admin, $activeTab, $options) {
            if($activeTab->settings['id'] == 'autoclose') {
                $warning_messages = (isset($_POST['wpas_autoclose_wm']) && $_POST['wpas_autoclose_wm']) ? $_POST['wpas_autoclose_wm'] : array();
                WPAC_WarningMessage::save_warnings($warning_messages);
            }
                
        }
        
        
        /**
         * Setting TitanFrameworkAdminPage once its available
         * @param object $settings
         */
        public function admin_page($settings) {
            $this->container = $settings;
        }
        
        
        
        
        /**
         * Enqueue Admin resources
         * @return void
         */
        
        
        public function admin_enqueue_scripts() {
            if( is_admin() ) { 
                
                add_thickbox();
                
                wp_enqueue_style('autoclose-style', plugins_url( 'assets/css/style.css', __FILE__ )); 
                wp_enqueue_script('autoclose-script', plugins_url( 'assets/js/functions.js', __FILE__ ), array('jquery')); 
            }
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
	 * @since   1.0.4
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function load_plugin_textdomain() {

		$lang_dir       = WPASS_AUTOCLOSE_ROOT . 'languages/';
		$lang_path      = WPASS_AUTOCLOSE_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'wpass_autoclose' );
		$mofile         = "wpass_autoclose-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/awesome-support-automatic-ticket-close/' . $mofile;

		// Look for the GlotPress language pack first of all
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'wpass_autoclose', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'wpass_autoclose', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'wpass_autoclose', false, $lang_dir );
		}

		return $language;

	}
        
}
