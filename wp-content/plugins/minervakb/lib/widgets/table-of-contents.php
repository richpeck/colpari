<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Table_Of_Contents_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'mkb_table_of_contents_widget',
			'description' => __('Displays table of contents for KB article', 'minerva-kb' ),
		);
		parent::__construct( 'kb_table_of_contents_widget', __('KB Table of contents', 'minerva-kb' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $minerva_kb;

		if (!$minerva_kb->info->is_single() || !MKB_Options::option('toc_in_content_disable') ||
		    (MKB_Options::option('toc_sidebar_desktop_only') && !$minerva_kb->info->is_desktop()) ||
		    (MKB_Options::option('restrict_on') && !MKB_Options::option('restrict_show_article_toc') && !$minerva_kb->restrict->check_access() ) ) {
			return;
		}

		echo $args['before_widget'];

		MKB_TemplateHelper::table_of_contents();

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$currently = MKB_Options::option('toc_in_content_disable') ? 'currently <strong style="color:green">disabled</strong>' : 'currently <strong style="color:red">enabled</strong>';
		?>
		<p><?php _e("Please note, that this widget only works in articles and only when table of contents in article body is disabled in settings ($currently), otherwise it will not be displayed. Colors can be configured in <strong>MinervaKB Settings</strong>", 'minerva-kb' ) ?></p>
	<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		return $instance;
	}
}