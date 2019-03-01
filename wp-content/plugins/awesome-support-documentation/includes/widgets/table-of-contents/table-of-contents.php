<?php
// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class WPAS_Documentation_TOC extends WP_Widget {

    protected $widget_slug = 'wpas-toc';

	public function __construct() {

		parent::__construct(
			$this->get_widget_slug(),
			__( 'AS Documentation: TOC', $this->get_widget_slug() ),
			array(
				'classname'   => 'WPAS_Documentation_TOC',
				'description' => __( 'Displays the table of contents for the current documentation.', 'wpas-documentation' )
			)
		);

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

	}

    /**
     * Return the widget slug.
     *
     * @since     0.1.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		global $post;

		/* Do not display the widget container if the current doc is one page */
		if ( wpas_doc_is_one_page( $post ) ) {
			return false;
		}
		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];

		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		ob_start();
		include( WPAS_DOC_PATH . 'includes/widgets/table-of-contents/views/widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	}
	
	
	public function flush_widget_cache() {
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// TODO: Here is where you update your widget's old values with the new, incoming values

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// TODO: Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		// TODO: Store the values of the widget in their own variable

		// Display the admin form
		include( WPAS_DOC_PATH . 'includes/widgets/table-of-contents/views/admin.php' );

	}

}
