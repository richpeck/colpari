<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_GuestPostShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'guestpost';
	protected $name = 'Guest Posting Form';
	protected $description = 'Allows guests to submit articles from the client side';
	protected $icon = 'fa fa-paper-plane-o';

	/**
	 * Renders shortcode
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
		MKB_TemplateHelper::render_guestpost();
	}
}