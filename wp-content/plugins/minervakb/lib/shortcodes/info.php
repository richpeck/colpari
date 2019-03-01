<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_InfoShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'info';
	protected $name = 'Info';
	protected $description = 'Highlight interesting points in your text';
	protected $icon = 'fa fa-info-circle';
	protected $has_content = true;

	public function render($atts, $content = '') {
		?>
		<div class="mkb-info">
			<div class="mkb-info__icon">
				<i class="fa fa-lg <?php echo esc_attr(MKB_Options::option( 'info_icon' )); ?>"></i>
			</div>
			<div class="mkb-info__content">
				<?php echo wp_kses_post( $content ); ?>
			</div>
		</div>
	<?php
	}
}