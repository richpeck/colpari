<?php

/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 4/7/2017
 * Time: 7:13 PM
 */
class WPAS_GF_MAPPINGS {

	protected $mappings;

	/**
	 * WPAS_GF_MAPPINGS constructor.
	 *
	 * @param array|null $mappings
	 *
	 */
	public function __construct( $mappings = null ) {

		$this->mappings = $mappings;

		return $this->mappings;
	}

	/**
	 * @param $form_id
	 * @param $field_id
	 *
	 * @return int|string|false
	 */
	public function get_field_name_by_id( $form_id, $field_id ) {

		$field_name = false;

		if( isset( $this->mappings[ $form_id ] ) ) {

			$mappings = $this->mappings[ $form_id ];

			foreach( $mappings as $name => $mapping ) {
				if( 'options' !== $name && absint( $mapping[ 'id' ] ) === $field_id ) {
					$field_name = $name;
					break;
				}
			}
		}

		return $field_name;
	}

	/**
	 * Determine if field is mapped
	 *
	 * @param integer $form_id    Gravity Forms form id
	 * @param string  $field_name Gravity Forms field name
	 *
	 * @return integer|false
	 */
	public function is_field_mapped( $form_id, $field_name ) {

		$value = false;

		if( isset( $this->mappings[ $form_id ][ $field_name ] ) && ! empty( $this->mappings[ $form_id ][ $field_name ] ) ) {
			if( ! empty( $this->mappings[ $form_id ][ $field_name ][ 'id' ] ) ) {
				$value = absint( $this->mappings[ $form_id ][ $field_name ][ 'id' ] );
			}
		}

		return $value;
	}

	/**
	 * @param $form_id
	 * @param $field_name
	 * @param $attribute_type
	 * @param $attribute
	 *
	 * @return bool|int
	 */
	public function get_attribute( $form_id, $field_name, $attribute_type, $attribute ) {

		$value = false;

		if( isset( $this->mappings[ $form_id ][ $field_name ][ $attribute_type ] ) && $this->mappings[ $form_id ][ $field_name ][ $attribute_type ] ) {
			if( isset( $this->mappings[ $form_id ][ $field_name ][ $attribute_type ][ $attribute ] ) ) {
				$value = absint( $this->mappings[ $form_id ][ $field_name ][ $attribute_type ][ $attribute ] );
			}
		}

		return $value;
	}

	/**
	 * @param        $form_id
	 * @param        $field_name
	 * @param string $attribute
	 * @param string $special_option
	 *
	 * @return bool|false|int
	 */
	public function get_field_setting( $form_id, $field_name, $attribute = '', $special_option = '' ) {

		if( $attribute !== '' ) {
			$setting = $this->get_attribute( $form_id, $field_name, 'attributes', $attribute); //'id' );
		}
		elseif( $special_option !== '' ) {
			$setting = $this->get_attribute( $form_id, $field_name, $special_option, 'id' );
		}
		else {
			$setting = $this->is_field_mapped( $form_id, $field_name );
		}

		return $setting;

	}

}

