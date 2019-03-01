<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_FAQShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'faq';
	protected $name = 'FAQ';
	protected $description = 'FAQ section with live filter';
	protected $icon = 'fa fa-question';

	protected $args_map = array(
		'home_faq_title' => 'title',
		'home_faq_title_size' => 'title_size',
		'home_faq_title_color' => 'title_color',
		'home_faq_margin_top' => 'margin_top',
		'home_faq_margin_bottom' => 'margin_bottom',
		'home_faq_limit_width_switch' => 'limit_width',
		'home_faq_width_limit' => 'width',
		'home_faq_controls_margin_top' => 'controls_margin_top',
		'home_faq_controls_margin_bottom' => 'controls_margin_bottom',
		'home_show_faq_filter' => 'show_filter',
		'home_show_faq_toggle_all' => 'show_toggle_all',
		'home_faq_categories' => 'categories',
		'home_show_faq_categories' => 'show_categories',
		'home_show_faq_category_count' => 'show_count',
	);

	public function render($atts, $content = '') {
		// shortcode defaults
		$args = wp_parse_args($atts, $this->get_defaults());

		MKB_TemplateHelper::render_faq($this->map_params($this->args_map, $args));
	}

	/**
	 * Returns all shortcode options
	 * @return array
	 */
	public static function get_options() {
		return array(
			array(
				'id' => 'home_faq_title_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ title', 'minerva-kb' ),
				'description' => __( 'Configure FAQ title on home page', 'minerva-kb' )
			),
			array(
				'id' => 'title',
				'type' => 'input',
				'label' => __( 'FAQ title', 'minerva-kb' ),
				'default' => __( 'Frequently Asked Questions', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'title_size',
				'type' => 'css_size',
				'label' => __( 'FAQ title font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'title_color',
				'type' => 'color',
				'label' => __( 'FAQ title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'layout_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ layout', 'minerva-kb' ),
				'description' => __( 'Configure FAQ layout on home page', 'minerva-kb' )
			),
			array(
				'id' => 'margin_top',
				'type' => 'css_size',
				'label' => __( 'FAQ section top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between FAQ and previous section', 'minerva-kb' ),
			),
			array(
				'id' => 'margin_bottom',
				'type' => 'css_size',
				'label' => __( 'FAQ section bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between FAQ and next sections', 'minerva-kb' ),
			),

			array(
				'id' => 'limit_width',
				'type' => 'checkbox',
				'label' => __( 'Limit FAQ container width?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'width',
				'type' => 'css_size',
				'label' => __( 'FAQ container maximum width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "60"),
				'description' => __( 'You can make FAQ section more narrow, than your content width', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'limit_width',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'controls_margin_top',
				'type' => 'css_size',
				'label' => __( 'FAQ controls top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'Distance between FAQ controls and title', 'minerva-kb' ),
			),
			array(
				'id' => 'controls_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'FAQ controls bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'Distance between FAQ controls and questions', 'minerva-kb' ),
			),
			array(
				'id' => 'controls_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ controls', 'minerva-kb' ),
				'description' => __( 'Configure FAQ controls on home page', 'minerva-kb' )
			),
			array(
				'id' => 'show_filter',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ live filter?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_toggle_all',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ toggle all button?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'categories_section_title',
				'type' => 'title',
				'label' => __( 'FAQ categories settings', 'minerva-kb' ),
				'description' => __( 'Configure FAQ categories', 'minerva-kb' )
			),
			array(
				'id' => 'categories',
				'type' => 'term_select',
				'label' => __( 'Select FAQ categories to display on home page', 'minerva-kb' ),
				'default' => '',
				'tax' => 'mkb_faq_category',
				'description' => __( 'You can leave it empty to display all categories.', 'minerva-kb' ),
				'admin_label' => true
			),
			array(
				'id' => 'show_categories',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ categories?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_count',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ category question count?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_categories',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'styles_note_title',
				'type' => 'title',
				'label' => __( 'NOTE: You can configure FAQ styles in FAQ (global)', 'minerva-kb' )
			)
		);
	}
}