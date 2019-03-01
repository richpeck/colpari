<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Typography Advanced
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_typography_advanced extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();

		echo '<div class="cs-typography_advanced cs-multifield">';

		$defaults_value = array(
			'family'	=> 'Arial',
			'variant'	=> 'regular',
			'font'		=> 'websafe',
			'size'		=> 12,
			'height'	=> 20,
			'spacing'	=> '0',
			'align'		=> 'left',
			'transform'	=> 'none',
			'color'		=> '#000',
			'preview'	=> '',
		);

		$default_variants = apply_filters( 'cs_websafe_fonts_variants', array(
			'regular',
			'italic',
			'700',
			'700italic',
			'inherit'
		));

		$websafe_fonts = apply_filters( 'cs_websafe_fonts', array(
			'Arial',
			'Arial Black',
			'Comic Sans MS',
			'Impact',
			'Lucida Sans Unicode',
			'Tahoma',
			'Trebuchet MS',
			'Verdana',
			'Courier New',
			'Lucida Console',
			'Georgia, serif',
			'Palatino Linotype',
			'Times New Roman'
		));

		$value 				= wp_parse_args( $this->element_value(), $defaults_value );

		$family_value 		= $value['family'];
		$variant_value 		= $value['variant'];
		$value_size			= $value['size'];
		$value_height 		= $value['height'];
		$value_spacing 		= $value['spacing'];
		$value_align 		= $value['align'];
		$value_transform 	= $value['transform'];
		$value_color		= $value['color'];
		$value_preview		= ($value['preview']) ? $value['preview'] : 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.';

		$is_variant 		= ( isset( $this->field['variant'] ) && $this->field['variant'] === false ) ? false : true;
		$is_chosen 			= ( isset( $this->field['chosen'] ) && $this->field['chosen'] === false ) ? '' : 'chosen ';
		$google_json 		= cs_get_google_fonts();
		$chosen_rtl 		= ( is_rtl() && ! empty( $is_chosen ) ) ? 'chosen-rtl ' : '';

		if( is_object( $google_json ) ) {

			$googlefonts 			= array();

			foreach ( $google_json->items as $key => $font ) {
				$googlefonts[$font->family] = $font->variants;
			}

			$is_google 	= ( array_key_exists( $family_value, $googlefonts ) ) ? true : false;

			echo '<div class="cs-element cs-field-select cs-pseudo-field">';
			echo '<label class="cs-typography-family">'.__('Font Family').'</label>';
			echo '<select name="'. $this->element_name( '[family]' ) .'" class="'. $is_chosen . $chosen_rtl .'cs-typo-family" data-atts="family"'. $this->element_attributes() .'>';

			do_action( 'cs_typography_family', $family_value, $this );

			echo '<optgroup label="'. __( 'Web Safe Fonts', 'cs-framework' ) .'">';
			foreach ( $websafe_fonts as $websafe_value ) {
				echo '<option value="'. $websafe_value .'" data-variants="'. implode( '|', $default_variants ) .'" data-type="websafe"'. selected( $websafe_value, $family_value, true ) .'>'. $websafe_value .'</option>';
			}
			echo '</optgroup>';

			echo '<optgroup label="'. __( 'Google Fonts', 'cs-framework' ) .'">';
			foreach ( $googlefonts as $google_key => $google_value ) {
				echo '<option value="'. $google_key .'" data-variants="'. implode( '|', $google_value ) .'" data-type="google"'. selected( $google_key, $family_value, true ) .'>'. $google_key .'</option>';
			}
			echo '</optgroup>';
			echo '</select>';
			echo '</div>';
			

			if( ! empty( $is_variant ) ) {
				$variants_options = array();

				$variants = ( $is_google ) ? $googlefonts[$family_value] : $default_variants;
				$variants = ( $value['font'] === 'google' || $value['font'] === 'websafe' ) ? $variants : array( 'regular' );

				foreach ( $variants as $variant ) {
					$variants_options[$variant] = $variant;
				}
			}

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'select',
				'name'		=> $this->element_name( '[variant]' ),
				'options'	=> $variants_options,
				'value'		=> $variant_value,
				'class'		=> 'cs-typo-variant',
				'before'	=> '<label>'.__('Font Weight & Style').'</label>',
			));

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[size]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> 'px',
				),
				'value'		=> $value_size,
				'attributes' => [
					'placeholder' => 'size'
				],
				'class'		=> 'cs-typo-size',
				'before'	=> '<label>'.__('Font Size').'</label>',
			) );

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[height]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> 'px',
				),
				'value'		=> $value_height,
				'attributes' => [
					'placeholder' => 'height'
				],
				'class'		=> 'cs-typo-height',
				'before'	=> '<label>'.__('Line Height').'</label>',
			) );

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text_addon',
				'name'		=> $this->element_name('[spacing]'),
				'options'	=> array(
					'type'			=> 'append',
					'addon_value'	=> 'px',
				),
				'value'		=> $value_spacing,
				'attributes' => [
					'placeholder' => 'spacing'
				],
				'class'		=> 'cs-typo-spacing',
				'before'	=> '<label>'.__('Letter Spacing').'</label>',
			) );

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'select',
				'name'		=> $this->element_name( '[align]' ),
				'options'	=> [
					'left'		=> __('Align Left'),
					'center'	=> __('Align Center'),
					'right'		=> __('Align Right'),
					'justify'	=> __('Justify')
				],
				'value'		=> $value_align,
				'class'		=> 'cs-typo-align',
				'before'	=> '<label>'.__('Text Align').'</label>',
			));

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'select',
				'name'		=> $this->element_name( '[transform]' ),
				'options'	=> [
					'none'			=> __('None'),
					'capitalize'	=> __('Capitalize'),
					'uppercase'		=> __('Uppercase'),
					'lowercase'		=> __('Lowercase'),
					'initial'		=> __('Initial'),
					'inherit'		=> __('Inherit'),
				],
				'value'		=> $value_transform,
				'class'		=> 'cs-typo-transform',
				'before'	=> '<label>'.__('Text Transform').'</label>',
			));

			echo cs_add_element( array(
				'pseudo'		=> true,
				'id'			=> $this->field['id'].'_color',
				'type'			=> 'color_picker',
				'name'			=> $this->element_name('[color]'),
				'attributes'	=> array(
					'data-atts'		=> 'bgcolor',
				),
				'value'			=> $value_color,
				'default'		=> ( isset( $this->field['default']['color'] ) ) ? $this->field['default']['color'] : '',
				'rgba'			=> ( isset( $this->field['rgba'] ) && $this->field['rgba'] === false ) ? false : '',
				'class'		=> 'cs-typo-color',
				'before'		=> '<label>'.__('Font Color').'</label>',
			) );


			$preview_styles = "--cs-typo-preview-weight: $variant_value; --cs-typo-preview-size: $value_size; --cs-typo-preview-size: $value_height; --cs-typo-preview-align: $value_align; --cs-typo-preview-color: $value_color";

			echo 	'<div class="cs-typo-preview" data-preview-id="cs-typo-preview_'.$this->field['id'].'_preview" id="cs-typo-preview_'.$this->field['id'].'_preview" style="'.$preview_styles.'">
						<div class="cs-typo-preview-toggle"></div>
						<p>'.$value_preview.'</p>
					</div>';

			echo '<input type="text" name="'. $this->element_name( '[font]' ) .'" class="cs-typo-font hidden" data-atts="font" value="'. $value['font'] .'" />';

		} else {

			echo __( 'Error! Can not load json file.', 'cs-framework' );

		}

		echo '</div>';

		echo $this->element_after();

	}

}
