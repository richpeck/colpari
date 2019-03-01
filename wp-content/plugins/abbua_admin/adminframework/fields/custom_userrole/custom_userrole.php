<?php if ( !defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Custom Field for User Roles
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_custom_userrole extends CSFramework_Options {

	public function __construct( $field, $value = '', $unique = '' ) {
		parent::__construct( $field, $value, $unique );
	}

	public function output() {
		global $wp_roles;
		$roles 			= $wp_roles->get_names();
		$defaults_value;
		foreach($roles as $key => $value){
			$defaults_value[$key] = '';
			$defaults_value[$key .'_status'] = false;
		}
		$value 			= wp_parse_args( $this->element_value(), $defaults_value );

		echo $this->element_before();
		echo '<div class="cs-custom-userrole cs-multifield">';
		
		foreach($roles as $key => $role){
			echo '<div class="cs-custom-userrole-content">';
			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'      => 'switcher',
				'name'		=> $this->element_name('['.$key.'_status]'),
				'value'		=> $value[$key.'_status'],
				// 'title'     => __('Custom Login Redirect','abbua_admin'),
				'label'     => sprintf(__('Redirect %s users to:','abbua_admin'), $role),
				'labels'    => array(
					'on'    => __('Yes','abbua_admin'),
					'off'   => __('No','abbua_admin'),
				),
			));

			echo cs_add_element( array(
				'pseudo'	=> true,
				'type'		=> 'text',
				'name'		=> $this->element_name('['.$key.']'),
				'value'		=> $value[$key],
				'attributes' => [
					'placeholder' => $role
				],
				'after'		=> __('<p class="cs-text-muted">Leave blank to use the default url.</p>','abbua_admin'),
			));
			echo '</div>';
		}





		echo '</div>';
		echo $this->element_after();
	}

}