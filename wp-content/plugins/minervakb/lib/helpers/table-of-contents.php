<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_TableOfContents {

	private $current_page = null;

	private $pages = array();

	private $page_items = array();

	private $paged = 1;

	private $show_numbers = true;

	private $shortcode_pattern;

	private $headings_pattern = '|<\s*h([1-6])(?:.*)>(.*)</\s*h|Ui';

	private $exclude_headings = array();

	private $anchors = array();

	private $content;

	private $levels = array();

	private $text = array();

	/**
	 * Contents
	 */
	public function __construct() {
		global $minerva_kb_content_filter_running_global;

		$this->content_raw = get_post_field( 'post_content', get_the_ID() );

		$minerva_kb_content_filter_running_global = true;
		$this->content = MKB_Options::option( 'toc_content_parse' ) ?
			apply_filters('the_content', $this->content_raw) :
			$this->content_raw;
		$minerva_kb_content_filter_running_global = false;

		$this->find_pages();

		$this->show_numbers = (bool) MKB_Options::option('toc_numbers_enable');

		if (MKB_Options::option('toc_headings_exclude')) {
			$this->set_excluded_headings(MKB_Options::option('toc_headings_exclude'));
		}

		$this->anchors = array();
		$this->shortcode_pattern = get_shortcode_regex();
	}

	/**
	 * Parses heading exclusion config, if available
	 * @param $value
	 */
	private function set_excluded_headings($value) {
		$this->exclude_headings = array_filter(array_map(function($item) {
			return strtolower(trim($item));
		}, explode(',', trim($value))));
	}

	private function find_pages() {
		$needle = '<!--nextpage-->';
		$lastPos = 0;
		$this->pages = array();

		while (($lastPos = strpos($this->content, $needle, $lastPos))!== false) {
			$this->pages[] = $lastPos;
			$lastPos = $lastPos + strlen($needle);
		}

		global $page;

		$this->paged = isset($page) && $page ? $page : 1;
	}

	private function save_paged_headings() {
		if (empty($this->pages)) {
			return;
		}

		$page = 0;
		$this->page_items[$page] = array();

		foreach($this->text as $text_item) {
			$position = $text_item[1];

			if ($page < sizeof($this->pages) && $position > $this->pages[$page]) {
				++$page;
				$this->page_items[$page] = array();
			}

			array_push($this->page_items[$page], $position);
		}
	}

	private function get_index_on_page($page, $pos) {
		$page_index = $page - 1;
		$index = 0;

		foreach($this->page_items[$page_index] as $page_item) {
			if ($pos <= $page_item) {
				break;
			}

			++$index;
		}

		return $index;
	}
	
	private function get_page_number($pos) {
		$page = 1;
		
		if (empty($this->pages)) {
			return $page;
		}
		
		foreach($this->pages as $page_start) {
			if ($pos < $page_start) {
				break;
			}

			++$page;
		}

		return $page;
	}
	
	private function is_same_page($pos) {
		return $this->get_page_number($pos) === $this->paged;
	}

	private function get_page_link($pos) {
		$page = $this->get_page_number($pos);

		return $this->is_same_page($pos) ?
			'#' :
			$this->get_paged_permalink($this->get_page_number($pos)) .
			'#' . 'ch_' . ($this->get_index_on_page($page, $pos) + 1);
	}

	/**
	 * Copy of WP private method for paginated links
	 * @param $i
	 *
	 * @return string
	 */
	private function get_paged_permalink( $i ) {
		global $wp_rewrite;
		$post = get_post();
		$query_args = array();

		if ( 1 == $i ) {
			$url = get_permalink();
		} else {
			if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
				$url = add_query_arg( 'page', $i, get_permalink() );
			elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') == $post->ID )
				$url = trailingslashit(get_permalink()) . user_trailingslashit("$wp_rewrite->pagination_base/" . $i, 'single_paged');
			else
				$url = trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged');
		}

		if ( is_preview() ) {

			if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
				$query_args['preview_id'] = wp_unslash( $_GET['preview_id'] );
				$query_args['preview_nonce'] = wp_unslash( $_GET['preview_nonce'] );
			}

			$url = get_preview_post_link( $post, $query_args, $url );
		}

		return $url;
	}

	/**
	 * Article table of contents
	 */
	public function render() {

		if ( preg_match_all( '/'. $this->shortcode_pattern .'/s', $this->content_raw, $matches )
		     && array_key_exists( 2, $matches )
		     && in_array( 'mkb-anchor', $matches[2] )
		     && isset($matches[5])) {

			foreach($matches[5] as $index => $match):
				if (isset($matches[2][$index]) && $matches[2][$index] === 'mkb-anchor') {
					array_push($this->anchors, $match);
				}
			endforeach;
		}

		if ( ! empty( $this->anchors ) ):
			?>
			<div class="mkb-anchors-list">
				<div class="mkb-anchors-list__title">
					<?php echo esc_html(MKB_Options::option( 'toc_label' )); ?>
				</div>
				<ul class="mkb-anchors-list__container">
					<?php
					foreach ( $this->anchors as $index => $anchor ):
						?>
						<li class="mkb-anchors-list__item">
						<a href="#" class="mkb-anchors-list__item-link" data-index="<?php echo esc_attr($index); ?>">
								<span class="mkb-anchors-list__item-link-label">
								<?php if ($this->show_numbers): ?>
									<?php echo esc_html($index + 1) . '. '; ?>
								<?php endif ?>
								<?php echo esc_html( $anchor ); ?>
								</span>
						</a>
						</li><?php
					endforeach;
					?>
				</ul>
			</div>
		<?php
		elseif(MKB_Options::option( 'toc_dynamic_enable' )):

			preg_match_all($this->headings_pattern, $this->content, $matches, PREG_OFFSET_CAPTURE);

			if (
				isset($matches) &&
				is_array($matches) &&
				sizeof($matches) == 3 &&
				isset($matches[1]) &&
				isset($matches[2]) &&
				sizeof($matches[1]) > 0 &&
				sizeof($matches[2]) > 0
			) {
				// headings found

				$this->levels = $matches[1];
				$this->text = $matches[2];

				if (!empty($this->exclude_headings)) {
					foreach($this->levels as $index => $level) {
						$heading_level = $level[0];

						if (in_array('h' . $heading_level, $this->exclude_headings)) {
							unset($this->levels[$index]);
							unset($this->text[$index]);
						}
					}
				}

				$this->save_paged_headings();
				$this->build_dynamic_toc($this->levels, $this->text);
			}

		endif;
	}

	/**
	 * Builds dynamic Table fo Contents from headings found in content
	 * @param $levels
	 * @param $text
	 */
	private function build_dynamic_toc($levels, $text) {

		// convert all heading levels to integers
		$levels = array_map(function($level) {
			return (int)$level[0];
		}, $levels);

		if (MKB_Options::option( 'toc_hierarchical_enable' )) {
			$this->render_hierarchical_dynamic_toc($levels, $text);
		} else {
			$this->render_flat_dynamic_toc($levels, $text);
		}
	}

	/**
	 * Renders hierarchical TOC list
	 * @param $levels
	 * @param $text
	 */
	private function render_hierarchical_dynamic_toc($levels, $text) {
		// build hierarchical tree
		$tree = $this->build_levels_tree(array_merge(array(0), $levels));

		// render TOC html
		if (sizeof($tree) && isset($tree["children"]) && sizeof($tree["children"])) {
			?>
			<div class="mkb-anchors-list mkb-anchors-list--dynamic mkb-anchors-list--hierarchical">
				<div class="mkb-anchors-list__title">
					<?php echo esc_html(MKB_Options::option( 'toc_label' )); ?>
				</div>
				<?php
				$index = -1;
				echo $this->get_toc_hierarchical_ul_html($tree["children"], $text, $index);
				?>
			</div>
		<?php
		}
	}

	/**
	 * Renders flat dynamic TOC
	 * @param $levels
	 * @param $text
	 */
	private function render_flat_dynamic_toc($levels, $text) {
		$index = -1;
		$link_index = -1;

		?>
		<div class="mkb-anchors-list mkb-anchors-list--dynamic mkb-anchors-list--hierarchical">
			<div class="mkb-anchors-list__title">
				<?php echo esc_html(MKB_Options::option( 'toc_label' )); ?>
			</div>
			<ul class="mkb-anchors-list__container">
				<?php foreach($levels as $level):
					$text_item = array_shift($text);

					$position = $text_item[1];

					if ($this->is_same_page($position)) {
						++$link_index;
					}

					++$index;

					?>
					<li class="mkb-anchors-list__item">
						<a href="<?php echo esc_attr($this->get_page_link($position)); ?>"
						   class="mkb-anchors-list__item-link" data-index="<?php echo esc_attr($link_index); ?>">
							<span class="mkb-anchors-list__item-link-label">
							<?php if ($this->show_numbers): ?>
								<?php echo $index + 1 . '. '; ?>
							<?php endif; ?>
							<?php echo strip_tags($text_item[0]); ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php
	}

	/**
	 * Builds normalized hierarchical headings tree from content,
	 * converting all detached nodes to top level nodes
	 * @param $list
	 * @return array
	 */
	private function build_levels_tree($list) {
		$tree_root = array();
		$root = array_shift($list);
		$tree_root["value"] = $root;

		if (sizeof($list) == 0) {
			return $tree_root;
		}

		// build child branches
		$children = array();
		$current_root = $list[0];
		$current_index = -1;

		foreach ($list as $level) {
			if ($level > $current_root) {
				array_push($children[$current_index], $level);
			} else {
				$current_root = $level;
				$children[++$current_index] = array($level);
			}
		}

		if (sizeof ($children)) {
			$tree_root["children"] = array();

			foreach ($children as $branch) {
				array_push($tree_root["children"], $this->build_levels_tree($branch));
			}
		}

		return $tree_root;
	}

	/**
	 * Builds hierarchical TOC html from headings tree
	 * @param $items
	 * @return string
	 */
	private function get_toc_hierarchical_ul_html($items, &$text, &$index, $parent_prefix = '') {
		$html = $parent_prefix == '' ? '<ul class="mkb-anchors-list__container">' : '<ul>';
		$local_index = 0;

		foreach ($items as $item) {
			$text_item = array_shift($text);
			$position = $text_item[1];

			if ($this->is_same_page($position)) {
				++$index;
			}

			$html .= '<li class="mkb-anchors-list__item">' .
			         '<a href="' . $this->get_page_link($position) . '" class="mkb-anchors-list__item-link" data-index="' .
			         esc_attr($index) . '">' .
			         '<span class="mkb-anchors-list__item-link-label">';

			if ($this->show_numbers) {
				$html .= $parent_prefix . ($parent_prefix !== '' ? '.' : '') . (++$local_index) . '. ';
			}

			$html .= strip_tags($text_item[0]);

			$html .= '</span>' .
			         '</a>';

			if (isset($item['children'])) {
				$html .= $this->get_toc_hierarchical_ul_html($item['children'], $text, $index,
					$parent_prefix . ($parent_prefix !== '' ? '.' : '') . $local_index);
			}

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

}