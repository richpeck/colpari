<?php
/**
 * Templating Functions.
 *
 * @package   Awesome Support/Documentation
 * @author    AwesomeSupport <contact@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2014-2017 AwesomeSupport
 */

/**
 * Get the post ID from the global object.
 *
 * Some functions can be given a post ID, or not. If no post ID is given
 * we try to get it from the global $post object if available.
 *
 * @since  0.1.0
 * @return integer|bool Post ID if found, false otherwise
 */
function wpas_doc_guess_the_post_id() {

	global $post;

	if ( isset( $post ) ) {
		return $post->ID;
	}

	return false;
}

/**
 * Get the parent of a post.
 *
 * @since  0.1.0
 * @param  integer          $post_id ID of the post to get the parent of
 * @param  boolean          $just_id Whether to return the parent post object or just the ID
 * @return WP_Post|integer  Parent post object or ID
 */
function wpas_doc_get_parent( $post_id = null, $just_id = false ) {

	if ( is_null( $post_id ) ) {
		$post_id = wpas_doc_guess_the_post_id();
	}

	if ( is_object( $post_id ) && isset( $post_id->post_parent ) ) {

		if ( 0 === $post_id->post_parent ) {
			return 0;
		}

		return $just_id ? $post_id->post_parent : get_post( $post_id->post_parent );
	}

	$post = get_post( $post_id );

	if ( 0 === $post->post_parent ) {
		return 0;
	}

	return $just_id ? $post->post_parent : get_post( $post->post_parent );

}

/**
 * Check if a post has a parent.
 *
 * This is basically a wrapper of wpas_doc_get_parent().
 * As this function will be called a lot we use transients to
 * temporarily save the result in order to avoir overloading
 * the database with multiple queries.
 *
 * @since  0.1.0
 * @param  integer 	$post_id ID of the post to check
 * @return bool     Whether or not the post has a parent
 */
function wpas_doc_has_parent( $post_id = null ) {

	if ( is_null( $post_id ) ) {
		$post_id = wpas_doc_guess_the_post_id();
	}

	/* Try to get the result from transient */
	$transient = get_transient( 'wpas_doc_parent_' . $post_id );

	if ( false !== $transient ) {
		return '1' === $transient ? true : false;
	}

	/* Get the parent post */
	$parent = wpas_doc_get_parent( $post_id );

	if ( 0 === $parent ) {
		set_transient( 'wpas_doc_parent_' . $post_id, '0', 12 * 60 * 60 );
		return false;
	}

	set_transient( 'wpas_doc_parent_' . $post_id, '1', 12 * 60 * 60 );

	return true;

}

/**
 * Get the children of a documentation.
 *
 * @since  0.1.0
 * @param  integer $post_id ID of the doc that has children
 * @return array   Array of child pages
 */
function wpas_doc_get_children( $post_id = null ) {

	if ( is_null( $post_id ) ) {
		$post_id = wpas_doc_guess_the_post_id();
	}

	$args = array(
		'post_parent'    => $post_id,
		'post_type'      => 'documentation',
		'posts_per_page' => -1,
		'post_status'    => 'publish'
	);

	$children = get_children( $args );

	return $children;

}

/**
 * Check if a doc has child pages.
 *
 * @since  0.1.0
 * @return bool Whether or not the documentation has children
 */
function wpas_doc_has_children( $post_id = null ) {

	if ( is_null( $post_id ) ) {
		$post_id = wpas_doc_guess_the_post_id();
	}

	$children = wpas_doc_get_children( $post_id );

	if ( empty( $children ) ) {
		return false;
	} else {
		return true;
	}

}

/**
 * Check if the current documentation is a one page doc.
 *
 * @since  0.1.0
 * @return bool   Whether or not it is a one page doc
 */
function wpas_doc_is_one_page( $post = null ) {

	if ( is_null( $post ) ) {
		global $post;
	}

	/* If it has a parent, then it's a child, hence not a one page */
	if ( is_object( $post ) && is_a( $post, 'WP_Post' ) && 0 !== $post->post_parent ) {
		return false;
	}

	/* If it has children then it's not a one page */
	if ( wpas_doc_has_children() ) {
		return false;
	}

	return true;

}

/**
 * Get the menu hierarchy.
 *
 * Get the entire hierarchical menu for a documentation.
 * As this menu will most likely be reloaded many times
 * for each visitor there is a risk to overload the database
 * as the sub-functions are using a lot of get_post().
 *
 * To avoid the overload we will cache the menus in a transient
 * for a limited amount of time.
 *
 * @since  0.1.0
 * @param  integer    $doc_id ID of the doc to get the menu for
 * @return array|bool Array containing the menu hierarchy or false in case of error
 */
function wpas_doc_get_hierarchy( $doc_id = false ) {

	if ( false === $doc_id ) {
		return false;
	}

	$doc      = get_post( $doc_id );
	$subpages = array();

	if ( 'documentation' !== $doc->post_type ) {
		return false;
	}

	/**
	 * If the current page is not the parent
	 * we go look for it.
	 */
	if ( 0 !== $doc->post_parent ) {
		do {
			$doc = wpas_doc_get_parent( $doc_id );
		} while ( 0 !== $doc->post_parent );
	}

	$cached = get_transient( 'wpas_doc_menu_' . $doc->ID );

	/* Return the cached menu if available */
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$children = wpas_doc_get_children( $doc->ID );

	if ( false !== $children ) {

		foreach ( $children as $child_id => $child ) {
			array_push( $subpages, $child_id );
		}

	}

	$hierarchy = array(
		$doc->ID => array(
			'name'  => $doc->post_title,
			'pages' => $subpages
		)
	);

	set_transient( 'wpas_doc_menu_' . $doc->ID, $hierarchy, 15 * 60 * 60 ); // Cache the menu for 15 minutes

	return $hierarchy;

}

/**
 * Locate documentation template.
 *
 * The function will locate the template and return the path
 * from the child theme, if no child theme from the theme,
 * and if no template in the theme it will load the default
 * template stored in the plugin's /templates directory.
 *
 * @since  0.1.0
 * @param  string $name  Name of the template to locate
 * @return string
 */
function wpas_doc_locate_template( $name ) {

	$filename = "$name.php";

	$template = locate_template(
		array(
			WPAS_TEMPLATE_PATH . 'documentation/' . $filename
		)
	);

	if ( ! $template )
		$template = WPAS_DOC_PATH . "templates/" . $filename;

	return apply_filters( 'wpas_doc_locate_template', $template, $name );

}

/**
 * Get documentation template.
 *
 * The function takes a template file name and loads it
 * from whatever location the template is found first.
 * The template is beeing searched for (in order) in
 * the child theme, the theme and the default templates
 * folder within the plugin.
 *
 * @since  0.1.0
 * @param  string $name  Name of the template to include
 * @param  array  $args  Pass variables to the template
 */
function wpas_doc_get_template( $name ) {

	$template = wpas_doc_locate_template( $name );

	if ( ! file_exists( $template ) )
		return false;

	$template = apply_filters( 'wpas_doc_get_template', $template, $name );

	do_action( 'wpas_doc_before_template', $name, $template );

	include( $template );

	do_action( 'wpas_doc_after_template', $name, $template );

}

/**
 * Load the documentation sidebar.
 *
 * This is essentially a wrapper for wpas_doc_get_template()
 * where the template name is pre-set to sidebar.
 *
 * @since  0.1.0
 * @return void
 */
function wpas_doc_get_sidebar() {
	wpas_doc_get_template( 'sidebar' );
}

/**
 * Get the terms with product taxonomy.
 *
 * Fetch all the records for posts with the product taxonomy.
 *
 * @since  3.0.0
 * @return array|null  Array containing the terms with product taxonomy, or null if taxonomy doesn't exist
 */
function wpas_doc_get_products() {
	$args = array(
		'taxonomy'		=> 'product',
		'hide_empty'	=> false,
		'orderby'		=> 'name',
		'order'			=> 'ASC',
	);

	if ( true === taxonomy_exists( 'product' ) ) {
		$the_terms = get_terms( $args );

		// Time to sort the terms by name - sometimes they just come back not sorted if product sync is turned on...
		$the_sorted_terms = usort( $the_terms, "obj_cmp" );

		return $the_terms;
	} else {
		return null;
	}
}

/**
 * Closure function used in wpas_doc_get_products to sort an object array by one of its fields
 *
 * @since  3.0.0
 * @return int  Returns < 0 if $a->name is less than $b->name, > 0 if $a->name is greater than $b->name, and 0 if they are equal
 */
function obj_cmp( $a, $b ) {
    return strcmp( $a->name, $b->name );
}

/**
 * Get chapters or versions by product id
 *
 * The function will query the documentation CPT
 * for post ids matching the provided $product_id
 * and then try to get posts from the $taxonomy,
 * using the $topic_ids
 *
 * @since	3.0.0
 * @param	string	$taxonomy	Slug of the taxonomy
 * @param	int		$product_id	Terms ID of the current product in the loop
 * @return	array|null	Returns the topics matching the taxonomy and product_id parameters,
 * null if none found or invalid arguments
 */
function wpas_doc_get_chapter_or_version_by_product( $taxonomy, $product_id ) {
	if ( $taxonomy != 'chapter' && $taxonomy != 'version' ) { return null; }
	if ( ! is_numeric( $product_id ) ) { return null; }

	$topic_ids = get_posts( array(
		'post_type' 		=> 'documentation',
		'fields'			=> 'ids',
		'numberposts' 		=> -1,
		'suppress_filters'	=> false,
		'tax_query'			=> 	array(
			array(
				'taxonomy' 	=> 'product',
				'field' 	=> 'id',
				'terms'		=> $product_id,
			)
		),
	));

	if( !empty( $topic_ids ) ) {
		$terms = wp_get_object_terms( $topic_ids, 'as-doc-'. $taxonomy );
	} else {
		return null;
	}

	return $terms;
}

/**
 * Get topics by version or chapter
 *
 * The function will query the documentation CPT
 * for posts matching the provided $taxonomy and
 * $product_id. The result is in ascending order
 * based on menu_order value.
 *
 * @since   3.0.0
 * @param   string	$taxonomy		Slug of the taxonomy
 * @param	int		$taxonomy_id	Term ID of the taxonomy
 * @param   int		$product_id		ID of the current product in the loop
 * @return	array|null	Returns the topics matching the provided parameters,
 * null if none found or invalid arguments
 */
function wpas_doc_get_topics_by_taxonomy( $taxonomy, $taxonomy_id, $product_id ) {
	if ( $taxonomy != 'chapter' && $taxonomy != 'version' ) { return null; }
	if ( ! is_numeric( $taxonomy_id ) ) { return null; }
	if ( ! is_numeric( $product_id ) ) { return null; }

	$topics = get_posts( array(
		'post_type'			=> 'documentation',
		'numberposts'		=> -1,
		'suppress_filters'	=> false,
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'tax_query'			=> array(
			array(
				'taxonomy'	=> 'as-doc-' . $taxonomy,
				'field'		=> 'id',
				'terms'		=> $taxonomy_id,
			),
			array(
				'taxonomy'	=> 'product',
				'field'		=> 'id',
				'terms'		=> $product_id,
			),
		),
	));

	if( empty( $topics ) ) { return null; }
	return $topics;
}

/**
 * Get topics with no version or chapter
 *
 * The function will query the documentation CPT
 * for posts that are not assigned to any existing
 * version or chapter, depending on the provided
 * $taxonomy argument
 *
 * @since   3.0.0
 * @param   string	$taxonomy		Slug of the taxonomy
 * @param   int		$product_id		ID of the current product in the loop
 * @return	array|null	Returns the topics matching the provided parameters,
 * null if none found or invalid arguments
 */
function wpas_doc_get_topics_with_no_chapters_or_versions( $taxonomy, $product_id ) {
	if ( $taxonomy != 'chapter' && $taxonomy != 'version' ) { return null; }
	if ( ! is_numeric( $product_id ) ) { return null; }

	$topics = get_posts( array (
		'post_type'			=> 'documentation',
		'numberposts'		=> -1,
		'hide_empty'		=> false,
		'suppress_filters'	=> false,
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'tax_query'			=> array(
			array(
				'taxonomy'	=> 'product',
				'field'		=> 'id',
				'terms'		=> $product_id,
			),
			array(
				'taxonomy'	=> 'as-doc-' . $taxonomy,
				'terms'		=> get_terms( 'as-doc-' . $taxonomy, [ 'fields' => 'ids'  ] ),
				'operator'	=> 'NOT IN',
			),
		),
	));

	if( empty( $topics ) ) { return null; }

	return $topics;
}

/**
 * Get documents with sections based off current document
 *
 * The function will query the documentation CPT
 * for other posts matching the provided section $id
 *
 * @since   3.0.0
 * @param   int	$id		ID of the current section
 * @return	array|null	Returns the topics matching the provided parameters,
 * null if none found or invalid arguments
 */
function wpas_doc_get_section( $id ) {
	if( ! is_numeric( $id ) ) { return null; }

	$return = get_posts( array(
		'numberposts'		=> -1, // we want to retrieve all of the posts
		'post_type'			=> 'documentation',
		'suppress_filters'	=> false, // this argument is required for CPT-onomies
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'tax_query' 		=> array(
			array(
				'taxonomy'	=> 'as-doc-section',
				'field'		=> 'id', // can be slug or id - a CPT-onomy term's ID is the same as its post ID
				'terms'		=> $id,
			)
		)
	));

	if( empty( $return ) ) { return null; }

	return $return;
}

/**
 * Get documents based on tags
 *
 * The function will query the documentation CPT
 * for other posts matching the current document's
 * tags
 *
 * @since   3.0.0
 * @param   int	$post_id	ID of the current document from the loop
 * @return	array|null	Returns the topics matching the provided parameters,
 * null if none found or invalid arguments
 */
function wpas_doc_get_tags_by_post( $post_id ) {
	if( is_numeric( $post_id ) ) { return null; }

	$the_terms = get_the_terms( $post_id , 'documentation_tag' );
	if( isset( $the_terms ) && !empty( $the_terms ) ) {
	    foreach( $the_terms as $the_term ) {
	        $the_terms_slugs[] = $the_term->slug;
	    }
	}

	if( empty( $the_terms_slugs ) ) { return null; }

	$posts = get_posts( array(
        'post_type' => 'documentation',
        'tax_query' => array(
			array(
	            'taxonomy'	=> 'documentation_tag',
	            'field'		=> 'slug',
				'terms'		=> $the_terms_slugs,
			),
		),
	));

	if( empty( $posts ) ) { return null; }

	return $posts;
}

/**
 * Get documents with sections based on categories
 *
 * The function will query the documentation CPT
 * for other posts matching the current document's
 * categories.
 *
 * @since   3.0.0
 * @param   int	$post_id	ID of the current document
 * @return	array|null	Returns the topics matching the provided parameters,
 * null if none found or invalid arguments
 */
function wpas_doc_get_categories_by_post( $post_id ) {
	if( ! is_numeric( $post_id ) ) { return null; }

	$the_terms_slugs = array();
	$the_terms = get_the_terms( $post_id , 'as-doc-category' );
	if( isset( $the_terms ) && !empty( $the_terms ) ) {
	    foreach( $the_terms as $the_term ) {
	        $the_terms_slugs[] = $the_term->slug;
	    }
	}

	if( empty( $the_terms_slugs ) ) { return null; }

	$posts = get_posts( array(
        'post_type' => 'documentation',
        'tax_query' => array(
			array(
	            'taxonomy'	=> 'as-doc-category',
	            'field'		=> 'slug',
				'terms'		=> $the_terms_slugs,
			),
		),
	));

	if( empty( $posts ) ) { return null; }

	return $posts;
}

/**
 * Get the customization options from the Titan Framework
 *
 * The function will fetch all of the registered
 * by the Titan Framework customization options
 * and set their default values matching ansible
 * color scheme, in case the users assigned an empty
 * option
 *
 * @since   3.0.0
 * @return	array	Returns an array map with the customization options
 */
function wpas_doc_get_customization_options( ) {
	$titan = TitanFramework::getInstance( 'asdoc' );
	$customization = array (
			'versions'					=> $titan->getOption( 'asdoc-customization-show-versions' ),
	        'name'						=> $titan->getOption( 'asdoc-customization-name'),
	        'title-link'				=> $titan->getOption( 'asdoc-customization-title-link'),
	        'logo'						=> wp_get_attachment_image_src( $titan->getOption( 'asdoc-customization-logo') ),
	        'sidebar-color'				=> $titan->getOption( 'asdoc-customization-sidebar-color' ),
	        'topbar-color'				=> $titan->getOption( 'asdoc-customization-topbar-color' ),
			'product-bg-color'			=> $titan->getOption( 'asdoc-customization-product-bg-color'),
			'product-text-color'		=> $titan->getOption( 'asdoc-customization-product-text-color'),
	        'chapter-bg-color'			=> $titan->getOption( 'asdoc-customization-chapter-bg-color' ),
	        'chapter-text-color'		=> $titan->getOption( 'asdoc-customization-chapter-text-color' ),
	        'version-bg-color'			=> $titan->getOption( 'asdoc-customization-version-bg-color' ),
	        'version-text-color'		=> $titan->getOption( 'asdoc-customization-version-text-color' ),
	        'topic-bg-color'			=> $titan->getOption( 'asdoc-customization-topic-bg-color' ),
	        'topic-text-color'			=> $titan->getOption( 'asdoc-customization-topic-text-color' ),
	        'menu-active-color'			=> $titan->getOption( 'asdoc-customization-menu-active-color' ),
	        'top-menu-font'				=> $titan->getOption( 'asdoc-customization-top-menu-font' ),
	        'copyright'					=> $titan->getOption( 'asdoc-customization-copyright' ),
			'product-font'				=> $titan->getOption( 'asdoc-customization-product-font' ),
			'chapter-font'				=> $titan->getOption( 'asdoc-customization-chapter-font' ),
			'version-font'				=> $titan->getOption( 'asdoc-customization-version-font' ),
			'topic-font'				=> $titan->getOption( 'asdoc-customization-topic-font' ),
	);

	/* Checking if the user has set customization options, if not - use ansible default */
	$customization['versions'] = $customization['versions'] ? $customization['versions'] : false;
	$customization['name'] = $customization['name'] ? $customization['name'] : 'WPAS Documentation';
	$customization['title-link'] = $customization['title-link'] ? esc_url( $customization['title-link'] ) : '#';
	$customization['logo'] = $customization['logo'] ? $customization['logo'] : ''; //Add a default logo
	$customization['sidebar-color'] = $customization['sidebar-color'] ? $customization['sidebar-color'] : '#343131';
	$customization['topbar-color'] = $customization['topbar-color'] ? $customization['topbar-color'] : '#000000';
	$customization['product-bg-color'] = $customization['product-bg-color'] ? $customization['product-bg-color'] : '#5bbdbf';
	$customization['product-text-color'] = $customization['product-text-color'] ? $customization['product-text-color'] : '#ffffff';
	$customization['chapter-bg-color'] = $customization['chapter-bg-color'] ? $customization['chapter-bg-color'] : '#343131';
	$customization['chapter-text-color'] = $customization['chapter-text-color'] ? $customization['chapter-text-color'] : '#b3b3b3';
	$customization['version-bg-color'] = $customization['version-bg-color'] ? $customization['version-bg-color'] : '#fcfcfc';
	$customization['version-text-color'] = $customization['version-text-color'] ? $customization['version-text-color'] : '#404040';
	$customization['topic-bg-color'] = $customization['topic-bg-color'] ? $customization['topic-bg-color'] : '#d6d6d6';
	$customization['topic-text-color'] = $customization['topic-text-color'] ? $customization['topic-text-color'] : 'gray';
	$customization['menu-active-color'] = $customization['menu-active-color'] ? $customization['menu-active-color'] : '';
	$customization['copyright'] = $customization['copyright'] ? $customization['copyright'] : '';

	return $customization;
}
