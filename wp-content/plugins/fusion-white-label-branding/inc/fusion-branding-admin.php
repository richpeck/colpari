<?php
/**
 * The admin class for the Fusion White Label Branding plugin.
 *
 * @package Fusion-White-Label-Branding
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main admin class for the plugin.
 *
 * @since 1.0
 */
class Fusion_White_Label_Branding_Admin {

	/**
	 * Projects.
	 *
	 * @var array
	 * @since 1.0
	 */
	private $settings = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct() {

		$this->settings = get_option( 'fusion_branding_settings', array() );

		// Add action links to settings page for easy access.
		add_filter( 'plugin_action_links_' . plugin_basename( FUSION_WHITE_LABEL_BRANDING_PLUGIN_FILE ), array( $this, 'add_action_settings_link' ) );

		// Add Fusion White Label Branding admin menu logo.
		add_action( 'admin_head', array( $this, 'admin_menu_styling' ) );

		// Add custom capability to user roles.
		add_action( 'admin_init', array( $this, 'add_user_role_caps' ) );

		// Register admin menu for form dashbaord.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 1 );
		add_action( 'admin_menu', array( $this, 'admin_menu_label_change' ), 1001 );

		// Save Branding Settings.
		add_action( 'admin_post_save_fusion_branding_settings', array( $this, 'settings_save' ) );

		// Login screen branding.
		add_action( 'login_head', array( $this, 'login_screen_branding' ) );

		// Handles settings export.
		add_action( 'init', array( $this, 'export_settings' ) );

		// Handles settings import.
		add_action( 'init', array( $this, 'import_settings' ) );

		// Handles settings reset.
		add_action( 'init', array( $this, 'reset_settings' ) );

		if ( $this->user_can_see_changes() ) {

			// Process admin menu changes.
			add_action( 'admin_menu', array( $this, 'admin_menu_remove_sub_menus' ), 1001 );
			add_action( 'admin_bar_menu', array( $this, 'remove_wp_nodes' ), 999 );

			// Remove Avada menu from admin bar on frontend.
			add_action( 'wp_before_admin_bar_render', array( $this, 'remove_wp_toolbar_menu' ), 100 );

			// Update strings with custom branding strings.
			add_filter( 'gettext', array( $this, 'update_branding_strings' ), 10, 3 );

			// Hide tabs from Avada admin screen.
			add_action( 'admin_head', array( $this, 'fusion_branding_admin_styles' ) );

			// Change logo on frontend.
			add_action( 'wp_head', array( $this, 'fusion_branding_frontend_styles' ) );

			// Overwrite fusion page builder metabox.
			add_action( 'add_meta_boxes', array( $this, 'update_builder_meta_box' ) );

			// Remove dashboard widgets.
			add_action( 'admin_init', array( $this, 'remove_dashboard_meta' ) );

			// Add custom welcome dashboard widget.
			$wp_admin_options      = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
			$welcome_panel_title   = isset( $wp_admin_options['welcome_panel_title'] ) && ! empty( $wp_admin_options['welcome_panel_title'] ) ? $wp_admin_options['welcome_panel_title'] : '';
			$welcome_panel_content = isset( $wp_admin_options['welcome_panel_content'] ) && ! empty( $wp_admin_options['welcome_panel_content'] ) ? $wp_admin_options['welcome_panel_content'] : '';

			if ( '' !== $welcome_panel_title || '' !== $welcome_panel_content ) {
				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				add_action( 'all_admin_notices', array( $this, 'update_welcome_panel' ), 999 );
			}

			// Change admin page title.
			add_action( 'admin_title', array( $this, 'change_dashboard_title' ) );

			// Handle admin footer text.
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
			add_filter( 'update_footer', array( $this, 'admin_footer_version_text' ), 99 );

			// Remove screen options tab.
			add_filter( 'screen_options_show_screen', array( $this, 'remove_screen_options' ) );

			// Remove help tab.
			add_filter( 'contextual_help', array( $this, 'remove_help_tabs' ), 999, 3 );

			// Update portfolio and faq menu labels.
			add_action( 'init', array( $this, 'change_post_object_label' ), 999 );

			// Update Fusion Builder welcome screen title.
			add_filter( 'fusion_builder_admin_welcome_title', array( $this, 'fusion_builder_welcome_screen_title' ) );

			// Update Fusion Builder welcome screen about text.
			add_filter( 'fusion_builder_admin_welcome_text', array( $this, 'fusion_builder_welcome_screen_about_text' ) );

			// Update Fusion Builder welcome screen about text.
			add_filter( 'fusion_builder_admin_welcome_screen_content', array( $this, 'fusion_builder_welcome_screen_content' ) );

			// Update Avada welcome screen title.
			add_filter( 'avada_admin_welcome_title', array( $this, 'avada_welcome_screen_title' ) );

			// Update Avada welcome screen about text.
			add_filter( 'avada_admin_welcome_text', array( $this, 'avada_welcome_screen_about_text' ) );

			// Update Avada welcome screen about text.
			add_filter( 'avada_admin_welcome_screen_content', array( $this, 'avada_welcome_screen_content' ) );

			// Add filter for version number in redux.
			add_filter( 'fusion_fusionredux_args', array( $this, 'fusion_fusionredux_args' ) );
		}
	}

	/**
	 * Add settings link on plugins page for easy access.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $links The array of action links.
	 * @return Array The $links array plus the added settings link.
	 */
	public function add_action_settings_link( $links ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=fusion-white-label-branding-settings' ) . '">' . esc_html__( 'Settings', 'fusion-white-label-branding' ) . '</a>';

		return $links;
	}

	/**
	 * Add custom capability to user roles to access the admin settings.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function add_user_role_caps() {

		// WP Admin Options.
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$user_roles       = isset( $wp_admin_options['user_role_visibility'] ) ? $wp_admin_options['user_role_visibility'] : array();

		// Get all user roles.
		$all_user_roles = get_editable_roles();

		// Add administrator to user roles array.
		$user_roles[] = 'administrator';

		// Set the custom capability name for user roles.
		$capability = 'access_white_label_branding';

		// Add capability to each user roles selected by administrator.
		foreach ( $all_user_roles as $role => $info ) {
			$user_role = get_role( $role );
			if ( in_array( $role, $user_roles, true ) ) {
				$user_role->add_cap( $capability );
			} else {
				$user_role->remove_cap( $capability );
			}
		}
	}

	/**
	 * Admin Menu.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_menu() {
		global $submenu;

		$capability = 'access_white_label_branding';

		$welcome_tab   = add_menu_page( esc_attr__( 'Fusion White Label Branding', 'fusion-white-label-branding' ), esc_attr__( 'Fusion Branding', 'fusion-white-label-branding' ), $capability, 'fusion-white-label-branding-admin', array( $this, 'welcome_tab' ), 'dashicons-fusion-white-label-logo', '3.555555' );
		$support       = add_submenu_page( 'fusion-white-label-branding-admin', esc_attr__( 'Support', 'fusion-white-label-branding' ), esc_attr__( 'Support', 'fusion-white-label-branding' ), $capability, 'fusion-white-label-branding-support', array( $this, 'support_tab' ) );
		$faq           = add_submenu_page( 'fusion-white-label-branding-admin', esc_attr__( 'FAQ', 'fusion-white-label-branding' ), esc_attr__( 'FAQ', 'fusion-white-label-branding' ), $capability, 'fusion-white-label-branding-faq', array( $this, 'faq_tab' ) );
		$settings      = add_submenu_page( 'fusion-white-label-branding-admin', esc_attr__( 'Branding Settings', 'fusion-white-label-branding' ), esc_attr__( 'Settings', 'fusion-white-label-branding' ), $capability, 'fusion-white-label-branding-settings', array( $this, 'branding_settings_tab' ) );
		$import_export = add_submenu_page( 'fusion-white-label-branding-admin', esc_attr__( 'Import / Export', 'fusion-white-label-branding' ), esc_attr__( 'Import / Export', 'fusion-white-label-branding' ), $capability, 'fusion-white-label-branding-import-export', array( $this, 'settings_import_export_tab' ) );

		add_action( 'admin_print_scripts-' . $welcome_tab, array( $this, 'admin_styles' ) );
		add_action( 'admin_print_scripts-' . $support, array( $this, 'admin_styles' ) );
		add_action( 'admin_print_scripts-' . $faq, array( $this, 'admin_scripts_and_styles' ) );
		add_action( 'admin_print_scripts-' . $settings, array( $this, 'admin_scripts_and_styles' ) );
		add_action( 'admin_print_scripts-' . $import_export, array( $this, 'admin_scripts_and_styles' ) );
	}

	/**
	 * Change the menu label for Fusion White Label Branding.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_menu_label_change() {
		global $menu, $submenu;

		// Change Fusion Branding first menu item label to welcome.
		if ( current_user_can( 'edit_theme_options' ) ) {
			// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
			$submenu['fusion-white-label-branding-admin'][0][0] = esc_html__( 'Welcome', 'fusion-white-label-branding' );
		}

		if ( $this->user_can_see_changes() ) {

			// WP Admin Options.
			$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
			$admin_menus      = isset( $wp_admin_options['rename_admin_menu'] ) ? $wp_admin_options['rename_admin_menu'] : array();

			// Avada Options.
			$avada_options = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
			$avada_menus   = isset( $avada_options['rename_admin_menu'] ) ? $avada_options['rename_admin_menu'] : array();

			// Fusion Slider Options.
			$fusion_slider_options = isset( $this->settings['fusion_branding']['fusion_slider'] ) ? $this->settings['fusion_branding']['fusion_slider'] : array();
			$fusion_slider_menus   = isset( $fusion_slider_options['remove_admin_menu'] ) ? $fusion_slider_options['remove_admin_menu'] : array();

			// Fusion Builder Options.
			$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();

			// Change dasbhoard menu label.
			if ( isset( $admin_menus ) && ! empty( $admin_menus ) ) {
				foreach ( $admin_menus as $menu_item => $label ) {
					if ( '' !== $label ) {
						switch ( $menu_item ) {
							case 'dashboard':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[2][0] = $label;
								break;
							case 'posts':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[5][0] = $label;
								break;
							case 'media':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[10][0] = $label;
								break;
							case 'pages':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[20][0] = $label;
								break;
							case 'comments':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[25][0] = $label;
								break;
							case 'themes':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[60][0] = $label;
								break;
							case 'plugins':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[65][0] = $label;
								break;
							case 'users':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[70][0] = $label;
								break;
							case 'tools':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[75][0] = $label;
								break;
							case 'settings':
								// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
								$menu[80][0] = $label;
								break;
						}
					}
				}
			}

			// Change avada post type menu label.
			if ( isset( $fusion_slider_options ) && isset( $fusion_slider_options['admin_menu_label'] ) && $fusion_slider_options['admin_menu_label'] && isset( $menu['2.333333'][0] ) ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['2.333333'][0] = $fusion_slider_options['admin_menu_label'];
			}

			// Remove selected sub-menus under Fusion Slider admin menu.
			if ( ! empty( $fusion_slider_menus ) ) {

				// If all sub-menus are selected, remove the parent menu as well to avoid confusion.
				if ( 3 === count( $fusion_slider_menus ) ) {
					unset( $menu['2.333333'] );
				} else {
					foreach ( $fusion_slider_menus as $menu_slug ) {
						switch ( $menu_slug ) {

							case 'slider':
								unset( $submenu['edit.php?post_type=slide'][5] );
								break;

							case 'slide-page':
								unset( $submenu['edit.php?post_type=slide'][15] );
								break;

							case 'import-export':
								unset( $submenu['edit.php?post_type=slide'][16] );
								break;
						}
					}
				}
			}

			// If set, change Avada admin menu label.
			if ( isset( $avada_options['admin_menu_label'] ) && '' !== $avada_options['admin_menu_label'] ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['2.111111'][0] = $avada_options['admin_menu_label'];
			}

			// Change Avada admin menu icon class name if set in settings.
			if ( isset( $avada_options['admin_menu_dashicon'] ) && '' !== $avada_options['admin_menu_dashicon'] ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['2.111111'][6] = $avada_options['admin_menu_dashicon'];
			}

			// Change Fusion White Label Branding admin menu icon class name if set in settings.
			if ( isset( $avada_options['fusion_white_label_branding_menu_dashicon'] ) && '' !== $avada_options['fusion_white_label_branding_menu_dashicon'] ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['3.555555'][6] = $avada_options['fusion_white_label_branding_menu_dashicon'];
			}

			// Change Theme Options menu label.
			if ( ( isset( $avada_options['theme_options_menu_label'] ) && '' !== $avada_options['theme_options_menu_label'] ) ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$submenu['avada'][7][0] = $avada_options['theme_options_menu_label'];
			}

			// If set, change Fusion Builder admin menu label.
			if ( isset( $fusion_builder_options['admin_menu_label'] ) && '' !== $fusion_builder_options['admin_menu_label'] ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['2.222222'][0] = $fusion_builder_options['admin_menu_label'];
			}

			// Change Fusion Builder admin menu icon class name if set in settings.
			if ( isset( $fusion_builder_options['admin_menu_dashicon'] ) && '' !== $fusion_builder_options['admin_menu_dashicon'] ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['2.222222'][6] = $fusion_builder_options['admin_menu_dashicon'];
			}

			// Change Fusion Slider admin menu icon class name if set in settings.
			if ( isset( $fusion_slider_options['admin_menu_dashicon'] ) && '' !== $fusion_slider_options['admin_menu_dashicon'] ) {
				// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				$menu['2.333333'][6] = $fusion_slider_options['admin_menu_dashicon'];
			}
		}
	}

	/**
	 * Remove selected sub-menus from admin menus.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_menu_remove_sub_menus() {
		global $menu;

		// WP Admin Options.
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$wp_admin_menus   = isset( $wp_admin_options['remove_admin_menu'] ) ? $wp_admin_options['remove_admin_menu'] : array();

		// Avada Options.
		$avada_options         = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$avada_menus           = isset( $avada_options['remove_admin_menu'] ) ? $avada_options['remove_admin_menu'] : array();
		$avada_post_type_menus = isset( $avada_options['remove_post_type_menu'] ) ? $avada_options['remove_post_type_menu'] : array();

		// Fusion Builder Options.
		$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();
		$fusion_builder_menus   = isset( $fusion_builder_options['remove_admin_menu'] ) ? $fusion_builder_options['remove_admin_menu'] : array();

		// Remove selected menus from admin menu.
		if ( ! empty( $wp_admin_menus ) ) {
			foreach ( $wp_admin_menus as $menu_slug ) {
				remove_menu_page( $menu_slug );
			}
		}

		// Remove selected sub-menus under Avada admin menu.
		if ( ! empty( $avada_menus ) ) {
			if ( 9 === count( $avada_menus ) ) {
				unset( $menu['2.111111'] );
			} else {
				foreach ( $avada_menus as $menu_slug ) {
					$parent_slug = 'avada';
					if ( 'theme_options' === $menu_slug ) {
						$menu_slug = 'themes.php?page=avada_options';
					} elseif ( 'avada' !== $menu_slug ) {
						$menu_slug = 'avada-' . $menu_slug;
					}

					remove_submenu_page( $parent_slug, $menu_slug );
				}
			}
		}

		// Remove admin menus for selected post types.
		if ( ! empty( $avada_post_type_menus ) ) {
			foreach ( $avada_post_type_menus as $key => $menu_slug ) {
				remove_menu_page( 'edit.php?post_type=' . $menu_slug );
			}
		}

		// Remove selected sub-menus under Fusion Builder admin menu.
		if ( ! empty( $fusion_builder_menus ) ) {
			if ( 6 === count( $fusion_builder_menus ) ) {
				unset( $menu['2.222222'] );
			} else {
				foreach ( $fusion_builder_menus as $menu_slug ) {
					remove_submenu_page( 'fusion-builder-options', 'fusion-builder-' . $menu_slug );
				}
			}
		}
	}

	/**
	 * Remove WordPress logo from admin bar.
	 *
	 * @access public
	 * @since 1.0
	 * @param  object $wp_admin_bar WP Admin Bar.
	 * @return void
	 */
	public function remove_wp_nodes( $wp_admin_bar ) {
		// WordPress Admin Settings.
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$wp_admin_menus   = isset( $wp_admin_options['remove_admin_menu'] ) ? $wp_admin_options['remove_admin_menu'] : array();

		// Avada Options.
		$avada_options         = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$avada_post_type_menus = isset( $avada_options['remove_post_type_menu'] ) ? $avada_options['remove_post_type_menu'] : array();

		// Fusion Slider Options.
		$fusion_slider_options = isset( $this->settings['fusion_branding']['fusion_slider'] ) ? $this->settings['fusion_branding']['fusion_slider'] : array();
		$fusion_slider_menus   = isset( $fusion_slider_options['remove_admin_menu'] ) ? $fusion_slider_options['remove_admin_menu'] : array();

		// Remove Fusion Slide menu from admin bar if selected.
		if ( is_array( $fusion_slider_menus ) && in_array( 'slider', $fusion_slider_menus, true ) ) {
			$wp_admin_bar->remove_node( 'new-slide' );
		}

		// Remove WP Logo is set to yes.
		if ( isset( $wp_admin_options['hide_wordpress_logo'] ) && $wp_admin_options['hide_wordpress_logo'] ) {
			$wp_admin_bar->remove_node( 'wp-logo' );
		}

		// Remove selected menus from admin bar.
		if ( ! empty( $wp_admin_menus ) ) {
			foreach ( $wp_admin_menus as $menu_slug ) {

				switch ( $menu_slug ) {

					case 'edit.php':
						$wp_admin_bar->remove_node( 'new-post' );
						break;

					case 'edit.php?post_type=page':
						$wp_admin_bar->remove_node( 'new-page' );
						break;

					case 'upload.php':
						$wp_admin_bar->remove_node( 'new-media' );
						break;

					case 'users.php':
						$wp_admin_bar->remove_node( 'new-user' );
						break;
				}
			}
		}

		// Remove admin bar menus for selected post types.
		if ( ! empty( $avada_post_type_menus ) ) {
			foreach ( $avada_post_type_menus as $key => $menu_slug ) {
				$wp_admin_bar->remove_node( 'new-' . $menu_slug );
			}
		}
	}

	/**
	 * Remove Avada menu item from admin bar on frontend.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function remove_wp_toolbar_menu() {
		global $wp_admin_bar, $avada_patcher;

		// Avada Options.
		$avada_options         = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$remove_admin_bar_menu = isset( $avada_options['remove_admin_bar_menu'] ) ? $avada_options['remove_admin_bar_menu'] : false;
		$admin_menu_dashicon   = isset( $avada_options['admin_menu_dashicon'] ) && '' !== $avada_options['admin_menu_dashicon'] ? 'dashicons-before ' . $avada_options['admin_menu_dashicon'] : '';
		$admin_menu_label      = isset( $avada_options['admin_menu_label'] ) && '' !== $avada_options['admin_menu_label'] ? $avada_options['admin_menu_label'] : __( 'Avada', 'Avada' );

		if ( $remove_admin_bar_menu || '' !== $admin_menu_dashicon ) {
			$wp_admin_bar->remove_node( 'avada' );
		}

		if ( ! is_admin() && '' !== $admin_menu_dashicon ) {
			$patches              = $avada_patcher->get_patcher_checker()->get_cache();
			$avada_updates_styles = 'display:inline-block;background-color:#d54e21;color:#fff;font-size:9px;line-height:17px;font-weight:600;border-radius:10px;padding:0 6px;';

			$avada_parent_menu_name  = $admin_menu_label;
			$avada_parent_menu_title = '<span class="ab-icon ' . $admin_menu_dashicon . '"></span><span class="ab-label">' . esc_html( $avada_parent_menu_name ) . '</span>';
			if ( isset( $patches['avada'] ) && 1 <= $patches['avada'] ) {
				$patches_label           = '<span style="' . $avada_updates_styles . '">' . $patches['avada'] . '</span>';
				$avada_parent_menu_title = '<span class="ab-icon"></span><span class="ab-label">' . esc_html( $avada_parent_menu_name ) . ' ' . $patches_label . '</span>';
			}

			$wp_admin_bar->add_node(
				array(
					'title'  => $avada_parent_menu_title,
					'parent' => false,
					'href'   => admin_url( 'admin.php?page=avada' ),
					'meta'   => array(
						'class' => 'avada-menu',
					),
					'id'     => 'avada',
				)
			);
		}
	}

	/**
	 * Update Page Builder MetaBox.
	 *
	 * @access public
	 * @since 1.0
	 * @param  string $post_type Post type slug.
	 * @return void
	 */
	public function update_builder_meta_box( $post_type ) {
		if ( class_exists( 'FusionBuilder' ) ) {
			$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();
			$screens                = $this->allowed_post_types();

			if ( post_type_supports( $post_type, 'editor' ) && ( isset( $fusion_builder_options['admin_menu_label'] ) && '' !== $fusion_builder_options['admin_menu_label'] ) ) {
				remove_meta_box( 'fusion_builder_layout', $screens, 'normal' );
				add_meta_box( 'fusion_builder_layout', '<span class="fusion-builder-logo fusion-branding-logo"></span><span class="fusion-builder-title">' . $fusion_builder_options['admin_menu_label'] . '</span><a href="https://theme-fusion.com/support/documentation/form-creator-documentation/" target="_blank" rel="noopener noreferrer"><span class="fusion-builder-help dashicons dashicons-editor-help"></span></a>', 'fusion_pagebuilder_meta_box', $screens, 'normal', 'high' );
			}
		}
	}

	/**
	 * Remove dashboard widgets.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function remove_dashboard_meta() {
		$wp_admin_options  = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$dashboard_widgets = isset( $wp_admin_options['remove_dashboard_widget'] ) && ! empty( $wp_admin_options['remove_dashboard_widget'] ) ? $wp_admin_options['remove_dashboard_widget'] : array();

		if ( ! empty( $dashboard_widgets ) ) {
			foreach ( $dashboard_widgets as $widget ) {
				switch ( $widget ) {
					case 'quick_press':
						remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
						break;
					case 'recent_drafts':
						remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
						break;
					case 'recent_comments':
						remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
						break;
					case 'right_now':
						remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
						break;
					case 'activity':
						remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
						break;
					case 'primary':
						remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
						break;
					case 'themefusion_news':
						remove_meta_box( 'themefusion_news', 'dashboard', 'side' );
						break;
					case 'gutenberg_panel':
						remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );
						break;
				}
			}
		}
	}

	/**
	 * Add custom welcome panel to dashboard.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function update_welcome_panel() {
		$screen = get_current_screen();
		if ( 'dashboard' === $screen->base ) {
			$wp_admin_options      = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
			$welcome_panel_title   = isset( $wp_admin_options['welcome_panel_title'] ) && ! empty( $wp_admin_options['welcome_panel_title'] ) ? $wp_admin_options['welcome_panel_title'] : '';
			$welcome_panel_content = isset( $wp_admin_options['welcome_panel_content'] ) && ! empty( $wp_admin_options['welcome_panel_content'] ) ? $wp_admin_options['welcome_panel_content'] : '';

			echo '<div class="wrap">';
			echo '<style type="text/css">.wrap h1:not(.dashboard-title) { display: none; }</style>';
			echo '<h1 class="dashboard-title">' . esc_html__( 'Dashboard' ) . '</h1>';

			echo '<div id="welcome-panel" class="welcome-panel">';

			// Display content for welcome panel.
			echo '<div class="fusion-white-label-branding-welcome-panel-wrapper">';

			// Display title for welcome panel.
			if ( '' !== $welcome_panel_title ) {
				echo '<h2 class="fusion-white-label-branding-welcome-panel-title">' . $welcome_panel_title . '</h2>'; // WPCS: XSS ok.
			}

			echo '<div class="fusion-white-label-branding-welcome-panel-content about-wrap">';
			echo $welcome_panel_content; // WPCS: XSS ok.
			echo '</div>';

			echo '</div>';

			echo '</div>';
			echo '</div>';

		}
	}

	/**
	 * Change admin page title.
	 *
	 * @access public
	 * @since 1.0
	 * @param  string $admin_title Admin page title.
	 * @return string $admin_title Updated admin page title.
	 */
	public function change_dashboard_title( $admin_title ) {
		global $current_screen, $title;

		if ( 'dashboard' !== $current_screen->id ) {
			return $admin_title;
		}

		// WP Admin Options.
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();

		// Change dasbhoard label.
		if ( isset( $wp_admin_options['dashboard_menu_label'] ) && '' !== $wp_admin_options['dashboard_menu_label'] ) {
			// @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
			$change_title = $title = $wp_admin_options['dashboard_menu_label'];
			$admin_title  = str_replace( esc_html__( 'Dashboard' ), $change_title, $admin_title );
		}

		return $admin_title;
	}

	/**
	 * Handles text displayed for admin footer.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $text The default text provided by WordPress.
	 * @return void
	 */
	public function admin_footer_text( $text ) {
		global $allowedposttags;

		$wp_admin_options  = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$admin_footer_text = isset( $wp_admin_options['admin_footer_text'] ) && ! empty( $wp_admin_options['admin_footer_text'] ) ? $wp_admin_options['admin_footer_text'] : '';

		if ( '' !== $admin_footer_text ) {
			$text = wp_kses( $admin_footer_text, $allowedposttags );
		}

		echo $text; // WPCS: XSS ok.
	}

	/**
	 * Handles text displayed for admin footer.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $text The default text provided by WordPress.
	 * @return void
	 */
	public function admin_footer_version_text( $text ) {
		global $allowedposttags;

		$wp_admin_options          = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$admin_footer_version_text = isset( $wp_admin_options['admin_footer_version_text'] ) && ! empty( $wp_admin_options['admin_footer_version_text'] ) ? $wp_admin_options['admin_footer_version_text'] : '';

		if ( '' !== $admin_footer_version_text ) {
			$text = wp_kses( $admin_footer_version_text, $allowedposttags );
		}

		echo $text; // WPCS: XSS ok.
	}

	/**
	 * Handles screen options tab removal.
	 *
	 * @access public
	 * @since 1.0
	 * @param bool $show_screen Whether to show Screen Options tab. Default true.
	 * @return bool Returns true or false depend on option to show Screen Options tab.
	 */
	public function remove_screen_options( $show_screen ) {
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$screen_options   = isset( $wp_admin_options['remove_screen_options'] ) && ! empty( $wp_admin_options['remove_screen_options'] ) ? $wp_admin_options['remove_screen_options'] : '0';

		if ( $screen_options ) {
			return false;
		} else {
			return $show_screen;
		}
	}

	/**
	 * Removes the Help tab in the WP Admin.
	 *
	 * @param array $old_help  Old help tabs array.
	 * @param int   $screen_id Current Screen ID.
	 * @param obj   $screen    Current Screen.
	 * @return array
	 */
	public function remove_help_tabs( $old_help, $screen_id, $screen ) {
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$help_tab         = isset( $wp_admin_options['remove_help_tab'] ) && ! empty( $wp_admin_options['remove_help_tab'] ) ? $wp_admin_options['remove_help_tab'] : '0';

		if ( $help_tab ) {
			$screen->remove_help_tabs();
		}

		return $old_help;
	}

	/**
	 * Builder is displayed on the following post types.
	 *
	 * @access public
	 * @since 1.0
	 * @return array Returns allowed post types array.
	 */
	private function allowed_post_types() {

		$options = get_option( 'fusion_builder_settings', array() );

		if ( ! empty( $options ) && isset( $options['post_types'] ) ) {
			// If there are options saved, used them.
			$post_types = ( ' ' === $options['post_types'] ) ? array() : $options['post_types'];
			return apply_filters( 'fusion_builder_allowed_post_types', $post_types );
		} else {
			// Otherwise use defaults.
			return FusionBuilder::default_post_types();
		}

	}

	/**
	 * Handles the saving of branding settings in admin area.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function settings_save() {
		global $allowedposttags;
		check_admin_referer( 'fusion_branding_save_settings', 'fusion_branding_save_settings' );

		$settings = get_option( 'fusion_branding_settings', array() );
		// @codingStandardsIgnoreLine WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
		$section  = isset( $_POST['section'] ) ? wp_unslash( $_POST['section'] ) : '';
		$settings['fusion_branding'] = ( isset( $settings['fusion_branding'] ) ) ? $settings['fusion_branding'] : array();

		// Assign section settings.
		if ( isset( $_POST['fusion_branding'] ) && isset( $_POST['fusion_branding'][ $section ] ) ) {
			// @codingStandardsIgnoreLine WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
			$settings['fusion_branding'][ $section ] = wp_unslash( $_POST['fusion_branding'][ $section ] );
		}

		if ( 'wp_admin' === $section ) {
			$settings['fusion_branding'][ $section ]['welcome_panel_content'] = wp_kses( $settings['fusion_branding'][ $section ]['welcome_panel_content'], $allowedposttags );
			$settings['fusion_branding'][ $section ]['admin_footer_text']     = wp_kses( $settings['fusion_branding'][ $section ]['admin_footer_text'], $allowedposttags );
		}

		if ( 'fusion_builder' === $section ) {
			$settings['fusion_branding'][ $section ]['welcome_screen_content']    = wp_kses( $settings['fusion_branding'][ $section ]['welcome_screen_content'], $allowedposttags );
			$settings['fusion_branding'][ $section ]['welcome_screen_about_text'] = wp_kses( stripslashes( $settings['fusion_branding'][ $section ]['welcome_screen_about_text'] ), $allowedposttags );
		}

		if ( 'avada' === $section ) {
			$settings['fusion_branding'][ $section ]['welcome_screen_content']    = wp_kses( $settings['fusion_branding'][ $section ]['welcome_screen_content'], $allowedposttags );
			$settings['fusion_branding'][ $section ]['welcome_screen_about_text'] = wp_kses( stripslashes( $settings['fusion_branding'][ $section ]['welcome_screen_about_text'] ), $allowedposttags );
		}

		// Update settings.
		update_option( 'fusion_branding_settings', $settings );

		// Redirect back to the corresponding section.
		wp_safe_redirect( admin_url( 'admin.php?page=fusion-white-label-branding-settings&section=' . $section ) );
		exit;
	}

	/**
	 * Handles the login screen branding.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function login_screen_branding() {
		$login_screen_options = ( isset( $this->settings['fusion_branding'] ) && isset( $this->settings['fusion_branding']['login_screen'] ) ) ? $this->settings['fusion_branding']['login_screen'] : array();

		if ( ! empty( $login_screen_options ) ) {
			echo '<div class="fusion-branding-overlay"></div>';
			echo '<style type="text/css">';

			if ( isset( $login_screen_options['login_background_color'] ) && '' !== $login_screen_options['login_background_color'] ) {
				echo '.fusion-branding-overlay{position:fixed;top:0;left:0;bottom:0;width:100%;height:100%;background-color:' . esc_attr( $login_screen_options['login_background_color'] ) . ';}';
				echo '#login {position: relative;}';
			}

			if ( isset( $login_screen_options['login_background_image'] ) && '' !== $login_screen_options['login_background_image'] ) {
				echo 'body.login{background-image:url( "' . esc_attr( $login_screen_options['login_background_image'] ) . '");background-repeat:no-repeat;background-position:center center;background-size:cover;}';
			}

			if ( isset( $login_screen_options['login_logo_image'] ) && '' !== $login_screen_options['login_logo_image'] ) {
				echo 'body.login h1 a{background-image: none, url( "' . esc_attr( $login_screen_options['login_logo_image'] ) . '");background-size:contain;width:auto;}';
			}

			if ( isset( $login_screen_options['login_box_background_color'] ) && '' !== $login_screen_options['login_box_background_color'] ) {
				echo 'body.login form{background-color:' . esc_attr( $login_screen_options['login_box_background_color'] ) . ';box-shadow:0 1px 3px ' . esc_attr( $login_screen_options['login_box_background_color'] ) . ';}';
			}

			if ( isset( $login_screen_options['login_box_text_color'] ) && '' !== $login_screen_options['login_box_text_color'] ) {
				echo 'body.login form label{color:' . esc_attr( $login_screen_options['login_box_text_color'] ) . ';}';
			}

			if ( isset( $login_screen_options['login_box_link_color'] ) && '' !== $login_screen_options['login_box_link_color'] ) {
				echo 'body.login #backtoblog a,body.login #nav a{color:' . esc_attr( $login_screen_options['login_box_link_color'] ) . ';}';
			}

			if ( isset( $login_screen_options['login_box_link_hover_color'] ) && '' !== $login_screen_options['login_box_link_hover_color'] ) {
				echo 'body.login #backtoblog a:hover,body.login #nav a:hover{color:' . esc_attr( $login_screen_options['login_box_link_hover_color'] ) . ';}';
			}

			$button_default = isset( $login_screen_options['login_button_background_color'] ) ? $login_screen_options['login_button_background_color'] : '';

			if ( '' !== $button_default ) {
				echo '#wp-submit{background:' . esc_attr( $button_default ) . ';border-color:' . esc_attr( $button_default ) . ';box-shadow:0 1px 0 ' . esc_attr( $button_default ) . ';text-decoration:none;text-shadow:0 -1px 1px ' . esc_attr( $button_default ) . ',1px 0 1px ' . esc_attr( $button_default ) . ',0 1px 1px ' . esc_attr( $button_default ) . ',-1px 0 1px ' . esc_attr( $button_default ) . ';}';
			}

			$button_text = isset( $login_screen_options['login_button_accent_color'] ) ? $login_screen_options['login_button_accent_color'] : '';

			if ( '' !== $button_text ) {
				echo '#wp-submit{color:' . esc_attr( $button_text ) . ';}';
			}

			$button_hover = isset( $login_screen_options['login_button_background_color_hover'] ) ? $login_screen_options['login_button_background_color_hover'] : '';

			if ( '' !== $button_hover ) {
				echo '#wp-submit:hover{background:' . esc_attr( $button_hover ) . ';border-color:' . esc_attr( $button_hover ) . ';}';
			}

			$text_hover = isset( $login_screen_options['login_button_accent_color_hover'] ) ? $login_screen_options['login_button_accent_color_hover'] : '';

			if ( '' !== $text_hover ) {
				echo '#wp-submit:hover{color:' . esc_attr( $text_hover ) . ';}';
			}

			if ( isset( $login_screen_options['remove_lost_password'] ) && $login_screen_options['remove_lost_password'] ) {
				echo '#nav a:last-child{display:none;}';
			}

			echo '</style>';

		}
	}

	/**
	 * Update strings with the new ones.
	 *
	 * @access public
	 * @since 1.0
	 * @param  string $text     String passed via gettext filter.
	 * @param  string $old_text Untranslated string.
	 * @param  string $domain   Text-domain.
	 * @return string $text Updated string.
	 */
	public function update_branding_strings( $text, $old_text, $domain ) {
		global $pagenow;

		// WP Admin Settings.
		$login_screen_options = ( isset( $this->settings['fusion_branding']['login_screen'] ) ) ? $this->settings['fusion_branding']['login_screen'] : array();

		// Avada Options.
		$avada_options = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();

		// Fusion Slider Options.
		$fusion_slider_options = isset( $this->settings['fusion_branding']['fusion_slider'] ) ? $this->settings['fusion_branding']['fusion_slider'] : array();

		// Fusion Builder Options.
		$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();

		if ( isset( $login_screen_options['remove_lost_password'] ) && $login_screen_options['remove_lost_password'] ) {
			add_filter( 'login_link_separator', '__return_false' );
		}

		if ( isset( $avada_options['admin_menu_label'] ) && '' !== $avada_options['admin_menu_label'] ) {
			$text = str_replace( 'Avada', $avada_options['admin_menu_label'], trim( $text, '?' ) );
		}

		if ( isset( $avada_options['theme_options_menu_label'] ) && '' !== $avada_options['theme_options_menu_label'] ) {
			$text = str_replace( 'Theme Options', $avada_options['theme_options_menu_label'], trim( $text, '?' ) );
		}

		if ( isset( $fusion_builder_options['admin_menu_label'] ) && '' !== $fusion_builder_options['admin_menu_label'] && 'plugins.php' !== $pagenow ) {
			$text = str_replace( 'Fusion Builder', $fusion_builder_options['admin_menu_label'], trim( $text, '?' ) );
		}

		$post_types = isset( $avada_options['rename_admin_menu'] ) ? $avada_options['rename_admin_menu'] : array();

		if ( class_exists( 'FusionCore_Plugin' ) ) {
			if ( 'fusion-core' == $domain ) {
				$portfolio_label = isset( $post_types['portfolio'] ) ? $post_types['portfolio'] : '';
				if ( '' !== $portfolio_label ) {
					$text = str_replace( 'Portfolio', $portfolio_label, trim( $text, '?' ) );
				}

				if ( isset( $fusion_slider_options['import_export_label'] ) && '' !== $fusion_slider_options['import_export_label'] ) {
					$text = str_replace( 'Export / Import', $fusion_slider_options['import_export_label'], trim( $text, '?' ) );
				}
			}

			// Change Fusion Slider name for Page Options.
			if ( isset( $fusion_slider_options['admin_menu_label'] ) && '' !== $fusion_slider_options['admin_menu_label'] ) {
				$slider_label = $fusion_slider_options['admin_menu_label'];
				$text = str_replace( 'Fusion Slider', $slider_label, trim( $text, '?' ) );
			}

			$slider_export_label = ( isset( $fusion_slider_options['import_export_label'] ) && '' !== $fusion_slider_options['import_export_label'] ) ? $fusion_slider_options['import_export_label'] : '';
			if ( '' !== $slider_export_label ) {
				$text = str_replace( 'Export / Import', $slider_export_label, trim( $text, '?' ) );
			}
		}

		return $text;
	}

	/**
	 * Admin styles.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function admin_menu_styling() {

		$style = '<style type="text/css" id="fusion-white-label-branding-menu-css">';

		if ( ! class_exists( 'Avada' ) ) :
			$style .= '.wp-menu-image.dashicons-before.dashicons-fusion-white-label-logo {
				background: url(' . FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL . 'assets/images/themefusion.svg ) no-repeat center center;
				background-size: 18px;
			}';
		else :
			$style .= '.wp-menu-image.dashicons-before.dashicons-fusion-white-label-logo:before {
				content: "\e62d";
				font-family: \'icomoon\';
				speak: none;
				font-style: normal;
				font-weight: normal;
				font-variant: normal;
				text-transform: none;
				line-height: 1;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}';
		endif;

		$style .= '</style>';

		echo $style; // WPCS: XSS ok.
	}

	/**
	 * Admin styles.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function fusion_branding_admin_styles() {
		// Avada Options.
		$avada_options = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$avada_menus   = isset( $avada_options['remove_admin_menu'] ) ? $avada_options['remove_admin_menu'] : array();

		// Fusion Builder Options.
		$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();
		$fusion_builder_menus   = isset( $fusion_builder_options['remove_admin_menu'] ) ? $fusion_builder_options['remove_admin_menu'] : array();

		// WordPress Admin Options.
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$admin_custom_css = isset( $wp_admin_options['admin_custom_css'] ) && '' !== $wp_admin_options['admin_custom_css'] ? $wp_admin_options['admin_custom_css'] : '';

		// Fusion Slider Options.
		$fusion_slider_options = isset( $this->settings['fusion_branding']['fusion_slider'] ) ? $this->settings['fusion_branding']['fusion_slider'] : array();

		$dashboard_widgets = isset( $wp_admin_options['remove_dashboard_widget'] ) && ! empty( $wp_admin_options['remove_dashboard_widget'] ) ? $wp_admin_options['remove_dashboard_widget'] : array();

		$admin_tab_selector = array();

		$style = '<style type="text/css">';

		// Remove selected tabs under Avada admin pages.
		if ( ! empty( $avada_menus ) ) {
			foreach ( $avada_menus as $menu_slug ) {
				$admin_tab_selector[] = '.avada-wrap .nav-tab[href$="' . $menu_slug . '"]';
			}
		}

		// Remove selected tabs under Fusion Builder admin pages.
		if ( ! empty( $fusion_builder_menus ) ) {
			foreach ( $fusion_builder_menus as $menu_slug ) {
				$admin_tab_selector[] = '.fusion-builder-wrap .nav-tab[href$="' . $menu_slug . '"]';
			}
		}

		if ( ! empty( $admin_tab_selector ) ) {
			// Enqueue css to hide tabs.
			$style .= implode( ',', $admin_tab_selector ) . ' { display: none; }';
		}

		// Update Avada admin menu icon logo.
		if ( isset( $avada_options['avada_icon_image'] ) && '' !== $avada_options['avada_icon_image'] ) :
			$style .= '#wpadminbar .avada-menu > .ab-item .ab-icon:before,
			#toplevel_page_avada .dashicons-fusiona-logo:before{';
			$style .= 'background-image: url( ' . $avada_options['avada_icon_image'] . ' );';
			$style .= 'content: "";
				background-size: 20px 20px;
				background-repeat: no-repeat;
				background-position: center;';
			$style .= '}';
		endif;

		// Update theme options logo.
		if ( isset( $avada_options['avada_logo_image'] ) && '' !== $avada_options['avada_logo_image'] ) :
			$style .= '.fusionredux-container #fusionredux-form-wrapper .fusion-redux-sidebar-head .fusion-redux-logo {';
			$style .= 'background: url( ' . $avada_options['avada_logo_image'] . ' ) no-repeat center !important;';
			$style .= 'background-size: contain !important;';
			$style .= '}';
			$style .= '.avada-wrap .avada-logo {';
			$style .= 'background: #fff url( ' . $avada_options['avada_logo_image'] . ' ) no-repeat center !important;';
			$style .= 'background-size: 150px !important;';
			$style .= '}';
		endif;

		// Update Fusion Builder version number box.
		if ( isset( $avada_options['version_number_text'] ) && '' !== $avada_options['version_number_text'] ) :
			$background_color = isset( $avada_options['version_number_box_background'] ) ? $avada_options['version_number_box_background'] : '#A0CE4E';
			$text_color       = isset( $avada_options['version_number_box_color'] ) && '' !== $avada_options['version_number_box_color'] ? $avada_options['version_number_box_color'] : '#ffffff';

			$style .= '.avada-version:before {
				content: "' . $avada_options['version_number_text'] . '";
				position: absolute;
				left: 0;
				width: 100%;
				color: ' . $text_color . ' !important;
			}';
			$style .= '.avada-wrap .avada-version {
				color: rgba(0,0,0,0) !important;
				background: ' . $background_color . ' !important;
			}';
		else :
			$avada_version_number_styles = '';

			if ( ! empty( $avada_options['version_number_box_background'] ) ) {
				$avada_version_number_styles .= 'background: ' . $avada_options['version_number_box_background'] . ' !important;';
			}

			if ( ! empty( $avada_options['version_number_box_color'] ) ) {
				$avada_version_number_styles .= 'color: ' . $avada_options['version_number_box_color'] . ' !important;';
			}

			if ( $avada_version_number_styles ) {
				$style .= '.avada-wrap .avada-version { ' . $avada_version_number_styles . '}';
			}
		endif;

		// Update Fusion Builder logo.
		if ( isset( $fusion_builder_options['fusion_builder_icon_image'] ) && '' !== $fusion_builder_options['fusion_builder_icon_image'] ) :
			$style .= '#toplevel_page_fusion-builder-options .wp-menu-image:before {';
			$style .= 'background-image: url( ' . $fusion_builder_options['fusion_builder_icon_image'] . ' ) !important;';
			$style .= 'content: "";
				background-size: 20px 20px;
				background-repeat: no-repeat;
				background-position: center;';
			$style .= '}';
		endif;

		// Update Fusion Builder logo.
		if ( isset( $fusion_builder_options['fusion_builder_logo_image'] ) && '' !== $fusion_builder_options['fusion_builder_logo_image'] ) :
			$style .= '#fusion_builder_layout .fusion-builder-logo {';
			$style .= 'background: url( ' . $fusion_builder_options['fusion_builder_logo_image'] . ' ) no-repeat center !important;';
			$style .= 'background-size: contain !important;';
			$style .= '}';
			$style .= '.fusion-builder-wrap .fusion-builder-logo {';
			$style .= 'background: #fff url( ' . $fusion_builder_options['fusion_builder_logo_image'] . ' ) no-repeat center !important;';
			$style .= 'background-size: 150px !important;';
			$style .= '}';
			$style .= '.fusiona-FB_logo_black:not(.fusion_builder_is_active):before, #fusion_builder_switch:before {';
			$style .= 'content: "" !important;';
			$style .= 'background: url( ' . $fusion_builder_options['fusion_builder_logo_image'] . ' ) no-repeat center !important;';
			$style .= 'background-size: 22px !important;';
			$style .= 'width: 22px;';
			$style .= 'height: 22px;';
			$style .= 'display: inline-block;';
			$style .= '}';
			$style .= '#fusion_builder_switch:before {';
			$style .= 'height: 40px;';
			$style .= '}';
		endif;

		// Update Fusion Builder version number box.
		if ( isset( $fusion_builder_options['version_number_text'] ) && '' !== $fusion_builder_options['version_number_text'] ) :
			$background_color = isset( $fusion_builder_options['version_number_box_background'] ) ? $fusion_builder_options['version_number_box_background'] : '#2d2d2d';
			$text_color       = isset( $fusion_builder_options['version_number_box_color'] ) && '' !== $fusion_builder_options['version_number_box_color'] ? $fusion_builder_options['version_number_box_color'] : '#ffffff';

			$style .= '.fusion-builder-version:before {
				content: "' . $fusion_builder_options['version_number_text'] . '";
				position: absolute;
				left: 0;
				width: 100%;
				color: ' . $text_color . ' !important;
			}';
			$style .= '.fusion-builder-wrap .fusion-builder-version {
				color: rgba(0,0,0,0) !important;
				background: ' . $background_color . ' !important;
			}';
		else :
			$fb_version_number_styles = '';

			if ( ! empty( $fusion_builder_options['version_number_box_background'] ) ) {
				$fb_version_number_styles .= 'background: ' . $fusion_builder_options['version_number_box_background'] . ' !important;';
			}

			if ( ! empty( $fusion_builder_options['version_number_box_color'] ) ) {
				$fb_version_number_styles .= 'color: ' . $fusion_builder_options['version_number_box_color'] . ' !important;';
			}

			if ( $fb_version_number_styles ) {
				$style .= '.fusion-builder-wrap .fusion-builder-version { ' . $fb_version_number_styles . '}';
			}
		endif;

		// Update Fusion Slider admin menu icon logo.
		if ( isset( $fusion_slider_options['fusion_slider_icon_image'] ) && '' !== $fusion_slider_options['fusion_slider_icon_image'] ) :
			$style .= '#menu-posts-slide .dashicons-fusiona-logo:before{';
			$style .= 'background-image: url( ' . $fusion_slider_options['fusion_slider_icon_image'] . ' );';
			$style .= 'content: "";
				background-size: 20px 20px;
				background-repeat: no-repeat;
				background-position: center;';
			$style .= '}';
		endif;

		// Update Fusion Slider element icon.
		if ( isset( $fusion_slider_options['fusion_slider_icon_image'] ) && '' !== $fusion_slider_options['fusion_slider_icon_image'] ) :
			$style .= '.fusion-module-icon.fusiona-TFicon:before{';
			$style .= 'background-image: url( ' . $fusion_slider_options['fusion_slider_icon_image'] . ' );';
			$style .= 'content: "";
				background-size: 14px 14px;
				background-repeat: no-repeat;
				background-position: center;
				width: 14px;
				height: 14px;
				display: inline-block;';
			$style .= '}';
		endif;

		// Update Fusion White Label Branding admin menu icon logo.
		if ( isset( $avada_options['fusion_white_label_branding_icon_image'] ) && '' !== $avada_options['fusion_white_label_branding_icon_image'] ) :
			$style .= '#toplevel_page_fusion-white-label-branding-admin .dashicons-fusion-white-label-logo:before {';
			$style .= 'content: ""';
			$style .= '}';
			$style .= '#toplevel_page_fusion-white-label-branding-admin .dashicons-fusion-white-label-logo {';
			$style .= 'background-image: url( ' . $avada_options['fusion_white_label_branding_icon_image'] . ' );';
			$style .= 'background-size: 20px 20px;
				background-repeat: no-repeat;
				background-position: center;';
			$style .= '}';
		endif;

		// Add styling for welcome panel.
		$style .= '.fusion-white-label-branding-welcome-panel-content.about-wrap {
		  max-width: 100%;
			margin-right: 25px;
			padding-bottom: 25px;
		}';
		$style .= 'h2.fusion-white-label-branding-welcome-panel-title {
		  margin: 0 20px;
		}';

		if ( in_array( 'gutenberg_panel', $dashboard_widgets ) ) {
			$style .= '#try-gutenberg-panel {
			  display: none !important;
			}';
		}

		if ( '' !== $admin_custom_css ) {
			$style .= $admin_custom_css;
		}

		$style .= '</style>';

		echo $style; // WPCS: XSS ok.
	}

	/**
	 * Admin styles.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function fusion_branding_frontend_styles() {
		// Avada Options.
		$avada_options = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$avada_menus   = isset( $avada_options['remove_admin_menu'] ) ? $avada_options['remove_admin_menu'] : array();

		if ( isset( $avada_options['avada_icon_image'] ) && '' !== $avada_options['avada_icon_image'] ) :
			$style  = '<style type="text/css" id="fusion-branding-style">';
			$style .= '#wpadminbar .avada-menu>.ab-item .ab-icon:before {';
			$style .= 'background: url( ' . $avada_options['avada_icon_image'] . ' ) no-repeat center !important;';
			$style .= 'background-size: auto !important;';
			$style .= 'content: "";
						width: 21px;
						height: 21px;
						display: inline-block;
						background-size: contain !important;';
			$style .= '}';
			$style .= '</style>';

			echo $style; // WPCS: XSS ok.
		endif;
	}

	/**
	 * Admin scripts and styles.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_scripts_and_styles() {
		global $pagenow;

		// Add the color picker css file.
		wp_enqueue_script( 'jquery-color' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// ColorPicker Alpha Channel.
		wp_enqueue_script( 'wp-color-picker-alpha', FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL . 'assets/js/wp-color-picker-alpha.js', array( 'wp-color-picker', 'jquery-color' ), FUSION_WHITE_LABEL_BRANDING_VERSION, true );

		// Add media uploader scripts and styles.
		wp_enqueue_media();

		wp_enqueue_style( 'fusion_branding_admin_css', FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL . 'assets/css/fusion-white-label-branding-admin.min.css', array(), FUSION_WHITE_LABEL_BRANDING_VERSION );
		wp_enqueue_script( 'fusion_branding_admin_js', FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL . 'assets/js/fusion-white-label-branding-admin.min.js', array( 'jquery', 'wp-color-picker-alpha' ), FUSION_WHITE_LABEL_BRANDING_VERSION, true );
	}

	/**
	 * Admin styles.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_styles() {
		wp_enqueue_style( 'fusion_branding_admin_css', FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL . 'assets/css/fusion-white-label-branding-admin.min.css', array(), FUSION_WHITE_LABEL_BRANDING_VERSION );
	}

	/**
	 * Loads the template file.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function welcome_tab() {
		require_once wp_normalize_path( dirname( __FILE__ ) . '/admin-screens/welcome.php' );
	}

	/**
	 * Loads the template file.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function support_tab() {
		require_once wp_normalize_path( dirname( __FILE__ ) . '/admin-screens/support.php' );
	}

	/**
	 * Loads the template file.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function faq_tab() {
		require_once wp_normalize_path( dirname( __FILE__ ) . '/admin-screens/faq.php' );
	}

	/**
	 * Loads the settings template file.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function branding_settings_tab() {
		require_once wp_normalize_path( dirname( __FILE__ ) . '/admin-screens/branding-settings.php' );
	}

	/**
	 * Handles settings import / export.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function settings_import_export_tab() {
		require_once wp_normalize_path( dirname( __FILE__ ) . '/admin-screens/import-export.php' );
	}

	/**
	 * Add the title.
	 *
	 * @static
	 * @access protected
	 * @since 1.0
	 * @param string $title The title.
	 * @param string $page  The page slug.
	 */
	protected static function admin_tab( $title, $page ) {

		if ( isset( $_GET['page'] ) ) {
			$active_page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // WPCS: CSRF ok.
		}

		if ( $active_page === $page ) {
			$link       = 'javascript:void(0);';
			$active_tab = ' nav-tab-active';
		} else {
			$link       = 'admin.php?page=' . $page;
			$active_tab = '';
		}

		echo '<a href="' . esc_attr( $link ) . '" class="nav-tab' . esc_attr( $active_tab ) . '">' . esc_attr( $title ) . '</a>';

	}

	/**
	 * Adds the footer.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public static function footer() {
		?>
		<div class="fusion-white-label-branding-thanks">
			<p class="description"><?php esc_html_e( 'Thank you for choosing Fusion White Label Branding. We are honored and are fully dedicated to making your experience perfect.', 'fusion-white-label-branding' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Adds the header.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public static function header() {
		?>
		<h1 class="wp-heading-inline-main"><?php esc_html_e( 'Welcome to Fusion White Label Branding!', 'fusion-white-label-branding' ); ?></h1>
		<div class="about-text">
			<?php
			/* translators: Avada */
			printf( esc_attr__( 'Fusion White Label Branding is now installed and ready to use! You can brand %1$s and the constituent functionality with your own custom branding through easy to use options! Click on the settings tab to find out all you can do with this amazing Fusion White Label Branding plugin. Recommended compatibility is %1$s 5.4 and newer.', 'fusion-white-label-branding' ), 'Avada' );
			?>
		</div>
		<div class="fusion-white-label-branding-logo">
			<span class="fusion-white-label-branding-version">
				<?php
				/* translators: The version number. */
				printf( esc_attr__( 'Version %s', 'fusion-white-label-branding' ), FUSION_WHITE_LABEL_BRANDING_VERSION ); // WPCS: XSS ok.
				?>
			</span>
		</div>
		<h2 class="nav-tab-wrapper">
			<?php
			self::admin_tab( esc_attr__( 'Welcome', 'fusion-white-label-branding' ), 'fusion-white-label-branding-admin' );
			self::admin_tab( esc_attr__( 'FAQ', 'fusion-white-label-branding' ), 'fusion-white-label-branding-faq' );
			self::admin_tab( esc_attr__( 'Support', 'fusion-white-label-branding' ), 'fusion-white-label-branding-support' );
			self::admin_tab( esc_attr__( 'Settings', 'fusion-white-label-branding' ), 'fusion-white-label-branding-settings' );
			self::admin_tab( esc_attr__( 'Import / Export', 'fusion-white-label-branding' ), 'fusion-white-label-branding-import-export' );
			?>
		</h2>
		<?php
	}

	/**
	 * Adds the branding setting links.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public static function branding_links() {
		$sections = array(
			'wp_admin' => esc_html__( 'WordPress Admin', 'fusion-white-label-branding' ),
		);

		// Add Avada if installed and activated.
		if ( class_exists( 'Avada' ) ) {
			$sections['avada'] = 'Avada';
		}

		// Add Fusion Builder if installed and activated.
		if ( class_exists( 'FusionBuilder' ) ) {
			$sections['fusion_builder'] = 'Fusion Builder';
		}

		// Add Fusion Slider if Fusion Core installed and activated.
		if ( class_exists( 'FusionCore_Plugin' ) ) {
			$sections['fusion_slider'] = 'Fusion Slider';
		}

		// Put wp login section at end.
		$sections['login_screen'] = esc_html__( 'WordPress Login Screen', 'fusion-white-label-branding' );

		// @codingStandardsIgnoreLine WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
		$current_section = ( isset( $_GET['section'] ) ) ? wp_unslash( $_GET['section'] ) : 'wp_admin';
		$c               = count( $sections );
		?>
		<ul class="subsubsub">
			<?php
			$i = 1;
			foreach ( $sections as $section => $title ) {
				$active_section = ( $section === $current_section ) ? 'current' : '';
				$sep            = ( $i !== $c ) ? '|' : '';
				$link           = ( 'current' === $active_section ) ? 'javascript:void(0);' : 'admin.php?page=fusion-white-label-branding-settings&section=' . $section;
				?>
				<li>
					<?php
						printf(
							'<a href="%1$s" class="%2$s">%3$s</a> %4$s',
							esc_attr( $link ), // esc_attr to make the active sub-tab non-clickable.
							esc_attr( $active_section ),
							esc_attr( $title ),
							esc_attr( $sep )
						);
					?>
				</li>
				<?php
				$i++;
			}
			?>
		</ul>
		<br class="clear">
		<?php
	}

	/**
	 * Change the post type labels for portfolio and faq.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function change_post_object_label() {
		global $wp_post_types, $wp_taxonomies;

		// Avada Options.
		$avada_options = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$post_types    = isset( $avada_options['rename_admin_menu'] ) ? $avada_options['rename_admin_menu'] : array();

		// Fusion Slider Options.
		$fusion_slider_options = isset( $this->settings['fusion_branding']['fusion_slider'] ) ? $this->settings['fusion_branding']['fusion_slider'] : array();

		if ( ! class_exists( 'FusionCore_Plugin' ) ) {
			return;
		}

		// Change taxonomy labels for slide-page.
		$sliders_menu_label = isset( $fusion_slider_options['add_edit_sliders_label'] ) ? $fusion_slider_options['add_edit_sliders_label'] : '';
		$sliders_label      = isset( $fusion_slider_options['sliders_label'] ) ? $fusion_slider_options['sliders_label'] : '';

		if ( isset( $wp_taxonomies['slide-page'] ) ) {
			$slider_labels = $wp_taxonomies['slide-page']->labels;

			if ( '' !== $sliders_menu_label ) {
				$slider_labels->menu_name = $sliders_menu_label;
			}

			if ( '' !== $sliders_label ) {
				$slider_labels->name      = $sliders_label;
				$slider_labels->all_items = $sliders_label;

				/* translators: The taxonomy (slide-page) name. */
				$slider_labels->view_item = sprintf( esc_html__( 'View %s', 'fusion-white-label-branding' ), $sliders_label );

				/* translators: The taxonomy (slide-page) name. */
				$slider_labels->add_new_item = sprintf( esc_html__( 'Add New %s', 'fusion-white-label-branding' ), $sliders_label );

				/* translators: The taxonomy (slide-page) name. */
				$slider_labels->edit_item = sprintf( esc_html__( 'Edit %s', 'fusion-white-label-branding' ), $sliders_label );
			}

			unset( $post_types['slide-page'] );
		}

		// Change taxonomy labels for slide post type.
		$slides_menu_label = isset( $fusion_slider_options['add_edit_slide_label'] ) ? $fusion_slider_options['add_edit_slide_label'] : '';
		$slides_label      = isset( $fusion_slider_options['admin_menu_label'] ) ? $fusion_slider_options['admin_menu_label'] : '';

		if ( isset( $wp_post_types['slide'] ) ) {
			$slide_labels = $wp_post_types['slide']->labels;

			if ( '' !== $slides_menu_label ) {
				$slide_labels->all_items = $slides_menu_label;
			}

			if ( '' !== $slides_label ) {
				$slide_labels->name = $slides_label;

				/* translators: The taxonomy (slide-page) name. */
				$slide_labels->view_item = sprintf( esc_html__( 'View %s', 'fusion-white-label-branding' ), $slides_label );

				/* translators: The taxonomy (slide-page) name. */
				$slide_labels->add_new_item = sprintf( esc_html__( 'Add New %s', 'fusion-white-label-branding' ), $slides_label );

				/* translators: The taxonomy (slide-page) name. */
				$slide_labels->edit_item = sprintf( esc_html__( 'Edit %s', 'fusion-white-label-branding' ), $slides_label );
			}
		}

		// Change taxonomy labels for skills.
		$skills_label = isset( $post_types['skills'] ) ? $post_types['skills'] : '';

		if ( isset( $wp_taxonomies['portfolio_skills'] ) && '' !== $skills_label ) {
			$skill_labels            = $wp_taxonomies['portfolio_skills']->labels;
			$skill_labels->menu_name = $skills_label;
			$skill_labels->name      = $skills_label;

			/* translators: The taxonomy (skills) name. */
			$skill_labels->view_item = sprintf( esc_html__( 'View %s', 'fusion-white-label-branding' ), $skills_label );

			/* translators: The taxonomy (skills) name. */
			$skill_labels->add_new_item = sprintf( esc_html__( 'Add New %s', 'fusion-white-label-branding' ), $skills_label );

			/* translators: The taxonomy (skills) name. */
			$skill_labels->edit_item = sprintf( esc_html__( 'Edit %s', 'fusion-white-label-branding' ), $skills_label );
			unset( $post_types['skills'] );
		}

		// Change taxonomy labels for tags.
		$tags_label = isset( $post_types['tags'] ) ? $post_types['tags'] : '';

		if ( isset( $wp_taxonomies['portfolio_tags'] ) && '' !== $tags_label ) {
			$tag_labels            = $wp_taxonomies['portfolio_tags']->labels;
			$tag_labels->menu_name = $tags_label;
			$tag_labels->name      = $tags_label;

			/* translators: The taxonomy name. */
			$tag_labels->view_item = sprintf( esc_html__( 'View %s', 'fusion-white-label-branding' ), $tags_label );

			/* translators: The taxonomy name. */
			$tag_labels->add_new_item = sprintf( esc_html__( 'Add New %s', 'fusion-white-label-branding' ), $tags_label );

			/* translators: The taxonomy name. */
			$tag_labels->edit_item = sprintf( esc_html__( 'Edit %s', 'fusion-white-label-branding' ), $tags_label );
			unset( $post_types['tags'] );
		}

		foreach ( $post_types as $post_type => $label ) {

			if ( '' !== $label && isset( $wp_post_types[ 'avada_' . $post_type ] ) ) {
				// Change post type labels for portfolio and faq.
				$labels                = $wp_post_types[ 'avada_' . $post_type ]->labels;
				$labels->menu_name     = $label;
				$labels->name          = $label;
				$labels->singular_name = $label;

				/* translators: The post-type name. */
				$labels->add_new = sprintf( esc_html__( 'Add %s', 'fusion-white-label-branding' ), $label );

				/* translators: The post-type name. */
				$labels->add_new_item = sprintf( esc_html__( 'Add %s', 'fusion-white-label-branding' ), $label );

				/* translators: The post-type name. */
				$labels->edit_item = sprintf( esc_html__( 'Edit %s', 'fusion-white-label-branding' ), $label );
				$labels->new_item  = $label;

				/* translators: The post-type name. */
				$labels->all_items = sprintf( esc_html__( 'All %s', 'fusion-white-label-branding' ), $label );

				/* translators: The post-type name. */
				$labels->view_item = sprintf( esc_html__( 'View %s', 'fusion-white-label-branding' ), $label );

				/* translators: The post-type name. */
				$labels->search_items = sprintf( esc_html__( 'Search %s', 'fusion-white-label-branding' ), $label );

				/* translators: The post-type name. */
				$labels->not_found = sprintf( esc_html__( 'No %s found', 'fusion-white-label-branding' ), $label );

				/* translators: The post-type name. */
				$labels->not_found_in_trash = sprintf( esc_html__( 'No %s found in Trash', 'fusion-white-label-branding' ), $label );

				if ( isset( $wp_taxonomies[ $post_type . '_category' ] ) ) {

					// Change taxonomy labels for portfolio and faq.
					$taxonomy_labels = $wp_taxonomies[ $post_type . '_category' ]->labels;

					/* translators: The post-type name. */
					$taxonomy_labels->menu_name = sprintf( esc_html__( '%s Categories', 'fusion-white-label-branding' ), $label );

					/* translators: The post-type name. */
					$taxonomy_labels->name = sprintf( esc_html__( '%s Categories', 'fusion-white-label-branding' ), $label );
				}
			}
		}
	}

	/**
	 * Update Fusion Builder Welcome Screen title.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $default_content The default content.
	 * @return string $welcome_screen_title Welcome Screen title.
	 */
	public function fusion_builder_welcome_screen_title( $default_content ) {
		$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();
		$welcome_screen_title   = isset( $fusion_builder_options['welcome_screen_title'] ) && ! empty( $fusion_builder_options['welcome_screen_title'] ) ? $fusion_builder_options['welcome_screen_title'] : $default_content;

		return $welcome_screen_title;
	}

	/**
	 * Update Fusion Builder Welcome Screen about text.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $default_content The default content.
	 * @return string $welcome_screen_about_text Welcome Screen about text.
	 */
	public function fusion_builder_welcome_screen_about_text( $default_content ) {
		$fusion_builder_options    = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();
		$welcome_screen_about_text = isset( $fusion_builder_options['welcome_screen_about_text'] ) && ! empty( $fusion_builder_options['welcome_screen_about_text'] ) ? stripslashes( $fusion_builder_options['welcome_screen_about_text'] ) : $default_content;

		return $welcome_screen_about_text;
	}

	/**
	 * Update Fusion Builder Welcome Screen content.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $default_content The default content.
	 * @return string $welcome_screen_content Welcome Screen content.
	 */
	public function fusion_builder_welcome_screen_content( $default_content ) {
		$fusion_builder_options = isset( $this->settings['fusion_branding']['fusion_builder'] ) ? $this->settings['fusion_branding']['fusion_builder'] : array();
		$welcome_screen_content = isset( $fusion_builder_options['welcome_screen_content'] ) && ! empty( $fusion_builder_options['welcome_screen_content'] ) ? stripslashes( $fusion_builder_options['welcome_screen_content'] ) : $default_content;

		return $welcome_screen_content;
	}

	/**
	 * Update Avada Welcome Screen title.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $default_content The default content.
	 * @return string $welcome_screen_title Welcome Screen title.
	 */
	public function avada_welcome_screen_title( $default_content ) {
		$avada_options        = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$welcome_screen_title = isset( $avada_options['welcome_screen_title'] ) && ! empty( $avada_options['welcome_screen_title'] ) ? $avada_options['welcome_screen_title'] : $default_content;

		return $welcome_screen_title;
	}

	/**
	 * Update Avada Welcome Screen about text.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $default_content The default content.
	 * @return string $welcome_screen_about_text Welcome Screen about text.
	 */
	public function avada_welcome_screen_about_text( $default_content ) {
		$avada_options             = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$welcome_screen_about_text = isset( $avada_options['welcome_screen_about_text'] ) && ! empty( $avada_options['welcome_screen_about_text'] ) ? stripslashes( $avada_options['welcome_screen_about_text'] ) : $default_content;

		return $welcome_screen_about_text;
	}

	/**
	 * Update Avada Welcome Screen content.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $default_content The default content.
	 * @return string $welcome_screen_content Welcome Screen content.
	 */
	public function avada_welcome_screen_content( $default_content ) {
		$avada_options          = isset( $this->settings['fusion_branding']['avada'] ) ? $this->settings['fusion_branding']['avada'] : array();
		$welcome_screen_content = isset( $avada_options['welcome_screen_content'] ) && ! empty( $avada_options['welcome_screen_content'] ) ? stripslashes( $avada_options['welcome_screen_content'] ) : $default_content;

		return $welcome_screen_content;
	}

	/**
	 * Check if admins can view branding changes.
	 *
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function user_can_see_changes() {
		// WordPress admin settings.
		$wp_admin_options = isset( $this->settings['fusion_branding']['wp_admin'] ) ? $this->settings['fusion_branding']['wp_admin'] : array();
		$apply_changes    = isset( $wp_admin_options['apply_changes_for_admin'] ) ? $wp_admin_options['apply_changes_for_admin'] : true;

		$current_user = wp_get_current_user();

		if ( in_array( 'administrator', $current_user->roles, true ) ) {
			return $apply_changes;
		} else {
			return true;
		}
	}

	/**
	 * Filter fusion_fusionredux_args.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $args The arguments array.
	 * @return array      Modified array of arguments.
	 */
	public function fusion_fusionredux_args( $args ) {
		// Check if we're on the avada instance.
		if ( isset( $args['page_slug'] ) && 'avada_options' === $args['page_slug'] ) {
			// Check if we've got a custom menu label defined.
			if ( isset( $this->settings['fusion_branding'] ) && isset( $this->settings['fusion_branding']['avada'] ) && isset( $this->settings['fusion_branding']['avada']['admin_menu_label'] ) && '' !== $this->settings['fusion_branding']['avada']['admin_menu_label'] ) {
				$args['display_name'] = $this->settings['fusion_branding']['avada']['admin_menu_label'];
			}

			// Check if we've got a custom version defined for Avada.
			if ( isset( $this->settings['fusion_branding'] ) && isset( $this->settings['fusion_branding']['avada'] ) && isset( $this->settings['fusion_branding']['avada']['version_number_text'] ) && '' !== $this->settings['fusion_branding']['avada']['version_number_text'] ) {
				$args['display_version'] = $this->settings['fusion_branding']['avada']['version_number_text'];
			}
		}
		return $args;
	}

	/**
	 * Download the settings json.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function export_settings() {

		if ( ! isset( $_GET['action'] ) || 'export_white_label_settings' !== $_GET['action'] ) {
			return;
		}

		// @codingStandardsIgnoreLine WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'] ) ) {
			die();
		}

		$section_id = '';

		if ( isset( $_GET['section_id'] ) ) {
			$section_id = sanitize_key( wp_unslash( $_GET['section_id'] ) );
		}

		$settings         = get_option( 'fusion_branding_settings', array() );
		$section_settings = ( 'all' !== $section_id ) ? $settings['fusion_branding'][ $section_id ] : $settings;

		// Assing the settings to a variable to export.
		$export                = array();
		$export[ $section_id ] = $section_settings;

		header( 'Content-Description: File Transfer' );
		header( 'Content-type: application/txt' );
		header( 'Content-Disposition: attachment; filename="fusion-white-label-branding-settings-' . $section_id . '-' . date( 'd-m-Y' ) . '.json"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		echo json_encode( $export );
		die();
	}

	/**
	 * Import the settings from provided json.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function import_settings() {

		// @codingStandardsIgnoreLine
		if ( ! isset( $_POST['action'] ) || 'import_white_label_settings' !== $_POST['action'] ) {
			return;
		}

		// Check for nonce validation.
		check_ajax_referer( 'branding_settings_import', 'security' );

		if ( isset( $_POST['json_data'] ) ) {
			$json_data = sanitize_textarea_field( wp_unslash( $_POST['json_data'] ) );
		} else {
			return;
		}

		$decoded_settings = json_decode( $json_data, true );
		$section_id       = key( $decoded_settings );
		$section_settings = $decoded_settings[ $section_id ];
		$settings         = get_option( 'fusion_branding_settings', array() );

		switch ( $section_id ) {

			case 'all':
				$settings = $section_settings;
				break;

			case 'wp_admin':
			case 'avada':
			case 'fusion_builder':
			case 'fusion_slider':
			case 'login_screen':
				$settings['fusion_branding'][ $section_id ] = $section_settings;
				break;
		}

		$result = update_option( 'fusion_branding_settings', $settings );

		echo esc_attr( $result );

		die();
	}

	/**
	 * Reset settings.
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function reset_settings() {

		if ( ! isset( $_GET['action'] ) || 'reset-branding-settings' !== $_GET['action'] ) {
			return;
		}

		// @codingStandardsIgnoreLine WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'] ) ) {
			die();
		}

		$section_id = '';

		if ( isset( $_GET['section_id'] ) ) {
			$section_id = sanitize_key( wp_unslash( $_GET['section_id'] ) );
		}

		$settings = get_option( 'fusion_branding_settings', array() );

		switch ( $section_id ) {

			case 'all':
				$settings['fusion_branding'] = array();
				break;

			case 'wp_admin':
			case 'avada':
			case 'fusion_builder':
			case 'login_screen':
			case 'fusion_slider':
				$settings['fusion_branding'][ $section_id ] = array();
				break;
		}

		update_option( 'fusion_branding_settings', $settings );

		// @codingStandardsIgnoreLine
		wp_safe_redirect( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );

		die();
	}
}
new Fusion_White_Label_Branding_Admin();
