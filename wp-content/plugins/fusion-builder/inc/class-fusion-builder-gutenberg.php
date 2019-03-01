<?php
/**
 * Fusion Builder Gutenberg compatibility class.
 *
 * @package Fusion-Builder
 * @since 1.7
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Builder Gutenberg compatibility class.
 *
 * @since 1.7
 */
class Fusion_Builder_Gutenberg {

	/**
	 * Function-name to check for Gutenberg block-editing.
	 *
	 * @access private
	 * @since 1.7.2
	 * @var string
	 */
	private $block_editor_check_function = '';

	/**
	 * Class constructor.
	 *
	 * @since 1.7
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ), 10 );
	}

	/**
	 * Class init.
	 *
	 * @since 1.7
	 * @access public
	 * @return void
	 */
	public function init() {
		global $typenow, $pagenow;

		if ( function_exists( 'use_block_editor_for_post' ) && ! defined( 'GUTENBERG_VERSION' ) ) {
			$this->block_editor_check_function = 'use_block_editor_for_post';
		} elseif ( function_exists( 'gutenberg_can_edit_post' ) && defined( 'GUTENBERG_VERSION' ) ) {
			$this->block_editor_check_function = 'gutenberg_can_edit_post';
		}

		if ( ! function_exists( $this->block_editor_check_function ) ) {
			return;
		}

		$post_type = $typenow;
		if ( 'edit.php' === $pagenow && '' === $typenow ) {
			$post_type = 'post';
		}

		if ( is_admin() && $this->is_fb_enabled( $post_type ) ) {

			// Alter the add new dropdown.
			add_action( 'admin_print_footer_scripts-edit.php', array( $this, 'edit_dropdown' ), 10 );

			// Add gutenberg edit link.
			add_filter( 'page_row_actions', array( $this, 'add_edit_link' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'add_edit_link' ), 10, 2 );
		}

		add_action( 'admin_print_footer_scripts-post-new.php', array( $this, 'adopt_to_builder' ), 10 );
		add_action( 'admin_print_footer_scripts-post.php', array( $this, 'adopt_to_builder' ), 10 );

		// Make sure G only loads with get variable if FB is new default.
		add_filter( $this->block_editor_check_function, array( $this, 'replace_gutenberg' ), 99, 2 );
	}

	/**
	 * Adopts to the chosen builder. Will add FB button to Gutenberg and trigger FB activation.
	 *
	 * @since 1.7
	 * @access public
	 * @return void
	 */
	public function adopt_to_builder() {
		global $post_type, $post;

		if ( $this->is_fb_enabled( $post_type ) && is_object( $post ) ) {
			if ( isset( $_GET['fb-be-editor'] ) ) {
				?>
				<script type="text/javascript">
				jQuery( window ).load( function() {
					var builderToggle = jQuery( '#fusion_toggle_builder' );

					setTimeout( function() {
						if ( ! builderToggle.hasClass( 'fusion_builder_is_active' ) ) {
							builderToggle.trigger( 'click' );
						}
					}, 100 );
				} );
				</script>
				<?php
			} elseif ( isset( $_GET['gutenberg-editor'] ) ) {
				$editor_label = esc_attr__( 'Edit With Fusion Builder', 'fusion-builder' );
				$post_link = add_query_arg( 'fb-be-editor', '', get_edit_post_link( $post->ID, 'raw' ) );
				$button       = '<a href="' . $post_link . '" id="fusion_builder_switch" class="button button-primary button-large">' . $editor_label . '</a>'; // WPCS: XSS ok.
				?>
				<script type="text/javascript">
				jQuery( window ).load( function() {
					var toolbar = jQuery( '.edit-post-header-toolbar' );

					if ( toolbar.length ) {
						toolbar.append( '<?php echo $button; // WPCS: XSS ok. ?>' );
					}
				} );
				</script>
				<?php
			}
		}
	}

	/**
	 * Checks if Gutenberg should be disabled.
	 *
	 * @since 1.7
	 * @access public
	 * @param bool    $use_block_editor Whether the post can be edited or not with Gutenberg.
	 * @param WP_Post $post             The post being checked.
	 * @return bool   Whether post should be edited or not with Gutenberg.
	 */
	public function replace_gutenberg( $use_block_editor, $post ) {
		global $post_type;

		if ( isset( $_GET['gutenberg-editor'] ) || ! $this->is_fb_enabled( $post_type ) ) { // WPCS: CSRF ok.
			return $use_block_editor;
		}
		return false;
	}

	/**
	 * Add edit dropdown to the all posts/pages screens.
	 *
	 * @since 1.7
	 * @access public
	 * @return void
	 */
	public function edit_dropdown() {
		global $typenow;

		$post_type_check = $this->block_editor_check_function . '_type';

		if ( ! $post_type_check( $typenow ) ) {
			return;
		}

		$edit          = 'post' !== $typenow ? 'post-new.php?post_type=' . $typenow : 'post-new.php';
		$fb_url        = add_query_arg( 'fb-be-editor', '', $edit );
		$gutenberg_url = add_query_arg( 'gutenberg-editor', '', $edit );
		$classic_url   = add_query_arg( 'classic-editor', '', $edit );

		$page_title_action_template = '<span id="fusion-split-page-title-action" class="fusion-split-page-title-action"><a href="' . $edit . '">' . esc_html__( 'Add New', 'fusion-builder' ) . '</a><span class="expander" tabindex="0" role="button" aria-haspopup="true" aria-label="' . esc_html__( 'Toggle editor selection menu', 'fusion-builder' ) . '"></span><span class="dropdown"><a href="' . $fb_url . '">' . esc_html__( 'Fusion Builder', 'fusion-builder' ) . '</a><a href="' . $gutenberg_url . '">' . esc_html__( 'Gutenberg Editor', 'fusion-builder' ) . '</a><a href="' . $classic_url . '">' . esc_html__( 'Classic Editor', 'fusion-builder' ) . '</a></span></span>';

		?>
		<script type="text/javascript">
		jQuery( document ).ready( function() {
			var pageTitleAction = ( jQuery( '.split-page-title-action' ).length ) ? jQuery( '.split-page-title-action' ) : jQuery( '.page-title-action' );

			pageTitleAction.before( '<?php echo $page_title_action_template; // WPCS: XSS ok. ?>' );
			pageTitleAction.remove();
			jQuery( '.fusion-split-page-title-action' ).find( '.expander' ).on( 'click', function( e ) {
				jQuery( this ).siblings( '.dropdown' ).toggleClass( 'visible' );
			} );
		} );
		</script>
		<style>
			.fusion-split-page-title-action {
				display: inline-block;
				position: relative;
			}
			.fusion-split-page-title-action a,
			.fusion-split-page-title-action a:active,
			.fusion-split-page-title-action .expander {
				padding: 6px 10px;
				position: relative;
				top: -3px;
				text-decoration: none;
				border: 1px solid #ccc;
				border-radius: 2px;
				background: #f7f7f7;
				text-shadow: none;
				font-weight: 600;
				font-size: 13px;
				line-height: normal;
				color: #0073aa;
				cursor: pointer;
				outline: 0;
			}
			.fusion-split-page-title-action > a {
				vertical-align: middle;
				width: 80px;
				display: inline-block;
			}
			.fusion-split-page-title-action .expander {
				outline: none;
				width: 31px;
				display: inline-block;
				padding: 0;
				height: 29px;
				vertical-align: middle;
			}
			.fusion-split-page-title-action .expander:after {
				content: "\f140";
				font: 400 20px/.5 dashicons;
				speak: none;
				top: 50%;
				left: 50%;
				position: absolute;
				transform: translate(-50%, -50%);
				text-decoration: none !important;
			}
			.fusion-split-page-title-action .dropdown {
				display: none;
				width: 135px;
			}
			.fusion-split-page-title-action .dropdown.visible {
				display: block;
				position: absolute;
				top: 28px;
				z-index: 1;
			}
			.fusion-split-page-title-action .dropdown.visible a {
				display: block;
				top: 0;
				margin: -1px 0;
				padding-right: 9px;
			}
			.fusion-split-page-title-action a:hover,
			.fusion-split-page-title-action .expander:hover {
				border-color: #008EC2;
				background: #00a0d2;
				color: #fff;
			}
		</style>
		<?php
	}

	/**
	 * Adds specigic Gutenberg edit link to the posts hover menu.
	 *
	 * @since 1.7
	 * @access public
	 * @param  array   $actions Post actions.
	 * @param  WP_Post $post    Edited post.
	 *
	 * @return array          Updated post actions.
	 */
	public function add_edit_link( $actions, $post ) {
		if ( ! function_exists( $this->block_editor_check_function ) ) {
			return $actions;
		}

		$edit_url = get_edit_post_link( $post->ID, 'raw' );
		$gutenberg_url = add_query_arg( 'gutenberg-editor', '', $edit_url );
		$classic_url = add_query_arg( 'classic-editor', '', $edit_url );

		// Build the classic edit action. See also: WP_Posts_List_Table::handle_row_actions().
		$title       = _draft_or_post_title( $post->ID );
		$edit_action = array(
			'gutenberg' => sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $gutenberg_url ),
				esc_attr(
					sprintf(
						/* translators: %s: post title */
						__( 'Edit &#8220;%s&#8221; in the Gutenberg editor', 'fusion-builder' ),
						$title
					)
				),
				__( 'Gutenberg Editor', 'fusion-builder' )
			),
			'classic' => sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $classic_url ),
				esc_attr(
					sprintf(
						/* translators: %s: post title */
						__( 'Edit &#8220;%s&#8221; in the Classic editor', 'fusion-builder' ),
						$title
					)
				),
				__( 'Classic Editor', 'fusion-builder' )
			),
		);

		// Insert the Gutenberg Edit action after the Edit action.
		$edit_offset = array_search( 'edit', array_keys( $actions ), true );
		$actions     = array_merge(
			array_slice( $actions, 0, $edit_offset + 1 ),
			$edit_action,
			array_slice( $actions, $edit_offset + 1 )
		);

		return $actions;
	}

	/**
	 * Check if FB is activated for the post type.
	 *
	 * @since 1.7
	 * @access public
	 * @param string $post_type Post type to check.
	 * @return bool
	 */
	public function is_fb_enabled( $post_type ) {

		$options = get_option( 'fusion_builder_settings', array() );

		if ( ! empty( $options ) && isset( $options['post_types'] ) ) {
			// If there are options saved, used them.
			$post_types = ( ' ' === $options['post_types'] ) ? array() : $options['post_types'];
			// Add fusion_element to allowed post types ( bc ).
			$post_types[] = 'fusion_element';
			$activated    = apply_filters( 'fusion_builder_allowed_post_types', $post_types );
		} else {
			// Otherwise use defaults.
			$activated = FusionBuilder::default_post_types();
		}

		if ( $post_type ) {
			return in_array( $post_type, $activated, true );
		}
		return false;
	}
}
