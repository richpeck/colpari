<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

if (!MKB_Options::option('no_page_header')):
	get_header();
endif;

do_action('minerva_page_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('page')); ?>"><?php

	MKB_TemplateHelper::maybe_render_left_sidebar( 'page' );

	?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('page')); ?>"><?php

		while (have_posts()) : the_post(); // main loop

			if ((!MKB_PageOptions::is_builder_page() && MKB_Options::option('home_page_title_switch')) || MKB_PageOptions::option('show_title')):

				do_action('minerva_page_title_before');

				?><div class="mkb-page-header"><?php

					do_action('minerva_page_title_inside_before');

					the_title( '<h1 class="mkb-page-title">', '</h1>' );

					do_action('minerva_page_title_inside_after');

				?></div><?php

				do_action('minerva_page_title_after');

			endif;

			do_action('minerva_page_loop_before');

			?><div class="mkb-page-content"><?php

				do_action('minerva_page_content_inside_before');

				MKB_TemplateHelper::home_content();

				do_action('minerva_page_content_inside_after');

			?></div><!-- .mkb-entry-content --></div><?php

			do_action('minerva_page_loop_after');

		endwhile;

	MKB_TemplateHelper::maybe_render_right_sidebar( 'page' );

	?></div><!--.mkb-container--><?php

do_action('minerva_page_root_after');

if (!MKB_Options::option('no_page_footer')):
	get_footer();
endif;

?>