<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_TopicShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'topic';
	protected $name = 'Topic';
	protected $description = 'Single KB topic';
	protected $icon = 'fa fa-file-text-o';

	public function render($atts, $content = '') {
		if (!isset($atts["id"])) {
			return '';
		}

		MKB_TemplateHelper::render_topic(wp_parse_args($atts, $this->get_defaults()));
	}

	/**
	 * Returns all shortcode options
	 * @return array
	 */
	public static function get_options() {
		return array(
			array(
				'id' => 'id',
				'type' => 'select',
				'label' => __( 'Select topic', 'minerva-kb' ),
				'options' => self::get_topics(),
				'default' => '',
				'admin_label' => true
			),
			array(
				'id' => 'view',
				'type' => 'image_select',
				'label' => __( 'Child topics view (if any)', 'minerva-kb' ),
				'options' => array(
					'list' => array(
						'label' => __( 'List view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'list-view.png'
					),
					'box' => array(
						'label' => __( 'Box view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'box-view.png'
					)
				),
				'default' => 'box'
			),
			array(
				'id' => 'columns',
				'type' => 'image_select',
				'label' => __( 'Child topics layout (if any)', 'minerva-kb' ),
				'options' => array(
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					),
					'4col' => array(
						'label' => __( '4 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-4.png'
					),
				),
				'default' => '3col'
			),
			array(
				'id' => 'limit',
				'type' => 'input',
				'label' => __( 'Number of articles to display', 'minerva-kb' ),
				'default' => 5,
				'description' => __( 'Use -1 to display all', 'minerva-kb' ),
				'admin_label' => true
			),
		);
	}

	private static function get_topics() {
		$options = array('' => __( 'Please, select topic', 'minerva-kb' ));

		$topics = get_terms( MKB_Options::option('article_cpt_category'), array(
			'hide_empty' => false,
		) );

		if (isset($topics) && !is_wp_error($topics) && !empty($topics)) {
			foreach($topics as $item):
				$options[$item->term_id] = $item->name;
			endforeach;
		}

		return $options;
	}
}