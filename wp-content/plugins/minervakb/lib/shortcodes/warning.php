<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_WarningShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'warning';
	protected $name = 'Warning';
	protected $description = 'Highlight warning points in your text';
	protected $icon = 'fa fa-exclamation-triangle';
	protected $has_content = true;

	public function render($atts, $content = '') {
		?>
		<div class="mkb-warning">
			<div class="mkb-warning__icon">
				<i class="fa fa-lg <?php echo esc_attr(MKB_Options::option( 'warning_icon' )); ?>"></i>
			</div>
			<div class="mkb-warning__content">
				<?php echo wp_kses_post( $content ); ?>
			</div>
		</div>
	<?php
	}
}