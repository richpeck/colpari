<?php

/*---
- This files contains shortcodes used by Public Tickets Plugin --
- */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function pbtk_shortcode_function( $atts, $content = null ) {

	global $post;

	$currentPost = get_permalink( $post->ID );

	extract(shortcode_atts(array(
	  'type'	 	=> 'public',
      'tag' 	 	=> '',
      'product'  	=> '',
      'display'  	=> 'list',
	  'status'   	=> 'all',
	  'search'   	=> 'no',
	  'show_filter' => 'yes'
	), $atts));

	$post_type = 'ticket';

	$posts_per_page = wpas_get_option( 'pbtk_tickets_per_page', 12 );
	

	if( $tag != "" && $product != ""){
		$tax_query = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product',
				'field'    => 'slug',
				'terms'    => $product,
			),
			array(
				'taxonomy' => 'ticket-tag',
				'field'    => 'slug',
				'terms'    => $tag,
			),
		);
	} elseif(( $tag != "" && $product == "" ) || ($tag == "" && $product != "")) {

		$tax_query = array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'product',
				'field'    => 'slug',
				'terms'    => $product,
			),
			array(
				'taxonomy' => 'ticket-tag',
				'field'    => 'slug',
				'terms'    => $tag,
			),
		);
	} else {
		$tax_query="";
	}
	
	if( $status == 'all'){
		$status_meta = array(
					'relation' =>'OR',
					array(
					'key'     => '_wpas_status',
					'value'   => 'open',
					'compare' => '='
					),
					array(
					'key'     => '_wpas_status',
					'value'   => 'closed',
					'compare' => '='
					),
				);
	}else{
		$status_meta =array(
				'key'     => '_wpas_status',
				'value'   => $status,
				'compare' => '='
				);
	} 
	if( $type != "" ){
		if( $type == 'private' ){
			$meta_query = array(
			'relation' =>'AND',
				array(
					'relation' =>'OR',
					array(
						'key' 		=> '_wpas_pbtk_flag',
						'value' 	=> $type,
						'compare' 	=> '='
					),

					array(
						'key'	  => '_wpas_pbtk_flag',
						'compare' => 'NOT EXISTS'
					),
					$status_meta,
				),				
			);
		}else {
			$meta_query = array(
				'relation' =>'AND',
				array(
				'key'	  => '_wpas_pbtk_flag',
				'value'   => $type,
				'compare' => '='
				),
				$status_meta,
			);
		}
	} else {
		$meta_query="";
	}

	$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
	$search_title = '';
	if( isset($_GET['cs_s']) ){
		$search_title = sanitize_text_field($_GET['cs_s']);
	}

	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $posts_per_page,
		'meta_query'     => $meta_query,
		'tax_query'      => $tax_query,
		's'              => $search_title,
		'paged'          => $paged
	);

	$tickets = new WP_Query( $args );
	$searchForm = '';
	if($search == 'yes'){
	$searchForm .= "<div class='custom-search'>
					<form role='search' method='get' class='pbtk-search-form' action='".esc_attr($currentPost)."'>
						<input class='aspbtk-search-field' placeholder='Search …' value='".esc_attr($search_title)."' name='cs_s' type='search'>						
						<input class='aspbtk-submit' value='' type='submit'>
						<input type='button' value='Reset' class='pbtk_reset'>
						<input value='' name='listtype' id='listtype' type='hidden'>
					</form>
				</div>";
	}

    if ($tickets->have_posts()):
		$content .= "<div class='pbtk_main_ticket_container'>";
		if($show_filter == 'yes'){
		$content .= "<div class='pbtk_top_ticket_area_left'><ul id='pbtk_ticket_filter' class='pbtk_ticket_filter'>";
		$content .= "<li><a class='ticket_list' href='javascript:void(0);'><i class='fa fa-th-list' aria-hidden='true'></i>List</a></li>";
		$content .= "<li><a class='ticket_grid' href='javascript:void(0);'><i class='fa fa-th-large' aria-hidden='true'></i>Grid</a></li>";
		$content .= "<li><a class='ticket_accordion' href='javascript:void(0);'>Accordion</a></li>";
		$content .= "</ul></div>";
		}
		$content .= "<div class='pbtk_top_ticket_area_right'>";
		$content .= $searchForm ;
		$content .= "</div>";
		$list_type = '';
		if( isset($_GET['listtype']) ){
			$list_type = sanitize_text_field($_GET['listtype'])."_view";
		} else {
			$list_type = "ticket_".$display."_view";
		}

		if($list_type == 'ticket_accordion_view'){
			$style1 = "display:none";
			$style2 = "display:block";
		} else {
			if($list_type == 'ticket_list_view' || $list_type == 'ticket_grid_view') {
				$style1 = "display:block";
				$style2 = "display:none";
			} else {
				if($display == 'accordion'){
					$style1 = "display:none";
					$style2 = "display:block";
				}else{
					$style1 = "display:block";
					$style2 = "display:none";
				}
			}
		}
		$content .= "<ul class='pbtk_ticket_list ".esc_attr($list_type)."' style='".esc_attr($style1)."'>\n";
		while ( $tickets->have_posts() ) : $tickets->the_post();
						
			$content .= "<li class='ticket_".get_the_ID()."'><a href='".get_the_permalink()."'>".get_the_title()."</a>";
			$content .= "<p class='detail'>".wp_trim_words(get_the_content(),$num_words = 20, $more = null)."</p>";
			$content .= "</li>\n";

		endwhile;

		$content .= "</ul>\n";
		$content .= "<div class='accordion_view' id='accordion' style='".esc_attr($style2)."'>";

			while ( $tickets->have_posts() ) : $tickets->the_post();				

				$content .= "<h3>".get_the_title()."</h3>";
				$content .= "<div>";
				$content .= "<p class='detail'>".wp_trim_words(get_the_content(),$num_words = 20, $more = null)."</p>";
				$content .= "</div>";

			endwhile;

		$content .= "</div>";
		if(function_exists("wpas_custom_pagination")){
			$content .= wpas_custom_pagination($tickets->max_num_pages);
		}
		$content .= "</div>\n";

		

		$content .= wp_reset_postdata();

		return $content;
	else:
		return "No tickets found.";
	endif;
}
add_shortcode('pbtk_tickets', 'pbtk_shortcode_function');