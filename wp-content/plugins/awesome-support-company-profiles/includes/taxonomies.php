<?php

/**
 * Register division taxonomy
 */
function wpas_cp_register_division_taxonomy() {
		
	$args = array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Division', 'wpas_cp' ),
			'singular_name' => __( 'Division', 'wpas_cp' ),
			'menu_name' => __( 'Divisions', 'wpas_cp' ),
			'search_items' => __( 'Search Divisions', 'wpas_cp' ),
			'popular_items' => __( 'Divisions', 'wpas_cp' ),
			'all_items' => __( 'All Divisions', 'wpas_cp' ),
			'edit_item' => __( 'Edit Division', 'wpas_cp' ),
			'update_item' => __( 'Update Division', 'wpas_cp' ),
			'add_new_item' => __( 'Add New Division', 'wpas_cp' ),
			'new_item_name' => __( 'New Division Name', 'wpas_cp' ),
			'separate_items_with_commas' => __( 'Separate Divisions with commas', 'wpas_cp' ),
			'add_or_remove_items' => __( 'Add or remove Divisions', 'wpas_cp' ),
			'choose_from_most_used' => __( 'Choose from the most popular Divisions', 'wpas_cp' ),
		),
		'show_ui'               => true,
		'meta_box_cb'           => false
	);
	
	register_taxonomy( 'wpas_cp_su_division', 'wpas_cp_support_user', $args );
}

/**
 * Register reporting group taxonomy
 */
function wpas_cp_register_reporting_group_taxonomy() {
		
	$args = array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Reporting Group', 'wpas_cp' ),
			'singular_name' => __( 'Reporting Group', 'wpas_cp' ),
			'menu_name' => __( 'Reporting Groups', 'wpas_cp' ),
			'search_items' => __( 'Search Reporting Groups', 'wpas_cp' ),
			'popular_items' => __( 'Reporting Groups', 'wpas_cp' ),
			'all_items' => __( 'All Reporting Groups', 'wpas_cp' ),
			'edit_item' => __( 'Edit Reporting Group', 'wpas_cp' ),
			'update_item' => __( 'Update Reporting Group', 'wpas_cp' ),
			'add_new_item' => __( 'Add New Reporting Group', 'wpas_cp' ),
			'new_item_name' => __( 'New Reporting Group Name', 'wpas_cp' ),
			'separate_items_with_commas' => __( 'Separate Reporting Groups with commas', 'wpas_cp' ),
			'add_or_remove_items' => __( 'Add or remove Reporting Groups', 'wpas_cp' ),
			'choose_from_most_used' => __( 'Choose from the most popular Reporting Groups', 'wpas_cp' ),
		),
		'show_ui'               => true,
		'meta_box_cb'           => false,
		'capabilities'			=> array(
			'manage_terms' => 'administer_awesome_support'
		)
	);
	
	register_taxonomy( 'wpas_cp_su_reporting_group', 'wpas_cp_support_user', $args );
}	




add_action( 'admin_menu', 'wpas_cp_register_submenu_items',  9, 0 );
/**
 * Add comment status submenu item in issue tracking menu.
 *
 * @return void
 */
function wpas_cp_register_submenu_items() {
	
	add_submenu_page( 'edit.php?post_type=wpas_company_profile', __( 'Divisions', 'wpas_cp' ), __( 'Divisions', 'wpas_cp' ), 'ticket_manage_company_profiles', 'edit-tags.php?taxonomy=wpas_cp_su_division' );
	add_submenu_page( 'edit.php?post_type=wpas_company_profile', __( 'Reporting Groups', 'wpas_cp' ), __( 'Reporting Groups', 'wpas_cp' ), 'ticket_manage_company_profiles', 'edit-tags.php?taxonomy=wpas_cp_su_reporting_group' );
}



add_filter( 'parent_file', 'wpas_cp_current_menu' );

/**
 * Highlight company profile submenu items
 * 
 * @global string $submenu_file
 * @global object $current_screen
 * @global string $pagenow
 * 
 * @param string $parent_file
 * 
 * @return string
 */
function wpas_cp_current_menu( $parent_file ) {
	
    global $current_screen, $pagenow;
	
	$taxonomies = array( 'wpas_cp_su_division', 'wpas_cp_su_reporting_group' );
	
	
    if ( ( $pagenow == 'edit-tags.php' || $pagenow === 'term.php' ) && in_array( $current_screen->taxonomy, $taxonomies ) ) {
		$parent_file = 'edit.php?post_type=wpas_company_profile';
    }
	
	return $parent_file;
}
 
    
	
	

add_action( 'init', 'wpas_cp_init_taxonomies' );

/**
 * Register all taxonomies
 */
function wpas_cp_init_taxonomies() {
	

	wpas_cp_register_division_taxonomy();
	wpas_cp_register_reporting_group_taxonomy();
	
}