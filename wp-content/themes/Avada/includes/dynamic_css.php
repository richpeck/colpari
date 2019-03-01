<?php
/**
 * Dynamic-css.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Format of the $css array:
 * $css['media-query']['element']['property'] = value
 *
 * If no media query is required then set it to 'global'
 *
 * If we want to add multiple values for the same property then we have to make it an array like this:
 * $css[media-query][element]['property'][] = value1
 * $css[media-query][element]['property'][] = value2
 *
 * Multiple values defined as an array above will be parsed separately.
 *
 * @param array $original_css The existing CSS.
 */
function avada_dynamic_css_array( $original_css = array() ) {
	global $wp_version, $avada_dynamic_css_array_added;

	if ( true === $avada_dynamic_css_array_added ) {
		return $original_css;
	}

	$css = array();

	$c_page_id = Avada()->fusion_library->get_page_id();

	$fusion_taxonomy_options = get_term_meta( intval( $c_page_id ), 'fusion_taxonomy_options', true );

	$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
	$dynamic_css_helpers = $dynamic_css->get_helpers();

	/**
	 * An array of all the elements that will be targeted from the body typography settings
	 */
	$body_typography_elements = apply_filters( 'avada_body_typography_elements', avada_get_body_typography_elements() );

	/**
	 * An array of all the elements that will be targeted from the nav typography settings
	 */
	$nav_typography_elements = array(
		'.side-nav li a',
		'.fusion-main-menu > ul > li > a',
		'.fusion-vertical-menu-widget ul.menu li a',
	);
	$nav_typography_elements = $dynamic_css_helpers->implode( $nav_typography_elements );

	/**
	 * An array of all the elements that will be targeter from the h1_typography settings
	 */
	$h1_typography_elements = apply_filters( 'avada_h1_typography_elements', avada_get_h1_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the h2_typography settings
	 */
	$h2_typography_elements = apply_filters( 'avada_h2_typography_elements', avada_get_h2_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the h3_typography settings
	 */
	$h3_typography_elements = apply_filters( 'avada_h3_typography_elements', avada_get_h3_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the h4_typography settings
	 */
	$h4_typography_elements = apply_filters( 'avada_h4_typography_elements', avada_get_h4_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the h5_typography settings
	 */
	$h5_typography_elements = apply_filters( 'avada_h5_typography_elements', avada_get_h5_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the h6_typography settings
	 */
	$h6_typography_elements = apply_filters( 'avada_h6_typography_elements', avada_get_h6_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the post title typography settings
	 */
	$post_title_typography_elements = apply_filters( 'avada_post_title_typography_elements', avada_get_post_title_typography_elements() );

	/**
	 * An array of all the elements that will be targeter from the post title typography settings
	 */
	$post_title_extras_typography_elements = apply_filters( 'avada_post_title_extras_typography_elements', avada_get_post_title_extras_typography_elements() );

	$page_title_bar_font_size = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_font_size' ) );

	$footer_headings_typography_elements = array(
		'.fusion-footer-widget-area h3',
		'.fusion-footer-widget-area .widget-title',
		'#slidingbar-area h3',
		'#slidingbar-area .widget-title',
		'.fusion-privacy-bar-full .column-title',
	);
	$footer_headings_typography_elements = $dynamic_css_helpers->implode( $footer_headings_typography_elements );

	// Set the correct paddings and negative margins for the "100% Width Left/Right Padding" option.
	$hundredplr_padding       = Fusion_Sanitize::size( fusion_get_option( 'hundredp_padding', 'hundredp_padding', $c_page_id ) );
	$hundredplr_padding_value = Fusion_Sanitize::number( $hundredplr_padding );
	$hundredplr_padding_unit  = Fusion_Sanitize::get_unit( $hundredplr_padding );

	$hundredplr_padding_negative_margin = '-' . $hundredplr_padding_value . $hundredplr_padding_unit;

	if ( '%' === $hundredplr_padding_unit ) {
		$fullwidth_max_width                = 100 - 2 * $hundredplr_padding_value;
		$hundredplr_padding_negative_margin = '-' . $hundredplr_padding_value / $fullwidth_max_width * 100 . $hundredplr_padding_unit;
	}

	$link_color_elements = array(
		'body a',
		'body a:before',
		'body a:after',
		'.single-navigation a[rel="prev"]:before',
		'.single-navigation a[rel="next"]:after',
		'.project-content .project-info .project-info-box a',
		'.fusion-content-widget-area .widget li a',
		'.fusion-content-widget-area .widget .recentcomments a',
		'.fusion-content-widget-area .widget_categories li',
		'#main .post h2 a',
		'.about-author .title a',
		'.shop_attributes tr th',
		'.fusion-rollover a',
		'.fusion-load-more-button',
		'.pagination .pagination-prev:before',
		'.pagination .pagination-next:after',
		'.fusion-hide-pagination-text .pagination-prev:before',
		'.fusion-hide-pagination-text .pagination-next:after',
	);
	if ( class_exists( 'bbPress' ) ) {
		$link_color_elements[] = '.bbp-forum-header a.bbp-forum-permalink';
		$link_color_elements[] = '.bbp-topic-header a.bbp-topic-permalink';
		$link_color_elements[] = '.bbp-reply-header a.bbp-reply-permalink';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$link_color_elements[] = '.fusion-woo-featured-products-slider .price .amount';
		$link_color_elements[] = '#main .product .product_title';
		$link_color_elements[] = '.widget_layered_nav li.chosen a';
		$link_color_elements[] = '.widget_layered_nav li.chosen a:before';
		$link_color_elements[] = '.widget_layered_nav_filters li.chosen a';
		$link_color_elements[] = '.widget_layered_nav_filters li.chosen a:before';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$link_color_elements[] = '#tribe-events .tribe-events-list .tribe-event-featured a';
		$link_color_elements[] = '#tribe-events-content .tribe-events-sub-nav li a';
		$link_color_elements[] = '.tribe-event-featured .recurringinfo .event-is-recurring';
		$link_color_elements[] = '.event-is-recurring';
		$link_color_elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__registration__back__to__cart';

	}
	$link_color_elements = $dynamic_css_helpers->implode( $link_color_elements );

	// Is the site width a percent value?
	$site_width_percent = ( false !== strpos( Avada()->settings->get( 'site_width' ), '%' ) ) ? true : false;

	$theme_info = wp_get_theme();
	if ( $theme_info->parent_theme ) {
		$template_dir = basename( Avada::$template_dir_path );
		$theme_info   = wp_get_theme( $template_dir );
	}

	$css['global'][ '.' . $theme_info->get( 'Name' ) . '_' . str_replace( '.', '', $theme_info->get( 'Version' ) ) ]['color'] = 'green';

	$side_header_width = ( 'Top' === Avada()->settings->get( 'header_position' ) ) ? 0 : intval( Avada()->settings->get( 'side_header_width' ) );

	if ( version_compare( $wp_version, '4.3.1', '<=' ) ) {
		// Tweak the comment-form CSS for WordPress versions < 4.4.
		$css['global']['#comment-input']['margin-bottom'] = '13px';
	}

	if ( class_exists( 'WooCommerce' ) ) {

		// For WooCommerce 2.6+ my account page this is in the content min media query.
		if ( 0 !== Fusion_Color::new_color( Avada()->settings->get( 'timeline_bg_color' ) )->alpha ) {
			$css['global']['.products .product-list-view']['padding-left']  = '20px';
			$css['global']['.products .product-list-view']['padding-right'] = '20px';
		}

		$css['global']['.fusion-woo-product-design-clean .products .fusion-rollover .star-rating span:before, .fusion-woo-product-design-clean .products .fusion-rollover .star-rating:before']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_icon_color' ) );
		$css['global']['.fusion-woo-product-design-clean .products .fusion-rollover-content .fusion-product-buttons, .fusion-woo-slider .fusion-product-buttons']['color']                               = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_text_color' ) );
		$css['global']['.fusion-woo-product-design-clean .products .fusion-rollover-content .fusion-product-buttons a, .fusion-woo-slider .fusion-product-buttons a']['color']                           = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_text_color' ) );
		$css['global']['.fusion-woo-product-design-clean .products .fusion-rollover-content .fusion-rollover-linebreak, .fusion-woo-slider .fusion-product-buttons .fusion-rollover-linebreak']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_text_color' ) );

		// Set single product gallery width.
		if ( is_product() ) {
			$post_image = get_the_post_thumbnail( get_the_ID(), apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );

			if ( $post_image ) {
				$css['global']['.product .images']['width']                      = Fusion_Sanitize::size( Avada()->settings->get( 'woocommerce_single_gallery_size' ) );
				$css['global']['.product .summary.entry-summary']['margin-left'] = 'calc( ' . $css['global']['.product .images']['width'] . ' + 30px)';

				if ( is_rtl() ) {
					$css['global']['.rtl .product .summary.entry-summary']['margin-right'] = 'calc( ' . $css['global']['.product .images']['width'] . ' + 30px)';
				}
			}
		}
		$elements = array(
			'.product-grid-view .fusion-product-content',
			'.product-category h2',
			'.related.products .fusion-product-content',
			'.up-sells .fusion-product-content',
			'.cross-sells .fusion-product-content',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'woocommerce_product_box_content_padding', 'top' ) ) . ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'woocommerce_product_box_content_padding', 'right' ) ) . ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'woocommerce_product_box_content_padding', 'bottom' ) ) . ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'woocommerce_product_box_content_padding', 'left' ) );

		$css['global']['.product .product-buttons .fusion-content-sep']['margin-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'woocommerce_product_box_content_padding', 'bottom' ) );
	}

	$elements = array(
		'html',
		'body',
		'html body.custom-background',
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce-tabs > .tabs .active a';
		$elements[] = '.woocommerce-MyAccount-navigation > ul .is-active a';
		$elements[] = '.woocommerce-checkout-nav .is-active a';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'content_bg_color' ) );

	if ( 'Boxed' === Avada()->settings->get( 'layout' ) || 'boxed' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'bg_color' ) );
	}

	if ( ! $site_width_percent ) {

		$elements = array(
			'#main',
			'.fusion-secondary-header',
			'.sticky-header .sticky-shadow',
			'.tfs-slider .slide-content-container',
			'.header-v4 #small-nav',
			'.header-v5 #small-nav',
			'.fusion-footer-copyright-area',
			'.fusion-footer-widget-area',
			'.fusion-sliding-bar-position-top .fusion-sliding-bar',
			'.fusion-sliding-bar-position-bottom .fusion-sliding-bar',
			'.fusion-page-title-bar',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left']  = '30px';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = '30px';
	}

	$css['global']['.width-100 .fusion-fullwidth']['margin-left']  = $hundredplr_padding_negative_margin;
	$css['global']['.width-100 .fusion-fullwidth']['margin-right'] = $hundredplr_padding_negative_margin;

	$css['global']['a:hover']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$elements = array(
		'.project-content .project-info .project-info-box a:hover',
		'#main .post h2 a:hover',
		'#main .about-author .title a:hover',
		'.fusion-footer-widget-area a:hover',
		'.fusion-copyright-notice a:hover',
		'.fusion-content-widget-area .widget_categories li a:hover',
		'.fusion-content-widget-area .widget li a:hover',
		'.fusion-date-and-formats .fusion-format-box i',
		'h5.toggle:hover a',
		'.content-box-percentage',
		'.more a:hover:after',
		'.fusion-read-more:hover:after',
		'.pagination-prev:hover:before',
		'.pagination-next:hover:after',
		'.single-navigation a[rel=prev]:hover:before',
		'.single-navigation a[rel=next]:hover:after',
		'.fusion-content-widget-area .widget li a:hover:before',
		'.fusion-content-widget-area .widget_nav_menu li a:hover:before',
		'.fusion-content-widget-area .widget_categories li a:hover:before',
		'.fusion-content-widget-area .widget .recentcomments:hover:before',
		'.fusion-content-widget-area .widget_recent_entries li a:hover:before',
		'.fusion-content-widget-area .widget_archive li a:hover:before',
		'.fusion-content-widget-area .widget_pages li a:hover:before',
		'.fusion-content-widget-area .widget_links li a:hover:before',
		'.side-nav .arrow:hover:after',
		'#wrapper .jtwt .jtwt_tweet a:hover',
		'.star-rating:before',
		'.star-rating span:before',
		'#wrapper .fusion-widget-area .current_page_item > a',
		'#wrapper .fusion-widget-area .current-menu-item > a',
		'#wrapper .fusion-vertical-menu-widget .menu li.current_page_ancestor > a',
		'#wrapper .fusion-vertical-menu-widget .menu li.current-menu-item > a',
		'#wrapper .fusion-widget-area .current_page_item > a:before',
		'#wrapper .fusion-widget-area .current-menu-item > a:before',
		'.side-nav ul > li.current_page_item > a',
		'.side-nav li.current_page_ancestor > a',
		'.price ins .amount',
		'.price > .amount',
	);
	if ( is_rtl() ) {
		$elements[] = '.rtl .more a:hover:before';
		$elements[] = '.rtl .fusion-read-more:hover:before';
	}
	if ( class_exists( 'GFForms' ) ) {
		$elements[] = '.gform_wrapper span.ginput_total';
		$elements[] = '.gform_wrapper span.ginput_product_price';
		$elements[] = '.ginput_shipping_price';
	}
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '.bbp-pagination .bbp-pagination-links .pagination-prev:hover:before';
		$elements[] = '.bbp-pagination .bbp-pagination-links .pagination-next:hover:after';
		$elements[] = '.bbp-topics-front ul.super-sticky a:hover';
		$elements[] = '.bbp-topics ul.super-sticky a:hover';
		$elements[] = '.bbp-topics ul.sticky a:hover';
		$elements[] = '.bbp-forum-content ul.sticky a:hover';

	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce .address .edit:hover:after';
		$elements[] = '.woocommerce-tabs .tabs a:hover .arrow:after';
		$elements[] = '.woocommerce-pagination .prev:hover';
		$elements[] = '.woocommerce-pagination .next:hover';
		$elements[] = '.woocommerce-pagination .prev:hover:before';
		$elements[] = '.woocommerce-pagination .next:hover:after';
		$elements[] = '.woocommerce-tabs .tabs li.active a';
		$elements[] = '.woocommerce-tabs .tabs li.active a .arrow:after';
		$elements[] = '.woocommerce-side-nav li.is-active a';
		$elements[] = '.woocommerce-side-nav li.is-active a:after';
		$elements[] = '.woocommerce-Pagination .woocommerce-Button:hover:before';
		$elements[] = '.woocommerce-Pagination .woocommerce-Button:hover:after';
		$elements[] = '.woocommerce-MyAccount-navigation ul li.is-active a';
		$elements[] = '.woocommerce-MyAccount-navigation ul li.is-active a:after';
		$elements[] = '.woocommerce-MyAccount-content .woocommerce-Addresses .edit:hover';
		$elements[] = '.woocommerce-MyAccount-content .woocommerce-Addresses .edit:hover:after';
		$elements[] = '.woocommerce-MyAccount-downloads .download-actions a:hover';
		$elements[] = '.woocommerce-MyAccount-downloads .download-actions a:hover:after';
		$elements[] = '.my_account_orders .woocommerce-orders-table__cell-order-actions a:hover:after';
		$elements[] = '.avada-order-details .shop_table.order_details tfoot tr:last-child .amount';
		$elements[] = '#wrapper .cart-checkout a:hover';
		$elements[] = '#wrapper .cart-checkout a:hover:before';
		$elements[] = '.widget_shopping_cart_content .total .amount';
		$elements[] = '.widget_layered_nav li a:hover:before';
		$elements[] = '.widget_product_categories li a:hover:before';
		$elements[] = '.my_account_orders .woocommerce-orders-table__cell-order-number a';
		$elements[] = '.shop_table .product-subtotal .amount';
		$elements[] = '.cart_totals .order-total .amount';
		$elements[] = '.checkout .shop_table tfoot .order-total .amount';
		$elements[] = '#final-order-details .mini-order-details tr:last-child .amount';
		$elements[] = '.fusion-carousel-title-below-image .fusion-carousel-meta .price .amount';
		$elements[] = '.widget_shopping_cart_content a:hover:before';
		$elements[] = '#wrapper .product-category a:hover h2.woocommerce-loop-category__title';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '.event-is-recurring:hover';
		$elements[] = '.tribe-event-featured .recurringinfo .event-is-recurring:hover';
		$elements[] = '#tribe-events .tribe-events-list .tribe-event-featured a:hover';
		$elements[] = '#tribe-events .tribe-events-list .tribe-event-featured a:active';
		$elements[] = '.tribe-events-gmap:hover:before';
		$elements[] = '.tribe-events-gmap:hover:after';
		$elements[] = '.tribe-events-nav-previous a:hover:before, .tribe-events-nav-previous a:hover:after';
		$elements[] = '.tribe-events-nav-next a:hover:before, .tribe-events-nav-next a:hover:after';
		$elements[] = '#tribe-events-content .tribe-events-sub-nav li a:hover';
		$elements[] = '.widget .tribe-mini-calendar-event .list-date .list-dayname';
		$elements[] = '#tribe_events_filters_wrapper .tribe_events_slider_val';
		$elements[] = '.tribe-events-tickets .tickets_price .amount';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__registration__back__to__cart:hover';
		$elements[] = '.tribe-block__tickets__registration__back__to__cart:hover:before';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$elements = array( '.star-rating:before', '.star-rating span:before' );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$elements = array(
		'#wrapper .post-content blockquote',
		'.comment-text blockquote',
		'.progress-bar-content',
		'.pagination .current',
		'.pagination a.inactive:hover',
		'.fusion-hide-pagination-text .pagination-prev:hover',
		'.fusion-hide-pagination-text .pagination-next:hover',
		'#nav ul li > a:hover',
		'#sticky-nav ul li > a:hover',
		'.tagcloud a:hover',
	);
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '.bbp-pagination .bbp-pagination-links .current';
		$elements[] = '.bbp-topic-pagination .page-numbers:hover';
		$elements[] = '#bbpress-forums div.bbp-topic-tags a:hover';
		$elements[] = '.fusion-hide-pagination-text .bbp-pagination .bbp-pagination-links .pagination-prev:hover';
		$elements[] = '.fusion-hide-pagination-text .bbp-pagination .bbp-pagination-links .pagination-next:hover';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce-pagination .page-numbers.current';
		$elements[] = '.woocommerce-pagination .page-numbers:hover';
		$elements[] = '.woocommerce-pagination .current';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li.active a']['border-top-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$css['global']['#wrapper .side-nav li.current_page_item a']['border-right-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
	$css['global']['#wrapper .side-nav li.current_page_item a']['border-left-color']  = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$css['global']['#wrapper .fusion-vertical-menu-widget .menu li.current_page_item > a']['border-right-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
	$css['global']['#wrapper .fusion-vertical-menu-widget .menu li.current_page_item > a']['border-left-color']  = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$elements = array(
		'ul.circle-yes li:before',
		'.circle-yes ul li:before',
		'.progress-bar-content',
		'.pagination .current',
		'.fusion-date-and-formats .fusion-date-box',
		'.table-2 table thead',
		'.tagcloud a:hover',
		'#toTop:hover',
		'.fusion-search-form-classic .searchform .fusion-search-form-content .fusion-search-button input[type="submit"]:hover',
		'ul.arrow li:before',
	);
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '.bbp-pagination .bbp-pagination-links .current';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.onsale';
		$elements[] = '.woocommerce-pagination .current';
		$elements[] = '.woocommerce .social-share li a:hover i';
		$elements[] = '.price_slider_wrapper .ui-slider .ui-slider-range';
		$elements[] = 'p.woocommerce-store-notice';
		$elements[] = '.avada-myaccount-data .digital-downloads li:before';
		$elements[] = '.avada-thank-you .order_details li:before';
		$elements[] = '.fusion-content-widget-area .widget_layered_nav li.chosen';
		$elements[] = '.fusion-content-widget-area .widget_layered_nav_filters li.chosen';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '.tribe-events-calendar thead th';
		$elements[] = 'body #wrapper .tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]';
		$elements[] = '#tribe-events-content table.tribe-events-calendar .type-tribe_events.tribe-event-featured';
		$elements[] = 'body #wrapper #tribe-events-content .tribe-events-calendar td.tribe-events-present.mobile-active:hover';
		$elements[] = 'body #wrapper #tribe-events-content .tribe-events-calendar .mobile-active:hover';
		$elements[] = 'body #wrapper .tribe-events-calendar .mobile-active div[id*=tribe-events-daynum-]';
		$elements[] = '#tribe-events-content .tribe-events-tooltip .entry-title';
		$elements[] = '#tribe-events-content .tribe-events-tooltip .tribe-event-title';
		$elements[] = '.tribe-events-list-separator-month';
		$elements[] = '.fusion-body .tribe-mini-calendar-event .list-date';
		$elements[] = '.tribe-grid-allday .tribe-event-featured.tribe-events-week-allday-single';
		$elements[] = '.tribe-grid-allday .tribe-event-featured.tribe-events-week-hourly-single';
		$elements[] = '.tribe-grid-body .tribe-event-featured.tribe-events-week-allday-single';
		$elements[] = '.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single';
		$elements[] = '.tribe-grid-allday .tribe-event-featured.tribe-events-week-allday-single:hover';
		$elements[] = '.tribe-grid-allday .tribe-event-featured.tribe-events-week-hourly-single:hover';
		$elements[] = '.tribe-grid-body .tribe-event-featured.tribe-events-week-allday-single:hover';
		$elements[] = '.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single:hover';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements = array(
			'.tribe-grid-allday .tribe-event-featured.tribe-events-week-allday-single',
			'.tribe-grid-allday .tribe-event-featured.tribe-events-week-hourly-single',
			'.tribe-grid-body .tribe-event-featured.tribe-events-week-allday-single',
			'.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
	}

	if ( Avada()->settings->get( 'slidingbar_widgets' ) ) {
		$sliding_bar_position = Avada()->settings->get( 'slidingbar_position' );

		if ( 'right' === $sliding_bar_position || 'left' === $sliding_bar_position ) {
			$sliding_bar_width           = $sliding_bar_width_original = Fusion_Sanitize::size( str_replace( '%', 'vw', Avada()->settings->get( 'slidingbar_width' ) ), 'px' );
			$sliding_bar_closed_position = '-' . $sliding_bar_width;
			$sliding_bar_toggle_width    = 0;

			if ( false !== strpos( $sliding_bar_width, 'vw' ) ) {
				// Add 20px to the actual toggle width to account for the scroll bar.
				if ( 'triangle' === Avada()->settings->get( 'slidingbar_toggle_style' ) ) {
					$sliding_bar_toggle_width = 56;
				} elseif ( 'rectangle' === Avada()->settings->get( 'slidingbar_toggle_style' ) ) {
					$sliding_bar_toggle_width = 55;
				} elseif ( 'circle' === Avada()->settings->get( 'slidingbar_toggle_style' ) ) {
					$sliding_bar_toggle_width = 85;
				}

				if ( $sliding_bar_toggle_width ) {
					$sliding_bar_closed_position = 'calc(' . $sliding_bar_toggle_width . 'px - ' . $sliding_bar_width . ')';
					$sliding_bar_width           = 'calc(' . $sliding_bar_width . ' - ' . $sliding_bar_toggle_width . 'px)';
				}
			}

			$elements = array(
				'.fusion-sliding-bar-position-right .fusion-sliding-bar',
				'.fusion-sliding-bar-position-left .fusion-sliding-bar',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width'] = $sliding_bar_width;

			$css['global'][ '.fusion-sliding-bar-position-' . $sliding_bar_position ][ $sliding_bar_position ] = $sliding_bar_closed_position;

			$sliding_bar_content_padding_container = ' .fusion-sliding-bar-content-wrapper';
		} else {
			$sliding_bar_content_padding_container = ' .fusion-sliding-bar';
		}

		$css['global'][ '.fusion-sliding-bar-position-' . $sliding_bar_position . $sliding_bar_content_padding_container ]['padding-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'slidingbar_content_padding', 'top' ) );
		$css['global'][ '.fusion-sliding-bar-position-' . $sliding_bar_position . $sliding_bar_content_padding_container ]['padding-right']  = Fusion_Sanitize::size( Avada()->settings->get( 'slidingbar_content_padding', 'right' ) );
		$css['global'][ '.fusion-sliding-bar-position-' . $sliding_bar_position . $sliding_bar_content_padding_container ]['padding-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'slidingbar_content_padding', 'bottom' ) );
		$css['global'][ '.fusion-sliding-bar-position-' . $sliding_bar_position . $sliding_bar_content_padding_container ]['padding-left']   = Fusion_Sanitize::size( Avada()->settings->get( 'slidingbar_content_padding', 'left' ) );

		$elements = array(
			'.fusion-sliding-bar',
			'.fusion-sliding-bar-toggle-rectangle .fusion-sb-toggle',
			'.fusion-sliding-bar-toggle-circle .fusion-sb-toggle',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_bg_color' ) );

		$elements = array(
			'.fusion-sliding-bar-position-top.fusion-sliding-bar-toggle-triangle .fusion-sb-toggle',
			'.fusion-sliding-bar-position-right.fusion-sliding-bar-toggle-triangle .fusion-sb-toggle',
			'.fusion-sliding-bar-position-left.fusion-sliding-bar-toggle-triangle .fusion-sb-toggle',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_bg_color' ) );

		$css['global']['.fusion-sliding-bar-position-bottom.fusion-sliding-bar-toggle-triangle .fusion-sb-toggle']['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_bg_color' ) );

		$css['global']['.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-nav ul li']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_bg_color' ) );

		$css['global']['.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul']['border']          = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_divider_color' ) );
		$css['global']['.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul li']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_divider_color' ) );

		if ( Avada()->settings->get( 'slidingbar_border' ) ) {
			$css['global']['#slidingbar-area']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_bg_color' ) );

			if ( 'top' === $sliding_bar_position ) {
				$css['global']['.fusion-header-wrapper']['margin-top']                 = '3px';
				$css['global']['.admin-bar p.woocommerce-store-notice']['padding-top'] = '13px';
			}
		}

		if ( 'bottom' === $sliding_bar_position && ! Avada()->settings->get( 'slidingbar_sticky' ) ) {
			$css['global']['body']['position'] = 'relative';
		}

		$elements = array(
			'.slidingbar-area a:hover',
			'#slidingbar-area ul li a:hover',
			'#slidingbar-area .widget li.recentcomments:hover:before',
			'#slidingbar-area .fusion-accordian .panel-title a:hover',
			'.slidingbar-area .widget li a:hover:before',
			'#slidingbar-area .jtwt .jtwt_tweet a:hover',
			'#slidingbar-area .widget_nav_menu .current-menu-item > a',
			'#slidingbar-area .widget_nav_menu .current-menu-item > a:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_link_color_hover' ) );

		$elements = array(
			'.slidingbar-area .tagcloud a:hover',
			'.fusion-search-form-classic #slidingbar-area .searchform .fusion-search-form-content .fusion-search-button input[type="submit"]:hover',
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$elements[] = '.slidingbar-area .price_slider_wrapper .ui-slider .ui-slider-range';
			$elements[] = '.slidingbar-area .price_slider_wrapper .price_slider_amount button';
			$elements[] = '.slidingbar-area .price_slider_wrapper .price_slider_amount button:hover';
		}

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_link_color_hover' ) );

		$css['global']['.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-nav ul li.active a']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_link_color_hover' ) );
	}

	$elements = array(
		'.fusion-separator .icon-wrapper',
		'html',
		'body',
		'#sliders-container',
		'#fusion-gmap-container',
	);

	if ( 'Boxed' !== Avada()->settings->get( 'layout' ) ) {
		$elements[] = '#wrapper';
		$elements[] = '#main';
	} else {
		$elements[] = '#main';
	}

	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '.bbp-arrow';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce-tabs > .tabs .active a';
		$elements[] = '.woocommerce-MyAccount-navigation > ul .is-active a';
		$elements[] = '.woocommerce-checkout-nav .is-active a';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'content_bg_color' ) );

	$css['global']['.fusion-footer-widget-area']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_bg_color' ) );

	$css['global']['.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_bg_color' ) );

	$css['global']['.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul']['border']          = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'footer_divider_color' ) );
	$css['global']['.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul li']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_divider_color' ) );

	$css['global']['.fusion-footer-widget-area']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'footer_border_color' ) );
	$css['global']['.fusion-footer-widget-area']['border-top-width'] = intval( Avada()->settings->get( 'footer_border_size' ) ) . 'px';

	$css['global']['.fusion-footer-copyright-area']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'copyright_bg_color' ) );
	$css['global']['.fusion-footer-copyright-area']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'copyright_border_color' ) );
	$css['global']['.fusion-footer-copyright-area']['border-top-width'] = intval( Avada()->settings->get( 'copyright_border_size' ) ) . 'px';

	$css['global']['.fusion-copyright-notice']['color']              = Fusion_Sanitize::color( Avada()->settings->get( 'copyright_text_color' ) );
	$css['global']['.fusion-copyright-notice a']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'copyright_link_color' ) );
	$css['global']['.fusion-footer-copyright-area a:hover']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'copyright_link_color_hover' ) );

	if ( Avada()->settings->get( 'footer_divider_line' ) ) {
		$css['global']['.fusion-footer footer .fusion-row .fusion-columns']['display']   = 'flex';
		$css['global']['.fusion-footer footer .fusion-row .fusion-columns']['flex-flow'] = 'wrap';
		if ( 'none' !== Avada()->settings->get( 'footer_divider_line_style' ) ) {
			if ( is_rtl() ) {
				$css['global']['.fusion-footer footer .fusion-columns .fusion-column.fusion-has-widgets']['border-left']             = Fusion_Sanitize::size( Avada()->settings->get( 'footer_divider_line_size' ), 'px' ) . ' ' . Avada()->settings->get( 'footer_divider_line_style' ) . ' ' . Fusion_Sanitize::color( Avada()->settings->get( 'footer_divider_color' ) );
				$css['global']['.fusion-footer footer .fusion-row .fusion-columns .fusion-column.fusion-column-last']['border-left'] = 'none';
			} else {
				$css['global']['.fusion-footer footer .fusion-columns .fusion-column.fusion-has-widgets']['border-right']             = Fusion_Sanitize::size( Avada()->settings->get( 'footer_divider_line_size' ), 'px' ) . ' ' . Avada()->settings->get( 'footer_divider_line_style' ) . ' ' . Fusion_Sanitize::color( Avada()->settings->get( 'footer_divider_color' ) );
				$css['global']['.fusion-footer footer .fusion-row .fusion-columns .fusion-column.fusion-column-last']['border-right'] = 'none';
			}
		}
	}

	$css['global']['.fusion-footer footer .fusion-columns ']['margin']                      = '0 -' . Fusion_Sanitize::size( Avada()->settings->get( 'footer_widgets_padding' ) );
	$css['global']['.fusion-footer footer .fusion-columns .fusion-column']['padding-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'footer_widgets_padding' ) );
	$css['global']['.fusion-footer footer .fusion-columns .fusion-column']['padding-left']  = Fusion_Sanitize::size( Avada()->settings->get( 'footer_widgets_padding' ) );

	$css['global']['.fusion-image-wrapper .fusion-rollover']['background-image'][] = 'linear-gradient(top, ' . Fusion_Sanitize::color( Avada()->settings->get( 'image_gradient_top_color' ) ) . ' 0%, ' . Fusion_Sanitize::color( Avada()->settings->get( 'image_gradient_bottom_color' ) ) . ' 100%)';
	$css['global']['.fusion-image-wrapper .fusion-rollover']['background-image'][] = '-webkit-gradient(linear, left top, left bottom, color-stop(0, ' . Fusion_Sanitize::color( Avada()->settings->get( 'image_gradient_top_color' ) ) . '), color-stop(1, ' . Fusion_Sanitize::color( Avada()->settings->get( 'image_gradient_bottom_color' ) ) . '))';
	$css['global']['.fusion-image-wrapper .fusion-rollover']['background-image'][] = 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=' . Fusion_Color::new_color( Avada()->settings->get( 'image_gradient_top_color' ) )->to_css( 'hex' ) . ', endColorstr=' . Fusion_Color::new_color( Avada()->settings->get( 'image_gradient_bottom_color' ) )->to_css( 'hex' ) . '), progid: DXImageTransform.Microsoft.Alpha(Opacity=0)';

	$css['global']['.no-cssgradients .fusion-image-wrapper .fusion-rollover']['background'] = Fusion_Color::new_color( Avada()->settings->get( 'image_gradient_top_color' ) )->to_css( 'hex' );

	$css['global']['.fusion-image-wrapper:hover .fusion-rollover']['filter'] = 'progid:DXImageTransform.Microsoft.gradient(startColorstr=' . Fusion_Color::new_color( Avada()->settings->get( 'image_gradient_top_color' ) )->to_css( 'hex' ) . ', endColorstr=' . Fusion_Color::new_color( Avada()->settings->get( 'image_gradient_bottom_color' ) )->to_css( 'hex' ) . '), progid: DXImageTransform.Microsoft.Alpha(Opacity=100)';

	$elements = array(
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-link',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-gallery',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_text_color' ) );

	$elements = array(
		'.fusion-rollover .fusion-rollover-content .fusion-rollover-title',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-title a',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-categories',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-categories a',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content a',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .price *',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a:before',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_text_color' ) );

	$css['global']['.fusion-page-title-bar']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'page_title_border_color' ) );

	$po_page_title_border_color = get_post_meta( $c_page_id, 'pyre_page_title_bar_borders_color', true );
	if ( ( 0 === Fusion_Color::new_color( Avada()->settings->get( 'page_title_border_color' ) )->alpha && empty( $po_page_title_border_color ) ) || 0 === Fusion_Color::new_color( $po_page_title_border_color )->alpha ) {
		$css['global']['.fusion-page-title-bar']['border'] = 'none';
	}

	if ( '' !== Avada()->settings->get( 'footerw_bg_image', 'url' ) ) {

		$css['global']['.fusion-footer-widget-area']['background-image']    = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'footerw_bg_image', 'url' ) ) . '")';
		$css['global']['.fusion-footer-widget-area']['background-repeat']   = esc_attr( Avada()->settings->get( 'footerw_bg_repeat' ) );
		$css['global']['.fusion-footer-widget-area']['background-position'] = esc_attr( Avada()->settings->get( 'footerw_bg_pos' ) );

		if ( Avada()->settings->get( 'footerw_bg_full' ) ) {

			$css['global']['.fusion-footer-widget-area']['background-attachment'] = 'scroll';
			$css['global']['.fusion-footer-widget-area']['background-position']   = 'center center';
			$css['global']['.fusion-footer-widget-area']['background-size']       = 'cover';

		}
	}

	$css['global'][ $footer_headings_typography_elements ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'footer_headings_typography' ) );
	$css['global'][ $footer_headings_typography_elements ]['font-size']      = Fusion_Sanitize::size( Avada()->settings->get( 'footer_headings_typography', 'font-size' ) );
	$css['global'][ $footer_headings_typography_elements ]['font-weight']    = intval( Avada()->settings->get( 'footer_headings_typography', 'font-weight' ) );
	$css['global'][ $footer_headings_typography_elements ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'footer_headings_typography', 'line-height' ) );
	$css['global'][ $footer_headings_typography_elements ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'footer_headings_typography', 'letter-spacing' ), 'px' );

	$font_style = Avada()->settings->get( 'footer_headings_typography', 'font-style' );
	$css['global'][ $footer_headings_typography_elements ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';

	if ( in_array( Avada()->settings->get( 'footer_special_effects' ), array( 'footer_sticky', 'footer_sticky_with_parallax_bg_image' ) ) ) {
		$css['global']['html']['height'] = '100%';
	}

	$css['global']['.fusion-footer-widget-area']['padding-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'footer_area_padding', 'top' ) );
	$css['global']['.fusion-footer-widget-area']['padding-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'footer_area_padding', 'bottom' ) );

	$elements = array(
		'.fusion-footer-widget-area > .fusion-row',
		'.fusion-footer-copyright-area > .fusion-row',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left']  = Fusion_Sanitize::size( Avada()->settings->get( 'footer_area_padding', 'left' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'footer_area_padding', 'right' ) );

	if ( Avada()->settings->get( 'footer_100_width' ) ) {
		$elements = array(
			'.layout-wide-mode .fusion-footer-widget-area > .fusion-row',
			'.layout-wide-mode .fusion-footer-copyright-area > .fusion-row',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = '100% !important';
	}

	$css['global']['.fusion-footer-copyright-area']['padding-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'copyright_padding', 'top' ) );
	$css['global']['.fusion-footer-copyright-area']['padding-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'copyright_padding', 'bottom' ) );

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$css['global']['.tribe-events-single .related-posts .fusion-title .title-sep']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_border_color' ), Avada()->settings->get_default( 'ec_border_color' ) );
	}

	if ( isset( $body_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'body_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'body_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['family'] ) ]['font-style']                         = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
		$css['global'][ $dynamic_css_helpers->implode( array( '.post-content blockquote', '.review blockquote q' ) ) ]['font-style'] = 'italic';
	}
	if ( isset( $body_typography_elements['line-height'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['line-height'] ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'body_typography', 'line-height' ) );
	}
	if ( isset( $body_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'body_typography', 'font-size' ) );
	}
	if ( isset( $body_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'body_typography', 'color' ) );
	}

	$elements = array(
		'.fusion-page-title-bar .fusion-breadcrumbs',
		'.fusion-page-title-bar .fusion-breadcrumbs li',
		'.fusion-page-title-bar .fusion-breadcrumbs li a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'breadcrumbs_font_size' ) );

	$css['global']['#wrapper .side-nav li a']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'side_nav_font_size' ) );

	$css['global']['.sidebar .widget .widget-title']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'sidew_font_size' ) );
	$css['global'][ $nav_typography_elements ]['font-family']     = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'nav_typography' ) );
	$elements = array(
		'.fusion-main-menu-cart .fusion-widget-cart-number',
		'.fusion-flyout-cart-wrapper .fusion-widget-cart-number',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-family'] = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'nav_typography' ) );
	$css['global'][ $nav_typography_elements ]['font-size']                     = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) );
	$css['global'][ $nav_typography_elements ]['font-weight']                   = intval( Avada()->settings->get( 'nav_typography', 'font-weight' ) );
	$css['global'][ $nav_typography_elements ]['letter-spacing']                = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'letter-spacing' ), 'px' );

	$css['global']['.fusion-menu-highlight-label']['border-radius'] = Fusion_Sanitize::size( Avada()->settings->get( 'main_nav_highlight_radius' ) );

	$font_style = Avada()->settings->get( 'nav_typography', 'font-style' );
	$css['global'][ $nav_typography_elements ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements = array(
			'.single-tribe_events .fusion-content-widget-area .widget .widget-title',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-single-section-title',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-tickets-title',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'ec_sidew_font_size' ) );

		$elements = array(
			'.single-tribe_events .fusion-content-widget-area',
			'.single-tribe_events .tribe-events-event-meta',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = intval( Avada()->settings->get( 'ec_text_font_size' ) ) . 'px';
	}

	$elements = array(
		'#slidingbar-area h3',
		'#slidingbar-area .widget-title',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size']   = Fusion_Sanitize::size( Avada()->settings->get( 'slidingbar_font_size' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'slidingbar_font_size' ) );

	$css['global']['.fusion-copyright-notice']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'copyright_font_size' ) );

	$elements = array(
		'#main .fusion-row',
		'.fusion-footer-widget-area .fusion-row',
		'#slidingbar-area .fusion-row',
		'.fusion-footer-copyright-area .fusion-row',
		'.fusion-page-title-row',
		'.tfs-slider .slide-content-container .slide-content',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );

	// Post title.
	if ( isset( $post_title_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'post_title_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'post_title_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'post_title_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'post_title_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'post_title_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $post_title_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'post_title_typography', 'font-size' ) );
	}
	if ( isset( $post_title_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $post_title_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'post_title_typography', 'color' ) );
	}

	// Post title extras.
	if ( isset( $post_title_extras_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'post_titles_extras_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'post_titles_extras_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'post_titles_extras_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'post_titles_extras_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'post_titles_extras_typography', 'font-style' );
		if ( ! empty( $font_style ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['family'] ) ]['font-style'] = esc_attr( Avada()->settings->get( 'post_titles_extras_typography', 'font-style' ) );
		}
	}
	if ( isset( $post_title_extras_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'post_titles_extras_typography', 'font-size' ) );
	}
	if ( isset( $post_title_extras_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $post_title_extras_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'post_titles_extras_typography', 'color' ) );
	}

	$css['global']['.ei-title h2']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'es_title_font_size' ) );
	$css['global']['.ei-title h3']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'es_caption_font_size' ) );

	if ( class_exists( 'bbPress' ) ) {

		$elements = array(
			'#bbpress-forums',
			'#bbpress-forums ul.bbp-lead-topic',
			'#bbpress-forums ul.bbp-topics',
			'#bbpress-forums ul.bbp-forums',
			'#bbpress-forums ul.bbp-replies',
			'#bbpress-forums ul.bbp-search-results',
			'#bbpress-forums .bbp-reply-header a.bbp-reply-permalink',
			'#bbpress-forums .bbp-forum-info .bbp-forum-content',
			'div.bbp-breadcrumb',
			'div.bbp-topic-tags',
		);

		$heading_elements = array(
			'#bbpress-forums li.bbp-header ul',
			'#bbpress-forums li.bbp-body .bbp-forum-title',
			'#bbpress-forums li.bbp-body .bbp-forum-topic-count',
			'#bbpress-forums li.bbp-body .bbp-forum-reply-count',
			'#bbpress-forums li.bbp-body .bbp-topic-permalink',
			'#bbpress-forums li.bbp-body .bbp-topic-voice-count',
			'#bbpress-forums li.bbp-body .bbp-topic-reply-count',
			'#bbpress-forums fieldset.bbp-form legend',
		);

		$side_elements = array(
			'#bbpress-forums div.bbp-reply-author',
			'#bbpress-forums div.bbp-topic-author',
			'#bbpress-forums fieldset.bbp-form label',
			'#bbpress-forums .bbp-reply-form fieldset label',
			'#bbpress-forums .bbp-reply-form fieldset label',
			'#bbpress-forums div.bbp-reply-favs',
		);

		$meta_elements = array(
			'#bbpress-forums li.bbp-body ul.forum .bbp-forum-freshness',
			'#bbpress-forums li.bbp-body ul.topic .bbp-topic-freshness',
			'#bbpress-forums .bbp-forum-info .bbp-forum-content',
			'#bbpress-forums p.bbp-topic-meta',
			'.bbp-pagination-count',
			'#bbpress-forums div.bbp-topic-author .fusion-reply-id',
			'#bbpress-forums div.bbp-reply-author .fusion-reply-id',
			'#bbpress-forums .bbp-reply-header .bbp-meta',
			'#bbpress-forums span.bbp-admin-links a',
			'#bbpress-forums span.bbp-admin-links',
			'#bbpress-forums .bbp-topic-content ul.bbp-topic-revision-log',
			'#bbpress-forums .bbp-reply-content ul.bbp-topic-revision-log',
			'#bbpress-forums .bbp-reply-content ul.bbp-reply-revision-log',
			'#bbpress-forums .bbp-reply-header a.bbp-reply-permalink',
			'#bbpress-forums div.bbp-reply-author span.bbp-author-ip',
		);

		$elements = array_merge( $elements, $heading_elements, $side_elements, $meta_elements );

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'bbp_forum_base_font_size' ) );

		$bbpress_base_font_size_value = Fusion_Sanitize::number( Fusion_Sanitize::size( Avada()->settings->get( 'bbp_forum_base_font_size' ) ) );
		$bbpress_base_font_size_unit  = Fusion_Sanitize::get_unit( Fusion_Sanitize::size( Avada()->settings->get( 'bbp_forum_base_font_size' ) ) );

		$css['global'][ $dynamic_css_helpers->implode( $heading_elements ) ]['font-size'] = ( 1.25 * $bbpress_base_font_size_value ) . $bbpress_base_font_size_unit;
		$css['global'][ $dynamic_css_helpers->implode( $side_elements ) ]['font-size']    = ( 1.1 * $bbpress_base_font_size_value ) . $bbpress_base_font_size_unit;
		$css['global'][ $dynamic_css_helpers->implode( $meta_elements ) ]['font-size']    = ( 1 * $bbpress_base_font_size_value ) . $bbpress_base_font_size_unit;

	}

	$elements = array(
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-categories',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-categories a',
		'.fusion-recent-posts .columns .column .meta',
		'.fusion-carousel-meta',
		'.fusion-single-line-meta',
		'#wrapper .fusion-events-shortcode .fusion-events-meta h4',
	);

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'meta_font_size' ) );

	$elements = array(
		'.fusion-meta',
		'.fusion-meta-info',
		'.fusion-recent-posts .columns .column .meta',
		'.post .single-line-meta',
		'.fusion-carousel-meta',
		'.widget_recent_entries .post-date',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'meta_font_size' ) );

	$elements = array(
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a',
		'.product-buttons a',
		'.fusion-rollover-linebreak',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'woo_icon_font_size' ) );

	if ( is_rtl() ) {
		$elements = array(
			'.rtl .fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a',
			'.rtl .product-buttons a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = 'calc(' . Avada()->settings->get( 'woo_icon_font_size' ) . ' + 2px)';

		$elements = array(
			'.rtl .fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a:before',
			'.rtl .product-buttons a:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right'] = 'calc(-' . Avada()->settings->get( 'woo_icon_font_size' ) . ' - 2px)';
	} else {
		$elements = array(
			'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a',
			'.product-buttons a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left'] = 'calc(' . Avada()->settings->get( 'woo_icon_font_size' ) . ' + 2px)';

		$elements = array(
			'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a:before',
			'.product-buttons a:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left'] = 'calc(-' . Avada()->settings->get( 'woo_icon_font_size' ) . ' - 2px)';
	}

	$elements = array(
		'.pagination',
		'.page-links',
		'.pagination .pagination-next',
		'.pagination .pagination-prev',
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce-pagination .page-numbers';
		$elements[] = '.woocommerce-pagination .next';
		$elements[] = '.woocommerce-pagination .prev';
		$elements[] = '.woocommerce-pagination--without-numbers .woocommerce-button';
	}

	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '.bbp-pagination .bbp-pagination-links';
		$elements[] = '.bbp-pagination .bbp-pagination-links .pagination-prev';
		$elements[] = '.bbp-pagination .bbp-pagination-links .pagination-next';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'pagination_font_size' ) );

	// Needed because the font size on the main pagination container is set to 0.
	if ( class_exists( 'WooCommerce' ) ) {
		$css['global']['.fusion-show-pagination-text .woocommerce-pagination']['margin-left']  = Fusion_Sanitize::size( Avada()->settings->get( 'pagination_font_size' ) );
		$css['global']['.fusion-show-pagination-text .woocommerce-pagination']['margin-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'pagination_font_size' ) );
	}

	$css['global']['.fusion-page-title-bar h1']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'page_title_color' ) );
	$css['global']['.fusion-page-title-bar h3']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'page_title_subheader_color' ) );

	$css['global'][ $dynamic_css_helpers->implode( $link_color_elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'link_color' ) );

	$elements = array(
		'.pagination .pagination-prev:hover:before',
		'.pagination .pagination-next:hover:after',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	if ( class_exists( 'bbPress' ) ) {
		$link_color_rgb   = fusion_hex2rgb( Fusion_Sanitize::color( Avada()->settings->get( 'link_color' ) ) );
		$link_color_hover = 'rgba(' . $link_color_rgb[0] . ',' . $link_color_rgb[1] . ',' . $link_color_rgb[2] . ',0.8)';

		$css['global']['#bbpress-forums #bbp-single-user-details #bbp-user-navigation li.current a:hover']['color'] = $link_color_hover;
	}

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements = array(
			'.single-tribe_events .fusion-content-widget-area a',
			'.single-tribe_events .fusion-content-widget-area a:before',
			'.single-tribe_events .fusion-content-widget-area a:after',
			'.single-tribe_events .fusion-content-widget-area .widget li a',
			'.single-tribe_events .fusion-content-widget-area .widget li a:before',
			'.single-tribe_events .fusion-content-widget-area .widget li a:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_sidebar_link_color' ) );

		$elements = array(
			'.single-tribe_events .fusion-content-widget-area a:hover',
			'.single-tribe_events .fusion-content-widget-area a:hover:before',
			'.single-tribe_events .fusion-content-widget-area a:hover:after',
			'.single-tribe_events .fusion-content-widget-area .widget li a:hover',
			'.single-tribe_events .fusion-content-widget-area .widget li a:hover:before',
			'.single-tribe_events .fusion-content-widget-area .widget li a:hover:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
	}

	$elements = array(
		'.fusion-page-title-bar .fusion-breadcrumbs',
		'.fusion-page-title-bar .fusion-breadcrumbs a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'breadcrumbs_text_color' ) );

	$css['global']['.fusion-page-title-bar .fusion-breadcrumbs a:hover']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'breadcrumbs_text_hover_color' ) );

	$elements = array(
		'#slidingbar-area h3',
		'#slidingbar-area .fusion-title > *',
		'#slidingbar-area .widget-title',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_headings_color' ) );

	if ( Avada()->settings->get( 'slidingbar_widgets' ) ) {
		$elements = array(
			'#slidingbar-area',
			'#slidingbar-area .widget_nav_menu li',
			'#slidingbar-area .widget_categories li',
			'#slidingbar-area .widget_product_categories li',
			'#slidingbar-area .widget_meta li',
			'#slidingbar-area .widget li.recentcomments',
			'#slidingbar-area .widget_recent_entries li',
			'#slidingbar-area .widget_archive li',
			'#slidingbar-area .widget_pages li',
			'#slidingbar-area .widget_links li',
			'#slidingbar-area .widget_layered_nav li',
			'#slidingbar-area .fusion-column',
			'#slidingbar-area .jtwt',
			'#slidingbar-area .jtwt .jtwt_tweet',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_text_color' ) );

		$elements = array(
			'.slidingbar-area a',
			'.slidingbar-area .widget li a:before',
			' #slidingbar-area .jtwt .jtwt_tweet a',
			'#slidingbar-area .fusion-tabs-widget .fusion-tabs-nav ul li a',
			'.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-date-box',
			'#slidingbar-area .fusion-accordian .panel-title a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_link_color' ) );

		$css['global']['.fusion-sliding-bar']['text-align'] = Avada()->settings->get( 'slidingbar_content_align' );
	}

	$elements = array(
		'.sidebar .widget .widget-title',
		'.sidebar .widget .heading h4',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'sidebar_heading_color' ) );

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements = array(
			'.single-tribe_events .fusion-content-widget-area .widget .widget-title',
			'.single-tribe_events .fusion-content-widget-area .widget .heading h4',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-single-section-title',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-tickets-title',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_sidebar_heading_color' ) );

		$css['global']['.single-tribe_events .fusion-content-widget-area']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_sidebar_text_color' ) );

		$elements = array(
			'.single-tribe_events .fusion-content-widget-area .widget_nav_menu li',
			'.single-tribe_events .fusion-content-widget-area .widget_meta li',
			'.single-tribe_events .fusion-content-widget-area .widget_recent_entries li',
			'.single-tribe_events .fusion-content-widget-area .widget_archive li',
			'.single-tribe_events .fusion-content-widget-area .widget_pages li',
			'.single-tribe_events .fusion-content-widget-area .widget_links li',
			'.single-tribe_events .fusion-content-widget-area .widget li a',
			'.single-tribe_events .fusion-content-widget-area .widget .recentcomments',
			'.single-tribe_events .fusion-content-widget-area .widget_categories li',
			'.single-tribe_events .fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-classic',
			'.single-tribe_events .fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-tabs-widget-items li',
			'.single-tribe_events .fusion-content-widget-area .tagcloud a',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-meta-group dd',
			'.single-tribe_events .fusion-content-widget-area .tribe-mini-calendar-event',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-list-widget ol li',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-venue-widget li',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-tickets td',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_sidebar_divider_color' ) );
	}

	$elements = array(
		'.sidebar .widget .widget-title',
		'.sidebar .widget .heading .widget-title',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'sidebar_widget_bg_color' ) );

	if ( '0' != Fusion_Color::new_color( Avada()->settings->get( 'sidebar_widget_bg_color' ) )->alpha ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = '9px 15px';
	}

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements = array(
			'.single-tribe_events .fusion-content-widget-area .widget .widget-title',
			'.single-tribe_events .fusion-content-widget-area .widget .heading .widget-title',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-single-section-title',
			'.single-tribe_events .fusion-content-widget-area .tribe-events-tickets-title',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_sidebar_widget_bg_color' ) );

		if ( '0' != Fusion_Color::new_color( Avada()->settings->get( 'ec_sidebar_widget_bg_color' ) )->alpha ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = '9px 15px';
		}
	}

	$elements = array(
		'.fusion-footer-widget-area h3',
		'.fusion-footer-widget-area .widget-title',
		'.fusion-footer-widget-column .product-title',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_headings_typography', 'color' ) );

	$elements = array(
		'.fusion-footer-widget-area',
		'.fusion-footer-widget-area .widget_nav_menu li',
		'.fusion-footer-widget-area .widget_categories li',
		'.fusion-footer-widget-area .widget_product_categories li',
		'.fusion-footer-widget-area .widget_meta li',
		'.fusion-footer-widget-area .widget li.recentcomments',
		'.fusion-footer-widget-area .widget_recent_entries li',
		'.fusion-footer-widget-area .widget_archive li',
		'.fusion-footer-widget-area .widget_pages li',
		'.fusion-footer-widget-area .widget_links li',
		'.fusion-footer-widget-area .widget_layered_nav li',
		'.fusion-footer-widget-area article.col',
		'.fusion-footer-widget-area .jtwt',
		'.fusion-footer-widget-area .jtwt .jtwt_tweet',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_text_color' ) );

	$elements = array(
		'.fusion-footer-widget-area a',
		'.fusion-footer-widget-area .widget li a:before',
		'.fusion-footer-widget-area .jtwt .jtwt_tweet a',
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li a',
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-date-box',
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .post-holder a',
		'.fusion-footer-widget-area .fusion-accordian .panel-title a',
	);

	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.fusion-footer-widget-area .woocommerce-mini-cart__buttons a:before';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_link_color' ) );

	$elements = array(
		'.fusion-footer-widget-area ul li a:hover',
		'.fusion-footer-widget-area .widget a:hover',
		'.fusion-footer-widget-area .widget li a:hover:before',
		'.fusion-footer-widget-area .widget li.recentcomments:hover:before',
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .post-holder a:hover',
		'.fusion-footer-widget-area .fusion-accordian .panel-title a:hover',
		'#wrapper .fusion-footer-widget-area .jtwt .jtwt_tweet a:hover',
	);

	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.fusion-footer-widget-area .widget_shopping_cart_content .total .amount';
		$elements[] = '.fusion-footer-widget-area .woocommerce-mini-cart__buttons a:hover:before';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_link_color_hover' ) );

	$css['global']['.fusion-footer-widget-area .tagcloud a:hover']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_link_color_hover' ) );

	$elements = array(
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li.active a',
		'#wrapper .fusion-footer-widget-area .fusion-tabs.classic .nav-tabs > li.active .tab-link',
		'#wrapper .fusion-footer-widget-area .fusion-tabs.classic .nav-tabs > li.active .tab-link:focus',
		'#wrapper .fusion-footer-widget-area .fusion-tabs.classic .nav-tabs > li.active .tab-link:hover',
		'#wrapper .fusion-footer-widget-area .fusion-tabs.vertical-tabs.classic .nav-tabs > li.active .tab-link',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_link_color_hover' ) );

	$css['global']['.fusion-footer-widget-area .fusion-accordian .panel-title a:hover .fa-fusion-box']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_link_color_hover' ) ) . ' !important';

	$css['global']['.ei-title h2']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'es_title_color' ) );
	$css['global']['.ei-title h3']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'es_caption_color' ) );

	// Blog element load more button.
	$css['global']['.fusion-load-more-button.fusion-blog-button']['background-color']       = Fusion_Sanitize::color( Avada()->settings->get( 'blog_load_more_posts_button_bg_color' ) );
	$css['global']['.fusion-load-more-button.fusion-blog-button:hover']['background-color'] = Fusion_Color::new_color( Avada()->settings->get( 'blog_load_more_posts_button_bg_color' ) )->get_new( 'alpha', '0.8' )->to_css( 'rgba' );

	$button_brightness = fusion_calc_color_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'blog_load_more_posts_button_bg_color' ) ) );
	$text_color        = ( 140 < $button_brightness ) ? '#333' : '#fff';
	$elements          = array(
		'.fusion-load-more-button.fusion-blog-button',
		'.fusion-load-more-button.fusion-blog-button:hover',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $text_color;

	// Portfolio element load more button.
	if ( class_exists( 'FusionSC_Portfolio' ) ) {
		$css['global']['.fusion-load-more-button.fusion-portfolio-button']['background-color']       = Fusion_Sanitize::color( Avada()->settings->get( 'portfolio_load_more_posts_button_bg_color' ) );
		$css['global']['.fusion-load-more-button.fusion-portfolio-button:hover']['background-color'] = Fusion_Color::new_color( Avada()->settings->get( 'portfolio_load_more_posts_button_bg_color' ) )->get_new( 'alpha', '0.8' )->to_css( 'rgba' );

		$button_brightness = fusion_calc_color_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'portfolio_load_more_posts_button_bg_color' ) ) );
		$text_color        = ( 140 < $button_brightness ) ? '#333' : '#fff';
		$elements          = array(
			'.fusion-load-more-button.fusion-portfolio-button',
			'.fusion-load-more-button.fusion-portfolio-button:hover',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $text_color;
	}

	// Portfolio Archive load more button.
	$css['global']['.fusion-portfolio-archive .fusion-load-more-button.fusion-portfolio-button']['background-color']       = Fusion_Sanitize::color( Avada()->settings->get( 'portfolio_archive_load_more_posts_button_bg_color' ) );
	$css['global']['.fusion-portfolio-archive .fusion-load-more-button.fusion-portfolio-button:hover']['background-color'] = Fusion_Color::new_color( Avada()->settings->get( 'portfolio_archive_load_more_posts_button_bg_color' ) )->get_new( 'alpha', '0.8' )->to_css( 'rgba' );

	$button_brightness = fusion_calc_color_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'portfolio_archive_load_more_posts_button_bg_color' ) ) );
	$text_color        = ( 140 < $button_brightness ) ? '#333' : '#fff';
	$elements          = array(
		'.fusion-portfolio-archive .fusion-load-more-button.fusion-portfolio-button',
		'.fusion-portfolio-archive .fusion-load-more-button.fusion-portfolio-button:hover',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $text_color;

	if ( class_exists( 'WooCommerce' ) ) {
		$elements = array( '.quantity .minus', '.quantity .plus, .fusion-body .tribe-events-tickets .quantity .minus, .fusion-body .tribe-events-tickets .quantity .plus' );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'qty_bg_color' ) );

		$elements = array( '.quantity .minus:hover', '.quantity .plus:hover, .fusion-body .tribe-events-tickets .quantity .minus:hover, .fusion-body .tribe-events-tickets .quantity .plus:hover' );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'qty_bg_hover_color' ) );

		$css['global']['.woocommerce .social-share li a:hover i']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
	}

	$css['global']['.fusion-sb-toggle-wrapper .fusion-sb-toggle:after']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_toggle_icon_color' ) );
	$css['global']['.fusion-sb-toggle-wrapper .fusion-sb-close:after']['color']  = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_toggle_icon_color' ) );

	if ( Avada()->settings->get( 'slidingbar_widgets' ) ) {
		$elements = array(
			'#slidingbar-area .widget_nav_menu li',
			'#slidingbar-area .widget_categories li',
			'#slidingbar-area .widget_product_categories li',
			'#slidingbar-area .widget_meta li',
			'#slidingbar-area .widget li.recentcomments',
			'#slidingbar-area .widget_recent_entries ul li',
			'#slidingbar-area .widget_archive li',
			'#slidingbar-area .widget_pages li',
			'#slidingbar-area .widget_links li',
			'#slidingbar-area .widget_layered_nav li',
			'#slidingbar-area .widget_product_categories li',
			'#slidingbar-area .product_list_widget li',
			'#slidingbar-area .price_slider_wrapper',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_divider_color' ) );

		$elements = array(
			'#slidingbar-area .tagcloud a',
			'.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-classic',
			'.fusion-sliding-bar-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-tabs-widget-items li',
			'#slidingbar-area .fusion-accordian .fusion-panel',
		);

		if ( class_exists( 'bbPress' ) ) {
			$elements[] = '#slidingbar-area .bbp-pagination .bbp-pagination-links a.inactive';
			$elements[] = '#slidingbar-area .bbp-topic-pagination .page-numbers';
			$elements[] = '#slidingbar-area .widget.widget.widget_display_replies ul li';
			$elements[] = '#slidingbar-area .widget.widget_display_topics ul li';
			$elements[] = '#slidingbar-area .widget.widget_display_views ul li';
			$elements[] = '#slidingbar-area .widget.widget_display_stats dt';
			$elements[] = '#slidingbar-area .widget.widget_display_stats dd';
		}

		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$elements[] = '#slidingbar-area .tribe-mini-calendar-event';
			$elements[] = '#slidingbar-area .tribe-events-list-widget ol li';
			$elements[] = '#slidingbar-area .tribe-events-venue-widget li';
		}
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'slidingbar_divider_color' ) );
	}

	$elements = array(
		'.fusion-footer-widget-area .widget_nav_menu li',
		'.fusion-footer-widget-area .widget_categories li',
		'.fusion-footer-widget-area .product_list_widget li',
		'.fusion-footer-widget-area .widget_meta li',
		'.fusion-footer-widget-area .widget li.recentcomments',
		'.fusion-footer-widget-area .widget_recent_entries li',
		'.fusion-footer-widget-area .widget_archive li',
		'.fusion-footer-widget-area .widget_pages li',
		'.fusion-footer-widget-area .widget_links li',
		'.fusion-footer-widget-area .widget_layered_nav li',
		'.fusion-footer-widget-area .widget_product_categories li',
		'.fusion-footer-widget-area ul li',
		'.fusion-footer-widget-area .tagcloud a',
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-classic',
		'.fusion-footer-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-tabs-widget-items li',
		'.fusion-footer-widget-area .fusion-accordian .fusion-panel',
	);

	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '.fusion-footer-widget-area .bbp-pagination .bbp-pagination-links a.inactive';
		$elements[] = '.fusion-footer-widget-area .bbp-topic-pagination .page-numbers';
		$elements[] = '.fusion-footer-widget-area .widget.widget.widget_display_replies ul li';
		$elements[] = '.fusion-footer-widget-area .widget.widget_display_forums ul li';
		$elements[] = '.fusion-footer-widget-area .widget.widget_display_topics ul li';
		$elements[] = '.fusion-footer-widget-area .widget.widget_display_views ul li';
		$elements[] = '.fusion-footer-widget-area .widget.widget_display_stats dt';
		$elements[] = '.fusion-footer-widget-area .widget.widget_display_stats dd';
	}

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '.fusion-footer-widget-area .tribe-mini-calendar-event';
		$elements[] = '.fusion-footer-widget-area .tribe-events-list-widget ol li';
		$elements[] = '.fusion-footer-widget-area .tribe-events-venue-widget li';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'footer_divider_color' ) );

	$elements = array(
		'.input-text',
		'input[type="text"]',
		'input[type="number"]',
		'input[type="email"]',
		'input[type="password"]',
		'textarea',
		'input.s',
		'#comment-input input',
		'#comment-textarea textarea',
		'.comment-form-comment textarea',
		'.post-password-form label input[type="password"]',
		'.main-nav-search-form input',
		'.search-page-search-form input',
		'.chzn-container-single .chzn-single',
		'.chzn-container .chzn-drop',
		'.fusion-body .avada-select-parent select',
		'.avada-select .select2-container .select2-choice',
		'.avada-select .select2-container .select2-choice2',
		'.select2-container--default .select2-selection--single',
		'.select2-dropdown',
		'select',
		'.searchform .fusion-search-form-content .fusion-search-field input',
	);
	if ( defined( 'ICL_SITEPRESS_VERSION' || class_exists( 'SitePress' ) ) ) {
		$elements[] = '#lang_sel_click a.lang_sel_sel';
		$elements[] = '#lang_sel_click ul ul a';
		$elements[] = '#lang_sel_click ul ul a:visited';
		$elements[] = '#lang_sel_click a';
		$elements[] = '#lang_sel_click a:visited';
	}
	if ( class_exists( 'GFForms' ) ) {
		$elements[] = '.gform_wrapper .gfield input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="file"])';
		$elements[] = '.gform_wrapper .gfield_select[multiple=multiple]';
		$elements[] = '.gform_wrapper .gfield select';
		$elements[] = '.gform_wrapper .gfield textarea';
	}
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = '.wpcf7-form .wpcf7-text';
		$elements[] = '.wpcf7-form .wpcf7-quiz';
		$elements[] = '.wpcf7-form .wpcf7-number';
		$elements[] = '.wpcf7-form textarea';
		$elements[] = '.wpcf7-form .wpcf7-select';
		$elements[] = '.wpcf7-captchar';
		$elements[] = '.wpcf7-form .wpcf7-date';
	}
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '#bbpress-forums .bbp-search-form #bbp_search';
		$elements[] = '.bbp-reply-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form input#bbp_topic_title';
		$elements[] = '.bbp-topic-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form select#bbp_stick_topic_select';
		$elements[] = '.bbp-topic-form select#bbp_topic_status_select';
		$elements[] = '#bbpress-forums div.bbp-the-content-wrapper textarea.bbp-the-content';
		$elements[] = '.bbp-login-form input';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]:focus';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select:focus';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) );

	$elements = array(
		'.avada-select-parent .select-arrow',
		'#wrapper .select-arrow',
		'.fusion-modal-content .select-arrow',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) );

	$elements = array(
		'.input-text',
		'input[type="text"]',
		'input[type="number"]',
		'input[type="email"]',
		'input[type="password"]',
		'textarea',
		'input.s',
		'input.s .placeholder',
		'#comment-input input',
		'#comment-textarea textarea',
		'#comment-input .placeholder',
		'#comment-textarea .placeholder',
		'.comment-form-comment textarea',
		'.post-password-form label input[type="password"]',
		'.avada-select .select2-container .select2-choice',
		'.avada-select .select2-container .select2-choice2',
		'select',
		'.main-nav-search-form input',
		'.search-page-search-form input',
		'.chzn-container-single .chzn-single',
		'.chzn-container .chzn-drop',
		'.fusion-body .avada-select-parent select',
		'.select2-container--default .select2-selection--single .select2-selection__rendered',
		'.select2-results__option',
		'#calc_shipping_state_field .select2-selection__placeholder',
		'.searchform .fusion-search-form-content .fusion-search-field input',
		'.fusion-search-form-clean .searchform .fusion-search-form-content .fusion-search-button input[type="submit"]',
	);
	if ( class_exists( 'GFForms' ) ) {
		$elements[] = '.gform_wrapper .gfield input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="file"])';
		$elements[] = '.gform_wrapper .gfield_select[multiple=multiple]';
		$elements[] = '.gform_wrapper .gfield select';
		$elements[] = '.gform_wrapper .gfield textarea';
	}
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = '.wpcf7-form .wpcf7-text';
		$elements[] = '.wpcf7-form .wpcf7-quiz';
		$elements[] = '.wpcf7-form .wpcf7-number';
		$elements[] = '.wpcf7-form textarea';
		$elements[] = '.wpcf7-form .wpcf7-select';
		$elements[] = '.wpcf7-select-parent .select-arrow';
		$elements[] = '.wpcf7-captchar';
		$elements[] = '.wpcf7-form .wpcf7-date';
	}
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '#bbpress-forums .bbp-search-form #bbp_search';
		$elements[] = '.bbp-reply-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form input#bbp_topic_title';
		$elements[] = '.bbp-topic-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form select#bbp_stick_topic_select';
		$elements[] = '.bbp-topic-form select#bbp_topic_status_select';
		$elements[] = '#bbpress-forums div.bbp-the-content-wrapper textarea.bbp-the-content';
		$elements[] = '.bbp-login-form input';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]:focus';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select:focus';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']     = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_text_size' ) );

	$form_base_font_size_value = Fusion_Sanitize::number( Fusion_Sanitize::size( Avada()->settings->get( 'form_text_size' ) ) );
	$form_base_font_size_unit  = Fusion_Sanitize::get_unit( Fusion_Sanitize::size( Avada()->settings->get( 'form_text_size' ) ) );

	$arrow_elements = array(
		'.select-arrow',
		'#wrapper .select-arrow',
		'.fusion-modal-content .select-arrow',
		'.avada-select-parent .select-arrow',
		'.gravity-select-parent .select-arrow',
		'.wpcf7-select-parent .select-arrow',
	);
	$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['font-size'] = ( 0.75 * $form_base_font_size_value ) . $form_base_font_size_unit;

	$elements = array(
		'input#s::-webkit-input-placeholder',
		'#comment-input input::-webkit-input-placeholder',
		'.post-password-form label input[type="password"]::-webkit-input-placeholder',
		'#comment-textarea textarea::-webkit-input-placeholder',
		'.comment-form-comment textarea::-webkit-input-placeholder',
		'.input-text::-webkit-input-placeholder',
		'input::-webkit-input-placeholder',
		'.searchform .s::-webkit-input-placeholder',
	);
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = '.wpcf7-form textarea::-webkit-input-placeholder';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]::-webkit-input-placeholder';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]::-webkit-input-placeholder';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );

	$elements = array(
		'input#s:-moz-placeholder',
		'#comment-input input:-moz-placeholder',
		'.post-password-form label input[type="password"]:-moz-placeholder',
		'#comment-textarea textarea:-moz-placeholder',
		'.comment-form-comment textarea:-moz-placeholder',
		'.input-text:-moz-placeholder',
		'input:-moz-placeholder',
		'.searchform .s:-moz-placeholder',
	);
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = '.wpcf7-form textarea::-moz-input-placeholder';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]:-moz-placeholder';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]:-moz-placeholder';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]:-moz-placeholder';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );

	$elements = array(
		'input#s::-moz-placeholder',
		'#comment-input input::-moz-placeholder',
		'.post-password-form label input[type="password"]::-moz-placeholder',
		'#comment-textarea textarea::-moz-placeholder',
		'.comment-form-comment textarea::-moz-placeholder',
		'.input-text::-moz-placeholder',
		'input::-moz-placeholder',
		'.searchform .s::-moz-placeholder',
	);
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]::-moz-placeholder';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]::-moz-placeholder';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]::-moz-placeholder';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );

	$elements = array(
		'input#s:-ms-input-placeholder',
		'#comment-input input:-ms-input-placeholder',
		'.post-password-form label input[type="password"]::-ms-input-placeholder',
		'#comment-textarea textarea:-ms-input-placeholder',
		'.comment-form-comment textarea:-ms-input-placeholder',
		'.input-text:-ms-input-placeholder',
		'input:-ms-input-placeholder',
		'.searchform .s:-ms-input-placeholder',
	);
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]::-ms-input-placeholder';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]::-ms-input-placeholder';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );

	$elements = array(
		'.input-text',
		'input[type="text"]',
		'input[type="number"]',
		'input[type="email"]',
		'input[type="password"]',
		'textarea',
		'input.s',
		'#comment-input input',
		'#comment-textarea textarea',
		'.comment-form-comment textarea',
		'.post-password-form label input[type="password"]',
		'.gravity-select-parent .select-arrow',
		'.select-arrow',
		'.main-nav-search-form input',
		'.search-page-search-form input',
		'.chzn-container-single .chzn-single',
		'.chzn-container .chzn-drop',
		'.fusion-body .avada-select-parent select',
		'.fusion-body .avada-select-parent .select-arrow',
		'select',
		'.searchform .fusion-search-form-content .fusion-search-field input',
		'.avada-select .select2-container .select2-choice',
		'.avada-select .select2-container .select2-choice .select2-arrow',
		'.avada-select .select2-container .select2-choice2 .select2-arrow',
		'.select2-container--default .select2-selection--single',
		'.select2-container--default .select2-dropdown .select2-search',
		'.select2-container .select2-selection .select2-selection__arrow',
		'.select2-container--default .select2-search--dropdown .select2-search__field',
		'.select2-dropdown',
	);
	if ( defined( 'ICL_SITEPRESS_VERSION' || class_exists( 'SitePress' ) ) ) {
		$elements[] = '#lang_sel_click a.lang_sel_sel';
		$elements[] = '#lang_sel_click ul ul a';
		$elements[] = '#lang_sel_click ul ul a:visited';
		$elements[] = '#lang_sel_click a';
		$elements[] = '#lang_sel_click a:visited';
	}
	if ( class_exists( 'GFForms' ) ) {
		$elements[] = '.gform_wrapper .gfield input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="file"])';
		$elements[] = '.gform_wrapper .gfield_select[multiple=multiple]';
		$elements[] = '.gform_wrapper .gfield select';
		$elements[] = '.gform_wrapper .gfield textarea';
	}
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = '.wpcf7-form .wpcf7-text';
		$elements[] = '.wpcf7-form .wpcf7-quiz';
		$elements[] = '.wpcf7-form .wpcf7-number';
		$elements[] = '.wpcf7-form textarea';
		$elements[] = '.wpcf7-form .wpcf7-select';
		$elements[] = '.wpcf7-select-parent .select-arrow';
		$elements[] = '.wpcf7-captchar';
		$elements[] = '.wpcf7-form .wpcf7-date';
	}
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '#bbpress-forums .quicktags-toolbar';
		$elements[] = '#bbpress-forums .bbp-search-form #bbp_search';
		$elements[] = '.bbp-reply-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form input#bbp_topic_title';
		$elements[] = '.bbp-topic-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form select#bbp_stick_topic_select';
		$elements[] = '.bbp-topic-form select#bbp_topic_status_select';
		$elements[] = '#bbpress-forums div.bbp-the-content-wrapper textarea.bbp-the-content';
		$elements[] = '#wp-bbp_topic_content-editor-container';
		$elements[] = '#wp-bbp_reply_content-editor-container';
		$elements[] = '.bbp-login-form input';
		$elements[] = '#bbpress-forums .wp-editor-container';
		$elements[] = '#wp-bbp_topic_content-editor-container';
		$elements[] = '#wp-bbp_reply_content-editor-container';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce-checkout .select2-drop-active';
		$elements[] = '#calc_shipping_state_field .select2-selection__arrow';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]:focus';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select:focus';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );

	$css['global']['.avada-contact-form .grecaptcha-badge']['box-shadow'] = '0 0 3px ' . Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) ) . '!important';

	$active_elements = array();
	foreach ( $elements as $element ) {
		$active_elements[] = $element . ':focus ';
	}
	$active_elements[] = 'select:focus + .select-arrow';
	$active_elements[] = 'select:focus + .select2-arrow';
	$active_elements[] = '.select2-container--default.select2-container--open .select2-selection--single .select2-selection__rendered';
	$active_elements[] = '.select2-container--default.select2-container--open .select2-dropdown';
	$active_elements[] = '.select2-container--default.select2-container--open .select2-dropdown .select2-search';
	$active_elements[] = '.select2-container--default.select2-container--open .select2-search--dropdown .select2-search__field';
	$active_elements[] = '.select2-container--default.select2-container--open .select2-selection--single';
	$active_elements[] = '.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow';
	$active_elements[] = '.select2-container .select2-selection:focus .select2-selection__arrow';

	$css['global'][ $dynamic_css_helpers->implode( $active_elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_focus_border_color' ) );

	$active_elements = array(
		'select:focus + .select-arrow',
		'select:focus + .select2-arrow',
	);
	$css['global'][ $dynamic_css_helpers->implode( $active_elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_focus_border_color' ) );

	$elements[] = '.fusion-search-form-classic .searchform .fusion-search-form-content';
	$elements[] = 'input[type="tel"]';

	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.quantity';
		$elements[] = '.fusion-body .tribe-events-tickets .quantity';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-radius'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_border_radius' ), 'px' );

	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.quantity .qty';
		$elements[] = '.quantity .tribe-ticket-quantity';
	}

	$elements[] = '.select2-container .select2-selection .select2-selection__arrow';

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width' ), 'px' );

	$arrow_elements = array(
		'.select-arrow',
		'.avada-select-parent .select-arrow',
		'.gravity-select-parent .select-arrow',
		'.wpcf7-select-parent .select-arrow',
	);
	$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width' ), 'px' );

	$form_elements = array(
		'.fusion-search-form-classic #bbpress-forums .bbp-search-form #bbp_search',
		'.fusion-search-form-classic .searchform .fusion-search-form-content .fusion-search-field input',
	);

	if ( is_rtl() ) {
		$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['border-top-right-radius']    = '0';
		$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['border-bottom-right-radius'] = '0';
		$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['left']                       = Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width' ), 'px' );

		$css['global'][ $dynamic_css_helpers->implode( $form_elements ) ]['border-left']               = 'none';
		$css['global'][ $dynamic_css_helpers->implode( $form_elements ) ]['border-top-left-radius']    = '0';
		$css['global'][ $dynamic_css_helpers->implode( $form_elements ) ]['border-bottom-left-radius'] = '0';
	} else {
		$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['border-top-left-radius']    = '0';
		$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['border-bottom-left-radius'] = '0';
		$css['global'][ $dynamic_css_helpers->implode( $arrow_elements ) ]['right']                     = Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width' ), 'px' );

		$css['global'][ $dynamic_css_helpers->implode( $form_elements ) ]['border-right']               = 'none';
		$css['global'][ $dynamic_css_helpers->implode( $form_elements ) ]['border-top-right-radius']    = '0';
		$css['global'][ $dynamic_css_helpers->implode( $form_elements ) ]['border-bottom-right-radius'] = '0';
	}

	$css['global']['.select2-container--default .select2-selection--single .select2-selection__arrow b']['border-top-color']                                  = Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );
	$css['global']['.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b']['border-bottom-color']       = Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );

	$css['global']['.select2-container--default .select2-selection--single:focus .select2-selection__arrow b']['border-top-color']                            = Fusion_Sanitize::color( Avada()->settings->get( 'form_focus_border_color' ) );
	$css['global']['.select2-container--default.select2-container--open .select2-selection--single:focus .select2-selection__arrow b']['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_focus_border_color' ) );
	$css['global']['.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b']['border-bottom-color']       = Fusion_Sanitize::color( Avada()->settings->get( 'form_focus_border_color' ) );

	$elements = array(
		'.input-text:not(textarea)',
		'input[type="text"]',
		'input[type="number"]',
		'input[type="email"]',
		'input[type="password"]',
		'input.s',
		'#comment-input input',
		'.post-password-form label input[type="password"]',
		'.main-nav-search-form input',
		'.search-page-search-form input',
		'.chzn-container-single .chzn-single',
		'.chzn-container .chzn-drop',
		'select',
		'.searchform .fusion-search-form-content .fusion-search-field input',
		'.fusion-body .avada-select-parent select',
		'.avada-select .select2-container .select2-choice',
		'.select2-container--default .select2-selection--single',
		'.select2-container--default .select2-selection--single .select2-selection__arrow',
		'.fusion-login-box .fusion-login-form input[type=text]',
		'.fusion-login-box .fusion-login-form input[type=password]',
	);

	if ( class_exists( 'GFForms' ) ) {
		$elements[] = '.gform_wrapper .gfield input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="file"])';
		$elements[] = '.gform_wrapper .gfield_select[multiple=multiple]';
		$elements[] = '.gform_wrapper .gfield .gravity-select-parent select';
		$elements[] = '.gform_wrapper .gfield select';
	}
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = '.wpcf7-form .wpcf7-text';
		$elements[] = '.wpcf7-form .wpcf7-quiz';
		$elements[] = '.wpcf7-form .wpcf7-number';
		$elements[] = '.wpcf7-form .wpcf7-select';
		$elements[] = '.wpcf7-captchar';
		$elements[] = '.wpcf7-form .wpcf7-date';
	}
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '#bbpress-forums .bbp-search-form #bbp_search';
		$elements[] = '.bbp-reply-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form input#bbp_topic_title';
		$elements[] = '.bbp-topic-form input#bbp_topic_tags';
		$elements[] = '.bbp-topic-form select#bbp_stick_topic_select';
		$elements[] = '.bbp-topic-form select#bbp_topic_status_select';
		$elements[] = '.bbp-login-form input';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.avada-shipping-calculator-form .avada-select-parent select';
		$elements[] = '.shipping-calculator-form .avada-select-parent select';
		$elements[] = '.cart-collaterals .form-row input';
		$elements[] = '.cart-collaterals .avada-select-parent input';
		$elements[] = '.cart-collaterals .woocommerce-shipping-calculator #calc_shipping_postcode';
		$elements[] = '.coupon .input-text';
		$elements[] = '.checkout .input-text:not(textarea)';
		$elements[] = '.woocommerce-checkout .select2-drop-active';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = '#tribe-bar-form input[type=text]';
		$elements[] = '.tribe-bar-disabled #tribe-bar-form .tribe-bar-filters input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields input[type=text]:focus';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select';
		$elements[] = '.page-tribe-attendee-registration .tribe-block__tickets__item__attendee__fields select:focus';
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']         = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-top']    = '0';
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-bottom'] = '0';

	$elements = array(
		'.avada-select .select2-container .select2-choice .select2-arrow',
		'.avada-select .select2-container .select2-choice2 .select2-arrow',
		'.searchform .fusion-search-form-content .fusion-search-button input[type="submit"]',

	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']      = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']       = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );

	$elements = array(
		'.fusion-search-form-clean .searchform .fusion-search-form-content .fusion-search-field input',
		'.fusion-search-form-clean #bbpress-forums .bbp-search-form #bbp_search',
	);

	if ( is_rtl() ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );
	} else {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );
	}

	$elements = array(
		'.select2-container--default .select2-selection--single .select2-selection__rendered',
		'.select2-container .select2-choice > .select2-chosen',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) );

	$elements = array( '.select-arrow', '.select2-arrow' );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );

	if ( class_exists( 'GFForms' ) ) {
		$css['global']['.gfield_time_ampm .gravity-select-parent select']['min-width'] = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) ) . ' * 2) !important';
	}

	if ( 35 < Fusion_Sanitize::units_to_px( Avada()->settings->get( 'form_input_height' ) ) ) {
		$css['global']['.fusion-main-menu .fusion-main-menu-search .fusion-custom-menu-item-contents']['width'] = 'calc(250px + 1.43 * ' . Avada()->settings->get( 'form_input_height' ) . ')';
	}

	if ( ! Avada()->settings->get( 'avada_styles_dropdowns' ) ) {

		$css['global']['select']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );
		$css['global']['select']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );
		$css['global']['select']['font-size']        = Fusion_Sanitize::size( Avada()->settings->get( 'form_text_size' ) );
		$css['global']['select']['border']           = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );
		$css['global']['select']['height']           = '35px';
		$css['global']['select']['text-indent']      = '5px';
		$css['global']['select']['width']            = '100%';

		$css['global']['select::-webkit-input-placeholder']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );
		$css['global']['select:-moz-placeholder']['color']           = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );
	}

	$css['global']['.fusion-page-title-bar .fusion-page-title-row h1']['font-size']   = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_font_size' ) );
	$css['global']['.fusion-page-title-bar .fusion-page-title-row h1']['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_line_height' ) );

	$css['global']['.fusion-page-title-bar h3']['font-size']   = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_subheader_font_size' ) );
	$css['global']['.fusion-page-title-bar h3']['line-height'] = Fusion_Sanitize::add_css_values( array( Fusion_Sanitize::size( Avada()->settings->get( 'page_title_subheader_font_size' ) ), '12px' ) );

	$single_sidebar_gutter = Avada()->settings->get( 'sidebar_gutter' );
	$dual_sidebar_gutter   = Avada()->settings->get( 'dual_sidebar_gutter' );

	/**
	 * Portfolio Styling Options
	 */

	// Portfolio Text Alignment / portfolio_text_alignment.
	$css['global']['.fusion-portfolio-content-wrapper .fusion-portfolio-content']['text-align'] = Avada()->settings->get( 'portfolio_archive_text_alignment' );

	// Portfolio Text Layout Padding / portfolio_layout_padding.
	$padding  = Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_archive_layout_padding', 'top' ) );
	$padding .= ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_archive_layout_padding', 'right' ) );
	$padding .= ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_archive_layout_padding', 'bottom' ) );
	$padding .= ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_archive_layout_padding', 'left' ) );
	$css['global']['.fusion-portfolio-boxed .fusion-portfolio-content']['padding'] = $padding;

	$padding  = Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_layout_padding', 'top' ) );
	$padding .= ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_layout_padding', 'right' ) );
	$padding .= ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_layout_padding', 'bottom' ) );
	$padding .= ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_layout_padding', 'left' ) );
	$css['global']['.fusion-portfolio-boxed.fusion-portfolio-element .fusion-portfolio-content']['padding'] = $padding;

	$css['global']['.fusion-portfolio-content .fusion-portfolio-meta']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'portfolio_meta_font_size' ) );

	/**
	 * Single-sidebar Layouts
	 */
	$sidebar_width = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_width' ) );
	if ( ! Fusion_Sanitize::get_unit( $sidebar_width ) ) {
		$sidebar_width = ( 100 > intval( $sidebar_width ) ) ? $sidebar_width . '%' : $sidebar_width . 'px';
	}
	$css['global']['body.has-sidebar #content']['width']       = Fusion_Sanitize::add_css_values( array( '100%', '-' . $sidebar_width, '-' . $single_sidebar_gutter ) );
	$css['global']['body.has-sidebar #main .sidebar']['width'] = $sidebar_width;
	/**
	 * Double-Sidebar layouts
	 */
	$sidebar_2_1_width = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_2_1_width' ) );
	if ( ! Fusion_Sanitize::get_unit( $sidebar_2_1_width ) ) {
		$sidebar_2_1_width = ( 100 > intval( $sidebar_2_1_width ) ) ? $sidebar_2_1_width . '%' : $sidebar_2_1_width . 'px';
	}
	$sidebar_2_2_width = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_2_2_width' ) );
	if ( ! Fusion_Sanitize::get_unit( $sidebar_2_2_width ) ) {
		$sidebar_2_2_width = ( 100 > intval( $sidebar_2_2_width ) ) ? $sidebar_2_2_width . '%' : $sidebar_2_2_width . 'px';
	}
	$css['global']['body.has-sidebar.double-sidebars #content']['width']               = Fusion_Sanitize::add_css_values( array( '100%', '-' . $sidebar_2_1_width, '-' . $sidebar_2_2_width, '-' . $dual_sidebar_gutter, '-' . $dual_sidebar_gutter ) );
	$css['global']['body.has-sidebar.double-sidebars #content']['margin-left']         = Fusion_Sanitize::add_css_values( array( $sidebar_2_1_width, $dual_sidebar_gutter ) );
	$css['global']['body.has-sidebar.double-sidebars #main #sidebar']['width']         = $sidebar_2_1_width;
	$css['global']['body.has-sidebar.double-sidebars #main #sidebar']['margin-left']   = Fusion_Sanitize::add_css_values( array( $dual_sidebar_gutter, '-100%', $sidebar_2_2_width ) );
	$css['global']['body.has-sidebar.double-sidebars #main #sidebar-2']['width']       = $sidebar_2_2_width;
	$css['global']['body.has-sidebar.double-sidebars #main #sidebar-2']['margin-left'] = $dual_sidebar_gutter;

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$sidebar_width = Fusion_Sanitize::size( Avada()->settings->get( 'ec_sidebar_width' ) );
		if ( ! Fusion_Sanitize::get_unit( $sidebar_width ) ) {
			$sidebar_width = ( 100 > intval( $sidebar_width ) ) ? $sidebar_width . '%' : $sidebar_width . 'px';
		}
		if ( '100-width.php' !== tribe_get_option( 'tribeEventsTemplate', 'default' ) ) {
			if ( 'sidebar' === Avada()->settings->get( 'ec_meta_layout' ) ) {
				$css['global']['.single-tribe_events #content']['width']       = Fusion_Sanitize::add_css_values( array( '100%', '-' . $sidebar_width, '-' . $single_sidebar_gutter ) );
				$css['global']['.single-tribe_events #main .sidebar']['width'] = $sidebar_width;
			} else {
				$css['global']['.single-tribe_events #content']['width'] = '100%';
			}
		}
		/**
		 * Single-sidebar Layouts
		 */
		$css['global']['body.has-sidebar.single-tribe_events #content']['width']       = Fusion_Sanitize::add_css_values( array( '100%', '-' . $sidebar_width, '-' . $single_sidebar_gutter ) );
		$css['global']['body.has-sidebar.single-tribe_events #main .sidebar']['width'] = $sidebar_width;
		/**
		 * Double-Sidebar layouts
		 */
		$sidebar_2_1_width = Fusion_Sanitize::size( Avada()->settings->get( 'ec_sidebar_2_1_width' ) );
		if ( ! Fusion_Sanitize::get_unit( $sidebar_2_1_width ) ) {
			$sidebar_2_1_width = ( 100 > intval( $sidebar_2_1_width ) ) ? $sidebar_2_1_width . '%' : $sidebar_2_1_width . 'px';
		}
		$sidebar_2_2_width = Fusion_Sanitize::size( Avada()->settings->get( 'ec_sidebar_2_2_width' ) );
		if ( ! Fusion_Sanitize::get_unit( $sidebar_2_2_width ) ) {
			$sidebar_2_2_width = ( 100 > intval( $sidebar_2_2_width ) ) ? $sidebar_2_2_width . '%' : $sidebar_2_2_width . 'px';
		}
		$css['global']['body.has-sidebar.double-sidebars.single-tribe_events #content']['width']               = Fusion_Sanitize::add_css_values( array( '100%', '-' . $sidebar_2_1_width, '-' . $sidebar_2_2_width, '-' . $dual_sidebar_gutter, '-' . $dual_sidebar_gutter ) );
		$css['global']['body.has-sidebar.double-sidebars.single-tribe_events #content']['margin-left']         = Fusion_Sanitize::add_css_values( array( $sidebar_2_1_width, $dual_sidebar_gutter ) );
		$css['global']['body.has-sidebar.double-sidebars.single-tribe_events #main #sidebar']['width']         = $sidebar_2_1_width;
		$css['global']['body.has-sidebar.double-sidebars.single-tribe_events #main #sidebar']['margin-left']   = Fusion_Sanitize::add_css_values( array( $dual_sidebar_gutter, '-100%', $sidebar_2_2_width ) );
		$css['global']['body.has-sidebar.double-sidebars.single-tribe_events #main #sidebar-2']['width']       = $sidebar_2_2_width;
		$css['global']['body.has-sidebar.double-sidebars.single-tribe_events #main #sidebar-2']['margin-left'] = $dual_sidebar_gutter;
	}

	$elements = array(
		'#main .sidebar:not( .fusion-sticky-sidebar )',
		'#main .fusion-sticky-sidebar .fusion-sidebar-inner-content',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'sidebar_bg_color' ) );
	$css['global']['#main .sidebar']['padding']                                      = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_padding' ) );

	$sticky_padding = str_replace( '%', 'vw', Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_padding' ) ) );
	$css['global']['#main .sidebar.fusion-sticky-sidebar .fusion-sidebar-inner-content']['padding'] = $sticky_padding;

	if ( class_exists( 'Tribe__Events__Main' ) ) {

		$elements = array(
			'.single-tribe_events #main .fusion-content-widget-area:not( .fusion-sticky-sidebar )',
			'.single-tribe_events #main .fusion-sticky-sidebar .fusion-sidebar-inner-content',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color']    = Fusion_Sanitize::color( Avada()->settings->get( 'ec_sidebar_bg_color' ) );
		$css['global']['.single-tribe_events #main .fusion-content-widget-area']['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'ec_sidebar_padding' ) );

		$sticky_padding = str_replace( '%', 'vw', Fusion_Sanitize::size( Avada()->settings->get( 'ec_sidebar_padding' ) ) );
		$css['global']['.single-tribe_events #main .sidebar.fusion-sticky-sidebar .fusion-sidebar-inner-content']['padding'] = $sticky_padding;
	}

	$css['global']['.fusion-single-sharing-box']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'social_bg_color' ) );
	if ( 'transparent' === Avada()->settings->get( 'social_bg_color' ) || 0 === Fusion_Color::new_color( Avada()->settings->get( 'social_bg_color' ) )->alpha ) {
		$css['global']['.fusion-single-sharing-box']['padding'] = '0';
	}

	// Blog Archive Masonry content positioning.
	$masonry_content_position = Avada()->settings->get( 'blog_archive_grid_column_spacing' ) / 2;
	$masonry_color            = Fusion_Color::new_color( Avada()->settings->get( 'timeline_bg_color' ) );
	$masonry_css              = $masonry_color->to_css( 'rgba' );
	if ( 0 === $masonry_color->alpha ) {
		$masonry_css = $masonry_color->to_css( 'rgb' );
	}
	$css['global']['.fusion-blog-layout-masonry .fusion-post-content-wrapper']['background-color'] = $masonry_css;

	if ( 'Grid' === Avada()->settings->get( 'blog_layout' ) || 'Timeline' === Avada()->settings->get( 'blog_layout' ) || 'masonry' === Avada()->settings->get( 'blog_layout' ) || 'Grid' === Avada()->settings->get( 'blog_archive_layout' ) || 'Timeline' === Avada()->settings->get( 'blog_archive_layout' ) || 'masonry' === Avada()->settings->get( 'blog_archive_layout' ) ) {
		$elements = array(
			'.fusion-blog-archive .fusion-blog-layout-grid .post .fusion-post-content-wrapper',
			'.fusion-blog-archive .fusion-blog-layout-timeline .post .fusion-post-content-wrapper',
			'.fusion-blog-archive .fusion-blog-layout-masonry .post .fusion-post-content-wrapper',
		);
		$padding  = implode( ' ', Avada()->settings->get( 'blog_archive_grid_padding' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = $padding;
	}

	// Portfolio Archive Masonry content positioning.
	$masonry_content_position = Avada()->settings->get( 'portfolio_archive_column_spacing' ) / 2;
	if ( $masonry_content_position ) {
		$margin = ( -1 ) * $masonry_content_position;
		$css['global']['.fusion-portfolio-layout-masonry']['margin'] = $margin . 'px;';
	}
	if ( 'boxed' !== Avada()->settings->get( 'portfolio_archive_text_layout' ) ) {
		$css['global']['.fusion-portfolio-layout-masonry .fusion-portfolio-content']['padding'] = '20px 0';
	}
	$css['global']['.fusion-portfolio-layout-masonry .fusion-portfolio-content']['background-color'] = $masonry_css;

	$elements = array(
		'.fusion-blog-layout-grid .post .fusion-post-wrapper',
		'.fusion-blog-layout-timeline .post',
		'.fusion-portfolio.fusion-portfolio-boxed .fusion-portfolio-content-wrapper',
		'.products li.product',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'timeline_bg_color' ) );

	$elements = array(
		'.fusion-blog-layout-grid .post .flexslider',
		'.fusion-blog-layout-grid .post .fusion-post-wrapper',
		'.products li',
		'.product-buttons',
		'.product-buttons-container',
		'.fusion-blog-layout-timeline .fusion-timeline-line',
		'.fusion-blog-timeline-layout .post',
		'.fusion-blog-timeline-layout .post .flexslider',
		'.fusion-blog-layout-timeline .post',
		'.fusion-portfolio.fusion-portfolio-boxed .fusion-portfolio-content-wrapper',
		'.fusion-blog-layout-timeline .post .flexslider',
		'.fusion-blog-layout-timeline .fusion-timeline-date',
		'.fusion-blog-layout-timeline .fusion-timeline-arrow',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'timeline_color' ) );

	if ( 'transparent' === Fusion_Sanitize::color( Avada()->settings->get( 'timeline_color' ) ) || 0 === Fusion_Color::new_color( Avada()->settings->get( 'timeline_color' ) )->alpha ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border'] = 'none';
	}

	$elements = array(
		'.fusion-blog-layout-grid .post .fusion-content-sep',
		'.fusion-blog-timeline-layout .post .fusion-content-sep',
		'.fusion-blog-layout-timeline .post .fusion-content-sep',
		'.fusion-portfolio.fusion-portfolio-boxed .fusion-content-sep',
		'.fusion-body .product .fusion-content-sep',
	);

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'grid_separator_color' ) );

	$elements = array(
		'.fusion-blog-layout-timeline .fusion-timeline-circle',
		'.fusion-blog-layout-timeline .fusion-timeline-date',
		'.fusion-blog-timeline-layout .fusion-timeline-circle',
		'.fusion-blog-timeline-layout .fusion-timeline-date',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'timeline_color' ) );

	$elements = array(
		'.fusion-timeline-icon',
		'.fusion-timeline-arrow',
		'.fusion-blog-timeline-layout .fusion-timeline-icon',
		'.fusion-blog-timeline-layout .fusion-timeline-arrow',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'timeline_color' ) );

	$elements = array(
		'div.indicator-hint',
	);
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '#bbpress-forums li.bbp-header';
		$elements[] = '#bbpress-forums div.bbp-reply-header';
		$elements[] = '#bbpress-forums #bbp-single-user-details #bbp-user-navigation li.current a';
		$elements[] = 'div.bbp-template-notice';
		$elements[] = '#bbpress-forums .bbp-search-results .bbp-forum-header';
		$elements[] = '#bbpress-forums .bbp-search-results .bbp-topic-header';

	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = Fusion_Sanitize::color( Avada()->settings->get( 'bbp_forum_header_bg' ) );

	if ( class_exists( 'bbPress' ) ) {
		$elements = array(
			'#bbpress-forums .forum-titles li',
			'span.bbp-admin-links',
			'span.bbp-admin-links a',
			'.bbp-forum-header a.bbp-forum-permalink',
			'.bbp-reply-header a.bbp-reply-permalink',
			'.bbp-topic-header a.bbp-topic-permalink',
			'.bbp-search-results .bbp-forum-header .bbp-forum-title h3',
			'.bbp-search-results .bbp-forum-header .bbp-forum-title a',
			'.bbp-search-results .bbp-forum-header .bbp-forum-post-date',
			'.bbp-search-results .bbp-forum-header .bbp-forum-permalink:before',
			'.bbp-search-results .bbp-reply-header .bbp-topic-title-meta',
			'.bbp-search-results .bbp-reply-header .bbp-topic-title-meta a',
			'.bbp-search-results .bbp-reply-header .bbp-reply-to',
			'.bbp-search-results .bbp-reply-header .bbp-reply-to a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'bbp_forum_header_font_color' ) );

		$css['global']['#bbpress-forums .bbp-replies div.even']['background'] = 'transparent';
	}
	$elements = array( 'div.indicator-hint' );
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = '#bbpress-forums ul.bbp-lead-topic';
		$elements[] = '#bbpress-forums ul.bbp-topics';
		$elements[] = '#bbpress-forums ul.bbp-forums';
		$elements[] = '#bbpress-forums ul.bbp-replies';
		$elements[] = '#bbpress-forums ul.bbp-search-results';
		$elements[] = '#bbpress-forums li.bbp-body ul.forum';
		$elements[] = '#bbpress-forums li.bbp-body ul.topic';
		$elements[] = '#bbpress-forums div.bbp-reply-content';
		$elements[] = '#bbpress-forums div.bbp-reply-header';
		$elements[] = '#bbpress-forums div.bbp-reply-author .bbp-reply-post-date';
		$elements[] = '#bbpress-forums div.bbp-topic-tags a';
		$elements[] = '#bbpress-forums #bbp-single-user-details';
		$elements[] = 'div.bbp-template-notice';
		$elements[] = '.bbp-arrow';
		$elements[] = '#bbpress-forums .bbp-search-results .bbp-forum-content';
		$elements[] = '#bbpress-forums .bbp-search-results .bbp-topic-content';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'bbp_forum_border_color' ) );

	if ( class_exists( 'bbPress' ) ) {

		$elements = array(
			'.bbp-topics-front ul.super-sticky',
			'.bbp-topics ul.super-sticky',
			'.bbp-topics ul.sticky',
			'.bbp-forum-content ul.sticky',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) ) )->get_new( 'alpha', '0.2' )->to_css( 'rgba' ) . '!important';
	}

	if ( 'Dark' === Avada()->settings->get( 'scheme_type' ) ) {

		$css['global']['.fusion-rollover .price .amount']['color'] = '#333333';
		$css['global']['.error_page .oops']['color']               = '#2F2F30';
		$css['global']['.meta li']['border-color']                 = Fusion_Sanitize::color( Avada()->settings->get( 'body_typography', 'color' ) );

		if ( class_exists( 'bbPress' ) ) {
			$elements = array( '.bbp-arrow', '#bbpress-forums .quicktags-toolbar' );
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'content_bg_color' ) );
		}

		$css['global']['#toTop']['background-color'] = '#111111';

		$css['global']['.chzn-container-single .chzn-single']['background-image'] = 'none';
		$css['global']['.chzn-container-single .chzn-single']['box-shadow']       = 'none';

		$elements = array( '.catalog-ordering a', '.order-dropdown > li:after', '.order-dropdown ul li a' );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );

		$elements = array(
			'.order-dropdown li',
			'.order-dropdown .current-li',
			'.order-dropdown > li:after',
			'.order-dropdown ul li a',
			'.catalog-ordering .order li a',
			'.order-dropdown li',
			'.order-dropdown .current-li',
			'.order-dropdown ul',
			'.order-dropdown ul li a',
			'.catalog-ordering .order li a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) );

		$elements = array(
			'.order-dropdown li:hover',
			'.order-dropdown .current-li:hover',
			'.order-dropdown ul li a:hover',
			'.catalog-ordering .order li a:hover',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = '#29292A';

		if ( class_exists( 'bbPress' ) ) {

			$elements = array(
				'.bbp-topics-front ul.super-sticky a',
				'.bbp-topics ul.super-sticky a',
				'.bbp-topics ul.sticky a',
				'.bbp-forum-content ul.sticky a',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = '#FFFFFF';

		}

		$elements = array(
			'.pagination-prev:before',
			'.pagination-next:after',
		);
		if ( class_exists( 'WooCommerce' ) ) {
			$elements[] = '.woocommerce-pagination .prev:before';
			$elements[] = '.woocommerce-pagination .next:after';
		}
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = '#747474';

		$elements = array( '.table-1 table', '.tkt-slctr-tbl-wrap-dv table' );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = '#313132';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['box-shadow']       = '0 1px 3px rgba(0, 0, 0, 0.08), inset 0 0 0 1px rgba(62, 62, 62, 0.5)';

		$elements = array(
			'.table-1 table th',
			'.tkt-slctr-tbl-wrap-dv table th',
			'.table-1 tbody tr:nth-child(2n)',
			'.tkt-slctr-tbl-wrap-dv tbody tr:nth-child(2n)',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = '#212122';

	}

	$blog_archive_grid_column_spacing      = Avada()->settings->get( 'blog_archive_grid_column_spacing' );
	$blog_archive_grid_column_spacing_half = intval( $blog_archive_grid_column_spacing / 2 );
	if ( $blog_archive_grid_column_spacing || '0' === $blog_archive_grid_column_spacing ) {

		$css['global']['#posts-container .fusion-blog-layout-grid']['margin'] = '-' . $blog_archive_grid_column_spacing_half . 'px -' . $blog_archive_grid_column_spacing_half . 'px 0 -' . $blog_archive_grid_column_spacing_half . 'px';

		$css['global']['#posts-container .fusion-blog-layout-grid .fusion-post-grid']['padding'] = $blog_archive_grid_column_spacing_half . 'px';

	}

	$css['global']['.quicktags-toolbar input']['background'][]     = 'linear-gradient(to top, ' . Fusion_Sanitize::color( Avada()->settings->get( 'content_bg_color' ) ) . ', ' . Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) ) . ' ) #3E3E3E';
	$css['global']['.quicktags-toolbar input']['background-image'] = '-webkit-gradient( linear, left top, left bottom, color-stop(0, ' . Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) ) . '), color-stop(1, ' . Fusion_Sanitize::color( Avada()->settings->get( 'content_bg_color' ) ) . '))';
	$css['global']['.quicktags-toolbar input']['filter']           = 'progid:DXImageTransform.Microsoft.gradient(startColorstr=' . Fusion_Color::new_color( Avada()->settings->get( 'form_bg_color' ) )->to_css( 'hex' ) . ', endColorstr=' . Fusion_Color::new_color( Avada()->settings->get( 'content_bg_color' ) )->to_css( 'hex' ) . '), progid: DXImageTransform.Microsoft.Alpha(Opacity=0)';
	$css['global']['.quicktags-toolbar input']['border']           = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) );
	$css['global']['.quicktags-toolbar input']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) );
	$css['global']['.quicktags-toolbar input']['font-size']        = Fusion_Sanitize::size( Avada()->settings->get( 'form_text_size' ) );

	$css['global']['.quicktags-toolbar input:hover']['background'] = Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) );

	if ( ! Avada()->settings->get( 'image_rollover' ) ) {
		$css['global']['.fusion-rollover']['display'] = 'none';

		$css['global']['.fusion-masonry-element-container.fusion-image-wrapper > a']['position'] = 'absolute';
		$css['global']['.fusion-masonry-element-container.fusion-image-wrapper > a']['top']      = '0';
		$css['global']['.fusion-masonry-element-container.fusion-image-wrapper > a']['bottom']   = '0';
		$css['global']['.fusion-masonry-element-container.fusion-image-wrapper > a']['left']     = '0';
		$css['global']['.fusion-masonry-element-container.fusion-image-wrapper > a']['right']    = '0';
	}

	if ( 'left' !== Avada()->settings->get( 'image_rollover_direction' ) ) {

		switch ( Avada()->settings->get( 'image_rollover_direction' ) ) {

			case 'fade':
				$image_rollover_direction_value       = 'translateY(0%)';
				$image_rollover_direction_hover_value = '';

				$css['global']['.fusion-image-wrapper .fusion-rollover']['transition'] = 'opacity 0.5s ease-in-out';
				break;
			case 'right':
				$image_rollover_direction_value       = 'translateX(100%)';
				$image_rollover_direction_hover_value = '';
				break;
			case 'bottom':
				$image_rollover_direction_value       = 'translateY(100%)';
				$image_rollover_direction_hover_value = 'translateY(0%)';
				break;
			case 'top':
				$image_rollover_direction_value       = 'translateY(-100%)';
				$image_rollover_direction_hover_value = 'translateY(0%)';
				break;
			case 'center_horiz':
				$image_rollover_direction_value       = 'scaleX(0)';
				$image_rollover_direction_hover_value = 'scaleX(1)';
				break;
			case 'center_vertical':
				$image_rollover_direction_value       = 'scaleY(0)';
				$image_rollover_direction_hover_value = 'scaleY(1)';
				break;
			default:
				$image_rollover_direction_value       = 'scaleY(0)';
				$image_rollover_direction_hover_value = 'scaleY(1)';
				break;
		}

		$css['global']['.fusion-image-wrapper .fusion-rollover']['transform'] = $image_rollover_direction_value;

		if ( '' != $image_rollover_direction_hover_value ) {
			$css['global']['.fusion-image-wrapper:hover .fusion-rollover']['transform'] = $image_rollover_direction_hover_value;
		}
	}

	$css['global']['.ei-slider']['width']  = Fusion_Sanitize::size( Avada()->settings->get( 'tfes_dimensions', 'width' ) );
	$css['global']['.ei-slider']['height'] = Fusion_Sanitize::size( Avada()->settings->get( 'tfes_dimensions', 'height' ) );

	if ( class_exists( 'WooCommerce' ) ) {
		if ( Avada()->settings->get( 'woocommerce_one_page_checkout' ) ) {

			$elements = array(
				'.woocommerce .checkout #customer_details .col-1',
				'.woocommerce .checkout #customer_details .col-2',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['box-sizing']    = 'border-box';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border']        = '1px solid';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['overflow']      = 'hidden';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding']       = '30px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom'] = '30px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['float']         = 'left';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']         = '48%';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right']  = '4%';

			if ( is_rtl() ) {

				$elements = array(
					'.rtl .woocommerce form.checkout #customer_details .col-1',
					'.rtl .woocommerce form.checkout #customer_details .col-2',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['float'] = 'right';

				$css['global']['.rtl .woocommerce form.checkout #customer_details .col-1']['margin-left']  = '4%';
				$css['global']['.rtl .woocommerce form.checkout #customer_details .col-1']['margin-right'] = 0;

			}

			$css['global']['.woocommerce form.checkout #customer_details div:last-child']['margin-right'] = '0';

			$css['global']['.woocommerce form.checkout .avada-checkout-no-shipping #customer_details .col-1']['width']        = '100%';
			$css['global']['.woocommerce form.checkout .avada-checkout-no-shipping #customer_details .col-1']['margin-right'] = '0';
			$css['global']['.woocommerce form.checkout .avada-checkout-no-shipping #customer_details .col-2']['display']      = 'none';

		} else {

			$elements = array(
				'.woocommerce form.checkout .col-2',
				'.woocommerce form.checkout #order_review_heading',
				'.woocommerce form.checkout #order_review',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';

		}
	}

	$po_page_title_100_width = get_post_meta( $c_page_id, 'pyre_page_title_100_width', true );
	if ( 'yes' === $po_page_title_100_width || ( 'no' !== $po_page_title_100_width && Avada()->settings->get( 'page_title_100_width' ) ) ) {
		$css['global']['.layout-wide-mode .fusion-page-title-row']['max-width'] = '100%';

		if ( Avada()->settings->get( 'header_100_width' ) ) {
			$css['global']['.layout-wide-mode .fusion-page-title-row']['padding-left']  = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'left' ) );
			$css['global']['.layout-wide-mode .fusion-page-title-row']['padding-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'right' ) );
		}
	}

	$elements = array(
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-link',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-gallery',
	);
	if ( ! Avada()->settings->get( 'icon_circle_image_rollover' ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'transparent';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']            = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'image_rollover_icon_size' ) ) . ' * 1.5)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']           = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'image_rollover_icon_size' ) ) . ' * 1.5)';
	} else {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']  = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'image_rollover_icon_size' ) ) . ' * 2.41)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height'] = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'image_rollover_icon_size' ) ) . ' * 2.41)';
	}

	$elements = array(
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-link:before',
		'.fusion-image-wrapper .fusion-rollover .fusion-rollover-gallery:before',
	);
	if ( Avada()->settings->get( 'image_rollover_icon_size' ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'image_rollover_icon_size' ) );
		if ( Avada()->settings->get( 'icon_circle_image_rollover' ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = '2.41';
		}
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'image_rollover_icon_color' ) );

	/**
	 * Headings.
	 */

	// H1.
	if ( isset( $h1_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'h1_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'h1_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'h1_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'h1_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'h1_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $h1_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'h1_typography', 'font-size' ) );
	}
	if ( isset( $h1_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h1_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'h1_typography', 'color' ) );
	}

	// H2.
	if ( isset( $h2_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'h2_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'h2_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'h2_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'h2_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'h2_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $h2_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'h2_typography', 'font-size' ) );
	}
	if ( isset( $h2_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h2_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'h2_typography', 'color' ) );
	}

	// H3.
	if ( isset( $h3_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'h3_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'h3_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'h3_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'h3_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'h3_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $h3_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'h3_typography', 'font-size' ) );
	}
	if ( isset( $h3_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h3_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'h3_typography', 'color' ) );
	}

	// H4.
	if ( isset( $h4_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'h4_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'h4_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'h4_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'h4_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'h4_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $h4_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'h4_typography', 'font-size' ) );
	}
	if ( isset( $h4_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'h4_typography', 'color' ) );
	}
	if ( isset( $h4_typography_elements['line-height'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h4_typography_elements['line-height'] ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'h4_typography', 'line-height' ) );
	}

	// H5.
	if ( isset( $h5_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'h5_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'h5_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'h5_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'h5_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'h5_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $h5_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'h5_typography', 'font-size' ) );
	}
	if ( isset( $h5_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h5_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'h5_typography', 'color' ) );
	}

	// H6.
	if ( isset( $h6_typography_elements['family'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['family'] ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'h6_typography' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['family'] ) ]['font-weight']    = intval( Avada()->settings->get( 'h6_typography', 'font-weight' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['family'] ) ]['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'h6_typography', 'line-height' ) );
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['family'] ) ]['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'h6_typography', 'letter-spacing' ), 'px' );

		$font_style = Avada()->settings->get( 'h6_typography', 'font-style' );
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';
	}
	if ( isset( $h6_typography_elements['size'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['size'] ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'h6_typography', 'font-size' ) );
	}
	if ( isset( $h6_typography_elements['color'] ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $h6_typography_elements['color'] ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'h6_typography', 'color' ) );
	}

	$css['global'][ $dynamic_css_helpers->implode( array( 'h1', '.fusion-title-size-one' ) ) ]['margin-top']      = Fusion_Sanitize::size( Avada()->settings->get( 'h1_typography', 'margin-top' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h1', '.fusion-title-size-one' ) ) ]['margin-bottom']   = Fusion_Sanitize::size( Avada()->settings->get( 'h1_typography', 'margin-bottom' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h2', '.fusion-title-size-two' ) ) ]['margin-top']      = Fusion_Sanitize::size( Avada()->settings->get( 'h2_typography', 'margin-top' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h2', '.fusion-title-size-two' ) ) ]['margin-bottom']   = Fusion_Sanitize::size( Avada()->settings->get( 'h2_typography', 'margin-bottom' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h3', '.fusion-title-size-three' ) ) ]['margin-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'h3_typography', 'margin-top' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h3', '.fusion-title-size-three' ) ) ]['margin-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'h3_typography', 'margin-bottom' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h4', '.fusion-title-size-four' ) ) ]['margin-top']     = Fusion_Sanitize::size( Avada()->settings->get( 'h4_typography', 'margin-top' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h4', '.fusion-title-size-four' ) ) ]['margin-bottom']  = Fusion_Sanitize::size( Avada()->settings->get( 'h4_typography', 'margin-bottom' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h5', '.fusion-title-size-five' ) ) ]['margin-top']     = Fusion_Sanitize::size( Avada()->settings->get( 'h5_typography', 'margin-top' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h5', '.fusion-title-size-five' ) ) ]['margin-bottom']  = Fusion_Sanitize::size( Avada()->settings->get( 'h5_typography', 'margin-bottom' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h6', '.fusion-title-size-six' ) ) ]['margin-top']      = Fusion_Sanitize::size( Avada()->settings->get( 'h6_typography', 'margin-top' ) );
	$css['global'][ $dynamic_css_helpers->implode( array( 'h6', '.fusion-title-size-six' ) ) ]['margin-bottom']   = Fusion_Sanitize::size( Avada()->settings->get( 'h6_typography', 'margin-bottom' ) );

	if ( class_exists( 'WooCommerce' ) ) {
		$css['global']['.woocommerce-Address-title']['margin-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'h3_typography', 'margin-bottom' ) );
	}

	/**
	 * HEADER IS NUMBER 5
	 */

	/**
	 * Header Styles
	 */
	if ( '' !== Avada()->settings->get( 'logo', 'url' ) || '' !== Avada()->settings->get( 'logo_retina', 'url' ) ) {
		$elements = array(
			'.fusion-header .fusion-logo',
			'#side-header .fusion-logo',
		);

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'logo_margin', 'top' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right']  = Fusion_Sanitize::size( Avada()->settings->get( 'logo_margin', 'right' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'logo_margin', 'bottom' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left']   = Fusion_Sanitize::size( Avada()->settings->get( 'logo_margin', 'left' ) );
	}

	if ( Avada()->settings->get( 'header_shadow' ) ) {

		$elements = array(
			'.fusion-is-sticky:before',
			'.fusion-is-sticky:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';

	}

	$css['global']['.fusion-header-wrapper .fusion-row']['padding-left']  = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'left' ) );
	$css['global']['.fusion-header-wrapper .fusion-row']['padding-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'right' ) );
	$css['global']['.fusion-header-wrapper .fusion-row']['max-width']     = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );

	$elements = array(
		'.fusion-header-v2 .fusion-header',
		'.fusion-header-v3 .fusion-header',
		'.fusion-header-v4 .fusion-header',
		'.fusion-header-v5 .fusion-header',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );

	$css['global']['#side-header .fusion-secondary-menu-search-inner']['border-top-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );

	$css['global']['.fusion-header .fusion-row']['padding-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'top' ) );
	$css['global']['.fusion-header .fusion-row']['padding-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'bottom' ) );

	$css['global']['.fusion-secondary-header']['background-color']    = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_bg_color' ) );
	$css['global']['.fusion-secondary-header']['font-size']           = Fusion_Sanitize::size( Avada()->settings->get( 'snav_font_size' ) );
	$css['global']['.fusion-secondary-header']['color']               = Fusion_Sanitize::color( Avada()->settings->get( 'snav_color' ) );
	$css['global']['.fusion-secondary-header']['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );

	$elements = array(
		'.fusion-secondary-header a',
		'.fusion-secondary-header a:hover',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'snav_color' ) );

	$css['global']['.fusion-header-v2 .fusion-secondary-header']['border-top-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	$css['global']['.fusion-mobile-menu-design-modern .fusion-secondary-header .fusion-alignleft']['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );

	$css['global']['.fusion-header-tagline']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'tagline_font_size' ) );
	$css['global']['.fusion-header-tagline']['color']     = Fusion_Sanitize::color( Avada()->settings->get( 'tagline_font_color' ) );

	$elements = array(
		'.fusion-secondary-main-menu',
		'.fusion-mobile-menu-sep',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );

	$css['global']['#side-header']['width']                               = intval( $side_header_width ) . 'px';
	$css['global']['#side-header .side-header-background-image']['width'] = intval( $side_header_width ) . 'px';
	$css['global']['#side-header .side-header-background-color']['width'] = intval( $side_header_width ) . 'px';
	$css['global']['#side-header .side-header-border']['width']           = intval( $side_header_width ) . 'px';

	$css['global']['.side-header-wrapper']['padding-top']              = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'top' ) );
	$css['global']['.side-header-wrapper']['padding-bottom']           = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'bottom' ) );
	$css['global']['#side-header .side-header-border']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );

	$css['global']['#side-header .side-header-content']['padding-left']  = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'left' ) );
	$css['global']['#side-header .side-header-content']['padding-right'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'right' ) );

	$css['global']['#side-header .fusion-main-menu > ul > li > a']['padding-left']               = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'left' ) );
	$css['global']['#side-header .fusion-main-menu > ul > li > a']['padding-right']              = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'right' ) );
	$css['global']['.side-header-left .fusion-main-menu > ul > li > a > .fusion-caret']['right'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'right' ) );
	$css['global']['.side-header-right .fusion-main-menu > ul > li > a > .fusion-caret']['left'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'left' ) );
	$css['global']['#side-header .fusion-main-menu > ul > li > a']['border-top-color']           = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );
	$css['global']['#side-header .fusion-main-menu > ul > li > a']['border-bottom-color']        = Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) );
	$css['global']['#side-header .fusion-main-menu > ul > li > a']['text-align']                 = esc_attr( Avada()->settings->get( 'menu_text_align' ) );

	$elements = array(
		'#side-header .fusion-main-menu > ul > li > a.fusion-flex-link',
		'#side-header .fusion-main-menu > ul > li.fusion-menu-item-button > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['justify-content'] = esc_attr( Avada()->settings->get( 'menu_text_align' ) );

	if ( class_exists( 'SitePress' ) ) {
		$wpml_text_align = 'center';
		if ( 'left' === esc_attr( Avada()->settings->get( 'menu_text_align' ) ) ) {
			$wpml_text_align = ( is_rtl() ) ? 'flex-end' : 'flex-start';
		} elseif ( 'right' === esc_attr( Avada()->settings->get( 'menu_text_align' ) ) ) {
			$wpml_text_align = ( is_rtl() ) ? 'flex-start' : 'flex-end';
		}
		$css['global']['#side-header .fusion-main-menu .wpml-ls-item > a, #side-header .fusion-main-menu .wpml-ls-item .menu-text']['justify-content'] = $wpml_text_align;
	}

	$elements = array(
		'#side-header .fusion-main-menu > ul > li.current-menu-ancestor > a',
		'#side-header .fusion-main-menu > ul > li.current-menu-item > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']              = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-right-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-left-color']  = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

	$css['global']['body.side-header-left #side-header .fusion-main-menu > ul > li > ul']['left'] = intval( $side_header_width - 1 ) . 'px';

	$css['global']['body.side-header-left #side-header .fusion-main-menu .fusion-custom-menu-item-contents']['left'] = intval( $side_header_width - 1 ) . 'px';

	$elements = array(
		'#side-header .side-header-content-1',
		'#side-header .side-header-content-2',
		'#side-header .fusion-secondary-menu > ul > li > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']     = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'snav_font_size' ) );
	$bar_width = ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) ? intval( Avada()->settings->get( 'nav_highlight_border' ) ) . 'px' : '0px';

	$elements = array(
		'.side-header-left #side-header .fusion-main-menu > ul > li.current-menu-ancestor > a',
		'.side-header-left #side-header .fusion-main-menu > ul > li.current-menu-item > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-right-width'] = $bar_width;

	$elements = array(
		'.side-header-right #side-header .fusion-main-menu > ul > li.current-menu-ancestor > a',
		'.side-header-right #side-header .fusion-main-menu > ul > li.current-menu-item > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-left-width'] = $bar_width;

	$elements = array(
		'.side-header-right #side-header .fusion-main-menu ul .fusion-dropdown-menu .sub-menu li ul',
		'.side-header-right #side-header .fusion-main-menu ul .fusion-dropdown-menu .sub-menu',
		'.side-header-right #side-header .fusion-main-menu ul .fusion-menu-login-box .sub-menu',
		'.side-header-right #side-header .fusion-main-menu .fusion-menu-cart-items',
		'.side-header-right #side-header .fusion-main-menu .fusion-menu-login-box .fusion-custom-menu-item-contents',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['left'] = '-' . intval( Avada()->settings->get( 'dropdown_menu_width' ) ) . 'px';

	/**
	 * Main Menu Styles
	 */
	// Main Menu Padding.
	$css['global']['.fusion-main-menu > ul > li']['padding-right'] = intval( Avada()->settings->get( 'nav_padding' ) ) . 'px';

	// Bar highlight style.
	if ( 0 !== intval( Avada()->settings->get( 'nav_highlight_border' ) ) && 'bar' === Avada()->settings->get( 'menu_highlight_style' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
		$css['global']['.fusion-main-menu > ul > li > a']['border-top'] = intval( Avada()->settings->get( 'nav_highlight_border' ) ) . 'px solid transparent';
	}

	// Arrow highlight style.
	if ( 'arrow' === Avada()->settings->get( 'menu_highlight_style' ) ) {
		$css['global']['.fusion-main-menu, .fusion-main-menu .fusion-dropdown-menu']['overflow'] = 'visible';
	}

	if ( 'Top' !== Avada()->settings->get( 'header_position' ) || 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
		$bar_width = ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) ) ? intval( Avada()->settings->get( 'nav_highlight_border' ) ) : 0;
		$css['global']['.fusion-main-menu > ul > li > a']['height']      = intval( Avada()->settings->get( 'nav_height' ) ) . 'px';
		$css['global']['.fusion-main-menu > ul > li > a']['line-height'] = ( intval( Avada()->settings->get( 'nav_height' ) ) - $bar_width ) . 'px';
	}

	$css['global']['.fusion-megamenu-icon img']['max-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) );

	// Main menu icon styling.
	$direction_opposite     = array(
		'left'   => 'right',
		'right'  => 'left',
		'top'    => 'bottom',
		'bottom' => 'top',
	);
	$icon_spacing_direction = 'padding-' . $direction_opposite[ Avada()->settings->get( 'menu_icon_position' ) ];
	$icon_spacing_value     = '0.45em';

	// Line height reset for links which are flex links.
	if ( 'Top' === Avada()->settings->get( 'header_position' ) && Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
		$css['global']['.fusion-header-wrapper:not(.fusion-is-sticky) .fusion-main-menu > ul > li > a.fusion-flex-link']['line-height'] = '1 !important';
		$css['global']['.fusion-is-sticky .fusion-main-menu>ul>li>a.fusion-flex-link']['display']                                       = 'block';
	} else {
		$css['global']['.fusion-header-wrapper .fusion-main-menu > ul > li > a.fusion-flex-link']['line-height'] = '1 !important';
	}

	if ( 'top' === Avada()->settings->get( 'menu_icon_position' ) || 'bottom' === Avada()->settings->get( 'menu_icon_position' ) ) {
		$icon_spacing_value = '0.35em';
		if ( Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
			$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li > a .fusion-megamenu-icon']['display'] = 'none';
		} else {
			$css['global']['.fusion-header-wrapper .fusion-main-menu > ul > li > a.fusion-flex-link']['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) ) . '!important';
		}
	} elseif ( Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
		// Resize side icons for shrinking enabled.
		$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li > a > .fusion-megamenu-icon']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_sticky_nav_font_size' ) );
	}

	$css['global']['.fusion-main-menu > ul > li > a > .fusion-megamenu-icon'][ $icon_spacing_direction ] = $icon_spacing_value;
	$css['global']['.fusion-main-menu > ul > li > a > .fusion-megamenu-icon']['font-size']               = Fusion_Sanitize::size( Avada()->settings->get( 'menu_icon_size' ) ) . 'px';
	$css['global']['.fusion-main-menu > ul > li > a > .fusion-megamenu-icon']['color']                   = Fusion_Sanitize::color( Avada()->settings->get( 'menu_icon_color' ) );

	$elements = array(
		'.fusion-main-menu > ul > li > a:hover > .fusion-megamenu-icon',
		'.fusion-main-menu .current_page_item > a > .fusion-megamenu-icon',
		'.fusion-main-menu .current-menu-item > a > .fusion-megamenu-icon',
		'.fusion-main-menu .current-menu-item > a > .fusion-megamenu-icon',
		'.fusion-main-menu .current-menu-parent > a > .fusion-megamenu-icon',
		'.fusion-main-menu .current-menu-ancestor > a > .fusion-megamenu-icon',
		'.fusion-is-sticky .fusion-main-menu > ul > li > a:hover > .fusion-megamenu-icon',
		'.fusion-is-sticky .fusion-main-menu .current_page_item > a > .fusion-megamenu-icon',
		'.fusion-is-sticky .fusion-main-menu .current-menu-item > a > .fusion-megamenu-icon',
		'.fusion-is-sticky .fusion-main-menu .current-menu-parent > a > .fusion-megamenu-icon',
		'.fusion-is-sticky .fusion-main-menu .current-menu-ancestor > a > .fusion-megamenu-icon',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_icon_hover_color' ) );

	// If sideheader and top/bottom, we need to adjust line height.
	if ( 'Top' !== Avada()->settings->get( 'header_position' ) && ( 'top' === Avada()->settings->get( 'menu_icon_position' ) || 'bottom' === Avada()->settings->get( 'menu_icon_position' ) ) ) {
		$menu_height    = Fusion_Sanitize::size( Avada()->settings->get( 'nav_height' ) ) . 'px';
		$menu_font_size = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) );
		$css['global']['.fusion-main-menu .fusion-flex-link']['line-height'] = '1';
		$css['global']['.fusion-main-menu .fusion-flex-link']['padding']     = 'calc( ( ' . $menu_height . ' - ' . $menu_font_size . ') / 2 ) 0';
	}

	// Side header icon alignment.
	if ( 'Top' !== Avada()->settings->get( 'header_position' ) ) {
		if ( 'right' === Avada()->settings->get( 'menu_text_align' ) ) {
			if ( 'left' !== Avada()->settings->get( 'menu_icon_position' ) && 'right' !== Avada()->settings->get( 'menu_icon_position' ) ) {
				$css['global']['.fusion-main-menu > ul > li > a.fusion-flex-link']['align-items']      = 'flex-end';
				$css['global']['.rtl .fusion-main-menu > ul > li > a.fusion-flex-link']['align-items'] = 'flex-start';
			}
		}
		if ( 'left' === Avada()->settings->get( 'menu_text_align' ) ) {
			if ( 'left' !== Avada()->settings->get( 'menu_icon_position' ) && 'right' !== Avada()->settings->get( 'menu_icon_position' ) ) {
				$css['global']['.fusion-main-menu > ul > li > a.fusion-flex-link']['align-items']     = 'flex-start';
				$css['global']['.rtl fusion-main-menu > ul > li > a.fusion-flex-link']['align-items'] = 'flex-end';
			}
		}
	}

	// Re-size mega menu thumbnail image.
	$css['global']['.fusion-main-menu > ul > li > a > .fusion-megamenu-image > img']['width']  = Fusion_Sanitize::size( Avada()->settings->get( 'menu_thumbnail_size', 'width' ) );
	$css['global']['.fusion-main-menu > ul > li > a > .fusion-megamenu-image > img']['height'] = Fusion_Sanitize::size( Avada()->settings->get( 'menu_thumbnail_size', 'height' ) );
	$elements = array(
		'.fusion-main-menu > ul > li > a',
		'.fusion-main-menu .fusion-widget-cart-counter > a:before',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$menu_font_size        = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) );
	$unit                  = preg_replace( '/\d+/u', '', $menu_font_size );
	$description_font_size = ( intval( $menu_font_size ) * 0.8 ) . $unit;
	$css['global']['.fusion-main-menu > ul > li > a .fusion-menu-description']['font-size']      = $description_font_size;
	$css['global']['.fusion-main-menu > ul > li > a .fusion-menu-description']['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
	$css['global']['.fusion-main-menu > ul > li > a .fusion-menu-description']['font-weight']    = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );
	$css['global']['.fusion-main-menu > ul > li > a .fusion-menu-description']['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'body_typography', 'letter-spacing' ), 'px' );

	$text_color = Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) ) )->get_new( 'alpha', '0.65' )->to_css( 'rgba' );

	$css['global']['.fusion-main-menu > ul > li > a .fusion-menu-description']['color'] = $text_color;

	$hover_color = Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) ) )->get_new( 'alpha', '0.65' )->to_css( 'rgba' );
	$elements    = array(
		'.fusion-body .fusion-main-menu > ul > li > a:hover .fusion-menu-description',
		'.fusion-body .fusion-main-menu .current_page_item > a .fusion-menu-description',
		'.fusion-body .fusion-main-menu .current-menu-item > a .fusion-menu-description',
		'.fusion-body .fusion-main-menu .current-menu-parent > a .fusion-menu-description',
		'.fusion-body .fusion-main-menu .current-menu-ancestor > a .fusion-menu-description',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $hover_color;

	// Hide description on sticky header and mobile menu.
	if ( Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
		$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li > a .fusion-menu-description']['display'] = 'none';
	}

	$font_style = Avada()->settings->get( 'body_typography', 'font-style' );
	$css['global'][ $dynamic_css_helpers->implode( $body_typography_elements['family'] ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';

	$elements = array(
		'.fusion-main-menu > ul > li > a:hover',
		'.fusion-main-menu .fusion-widget-cart-counter > a:hover:before',
		'.fusion-is-sticky .fusion-main-menu > ul > li > a:hover',
		'.fusion-is-sticky .fusion-main-menu .fusion-widget-cart-counter > a:hover:before',
		'.fusion-is-sticky .fusion-main-menu > ul > li.current-menu-item > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']                         = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global']['.fusion-main-menu > ul > li > a:hover']['border-color']                       = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global']['.fusion-main-menu > ul > .fusion-menu-item-button > a:hover']['border-color'] = 'transparent';
	$css['global']['.fusion-widget-cart-number']['background-color']                              = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global']['.fusion-widget-cart-counter a:hover:before']['color']                         = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global']['.fusion-widget-cart-number']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$css['global']['#side-header .fusion-main-menu > ul > li > a']['min-height'] = intval( Avada()->settings->get( 'nav_height' ) ) . 'px';

	$css['global']['.ua-ie-11 #side-header .fusion-main-menu > ul > li > .fusion-flex-link']['height']     = intval( Avada()->settings->get( 'nav_height' ) ) . 'px';
	$css['global']['.ua-ie-11 #side-header .fusion-main-menu > ul > li > .fusion-flex-link']['box-sizing'] = 'content-box';

	$elements = array(
		'.fusion-body .fusion-main-menu .current_page_item > a',
		'.fusion-body .fusion-main-menu .current-menu-item > a',
		'.fusion-body .fusion-main-menu .current-menu-item:not(.fusion-main-menu-cart) > a:before',
		'.fusion-body .fusion-main-menu .current-menu-item > a',
		'.fusion-body .fusion-main-menu .current-menu-parent > a',
		'.fusion-body .fusion-main-menu .current-menu-ancestor > a',
	);

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']        = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

	// Arrow highlight.
	if ( 'arrow' === Avada()->settings->get( 'menu_highlight_style' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) {

		// Drop down svg.
		$css['global']['.fusion-dropdown-svg']['visibility']               = 'hidden';
		$css['global']['.fusion-dropdown-svg']['opacity']                  = '0';
		$css['global']['.fusion-dropdown-svg']['pointer-events']           = 'none';
		$css['global']['.fusion-dropdown-svg']['transition']               = 'opacity .2s ease-in';
		$css['global']['.fusion-dropdown-menu .fusion-button']['position'] = 'static';

		$css['global']['li:hover .fusion-dropdown-svg']['visibility']     = 'visible';
		$css['global']['li:hover .fusion-dropdown-svg']['opacity']        = '1';
		$css['global']['li:hover .fusion-dropdown-svg']['pointer-events'] = 'auto';

		// Hover.
		$css['global']['.fusion-dropdown-svg svg path']['transition']         = 'fill .2s ease-in-out';
		$css['global']['.fusion-dropdown-svg.fusion-svg-active path']['fill'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_bg_hover_color' ) ) . '!important';

		// Mobile.
		$elements = array(
			'.fusion-mobile-nav-holder .fusion-dropdown-svg',
			'.fusion-mobile-nav-holder .fusion-arrow-svg',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';

		$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['opacity']    = '0';
		$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['font-size']  = '0px';
		$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['visibility'] = 'hidden';
		$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['position']   = 'absolute';
		$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['overflow']   = 'hidden';
		$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['display']    = 'inline-block';

		if ( 'Top' === Avada()->settings->get( 'header_position' ) ) {

			// Transition for sticky header color transition v4 and v5 not included.
			if ( 'v4' !== Avada()->settings->get( 'header_layout' ) && 'v5' !== Avada()->settings->get( 'header_layout' ) ) {
				$css['global']['.fusion-arrow-highlight .fusion-arrow-svg svg path']['transition'] = 'fill .25s ease-in-out';
			}

			$css['global']['.fusion-logo-link, .fusion-main-menu > ul']['line-height'] = '1';
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['transform']   = 'translateX( -50% );';
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['height']      = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['left']        = '50%';

			// Sticky Header Shadow.
			$shadow_svg = array(
				'.fusion-sticky-shadow .fusion-arrow-highlight .fusion-arrow-svg svg',
				'.fusion-is-sticky .fusion-sticky-menu-only .fusion-arrow-highlight .fusion-arrow-svg svg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $shadow_svg ) ]['-webkit-filter'] = 'drop-shadow( 0px 1px 3px rgba(0,0,0,.117647) )';
			$css['global'][ $dynamic_css_helpers->implode( $shadow_svg ) ]['filter']         = 'drop-shadow( 0px 1px 3px rgba(0,0,0,.117647) )';

			$shadow_svg = array(
				'.fusion-sticky-shadow .fusion-arrow-highlight .fusion-arrow-svg',
				'.fusion-is-sticky .fusion-sticky-menu-only .fusion-arrow-highlight .fusion-arrow-svg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $shadow_svg ) ]['box-sizing']     = 'content-box';
			$css['global'][ $dynamic_css_helpers->implode( $shadow_svg ) ]['padding-bottom'] = '3px';
			$css['global'][ $dynamic_css_helpers->implode( $shadow_svg ) ]['margin-bottom']  = '-3px';

			// If header border is being use and is not transparent.
			$header_2_3_border = ( 'v2' === Avada()->settings->get( 'header_layout' ) || 'v3' === Avada()->settings->get( 'header_layout' ) );
			$header_4_5_border = ( ( 'v4' === Avada()->settings->get( 'header_layout' ) || 'v5' === Avada()->settings->get( 'header_layout' ) ) && 1 === Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'header_bg_color' ) ) )->alpha );
			if ( ( $header_2_3_border || $header_4_5_border ) && 0 !== Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) ) )->alpha ) {
				$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['bottom'] = '-' . Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
				$css['global']['.fusion-arrow-svg svg']['margin-top']                 = '-1px';
				$css['global']['.fusion-arrow-svg svg']['display']                    = 'block';
			} elseif ( ( $header_2_3_border || $header_4_5_border ) && 0 === Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) ) )->alpha ) {
				$css['global']['.fusion-arrow-svg svg']['margin-top']                 = '1px';
				$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['bottom'] = '-' . Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );

				if ( Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
					$css['global']['.fusion-is-sticky .fusion-arrow-svg svg']['margin-top'] = '0px';
				}
			} else {
				$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['bottom'] = '-' . Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			}
			if ( Avada()->settings->get( 'header_sticky' ) ) {
				$css['global']['.fusion-is-sticky .fusion-arrow-svg svg path']['fill']   = Fusion_Sanitize::color( Avada()->settings->get( 'header_sticky_bg_color' ) );
				$css['global']['.fusion-is-sticky .fusion-arrow-svg svg path']['stroke'] = 'transparent';
			}

			// Arrow drop-down for top menu.
			$css['global']['.fusion-dropdown-svg']['transform']      = 'translateX( -50% ) translateY( 200% )';
			$css['global']['.fusion-dropdown-svg']['position']       = 'absolute';
			$css['global']['.fusion-dropdown-svg']['left']           = '50%';
			$css['global']['.fusion-dropdown-svg']['bottom']         = '-10px';
			$css['global']['.fusion-dropdown-svg']['height']         = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-dropdown-svg']['line-height']    = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-dropdown-svg']['z-index']        = '100';
			$css['global']['.fusion-dropdown-svg']['-webkit-filter'] = 'drop-shadow( 0px -2px 1px rgba( 0, 0, 0, 0.03 ) );';
			$css['global']['.fusion-dropdown-svg']['filter']         = 'drop-shadow( 0px -2px 1px rgba( 0, 0, 0, 0.03 ));';

			$transparent_spacing = ( intval( Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) ) ) * 2 + 10 ) . 'px';

			// Top level spacer.
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['position']   = 'absolute';
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['bottom']     = '-' . $transparent_spacing;
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['height']     = $transparent_spacing;
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['background'] = 'transparent';
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['width']      = '100%';
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['display']    = 'block';
			$css['global']['.fusion-main-menu > ul > .menu-item-has-children:hover:before']['content']    = '""';

			// Depper level spacer.
			$elements = array(
				'.fusion-main-menu > ul .sub-menu .menu-item-has-children:hover:before',
				'.fusion-main-menu > ul .sub-menu .menu-item-has-children:hover:after',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['position']                         = 'absolute';
			$css['global']['.fusion-main-menu > ul .sub-menu .menu-item-has-children:hover:before']['right'] = '-5px';
			$css['global']['.fusion-main-menu > ul .sub-menu .menu-item-has-children:hover:after']['left']   = '-5px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']                           = '100%';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background']                       = 'transparent';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']                            = '5px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display']                          = 'block';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['content']                          = '""';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['top']                              = '0';

			// Deeper levels.
			$css['global']['.fusion-main-menu > ul > .fusion-dropdown-menu .sub-menu .sub-menu']['top'] = '0px';

			$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu']['margin-left']                      = '5px';
			$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu.fusion-switched-side']['margin-left'] = '-5px';

			$css['global']['.rtl .fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu']['margin-right']                      = '5px';
			$css['global']['.rtl .fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu.fusion-switched-side']['margin-right'] = '-5px';

			$elements = array(
				'.fusion-main-menu .fusion-dropdown-menu > .sub-menu',
				'.fusion-main-menu .fusion-megamenu-wrapper',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-top'] = $transparent_spacing;

			$elements = array(
				'.fusion-main-menu .fusion-dropdown-menu .sub-menu',
				'.fusion-main-menu  .fusion-megamenu-holder',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top-width'] = '0px';

		} elseif ( 'Left' === Avada()->settings->get( 'header_position' ) ) {
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['transform']   = 'translateY( -50% )';
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['line-height'] = '1';
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['right']       = Fusion_Sanitize::add_css_values( array( '-' . Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ), '2px' ) );
			if ( 0 === Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) ) )->alpha ) {
				$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['right'] = Fusion_Sanitize::add_css_values( array( '-' . Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ), '1px' ) );
			}
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['top'] = '50%';
			$css['global']['.fusion-arrow-svg svg']['margin-left']             = '-1px';

			// Arrow drop-down for left header.
			$css['global']['.fusion-dropdown-svg']['transform']   = 'translateX( 200% ) translateY( -50% )';
			$css['global']['.fusion-dropdown-svg']['position']    = 'absolute';
			$css['global']['.fusion-dropdown-svg']['top']         = '50%';
			$css['global']['.fusion-dropdown-svg']['right']       = '-4px';
			$css['global']['.fusion-dropdown-svg']['height']      = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-dropdown-svg']['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-dropdown-svg']['z-index']     = '100';

			$transparent_spacing = ( intval( Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ) ) * 2 + 5 ) . 'px';

			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['position']   = 'absolute';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['right']      = '-' . $transparent_spacing;
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['width']      = $transparent_spacing;
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['top']        = '0';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['background'] = 'transparent';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['height']     = '100%';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['display']    = 'block';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['content']    = '""';

			$elements = array(
				'.fusion-main-menu .fusion-dropdown-menu > .sub-menu',
				'.fusion-main-menu .fusion-megamenu-wrapper',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left'] = $transparent_spacing;

			$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu']['margin-left']  = '5px';
			$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu']['margin-right'] = '5px';

		} elseif ( 'Right' === Avada()->settings->get( 'header_position' ) ) {
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['transform']   = 'translateY( -50% )';
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['line-height'] = '1';
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['left']        = Fusion_Sanitize::add_css_values( array( '-' . Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ), '2px' ) );
			if ( 0 === Fusion_Color::new_color( Fusion_Sanitize::color( Avada()->settings->get( 'header_border_color' ) ) )->alpha ) {
				$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['left'] = Fusion_Sanitize::add_css_values( array( Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ), '1px' ) );
			}
			$css['global']['.fusion-arrow-highlight .fusion-arrow-svg']['top'] = '50%';
			$css['global']['.fusion-arrow-svg svg']['margin-right']            = '-1px';

			// Arrow drop-down for right header.
			$css['global']['.fusion-dropdown-svg']['transform']   = 'translateX( -200% ) translateY( -50% )';
			$css['global']['.fusion-dropdown-svg']['position']    = 'absolute';
			$css['global']['.fusion-dropdown-svg']['top']         = '50%';
			$css['global']['.fusion-dropdown-svg']['left']        = '-5px';
			$css['global']['.fusion-dropdown-svg']['height']      = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-dropdown-svg']['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
			$css['global']['.fusion-dropdown-svg']['z-index']     = '100';

			$transparent_spacing = ( intval( Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ) ) * 2 + 5 ) . 'px';
			$megamanu_spacing    = ( intval( Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) ) ) * 2 + 4 ) . 'px';

			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['position']   = 'absolute';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['left']       = '-' . $transparent_spacing;
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['width']      = $transparent_spacing;
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['top']        = '0';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['background'] = 'transparent';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['height']     = '100%';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['display']    = 'block';
			$css['global']['.fusion-main-menu .menu-item-has-children:hover:before']['content']    = '""';

			$css['global']['.fusion-main-menu .fusion-dropdown-menu > .sub-menu']['margin-left']          = '-' . $transparent_spacing;
			$css['global']['.fusion-main-menu .fusion-megamenu-wrapper']['margin-left']                   = '-' . $transparent_spacing;
			$css['global']['.rtl .fusion-main-menu .fusion-megamenu-wrapper']['margin-left']              = '-' . $megamanu_spacing;
			$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu']['margin-left']  = '-5px';
			$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu .sub-menu']['margin-right'] = '-5px';
		}

		$elements = array(
			'.fusion-main-menu > ul > li > a.fusion-arrow-highlight:hover .fusion-arrow-svg',
			'.fusion-main-menu > ul > li:hover > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-main-menu .current_page_item > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-main-menu .current-menu-item > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-main-menu .current-menu-parent > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-main-menu .current-menu-ancestor > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-is-sticky .fusion-main-menu > ul > li > a.fusion-arrow-highlight:hover .fusion-arrow-svg',
			'.fusion-is-sticky .fusion-main-menu .current_page_item > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-is-sticky .fusion-main-menu .current-menu-item > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-is-sticky .fusion-main-menu .current-menu-parent > a.fusion-arrow-highlight .fusion-arrow-svg',
			'.fusion-is-sticky .fusion-main-menu .current-menu-ancestor > a.fusion-arrow-highlight .fusion-arrow-svg',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['opacity']              = '1';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['visibility']           = 'visible';
		$css['global']['.fusion-main-menu > ul > li > a.fusion-arrow-highlight']['overflow'] = 'visible !important';
		if ( Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
			$css['global']['.fusion-header']['overflow'] = 'visible !important';
		}
	}

	// Background hover.
	if ( 'background' === Avada()->settings->get( 'menu_highlight_style' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
		$elements = array(
			'.fusion-main-menu > ul > li:not(.fusion-middle-logo-menu-logo) > a:hover',
			'.fusion-main-menu > ul > li:not(.fusion-middle-logo-menu-logo):hover > a',
			'.fusion-main-menu > ul > li.current_page_item > a',
			'.fusion-main-menu > ul > li.current-menu-item > a',
			'.fusion-main-menu > ul > li.current-menu-parent > a',
			'.fusion-main-menu > ul > li.current-menu-ancestor > a',
			'.fusion-is-sticky .fusion-main-menu > ul > li:not(.fusion-middle-logo-menu-logo) > a:hover',
			'.fusion-is-sticky .fusion-main-menu > ul > li.current_page_item > a',
			'.fusion-is-sticky .fusion-main-menu > ul > li.current-menu-item > a',
			'.fusion-is-sticky .fusion-main-menu > ul > li.current-menu-parent > a',
			'.fusion-is-sticky .fusion-main-menu > ul > li.current-menu-ancestor > a',
		);

		if ( 'Top' === Avada()->settings->get( 'header_position' ) ) {
			$css['global']['.fusion-logo-link, .fusion-main-menu > ul']['line-height'] = '1';
		}

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_highlight_background' ) );

		// Icon and button menu items, no bg color.
		$elements = array(
			'.fusion-body .fusion-main-menu > ul.fusion-menu > li.fusion-menu-item-button > a',
			'.fusion-body .fusion-main-menu > ul.fusion-menu > li.fusion-main-menu-cart > a',
			'.fusion-body .fusion-main-menu > ul.fusion-menu > li.fusion-main-menu-sliding-bar > a',
			'.fusion-body .fusion-main-menu > ul.fusion-menu > li.fusion-main-menu-search > a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'transparent';

		$elements = array(
			'.fusion-main-menu > ul > li',
			'.fusion-is-sticky .fusion-main-menu > ul > li',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = '0px';

		$half_menu_spacing = ( intval( Fusion_Sanitize::size( Avada()->settings->get( 'nav_padding' ) ) ) / 2 ) . 'px';
		$css['global']['.fusion-main-menu > ul > li > a']['padding-left']  = $half_menu_spacing;
		$css['global']['.fusion-main-menu > ul > li > a']['padding-right'] = $half_menu_spacing;

		$half_menu_spacing = ( intval( Fusion_Sanitize::size( Avada()->settings->get( 'header_sticky_nav_padding' ) ) ) / 2 ) . 'px';
		$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li > a']['padding-left']  = $half_menu_spacing;
		$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li > a']['padding-right'] = $half_menu_spacing;
		$css['global']['#side-header .fusion-main-menu > ul > li']['border-right']           = '1px solid transparent';
	}

	$elements = array(
		'.fusion-main-menu .fusion-main-menu-icon:after',
		'.fusion-main-menu .fusion-widget-cart-counter > a:before',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$elements = array(
		'.fusion-main-menu .fusion-menu-cart-link a:hover',
		'.fusion-main-menu .fusion-menu-cart-checkout-link a:hover',
		'.fusion-main-menu .fusion-menu-cart-link a:hover:before',
		'.fusion-main-menu .fusion-menu-cart-checkout-link a:hover:before',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

	$elements = array(
		'.fusion-main-menu .fusion-main-menu-icon:after',
		'.fusion-main-menu .fusion-widget-cart-counter > a:before',
		'.fusion-main-menu .fusion-widget-cart-counter > a .fusion-widget-cart-number',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height'] = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']  = Fusion_Sanitize::size( Avada()->settings->get( 'nav_typography', 'font-size' ) );

	if ( Avada()->settings->get( 'main_nav_icon_circle' ) ) {
		$elements = array(
			'.fusion-main-menu .fusion-main-menu-icon:after',
			'.fusion-main-menu .fusion-widget-cart-counter > a:before',
		);

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

		preg_match_all( '!\d+!', Avada()->settings->get( 'nav_typography', 'font-size' ), $matches );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = $matches[0][0] * 0.35 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'nav_typography', 'font-size' ) );
	}

	$elements = array(
		'.fusion-main-menu .fusion-main-menu-icon:hover:after',
		'.fusion-is-sticky .fusion-main-menu .fusion-main-menu-icon:hover:after',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

	if ( Avada()->settings->get( 'main_nav_icon_circle' ) ) {
		$css['global']['.fusion-main-menu .fusion-main-menu-icon:hover:after']['border']           = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
		$css['global']['.fusion-main-menu .fusion-widget-cart-counter > a:hover:before']['border'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	}

	$elements = array(
		'.fusion-main-menu .fusion-main-menu-search-open .fusion-main-menu-icon:after',
		'.fusion-main-menu .fusion-main-menu-icon-active:after',
		'.fusion-main-menu .fusion-icon-sliding-bar.fusion-main-menu-icon-active:before',
		'.woocommerce-cart .fusion-main-menu-cart .fusion-main-menu-icon:after',
		'.woocommerce-cart .fusion-main-menu-cart .fusion-main-menu-icon:before',
		'.fusion-body .fusion-main-menu .fusion-widget-cart-counter .fusion-main-menu-icon-active:before',
		'.fusion-is-sticky .fusion-main-menu .fusion-menu .fusion-main-menu-icon-active:after',
		'.fusion-is-sticky .fusion-main-menu .fusion-main-menu-search-open .fusion-main-menu-icon:after',
		'.fusion-is-sticky .fusion-main-menu .fusion-main-menu-icon-active:after',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

	if ( Avada()->settings->get( 'main_nav_icon_circle' ) ) {
		$elements = array(
			'.fusion-main-menu .fusion-main-menu-search-open .fusion-main-menu-icon:after',
			'.fusion-main-menu .fusion-main-menu-icon-active:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
	}

	$css['global']['.fusion-main-menu .sub-menu']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_bg_color' ) );
	$css['global']['.fusion-main-menu .sub-menu']['width']            = intval( Avada()->settings->get( 'dropdown_menu_width' ) ) . 'px';

	if ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) ) {
		$css['global']['.fusion-main-menu .sub-menu']['border-top'] = intval( Avada()->settings->get( 'dropdown_menu_top_border_size' ) ) . 'px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );
		$css['global']['.fusion-main-menu .sub-menu ul']['top']     = '-' . intval( Avada()->settings->get( 'dropdown_menu_top_border_size' ) ) . 'px';
	}
	$css['global']['.fusion-main-menu .sub-menu']['font-family'] = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
	$css['global']['.fusion-main-menu .sub-menu']['font-weight'] = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );
	if ( Avada()->settings->get( 'megamenu_shadow' ) ) {
		$css['global']['.fusion-main-menu .sub-menu']['box-shadow'] = '1px 1px 30px rgba(0, 0, 0, 0.06)';
	}

	$css['global']['.fusion-main-menu .sub-menu ul']['left'] = intval( Avada()->settings->get( 'dropdown_menu_width' ) ) . 'px';

	if ( Avada()->settings->get( 'mainmenu_dropdown_display_divider' ) ) {
		$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu li a']['border-bottom'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );
	} else {
		$css['global']['.fusion-main-menu .fusion-dropdown-menu .sub-menu li a']['border-bottom'] = 'none';
	}
	$elements = array(
		'.fusion-main-menu .sub-menu li a',
		'.fusion-main-menu .fusion-dropdown-menu .sub-menu li a',
		'.fusion-megamenu-wrapper li .fusion-megamenu-title-disabled',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-top']    = intval( Avada()->settings->get( 'mainmenu_dropdown_vertical_padding' ) ) . 'px';
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-bottom'] = intval( Avada()->settings->get( 'mainmenu_dropdown_vertical_padding' ) ) . 'px';
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']          = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-family']    = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-weight']    = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size']      = Fusion_Sanitize::size( Avada()->settings->get( 'nav_dropdown_font_size' ) );

	$css['global']['.sub-menu .fusion-caret']['top'] = intval( Avada()->settings->get( 'mainmenu_dropdown_vertical_padding' ) ) . 'px';

	$css['global']['.fusion-main-menu .fusion-main-menu-cart']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'nav_dropdown_font_size' ) );

	$css['global']['.fusion-main-menu .sub-menu li a:hover']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_bg_hover_color' ) );

	if ( class_exists( 'WooCommerce' ) ) {
		$css['global']['.fusion-main-menu .fusion-menu-login-box-register']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );
	}

	$elements = array(
		'.fusion-main-menu .sub-menu .current_page_item > a',
		'.fusion-main-menu .sub-menu .current-menu-item > a',
		'.fusion-main-menu .sub-menu .current-menu-parent > a',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_bg_hover_color' ) );

	$css['global']['.fusion-main-menu .fusion-custom-menu-item-contents']['font-family'] = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
	$css['global']['.fusion-main-menu .fusion-custom-menu-item-contents']['font-weight'] = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );

	$elements = array(
		'.fusion-main-menu .fusion-main-menu-search .fusion-custom-menu-item-contents',
		'.fusion-main-menu .fusion-main-menu-cart .fusion-custom-menu-item-contents',
		'.fusion-main-menu .fusion-menu-login-box .fusion-custom-menu-item-contents',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_bg_color' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );

	if ( 'transparent' === Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) ) || 0 === Fusion_Color::new_color( Avada()->settings->get( 'menu_sub_sep_color' ) )->alpha ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border'] = '0';
	}

	if ( is_rtl() ) {
		$elements = array(
			'.rtl .fusion-header-v1 .fusion-main-menu > ul > li',
			'.rtl .fusion-header-v2 .fusion-main-menu > ul > li',
			'.rtl .fusion-header-v3 .fusion-main-menu > ul > li',
			'.rtl .fusion-header-v4 .fusion-main-menu > ul > li',
			'.rtl .fusion-header-v5 .fusion-main-menu > ul > li',
			'.rtl .fusion-header-v7 .fusion-main-menu > ul > li',
		);

		if ( 0 != Avada()->settings->get( 'nav_padding' ) && 'background' !== Avada()->settings->get( 'menu_highlight_style' ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left'] = intval( Avada()->settings->get( 'nav_padding' ) ) . 'px';
		}

		$css['global']['.rtl.fusion-top-header .fusion-main-menu .sub-menu ul']['right'] = intval( Avada()->settings->get( 'dropdown_menu_width' ) ) . 'px';

	}

	/**
	 * Flyout Menu Styles
	 */
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons']['font-size']  = Fusion_Sanitize::size( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-header-has-flyout-menu .fusion-widget-cart-number']['min-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );

	$icon_font_size = Fusion_Sanitize::number( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-menu-toggle']['height']                       = $icon_font_size * 0.9 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-menu-toggle']['width']                        = $icon_font_size * 1.5 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle .fusion-toggle-icon']['height'] = $icon_font_size * 0.9 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle .fusion-toggle-icon']['width']  = $icon_font_size * 0.9 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );

	if ( ! Avada()->settings->get( 'main_nav_search_icon' ) && Avada()->settings->get( 'mobile_menu_search' ) ) {
		$css['global']['.fusion-header-v6.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle']['display'] = 'none';
	}

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-toggle-icon-line']['height'] = round( $icon_font_size * 0.1 ) . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-body .fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-menu-icons .fusion-flyout-search-toggle .fusion-toggle-icon-line']['height'] = $icon_font_size * 0.1 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-toggle-icon-line']['width'] = $icon_font_size * 1.5 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );

	$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-menu-active .fusion-flyout-menu-icons .fusion-flyout-menu-toggle .fusion-toggle-icon-line']['width']     = $icon_font_size * 0.9 / 0.75 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-menu-icons .fusion-flyout-search-toggle .fusion-toggle-icon-line']['width'] = $icon_font_size * 0.9 / 0.75 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );

	$elements = array(
		'.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-cart-wrapper',
		'.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-sliding-bar-toggle',
		'.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle',
		'.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-menu-toggle',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = sprintf( '0 %spx', round( Avada()->settings->get( 'flyout_nav_icons_padding' ) / 2 ) );

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons']['margin'] = sprintf( '0 -%spx', Avada()->settings->get( 'flyout_nav_icons_padding' ) / 2 );

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-icon:before']['color']       = Avada()->settings->get( 'flyout_menu_icon_color' );
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-icon:hover:before']['color'] = Avada()->settings->get( 'flyout_menu_icon_hover_color' );

	if ( Avada()->settings->get( 'main_nav_icon_circle' ) ) {

		$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-icon:before']['border']                            = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'flyout_menu_icon_color' ) );
		$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-icon:hover:before']['border']                      = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'flyout_menu_icon_hover_color' ) );
		$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-icon:before']['border']       = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
		$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-icon:hover:before']['border'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

		$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-icon:before']['padding']             = $icon_font_size * 0.35 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
		$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-icon-sliding-bar:before']['padding'] = $icon_font_size * 0.2 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'flyout_menu_icon_font_size' ) );
	}

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-toggle-icon-line']['background-color'] = Avada()->settings->get( 'flyout_menu_icon_color' );

	$elements = array(
		'.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-menu-toggle:hover .fusion-toggle-icon-line',
		'.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle:hover .fusion-toggle-icon-line',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Avada()->settings->get( 'flyout_menu_icon_hover_color' );

	$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-icon:before']['color']       = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
	$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-icon:hover:before']['color'] = Avada()->settings->get( 'menu_hover_first_color' );

	$css['global']['.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-toggle-icon-line']['background-color'] = Avada()->settings->get( 'nav_typography', 'color' );

	$elements = array(
		'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-flyout-menu-toggle:hover .fusion-toggle-icon-line',
		'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-icons .fusion-flyout-search-toggle:hover .fusion-toggle-icon-line',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Avada()->settings->get( 'menu_hover_first_color' );

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu-bg']['background-color'] = Avada()->settings->get( 'flyout_menu_background_color' );

	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s']['color']        = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s']['font-family']  = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'nav_typography' ) );

	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s::-webkit-input-placeholder']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s::-moz-placeholder']['color']          = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s:-moz-placeholder']['color']           = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu .fusion-flyout-search .searchform .s:-ms-input-placeholder']['color']      = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-menu > ul > li']['padding'] = ( Fusion_Sanitize::number( Avada()->settings->get( 'flyout_menu_item_padding' ) ) / 2 ) . 'px 0';

	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s']['color']        = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s']['font-family']  = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'mobile_menu_typography' ) );

	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s::-webkit-input-placeholder']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s::-moz-placeholder']['color']          = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s:-moz-placeholder']['color']           = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['#wrapper .fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout .fusion-flyout-search .searchform .s:-ms-input-placeholder']['color']      = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );

	$css['global']['.fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout.fusion-flyout-active .fusion-flyout-menu-icons .fusion-icon:before']['color']       = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['.fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout.fusion-flyout-active .fusion-flyout-menu-icons .fusion-icon:hover:before']['color'] = Avada()->settings->get( 'mobile_menu_font_hover_color' );

	$elements = array(
		'.fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout.fusion-flyout-active .fusion-flyout-menu-icons .fusion-flyout-menu-toggle:hover .fusion-toggle-icon-line',
		'.fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout.fusion-flyout-active .fusion-flyout-menu-icons .fusion-flyout-search-toggle:hover .fusion-toggle-icon-line',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Avada()->settings->get( 'mobile_menu_font_hover_color' );

	$css['global']['.fusion-header-has-flyout-menu.fusion-mobile-menu-design-flyout.fusion-flyout-active .fusion-flyout-menu-icons .fusion-toggle-icon-line']['background-color'] = Avada()->settings->get( 'mobile_menu_typography', 'color' );

	switch ( Avada()->settings->get( 'flyout_menu_direction' ) ) {

		case 'fade':
			$elements = array(
				'.fusion-header-has-flyout-menu .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu .fusion-flyout-search',
				'.fusion-header-has-flyout-menu .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['top']        = '-1000%';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transition'] = 'opacity 0.4s ease 0s, top 0s ease 0.4s';

			$elements = array(
				'.fusion-header-has-flyout-menu.fusion-flyout-menu-active .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-search',
				'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['top']        = '0';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transition'] = 'opacity 0.4s ease 0s, top 0s ease 0s';

			break;
		case 'left':
			$elements = array(
				'.fusion-header-has-flyout-menu .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu .fusion-flyout-search',
				'.fusion-header-has-flyout-menu .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateX(-100%)';

			$elements = array(
				'.fusion-header-has-flyout-menu.fusion-flyout-menu-active .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-search',
				'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateX(0%)';
			break;

		case 'right':
			$elements = array(
				'.fusion-header-has-flyout-menu .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu .fusion-flyout-search',
				'.fusion-header-has-flyout-menu .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateX(100%)';

			$elements = array(
				'.fusion-header-has-flyout-menu.fusion-flyout-menu-active .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-search',
				'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateX(0%)';
			break;

		case 'bottom':
			$elements = array(
				'.fusion-header-has-flyout-menu .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu .fusion-flyout-search',
				'.fusion-header-has-flyout-menu .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateY(100%)';

			$elements = array(
				'.fusion-header-has-flyout-menu.fusion-flyout-menu-active .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-search',
				'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateY(0%)';
			break;
		case 'top':
			$elements = array(
				'.fusion-header-has-flyout-menu .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu .fusion-flyout-search',
				'.fusion-header-has-flyout-menu .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateY(-100%)';

			$elements = array(
				'.fusion-header-has-flyout-menu.fusion-flyout-menu-active .fusion-flyout-menu',
				'.fusion-header-has-flyout-menu.fusion-flyout-search-active .fusion-flyout-search',
				'.fusion-header-has-flyout-menu.fusion-flyout-active .fusion-flyout-menu-bg',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['transform'] = 'translateY(0%)';
			break;
	}

	/**
	 * Secondary Menu Styles
	 */

	$css['global']['.fusion-secondary-menu > ul > li']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_first_border_color' ) );

	if ( 0 !== Fusion_Sanitize::number( Avada()->settings->get( 'sec_menu_lh' ) ) ) {
		$css['global']['.fusion-secondary-menu > ul > li > a']['height'] = Fusion_Sanitize::size( Avada()->settings->get( 'sec_menu_lh' ) );
	}

	$css['global']['.fusion-secondary-menu .sub-menu, .fusion-secondary-menu .fusion-custom-menu-item-contents']['width'] = intval( Avada()->settings->get( 'topmenu_dropwdown_width' ) ) . 'px';
	$css['global']['.fusion-secondary-menu .fusion-secondary-menu-icon']['min-width']                                     = intval( Avada()->settings->get( 'topmenu_dropwdown_width' ) ) . 'px';
	$css['global']['.fusion-secondary-menu .sub-menu']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_sub_bg_color' ) );
	$css['global']['.fusion-secondary-menu .sub-menu']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_sep_color' ) );

	$css['global']['.fusion-secondary-menu .sub-menu a']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_sep_color' ) );
	$css['global']['.fusion-secondary-menu .sub-menu a']['color']        = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_color' ) );

	$css['global']['.fusion-secondary-menu .sub-menu a:hover']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_bg_hover_color' ) );
	$css['global']['.fusion-secondary-menu .sub-menu a:hover']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_hover_color' ) );

	$css['global']['.fusion-secondary-menu > ul > li > .sub-menu .sub-menu']['left'] = intval( Avada()->settings->get( 'topmenu_dropwdown_width' ) ) - 2 . 'px';

	$css['global']['.fusion-secondary-menu .fusion-custom-menu-item-contents']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_sub_bg_color' ) );
	$css['global']['.fusion-secondary-menu .fusion-custom-menu-item-contents']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_sep_color' ) );
	$css['global']['.fusion-secondary-menu .fusion-custom-menu-item-contents']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_color' ) );

	$elements = array(
		'.fusion-secondary-menu .fusion-secondary-menu-icon',
		'.fusion-secondary-menu .fusion-secondary-menu-icon:hover',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$css['global']['.fusion-secondary-menu .fusion-menu-cart-items a']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_color' ) );

	$css['global']['.fusion-secondary-menu .fusion-menu-cart-item a']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_sep_color' ) );

	$css['global']['.fusion-secondary-menu .fusion-menu-cart-item a:hover']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_bg_hover_color' ) );
	$css['global']['.fusion-secondary-menu .fusion-menu-cart-item a:hover']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_hover_color' ) );

	if ( class_exists( 'WooCommerce' ) ) {
		$css['global']['.fusion-secondary-menu .fusion-menu-cart-checkout']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_cart_bg_color' ) );

		$css['global']['.fusion-secondary-menu .fusion-menu-cart-checkout a:before']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_color' ) );

		$elements = array(
			'.fusion-secondary-menu .fusion-menu-cart-checkout a:hover',
			'.fusion-secondary-menu .fusion-menu-cart-checkout a:hover:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_menu_sub_hover_color' ) );
	}

	$css['global']['.fusion-secondary-menu-icon']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_cart_bg_color' ) );
	$css['global']['.fusion-secondary-menu-icon']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$elements = array(
		'.fusion-secondary-menu-icon:before',
		'.fusion-secondary-menu-icon:after',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	if ( is_rtl() ) {
		$css['global']['.rtl .fusion-secondary-menu > ul > li:first-child']['border-left'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'header_top_first_border_color' ) );

		$css['global']['.rtl .fusion-secondary-menu > ul > li > .sub-menu .sub-menu']['right'] = intval( Avada()->settings->get( 'topmenu_dropwdown_width' ) ) . 'px';
	}

	if ( 0 !== Fusion_Sanitize::number( Avada()->settings->get( 'sec_menu_lh' ) ) ) {
		$css['global']['.fusion-contact-info']['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'sec_menu_lh' ) );
	}

	/**
	 * Common Menu Styles
	 */

	if ( class_exists( 'WooCommerce' ) ) {
		$css['global']['.fusion-menu-cart-items']['width'] = intval( Avada()->settings->get( 'dropdown_menu_width' ) ) . 'px';

		$css['global']['.fusion-menu-cart-items']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'woo_icon_font_size' ) );

		$css['global']['.fusion-menu-cart-items a']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );

		$css['global']['.fusion-menu-cart-item a']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );

		$css['global']['.fusion-menu-cart-item a:hover']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_bg_hover_color' ) );

		$css['global']['.fusion-menu-cart-checkout']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_cart_bg_color' ) );

		$css['global']['.fusion-menu-cart-checkout a:before']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );
	}

	/**
	 * Megamenu Styles
	 */

	$css['global']['.fusion-megamenu-holder']['border-top-width'] = intval( Avada()->settings->get( 'dropdown_menu_top_border_size' ) ) . 'px';
	$css['global']['.fusion-megamenu-holder']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'menu_hover_first_color' ) );

	$css['global']['.fusion-megamenu-holder']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_bg_color' ) );
	if ( Avada()->settings->get( 'megamenu_shadow' ) ) {
		$css['global']['.fusion-megamenu-holder']['box-shadow'] = '1px 1px 30px rgba(0, 0, 0, 0.06)';
	}

	$elements = array(
		'.fusion-megamenu-wrapper .fusion-megamenu-submenu',
		'.rtl .fusion-megamenu-wrapper .fusion-megamenu-submenu:last-child',
		'.fusion-megamenu-wrapper .fusion-megamenu-border',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );

	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a']['padding-top']    = intval( Avada()->settings->get( 'megamenu_item_vertical_padding' ) ) . 'px';
	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a']['padding-bottom'] = intval( Avada()->settings->get( 'megamenu_item_vertical_padding' ) ) . 'px';
	if ( Avada()->settings->get( 'megamenu_item_display_divider' ) ) {
		$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a']['border-bottom']     = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );
		$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu > a']['border-bottom']             = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );
		$css['global']['#side-header .fusion-main-menu > ul .sub-menu > li:last-child > a']['border-bottom'] = '1px solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_sep_color' ) );
		$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu']['padding-bottom']                = '0';
		$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu-notitle']['padding-top']           = '0';
	}

	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_bg_hover_color' ) );
	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );
	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover']['font-family']      = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover']['font-weight']      = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );
	$css['global']['.fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover']['font-size']        = Fusion_Sanitize::size( Avada()->settings->get( 'nav_dropdown_font_size' ) );

	$css['global']['.fusion-megamenu-title']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'megamenu_title_size' ) );
	$css['global']['.fusion-megamenu-title']['color']     = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$css['global']['.fusion-megamenu-title a']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'nav_typography', 'color' ) );

	$css['global']['.fusion-megamenu-bullet']['border-left-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );

	$css['global']['.fusion-megamenu-widgets-container']['color']       = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );
	$css['global']['.fusion-megamenu-widgets-container']['font-family'] = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'body_typography' ) );
	$css['global']['.fusion-megamenu-widgets-container']['font-weight'] = intval( Avada()->settings->get( 'body_typography', 'font-weight' ) );
	$css['global']['.fusion-megamenu-widgets-container']['font-size']   = Fusion_Sanitize::size( Avada()->settings->get( 'nav_dropdown_font_size' ) );

	if ( is_rtl() ) {
		$css['global']['.rtl .fusion-megamenu-bullet']['border-right-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_color' ) );
	}

	/**
	 * Sticky Header Styles
	 */
	$elements = array(
		'.fusion-header-wrapper.fusion-is-sticky .fusion-header',
		'.fusion-header-wrapper.fusion-is-sticky .fusion-secondary-main-menu',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_sticky_bg_color' ) );

	if ( 'background' !== Avada()->settings->get( 'menu_highlight_style' ) ) {
		$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li']['padding-right'] = intval( Avada()->settings->get( 'header_sticky_nav_padding' ) ) . 'px';
	}

	$elements = array(
		'.fusion-is-sticky .fusion-main-menu .fusion-main-menu-icon:after',
		'.fusion-is-sticky .fusion-main-menu .fusion-widget-cart-counter > a:before',
		'.fusion-is-sticky .fusion-main-menu > ul > li > a',
		'.fusion-is-sticky .fusion-main-menu > ul > li > a .fusion-menu-description',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_sticky_menu_color' ) );

	if ( 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
		$css['global']['.fusion-is-sticky .fusion-main-menu > ul > li > a']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_sticky_nav_font_size' ) );
	}

	if ( is_rtl() && 'background' !== Avada()->settings->get( 'menu_highlight_style' ) ) {
		$css['global']['.rtl .fusion-is-sticky .fusion-main-menu > ul > li']['padding-left'] = intval( Avada()->settings->get( 'header_sticky_nav_padding' ) ) . 'px';
	}

	/**
	 * Mobile Menu Styles.
	 */
	$elements = array(
		'.fusion-mobile-nav-holder > ul li a',
		'.fusion-mobile-nav-holder > ul li .fusion-icon-only-link .menu-text',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-family'] = $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'mobile_menu_typography' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-weight'] = intval( Avada()->settings->get( 'mobile_menu_typography', 'font-weight' ) );
	$font_style = Avada()->settings->get( 'mobile_menu_typography', 'font-style' );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-style'] = ( $font_style ) ? esc_attr( $font_style ) : 'normal';

	$css['global']['.fusion-mobile-selector']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_background_color' ) );
	$css['global']['.fusion-mobile-selector']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_border_color' ) );
	$css['global']['.fusion-mobile-selector']['font-size']        = Fusion_Sanitize::size( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ) );
	$css['global']['.fusion-mobile-selector']['height']           = intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) . 'px';
	$css['global']['.fusion-mobile-selector']['line-height']      = intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) . 'px';
	$css['global']['.fusion-mobile-selector']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );

	$elements = array(
		'.fusion-body .fusion-mobile-nav-holder .fusion-selector-down',
	);
	if ( is_rtl() ) {
		$elements[] = '.rtl .fusion-mobile-nav-holder .fusion-selector-down';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']       = ( intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) - 2 ) . 'px';
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height']  = ( intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) - 2 ) . 'px';
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_border_color' ) );

	$elements = array(
		'.fusion-selector-down:before',
	);
	if ( is_rtl() ) {
		$elements[] = '.rtl .fusion-selector-down:before';
	}
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_toggle_color' ) );

	if ( false !== strpos( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ), 'px' ) && 35 < intval( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ) ) ) {
		$css['global']['.fusion-selector-down']['font-size'] = '30px';
	}

	$elements = array(
		'.fusion-mobile-nav-holder > ul',
		'.fusion-mobile-menu-design-modern .fusion-mobile-nav-holder > ul',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_border_color' ) );

	$css['global']['.fusion-mobile-nav-item .fusion-open-submenu']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['.fusion-mobile-nav-item a']['color']                    = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );
	$css['global']['.fusion-mobile-nav-item a']['line-height']              = intval( Avada()->settings->get( 'mobile_menu_typography', 'line-height' ) ) . 'px';

	$elements = array(
		'.fusion-mobile-current-nav-item > a',
		'.fusion-mobile-nav-item.current-menu-item > a',
		'.fusion-mobile-nav-item a:hover',
	);

	if ( 'flyout' !== Avada()->settings->get( 'mobile_menu_design' ) ) {
		$css['global']['.fusion-mobile-nav-item a']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_background_color' ) );
		$css['global']['.fusion-mobile-nav-item a']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_border_color' ) );
		$css['global']['.fusion-mobile-nav-item a']['height']           = intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) . 'px';

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_hover_color' ) );
	}

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_font_hover_color' ) );

	$css['global']['.fusion-mobile-nav-item.fusion-main-menu-sliding-bar a:after']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );

	$css['global']['.fusion-mobile-nav-item a, .fusion-mobile-nav-holder > ul > li.fusion-mobile-nav-item > a']['font-size']      = Fusion_Sanitize::size( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ) );
	$css['global']['.fusion-mobile-nav-item a, .fusion-mobile-nav-holder > ul > li.fusion-mobile-nav-item > a']['letter-spacing'] = Fusion_Sanitize::size( Avada()->settings->get( 'mobile_menu_typography', 'letter-spacing' ), 'px' );

	$css['global']['.fusion-mobile-nav-item a:before']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_typography', 'color' ) );

	$css['global']['.fusion-mobile-menu-icons']['margin-top']                                       = intval( Avada()->settings->get( 'mobile_menu_icons_top_margin' ) ) . 'px';
	$css['global']['.fusion-header-has-flyout-menu .fusion-flyout-mobile-menu-icons']['margin-top'] = intval( Avada()->settings->get( 'mobile_menu_icons_top_margin' ) ) . 'px';

	$elements = array(
		'.fusion-mobile-menu-icons a',
		'.fusion-mobile-menu-icons a:before',
		'.fusion-mobile-menu-icons a:after',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'mobile_menu_toggle_color' ) );

	$css['global']['.fusion-open-submenu']['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ) );

	$css['global']['.fusion-open-submenu']['height']      = intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) . 'px';
	$css['global']['.fusion-open-submenu']['line-height'] = intval( Avada()->settings->get( 'mobile_menu_nav_height' ) ) . 'px';

	if ( false !== strpos( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ), 'px' ) && 30 < intval( Avada()->settings->get( 'mobile_menu_typography', 'font-size' ) ) ) {
		$css['global']['.fusion-open-submenu']['font-size'] = '20px';
	}

	$css['global']['.fusion-open-submenu:hover']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

	/**
	 * Social Links.
	 */
	if ( 0 === Fusion_Color::new_color( Avada()->settings->get( 'social_bg_color' ) )->alpha ) {
		$css['global']['.fusion-sharing-box']['padding-left']  = '0';
		$css['global']['.fusion-sharing-box']['padding-right'] = '0';
	}
	$css['global']['.fusion-sharing-box h4']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'sharing_box_tagline_text_color' ) );

	$css['global']['.fusion-social-links-header .fusion-social-networks a']['font-size']           = Fusion_Sanitize::size( Avada()->settings->get( 'header_social_links_font_size' ) );
	$css['global']['.fusion-social-links-header .fusion-social-networks.boxed-icons a']['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'header_social_links_boxed_padding' ) );
	$css['global']['.fusion-social-links-header .fusion-social-networks.boxed-icons a']['width']   = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'header_social_links_font_size' ) ) . ' + (2 * ' . Fusion_Sanitize::size( Avada()->settings->get( 'header_social_links_boxed_padding' ) ) . ') + 2px)';
	$css['global']['.fusion-social-links-footer .fusion-social-networks a']['font-size']           = Fusion_Sanitize::size( Avada()->settings->get( 'footer_social_links_font_size' ) );
	$css['global']['.fusion-social-links-footer .fusion-social-networks.boxed-icons a']['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'footer_social_links_boxed_padding' ) );
	$css['global']['.fusion-social-links-footer .fusion-social-networks.boxed-icons a']['width']   = 'calc(' . Fusion_Sanitize::size( Avada()->settings->get( 'footer_social_links_font_size' ) ) . ' + (2 * ' . Fusion_Sanitize::size( Avada()->settings->get( 'footer_social_links_boxed_padding' ) ) . ') + 2px)';

	/**
	 * Single Post Slideshow.
	 */
	if ( Avada()->settings->get( 'slideshow_smooth_height' ) || ( 'auto' === get_post_meta( $c_page_id, 'pyre_fimg_width', true ) && 'half' === get_post_meta( $c_page_id, 'pyre_width', true ) ) ) {
		$elements = array(
			'.fusion-post-slider.fusion-flexslider',
			'.fusion-post-slideshow.fusion-flexslider',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['overflow'] = 'hidden';
	}

	if ( class_exists( 'WooCommerce' ) ) {

		/**
		 * Woocommerce - Dynamic Styling.
		 */

		$css['global']['.order-dropdown']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );

		$css['global']['.order-dropdown > li:after']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_border_color' ) );

		$elements = array(
			'.order-dropdown a',
			'.order-dropdown a:hover',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );

		$elements = array(
			'.order-dropdown .current-li',
			'.order-dropdown ul li a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_bg_color' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_border_color' ) );

		$css['global']['.order-dropdown ul li a:hover']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );

		$css['global']['.order-dropdown ul li a:hover']['background-color'] = Fusion_Sanitize::color( fusion_color_luminance( Avada()->settings->get( 'woo_dropdown_bg_color' ), 0.1 ) );

		$css['global']['.catalog-ordering .order li a']['color']            = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );
		$css['global']['.catalog-ordering .order li a']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_bg_color' ) );
		$css['global']['.catalog-ordering .order li a']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_border_color' ) );

		$css['global']['.fusion-grid-list-view']['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_border_color' ) );

		$css['global']['.fusion-grid-list-view li']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_bg_color' ) );
		$css['global']['.fusion-grid-list-view li']['border-color']     = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_border_color' ) );

		$css['global']['.fusion-grid-list-view a']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );

		$css['global']['.fusion-grid-list-view li:hover']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );

		$css['global']['.fusion-grid-list-view li:hover']['background-color'] = Fusion_Sanitize::color( fusion_color_luminance( Avada()->settings->get( 'woo_dropdown_bg_color' ), 0.1 ) );

		$css['global']['.fusion-grid-list-view li.active-view']['background-color'] = Fusion_Sanitize::color( fusion_color_luminance( Avada()->settings->get( 'woo_dropdown_bg_color' ), 0.1 ) );

		$css['global']['.fusion-grid-list-view li.active-view a i']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'woo_dropdown_text_color' ) );

		if ( is_rtl() ) {
			$woo_message_direction = 'right';
		} else {
			$woo_message_direction = 'left';
		}
		$elements = array(
			'.woocommerce-message:before',
			'.woocommerce-info:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ][ 'margin-' . $woo_message_direction ] = Fusion_Sanitize::add_css_values( array( '-' . Fusion_Sanitize::size( Avada()->settings->get( 'body_typography', 'font-size' ) ), '-3px' ) );

		$elements = array(
			'.woocommerce-message',
			'.woocommerce-info',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ][ 'padding-' . $woo_message_direction ] = Fusion_Sanitize::add_css_values( array( Fusion_Sanitize::size( Avada()->settings->get( 'body_typography', 'font-size' ) ), '3px' ) );

	}

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements = array(
			'.tribe-grid-allday .tribe-events-week-allday-single, .tribe-grid-allday .tribe-events-week-allday-single:hover, .tribe-grid-body .tribe-events-week-hourly-single',
			'.datepicker.dropdown-menu .datepicker-days table tr td.active:hover',
		);
		$color    = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
		$rgb      = fusion_hex2rgb( $color );
		$rgba     = 'rgba( ' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',0.7)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $rgba;

		$elements = array(
			'.fusion-tribe-primary-info .tribe-events-list-event-title a',
			'.fusion-events-single-title-content',
			'.fusion-tribe-primary-info .tribe-events-list-event-title a',
			'.datepicker.dropdown-menu table tr td.day',
			'.datepicker.dropdown-menu table tr td span.month',
			'.tribe-events-venue-widget .tribe-venue-widget-thumbnail .tribe-venue-widget-venue-name',
			".tribe-mini-calendar div[id*='daynum-'] a, .tribe-mini-calendar div[id*='daynum-'] span",
		);
		$color    = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
		$rgb      = fusion_hex2rgb( $color );
		$rgba     = 'rgba( ' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',0.85)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $rgba;

		$elements = array(
			'.tribe-events-list .tribe-events-event-cost',
			'.tribe-events-list .tribe-events-event-cost span',
			'.fusion-tribe-events-headline',
			'#tribe-events .tribe-events-day .tribe-events-day-time-slot .tribe-events-day-time-slot-heading',
			'.tribe-mobile-day-date',
			'.tribe-mobile-day .tribe-mobile-day-heading',
			'.datepicker.dropdown-menu table thead tr:nth-child(2)',
			'.datepicker.dropdown-menu table thead tr:nth-child(2) th:hover',
			'.datepicker.dropdown-menu .datepicker-days table tr td.active',
			'.datepicker.dropdown-menu .datepicker-days table tr td:hover',
			'.tribe-grid-header',
			'.datepicker.dropdown-menu table tr td span.month.active',
			'.datepicker.dropdown-menu table tr td span.month:hover',
			'.fusion-body .tribe-grid-body div[id*="tribe-events-event-"]:hover',
			'.tribe-events-venue-widget .tribe-venue-widget-venue-name',
			'.tribe-mini-calendar .tribe-mini-calendar-nav td',
			".tribe-mini-calendar div[id*='daynum-'] a:hover",
			'.tribe-mini-calendar td.tribe-events-has-events:hover a',
			'.fusion-body .tribe-mini-calendar td.tribe-events-has-events:hover a:hover',
			'.fusion-body .tribe-mini-calendar td.tribe-events-has-events a:hover',
			'.tribe-mini-calendar td.tribe-events-has-events.tribe-events-present a:hover',
			'.tribe-mini-calendar td.tribe-events-has-events.tribe-mini-calendar-today a:hover',
			".tribe-mini-calendar .tribe-mini-calendar-today div[id*='daynum-'] a",
			".tribe-mini-calendar .tribe-mini-calendar-today div[id*='daynum-'] a",
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

		$elements = array(
			'.tribe-grid-header',
			'.tribe-events-grid .tribe-grid-header .tribe-grid-content-wrap .column',
			'.fusion-body .tribe-grid-body div[id*="tribe-events-event-"]',
			'.fusion-body .tribe-grid-body div[id*="tribe-events-event-"]:hover',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );

		$elements = array(
			'.tribe-events-calendar thead th',
			'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]',
			'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]>a',
			'.tribe-events-calendar div[id*=tribe-events-daynum-]',
			'.tribe-events-calendar div[id*=tribe-events-daynum-] a',
			'.tribe-events-calendar td.tribe-events-past div[id*=tribe-events-daynum-]',
			'.tribe-events-calendar td.tribe-events-past div[id*=tribe-events-daynum-]>a',
			'#tribe-events-content .tribe-events-tooltip .entry-title',
			'#tribe-events-content .tribe-events-tooltip .tribe-event-title',
			'.tribe-events-list-separator-month span',
			'.fusion-body #tribe-events .fusion-tribe-primary-info .tribe-events-list-event-title',
			'.fusion-body #tribe-events .fusion-tribe-primary-info .tribe-events-list-event-title a',
			'.tribe-events-list .tribe-events-event-cost',
			'#tribe-events .fusion-tribe-events-headline h3',
			'#tribe-events .fusion-tribe-events-headline h3 a',
			'#tribe-events .tribe-events-day .tribe-events-day-time-slot .tribe-events-day-time-slot-heading',
			'.tribe-mobile-day .tribe-mobile-day-date',
			'.tribe-mobile-day .tribe-mobile-day-heading',
			'.datepicker.dropdown-menu table thead tr:nth-child(2)',
			'.datepicker.dropdown-menu table tr td.day',
			'.fusion-events-single-title-content h2',
			'.fusion-events-single-title-content h3',
			'.fusion-events-single-title-content span',
			'.tribe-grid-header',
			'.fusion-body .tribe-grid-body div[id*="tribe-events-event-"] .entry-title a',
			'.fusion-body .tribe-grid-body div[id*="tribe-events-event-"]:hover .entry-title a',
			'.datepicker.dropdown-menu .datepicker-days table tr td.active:hover',
			'.datepicker.dropdown-menu table tr td span.month',
			'.datepicker.dropdown-menu table tr td span.month.active:hover',
			'.recurringinfo',
			'.fusion-events-featured-image .event-is-recurring',
			'.fusion-events-featured-image .event-is-recurring:hover',
			'.fusion-events-featured-image .event-is-recurring a',
			'.single-tribe_events .fusion-events-featured-image .recurringinfo .tribe-events-divider',
			'.tribe-events-venue-widget .tribe-venue-widget-venue-name, .tribe-events-venue-widget .tribe-venue-widget-venue-name a, #slidingbar-area .tribe-events-venue-widget .tribe-venue-widget-venue-name a',
			'.tribe-events-venue-widget .tribe-venue-widget-venue-name, .tribe-events-venue-widget .tribe-venue-widget-venue-name a:hover, #slidingbar-area .tribe-events-venue-widget .tribe-venue-widget-venue-name a:hover',
			'.tribe-mini-calendar .tribe-mini-calendar-nav td',
			".tribe-mini-calendar div[id*='daynum-'] a, .tribe-mini-calendar div[id*='daynum-'] span",
			"#slidingbar-area .tribe-mini-calendar div[id*='daynum-'] a",
			".tribe-mini-calendar div[id*='daynum-'] a:hover",
			'.tribe-mini-calendar .tribe-events-has-events:hover',
			'.tribe-mini-calendar .tribe-events-has-events:hover a',
			'.tribe-mini-calendar .tribe-events-has-events:hover a:hover',
			'.tribe-mini-calendar .tribe-events-has-events a:hover',
			'.tribe-mini-calendar .tribe-events-has-events.tribe-events-present a:hover',
			'.tribe-mini-calendar td.tribe-events-has-events.tribe-mini-calendar-today a:hover',
			'.tribe-mini-calendar .tribe-events-has-events.tribe-mini-calendar-today a',
			'.tribe-mini-calendar .tribe-events-has-events.tribe-mini-calendar-today a',
			".tribe-mini-calendar .tribe-events-othermonth.tribe-mini-calendar-today div[id*='daynum-'] a",
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_overlay_text_color' ) );

		$elements = array(
			'#tribe-events .tribe-events-list .tribe-events-event-meta .author > div',
			'.fusion-body #tribe-events .tribe-events-list .tribe-events-event-meta .author > div:last-child',
			'.events-list #tribe-events-footer, .single-tribe_events #tribe-events-footer, #tribe-events #tribe-events-footer',
			'.tribe-grid-allday',
			'.tribe-events-grid',
			'.tribe-events-grid .tribe-scroller',
			'.tribe-events-grid .tribe-grid-content-wrap .column',
			'.tribe-week-grid-block div',
			'#tribe-events #tribe-geo-results .type-tribe_events:last-child',
			'.events-archive.events-gridview #tribe-events-content table .type-tribe_events',
			'.tribe-events-viewmore',
			'.fusion-events-before-title .tribe-events-page-title',
			'.fusion-events-before-title .fusion-events-title-above:before',
			'.fusion-events-before-title .fusion-events-title-above:after',
			'#tribe-events .tribe-events-list .type-tribe_events',
			'#tribe-events .tribe-events-list-separator-month+.type-tribe_events.tribe-events-first',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_border_color' ) );

		$elements = array(
			'.tribe-bar-views-inner',
			'#tribe-bar-views .tribe-bar-views-list',
			'#tribe_events_filters_wrapper .tribe-events-filters-group-heading',
			'.tribe-block__tickets__registration__event .tribe-block__tickets__registration__summary .tribe-block__tickets__registration__toggle__handler',
			'.tribe-block__tickets__registration__event .tribe-block__tickets__item__attendee__fields',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_bg_color' ) );

		$elements = array(
			'#tribe_events_filters_wrapper .tribe-events-filters-group-heading',
			'.tribe-events-filter-group',
			'.tribe-events-filter-group:after',
			'#tribe_events_filters_wrapper .tribe-events-filter-group label',
			'.tribe-events-filters-horizontal .tribe-events-filter-group:before',
			'.tribe-events-filters-horizontal .tribe-events-filter-group:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom-color'] = fusion_adjust_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_bg_color' ) ), -25 );

		$elements = array(
			'#tribe-bar-form',
			'#tribe-events-bar:before',
			'#tribe-events-bar:after',
			'#tribe-events-content-wrapper #tribe_events_filters_wrapper.tribe-events-filters-horizontal:before',
			'#tribe-events-content-wrapper #tribe_events_filters_wrapper.tribe-events-filters-horizontal:after',
			'#tribe-bar-collapse-toggle',
			'#tribe-bar-collapse-toggle:hover',
			'#tribe-bar-collapse-toggle:focus',
			'#tribe-bar-form.tribe-bar-collapse .tribe-bar-views-inner',
			'#tribe-bar-form.tribe-bar-collapse .tribe-bar-views-list',
			'#tribe-bar-form.tribe-bar-collapse .tribe-bar-filters',
			'#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a:hover',
			'#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option.tribe-bar-active a:hover',
			'#tribe-events-content-wrapper #tribe_events_filters_wrapper.tribe-events-filters-horizontal',
			'#tribe-events-content-wrapper #tribe_events_filters_wrapper.tribe-events-filters-vertical .tribe-events-filters-content',
			'#tribe-events-content-wrapper #tribe_events_filters_wrapper:before',
			'#tribe-events-content-wrapper #tribe_events_filters_wrapper:after',
			'.tribe-events-filter-group.tribe-events-filter-autocomplete',
			'.tribe-events-filter-group.tribe-events-filter-multiselect',
			'.tribe-events-filter-group.tribe-events-filter-range',
			'.tribe-events-filter-group.tribe-events-filter-select',
			'#tribe_events_filters_wrapper .tribe-events-filters-group-heading:hover',
			'#tribe_events_filters_wrapper .tribe-events-filter-group label',
			'#tribe_events_filters_wrapper .closed .tribe-events-filters-group-heading:hover',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = fusion_adjust_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_bg_color' ) ), 10 );

		$elements = array(
			'#tribe-bar-views-toggle:focus',
			'#tribe-bar-views-toggle:hover',
			'#tribe-bar-views .tribe-bar-views-option.tribe-bar-active',
			'#tribe-bar-views .tribe-bar-views-option:hover',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = fusion_adjust_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_bg_color' ) ), -15 );

		$css['global']['.tribe-events-filters-horizontal .tribe-events-filter-group']['border-color'] = fusion_adjust_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_bg_color' ) ), -25 );

		$css['global']['.tribe-events-filter-group:after']['border-bottom-color'] = fusion_adjust_brightness( Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_bg_color' ) ), 10 );

		$elements = array(
			'#tribe-bar-form label',
			'.tribe-bar-disabled #tribe-bar-form label',
			'#tribe-bar-form.tribe-bar-collapse #tribe-bar-collapse-toggle',
			'#tribe-events-bar #tribe-bar-views .tribe-bar-views-toggle',
			'#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option',
			'#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a',
			'#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a:hover',
			'#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option.tribe-bar-active a:hover',
			'#tribe_events_filters_wrapper .tribe-events-filters-label',
			'#tribe_events_filters_wrapper .tribe-events-filters-group-heading',
			'#tribe_events_filters_wrapper .tribe-events-filters-group-heading:after',
			'#tribe_events_filters_wrapper .tribe-events-filters-content > label',
			'#tribe_events_filters_wrapper label span',
			'.tribe-block__tickets__registration__event .tribe-block__tickets__registration__summary .tribe-block__tickets__registration__toggle__handler',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_bar_text_color' ) );

		$elements = array(
			'.tribe-events-calendar div[id*=tribe-events-daynum-]',
			'.tribe-events-calendar div[id*=tribe-events-daynum-] a',
			'.tribe-events-grid .tribe-grid-header .tribe-week-today',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_calendar_heading_bg_color' ) );

		$elements = array(
			'#tribe-events-content .tribe-events-calendar td.tribe-events-othermonth',
			'.tribe-events-calendar td.tribe-events-past div[id*=tribe-events-daynum-]',
			'.tribe-events-calendar td.tribe-events-past div[id*=tribe-events-daynum-]>a',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( fusion_adjust_brightness( Avada()->settings->get( 'ec_calendar_heading_bg_color' ), 40 ) );

		$css['global']['#tribe-events-content .tribe-events-calendar td']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_calendar_bg_color' ) );

		$css['global']['#tribe-events-content .tribe-events-calendar td.tribe-events-othermonth']['background-color'] = Fusion_Sanitize::color( fusion_adjust_brightness( Avada()->settings->get( 'ec_calendar_bg_color' ), 80 ) );

		$elements = array(
			'#tribe-events-content .tribe-events-calendar td',
			'#tribe-events-content table.tribe-events-calendar',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_border_color' ) );

		$elements = array(
			'#tribe-events-content .tribe-events-calendar td:hover',
			'.tribe-week-today',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( fusion_adjust_brightness( Avada()->settings->get( 'ec_calendar_bg_color' ), 60 ) );

		$elements = array(
			'.tribe-grid-allday',
			'.tribe-week-grid-hours',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( fusion_adjust_brightness( Avada()->settings->get( 'ec_calendar_bg_color' ), 70 ) );

		$elements = array(
			'.recurring-info-tooltip',
			'#tribe-events-content .tribe-events-tooltip',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_tooltip_bg_color' ) );

		$elements = array(
			'.tribe-grid-body .tribe-events-tooltip:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-right-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_tooltip_bg_color' ) );

		$elements = array(
			'.tribe-grid-body .tribe-events-right .tribe-events-tooltip:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-left-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_tooltip_bg_color' ) );

		$elements = array(
			'.tribe-events-month .tribe-events-tooltip:after',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_tooltip_bg_color' ) );

		$elements = array(
			'.tribe-events-month .tribe-events-tooltip.tribe-events-tooltip-flipdown:before',
			'.tribe-events-month .tribe-events-right .tribe-events-tooltip.tribe-events-tooltip-flipdown:before',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top-color']    = 'transparent';

		$css['global']['#tribe-events-content .tribe-events-tooltip']['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'ec_tooltip_body_color' ) );

		$elements = array(
			'#wrapper .tribe-events-list .tribe-events-list-separator-month',
			'#wrapper .tribe-events-list .tribe-events-day-time-slot-heading',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'ec_sep_heading_font_size' ) );

	}

	// Non-responsive mode.
	if ( ! Avada()->settings->get( 'responsive' ) ) {

		if ( 'Top' === Avada()->settings->get( 'header_position' ) ) {
			$elements = array( 'html', 'body' );
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['overflow-x'] = 'hidden';
		} else {
			$css['global']['.ua-mobile #wrapper']['width'] = 'auto !important';
		}

		$media_query = '@media screen and (max-width: ' . intval( Avada()->settings->get( 'side_header_break_point' ) ) . 'px)';
		$css[ $media_query ]['.fusion-main-menu > ul > li']['padding-right'] = intval( Avada()->settings->get( 'mobile_nav_padding' ) ) . 'px';
	}

	$header_bg         = Avada_Helper::get_header_color( $c_page_id, $fusion_taxonomy_options );
	$header_bg_opacity = Fusion_Color::new_color( $header_bg )->alpha;

	// Responsive mode.
	if ( Avada()->settings->get( 'responsive' ) ) {
		/*
		Media Queries
			00 Side Width / Layout Responsive Styles
				# General Styles
				# Grid System
			01 Side Header Responsive Styles
			02 Top Header Responsive Styles
			03 Mobile Menu Responsive Styles
			04 @media only screen and ( max-width: $content_break_point )
				# Layout
				# General Styles
				# Page Title Bar
				# Blog Layouts
				# Author Page - Info
				# Shortcodes
				# Events Calendar
				# Woocommerce
				# Not restructured mobile.css styles
			05 @media only screen and ( min-width: $content_break_point )
				# Shortcodes
			06 @media only screen and ( max-width: 640px )
				# Layout
				# General Styles
				# Page Title Bar
				# Blog Layouts
				# Footer Styles
				# Filters
				# Not restructured mobile.css styles
			07 @media only screen and ( min-device-width: 320px ) and ( max-device-width: 640px )
			08 @media only screen and ( max-width: 480px )
			09 media.css CSS
			10 iPad Landscape Responsive Styles
				# Footer Styles
			11 iPad Portrait Responsive Styles
				# Layout
				# Footer Styles
		*/

		$side_header_width = ( 'Top' === Avada()->settings->get( 'header_position' ) ) ? 0 : intval( Avada()->settings->get( 'side_header_width' ) );

		/**
		 * Side Header Only Responsive Styles.
		 */
		$side_header_media_query     = '@media only screen and (max-width: ' . intval( Avada()->settings->get( 'side_header_break_point' ) ) . 'px)';
		$side_header_min_media_query = '@media only screen and (min-width: ' . intval( Avada()->settings->get( 'side_header_break_point' ) ) . 'px)';

		if ( Avada()->settings->get( 'logo_background' ) && 'v4' !== Avada()->settings->get( 'header_layout' ) && 'v5' !== Avada()->settings->get( 'header_layout' ) && 'Top' === Avada()->settings->get( 'header_position' ) ) {
			$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'logo_background_color' ) );
			if ( 'v7' !== Avada()->settings->get( 'header_layout' ) ) {
				$alignment = ( 'Center' === Avada()->settings->get( 'logo_alignment' ) ) ? 'left' : Avada()->settings->get( 'logo_alignment' );
				$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['float'] = $alignment;
				if ( Avada()->settings->get( 'header_100_width' ) ) {
					$css[ $side_header_min_media_query ]['.fusion-header'][ 'padding-' . $alignment ] = '0px';
				}
			}
			$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['display']                                     = 'inline-flex';
			$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['position']                                    = 'relative';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['content']        = '""';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['pointer-events'] = 'none';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['position']       = 'absolute';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['top']            = '-' . Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'top' ) );
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['left']           = '0';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['width']          = '100%';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['height']         = '100%';
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['border-top']     = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'top' ) ) . ' solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'logo_background_color' ) );
			$css[ $side_header_min_media_query ]['.fusion-header-wrapper .fusion-header .fusion-logo-background:after']['border-bottom']  = Fusion_Sanitize::size( Avada()->settings->get( 'header_padding', 'bottom' ) ) . ' solid ' . Fusion_Sanitize::color( Avada()->settings->get( 'logo_background_color' ) );
			if ( Avada()->settings->get( 'header_sticky_shrinkage' ) ) {
				$css[ $side_header_min_media_query ]['.fusion-header-wrapper.fusion-is-sticky .fusion-header .fusion-logo-background:after']['transition']          = 'border-width 0.25s ease-in-out';
				$css[ $side_header_min_media_query ]['.fusion-header-wrapper.fusion-is-sticky .fusion-header .fusion-logo-background:after']['border-bottom-width'] = '0px';
			}
			if ( 'v6' === Avada()->settings->get( 'header_layout' ) && 'Right' === $alignment ) {
				$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['order'] = '2';
			}
			if ( 'v7' === Avada()->settings->get( 'header_layout' ) && 'background' !== Avada()->settings->get( 'menu_highlight_style' ) ) {
				$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['padding-right']                   = '0px';
				$css[ $side_header_min_media_query ]['.fusion-header .fusion-logo-background']['margin-right']                    = intval( Avada()->settings->get( 'nav_padding' ) ) . 'px';
				$css[ $side_header_min_media_query ]['.fusion-is-sticky .fusion-header .fusion-logo-background']['padding-right'] = '0px';
				$css[ $side_header_min_media_query ]['.fusion-is-sticky .fusion-header .fusion-logo-background']['margin-right']  = intval( Avada()->settings->get( 'header_sticky_nav_padding' ) ) . 'px';
			}
		}

		if ( 'Top' !== Avada()->settings->get( 'header_position' ) && Avada()->settings->get( 'logo_background' ) ) {
			$elements = array(
				'.side-header-content.fusion-logo-center',
				'.side-header-content.fusion-logo-left',
				'.side-header-content.fusion-logo-right',
			);
			$css[ $side_header_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'logo_background_color' ) );
		}

		$mobile_header_bg         = Avada_Helper::get_header_color( $c_page_id, $fusion_taxonomy_options, true );
		$mobile_header_bg_opacity = Fusion_Color::new_color( $mobile_header_bg )->alpha;

		$elements = array(
			'#side-header',
			'.side-header-background-color',
		);
		$css[ $side_header_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $mobile_header_bg;
		$css[ $side_header_media_query ]['#side-header.fusion-is-sticky']['background-color']              = Fusion_Sanitize::color( Avada()->settings->get( 'header_sticky_bg_color' ) );

		$css[ $side_header_min_media_query ]['body.layout-boxed-mode.side-header-right #side-header .side-header-wrapper']['width'] = intval( Avada()->settings->get( 'side_header_width' ) ) . 'px';

		/*
		Top Header Only Responsive Styles.
		*/
		$mobile_header_media_query     = '@media only screen and (max-width: ' . intval( Avada()->settings->get( 'side_header_break_point' ) ) . 'px)';
		$mobile_header_min_media_query = '@media only screen and (min-width: ' . intval( Avada()->settings->get( 'side_header_break_point' ) ) . 'px)';

		$elements = array(
			'.fusion-mobile-menu-design-modern .fusion-secondary-menu .fusion-secondary-menu-icon',
			'.fusion-mobile-menu-design-modern .fusion-secondary-menu .fusion-secondary-menu-icon:hover',
			'.fusion-mobile-menu-design-modern .fusion-secondary-menu-icon:before',
			'.fusion-mobile-menu-design-flyout .fusion-secondary-menu .fusion-secondary-menu-icon',
			'.fusion-mobile-menu-design-flyout .fusion-secondary-menu .fusion-secondary-menu-icon:hover',
			'.fusion-mobile-menu-design-flyout .fusion-secondary-menu-icon:before',
		);
		$css[ $mobile_header_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['color'] = Fusion_Sanitize::color( Avada()->settings->get( 'snav_color' ) );

		if ( ! Avada()->settings->get( 'main_nav_search_icon' ) && Avada()->settings->get( 'mobile_menu_search' ) ) {
			$css[ $mobile_header_media_query ]['.fusion-header-v6.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle']['display'] = 'flex';
		} elseif ( ! Avada()->settings->get( 'mobile_menu_search' ) ) {
			$css[ $mobile_header_media_query ]['.fusion-header-v6.fusion-header-has-flyout-menu .fusion-flyout-menu-icons .fusion-flyout-search-toggle']['display'] = 'none';
		}

		// Desktop absolute header.
		if ( 1 > $header_bg_opacity ) {
			$elements = array(
				'.fusion-header',
				'.fusion-secondary-header',
			);
			$css[ $mobile_header_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['border-top'] = 'none';

			$elements = array(
				'.fusion-header-v1 .fusion-header',
				'.fusion-secondary-main-menu',
			);
			$css[ $mobile_header_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['border'] = 'none';
			$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper']['position']                   = 'absolute';
			$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper']['z-index']                    = '10000';

			if ( 'boxed' === fusion_get_option( 'layout', 'page_bg_layout', $c_page_id ) ) {
				$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper']['width']     = '100%';
				$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper']['max-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
			} else {
				$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper']['left']  = '0';
				$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper']['right'] = '0';

			}
		}

		/*
		Mobile Menu Responsive Styles
		*/
		$mobile_menu_media_query = '@media only screen and (max-width: ' . ( intval( $side_header_width ) + intval( Avada()->settings->get( 'side_header_break_point' ) ) ) . 'px)';

		// Mobile absolute header.
		if ( 1 > $mobile_header_bg_opacity ) {
			$css[ $mobile_menu_media_query ]['.fusion-header-wrapper']['position'] = 'absolute';
			$css[ $mobile_menu_media_query ]['.fusion-header-wrapper']['z-index']  = '10000';
			if ( 'boxed' === fusion_get_option( 'layout', 'page_bg_layout', $c_page_id ) ) {
				$css[ $mobile_menu_media_query ]['.fusion-header-wrapper']['width']     = '100%';
				$css[ $mobile_menu_media_query ]['.fusion-header-wrapper']['max-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
			} else {
				$css[ $mobile_menu_media_query ]['.fusion-header-wrapper']['left']  = '0';
				$css[ $mobile_menu_media_query ]['.fusion-header-wrapper']['right'] = '0';
			}
		}

		// Mobile absolute side-header.
		if ( 1 > $mobile_header_bg_opacity ) {
			$css[ $side_header_media_query ]['.fusion-body #side-header']['position'] = 'absolute';
			$css[ $side_header_media_query ]['.fusion-body #side-header']['z-index']  = '10000';
			if ( 'boxed' === fusion_get_option( 'layout', 'page_bg_layout', $c_page_id ) ) {
				$css[ $side_header_media_query ]['.fusion-body #side-header']['width']     = '100%';
				$css[ $side_header_media_query ]['.fusion-body #side-header']['max-width'] = Fusion_Sanitize::add_css_values( array( Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) ), '-' . intval( Avada()->settings->get( 'side_header_width' ) ) . 'px' ) );
			} else {
				$css[ $side_header_media_query ]['.fusion-body #side-header']['left']  = '0';
				$css[ $side_header_media_query ]['.fusion-body #side-header']['right'] = '0';
			}
		}

		if ( 1 > $header_bg_opacity ) {
			$css[ $mobile_header_min_media_query ]['.fusion-header-wrapper .fusion-header']['background-image'] = 'none';
		}
		if ( 1 > $mobile_header_bg_opacity ) {
			$css[ $mobile_menu_media_query ]['.fusion-header-wrapper .fusion-header']['background-image'] = 'none';
		}

		$elements = array(
			'.fusion-header-wrapper .fusion-header',
			'.fusion-header-wrapper .fusion-secondary-main-menu',
		);
		$css[ $mobile_menu_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( $mobile_header_bg );

		$css[ $mobile_menu_media_query ]['.fusion-secondary-header']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'header_top_bg_color' ) );

		if ( 'Right' === Avada()->settings->get( 'logo_alignment' ) ) {
			$css[ $mobile_menu_media_query ]['.fusion-mobile-menu-design-modern.fusion-header-v7 .fusion-main-menu']['float'] = 'right';
		}

		$elements = array(
			'.fusion-mobile-menu-design-modern.fusion-header-v1 .fusion-mobile-nav-holder',
			'.fusion-mobile-menu-design-modern.fusion-header-v2 .fusion-mobile-nav-holder',
			'.fusion-mobile-menu-design-modern.fusion-header-v3 .fusion-mobile-nav-holder',
			'.fusion-mobile-menu-design-modern.fusion-header-v4 .fusion-mobile-nav-holder',
			'.fusion-mobile-menu-design-modern.fusion-header-v5 .fusion-mobile-nav-holder',
			'.fusion-mobile-menu-design-modern.fusion-header-v7 .fusion-mobile-nav-holder',
		);
		$css[ $mobile_menu_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom'] = Fusion_Sanitize::add_css_values( array( '-20px', '-' . Fusion_Sanitize::get_value_with_unit( Avada()->settings->get( 'header_padding', 'bottom' ) ) ) );

		if ( class_exists( 'SitePress' ) ) {
			$elements               = array(
				'.fusion-mobile-nav-holder .wpml-ls-item .menu-text',
				'.wpml-ls-item .menu-text, .wpml-ls-item .sub-menu a > span',
				'.fusion-mobile-nav-holder .wpml-ls-item > a',
			);
			$wpml_mobile_text_align = 'center';
			if ( 'left' === esc_attr( Avada()->settings->get( 'mobile_menu_text_align' ) ) ) {
				$wpml_mobile_text_align = ( is_rtl() ) ? 'flex-end' : 'flex-start';
			} elseif ( 'right' === esc_attr( Avada()->settings->get( 'mobile_menu_text_align' ) ) ) {
				$wpml_mobile_text_align = ( is_rtl() ) ? 'flex-start' : 'flex-end';
			}
			$css[ $mobile_menu_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['justify-content'] = $wpml_mobile_text_align;
		}

		/*
		@media only screen and ( max-width: sidebar_break_point )
		*/
		$sidebar_break_point = '@media only screen and (max-width: ' . ( intval( $side_header_width ) + intval( Avada()->settings->get( 'sidebar_break_point' ) ) ) . 'px)';

		$po_sidebar_order = get_post_meta( $c_page_id, 'pyre_responsive_sidebar_order', true );
		if ( $po_sidebar_order ) {
			$sidebar_order = explode( ',', $po_sidebar_order );
		} else {
			$sidebar_order = explode( ',', Avada()->settings->get( 'responsive_sidebar_order' ) );
		}

		$sidebar_order = apply_filters( 'fusion_responsive_sidebar_order', $sidebar_order );

		foreach ( $sidebar_order as $key => $element ) {
			$css[ $sidebar_break_point ][ '.has-sidebar #' . $element ]['order'] = $key + 1;

			if ( 0 < $key ) {
				$css[ $sidebar_break_point ][ '.has-sidebar #' . $element ]['margin-top'] = '50px';
			}
		}

		/*
		@media only screen and ( max-width: $content_break_point )
		*/
		$content_media_query     = '@media only screen and (max-width: ' . ( intval( $side_header_width ) + intval( Avada()->settings->get( 'content_break_point' ) ) ) . 'px)';
		$content_min_media_query = '@media only screen and (min-width: ' . ( intval( $side_header_width ) + intval( Avada()->settings->get( 'content_break_point' ) ) ) . 'px)';

		// # Layout
		if ( Avada()->settings->get( 'smooth_scrolling' ) ) {
			$css[ $content_min_media_query ]['.no-overflow-y body']['padding-right'] = '9px !important';
			$css[ $content_min_media_query ]['.no-overflow-y .modal']['overflow-y']  = 'hidden';

			$elements = array(
				'.no-overflow-y .fusion-sliding-bar-position-top',
				'.no-overflow-y .fusion-sliding-bar-position-bottom',
			);
			$css[ $content_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['right'] = '9px';

			if ( Avada()->settings->get( 'slidingbar_widgets' ) && 'right' === $sliding_bar_position ) {
				$sliding_bar_toggle_width   += 9;
				$sliding_bar_closed_position = 'calc(' . $sliding_bar_toggle_width . 'px - ' . $sliding_bar_width_original . ')';

				$css[ $content_min_media_query ]['.no-overflow-y .fusion-sliding-bar-position-right:not(.open)']['right'] = $sliding_bar_closed_position;
			}
		}

		if ( ! Avada()->settings->get( 'breadcrumb_mobile' ) ) {
			$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar .fusion-breadcrumbs']['display'] = 'none';
		}

		if ( get_post_meta( $c_page_id, 'pyre_fallback', true ) ) {
			$css[ $content_media_query ]['#sliders-container']['display'] = 'none';
			$css[ $content_media_query ]['#fallback-slide']['display']    = 'block';
		}

		// Mobile Logo.
		if ( Avada()->settings->get( 'mobile_logo', 'url' ) ) {
			$elements = array(
				'.fusion-mobile-logo-1 .fusion-standard-logo',
				'#side-header .fusion-mobile-logo-1 .fusion-standard-logo',
			);
			$css[ $mobile_header_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';

			$elements = array(
				'.fusion-mobile-logo',
				'#side-header .fusion-mobile-logo',
			);
			$css[ $mobile_header_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'inline-block';
		}

		// # Page Title Bar
		if ( 'auto' !== Avada()->settings->get( 'page_title_mobile_height' ) ) {
			$page_title_mobile_height = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_mobile_height' ) );
			$page_title_bar_height    = ( strpos( $page_title_mobile_height, 'em' ) ) ? 'calc(' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_mobile_height ) . ')' : $page_title_mobile_height;

			$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['padding-top']    = '5px';
			$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['padding-bottom'] = '5px';
			$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['min-height']     = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );
			$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['height']         = 'auto';

			$css[ $content_media_query ]['.fusion-page-title-row']['display']     = 'flex';
			$css[ $content_media_query ]['.fusion-page-title-row']['align-items'] = 'center';
			$css[ $content_media_query ]['.fusion-page-title-row']['width']       = '100%';
			$css[ $content_media_query ]['.fusion-page-title-row']['min-height']  = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );

			// Special case for IE10/IE11.
			$css[ $content_media_query ]['.ua-ie-11 .fusion-page-title-row']['height']     = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );
			$css[ $content_media_query ]['.ua-ie-10 .fusion-page-title-row']['height']     = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );
			$css[ $content_media_query ]['.ua-ie-11 .fusion-page-title-wrapper']['height'] = 'auto';
			$css[ $content_media_query ]['.ua-ie-10 .fusion-page-title-wrapper']['height'] = 'auto';

			$css[ $content_media_query ]['.fusion-page-title-bar-center .fusion-page-title-row']['width'] = 'auto';

			$css[ $content_media_query ]['.fusion-page-title-captions']['width'] = '100%';
		}

		if ( get_post_meta( $c_page_id, 'pyre_page_title_mobile_height', true ) ) {

			if ( 'auto' !== get_post_meta( $c_page_id, 'pyre_page_title_mobile_height', true ) ) {

				$page_title_mobile_height = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_page_title_mobile_height', true ) );
				$page_title_bar_height    = ( strpos( $page_title_mobile_height, 'em' ) ) ? 'calc(' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_mobile_height ) . ')' : $page_title_mobile_height;

				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['min-height'] = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );
				$css[ $content_media_query ]['.fusion-page-title-row']['min-height']              = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );

				// Special case for IE10/IE11.
				$css[ $content_media_query ]['.ua-ie-11 .fusion-page-title-row']['height'] = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );
				$css[ $content_media_query ]['.ua-ie-10 .fusion-page-title-row']['height'] = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );

				$css[ $content_media_query ]['.fusion-page-title-row']['display']     = 'flex';
				$css[ $content_media_query ]['.fusion-page-title-row']['align-items'] = 'center';

				$css[ $content_media_query ]['.fusion-page-title-captions']['width'] = '100%';

			} else {

				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['padding-top']    = '10px';
				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['padding-bottom'] = '10px';
				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['height']         = 'auto';

			}
		}

		if ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_mobile_height' ) ) {

			if ( 'auto' !== Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_mobile_height' ) ) {

				$page_title_mobile_height = Fusion_Sanitize::size( Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_mobile_height' ) );
				$page_title_height        = ( strpos( $page_title_mobile_height, 'em' ) ) ? 'calc(' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_mobile_height ) . ')' : $page_title_mobile_height;

				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['min-height'] = Fusion_Sanitize::add_css_values( array( $page_title_height, '-10px' ) );
				$css[ $content_media_query ]['.fusion-page-title-row']['min-height']              = Fusion_Sanitize::add_css_values( array( $page_title_height, '-10px' ) );

				// Special case for IE10/IE11.
				$css[ $content_media_query ]['.ua-ie-11 .fusion-page-title-row']['height'] = Fusion_Sanitize::add_css_values( array( $page_title_height, '-10px' ) );
				$css[ $content_media_query ]['.ua-ie-10 .fusion-page-title-row']['height'] = Fusion_Sanitize::add_css_values( array( $page_title_height, '-10px' ) );

				$css[ $content_media_query ]['.fusion-page-title-row']['display']     = 'flex';
				$css[ $content_media_query ]['.fusion-page-title-row']['align-items'] = 'center';

				$css[ $content_media_query ]['.fusion-page-title-captions']['width'] = '100%';

			} else {

				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['padding-top']    = '10px';
				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['padding-bottom'] = '10px';
				$css[ $content_media_query ]['.fusion-body .fusion-page-title-bar']['height']         = 'auto';

			}
		}

		if ( class_exists( 'WooCommerce' ) ) {

			$css[ $content_media_query ]['.shop_table_responsive .product-remove']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'qty_bg_color' ) );

			if ( is_rtl() ) {
				$css[ $content_media_query ]['.shop_table_responsive .product-remove']['left'] = '0';
			} else {
				$css[ $content_media_query ]['.shop_table_responsive .product-remove']['right'] = '0';
			}

			if ( Avada()->settings->get( 'disable_woo_gallery' ) ) {
				$css[ $content_media_query ]['.product .entry-summary .summary-container']['margin-top'] = '20px';
			}
		}

		// Sliding bar position already set above for sliding bar desktop calcs.
		if ( Avada()->settings->get( 'slidingbar_widgets' ) && Avada()->settings->get( 'mobile_slidingbar_widgets' ) && ( 'right' === $sliding_bar_position || 'left' === $sliding_bar_position ) ) {

			// On mobile for left/right sliding bar the width should be 100vw - triangle toggle width.
			$sliding_bar_width           = '100vw';
			$sliding_bar_closed_position = '-' . $sliding_bar_width;
			$sliding_bar_toggle_width    = '56px'; // 20px added for scroll bar.

			if ( 'menu' === Avada()->settings->get( 'slidingbar_toggle_style' ) ) {
				$sliding_bar_toggle_width = '0';
			}

			if ( $sliding_bar_toggle_width ) {
				$sliding_bar_closed_position = 'calc(' . $sliding_bar_toggle_width . ' - ' . $sliding_bar_width . ')';
				$sliding_bar_width           = 'calc(' . $sliding_bar_width . ' - ' . $sliding_bar_toggle_width . ')';
			}

			$elements = array(
				'.fusion-sliding-bar-position-right .fusion-sliding-bar',
				'.fusion-sliding-bar-position-left .fusion-sliding-bar',
			);
			$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = $sliding_bar_width;

			$css[ $content_media_query ][ '.fusion-sliding-bar-position-' . $sliding_bar_position ][ $sliding_bar_position ] = $sliding_bar_closed_position;
		}

		// # Events Calendar
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			if ( ( Avada()->settings->get( 'main_padding', 'top' ) || '0' == Avada()->settings->get( 'main_padding', 'top' ) ) && ! get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) && '0' != get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) ) {
				$css['global']['.tribe-mobile #main']['padding-top'] = Fusion_Sanitize::size( Avada()->settings->get( 'main_padding', 'top' ) );
			} elseif ( get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) ) {
				$css['global']['.tribe-mobile #main']['padding-top'] = get_post_meta( $c_page_id, 'pyre_main_top_padding', true );
			} elseif ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'main_padding_top' ) ) {
				$css['global']['.tribe-mobile #main']['padding-top'] = Fusion_Sanitize::size( Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'main_padding_top' ) );
			} else {
				$css['global']['.tribe-mobile #main']['padding-top'] = '55px !important';
			}
		}

		/*
		Landscape Responsive Styles - iPad
		*/
		$ipad_landscape_media_query = '@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape)';

		$menu_elements = array(
			'.fusion-header-v1 .fusion-main-menu > ul > li',
			'.fusion-header-v2 .fusion-main-menu > ul > li',
			'.fusion-header-v3 .fusion-main-menu > ul > li',
			'.fusion-header-v4 .fusion-main-menu > ul > li',
			'.fusion-header-v5 .fusion-main-menu > ul > li',
			'.fusion-header-v7 .fusion-main-menu > ul > li',
		);

		if ( 'background' !== Avada()->settings->get( 'menu_highlight_style' ) ) {
			if ( is_rtl() ) {
				$css[ $ipad_landscape_media_query ][ $dynamic_css_helpers->implode( $menu_elements, '.rtl' ) ]['padding-left'] = intval( Avada()->settings->get( 'mobile_nav_padding' ) ) . 'px';
			} else {
				$css[ $ipad_landscape_media_query ][ $dynamic_css_helpers->implode( $menu_elements ) ]['padding-right'] = intval( Avada()->settings->get( 'mobile_nav_padding' ) ) . 'px';
			}
		}

		if ( Avada()->settings->get( 'footerw_bg_image' ) && ( in_array( Avada()->settings->get( 'footer_special_effects' ), array( 'footer_parallax_effect', 'footer_area_bg_parallax', 'footer_sticky_with_parallax_bg_image' ) ) ) ) {
			$css[ $ipad_landscape_media_query ]['.fusion-body #wrapper']['background-color'] = 'transparent';
		}

		/*
		Portrait Responsive Styles - iPad
		*/
		$ipad_portrait_media_query = '@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: portrait)';

		if ( Avada()->settings->get( 'footerw_bg_image' ) && ( in_array( Avada()->settings->get( 'footer_special_effects' ), array( 'footer_parallax_effect', 'footer_area_bg_parallax', 'footer_sticky_with_parallax_bg_image' ) ) ) ) {
			$css[ $ipad_portrait_media_query ]['.fusion-body #wrapper']['background-color'] = 'transparent';
		}

		if ( 'background' !== Avada()->settings->get( 'menu_highlight_style' ) ) {
			if ( is_rtl() ) {
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $menu_elements, '.rtl' ) ]['padding-left'] = intval( Avada()->settings->get( 'mobile_nav_padding' ) ) . 'px';
			} else {
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $menu_elements ) ]['padding-right'] = intval( Avada()->settings->get( 'mobile_nav_padding' ) ) . 'px';
			}
		}

		if ( ! Avada()->settings->get( 'breadcrumb_mobile' ) ) {
			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar .fusion-breadcrumbs']['display'] = 'none';
		}

		// Page Title Bar.
		if ( 'auto' !== Avada()->settings->get( 'page_title_mobile_height' ) ) {
			$page_title_mobile_height = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_mobile_height' ) );
			$page_title_bar_height    = ( strpos( $page_title_mobile_height, 'em' ) ) ? 'calc(' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_mobile_height ) . ')' : $page_title_mobile_height;

			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['padding-top']    = '5px';
			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['padding-bottom'] = '5px';
			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['min-height']     = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );
			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['height']         = 'auto';

			$css[ $ipad_portrait_media_query ]['.fusion-page-title-row']['display']     = 'flex';
			$css[ $ipad_portrait_media_query ]['.fusion-page-title-row']['align-items'] = 'center';
			$css[ $ipad_portrait_media_query ]['.fusion-page-title-row']['width']       = '100%';
			$css[ $ipad_portrait_media_query ]['.fusion-page-title-row']['min-height']  = Fusion_Sanitize::add_css_values( array( $page_title_bar_height, '-10px' ) );

			$css[ $ipad_portrait_media_query ]['.fusion-page-title-bar-center .fusion-page-title-row']['width'] = 'auto';

			$css[ $ipad_portrait_media_query ]['.fusion-page-title-captions']['width'] = '100%';

		} else {

			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['padding-top']    = '10px';
			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['padding-bottom'] = '10px';
			$css[ $ipad_portrait_media_query ]['.fusion-body .fusion-page-title-bar']['height']         = 'auto';

		}

		if ( class_exists( 'WooCommerce' ) ) {

			if ( Avada()->settings->get( 'disable_woo_gallery' ) ) {
				$css[ $ipad_portrait_media_query ]['.product .entry-summary .summary-container']['margin-top'] = '20px';
			}
		}

		if ( get_post_meta( $c_page_id, 'pyre_fallback', true ) ) {
			$css[ $ipad_portrait_media_query ]['#sliders-container']['display'] = 'none';
			$css[ $ipad_portrait_media_query ]['#fallback-slide']['display']    = 'block';
		}
	}

	$css['global']['.ua-mobile .avada-not-responsive #slidingbar-area.fusion-sliding-bar-position-top']['width']    = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
	$css['global']['.ua-mobile .avada-not-responsive #slidingbar-area.fusion-sliding-bar-position-bottom']['width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );

	// WPML Flag positioning on the main menu when header is on the Left/Right.
	if ( class_exists( 'SitePress' ) && 'Top' !== Avada()->settings->get( 'header_position' ) ) {
		$css['global']['.fusion-main-menu > ul > li > a .iclflag']['margin-top'] = '14px !important';
	}

	if ( $site_width_percent ) {

		$elements = array(
			'.fusion-secondary-header',
			'.header-v4 #small-nav',
			'.header-v5 #small-nav',
			'#main',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left']  = '0px';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = '0px';

		if ( '100%' === Avada()->settings->get( 'site_width' ) ) {
			$elements = array(
				'#slidingbar .fusion-row',
				'#sliders-container .tfs-slider .slide-content-container',
				'#main .fusion-row',
				'.fusion-page-title-bar',
				'.fusion-header',
				'.fusion-footer-widget-area',
				'.fusion-footer-copyright-area',
				'.fusion-secondary-header',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left']  = $hundredplr_padding;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = $hundredplr_padding;
		}

		$elements = array(
			'.width-100 .fullwidth-box',
			'.width-100 .fullwidth-box .fusion-row .fusion-full-width-sep',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left']  = $hundredplr_padding_negative_margin;
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right'] = $hundredplr_padding_negative_margin;

		$css['global']['#main.width-100 > .fusion-row']['padding-left']  = '0';
		$css['global']['#main.width-100 > .fusion-row']['padding-right'] = '0';

	}

	if ( 'Boxed' === Avada()->settings->get( 'layout' ) || 'boxed' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) {

		$elements = array(
			'#wrapper',
			'.fusion-footer-parallax',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin'] = '0 auto';
		if ( 'Top' !== Avada()->settings->get( 'header_position' ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = Fusion_Sanitize::add_css_values( array( Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) ), '-' . intval( Avada()->settings->get( 'side_header_width' ) ) . 'px' ) );
		} else {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
		}

		$css['global']['.fusion-body #wrapper.wrapper_blank']['display'] = 'block';

		if ( Avada()->settings->get( 'responsive' ) && $site_width_percent ) {

			$elements = array(
				'#main .fusion-row',
				'.fusion-footer-widget-area .fusion-row',
				'#slidingbar-area .fusion-row',
				'.fusion-footer-copyright-area .fusion-row',
				'.fusion-page-title-row',
				'.fusion-secondary-header .fusion-row',
				'#small-nav .fusion-row',
				'.fusion-header .fusion-row',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width']     = 'none';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-left']  = '10px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding-right'] = '10px';

		}
	}

	// For page BG #6326.
	$elements_with_html = array(
		'html',
		'body',
	);

	$elements_without_html = array(
		'body',
	);

	if ( 'framed' === Avada()->settings->get( 'scroll_offset' ) && ( 'Boxed' === Avada()->settings->get( 'layout' ) || 'boxed' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) ) {
		$elements_with_html    = array(
			'.fusion-top-frame',
			'.fusion-sides-frame',
			'.fusion-bottom-frame',
		);
		$elements_without_html = array(
			'.fusion-top-frame',
			'.fusion-sides-frame',
			'.fusion-bottom-frame',
		);
	}

	$elements         = $elements_with_html;
	$background_color = ( get_post_meta( $c_page_id, 'pyre_page_bg_color', true ) ) ? get_post_meta( $c_page_id, 'pyre_page_bg_color', true ) : Avada()->settings->get( 'bg_color' );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( $background_color );
	$alpha = 1;
	if ( class_exists( 'Fusion_Color' ) ) {
		$alpha = Fusion_Color::new_color( $background_color )->alpha;
	}
	if ( 1 > $alpha ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-blend-mode'] = 'overlay';
	}

	$elements = $elements_without_html;
	if ( get_post_meta( $c_page_id, 'pyre_page_bg', true ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( get_post_meta( $c_page_id, 'pyre_page_bg', true ) ) . '")';
		if ( 'default' !== get_post_meta( $c_page_id, 'pyre_page_bg_repeat', true ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-repeat'] = get_post_meta( $c_page_id, 'pyre_page_bg_repeat', true );
		} else {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-repeat'] = esc_attr( Avada()->settings->get( 'bg_repeat' ) );
		}

		if ( 'yes' === get_post_meta( $c_page_id, 'pyre_page_bg_full', true ) || ( Avada()->settings->get( 'bg_full' ) && 'default' === get_post_meta( $c_page_id, 'pyre_page_bg_full', true ) ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-attachment'] = 'fixed';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-position']   = 'center center';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-size']       = 'cover';
		}
	} elseif ( '' !== Avada()->settings->get( 'bg_image', 'url' ) ) {
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-image']  = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'bg_image', 'url' ) ) . '")';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-repeat'] = esc_attr( Avada()->settings->get( 'bg_repeat' ) );

		if ( Avada()->settings->get( 'bg_full' ) ) {

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-attachment'] = 'fixed';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-position']   = 'center center';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-size']       = 'cover';
		}
	}

	if ( Avada()->settings->get( 'bg_pattern_option' ) && Avada()->settings->get( 'bg_pattern' ) && ! ( get_post_meta( $c_page_id, 'pyre_page_bg_color', true ) || get_post_meta( $c_page_id, 'pyre_page_bg', true ) ) ) {

		$elements = $elements_with_html;
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-image']  = 'url("' . Fusion_Sanitize::css_asset_url( Avada::$template_dir_url . '/assets/images/patterns/' . esc_attr( Avada()->settings->get( 'bg_pattern' ) ) . '.png' ) . '")';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-repeat'] = 'repeat';
	} // Ends page BG #6326.

	if ( 'Wide' !== Avada()->settings->get( 'layout' ) || 'boxed' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) {

		$css['global']['body']['margin-top']    = Fusion_Sanitize::size( Avada()->settings->get( 'margin_offset', 'top' ) ) . ' !important';
		$css['global']['body']['margin-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'margin_offset', 'bottom' ) ) . ' !important';

		// Boxed framed scrolling.
		if ( 'framed' === Avada()->settings->get( 'scroll_offset' ) ) {
			$elements = array(
				'.fusion-top-frame',
				'.fusion-sides-frame',
				'.fusion-bottom-frame',
				'.fusion-boxed-shadow',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['position'] = 'fixed';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['z-index']  = '99996';

			$site_width    = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
			$margin_top    = '%' === Fusion_Sanitize::get_unit( Fusion_Sanitize::size( Avada()->settings->get( 'margin_offset', 'top' ) ) ) ? intval( Avada()->settings->get( 'margin_offset', 'top' ) ) . 'vh' : Fusion_Sanitize::size( Avada()->settings->get( 'margin_offset', 'top' ) );
			$margin_bottom = '%' === Fusion_Sanitize::get_unit( Fusion_Sanitize::size( Avada()->settings->get( 'margin_offset', 'bottom' ) ) ) ? intval( Avada()->settings->get( 'margin_offset', 'bottom' ) ) . 'vh' : Fusion_Sanitize::size( Avada()->settings->get( 'margin_offset', 'bottom' ) );

			$css['global']['body']['margin-top']    = $margin_top . ' !important';
			$css['global']['body']['margin-bottom'] = $margin_bottom . ' !important';

			$elements = array(
				'.fusion-top-frame',
				'.fusion-bottom-frame',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '100%';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['left']  = '0';
			$css['global']['.fusion-top-frame']['top']                            = '0';
			$css['global']['.fusion-top-frame']['height']                         = $margin_top;
			$css['global']['.fusion-bottom-frame']['height']                      = $margin_bottom;
			$css['global']['.fusion-bottom-frame']['bottom']                      = '0';

			$css['global']['.fusion-sides-frame']['z-index']        = '0';
			$css['global']['.fusion-sides-frame']['height']         = '100%';
			$css['global']['.fusion-sides-frame']['width']          = '100%';
			$css['global']['.fusion-sides-frame']['left']           = '0';
			$css['global']['.fusion-sides-frame']['top']            = '0';
			$css['global']['.fusion-sides-frame']['pointer-events'] = 'none';

			$css['global']['.fusion-boxed-shadow']['width']                     = $site_width;
			$css['global']['.fusion-boxed-shadow, body #side-header']['height'] = 'calc( 100vh - ' . $margin_top . ' - ' . $margin_bottom . ' )';
			$css['global']['.fusion-boxed-shadow']['top']                       = $margin_top;
			$css['global']['.fusion-boxed-shadow']['left']                      = 'calc( ( 100% - ' . $site_width . ' ) / 2 )';
			$css['global']['.fusion-boxed-shadow']['pointer-events']            = 'none';

			$mobile_wordpress = '@media screen and (max-width: 782px)';
			$elements         = array(
				'.admin-bar .fusion-top-frame',
				'.admin-bar .fusion-sides-frame',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['top']                             = '32px';
			$css['global']['.admin-bar .fusion-boxed-shadow, .admin-bar #side-header']['height']            = 'calc( 100vh - ' . $margin_top . ' - ' . $margin_bottom . ' - 32px )';
			$css[ $mobile_wordpress ]['.admin-bar .fusion-boxed-shadow, .admin-bar #side-header']['height'] = 'calc( 100vh - ' . $margin_top . ' - ' . $margin_bottom . ' - 46px )';
			$css['global']['.admin-bar .fusion-boxed-shadow']['top']                                        = 'calc( ' . $margin_top . ' + 32px )';
			$css[ $mobile_wordpress ]['.admin-bar .fusion-boxed-shadow']['top']                             = 'calc( ' . $margin_top . ' + 46px )';

			$css['global']['.fusion-side-header-stuck']['position'] = 'fixed !important';

			$css['global']['.fusion-page-title-bar, #main']['position'] = 'relative';

			$side_header_media_query = '@media screen and (max-width: ' . intval( Avada()->settings->get( 'side_header_break_point' ) ) . 'px)';
			$css[ $side_header_media_query ]['body #wrapper #side-header']['height']   = 'auto';
			$css[ $side_header_media_query ]['body #wrapper #side-header']['position'] = 'relative';
			if ( ( get_post_meta( $c_page_id, 'pyre_page_bg', true ) && 'no' === fusion_get_page_option( 'pyre_page_bg_full', $c_page_id ) ) ||
			( ! get_post_meta( $c_page_id, 'pyre_page_bg', true ) && ! Avada()->settings->get( 'bg_full' ) ) ) {
				$css['global']['.fusion-bottom-frame']['background-position']            = '0 calc( ( 100vh - ' . $margin_bottom . ' ) * -1 )';
				$css['global']['.admin-bar .fusion-bottom-frame']['background-position'] = '0 calc( ( 100vh - ' . $margin_bottom . ' - 32px ) * -1 )';
				$css['global'][ $mobile_wordpress ]['background-position']               = '0 calc( ( 100vh - ' . $margin_bottom . ' - 46px ) * -1 )';
			}
		}

		if ( 'Top' === Avada()->settings->get( 'header_position' ) ) {
			$boxed_shadow_selectors = '#wrapper, .fusion-boxed-shadow';
			if ( 'footer_sticky' === Avada()->settings->get( 'footer_special_effects' ) ) {
				$boxed_shadow_selectors = '.fusion-boxed-shadow';
			}

			if ( 'Light' === Avada()->settings->get( 'boxed_modal_shadow' ) ) {
				$css['global'][ $boxed_shadow_selectors ]['box-shadow'] = '0px 0px 50px 1px rgba(0,0,0,0.22)';
			} elseif ( 'Medium' === Avada()->settings->get( 'boxed_modal_shadow' ) ) {
				$css['global'][ $boxed_shadow_selectors ]['box-shadow'] = '0px 0px 50px 5px rgba(0,0,0,0.35)';
			} elseif ( 'Hard' === Avada()->settings->get( 'boxed_modal_shadow' ) ) {
				$css['global'][ $boxed_shadow_selectors ]['box-shadow'] = '0px 0px 50px 10px rgba(0,0,0,0.55)';
			}
			if ( 'full' === Avada()->settings->get( 'scroll_offset' ) ) {
				$css['global']['#sliders-container .tfs-slider']['position']                     = 'absolute !important';
				$css['global']['#sliders-container .tfs-slider.fusion-fixed-slider']['position'] = 'fixed !important';
				$css['global']['#sliders-container .tfs-slider']['top']                          = '0';
			}
		} else {
			if ( 'full' === Avada()->settings->get( 'scroll_offset' ) ) {
				$css['global']['#sliders-container .tfs-slider']['position']                     = 'relative !important';
				$css['global']['#sliders-container .tfs-slider.fusion-fixed-slider']['position'] = 'fixed !important';
				if ( 'Left' === Avada()->settings->get( 'header_position' ) ) {
					$css['global']['#sliders-container .tfs-slider']['margin-left'] = 'auto !important';
					$css['global']['#sliders-container .tfs-slider']['left']        = 'auto !important';
				} else {
					$css['global']['#sliders-container .tfs-slider']['margin-left'] = 'auto !important';
					$css['global']['#sliders-container .tfs-slider']['right']       = 'auto !important';
					$css['global']['#sliders-container .tfs-slider']['left']        = 'auto !important';
				}
			}
			if ( 'Light' === Avada()->settings->get( 'boxed_modal_shadow' ) ) {
				$css['global']['#boxed-wrapper, .fusion-boxed-shadow']['box-shadow'] = '0px 0px 50px 1px rgba(0,0,0,0.22)';
			} elseif ( 'Medium' === Avada()->settings->get( 'boxed_modal_shadow' ) ) {
				$css['global']['#boxed-wrapper, .fusion-boxed-shadow']['box-shadow'] = '0px 0px 50px 5px rgba(0,0,0,0.35)';
			} elseif ( 'Hard' === Avada()->settings->get( 'boxed_modal_shadow' ) ) {
				$css['global']['#boxed-wrapper, .fusion-boxed-shadow']['box-shadow'] = '0px 0px 50px 10px rgba(0,0,0,0.55)';
			}
		}
	}

	if ( 'boxed' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) {

		$elements = array( 'html', 'body' );

		$background_color = ( get_post_meta( $c_page_id, 'pyre_page_bg_color', true ) ) ? get_post_meta( $c_page_id, 'pyre_page_bg_color', true ) : Fusion_Sanitize::color( Avada()->settings->get( 'bg_color' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = Fusion_Sanitize::color( $background_color );

		if ( get_post_meta( $c_page_id, 'pyre_page_bg', true ) ) {

			$css['global']['body']['background-image']  = 'url("' . Fusion_Sanitize::css_asset_url( get_post_meta( $c_page_id, 'pyre_page_bg', true ) ) . '")';
			$css['global']['body']['background-repeat'] = get_post_meta( $c_page_id, 'pyre_page_bg_repeat', true );

			if ( 'yes' === get_post_meta( $c_page_id, 'pyre_page_bg_full', true ) ) {

				$css['global']['body']['background-attachment'] = 'fixed';
				$css['global']['body']['background-position']   = 'center center';
				$css['global']['body']['background-size']       = 'cover';

			}
		} elseif ( '' !== Avada()->settings->get( 'bg_image', 'url' ) ) {

			$css['global']['body']['background-image']  = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'bg_image', 'url' ) ) . '")';
			$css['global']['body']['background-repeat'] = esc_attr( Avada()->settings->get( 'bg_repeat' ) );

			if ( Avada()->settings->get( 'bg_full' ) ) {

				$css['global']['body']['background-attachment'] = 'fixed';
				$css['global']['body']['background-position']   = 'center center';
				$css['global']['body']['background-size']       = 'cover';

			}
		}

		if ( Avada()->settings->get( 'bg_pattern_option' ) && Avada()->settings->get( 'bg_pattern' ) && ! ( get_post_meta( $c_page_id, 'pyre_page_bg_color', true ) || get_post_meta( $c_page_id, 'pyre_page_bg', true ) ) ) {

			$css['global']['body']['background-image']  = 'url("' . Fusion_Sanitize::css_asset_url( Avada::$template_dir_url . '/assets/images/patterns/' . esc_attr( Avada()->settings->get( 'bg_pattern' ) ) . '.png' ) . '")';
			$css['global']['body']['background-repeat'] = 'repeat';

		}

		$elements = array( '#wrapper', '.fusion-footer-parallax' );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']     = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin']    = '0 auto';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = '100%';

		$css['global']['.fusion-body #wrapper.wrapper_blank']['display'] = 'block';

	}

	if ( 'wide' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) {

		$css['global']['#wrapper']['width']     = '100%';
		$css['global']['#wrapper']['max-width'] = 'none';

	}

	if ( get_post_meta( $c_page_id, 'pyre_page_bg', true ) || '' !== Avada()->settings->get( 'bg_image', 'url' ) ) {
		$css['global']['html']['background'] = 'none';
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_bar_bg', true ) ) {
		$css['global']['.fusion-page-title-bar']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( get_post_meta( $c_page_id, 'pyre_page_title_bar_bg', true ) ) . '")';
	} elseif ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_bg' ) ) {
		$css['global']['.fusion-page-title-bar']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_bg' ) ) . '")';
	} elseif ( '' !== Avada()->settings->get( 'page_title_bg', 'url' ) ) {
		$css['global']['.fusion-page-title-bar']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'page_title_bg', 'url' ) ) . '")';
	}

	$css['global']['.fusion-page-title-bar']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'page_title_bg_color' ) );
	if ( get_post_meta( $c_page_id, 'pyre_page_title_bar_bg_color', true ) ) {
		$css['global']['.fusion-page-title-bar']['background-color'] = get_post_meta( $c_page_id, 'pyre_page_title_bar_bg_color', true );
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_bar_borders_color', true ) ) {
		$css['global']['.fusion-page-title-bar']['border-color'] = get_post_meta( $c_page_id, 'pyre_page_title_bar_borders_color', true );
	}

	if ( '' !== Avada()->settings->get( 'header_bg_image', 'url' ) ) {
		// Top bar semi transparent for header 3, move header background to wrapper.
		if ( in_array( Avada()->settings->get( 'header_layout' ), array( 'v2', 'v3' ) ) && 'Top' === Avada()->settings->get( 'header_position' ) && Fusion_Color::new_color( Avada()->settings->get( 'header_top_bg_color' ) )->alpha < 1 ) {

			if ( intval( Avada()->settings->get( 'sec_menu_lh' ) ) > 43 ) {
				$top_bar_height = ( intval( Avada()->settings->get( 'sec_menu_lh' ) ) / 2 ) . 'px';
			} else {
				$top_bar_height = '21.5px';
			}

			$css['global']['body .fusion-header-wrapper .fusion-header']['background-color']               = 'transparent';
			$css['global']['.fusion-header-wrapper, .fusion-is-sticky .fusion-header']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'header_bg_image', 'url' ) ) . '")';

			if ( in_array( Avada()->settings->get( 'header_bg_repeat' ), array( 'repeat-y', 'no-repeat' ) ) ) {
				$css['global']['.fusion-header-wrapper']['background-position']           = 'center center';
				$css['global']['.fusion-is-sticky .fusion-header']['background-position'] = '50% ' . Fusion_Sanitize::add_css_values( array( '50%', '-' . $top_bar_height ) );
			}

			$css['global']['.fusion-header-wrapper']['background-repeat'] = esc_attr( Avada()->settings->get( 'header_bg_repeat' ) );

			if ( Avada()->settings->get( 'header_bg_full' ) ) {
				$css['global']['.fusion-header-wrapper, .fusion-is-sticky .fusion-header']['background-attachment'] = 'scroll';
				$css['global']['.fusion-header-wrapper']['background-position']                                     = 'center center';
				$css['global']['.fusion-is-sticky .fusion-header']['background-position']                           = '50% ' . Fusion_Sanitize::add_css_values( array( '50%', '-' . $top_bar_height ) );
				$css['global']['.fusion-header-wrapper, .fusion-is-sticky .fusion-header']['background-size']       = 'cover';
			}

			if ( Avada()->settings->get( 'header_bg_parallax' ) ) {
				$css['global']['.fusion-header-wrapper, .fusion-is-sticky .fusion-header']['background-attachment'] = 'fixed';
				$css['global']['.fusion-header-wrapper, .fusion-is-sticky .fusion-header']['background-position']   = 'top center';
			}
		} else {

			$css['global']['.side-header-background-image']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'header_bg_image', 'url' ) ) . '")';
			$css['global']['.fusion-header']['background-image']                = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'header_bg_image', 'url' ) ) . '")';

			if ( in_array( Avada()->settings->get( 'header_bg_repeat' ), array( 'repeat-y', 'no-repeat' ) ) ) {
				$css['global']['.side-header-background-image']['background-position'] = 'center center';
				$css['global']['.fusion-header']['background-position']                = 'center center';
			}

			$css['global']['.side-header-background-image']['background-repeat'] = esc_attr( Avada()->settings->get( 'header_bg_repeat' ) );
			$css['global']['.fusion-header']['background-repeat']                = esc_attr( Avada()->settings->get( 'header_bg_repeat' ) );

			if ( Avada()->settings->get( 'header_bg_full' ) ) {
				if ( 'Top' === Avada()->settings->get( 'header_position' ) ) {
					$css['global']['.side-header-background-image']['background-attachment'] = 'scroll';
					$css['global']['.fusion-header']['background-attachment']                = 'scroll';
				}
				$css['global']['.side-header-background-image']['background-position'] = 'center center';
				$css['global']['.fusion-header']['background-position']                = 'center center';
				$css['global']['.side-header-background-image']['background-size']     = 'cover';
				$css['global']['.fusion-header']['background-size']                    = 'cover';
			}
			if ( Avada()->settings->get( 'header_bg_parallax' ) && 'Top' === Avada()->settings->get( 'header_position' ) ) {
				$css['global']['.side-header-background-image']['background-attachment'] = 'fixed';
				$css['global']['.fusion-header']['background-attachment']                = 'fixed';
				$css['global']['.side-header-background-image']['background-position']   = 'top center';
				$css['global']['.fusion-header']['background-position']                  = 'top center';
			}
		}
	}

	if ( 'Top' !== Avada()->settings->get( 'header_position' ) ) {
		$elements = array(
			'.side-header-background-color',
			'.side-header .fusion-footer-parallax',
		);

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $header_bg;
	} else {
		$css['global']['.fusion-header']['background-color'] = $header_bg;
	}

	$css['global']['.fusion-arrow-svg path']['fill'] = $header_bg;

	$header_bg_color_rgb = Fusion_Sanitize::color( Avada()->settings->get( 'menu_h45_bg_color' ) );

	// If v4 or v5 the arrow svg must come from separate option.
	if ( ( 'v4' === Avada()->settings->get( 'header_layout' ) || 'v5' === Avada()->settings->get( 'header_layout' ) ) &&
		'Top' === Avada()->settings->get( 'header_position' ) &&
		'arrow' === Avada()->settings->get( 'menu_highlight_style' ) ) {
		$css['global']['.fusion-arrow-svg path']['fill'] = $header_bg_color_rgb;
	}

	$css['global']['.fusion-secondary-main-menu']['background-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'menu_h45_bg_color' ) );

	$elements = array( '.fusion-header', '.side-header-background-image' );

	if ( get_post_meta( $c_page_id, 'pyre_header_bg', true ) ) {

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( get_post_meta( $c_page_id, 'pyre_header_bg', true ) ) . '")';

		if ( in_array( get_post_meta( $c_page_id, 'pyre_header_bg_repeat', true ), array( 'repeat-y', 'no-repeat' ) ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-position'] = 'center center';
		}

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-repeat'] = get_post_meta( $c_page_id, 'pyre_header_bg_repeat', true );

		if ( 'yes' === get_post_meta( $c_page_id, 'pyre_header_bg_full', true ) ) {

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-position'] = 'center center';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-size']     = 'cover';

		}

		if ( Avada()->settings->get( 'header_bg_parallax' ) && 'Top' === Avada()->settings->get( 'header_position' ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-attachment'] = 'fixed';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-position']   = 'top center';
		}
	}

	// Adjust header padding if opacity is not 1 for smooth scroll.
	if ( 1 > $header_bg_opacity ) {
		$css['global']['.no-overflow-y .fusion-header-v1 .fusion-header']['padding-right']                     = '39px';
		$css['global']['.no-overflow-y .fusion-header-v2 .fusion-header']['padding-right']                     = '39px';
		$css['global']['.no-overflow-y .fusion-header-v3 .fusion-header']['padding-right']                     = '39px';
		$css['global']['.no-overflow-y .fusion-sticky-menu-only .fusion-secondary-main-menu']['padding-right'] = '39px';
	}

	if ( 'no' === get_post_meta( $c_page_id, 'pyre_avada_rev_styles', true ) || ( Avada()->settings->get( 'avada_rev_styles' ) && 'yes' !== get_post_meta( $c_page_id, 'pyre_avada_rev_styles', true ) ) ) {

		$css['global']['.rev_slider_wrapper']['position'] = 'relative';

		if ( class_exists( 'RevSliderFront' ) ) {
			if ( 1 === $header_bg_opacity ) {

				$css['global']['.rev_slider_wrapper .shadow-left']['position']            = 'absolute';
				$css['global']['.rev_slider_wrapper .shadow-left']['pointer-events']      = 'none';
				$css['global']['.rev_slider_wrapper .shadow-left']['background-image']    = 'url("' . Fusion_Sanitize::css_asset_url( Avada::$template_dir_url . '/assets/images/shadow-top.png' ) . '")';
				$css['global']['.rev_slider_wrapper .shadow-left']['background-repeat']   = 'no-repeat';
				$css['global']['.rev_slider_wrapper .shadow-left']['background-position'] = 'top center';
				$css['global']['.rev_slider_wrapper .shadow-left']['height']              = '42px';
				$css['global']['.rev_slider_wrapper .shadow-left']['width']               = '100%';
				$css['global']['.rev_slider_wrapper .shadow-left']['top']                 = '0';
				$css['global']['.rev_slider_wrapper .shadow-left']['z-index']             = '99';

				$css['global']['.rev_slider_wrapper .shadow-left']['top'] = '-1px';

			}

			$css['global']['.rev_slider_wrapper .shadow-right']['position']            = 'absolute';
			$css['global']['.rev_slider_wrapper .shadow-right']['pointer-events']      = 'none';
			$css['global']['.rev_slider_wrapper .shadow-right']['background-image']    = 'url("' . Fusion_Sanitize::css_asset_url( Avada::$template_dir_url . '/assets/images/shadow-bottom.png' ) . '")';
			$css['global']['.rev_slider_wrapper .shadow-right']['background-repeat']   = 'no-repeat';
			$css['global']['.rev_slider_wrapper .shadow-right']['background-position'] = 'bottom center';
			$css['global']['.rev_slider_wrapper .shadow-right']['height']              = '32px';
			$css['global']['.rev_slider_wrapper .shadow-right']['width']               = '100%';
			$css['global']['.rev_slider_wrapper .shadow-right']['bottom']              = '0';
			$css['global']['.rev_slider_wrapper .shadow-right']['z-index']             = '99';

		}

		$css['global']['.avada-skin-rev']['border-top']    = '1px solid #d2d3d4';
		$css['global']['.avada-skin-rev']['border-bottom'] = '1px solid #d2d3d4';
		$css['global']['.avada-skin-rev']['box-sizing']    = 'content-box';

		$css['global']['.tparrows']['border-radius'] = '0';

		if ( class_exists( 'RevSliderFront' ) ) {

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['opacity']          = '0.8 !important';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['position']         = 'absolute';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['top']              = '50% !important';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-top']       = '-31px !important';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']            = '63px !important';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']           = '63px !important';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background']       = 'none';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'rgba(0, 0, 0, 0.5)';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']            = '#fff';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-radius']    = '0';

			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows:before']['content']                = '"\e61e"';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows:before']['-webkit-font-smoothing'] = 'antialiased';

			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows:before']['content']                = '"\e620"';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows:before']['-webkit-font-smoothing'] = 'antialiased';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows:before',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows:before',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['position']    = 'absolute';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding']     = '0';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']       = '100%';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = '63px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['text-align']  = 'center';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size']   = '25px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-family'] = "'icomoon'";

			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows:before']['margin-left'] = '-2px';

			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows:before']['margin-left'] = '-1px';

			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows']['left']  = 'auto';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows']['right'] = '0';

			$elements = array(
				'.rev_slider_wrapper:hover .rev_slider .tp-leftarrow.tparrows',
				'.rev_slider_wrapper:hover .rev_slider .tp-rightarrow.tparrows',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['opacity'] = '0.8 !important';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows:hover',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows:hover',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['opacity'] = '1 !important';

			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows']['background-position'] = '19px 19px';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows']['left']                = '0';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows']['margin-left']         = '0';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows']['z-index']             = '100';

			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows']['background-position'] = '29px 19px';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows']['right']               = '0';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows']['margin-left']         = '0';
			$css['global']['.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows']['z-index']             = '100';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows.hidearrows',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows.hidearrows',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['opacity'] = '0';

			// Additional arrow styles.
			$css['global']['.rev_slider_wrapper .rev_slider .tparrows.hades .tp-arr-allwrapper']['width']  = '63px';
			$css['global']['.rev_slider_wrapper .rev_slider .tparrows.hades .tp-arr-allwrapper']['height'] = '63px';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows.hebe:before',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows.hebe:before',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['position'] = 'relative';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']    = 'auto';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows.zeus',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows.zeus',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['min-width']  = '63px';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['min-height'] = '63px';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows.zeus .tp-title-wrap',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows.zeus .tp-title-wrap',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-radius'] = '0';

			$elements = array(
				'.rev_slider_wrapper .rev_slider .tp-leftarrow.tparrows.metis',
				'.rev_slider_wrapper .rev_slider .tp-rightarrow.tparrows.metis',
			);
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = '0';
		}

		$css['global']['.tp-bullets .bullet.last']['clear'] = 'none';

	}

	if ( '' !== Avada()->settings->get( 'content_bg_image', 'url' ) && ! get_post_meta( $c_page_id, 'pyre_wide_page_bg_color', true ) ) {

		$css['global']['#main']['background-image']  = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'content_bg_image', 'url' ) ) . '")';
		$css['global']['#main']['background-repeat'] = esc_attr( Avada()->settings->get( 'content_bg_repeat' ) );

		if ( Avada()->settings->get( 'content_bg_full' ) ) {

			$css['global']['#main']['background-attachment'] = 'fixed';
			$css['global']['#main']['background-position']   = 'center center';
			$css['global']['#main']['background-size']       = 'cover';

		}
	}

	if ( ( Avada()->settings->get( 'main_padding', 'top' ) || '0' == Avada()->settings->get( 'main_padding', 'top' ) ) && ( ( ! get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) && get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) !== '0' ) || ! $c_page_id ) ) {
		$css['global']['#main']['padding-top'] = Fusion_Sanitize::size( Avada()->settings->get( 'main_padding', 'top' ) );
	}

	if ( ( Avada()->settings->get( 'main_padding', 'bottom' ) || '0' == Avada()->settings->get( 'main_padding', 'bottom' ) ) && ( ( ! get_post_meta( $c_page_id, 'pyre_main_bottom_padding', true ) && get_post_meta( $c_page_id, 'pyre_main_bottom_padding', true ) !== '0' ) || ! $c_page_id ) ) {
		$css['global']['#main']['padding-bottom'] = Fusion_Sanitize::size( Avada()->settings->get( 'main_padding', 'bottom' ) );
	}

	if ( 'wide' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) && get_post_meta( $c_page_id, 'pyre_wide_page_bg_color', true ) ) {
		$elements = array( '#wrapper' );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = get_post_meta( $c_page_id, 'pyre_wide_page_bg_color', true );
	}

	if ( get_post_meta( $c_page_id, 'pyre_wide_page_bg_color', true ) ) {
		$elements = array(
			'#main',
			'#wrapper',
			'.fusion-separator .icon-wrapper',
		);
		if ( class_exists( 'bbPress' ) ) {
			$elements[] = '.bbp-arrow';
		}
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = get_post_meta( $c_page_id, 'pyre_wide_page_bg_color', true );

		$alpha = 1;
		if ( class_exists( 'Fusion_Color' ) ) {
			$alpha = Fusion_Color::new_color( get_post_meta( $c_page_id, 'pyre_wide_page_bg_color', true ) )->alpha;
		}

		if ( 1 > $alpha ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-blend-mode'] = 'overlay';
		}
	}

	if ( get_post_meta( $c_page_id, 'pyre_wide_page_bg', true ) ) {
		$elements = array(
			'.wrapper_blank #main',
			'#main',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( get_post_meta( $c_page_id, 'pyre_wide_page_bg', true ) ) . '")';
		if ( 'default' !== get_post_meta( $c_page_id, 'pyre_wide_page_bg_repeat', true ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-repeat'] = get_post_meta( $c_page_id, 'pyre_wide_page_bg_repeat', true );
		} else {
			$css['global']['#main']['background-repeat'] = esc_attr( Avada()->settings->get( 'content_bg_repeat' ) );
		}

		if ( 'yes' === get_post_meta( $c_page_id, 'pyre_wide_page_bg_full', true ) || ( Avada()->settings->get( 'content_bg_full' ) && 'default' === get_post_meta( $c_page_id, 'pyre_wide_page_bg_full', true ) ) ) {

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-attachment'] = 'fixed';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-position']   = 'center center';
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-size']       = 'cover';

		}
	}

	if ( get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) || get_post_meta( $c_page_id, 'pyre_main_top_padding', true ) === '0' ) {
		$css['global']['#main']['padding-top'] = get_post_meta( $c_page_id, 'pyre_main_top_padding', true );
	}

	if ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'main_padding_top' ) ) {
		$css['global']['#main']['padding-top'] = Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'main_padding_top' );
	}

	if ( get_post_meta( $c_page_id, 'pyre_main_bottom_padding', true ) || get_post_meta( $c_page_id, 'pyre_main_bottom_padding', true ) === '0' ) {
		$css['global']['#main']['padding-bottom'] = get_post_meta( $c_page_id, 'pyre_main_bottom_padding', true );
	}

	if ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'main_padding_bottom' ) ) {
		$css['global']['#main']['padding-bottom'] = Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'main_padding_bottom' );
	}

	if ( get_post_meta( $c_page_id, 'pyre_sidebar_bg_color', true ) ) {
		$elements = array(
			'#main .sidebar:not( .fusion-sticky-sidebar )',
			'#main .fusion-sticky-sidebar .fusion-sidebar-inner-content',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = get_post_meta( $c_page_id, 'pyre_sidebar_bg_color', true );
	}

	if ( Avada()->settings->get( 'page_title_bg_full' ) ) {
		$css['global']['.fusion-page-title-bar']['background-size'] = 'cover';
	}

	if ( 'yes' === get_post_meta( $c_page_id, 'pyre_page_title_bar_bg_full', true ) ) {
		$css['global']['.fusion-page-title-bar']['background-size'] = 'cover';
	} elseif ( 'no' === get_post_meta( $c_page_id, 'pyre_page_title_bar_bg_full', true ) ) {
		$css['global']['.fusion-page-title-bar']['background-size'] = 'auto';
	}

	if ( Avada()->settings->get( 'page_title_bg_parallax' ) ) {
		$css['global']['.fusion-page-title-bar']['background-attachment'] = 'fixed';
		$css['global']['.fusion-page-title-bar']['background-position']   = 'top center';
	}

	if ( 'yes' === get_post_meta( $c_page_id, 'pyre_page_title_bg_parallax', true ) ) {
		$css['global']['.fusion-page-title-bar']['background-attachment'] = 'fixed';
		$css['global']['.fusion-page-title-bar']['background-position']   = 'top center';
	} elseif ( 'no' === get_post_meta( $c_page_id, 'pyre_page_title_bg_parallax', true ) ) {
		$css['global']['.fusion-page-title-bar']['background-attachment'] = 'scroll';
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_height', true ) ) {
		$page_title_bar_height                             = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_page_title_height', true ) );
		$page_title_bar_height                             = ( strpos( $page_title_bar_height, 'em' ) ) ? 'calc( ' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_bar_height ) . ' )' : $page_title_bar_height;
		$css['global']['.fusion-page-title-bar']['height'] = $page_title_bar_height;
	} elseif ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_height' ) ) {
		$page_title_bar_height                             = Fusion_Sanitize::size( Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_height' ) );
		$page_title_bar_height                             = ( strpos( $page_title_bar_height, 'em' ) ) ? 'calc( ' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_bar_height ) . ' )' : $page_title_bar_height;
		$css['global']['.fusion-page-title-bar']['height'] = $page_title_bar_height;
	} else {
		$page_title_bar_height                             = Fusion_Sanitize::size( Avada()->settings->get( 'page_title_height' ) );
		$page_title_bar_height                             = ( strpos( $page_title_bar_height, 'em' ) ) ? 'calc( ' . $page_title_bar_font_size . ' * ' . str_replace( 'em', '', $page_title_bar_height ) . ' )' : $page_title_bar_height;
		$css['global']['.fusion-page-title-bar']['height'] = $page_title_bar_height;
	}

	if ( is_single() && get_post_meta( $c_page_id, 'pyre_fimg_width', true ) ) {

		if ( 'auto' != get_post_meta( $c_page_id, 'pyre_fimg_width', true ) ) {
			$css['global'][ '#post-' . $c_page_id . ' .fusion-post-slideshow' ]['max-width'] = get_post_meta( $c_page_id, 'pyre_fimg_width', true );
		} else {
			$css['global']['.fusion-post-slideshow .flex-control-nav']['position']   = 'relative';
			$css['global']['.fusion-post-slideshow .flex-control-nav']['text-align'] = 'center';
			$css['global']['.fusion-post-slideshow .flex-control-nav']['margin-top'] = '10px';

			$css['global'][ '#post-' . $c_page_id . ' .fusion-post-slideshow img' ]['width'] = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_fimg_width', true ) );
		}

		$css['global'][ '#post-' . $c_page_id . ' .fusion-post-slideshow img' ]['max-width'] = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_fimg_width', true ) );
	}

	if ( is_single() && get_post_meta( $c_page_id, 'pyre_fimg_height', true ) ) {
		$elements = array(
			'#post-' . $c_page_id . ' .fusion-post-slideshow',
			'#post-' . $c_page_id . ' .fusion-post-slideshow img',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-height']                = get_post_meta( $c_page_id, 'pyre_fimg_height', true );
		$css['global'][ '#post-' . $c_page_id . ' .fusion-post-slideshow .slides' ]['max-height'] = '100%';
	}

	// Page Title Bar Retina.
	$media_query = '@media only screen and (-webkit-min-device-pixel-ratio: 1.5), only screen and (min-resolution: 144dpi), only screen and (min-resolution: 1.5dppx)';
	if ( get_post_meta( $c_page_id, 'pyre_page_title_bar_bg_retina', true ) ) {
		$css[ $media_query ]['.fusion-page-title-bar']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( get_post_meta( $c_page_id, 'pyre_page_title_bar_bg_retina', true ) ) . '")';
		$css[ $media_query ]['.fusion-page-title-bar']['background-size']  = 'cover';
	} elseif ( ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_bg_retina' ) ) {
		$css[ $media_query ]['.fusion-page-title-bar']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'page_title_bg_retina' ) ) . '")';
		$css[ $media_query ]['.fusion-page-title-bar']['background-size']  = 'cover';
	} elseif ( '' !== Avada()->settings->get( 'page_title_bg_retina', 'url' ) ) {
		$css[ $media_query ]['.fusion-page-title-bar']['background-image'] = 'url("' . Fusion_Sanitize::css_asset_url( Avada()->settings->get( 'page_title_bg_retina', 'url' ) ) . '")';
		$css[ $media_query ]['.fusion-page-title-bar']['background-size']  = 'cover';
	}

	// Page title bar option to use.
	$page_title_option = Avada()->settings->get( 'page_title_bar' );
	if ( is_home() ) {

		// Designated blog page/latest posts.
		$page_title_option = Avada()->settings->get( 'blog_show_page_title_bar' );
	} elseif ( is_tag() || is_category() || is_author() || is_date() || is_singular( 'post' ) ) {

		// Blog archive or post.
		$page_title_option = Avada()->settings->get( 'blog_page_title_bar' );
	}

	if ( ( 'content_only' === $page_title_option && ( 'default' === get_post_meta( $c_page_id, 'pyre_page_title', true ) || ! get_post_meta( $c_page_id, 'pyre_page_title', true ) ) ) || 'yes_without_bar' === get_post_meta( $c_page_id, 'pyre_page_title', true ) ) {
		$css['global']['.fusion-body .fusion-page-title-bar']['background'] = 'none';
		$css['global']['.fusion-body .fusion-page-title-bar']['border']     = 'none';
	}

	$css['global']['.width-100 .nonhundred-percent-fullwidth']['margin-left']  = $hundredplr_padding_negative_margin;
	$css['global']['.width-100 .nonhundred-percent-fullwidth']['margin-right'] = $hundredplr_padding_negative_margin;

	if ( (float) $wp_version < 3.8 ) {
		$css['global']['#wpadminbar *']['color'] = '#ccc';
		$elements                                = array(
			'#wpadminbar .hover a',
			'#wpadminbar .hover a span',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = '#464646';
	}

	if ( class_exists( 'WooCommerce' ) ) {
		$css['global']['.woocommerce-invalid:after']['content'] = __( 'Please enter correct details for this required field.', 'Avada' );
	}

	if ( 'no' !== get_post_meta( get_queried_object_id(), 'pyre_display_header', true ) ) {

		$elements = array(
			'body.side-header-left #wrapper',
			'.side-header-left .fusion-footer-parallax',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left'] = intval( Avada()->settings->get( 'side_header_width' ) ) . 'px';

		$elements = array(
			'body.side-header-right #wrapper',
			'.side-header-right .fusion-footer-parallax',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right'] = intval( Avada()->settings->get( 'side_header_width' ) ) . 'px';

		$elements = array(
			'body.side-header-left #side-header #nav > ul > li > ul',
			'body.side-header-left #side-header #nav .login-box',
			'body.side-header-left #side-header #nav .main-nav-search-form',
		);
		if ( class_exists( 'WooCommerce' ) ) {
			$elements[] = 'body.side-header-left #side-header #nav .cart-contents';
		}
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['left'] = ( intval( Avada()->settings->get( 'side_header_width' ) ) - 1 ) . 'px';

		if ( is_rtl() ) {
			$css['global']['.rtl.side-header-left #boxed-wrapper']['direction']     = 'ltr';
			$css['global']['.rtl.side-header-left #side-header']['direction']       = 'rtl';
			$css['global']['.rtl.side-header-left #sliders-container']['direction'] = 'rtl';
			$css['global']['.rtl.side-header-left #main']['direction']              = 'rtl';
			$css['global']['.rtl.side-header-left .fusion-footer']['direction']     = 'rtl';
		}
	}

	if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && 'wide' !== get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) || 'boxed' === get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) && 'Top' !== Avada()->settings->get( 'header_position' ) ) {

		$css['global']['#boxed-wrapper']['min-height'] = '100vh';

		$elements = array(
			'#boxed-wrapper',
			'#slidingbar-area .fusion-row',
			'.fusion-footer-parallax',
			'body > #lang_sel_footer',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left']  = 'auto';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right'] = 'auto';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width']    = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );

		if ( $site_width_percent ) {
			$css['global']['#wrapper']['max-width'] = 'none';
		}

		if ( 'Left' === Avada()->settings->get( 'header_position' ) ) {

			$css['global']['body.side-header-left #side-header']['left']        = 'auto';
			$css['global']['body.side-header-left #side-header']['margin-left'] = '-' . intval( Avada()->settings->get( 'side_header_width' ) ) . 'px';

			if ( is_rtl() ) {
				$css['global']['.rtl.avada-footer-fx-sticky.side-header-left #boxed-wrapper #side-header']['margin-left'] = '-' . ( intval( Avada()->settings->get( 'side_header_width' ) ) / 2 ) . 'px';
			}

			$css['global']['.side-header-left .fusion-footer-parallax']['margin']       = '0 auto';
			$css['global']['.side-header-left .fusion-footer-parallax']['padding-left'] = intval( Avada()->settings->get( 'side_header_width' ) ) . 'px';

		} else {

			$css['global']['#boxed-wrapper']['position'] = 'relative';

			$css['global']['.side-header-right .fusion-footer-parallax']['margin']        = '0 auto';
			$css['global']['.side-header-right .fusion-footer-parallax']['padding-right'] = intval( Avada()->settings->get( 'side_header_width' ) ) . 'px';
		}
	}

	if ( is_page_template( 'contact.php' ) && Avada()->settings->get( 'gmap_address' ) && Avada()->settings->get( 'status_gmap' ) ) {

		$css['global']['.avada-google-map']['width']  = Fusion_Sanitize::size( Avada()->settings->get( 'gmap_dimensions', 'width' ) );
		$css['global']['.avada-google-map']['margin'] = '0 auto';

		if ( '100%' !== Avada()->settings->get( 'gmap_dimensions', 'width' ) ) {
			$margin_top                                       = ( Avada()->settings->get( 'gmap_topmargin' ) ) ? Avada()->settings->get( 'gmap_topmargin' ) : '55px';
			$css['global']['.avada-google-map']['margin-top'] = Fusion_Sanitize::size( $margin_top );
		}

		$gmap_height                                  = ( Avada()->settings->get( 'gmap_dimensions', 'height' ) ) ? Avada()->settings->get( 'gmap_dimensions', 'height' ) : '415px';
		$css['global']['.avada-google-map']['height'] = Fusion_Sanitize::size( $gmap_height );

	} elseif ( is_page_template( 'contact-2.php' ) && Avada()->settings->get( 'gmap_address' ) && Avada()->settings->get( 'status_gmap' ) ) {

		$css['global']['.avada-google-map']['margin']     = '0 auto';
		$css['global']['.avada-google-map']['margin-top'] = '55px';
		$css['global']['.avada-google-map']['height']     = '415px !important';
		$css['global']['.avada-google-map']['width']      = '940px !important';

	}

	if ( 'yes' === get_post_meta( $c_page_id, 'pyre_footer_100_width', true ) ) {

		$elements = array(
			'.layout-wide-mode .fusion-footer-widget-area > .fusion-row',
			'.layout-wide-mode .fusion-footer-copyright-area > .fusion-row',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = '100% !important';

	} elseif ( 'no' === get_post_meta( $c_page_id, 'pyre_footer_100_width', true ) ) {

		$elements = array(
			'.layout-wide-mode .fusion-footer-widget-area > .fusion-row',
			'.layout-wide-mode .fusion-footer-copyright-area > .fusion-row',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['max-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) ) . ' !important';

	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_font_color', true ) && '' != get_post_meta( $c_page_id, 'pyre_page_title_font_color', true ) ) {
		$css['global']['.fusion-page-title-bar h1']['color'] = Fusion_Sanitize::color( get_post_meta( $c_page_id, 'pyre_page_title_font_color', true ) );
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_subheader_font_color', true ) && '' != get_post_meta( $c_page_id, 'pyre_page_title_subheader_font_color', true ) ) {
		$css['global']['.fusion-page-title-bar h3']['color'] = Fusion_Sanitize::color( get_post_meta( $c_page_id, 'pyre_page_title_subheader_font_color', true ) );
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_text_size', true ) && '' != get_post_meta( $c_page_id, 'pyre_page_title_text_size', true ) ) {
		$css['global']['.fusion-page-title-bar .fusion-page-title-row h1']['font-size'] = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_page_title_text_size', true ) );
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_line_height', true ) && '' != get_post_meta( $c_page_id, 'pyre_page_title_line_height', true ) ) {
		$css['global']['.fusion-page-title-bar .fusion-page-title-row h1']['line-height'] = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_page_title_line_height', true ) );
	}

	if ( get_post_meta( $c_page_id, 'pyre_page_title_custom_subheader_text_size', true ) && '' != get_post_meta( $c_page_id, 'pyre_page_title_custom_subheader_text_size', true ) ) {
		$css['global']['.fusion-page-title-bar h3']['font-size']   = Fusion_Sanitize::size( get_post_meta( $c_page_id, 'pyre_page_title_custom_subheader_text_size', true ) );
		$css['global']['.fusion-page-title-bar h3']['line-height'] = Fusion_Sanitize::add_css_values( array( Fusion_Sanitize::size( Avada()->settings->get( 'page_title_subheader_font_size' ) ), '12px' ) );
	}

	$header_width = Fusion_Sanitize::size( Avada()->settings->get( 'header_100_width' ) );

	if ( 'yes' === get_post_meta( $c_page_id, 'pyre_header_100_width', true ) ) {
		$header_width = true;
	} elseif ( 'no' === get_post_meta( $c_page_id, 'pyre_header_100_width', true ) ) {
		$header_width = false;
	}

	if ( $header_width ) {
		$css['global']['.layout-wide-mode .fusion-header-wrapper .fusion-row']['max-width'] = '100%';
	}

	$css['global']['body.avada-not-responsive']['min-width'] = Fusion_Sanitize::size( Avada()->settings->get( 'site_width' ) );
	if ( ! Avada()->settings->get( 'responsive' ) ) {
		if ( ! $site_width_percent ) {
			$css['global']['html']['overflow-x'] = 'auto';
			$css['global']['body']['overflow-x'] = 'auto';
		}
	}

	$elements = array(
		'.fusion-flexslider .flex-direction-nav a',
		'.fusion-flexslider.flexslider-posts .flex-direction-nav a',
		'.fusion-flexslider.flexslider-posts-with-excerpt .flex-direction-nav a',
		'.fusion-flexslider.flexslider-attachments .flex-direction-nav a',
		'.fusion-slider-sc .flex-direction-nav a',
	);

	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = '.woocommerce-product-gallery .flex-direction-nav a';
	}

	$carousel_elements = array(
		'.fusion-carousel .fusion-carousel-nav .fusion-nav-prev',
		'.fusion-carousel .fusion-carousel-nav .fusion-nav-next',
	);

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']          = Fusion_Sanitize::size( Avada()->settings->get( 'slider_nav_box_dimensions', 'width' ) );
	$css['global'][ $dynamic_css_helpers->implode( $carousel_elements ) ]['width'] = Fusion_Sanitize::size( Avada()->settings->get( 'slider_nav_box_dimensions', 'width' ) );

	preg_match_all( '!\d+!', Avada()->settings->get( 'slider_nav_box_dimensions', 'height' ), $matches );
	$half_slider_nav_box_height = $matches[0][0] / 2 . Fusion_Sanitize::get_unit( Avada()->settings->get( 'slider_nav_box_dimensions', 'height' ) );

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']      = Fusion_Sanitize::size( Avada()->settings->get( 'slider_nav_box_dimensions', 'height' ) );
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'slider_nav_box_dimensions', 'height' ) );

	$css['global'][ $dynamic_css_helpers->implode( $carousel_elements ) ]['height']     = Fusion_Sanitize::size( Avada()->settings->get( 'slider_nav_box_dimensions', 'height' ) );
	$css['global'][ $dynamic_css_helpers->implode( $carousel_elements ) ]['margin-top'] = '-' . $half_slider_nav_box_height;

	$carousel_elements = array(
		'.fusion-carousel .fusion-carousel-nav .fusion-nav-prev:before',
		'.fusion-carousel .fusion-carousel-nav .fusion-nav-next:before',
	);

	$css['global'][ $dynamic_css_helpers->implode( $carousel_elements ) ]['line-height'] = Fusion_Sanitize::size( Avada()->settings->get( 'slider_nav_box_dimensions', 'height' ) );

	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'slider_arrow_size' ) );

	$css['global'][ $dynamic_css_helpers->implode( $carousel_elements ) ]['font-size'] = Fusion_Sanitize::size( Avada()->settings->get( 'slider_arrow_size' ) );

	$elements = array(
		'.pagination a.inactive',
		'.page-links a',
		'.woocommerce-pagination .page-numbers',
		'.bbp-pagination .bbp-pagination-links a.inactive',
		'.bbp-topic-pagination .page-numbers',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'pagination_box_padding', 'height' ) ) . ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'pagination_box_padding', 'width' ) );

	$elements = array(
		'.pagination .current',
		'.page-links > .page-number',
		'.woocommerce-pagination .current',
		'.bbp-pagination .bbp-pagination-links .current',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'pagination_box_padding', 'height' ) ) . ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'pagination_box_padding', 'width' ) );

	$elements = array(
		'.fusion-hide-pagination-text .pagination .pagination-prev',
		'.fusion-hide-pagination-text .woocommerce-pagination .prev',
		'.fusion-hide-pagination-text .bbp-pagination .bbp-pagination-links .pagination-prev',
		'.fusion-hide-pagination-text .pagination .pagination-next',
		'.fusion-hide-pagination-text .woocommerce-pagination .next',
		'.fusion-hide-pagination-text .bbp-pagination .bbp-pagination-links .pagination-next',
		'.bbp-pagination-links span.dots',
	);
	$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = Fusion_Sanitize::size( Avada()->settings->get( 'pagination_box_padding', 'height' ) ) . ' ' . Fusion_Sanitize::size( Avada()->settings->get( 'pagination_box_padding', 'width' ) );

	// Button styling.
	if ( ! class_exists( 'FusionBuilder' ) ) {
		$elements = array(
			'.fusion-button-default',
			'.wpcf7-submit',
			'.comment-form .fusion-button-default',
			'.single_add_to_cart_button',
			'.comment-respond .submit',
		);
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background']   = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) );
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']        = '#ffffff';
	}

	/**
	 * Hack to fix font-names using '+' instead of ' '.
	 * This happens when using WPML coupled with the string-translation plugin.
	 * FIxes #3309
	 */
	if ( defined( 'ICL_SITEPRESS_VERSION' || class_exists( 'SitePress' ) ) ) {
		foreach ( $css as $media_query => $elements ) {
			foreach ( $elements as $element => $properties ) {
				foreach ( $properties as $property => $value ) {
					if ( 'font-family' === $property ) {
						$css[ $media_query ][ $element ][ $property ] = str_replace( '+', ' ', $value );
					}
				}
			}
		}
	}

	$avada_dynamic_css_array_added = true;

	$css = array_replace_recursive( $css, $original_css );

	return apply_filters( 'avada_dynamic_css_array', $css );

}
add_filter( 'fusion_dynamic_css_array', 'avada_dynamic_css_array', 999 );
