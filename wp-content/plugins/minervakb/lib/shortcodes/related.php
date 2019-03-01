<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_RelatedShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'related';
	protected $name = 'Related Articles';
	protected $description = 'Block with related content links';
	protected $icon = 'fa fa-sitemap';

	public function render($atts, $content = '') {
		$args = wp_parse_args($atts, $this->get_defaults());

		$ids = explode(",", $args["ids"]);

		if ($ids && is_array($ids) && !empty($ids)):
			?>
			<div class="mkb-related-content">
				<div class="mkb-related-content-title"><?php echo esc_html(MKB_Options::option('related_content_label')); ?></div>
				<ul class="mkb-related-content-list">
					<?php foreach($ids as $id):
						if ( empty($id) || !is_string( get_post_status( $id )) ) {
							continue;
						}
						?>
						<li><a href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php
		endif;
	}

	/**
	 * Returns all shortcode options
	 * @return array
	 */
	public static function get_options() {
		return array(
			array(
				'id' => 'ids',
				'type' => 'articles_list',
				'label' => __( 'Select related articles', 'minerva-kb' ),
				'default' => '',
				'admin_label' => true
			)
		);
	}
}