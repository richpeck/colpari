<?php
/**
 * Fusion Library.
 *
 * @package Fusion-Builder
 * @subpackage Options
 * @since 1.6
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class Fusion_Builder_Library_Table extends WP_List_Table {

	/**
	 * Data columns.
	 *
	 * @since 1.0
	 * @var array
	 */
	public $columns = array();

	/**
	 * Class constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => esc_attr__( 'Element', 'fusion-builder' ), // Singular name of the listed records.
				'plural'   => esc_attr__( 'Elements', 'fusion-builder' ), // Plural name of the listed records.
				'ajax'     => false, // This table doesn't support ajax.
				'class'    => 'fusion-library-table',
			)
		);

		$this->columns = $this->get_columns();
	}

	/**
	 * Set the custom classes for table.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'fusion-library-table' );
	}

	/**
	 * Prepare the items for the table to process.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function prepare_items() {
		$columns      = $this->columns;
		$per_page     = 15;
		$current_page = $this->get_pagenum();
		$data         = $this->table_data( $per_page, $current_page );
		$hidden       = $this->get_hidden_columns();
		$sortable     = $this->get_sortable_columns();

		$total_items = count( $this->table_data() );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => esc_attr__( 'Title', 'fusion-builder' ),
			'type'   => esc_attr__( 'Type', 'fusion-builder' ),
			'global' => esc_attr__( 'Global', 'fusion-builder' ),
			'date'   => esc_attr__( 'Date', 'fusion-builder' ),
		);

		return apply_filters( 'manage_fusion_element_posts_columns', $columns );
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'title' => array( 'title', true ),
			'date'  => array( 'date', true ),
		);
	}

	/**
	 * Get the table data.
	 *
	 * @since 1.0
	 * @access public
	 * @param  number $per_page     Posts per page.
	 * @param  number $current_page - Current page number.
	 * @return array
	 */
	private function table_data( $per_page = -1, $current_page = 0 ) {
		$data            = array();
		$library_query   = array();
		$status          = array( 'publish' );

		// Make sure current-page and per-page are integers.
		$per_page     = (int) $per_page;
		$current_page = (int) $current_page;

		if ( isset( $_GET['status'] ) ) {
			$status = sanitize_text_field( wp_unslash( $_GET['status'] ) );
		}

		$args = array(
			'post_type'      => array( 'fusion_template', 'fusion_element' ),
			'posts_per_page' => $per_page,
			'post_status'    => $status,
			'offset'         => ( $current_page - 1 ) * $per_page,
		);

		// Add sorting.
		if ( isset( $_GET['orderby'] ) ) {
			$args['orderby'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			$args['order']   = ( isset( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC';
		}

		// Get by type.
		if ( isset( $_GET['type'] ) ) {
			$args['post_type'] = 'fusion_element';

			if ( 'global' === $_GET['type'] ) {
				$args['meta_key']   = '_fusion_is_global';
				$args['meta_value'] = 'yes';
			} elseif ( 'template' === $_GET['type'] ) {
				$args['post_type'] = 'fusion_template';
			} else {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'element_category',
						'field'    => 'name',
						'terms'    => sanitize_text_field( wp_unslash( $_GET['type'] ) ),
					),
				);
			}
		}

		$library_query = new WP_Query( $args );

		// Check if there are items available.
		if ( $library_query->have_posts() ) {
			// The loop.
			while ( $library_query->have_posts() ) :
				$library_query->the_post();
				$element_post_id = get_the_ID();

				$terms         = get_the_terms( $element_post_id, 'element_category' );
				$display_terms = '';
				$global        = '';

				if ( $terms ) {
					foreach ( $terms as $term ) {
						$term_name = $term->name;

						if ( 'sections' === $term_name ) {
							$term_name = esc_html__( 'Container', 'fusion-builder' );
						} elseif ( 'columns' === $term_name ) {
							$term_name = esc_html__( 'Column', 'fusion-builder' );
						} elseif ( 'elements' === $term_name ) {
							$term_name = esc_html__( 'Element', 'fusion-builder' );
						}
						$display_terms .= '<span class="fusion-library-element-type fusion-library-element-' . esc_attr( $term->name ) . '"><a href="' . esc_url_raw( admin_url( 'admin.php?page=fusion-builder-library&type=' ) . $term->name ) . '">' . esc_html( $term_name ) . '</a></span>';
					}
				} else {
					$display_terms .= '<span class="fusion-library-element-type fusion-library-element-template"><a href="' . esc_url_raw( admin_url( 'admin.php?page=fusion-builder-library&type=template' ) ) . '">' . esc_html__( 'Template', 'fusion-builder' ) . '</a></span>';
				}

				$global = '';
				if ( 'yes' === get_post_meta( $element_post_id, '_fusion_is_global', true ) ) {
					$global = '<a href="' . esc_url_raw( admin_url( 'admin.php?page=fusion-builder-library&type=global' ) ) . '"><span class="fusion-library-element-global"></span></a>';
				}

				$element_post = array(
					'title'  => get_the_title(),
					'id'     => $element_post_id,
					'date'   => get_the_date( 'm/d/Y' ),
					'time'   => get_the_date( 'm/d/Y g:i:s A' ),
					'status' => get_post_status(),
					'global' => $global,
					'type'   => $display_terms,
				);

				$data[] = $element_post;
			endwhile;

			// Restore original Post Data.
			wp_reset_postdata();
		}
		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 1.0
	 * @access public
	 * @param  array  $item        Data.
	 * @param  string $column_id - Current column id.
	 * @return string
	 */
	public function column_default( $item, $column_id ) {
		do_action( 'manage_fusion_element_custom_column', $column_id, $item );

		if ( isset( $item[ $column_id ] ) ) {
			return $item[ $column_id ];
		}
		return '';
	}

	/**
	 * Set row actions for title column.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_title( $item ) {
		$wpnonce = wp_create_nonce( 'fusion-library' );

		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) {
			$actions['restore'] = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Restore', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_restore_element', esc_attr( $item['id'] ) );
			$actions['delete']  = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Delete Permanently', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_delete_element', esc_attr( $item['id'] ) );
		} else {
			$actions['edit']   = sprintf( '<a href="post.php?post=%s&action=%s">' . esc_html__( 'Edit', 'fusion-builder' ) . '</a>', esc_attr( $item['id'] ), 'edit' );
			$actions['trash']  = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Trash', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_trash_element', esc_attr( $item['id'] ) );
		}

		$status = '';
		if ( 'draft' === $item['status'] ) {
			$status = ' &mdash; <span class="post-state">' . ucwords( $item['status'] ) . '</span>';
		}

		$title = sprintf( '<strong><a href="post.php?post=%s&action=%s">' . esc_html( $item['title'] ) . '</a>' . $status . '</strong>', esc_attr( $item['id'] ), 'edit' );

		return $title . ' ' . $this->row_actions( $actions );
	}

	/**
	 * Set date column.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_date( $item ) {
		$date_html = __( 'Published', 'fusion-builder' );
		if ( isset( $_GET['status'] ) && ( 'draft' === $_GET['status'] || 'trash' === $_GET['status'] ) ) {
			$date_html = __( 'Last Modified', 'fusion-builder' );
		}
		$date_html .= '<br/>';
		$date_html .= '<abbr title="' . $item['time'] . '">' . $item['date'] . '</abbr>';
		return $date_html;
	}

	/**
	 * Set bulk actions dropdown.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_bulk_actions() {
		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) {
			$actions = array(
				'fusion_restore_element' => esc_html__( 'Restore', 'fusion-builder' ),
				'fusion_delete_element'  => esc_html__( 'Delete Permanently', 'fusion-builder' ),
			);
		} else {
			$actions = array(
				'fusion_trash_element' => esc_html__( 'Move to Trash', 'fusion-builder' ),
			);
		}

		return $actions;
	}

	/**
	 * Set checkbox for bulk selection and actions.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return "<input type='checkbox' name='post[]' value='{$item['id']}' />";
	}

	/**
	 * Display custom text if library is empty.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function no_items() {
		esc_attr_e( 'Fusion library is empty.', 'fusion-builder' );
	}

	/**
	 * Display status count with link.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function get_status_links() {
		$post_status     = array();
		$status_lists    = array();
		$count_posts     = array();
		$count_elements  = wp_count_posts( 'fusion_element' );
		$count_templates = wp_count_posts( 'fusion_template' );
		$count_elements  = (array) $count_elements;
		$count_templates = (array) $count_templates;
		$element_types   = array( 'sections', 'columns', 'elements' );

		$count_posts['publish'] = $count_elements['publish'] + $count_templates['publish'];
		$count_posts['trash'] = $count_elements['trash'] + $count_templates['trash'];

		if ( isset( $count_posts['publish'] ) && $count_posts['publish'] ) {
			$post_status['all'] = $count_posts['publish'];
		}

		$globals_query = new WP_Query(
			array(
				'post_type'      => 'fusion_element',
				'posts_per_page' => '-1',
				'post_status'    => 'publish',
				'meta_key'       => '_fusion_is_global',
				'meta_value'     => 'yes',
			)
		);

		if ( isset( $count_posts['trash'] ) && $count_posts['trash'] ) {
			$post_status['trash'] = $count_posts['trash'];
		}

		if ( isset( $count_templates['publish'] ) && $count_templates['publish'] ) {
			$post_status['template'] = $count_templates['publish'];
		}

		foreach ( $element_types as $type ) {
			$element = get_term_by( 'name', $type, 'element_category' );
			if ( $element ) {
				$post_status[ $type ] = $element->count;
			}
		}

		if ( $globals_query->have_posts() ) {
			$post_status['global'] = $globals_query->post_count;
		}

		$status_html = '<ul class="subsubsub">';

		foreach ( $post_status as $status => $count ) {
			$current_type = 'all';

			if ( isset( $_GET['type'] ) ) {
				$current_type = sanitize_text_field( wp_unslash( $_GET['type'] ) );
			}

			if ( isset( $_GET['status'] ) ) {
				$current_type = sanitize_text_field( wp_unslash( $_GET['status'] ) );
			}

			$current = ( $status == $current_type ) ? ' class="current" ' : '';

			$status_attr = ( 'all' !== $status ) ? '&type=' . $status : '';
			if ( 'trash' === $status ) {
				$status_attr = '&status=' . $status;
			}

			$status_title = $status;
			if ( 'publish' === $status ) {
				$status_title = esc_html__( 'Published', 'fusion-builder' );
			} else if ( 'sections' === $status ) {
				$status_title = esc_html__( 'Containers', 'fusion-builder' );
			}

			$status_list  = '<li class="' . $status . '">';
			$status_list .= '<a href="' . admin_url( 'admin.php?page=fusion-builder-library' ) . $status_attr . '"' . $current . '>' . ucwords( $status_title );
			$status_list .= ' (' . $count . ')</a>';
			$status_list .= '</li>';

			$status_lists[] = $status_list;
		}

		$status_html .= implode( ' | ', $status_lists );
		$status_html .= '</ul>';

		echo $status_html; // WPCS: XSS ok.
	}
}
