<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_TipShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'tip';
	protected $name = 'Tip';
	protected $description = 'Highlight useful points in your text';
	protected $icon = 'fa fa-lightbulb-o';
	protected $has_content = true;

	public function render($atts, $content = '') {
		?>
		<div class="mkb-tip">
			<div class="mkb-tip__icon">
				<i class="fa fa-lg <?php echo esc_attr(MKB_Options::option( 'tip_icon' )); ?>"></i>
			</div>
			<div class="mkb-tip__content">
				<?php echo wp_kses_post( $content ); ?>
			</div>
		</div>
	<?php
	}
}