<?php

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

class TitanFrameworkOptionMultiCheckboxOptions extends TitanFrameworkOptionMulticheck {

	/*
	 * Display for options and meta
	 */
	public function display() {

		$values = $this->getValue();

		include AS_Frontend_Agents::get_instance()->dir_path . 'includes/views/settings_template.php';

	}

	public function cleanValueForSaving( $values ) {

		if ( is_array( $values) ) {
			foreach( $values as $fields ) {
				foreach($fields as $view => $data) {
					foreach( $data as $key => $value ) {
						$values[$view][$key] = ( $value != '1') ? 0 : 1;
					}
				}
			}
		}

		return $values;
	}

}
