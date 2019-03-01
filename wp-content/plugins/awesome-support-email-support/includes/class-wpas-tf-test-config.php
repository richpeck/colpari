<?php

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class TitanFrameworkOptionEmailTestConfig extends TitanFrameworkOption {

	public $defaultSecondarySettings = array(
		'placeholder' => '', // show this when blank
		'is_password' => false,
		'sanitize_callbacks' => array(),
		'maxlength' => '',
		'unit' => '',
	);

	/*
	 * Display for options and meta
	 */
	public function display() {
		$this->echoOptionHeader();
		printf( '<a href="#" class="button button-secondary wpas-mail-test-settings">Test</a>' );
		printf( '<div class="wpas-mail-test-settings-result"></div>' );
		$this->echoOptionFooter();
	}
}
