<?php

/**
 * Settings page controller
 * Class MKB_SettingsPage
 */

/**
 * Class MinervaKB_SettingsPage
 * Settings page controller
 */
class MinervaKB_SettingsPage implements KST_SubmenuPage_Interface {

	private $info;

	private $ajax;

	private $SCREEN_BASE = null;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->SCREEN_BASE = MKB_Options::option('article_cpt') . '_page_minerva-kb-submenu-settings';

		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}

		if (isset($deps['ajax'])) {
			$this->ajax = $deps['ajax'];
		}
	}

	/**
	 * Adds settings menu
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'edit.php?post_type=' . MKB_Options::option('article_cpt'),
			__( 'Settings', 'minerva-kb' ),
			__( 'Settings', 'minerva-kb' ),
			'manage_options',
			'minerva-kb-submenu-settings',
			array( $this, 'submenu_html' )
		);
	}

	/**
	 * Settings menu HTML
	 */
	public function submenu_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'minerva-kb' ) );
		}

		$settings_helper = new MKB_SettingsBuilder(); // TODO: refactor this

		?>
		<div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr(MINERVA_KB_IMG_URL . 'logo.png'); ?>" title="logo" />
			</span>
			<span class="mkb-header-title mkb-header-item"><?php echo __( 'Settings', 'minerva-kb' ); ?></span>
			<?php MinervaKB_AutoUpdate::registered_label(); ?>
			<a href="#" id="mkb-plugin-settings-save" class="mkb-action-button mkb-action-default mkb-header-item"
			   title="<?php esc_attr_e('Save Settings', 'minerva-kb'); ?>"><?php echo __( 'Save Settings', 'minerva-kb' ); ?></a>
			<a href="#" id="mkb-plugin-settings-reset" class="mkb-action-button mkb-action-danger mkb-header-item"
			   title="<?php esc_attr_e('Restore defaults', 'minerva-kb'); ?>"><?php echo __( 'Restore defaults', 'minerva-kb' ); ?></a>
		</div>

		<form id="mkb-plugin-settings" class="mkb-loading" novalidate>
			<div class="mkb-plugin-settings-preloader">
				<div class="mkb-loader">
					<span class="inner1"></span>
					<span class="inner2"></span>
					<span class="inner3"></span>
				</div>
			</div>
			<div class="mkb-settings-content-holder">
				<?php

				$options = MKB_Options::get_options();

				$settings_helper->render_tab_links( $options );

				?>
				<div class="mkb-settings-content fn-mkb-settings-container">
					<?php
					foreach ( $options as $option ):
						$settings_helper->render_option(
							$option["type"],
							MKB_Options::option( $option["id"] ),
							$option
						);
					endforeach;

					$settings_helper->close_tab_container();
					?>
				</div>
				<?php
				?>
			</div>
		</form>
	<?php
	}

	/**
	 * Loads settings page admin assets
	 */
	public function load_assets() {

		$screen = get_current_screen();

		if ( $screen->base !== $this->SCREEN_BASE ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );

		// toastr
		wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/toastr/toastr.min.css', false, '2.1.3' );

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/toastr/toastr.min.js', array(), '2.1.3', true );

		wp_enqueue_script( 'minerva-kb/admin-settings-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-settings.js', array(
			'jquery',
			'wp-color-picker',
			'minerva-kb/admin-ui-js',
			'minerva-kb/admin-toastr-js'
		), MINERVA_KB_VERSION, true );
	}
}
