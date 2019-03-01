<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

?><?php

global $minerva_kb_page_render;
$minerva_kb_page_render = true;

if (MKB_Options::option('home_sections_switch')) {
	// custom sections select
	$sections = explode(',', MKB_Options::option('home_sections'));

	foreach($sections as $section_id) {
		switch($section_id) {
			case 'search':
				MKB_TemplateHelper::render_search();
				break;

			case 'topics':
				MKB_TemplateHelper::render_topics();
				break;

			case 'faq':
				MKB_TemplateHelper::render_faq(array(
					'home_faq_title' => MKB_Options::option('home_faq_title'),
					'home_faq_margin_top' => MKB_Options::option('home_faq_margin_top'),
					'home_faq_margin_bottom' => MKB_Options::option('home_faq_margin_bottom'),
					'home_faq_categories' => MKB_Options::option('home_faq_categories'),
					'home_faq_title_size' => MKB_Options::option('home_faq_title_size'),
					'home_faq_title_color' => MKB_Options::option('home_faq_title_color'),
					'home_faq_width_limit' => MKB_Options::option('home_faq_width_limit'),
					'home_faq_controls_margin_top' => MKB_Options::option('home_faq_controls_margin_top'),
					'home_faq_controls_margin_bottom' => MKB_Options::option('home_faq_controls_margin_bottom'),
					'home_faq_limit_width_switch' => MKB_Options::option('home_faq_limit_width_switch'),
					'home_show_faq_filter' => MKB_Options::option('home_show_faq_filter'),
					'show_faq_filter_icon' => MKB_Options::option('show_faq_filter_icon'),
					'faq_filter_icon' => MKB_Options::option('faq_filter_icon'),
					'faq_filter_theme' => MKB_Options::option('faq_filter_theme'),
					'faq_filter_placeholder' => MKB_Options::option('faq_filter_placeholder'),
					'faq_filter_clear_icon' => MKB_Options::option('faq_filter_clear_icon'),
					'faq_no_results_text' => MKB_Options::option('faq_no_results_text'),
					'home_show_faq_toggle_all' => MKB_Options::option('home_show_faq_toggle_all'),
					'show_faq_toggle_all_icon' => MKB_Options::option('show_faq_toggle_all_icon'),
					'faq_toggle_all_icon' => MKB_Options::option('faq_toggle_all_icon'),
					'faq_toggle_all_icon_open' => MKB_Options::option('faq_toggle_all_icon_open'),
					'faq_toggle_all_open_text' => MKB_Options::option('faq_toggle_all_open_text'),
					'faq_toggle_all_close_text' => MKB_Options::option('faq_toggle_all_close_text'),
				));
				break;

			default:
				break;
		}
	}
} else {
	// default sections

	// home search
	MKB_TemplateHelper::render_search();

	// home topics
	MKB_TemplateHelper::render_topics();
}

$minerva_kb_page_render = false;
