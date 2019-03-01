<?php
/**
 * Private Note
 *
 * @package   Awesome Support Private Notes
 * @author    Awesome Support <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016 Awesome Support
 */

class WPAS_Private_Note {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct() {

		add_action( 'after_setup_theme',      array( $this, 'post_type' ),          10, 0 );
		add_filter( 'wpas_replies_post_type', array( $this, 'add_note_post_type' ), 10, 1 );
		add_filter( 'wpas_addons_licenses',   array( $this, 'addon_license' ),      10, 1 );
		add_action( 'wpas_backend_replies_inside_row_after', array( $this, 'note_row_content' ), 10, 1 );

		// Load the plugin translation.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 15 );

		if ( is_admin() ) {
			if ( isset( $_GET['post'] ) && 'ticket' === get_post_type( intval( $_GET['post'] ) ) ) {
				add_action( 'admin_bar_menu',         array( $this, 'add_new_note_link' ), 9999 );
				add_action( 'admin_init',             array( $this, 'save_note' ),         10, 0 );
				add_action( 'in_admin_footer',        array( $this, 'modal_box' ),         10, 0 );
				add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_styles' ),    10, 0 );
				add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_thickbox' ),  10, 0 );
				add_action( 'admin_footer',           array( $this, 'add_script' ),        99, 0 );
			}
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
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

		if ( !class_exists( 'Awesome_Support' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(
				sprintf( __( 'You need Awesome Support to activate this addon. Please <a href="%s" target="_blank">install Awesome Support</a> before continuing.', 'as-private-notes' ), esc_url( 'http://getawesomesupport.com' ) )
			);
		}

	}

	/**
	 * Load addon styles.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wpaspr-admin-style', WPASPR_URL . 'assets/css/admin.css', array( 'thickbox' ), WPASPR_VERSION, 'all' );
	}

	/**
	 * Load the ThickBox JS.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function enqueue_thickbox() {
		wp_enqueue_script( 'thickbox' );
	}

	/**
	 * Load addon script.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function add_script() {
		?><script type="text/javascript">jQuery('#wp-admin-bar-wpas-private-note a').addClass('thickbox');</script><?php
	}

	/**
	 * Add license option.
	 *
	 * @since  0.1.0
	 * @param  array $licenses List of addons licenses
	 * @return array           Updated list of licenses
	 */
	public function addon_license( $licenses ) {

		$licenses[] = array(
			'name'      => __( 'Private Notes', 'as-private-notes' ),
			'id'        => 'license_private_notes',
			'type'      => 'edd-license',
			'default'   => '',
			'server'    => esc_url( 'http://getawesomesupport.com' ),
			'item_name' => 'Private Notes',
			'item_id'   => 59,
			'file'      => WPASPR_PATH . 'private-notes.php'
		);

		return $licenses;
	}

	public function post_type() {
		
	$labels = apply_filters( 'wpas_ticket_note_type_labels', array(
			'name'               => _x( 'Ticket Notes', 'post type general name', 'as-private-notes' ),
			'singular_name'      => _x( 'Ticket Note', 'post type singular name', 'as-private-notes' ),
			'menu_name'          => _x( 'Private Notes', 'admin menu', 'as-private-notes' ),
			'name_admin_bar'     => _x( 'Private Notes', 'add new on admin bar', 'as-private-notes' ),
			'add_new'            => _x( 'Add New', 'private notes', 'as-private-notes' ),
			'add_new_item'       => __( 'Add New Private Note', 'as-private-notes' ),
			'new_item'           => __( 'New Private Note', 'as-private-notes' ),
			'edit_item'          => __( 'Edit Private Note', 'as-private-notes' ),
			'view_item'          => __( 'View Private Note', 'as-private-notes' ),
			'all_items'          => __( 'All Private Notes', 'as-private-notes' ),
			'search_items'       => __( 'Search Private Notes', 'as-private-notes' ),
			'parent_item_colon'  => __( 'Parent Note:', 'as-private-notes' ),
			'not_found'          => __( 'No private notes found.', 'as-private-notes' ),
			'not_found_in_trash' => __( 'No private notes found in Trash.', 'as-private-notes' ),
	) );		

		/* Post type capabilities */
		$cap = array(
			'read'					 => 'view_ticket',
			'read_post'				 => 'view_ticket',
			'read_private_posts' 	 => 'view_private_ticket',
			'edit_post'				 => 'edit_ticket',
			'edit_posts'			 => 'edit_ticket',
			'edit_others_posts' 	 => 'edit_other_ticket',
			'edit_private_posts' 	 => 'edit_private_ticket',
			'edit_published_posts' 	 => 'edit_ticket',
			'publish_posts'			 => 'create_ticket',
			'delete_post'			 => 'delete_ticket',
			'delete_posts'			 => 'delete_ticket',
			'delete_private_posts' 	 => 'delete_private_ticket',
			'delete_published_posts' => 'delete_ticket',
			'delete_others_posts' 	 => 'delete_other_ticket'
		);

		/* Post type arguments */
		$args = array(
			'labels'              => $labels,		
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'query_var'           => true,
			'capability_type'     => 'edit_ticket',
			'capabilities'        => $cap,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
		);

		register_post_type( 'ticket_note', $args );

	}

	/**
	 * Add new note link.
	 *
	 * Adds a new link in admin bar to let agents
	 * create a new private note for the ticket
	 * currently being displayed.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function add_new_note_link() { 

		global $wp_admin_bar, $current_user, $post, $pagenow;

		if ( current_user_can( 'edit_ticket' ) && 'post.php' === $pagenow && 'ticket' === $post->post_type ) {

			$wp_admin_bar->add_menu( array(
				'id' 	=> 'wpas-private-note',
				'title' => __( 'Add Note', 'as-private-notes' ),
				'href' 	=> '#TB_inline?width=630&amp;height=380&amp;inlineId=wpas-note-modal',
				'meta'  => array( 'title' => __( 'Add a private note to this ticket', 'as-private-notes' ) )
			) );

		}

	}

	/**
	 * Private note colorbox.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function modal_box() {

		global $post; ?>

		<div id="wpas-note-modal" style="display:none">

			<div class="wpas-modal-inner">

				<h2><?php _e( 'Add a private note to this ticket', 'as-private-notes' ); ?></h2>
				<p><?php _e( 'This note will only be seen by you and other agents. Clients will not see private notes.', 'as-private-notes' ); ?></p>
				
				<form method="post" action="" id="wpas-new-note">
					<textarea name="wpas_note" id="wpas_note" rows="10" required autofocus></textarea>
					<input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
					<?php wp_nonce_field( 'add_note', '_wpas_note_nonce', false, true );

					/* get the roles */
					global $wp_roles;

					/* Prepare the empty users list */
					$users = array();

					/* Parse the roles */
					foreach( $wp_roles->roles as $role => $data ) {

						/* Check if current role can edit tickets */
						if( array_key_exists( 'edit_ticket', $data['capabilities'] ) ) {

							/* Get users with current role */
							$usrs = new WP_User_Query( array( 'role' => $role ) );

							/* Save users in global array */
							$users = array_merge( $users, $usrs->get_results() );
						}
					}
					?>

					<div class="wpas-cf">

						<input type="submit" class="button button-primary wpas-pl" value="<?php _e( 'Add Note', 'as-private-notes' ); ?>">

						<?php if( count( $users ) > 1 ): ?>

							<div class="wpas-pr">

								<label for="wpas-new-agent"><?php _e( 'Transfer ticket to: ', 'as-private-notes' ); ?></label>

								<select name="wpas_agent" id="wpas-new-agent">
									<?php
									foreach( $users as $usr => $data ) {

										if( get_current_user_id() !=  $data->ID ) {
											?><option value="<?php echo $data->ID; ?>"><?php echo $data->data->display_name; ?></option><?php
										}
									}
									?>
								</select>

								<input type="submit" class="button button-secondary" name="add_transfer" value="<?php _e( 'Add Note &amp; Transfer', 'as-private-notes' ); ?>">
							</div>

						<?php endif; ?>

					</div>

				</form>

			</div>
		</div>

	<?php }

	/**
	 * Add the note type.
	 *
	 * Adds the ticket_note post type to the list of post types
	 * to query when getting replies for a ticket.
	 *
	 * @since   0.1.0
	 * @param   array $types Post types to query
	 * @return  void
	 */
	public function add_note_post_type( $types ) {
		array_push( $types, 'ticket_note' );
		return $types;
	}

	/**
	 * Save private notes.
	 *
	 * @since  0.1.0
	 * @return mixed ID of the post on success, WP_Error on failure
	 */
	public function save_note() {

		if ( !isset( $_POST['wpas_note'] ) || empty( $_POST['wpas_note'] ) ) {
			return false;
		}

		if ( !isset( $_POST['_wpas_note_nonce'] ) || !wp_verify_nonce( $_POST['_wpas_note_nonce'], 'add_note' ) ) {
			return false;
		}

		global $current_user;

		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';
		$note    = wp_kses_post( $_POST['wpas_note'] );
		$args    = array(
			'post_content'   => $note,
			'post_type'      => 'ticket_note',
			'post_author'    => $current_user->ID,
			'post_parent'    => $post_id,
			'post_title'     => sprintf( __( 'Note to ticket %s', 'as-private-notes' ), "#$post_id" ),
			'post_status'    => 'publish',
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
		);
		
		$insert = wp_insert_post( $args, true );

		/* Transfer the ticket to another agent. */
		if ( isset( $_POST['wpas_agent'] ) && isset( $_POST['add_transfer'] ) ) {

			$user = get_user_by( 'id', filter_input( INPUT_POST, 'wpas_agent', FILTER_SANITIZE_NUMBER_INT ) );

			if ( false !== $user ) {

				wpas_assign_ticket( $post_id, $user->ID );

				$args = array( 'post_type' => 'ticket' );

				if ( true === boolval( wpas_get_option( 'hide_closed' ) ) ) {
					$args['wpas_status'] = 'open';
				}

				wp_redirect( add_query_arg( $args, admin_url( 'edit.php' ) ) );
				exit;
			}
		}

		return $insert;

	}

	/**
	 * Diaplay the provate note.
	 *
	 * Adds the HTML markup to display the private note
	 * amongst all other post types.
	 *
	 * @since  0.1.0
	 * @param  object $post Post currently being displayed
	 * @return void
	 */
	public static function note_row_content( $post ) {

		if ( 'ticket_note' !== $post->post_type ) {
			return false;
		}

		$user_data = get_userdata( $post->post_author );
		$user_id   = $user_data->data->ID;
		$user_name = $user_data->data->display_name;
		$date      = human_time_diff( get_the_time( 'U', $post->ID ), current_time( 'timestamp' ) );
		?>

		<td class="col1">
			<div class="wpas-ticket-note-heading"></div>
			<?php echo get_avatar( $post->post_author, '64', get_option( 'avatar_default' ) ); ?>
		</td>
		<td class="col2">
			<div class="wpas-ticket-note-heading"><span class="dashicons dashicons-lock"></span> <?php _e( 'This is a private note', 'as-private-notes' ); ?></div>
			<div class="wpas-reply-meta">
				<div class="wpas-reply-user">
					<strong class="wpas-profilename"><?php echo $user_name; ?></strong> <span class="wpas-profilerole">(<?php echo wpas_get_user_nice_role( $user_data->roles[0] ); ?>)</span>
				</div>
				<div class="wpas-reply-time">
					<time class="wpas-timestamp" datetime="<?php echo get_the_date( 'Y-m-d\TH:i:s' ) . wpas_get_offset_html5(); ?>"><span class="wpas-human-date"><?php echo date( get_option( 'date_format' ), strtotime( $post->post_date ) ); ?> | </span><?php printf( __( '%s ago', 'as-private-notes' ), $date ); ?></time>
				</div>
			</div>

			<?php
			/* Filter the content before we display it */
			$content = apply_filters( 'the_content', $post->post_content );

			/* The content displayed to agents */
			echo '<div class="wpas-reply-content" id="wpas-reply-' . $post->ID . '">';

			/**
			 * wpas_backend_reply_content_before hook
			 *
			 * @since  3.0.0
			 */
			do_action( 'wpas_private_note_content_before', $post->ID );

			echo $content;

			/**
			 * wpas_backend_reply_content_after hook
			 *
			 * @since  3.0.0
			 */
			do_action( 'wpas_private_note_content_after', $post->ID );

			echo '</div>';
			?>
		</td>

	<?php }

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
	 * @since   0.1.2
	 * @return boolean True if the language file was loaded, false otherwise
	 */
	public function load_plugin_textdomain() {

		$lang_dir       = WPASPR_ROOT . 'languages/';
		$lang_path      = WPASPR_PATH . 'languages/';
		$locale         = apply_filters( 'plugin_locale', get_locale(), 'as-private-notes' );
		$mofile         = "as-private-notes-$locale.mo";
		$glotpress_file = WP_LANG_DIR . '/plugins/awesome-support-private-notes/' . $mofile;

		// Look for the GlotPress language pack first of all
		if ( file_exists( $glotpress_file ) ) {
			$language = load_textdomain( 'as-private-notes', $glotpress_file );
		} elseif ( file_exists( $lang_path . $mofile ) ) {
			$language = load_textdomain( 'as-private-notes', $lang_path . $mofile );
		} else {
			$language = load_plugin_textdomain( 'as-private-notes', false, $lang_dir );
		}

		return $language;

	}

}