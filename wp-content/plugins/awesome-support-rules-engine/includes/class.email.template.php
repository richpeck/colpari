<?php
namespace AsRulesEngine;
/**
 * Class to manage Email template setting.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class: RE_Email_Template handle rules engine email functionality.
 */
if ( ! class_exists( 'RE_Email_Template' ) ) {

	class RE_Email_Template {

		/**
		 * RE_Email_Template class constructor.
		 */
		public function __construct() {
			$this->init();
		}

		/**
		 * Function get call on class initialization.
		 */
		private function init() {
			$this->register_email_cpt();
			$this->hooks();
		}

		/**
		 * Call required wp hooks on class initialization.
		 */
		public function hooks() {

			add_filter( 'enter_title_here', array( $this, 'add_subject' ) );
		}

		/**
		 * Register new email CPT
		 * This will be for email templating
		*/
		private function register_email_cpt() {
			$labels = array(
				'name'                => __( 'Email Template', 'as-rules-engine' ),
				'singular_name'       => __( 'Email Template', 'as-rules-engine' ),
				'add_new'             => _x( 'Add New Template', 'as-rules-engine', 'as-rules-engine' ),
				'add_new_item'        => __( 'Add New Template', 'as-rules-engine' ),
				'edit_item'           => __( 'Edit Template', 'as-rules-engine' ),
				'new_item'            => __( 'New Template', 'as-rules-engine' ),
				'view_item'           => __( 'View Template', 'as-rules-engine' ),
				'search_items'        => __( 'Search Email Templates', 'as-rules-engine' ),
				'not_found'           => __( 'No Email Templates found', 'as-rules-engine' ),
				'not_found_in_trash'  => __( 'No Email Templates found in Trash', 'as-rules-engine' ),
				'menu_name'           => __( 'Email Template', 'as-rules-engine' ),
			);
			// Post type capabilities.
			$cap = apply_filters( 'wpas_ticket_type_cap', array(
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
			) );
			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => array( '' ),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=' . AS_RE_RULESET_CPT,
				'show_in_admin_bar'   => false,
				'menu_position'       => null,
				'menu_icon'           => 'dashicons-email-alt',
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => false,
				'capability_type'     => 'view_ticket',
				'capabilities'        => $cap,
				'supports'            => array(
					'title',
					'editor',
				),
			);
			register_post_type( AS_RE_EMAIL_CPT, $args );
		}

		/**
		 * Add Email title field array.
		 * @param string $title Email Subject field Title.
		 */
		public function add_subject( $title ) {
			$screen = get_current_screen();
			if ( 'asre_email' == $screen->post_type ) {
				$title = __( 'Enter subject', 'as-rules-engine' );
			}
			return $title;
		}

	}

}


