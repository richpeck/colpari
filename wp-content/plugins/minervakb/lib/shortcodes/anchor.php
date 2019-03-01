<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_AnchorShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'anchor';
	protected $name = 'Anchor';
	protected $description = 'TOC anchor (deprecated, use dynamic Table of Contents)';
	protected $icon = 'fa fa-list-ol';
	protected $has_content = true;

	public function render($atts, $content = '') {
		?>
		<div class="mkb-anchor mkb-clearfix mkb-back-to-top-<?php echo esc_attr(MKB_Options::option('back_to_top_position')); ?>">
			<h3 class="mkb-anchor__title"><?php echo wp_kses_post( $content ); ?></h3>
			<?php if (MKB_Options::option( 'show_back_to_top' )): ?>
				<a href="#" class="mkb-back-to-top" title="<?php echo esc_attr(MKB_Options::option( 'back_to_top_text' )); ?>">
					<?php echo esc_html(MKB_Options::option( 'back_to_top_text' )); ?>
					<?php if (MKB_Options::option('show_back_to_top_icon')): ?>
						<i class="mkb-back-to-top-icon fa <?php echo esc_attr(MKB_Options::option('back_to_top_icon')); ?>"></i>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</div>
	<?php
	}
}