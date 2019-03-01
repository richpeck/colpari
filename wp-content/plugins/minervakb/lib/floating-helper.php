<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_FloatingHelper {

	private $info;

	/**
	 * Init
	 */
	public function __construct($deps) {
		$this->setup_dependencies($deps);

		add_action('wp_footer', array($this, 'render'));
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}
	}

	/**
	 * Checks if helper should be rendered on current page
	 * @return null
	 */
	private function should_display() {
		$should_display = true;

		if (!MKB_Options::option('floating_helper_switch')) {
			// global switch
			$should_display = false;
		} else if (MKB_Options::option('fh_hide_on_kb') && $this->info->is_kb_page()) {
			// kb pages
			$should_display = false;
		} else if (MKB_Options::option('fh_hide_on_blog') && $this->info->is_blog_page()) {
			// blog restriction
			$should_display = false;
		} else if (MKB_Options::option('fh_hide_on_pages') && is_page()) {
			// pages restriction
			$specific_pages = array_filter(explode(',', trim(MKB_Options::option('fh_hide_on_pages_ids'))));

			if (!empty($specific_pages)) {
				if (in_array(get_the_ID(), $specific_pages)) {
					$should_display = false;
				}
			} else {
				$should_display = false;
			}
		} else if (MKB_Options::option('fh_hide_on_mobile') && $this->info->is_mobile()) {
			// mobile
			$should_display = false;
		} else if (MKB_Options::option('fh_hide_on_tablet') && $this->info->is_tablet()) {
			// tablet
			$should_display = false;
		} else if (MKB_Options::option('fh_hide_on_desktop') && $this->info->is_desktop()) {
			// mobile
			$should_display = false;
		}

		return apply_filters('minerva_should_display_helper', $should_display);
	}

	/**
	 * Main helper HTML render
	 */
	public function render() {
		if (!$this->should_display()) {
			return;
		}

		?>
		<div class="mkb-floating-helper-wrap helper-position-<?php esc_attr_e(MKB_Options::option('fh_display_position')); ?> js-mkb-floating-helper">
			<div class="mkb-floating-helper-btn js-mkb-floating-helper-btn">
				<i class="mkb-floating-helper-btn-icon fa <?php esc_attr_e(MKB_Options::option('fh_btn_icon')); ?>"></i>
			</div>
			<div class="mkb-floating-helper-content">
				<?php

				do_action('minerva_helper_content_before');

				?>
				<div class="js-mkb-floating-helper-close mkb-floating-helper-close">
					<i class="fa fa-times-circle"></i>
				</div>
				<div class="mkb-floating-helper-label"><?php esc_html_e(MKB_Options::option('fh_label_text')); ?></div>
				<?php

				do_action('minerva_helper_search_before');

				?>
				<div class="mkb-floating-helper-search"><?php
					MKB_TemplateHelper::render_search(array(
						"search_title" => "",
						"search_tip" => "",
						"search_border_color" => "rgba(0,0,0,0)",
						"search_container_padding_top" => "0px",
						"search_container_padding_bottom" => "0px",
						"search_min_width" => "100%",
						"search_topics" => "",
						"add_gradient_overlay" => false,
						"add_pattern_overlay" => false,
						"disable_autofocus" => true,
						"search_container_bg" => "rgba(0,0,0,0)",
						"search_container_image_bg" => "",
						"show_topic_in_results" => true,

						"search_placeholder" => MKB_Options::option('fh_search_placeholder_text'),
						"search_theme" => 'mini'
					));
					?>
				</div>
				<?php

				do_action('minerva_helper_search_after');

				if (trim(MKB_Options::option('fh_bottom_html'))): ?>
					<div class="mkb-floating-helper-bottom-html">
						<?php echo MKB_Options::option('fh_bottom_html'); ?>
					</div>
				<?php endif;

				do_action('minerva_helper_content_after');

				?>
			</div>
		</div>
	<?php
	}
}