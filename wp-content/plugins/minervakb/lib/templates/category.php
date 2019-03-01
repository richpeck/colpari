<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

$term = get_queried_object();

if (!MKB_Options::option('no_topic_header')):
	get_header();
endif;

do_action('minerva_category_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('topic')); ?>"><?php

	MKB_TemplateHelper::maybe_render_left_sidebar( 'topic', $term );

	?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('topic')); ?>"><?php
			
		do_action('minerva_category_title_before');

		?><div class="mkb-page-header"><?php

			do_action('minerva_category_title_inside_before');

			if (MKB_Options::option('show_topic_title') && !MinervaKB::topic_option($term, 'topic_no_title_switch')) {
				if (MKB_Options::option('topic_customize_title')) {
					?><h1 class="mkb-page-title"><?php
						single_term_title(MKB_Options::option('topic_custom_title_prefix'));
					?></h1><?php
				} else {
					the_archive_title('<h1 class="mkb-page-title">', '</h1>');
				}
			}

			if (MKB_Options::option('show_topic_description') && !MinervaKB::topic_option($term, 'topic_no_description_switch')) {
				the_archive_description('<div class="mkb-taxonomy-description">', '</div>');
			}

			do_action('minerva_category_title_inside_after');

		?></div><?php

		do_action('minerva_category_title_after');

		do_action('minerva_category_loop_before');

		if (MinervaKB::topic_option($term, 'topic_page_switch') && MinervaKB::topic_option($term, 'topic_page')) {
			$page = get_post(MinervaKB::topic_option($term, 'topic_page'));
			echo apply_filters('the_content', $page->post_content);
		} else {
			$topic_view = 'content';

			if (MKB_Options::option('topic_template_view') === 'detailed') {
				$topic_view = 'content-detailed';
			}

			?>
			<div class="mkb-article-list-container article-list-layout-<?php esc_attr_e(MKB_Options::option('topic_list_layout')); ?>">
				<?php
				while (have_posts()) : the_post();
					include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/' . $topic_view . '.php' );
				endwhile;
				?>
			</div>
		<?php
		}

		do_action('minerva_category_loop_after');

		?></div><!--.mkb-content-main--><?php

	MKB_TemplateHelper::maybe_render_right_sidebar( 'topic', $term );

	?></div><!--.mkb-container--><?php

do_action('minerva_category_root_after');

if (!MKB_Options::option('no_topic_footer')):
	get_footer();
endif;

?>