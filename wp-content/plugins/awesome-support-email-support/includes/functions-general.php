<?php
/**
 * Generate html markup for drop-downs that pull data from taxonomies
 *
 * Example use: echo show_dropdown( 'department', "html_inboxrules_rule_new_dept", "wpas-multi-inbox-config-item wpas-multi-inbox-config-item-select", $new_dept );
 *
 * Note: We should move this to CORE AS later!

 * @since 5.0.0
 *
 * @param string    $taxonomy       The taxonomy to be used as the dropdown passed as a string parameter
 * @param string    $field_id       The html id name to be used in the generated markup - passed as a string
 * @param string    $class          The HTML class string to wrap around the dropdown - passed as a string
 * @param string    $selected       Returns the item that was selected by the user.  If this has an initial value the selected value in the dropdown will be set to that item.
 * @param bool      $showcount      A flag to control whether or not to show the taxonomy count in parens next to each item in the dropdown.
 *
 * @return string
 */
function wpas_es_show_taxonomy_terms_dropdown( $taxonomy, $field_id, $class, $selected, $showcount = false ) {
	$categories = get_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );

	$select = "<select name='$field_id' id='$field_id' class='$class'>";
	$select .= "<option value='-1'>Select</option>";

	foreach( $categories as $category ) {
		$is_selected = (int)$selected === $category->term_id ? ' selected ' : '';
		
		$countstr='';
		if ( true === $showcount ) {
			$countstr = " (" . $category->count . ") ";
		}
		
		$select .= "<option value='" . $category->term_id . "' " . $is_selected . "' >" . $category->name . $countstr . "</option>";
	}
	$select .= "</select>";

	return $select;
}


/**
 * Generate html markup for a standard html agent dropdown
 *
 * @since 5.0.0
 *
 * @param string    $field_id       The html id name to be used in the generated markup - passed as a string
 * @param string    $class          The HTML class string to wrap around the dropdown - passed as a string
 * @param string	$new_assignee	Returns the item that was selected by the user.  If this has an initial value the selected value in the dropdown will be set to that item.
 *
 * Note: We should move this to CORE AS later!
 */
function wpas_es_show_assignee_dropdown_simple( $field_id, $class, $new_assignee = "" ) {

	$args = array(
		'name' => $field_id,
		'id' => $field_id,
		'class' => $class,
		'exclude' => array(),
		'selected' => empty($new_assignee) ? false : $new_assignee,
		'cap' => 'edit_ticket',
		'cap_exclude' => '',
		'agent_fallback' => false,
		'please_select' => 'Select',
		'select2' => false,
		'disabled' => false,
		'data_attr' => array()
	);

	echo wpas_users_dropdown( $args );
}