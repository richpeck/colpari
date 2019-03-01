<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Slider
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_easing_editor extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {

		echo $this->element_before();

		$default 		= '0.400, 0.000, 0.200, 1.000';
		if (isset($this->value['easingSelectorType'])){
			$customValue 	= ($this->value['easingSelectorType'] == 'custom') ? $this->value['easingSelector'] : '0.4, 0.0, 0.5, 1.0' ;
		}

		echo '<div class="cs-easing-editor">';		

		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'select',
			'options'	=> [
				__('Defaults') => [
					'0, 0, 1, 1'					=> 'linear',
					'0.250, 0.100, 0.250, 1.000'	=> 'ease (default)',
					'0.420, 0.000, 1.000, 1.000'	=> 'ease-in',
					'0.000, 0.000, 0.580, 1.000'	=> 'ease-out',
					'0.420, 0.000, 0.580, 1.000'	=> 'ease-in-out',
				],
				__('Material Design Easing Curves') => [
					'0.400, 0.000, 0.200, 1.000'	=> 'Fast Out Slow In',
					'0.000, 0.000, 0.200, 1.000'	=> 'Linear Out Slow In',
					'0.400, 0.000, 1.000, 1.000'	=> 'Fast Out Linear In',
					'0.400, 0.000, 0.600, 1.000'	=> 'Ease-in-out',
				],
				__('Penner Equations (approximated)')	=> [
					'0.550, 0.085, 0.680, 0.530' 	=> 'easeInQuad',
					'0.550, 0.055, 0.675, 0.190' 	=> 'easeInCubic',
					'0.895, 0.030, 0.685, 0.220' 	=> 'easeInQuart',
					'0.755, 0.050, 0.855, 0.060' 	=> 'easeInQuint',
					'0.470, 0.000, 0.745, 0.715' 	=> 'easeInSine',
					'0.950, 0.050, 0.795, 0.035' 	=> 'easeInExpo',
					'0.600, 0.040, 0.980, 0.335' 	=> 'easeInCirc',
					'0.600, -0.280, 0.735, 0.045' 	=> 'easeInBack',

					'0.250, 0.460, 0.450, 0.940' 	=> 'easeOutQuad',
					'0.215, 0.610, 0.355, 1.000' 	=> 'easeOutCubic',
					'0.165, 0.840, 0.440, 1.000' 	=> 'easeOutQuart',
					'0.230, 1.000, 0.320, 1.000' 	=> 'easeOutQuint',
					'0.390, 0.575, 0.565, 1.000' 	=> 'easeOutSine',
					'0.190, 1.000, 0.220, 1.000' 	=> 'easeOutExpo',
					'0.075, 0.820, 0.165, 1.000' 	=> 'easeOutCirc',
					'0.175, 0.885, 0.320, 1.275' 	=> 'easeOutBack',

					'0.455, 0.030, 0.515, 0.955' 	=> 'easeInOutQuad',
					'0.645, 0.045, 0.355, 1.000' 	=> 'easeInOutCubic',
					'0.770, 0.000, 0.175, 1.000' 	=> 'easeInOutQuart',
					'0.860, 0.000, 0.070, 1.000' 	=> 'easeInOutQuint',
					'0.445, 0.050, 0.550, 0.950' 	=> 'easeInOutSine',
					'1.000, 0.000, 0.000, 1.000' 	=> 'easeInOutExpo',
					'0.785, 0.135, 0.150, 0.860' 	=> 'easeInOutCirc',
					'0.680, -0.550, 0.265, 1.550' 	=> 'easeInOutBack',
				],
				__('Custom Easing')	=> [
					$customValue 	=> __('Custom Easing')
				]
			],
			'class'		=> 'easingSelector',
			'value'		=> isset($this->value['easingSelector']) ? $this->value['easingSelector'] : '',
			'default'	=> $default
		) );

		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[easingSelector]'),
			'value'		=> isset($this->value['easingSelector']) ? $this->value['easingSelector'] : '',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
		) );

		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'text',
			'name'		=> $this->element_name('[easingSelectorType]'),
			'value'		=> isset($this->value['easingSelectorType']) ? $this->value['easingSelectorType'] : '',
			'class'		=> 'easingSelectorType',
			'attributes'	=> [
				'type'	=> 'hidden',
			]
		) );

		echo cs_add_element( array(
			'pseudo'	=> true,
			'type'		=> 'button',
			'name'		=> $this->element_name('[toggleEditor]'),
			'class'		=> 'cs-toggle-editor',
			'value'		=> __('Toggle Editor'),
		) );

		echo '<div class="cs-easing-editor__graph-outer-wrapper">';
		
		echo '<div class="cs-easing-editor__graph-wrapper">';
			echo '<div class="cs-easing-editor__graph">';
			echo '<a class="knob p0"></a>';
			echo '<a class="knob p1"></a>';
			echo '<a class="knob p2"></a>';
			echo '<a class="knob p3"></a>';
			echo '<canvas height="200" width="200" class="cs-easing-editor__bezierCurve" />';
			echo '</div>';
		echo '</div>';

		echo '<div class="cs-easing-editor__preview">';
		echo '<div class="cs-easing-editor__preview-box"></div>';
		echo '</div>';

		echo '<div class="cs-easing-editor__result">';
		echo '<code><span class="cubicBezier">cubic-bezier(<span class="p1X">0</span>, <span class="p1Y">0</span>, <span class="p2X">.25</span>, <span class="p2Y">1</span>)</span></code>';
		echo '</div>';

		echo '</div>';
		
		echo '</div>';

		echo $this->element_after();

	}

}