<?php

/**
 * Welcome page controller
 * Class MinervaKB_WelcomePage
 */

/**
 * Class MinervaKB_WelcomePage
 * Settings page controller
 */
class MinervaKB_WelcomePage {

	private $info;

	private $ajax;

	private $SCREEN_BASE = null;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->SCREEN_BASE = MKB_Options::option('article_cpt') . '_page_minerva-kb-submenu-welcome-page';

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
			__( 'Quick start', 'minerva-kb' ),
			__( 'Quick start', 'minerva-kb' ),
			'edit_theme_options',
			'minerva-kb-submenu-welcome-page',
			array( $this, 'submenu_html' )
		);
	}

	/**
	 * Settings menu HTML
	 */
	public function submenu_html() {
		?>
		<div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr(MINERVA_KB_IMG_URL . 'logo.png'); ?>" title="logo" />
			</span>
			<span class="mkb-header-title mkb-header-item"><?php _e( 'Welcome to MinervaKB!', 'minerva-kb' ); ?></span>
			<?php MinervaKB_AutoUpdate::registered_label(); ?>
		</div>

		<div id="mkb-welcome-screen" class="mkb-initial-setup mkb-steps fn-mkb-steps">

			<div id="init_guide" class="mkb-initial-setup-item fn-mkb-steps-item mkb-steps-item active">
				<h2><?php _e('Quick Start', 'minerva-kb'); ?></h2>

				<?php

				$kb_url = admin_url( 'edit.php?post_type=' . MKB_Options::option('article_cpt') );
				$topics_url = admin_url( 'edit-tags.php?taxonomy=' . MKB_Options::option('article_cpt_category') . '&post_type=' . MKB_Options::option('article_cpt') );
				$faq_url = admin_url( 'edit.php?post_type=mkb_faq' );
				$sorting_url = admin_url( 'edit.php?post_type=' . MKB_Options::option('article_cpt') . '&page=' . MKB_Options::option('article_cpt') . '-sorting' );
				$dashboard_url = admin_url( 'edit.php?post_type=' . MKB_Options::option('article_cpt') . '&page=minerva-kb-submenu-dashboard' );
				$settings_url = admin_url( 'edit.php?post_type=' . MKB_Options::option('article_cpt') . '&page=minerva-kb-submenu-settings' );
				$import_url = admin_url( 'edit.php?post_type=' . MKB_Options::option('article_cpt') . '&page=minerva-kb-submenu-settings&mkb_options_tab=demo_import_tab' );

				?>

				<p><?php _e('Welcome to MinervaKB!', 'minerva-kb'); ?></p>

				<p>
					<?php _e('Thank you for purchasing our product, we will do our best to help you with it. Here are a few tips to get you started.', 'minerva-kb'); ?>
				</p>

				<ol>
					<li>
						<?php printf(
							__( 'Plugin settings are available at <a href="%s">MinervaKB - Settings</a> page.', 'minerva-kb' ),
							esc_url( $settings_url )
						); ?>
					</li>
					<li>
						<?php printf(
							__('You can either start from scratch, or import sample data to see how your site will look like when ready. To do so, visit the <a href="%s">Demo Import</a> tab in <strong>Settings</strong>.', 'minerva-kb'),
							esc_url( $import_url )
						);
						?>
					</li>
					<li>
						<?php printf(
							__( 'All the documentation is created via <strong>Articles</strong>. Visit <a href="%s">Knowledge Base</a> page to add or edit them.', 'minerva-kb' ),
							esc_url( $kb_url )
						); ?>
					</li>
					<li>
						<?php printf(
							__( 'You can organize <strong>KB Articles</strong> in <strong>Topics</strong>. Visit <a href="%s">Topics</a> page to manage your <strong>Topics</strong>.', 'minerva-kb' ),
							esc_url( $topics_url )
						); ?>
					</li>
					<li>
						<?php _e('To create <strong>Knowledge Base Home page</strong> you can use either <strong>Shortcode Builder</strong> inside any page (<strong>recommended</strong>) or use plugin Settings for home page.', 'minerva-kb'); ?>
						<?php _e('Please see this short video to learn how to use the <strong>Shortcode Builder</strong>:', 'minerva-kb'); ?>
						<p>
							<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/CpkeJcrRNj0?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
						</p>
						<p>
							<?php _e('You can also use <strong>Visual Composer</strong> modules, available for all shortcodes.', 'minerva-kb'); ?>
						</p>
					</li>
					<li>
						<?php printf(
							__( 'To reorder <strong>Articles</strong> using drag n drop, please visit <a href="%s">Sorting</a> page.', 'minerva-kb' ),
							esc_url( $sorting_url )
						); ?>
					</li>
					<li>
						<?php printf(
							__( 'You can also create unlimited amount of <strong>FAQ</strong> items with categories. FAQ blocks are styled globally in settings and can be inserted via simple shortcodes. Visit <a href="%s">FAQ</a> page to edit them.', 'minerva-kb' ),
							esc_url( $faq_url )
						); ?>
					</li>
					<li>
						<?php printf(
							__( 'Knowledge Base usage and performance statistics can be seen at any time at <a href="%s">Dashboard</a> page.', 'minerva-kb' ),
							esc_url( $dashboard_url )
						); ?>
						<?php _e('You can view search statistics, feedback, views, likes / dislikes and take action to improve your Knowledge Base and support efficiency.', 'minerva-kb'); ?>
					</li>
				</ol>
			</div>

			<div>
				<p>&nbsp;</p>
				<p><?php _e('If you have any questions (or requests), please contact us via email: <a href="mailto:konstrukteam@gmail.com?subject=MinervaKB Support Question">konstrukteam@gmail.com</a>', 'minerva-kb'); ?></p>
				<p>
					<?php _e('Finally, if you like our plugin, please consider <a href="https://codecanyon.net/downloads" target="_blank">rating it</a>. It takes only a couple of seconds and it <strong>really helps us</strong>! Thank you! :)', 'minerva-kb'); ?>
				</p>
			</div>

		</div>
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
//		wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_THEME_CSS_URL . 'vendor/toastr/toastr.min.css', false, '2.1.3' );

//		wp_enqueue_script( 'jquery-ui-sortable' );
//		wp_enqueue_script( 'jquery-ui-slider' );
//		wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_THEME_JS_URL . 'vendor/toastr/toastr.min.js', array(), '2.1.3', true );
//
//		wp_enqueue_script( 'minerva-kb/admin-welcome-js', MINERVA_THEME_JS_URL . 'minerva-kb-initial.js', array(
//			'jquery',
//			'minerva-kb/admin-ui-js',
//			'minerva-kb/admin-toastr-js'
//		), null, true );
	}
}
