<?php

/**
 * Register Issue status taxonomy
 */
function wpas_it_register_status_taxonomy() {
		
	$args = array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Issue Statuses', 'wpas_it' ),
			'singular_name' => __( 'Status', 'wpas_it' ),
			'menu_name' => __( 'Issue Statuses', 'wpas_it' ),
			'search_items' => __( 'Search Status', 'wpas_it' ),
			'popular_items' => __( 'Statuses', 'wpas_it' ),
			'all_items' => __( 'All Statuses', 'wpas_it' ),
			'edit_item' => __( 'Edit Status', 'wpas_it' ),
			'update_item' => __( 'Update Status', 'wpas_it' ),
			'add_new_item' => __( 'Add New Status', 'wpas_it' ),
			'new_item_name' => __( 'New Status Name', 'wpas_it' ),
			'separate_items_with_commas' => __( 'Separate Statuses with commas', 'wpas_it' ),
			'add_or_remove_items' => __( 'Add or remove Statuses', 'wpas_it' ),
			'choose_from_most_used' => __( 'Choose from the most popular Statuses', 'wpas_it' ),
		),
		'show_ui'               => true,
		'meta_box_cb'           => false,
		'capabilities'			=> array(
			'manage_terms' => 'administer_awesome_support'
		)
	);
	
	register_taxonomy( 'wpas_it_status', 'wpas_issue_tracking', $args );
}
	
/**
 * Register Issue priority taxonomy
 */
function wpas_it_register_priority_taxonomy() {
		
	$args = array(
		'public' => true,
		'labels' => array(
			'name' => __( 'Priority', 'wpas_it' ),
			'singular_name' => __( 'Priority', 'wpas_it' ),
			'menu_name' => __( 'Priorities', 'wpas_it' ),
			'search_items' => __( 'Search Priorities', 'wpas_it' ),
			'popular_items' => __( 'Priorities', 'wpas_it' ),
			'all_items' => __( 'All Priorities', 'wpas_it' ),
			'edit_item' => __( 'Edit Priority', 'wpas_it' ),
			'update_item' => __( 'Update Priority', 'wpas_it' ),
			'add_new_item' => __( 'Add New Priority', 'wpas_it' ),
			'new_item_name' => __( 'New Priority Name', 'wpas_it' ),
			'separate_items_with_commas' => __( 'Separate Priorities with commas', 'wpas_it' ),
			'add_or_remove_items' => __( 'Add or remove Priorities', 'wpas_it' ),
			'choose_from_most_used' => __( 'Choose from the most popular Priorities', 'wpas_it' ),
		),
		'show_ui'               => true,
		'meta_box_cb'           => false,
		'capabilities'			=> array(
			'manage_terms' => 'administer_awesome_support'
		)
	);
	
	register_taxonomy( 'wpas_it_priority', 'wpas_issue_tracking', $args );
}	


/**
 * Register comment status taxonomy
 */
function wpas_it_register_comment_status_taxonomy() {
		
		$args = array(
			'public' => true,
			'labels' => array(
				'name' => __( 'Issue Comment Statuses', 'wpas_it' ),
				'singular_name' => __( 'Status', 'wpas_it' ),
				'menu_name' => __( 'Issue Comment Statuses', 'wpas_it' ),
				'search_items' => __( 'Search Status', 'wpas_it' ),
				'popular_items' => __( 'Statuses', 'wpas_it' ),
				'all_items' => __( 'All Statuses', 'wpas_it' ),
				'edit_item' => __( 'Edit Status', 'wpas_it' ),
				'update_item' => __( 'Update Status', 'wpas_it' ),
				'add_new_item' => __( 'Add New Status', 'wpas_it' ),
				'new_item_name' => __( 'New Status Name', 'wpas_it' ),
				'separate_items_with_commas' => __( 'Separate Statuses with commas', 'wpas_it' ),
				'add_or_remove_items' => __( 'Add or remove Statuses', 'wpas_it' ),
				'choose_from_most_used' => __( 'Choose from the most popular Statuses', 'wpas_it' ),
			),
			'show_ui'               => true,
			'show_in_menu'          => true,
			'meta_box_cb'           => false,
			'capabilities'			=> array(
				'manage_terms' => 'administer_awesome_support'
			)
		);
		
		register_taxonomy( 'wpas_it_cmt_status', 'wpas_it_comment', $args );
}



add_action( 'admin_menu', 'wpas_it_register_submenu_items',  9, 0 );
/**
 * Add comment status submenu item in issue tracking menu.
 *
 * @return void
 */
function wpas_it_register_submenu_items() {
	
	add_submenu_page( 'edit.php?post_type=wpas_issue_tracking', __( 'Comment Status', 'wpas_it' ), __( 'Comment Status', 'wpas_it' ), 'administer_awesome_support', 'edit-tags.php?taxonomy=wpas_it_cmt_status&post_type=wpas_it_comment' );
}



add_filter( 'parent_file', 'wpas_it_current_menu' );

/**
 * Highlight comment status tax submenu item
 * 
 * @global string $submenu_file
 * @global object $current_screen
 * @global string $pagenow
 * 
 * @param string $parent_file
 * 
 * @return string
 */
function wpas_it_current_menu( $parent_file ) {
	
    global $submenu_file, $current_screen, $pagenow;
 
    if ( ( $pagenow == 'edit-tags.php' || $pagenow === 'term.php') && $current_screen->post_type === 'wpas_it_comment' ) {
		$submenu_file = 'edit-tags.php?taxonomy=wpas_it_cmt_status&post_type=wpas_it_comment';
		$parent_file = 'edit.php?post_type=wpas_issue_tracking';
    }
 
	
 
	return $parent_file;
 
}
 
    
	
	

add_action( 'init', 'wpas_it_init_taxonomies' );

/**
 * Register all taxonomies
 */
function wpas_it_init_taxonomies() {
	

	wpas_it_register_status_taxonomy();
	wpas_it_register_priority_taxonomy();
	wpas_it_register_comment_status_taxonomy();
	
	$taxonomies = array( 'wpas_it_priority', 'wpas_it_status', 'wpas_it_cmt_status' );

	foreach ( $taxonomies as $tax ) {

		add_action( "{$tax}_add_form_fields",  'wpas_it_add_tax_fields' );
		add_action( "{$tax}_edit_form_fields", 'wpas_it_edit_tax_fields', 10, 2 );


		add_action( "created_{$tax}", 'wpas_it_save_tax', 10, 2 );
		add_action( "edited_{$tax}",  'wpas_it_save_tax', 10, 2 );

	}
}


/**
 * Add color picker field in status and priority taxonomies
 */
function wpas_it_tax_add_color_field(){
	
	?>

	<div class="form-field term-color-wrap">
		<label for="term-meta-color"><?php echo _e( 'Color', 'wpas_it' ); ?></label>
		<input type="text" name="term_meta_color" id="term-meta-color" value="" />
		<p class="description"><?php echo _e( 'Set Color.', 'wpas_it' ); ?></p>
	</div>

	<?php
	
}

/**
 * Add color picker field in status and priority taxonomies edit tax page
 * 
 * @param object $term
 * @param string $taxonomy
 */
function wpas_it_tax_edit_color_field( $term, $taxonomy ){
	$color = get_term_meta( $term->term_id, 'color', true );
	?>

	<tr class="form-field term-color-wrap">
		<th scope="row" valign="top">
			<label for="term-meta-color"><?php echo _e( 'Color', 'wpas_it' ); ?></label>
		</th>
		<td>
			<input type="text" name="term_meta_color" id="term-meta-color" value="<?php echo esc_attr( $color ); ?>" />
			<p class="description"><?php echo _e( 'Set Color.', 'wpas_it' ); ?></p>
		</td>
	</tr>
	<?php
}


/**
 * Add taxonomy meta fields for add new term page
 * 
 * @param string $taxonomy
 */
function wpas_it_add_tax_fields( $taxonomy ) {
	wpas_it_tax_add_color_field();
}

/**
 * Add taxonomy meta fields for edit term page
 * 
 * @param string $taxonomy
 */
function wpas_it_edit_tax_fields( $term, $taxonomy  ) {
	wpas_it_tax_edit_color_field( $term, $taxonomy );
}


/**
 * Save taxonomy meta fields
 * 
 * @param int $term_id
 * @param int $tt_id
 */
function wpas_it_save_tax( $term_id, $tt_id ) {
	
	if( isset( $_POST['term_meta_color'] ) ) {
		update_term_meta( $term_id, 'color', $_POST['term_meta_color'] );
	}
}