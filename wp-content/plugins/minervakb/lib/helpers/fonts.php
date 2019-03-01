<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/google-fonts.php');

function mkb_get_all_fonts() {
	return array(
		'STANDARD_SERIF' => array(
			"id" => 'Standard serif',
			"fonts" => array(
				"Georgia" => "Georgia",
				"Palatino Linotype" => "Palatino Linotype",
				"Book Antiqua" => "Book Antiqua",
				"Times New Roman" => "Times New Roman"
			)
		),
		'STANDARD_SANS_SERIF' => array(
			"id" => 'Standard sans-serif',
			"fonts" => array(
				"Arial" => "Arial",
				"Arial Black" => "Arial Black",
				"Comic Sans MS" => "Comic Sans MS",
				"Impact" => "Impact",
				"Lucida Sans Unicode" => "Lucida Sans Unicode",
				"Tahoma" => "Tahoma",
				"Trebuchet MS" => "Trebuchet MS",
				"Verdana" => "Verdana"
			)
		),
		'STANDARD_MONOSPACE' => array(
			"id" => 'Standard monospace',
			"fonts" => array(
				"Courier New" => "Courier New",
				"Lucida Console" => "Lucida Console"
			)
		),
		'GOOGLE' => array(
			"id" => "Google fonts",
			"fonts" => mkb_get_google_fonts()
		)
	);
}

/**
 * Gets all available Google Fonts styles
 * @return array
 */
function mkb_get_all_gf_weights() {
	return array(
		'300' => 'Light',
		'300i' => 'Light Italic',
		'400' => 'Regular',
		'400i' => 'Regular Italic',
		'600' => 'Semi-bold',
		'600i' => 'Semi-bold Italic',
		'700' => 'Bold',
		'700i' => 'Bold Italic',
		'800' => 'Extra Bold',
		'800i' => 'Extra Bold Italic',
	);
}

/**
 * Gets all available Google Fonts language subsets
 * @return array
 */
function mkb_get_all_gf_languages() {
	return array(
		'latin-ext' => 'Latin Extended',
		'cyrillic' => 'Cyrillic',
		'cyrillic-ext' => 'Cyrillic Extended',
		'greek' => 'Greek',
		'greek-ext' => 'Greek Extended',
		'vietnamese' => 'Vietnamese'
	);
}

/**
 * Gets google font URL for family
 * @param $family
 * @return string
 */
function mkb_get_google_font_url($family, $weights, $languages) {
	$endpoint = 'https://fonts.googleapis.com/css';

	return $endpoint . "?" .
		"family=$family" . (is_array($weights) && sizeof($weights) ? (":" . implode(',', $weights)) : "") .
	       (is_array($languages) && sizeof($languages) ? "&subset=" . implode(',', $languages) : "");
}