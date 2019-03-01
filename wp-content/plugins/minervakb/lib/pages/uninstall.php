<?php

/**
 * Uninstall page controller
 * Class MKB_UninstallPage
 */

/**
 * Class MinervaKB_UninstallPage
 * Uninstall page controller
 */
class MinervaKB_UninstallPage implements KST_SubmenuPage_Interface {

	private $info;

	private $ajax;

	private $SCREEN_BASE = null;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->SCREEN_BASE = MKB_Options::option('article_cpt') . '_page_minerva-kb-submenu-uninstall';

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
	 * Adds uninstall menu
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'edit.php?post_type=' . MKB_Options::option('article_cpt'),
			__( 'Uninstall', 'minerva-kb' ),
			__( 'Uninstall', 'minerva-kb' ),
			'manage_options',
			'minerva-kb-submenu-uninstall',
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

		?>
		<div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr(MINERVA_KB_IMG_URL . 'logo.png'); ?>" title="logo" />
			</span>
			<span class="mkb-header-title mkb-header-item"><?php echo __( 'Uninstall / Reset', 'minerva-kb' ); ?></span>
			<?php MinervaKB_AutoUpdate::registered_label(); ?>
		</div>

		<form id="mkb-plugin-uninstall" novalidate>
			<div class="mkb-uninstall-content-holder">
				<?php

				?>
				<div class="mkb-uninstall-content">
					<h3><?php _e( 'Uninstall / Reset', 'minerva-kb' ); ?></h3>
					<p>
						<?php _e( 'You can remove all plugin data from database before uninstalling or to reset it completely.<br/>Note, that you\'ll need to re-activate the plugin, if you want to use it after the reset. <br/> Reset will remove plugin options, imported data, feedback, plugin technical options and search analytics data.<br/>Articles, topics and pages are not removed, you can remove them via Dashboard Knowledge Base menu.', 'minerva-kb' ); ?>
					</p>
					<br/>
					<p>
						<a href="#" class="mkb-action-button mkb-action-danger fn-mkb-uninstall-btn"
						   title="<?php esc_attr_e('Remove plugin data', 'minerva-kb'); ?>"><?php echo __( 'Remove plugin data', 'minerva-kb' ); ?></a>
					</p>
				</div>
				<?php
				?>
			</div>
		</form>
	<?php
	}

	/**
	 * Loads uninstall page admin assets
	 */
	public function load_assets() {

		$screen = get_current_screen();

		if ( $screen->base !== $this->SCREEN_BASE ) {
			return;
		}

		// toastr
		wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/toastr/toastr.min.css', false, '2.1.3' );
		wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/toastr/toastr.min.js', array(), '2.1.3', true );

		wp_enqueue_script( 'minerva-kb/admin-settings-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-uninstall.js', array(
			'jquery',
			'minerva-kb/admin-ui-js',
			'minerva-kb/admin-toastr-js'
		), MINERVA_KB_VERSION, true );
	}
}
