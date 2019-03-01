<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

if (!MKB_Options::option('no_search_header')):
	get_header();
endif;

do_action('minerva_search_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('search')); ?>"><?php

	MKB_TemplateHelper::maybe_render_left_sidebar( 'search' );

	?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('search')); ?>"><?php

		if (have_posts()): // search returned results

			do_action('minerva_search_title_before');

			?><div class="mkb-page-header"><?php

				do_action('minerva_search_title_inside_before');

				?><h1 class="mkb-page-title"><?php

				global $wp_query;

			    printf( MKB_Options::option('search_results_page_title'),
					 esc_html($wp_query->found_posts),
					 '<span>' . esc_html( get_search_query() ) . '</span>' );
			    ?></h1><?php

				do_action('minerva_search_title_inside_after');

			?></div><?php

			do_action('minerva_search_title_after');

			do_action('minerva_search_loop_before');

			$template = MKB_Options::option('search_results_layout') === 'simple' ? 'content' : 'content-detailed';

			// main search loop
			while ( have_posts() ) : the_post();
				include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/' . $template . '.php' );
			endwhile;

			do_action('minerva_search_loop_after');

		else: // search returned no results

			include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/no-content.php' );

		endif;
		?></div><!--.mkb-content-main--><?php

	MKB_TemplateHelper::maybe_render_right_sidebar( 'search' );

	?></div><!--.mkb-container--><?php

do_action('minerva_search_root_after');

if (!MKB_Options::option('no_search_footer')):
	get_footer();
endif;

?>