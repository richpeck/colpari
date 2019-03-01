<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MKB_LayoutEditor {

	private $settings_builder = null;

	public function __construct($settings_builder) {
		$this->settings_builder = $settings_builder;
	}

	public function render() {
		global $post;

		$sections = array();

		if ($post) {
			$sections = get_post_meta($post->ID, '_mkb_page_sections', true);

			if (isset($sections) && !empty($sections)) {
				$sections = array_map(function($str) {
					return json_decode($str, true);
				}, $sections);
			}
		}

		?>
		<div class="mkb-layout-editor__sections">
			<?php if ( isset( $sections ) && ! empty( $sections ) ): ?>
				<?php foreach ( $sections as $index => $section ):
					if (empty($section)) {
						continue;
					}

					?>
					<div class="mkb-layout-editor__section fn-layout-editor-section"
					     data-type="<?php echo esc_attr($section["type"]); ?>">
						<div class="fn-section-inner">
							<?php $this->put_section_html( $section["type"], $index, $section["settings"] ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<div class="mkb-layout-editor__actions">
				<span href="#" class="mkb-layout-editor__add-new fn-layout-editor-add mkb-action-button mkb-action-default"
				   title="<?php esc_attr_e('Save Settings', 'minerva-kb'); ?>">
					<i class="fa fa-plus"></i>
					<?php echo __( 'Add section', 'minerva-kb' ); ?>
					<ul class="mkb-layout-editor__add-new-list">
						<li>
							<a href="#" class="mkb-layout-editor__add-new-list-item fn-layout-editor-add-section" data-type="search">
								<?php esc_html_e('Search', 'minerva-kb'); ?>
							</a>
						</li>
						<li>
							<a href="#" class="mkb-layout-editor__add-new-list-item fn-layout-editor-add-section" data-type="topics">
								<?php esc_html_e('Topics', 'minerva-kb'); ?>
							</a>
						</li>
						<li>
							<a href="#" class="mkb-layout-editor__add-new-list-item fn-layout-editor-add-section" data-type="page-content">
								<?php esc_html_e('Page content', 'minerva-kb'); ?>
							</a>
						</li>
						<?php if (!MKB_Options::option('disable_faq')): ?>
						<li>
							<a href="#" class="mkb-layout-editor__add-new-list-item fn-layout-editor-add-section" data-type="faq">
								<?php esc_html_e('FAQ', 'minerva-kb'); ?>
							</a>
						</li>
						<?php endif; ?>
					</ul>
				</span>
			</div>
		</div>
	<?php
	}

	public function put_section_html($type, $index, $values = array()) {
		$types_options = $this->get_section_options();
		$section_config = $types_options[$type];
		$section_settings = isset($section_config["settings"]) ? $section_config["settings"] : array();
		$default_value = array(
			"type" => $type,
			"settings" => array()
		)

		?>
		<div class="mkb-layout-editor__section-handle fn-layout-editor-section-handle"></div>
		<div class="mkb-layout-editor__section-toolbar">
			<a class="mkb-layout-editor__section-settings-open fn-section-settings-toggle mkb-unstyled-link" href="#">
				<i class="fa fa-cogs"></i>
			</a>
			<a class="mkb-layout-editor__section-remove fn-section-remove mkb-unstyled-link" href="#">
				<i class="fa fa-close"></i>
			</a>
		</div>
		<div class="mkb-layout-editor__section-title"><?php echo esc_html($section_config["title"]); ?></div>
		<div class="mkb-section-settings-container fn-settings-block fn-section-settings-container mkb-hidden">
			<input type="hidden" name="mkb_page_section[<?php echo esc_attr($index); ?>]"
			       class="fn-section-settings-store fn-settings-block-store"
			       data-type="<?php echo esc_attr($type); ?>"
			       value="<?php echo esc_attr(json_encode($default_value)); ?>" />
			<?php
			if ( ! empty( $section_settings ) ):
				foreach ( $section_settings as $option ):
					$id_postfix = uniqid( '_' );
					$value = isset($option["default"]) ? $option["default"] : '';

					if ( isset( $values[ $option["id"] ] ) ) {
						$value = $values[ $option["id"] ];
					}

					$this->settings_builder->render_option(
						$option["type"],
						$value,
						wp_parse_args( $option, array(
							'id_postfix' => $id_postfix
						) )
					);
				endforeach;
			else:
				?>
				<div><?php echo esc_html( __( 'This section currently has no options', 'minerva-kb' ) ); ?></div>
			<?php
			endif; ?>
		</div>
		<?php
	}

	public function get_section_html($type, $index) {
		ob_start();
		$this->put_section_html($type, $index);
		$html = ob_get_clean();

		return $html;
	}

	public static function get_section_options() {
		return array(
			/**
			 * Search
			 */
			'search' => array(
				'id' => 'search',
				'title' => __( 'Search', 'minerva-kb' ),
				'settings' => array(
					array(
						'id' => 'search_title',
						'type' => 'input',
						'label' => __( 'Search title', 'minerva-kb' ),
						'default' => __( 'Need some help?', 'minerva-kb' )
					),
					array(
						'id' => 'search_title_size',
						'type' => 'input',
						'label' => __( 'Search title font size', 'minerva-kb' ),
						'default' => __( '3em', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 3em or 20px',
						'dependency' => array(
							'target' => 'search_title',
							'type' => 'NEQ',
							'value' => ''
						)
					),
					array(
						'id' => 'search_theme',
						'type' => 'select',
						'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
						'options' => array(
							'minerva' => __( 'Minerva', 'minerva-kb' ),
							'clean' => __( 'Clean', 'minerva-kb' ),
							'mini' => __( 'Mini', 'minerva-kb' ),
							'bold' => __( 'Bold', 'minerva-kb' ),
							'invisible' => __( 'Invisible', 'minerva-kb' ),
							'thick' => __( 'Thick', 'minerva-kb' ),
							'3d' => __( '3d', 'minerva-kb' ),
						),
						'default' => 'minerva',
						'description' => __( 'Use predefined styles for search bar', 'minerva-kb' )
					),
					array(
						'id' => 'search_min_width',
						'type' => 'input',
						'label' => __( 'Search input minimum width', 'minerva-kb' ),
						'default' => __( '38em', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices'
					),
					array(
						'id' => 'search_container_padding_top',
						'type' => 'input',
						'label' => __( 'Search container top padding', 'minerva-kb' ),
						'default' => __( '3em', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 3em or 50px'
					),
					array(
						'id' => 'search_container_padding_bottom',
						'type' => 'input',
						'label' => __( 'Search container bottom padding', 'minerva-kb' ),
						'default' => __( '3em', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 3em or 50px'
					),
					array(
						'id' => 'search_placeholder',
						'type' => 'input',
						'label' => __( 'Search placeholder', 'minerva-kb' ),
						'default' => __( 'ex.: Installation', 'minerva-kb' )
					),
					array(
						'id' => 'search_topics',
						'type' => 'term_select',
						'label' => __( 'Optional: you can limit search to specific topics', 'minerva-kb' ),
						'default' => '',
						'tax' => MKB_Options::option('article_cpt_category'),
						'description' => __( 'You can leave it empty to search all topics (default).', 'minerva-kb' )
					),
					array(
						'id' => 'disable_autofocus',
						'type' => 'checkbox',
						'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
						'default' => false
					),
					array(
						'id' => 'show_topic_in_results',
						'type' => 'checkbox',
						'label' => __( 'Show topic in results?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'search_results_multiline',
						'type' => 'checkbox',
						'label' => __( 'Allow multiline titles in results?', 'minerva-kb' ),
						'default' => false,
						'description' => __( 'By default, results are fit in one line. You can change this to allow multiline titles', 'minerva-kb' )
					),
					array(
						'id' => 'search_result_topic_label',
						'type' => 'input',
						'label' => __( 'Search result topic label', 'minerva-kb' ),
						'default' => __( 'Topic', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'show_topic_in_results',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'show_search_tip',
						'type' => 'checkbox',
						'label' => __( 'Show search tip?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'search_tip',
						'type' => 'input',
						'label' => __( 'Search tip (under the input)', 'minerva-kb' ),
						'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'show_search_tip',
							'type' => 'EQ',
							'value' => true
						)
					),

					// COLORS
					array(
						'id' => 'home_search_colors_title',
						'type' => 'title',
						'label' => __( 'Search colors and background', 'minerva-kb' ),
						'description' => __( 'Configure search style', 'minerva-kb' )
					),
					array(
						'id' => 'search_title_color',
						'type' => 'color',
						'label' => __( 'Search title color', 'minerva-kb' ),
						'default' => '#333333',
						'dependency' => array(
							'target' => 'search_title',
							'type' => 'NEQ',
							'value' => ''
						)
					),
					array(
						'id' => 'search_border_color',
						'type' => 'color',
						'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
						'default' => '#ffffff'
					),
					array(
						'id' => 'search_container_bg',
						'type' => 'color',
						'label' => __( 'Search container background', 'minerva-kb' ),
						'default' => '#ffffff'
					),
					array(
						'id' => 'search_container_image_bg',
						'type' => 'media',
						'label' => __( 'Search container background image (optional)', 'minerva-kb' ),
						'default' => ''
					),
					array(
						'id' => 'add_gradient_overlay',
						'type' => 'checkbox',
						'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
						'default' => false
					),
					array(
						'id' => 'search_container_gradient_from',
						'type' => 'color',
						'label' => __( 'Search container gradient from', 'minerva-kb' ),
						'default' => '#00c1b6',
						'dependency' => array(
							'target' => 'add_gradient_overlay',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_container_gradient_to',
						'type' => 'color',
						'label' => __( 'Search container gradient to', 'minerva-kb' ),
						'default' => '#136eb5',
						'dependency' => array(
							'target' => 'add_gradient_overlay',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_container_gradient_opacity',
						'type' => 'input',
						'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
						'default' => 1,
						'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'add_gradient_overlay',
							'type' => 'EQ',
							'value' => true
						),
					),
					array(
						'id' => 'add_pattern_overlay',
						'type' => 'checkbox',
						'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
						'default' => false
					),
					array(
						'id' => 'search_container_image_pattern',
						'type' => 'media',
						'label' => __( 'Search container background pattern image (optional)', 'minerva-kb' ),
						'default' => '',
						'dependency' => array(
							'target' => 'add_pattern_overlay',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_container_image_pattern_opacity',
						'type' => 'input',
						'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
						'default' => 1,
						'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'add_pattern_overlay',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_tip_color',
						'type' => 'color',
						'label' => __( 'Search tip color', 'minerva-kb' ),
						'default' => '#cccccc',
						'dependency' => array(
							'target' => 'show_search_tip',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_results_topic_bg',
						'type' => 'color',
						'label' => __( 'Search results topic background', 'minerva-kb' ),
						'default' => '#4a90e2',
						'dependency' => array(
							'target' => 'show_topic_in_results',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_results_topic_color',
						'type' => 'color',
						'label' => __( 'Search results topic color', 'minerva-kb' ),
						'default' => '#ffffff',
						'dependency' => array(
							'target' => 'show_topic_in_results',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_results_topic_use_custom',
						'type' => 'checkbox',
						'label' => __( 'Use custom topic colors in search results?', 'minerva-kb' ),
						'default' => false,
						'description' => __( 'Topic custom color will be used as background color for topic label', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'show_topic_in_results',
							'type' => 'EQ',
							'value' => true
						)
					),

					// ICONS
					array(
						'id' => 'home_search_icons_title',
						'type' => 'title',
						'label' => __( 'Search icons', 'minerva-kb' ),
						'description' => __( 'Configure search icons', 'minerva-kb' )
					),
					array(
						'id' => 'search_icons_left',
						'type' => 'checkbox',
						'label' => __( 'Show search bar icons on the left side?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'show_search_icon',
						'type' => 'checkbox',
						'label' => __( 'Show search icon?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'search_icon',
						'type' => 'icon_select',
						'label' => __( 'Search icon', 'minerva-kb' ),
						'default' => 'fa-search',
						'dependency' => array(
							'target' => 'show_search_icon',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'search_clear_icon',
						'type' => 'icon_select',
						'label' => __( 'Search clear icon', 'minerva-kb' ),
						'default' => 'fa-times-circle'
					),
					array(
						'id' => 'search_clear_icon_tooltip',
						'type' => 'input',
						'label' => __( 'Clear icon tooltip', 'minerva-kb' ),
						'default' => __( 'Clear search', 'minerva-kb' )
					)
				)
			),
			/**
			 * Topics
			 */
			'topics' => array(
				'id' => 'topics',
				'title' => __( 'Topics', 'minerva-kb' ),
				'settings' => array(
					array(
						'id' => 'topics_title',
						'type' => 'input',
						'label' => __( 'Topics title', 'minerva-kb' ),
						'default' => __( 'Popular topics', 'minerva-kb' )
					),
					array(
						'id' => 'topics_title_size',
						'type' => 'input',
						'label' => __( 'Topics title font size', 'minerva-kb' ),
						'default' => __( '2em', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 2em or 20px',
						'dependency' => array(
							'target' => 'topics_title',
							'type' => 'NEQ',
							'value' => ''
						)
					),
					array(
						'id' => 'home_view',
						'type' => 'image_select',
						'label' => __( 'Home view', 'minerva-kb' ),
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
						'default' => 'list'
					),
					array(
						'id' => 'home_layout',
						'type' => 'image_select',
						'label' => __( 'Page layout', 'minerva-kb' ),
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
						'id' => 'home_topics',
						'type' => 'term_select',
						'label' => __( 'Select topics to display on home page', 'minerva-kb' ),
						'default' => '',
						'tax' => MKB_Options::option('article_cpt_category'),
						'extra_items' => array(
							array(
								'key' => 'recent',
								'label' => __('Recent', 'minerva-kb')
							),
							array(
								'key' => 'updated',
								'label' => __('Recently updated', 'minerva-kb')
							),
							array(
								'key' => 'top_views',
								'label' => __('Most viewed', 'minerva-kb')
							),
							array(
								'key' => 'top_likes',
								'label' => __('Most liked', 'minerva-kb')
							)
						),
						'description' => __( 'You can leave it empty to display all recent topics. NOTE: dynamic topics only work for list view', 'minerva-kb' )
					),
					array(
						'id' => 'home_topics_limit',
						'type' => 'input',
						'label' => __( 'Number of topics to display', 'minerva-kb' ),
						'default' => -1,
						'description' => __( 'Used in case no specific topics are selected. You can use -1 to display all', 'minerva-kb' )
					),
					array(
						'id' => 'home_topics_hide_children',
						'type' => 'checkbox',
						'label' => __( 'Hide child topics?', 'minerva-kb' ),
						'default' => false,
						'description' => __( 'If you don\'t select specific topics, you can use this option to show only top-level topics', 'minerva-kb' )
					),
					array(
						'id' => 'home_topics_articles_limit',
						'type' => 'input',
						'label' => __( 'Number of article to display', 'minerva-kb' ),
						'default' => 5,
						'description' => __( 'You can use -1 to display all', 'minerva-kb' )
					),
					array(
						'id' => 'home_topics_show_description',
						'type' => 'checkbox',
						'label' => __( 'Show description?', 'minerva-kb' ),
						'default' => true,
						'dependency' => array(
							'target' => 'home_view',
							'type' => 'EQ',
							'value' => 'box'
						)
					),
					array(
						'id' => 'show_all_switch',
						'type' => 'checkbox',
						'label' => __( 'Add "Show all" link?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'show_all_label',
						'type' => 'input',
						'label' => __( 'Show all link label', 'minerva-kb' ),
						'default' => __( 'Show all', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'show_all_switch',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'show_articles_count',
						'type' => 'checkbox',
						'label' => __( 'Show articles count?', 'minerva-kb' ),
						'default' => true
					),

					// COLORS
					array(
						'id' => 'home_topic_colors_title',
						'type' => 'title',
						'label' => __( 'Topic colors', 'minerva-kb' ),
						'description' => __( 'Configure topic colors', 'minerva-kb' )
					),
					array(
						'id' => 'topic_color',
						'type' => 'color',
						'label' => __( 'Topic color', 'minerva-kb' ),
						'default' => '#4a90e2',
						'description' => __( 'Note, that topic color can be changed for each topic individually on topic edit page', 'minerva-kb' )
					),
					array(
						'id' => 'force_default_topic_color',
						'type' => 'checkbox',
						'label' => __( 'Force topic color (override topic custom colors)?', 'minerva-kb' ),
						'default' => false,
						'description' => __( 'By default, colors from topic settings have higher priority. You can override it with this setting', 'minerva-kb' )
					),
					array(
						'id' => 'topics_title_color',
						'type' => 'color',
						'label' => __( 'Topics title color', 'minerva-kb' ),
						'default' => '#333333',
						'dependency' => array(
							'target' => 'topics_title',
							'type' => 'NEQ',
							'value' => ''
						)
					),
					array(
						'id' => 'box_view_item_bg',
						'type' => 'color',
						'label' => __( 'Box view items background', 'minerva-kb' ),
						'default' => '#ffffff',
						'dependency' => array(
							'target' => 'home_view',
							'type' => 'EQ',
							'value' => 'box'
						)
					),
					array(
						'id' => 'box_view_item_hover_bg',
						'type' => 'color',
						'label' => __( 'Box view items hover background', 'minerva-kb' ),
						'default' => '#f8f8f8',
						'dependency' => array(
							'target' => 'home_view',
							'type' => 'EQ',
							'value' => 'box'
						)
					),
					array(
						'id' => 'articles_count_bg',
						'type' => 'color',
						'label' => __( 'List view articles count background', 'minerva-kb' ),
						'default' => '#4a90e2',
						'dependency' => array(
							'target' => 'show_articles_count',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'articles_count_color',
						'type' => 'color',
						'label' => __( 'List view articles count color', 'minerva-kb' ),
						'default' => '#ffffff',
						'dependency' => array(
							'target' => 'show_articles_count',
							'type' => 'EQ',
							'value' => true
						)
					),

					// ICONS
					array(
						'id' => 'home_topic_icons_title',
						'type' => 'title',
						'label' => __( 'Topic icons', 'minerva-kb' ),
						'description' => __( 'Configure topic icons settings', 'minerva-kb' )
					),
					array(
						'id' => 'show_topic_icons',
						'type' => 'checkbox',
						'label' => __( 'Show topic icons?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'topic_icon',
						'type' => 'icon_select',
						'label' => __( 'Topic icon', 'minerva-kb' ),
						'default' => 'fa-list-alt',
						'description' => __( 'Note, that topic icon can be changed for each topic individually on topic edit page', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'show_topic_icons',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'force_default_topic_icon',
						'type' => 'checkbox',
						'label' => __( 'Force topic icon (override topic custom icons)?', 'minerva-kb' ),
						'default' => false,
						'description' => __( 'By default, icons from topic settings have higher priority. You can override it with this setting', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'show_topic_icons',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'use_topic_image',
						'type' => 'checkbox',
						'label' => __( 'Box view only: Show image instead of icon? Image URL can be added on each topic page', 'minerva-kb' ),
						'default' => false,
						'dependency' => array(
							'target' => 'show_topic_icons',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'image_size',
						'type' => 'input',
						'label' => __( 'Topic image size', 'minerva-kb' ),
						'default' => __( '10em', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 2em or 20px',
						'dependency' => array(
							'target' => 'show_topic_icons',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'topic_icon_padding_top',
						'type' => 'input',
						'label' => __( 'Topic icon/image top padding', 'minerva-kb' ),
						'default' => __( '0', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 2em or 20px',
						'dependency' => array(
							'target' => 'show_topic_icons',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'topic_icon_padding_bottom',
						'type' => 'input',
						'label' => __( 'Topic icon/image bottom padding', 'minerva-kb' ),
						'default' => __( '0', 'minerva-kb' ),
						'description' => 'Use any CSS value, for ex. 2em or 20px',
						'dependency' => array(
							'target' => 'show_topic_icons',
							'type' => 'EQ',
							'value' => true
						)
					),

					// ARTICLES
					array(
						'id' => 'home_articles_title',
						'type' => 'title',
						'label' => __( 'Articles settings', 'minerva-kb' ),
						'description' => __( 'Configure how articles list should look', 'minerva-kb' )
					),
					array(
						'id' => 'show_article_icons',
						'type' => 'checkbox',
						'label' => __( 'List view only: Show article icons?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'article_icon',
						'type' => 'icon_select',
						'label' => __( 'Article icon', 'minerva-kb' ),
						'default' => 'fa-book',
						'dependency' => array(
							'target' => 'show_article_icons',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'articles_text',
						'type' => 'input',
						'label' => __( 'Article plural text', 'minerva-kb' ),
						'default' => __( 'articles', 'minerva-kb' )
					),
					array(
						'id' => 'article_text',
						'type' => 'input',
						'label' => __( 'Article singular text', 'minerva-kb' ),
						'default' => __( 'article', 'minerva-kb' )
					),
				)
			),
			/**
			 * Page content
			 */
			'page-content' => array(
				'id' => 'page-content',
				'title' => __( 'Page content', 'minerva-kb' ),
			),
			/**
			 * FAQ
			 */
			'faq' => array(
				'id' => 'faq',
				'title' => __( 'FAQ', 'minerva-kb' ),
				'settings' => array(
					array(
						'id' => 'home_faq_title',
						'type' => 'input',
						'label' => __( 'FAQ title', 'minerva-kb' ),
						'default' => __( 'Frequently Asked Questions', 'minerva-kb' )
					),
					array(
						'id' => 'home_faq_title_size',
						'type' => 'css_size',
						'label' => __( 'FAQ title font size', 'minerva-kb' ),
						'default' => array("unit" => 'em', "size" => "3"),
						'description' => 'Use any CSS value, for ex. 3em or 20px',
						'dependency' => array(
							'target' => 'home_faq_title',
							'type' => 'NEQ',
							'value' => ''
						)
					),
					array(
						'id' => 'home_faq_title_color',
						'type' => 'color',
						'label' => __( 'FAQ title color', 'minerva-kb' ),
						'default' => '#333333',
						'dependency' => array(
							'target' => 'home_faq_title',
							'type' => 'NEQ',
							'value' => ''
						)
					),
					array(
						'id' => 'home_faq_layout_section_title',
						'type' => 'title',
						'label' => __( 'Home FAQ layout', 'minerva-kb' ),
						'description' => __( 'Configure FAQ layout on home page', 'minerva-kb' )
					),
					array(
						'id' => 'home_faq_margin_top',
						'type' => 'css_size',
						'label' => __( 'FAQ section top margin', 'minerva-kb' ),
						'default' => array("unit" => 'em', "size" => "3"),
						'description' => __( 'Distance between FAQ and previous section', 'minerva-kb' ),
					),
					array(
						'id' => 'home_faq_margin_bottom',
						'type' => 'css_size',
						'label' => __( 'FAQ section bottom margin', 'minerva-kb' ),
						'default' => array("unit" => 'em', "size" => "3"),
						'description' => __( 'Distance between FAQ and next sections', 'minerva-kb' ),
					),
					array(
						'id' => 'home_faq_limit_width_switch',
						'type' => 'checkbox',
						'label' => __( 'Limit FAQ container width?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'home_faq_width_limit',
						'type' => 'css_size',
						'label' => __( 'FAQ container maximum width', 'minerva-kb' ),
						'default' => array("unit" => 'em', "size" => "60"),
						'description' => __( 'You can make FAQ section more narrow, than your content width', 'minerva-kb' ),
						'dependency' => array(
							'target' => 'home_faq_limit_width_switch',
							'type' => 'EQ',
							'value' => true
						)
					),
					array(
						'id' => 'home_faq_controls_margin_top',
						'type' => 'css_size',
						'label' => __( 'FAQ controls top margin', 'minerva-kb' ),
						'default' => array("unit" => 'em', "size" => "2"),
						'description' => __( 'Distance between FAQ controls and title', 'minerva-kb' ),
					),
					array(
						'id' => 'home_faq_controls_margin_bottom',
						'type' => 'css_size',
						'label' => __( 'FAQ controls bottom margin', 'minerva-kb' ),
						'default' => array("unit" => 'em', "size" => "2"),
						'description' => __( 'Distance between FAQ controls and questions', 'minerva-kb' ),
					),
					array(
						'id' => 'home_faq_controls_section_title',
						'type' => 'title',
						'label' => __( 'Home FAQ controls', 'minerva-kb' ),
						'description' => __( 'Configure FAQ controls on home page', 'minerva-kb' )
					),
					array(
						'id' => 'home_show_faq_filter',
						'type' => 'checkbox',
						'label' => __( 'Show FAQ live filter?', 'minerva-kb' ),
						'default' => true
					),
					array(
						'id' => 'home_show_faq_toggle_all',
						'type' => 'checkbox',
						'label' => __( 'Show FAQ toggle all button?', 'minerva-kb' ),
						'default' => false
					),
					array(
						'id' => 'home_faq_categories_section_title',
						'type' => 'title',
						'label' => __( 'FAQ categories settings', 'minerva-kb' ),
						'description' => __( 'Configure FAQ categories', 'minerva-kb' )
					),
					array(
						'id' => 'home_faq_categories',
						'type' => 'term_select',
						'label' => __( 'Select FAQ categories to display on home page', 'minerva-kb' ),
						'default' => '',
						'tax' => 'mkb_faq_category',
						'description' => __( 'You can leave it empty to display all categories.', 'minerva-kb' )
					),
					array(
						'id' => 'home_show_faq_categories',
						'type' => 'checkbox',
						'label' => __( 'Show FAQ categories?', 'minerva-kb' ),
						'default' => false
					),
					array(
						'id' => 'home_show_faq_category_count',
						'type' => 'checkbox',
						'label' => __( 'Show FAQ category question count?', 'minerva-kb' ),
						'default' => true,
					),
					array(
						'id' => 'home_faq_styles_note_title',
						'type' => 'title',
						'label' => __( 'NOTE: You can configure FAQ styles in Settings - FAQ (global)', 'minerva-kb' )
					),
				)
			),
		);
	}
}