<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

if (!MKB_Options::option('no_article_header')):
	get_header();
endif;

do_action('minerva_single_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('article')); ?>"><?php

		MKB_TemplateHelper::maybe_render_left_sidebar( 'article' );

		?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('article')); ?>"><?php

			while (have_posts()) : the_post(); // main loop

				?><div id="mkb-article-<?php the_ID(); ?>"><?php

					do_action('minerva_single_title_before');

					?><div class="mkb-page-header"><?php

						do_action('minerva_single_title_inside_before');

						if (MKB_Options::option('show_article_title')):
							the_title( '<h1 class="mkb-page-title">', '</h1>' );
						endif;

						do_action('minerva_single_title_inside_after');

					?></div><!-- .mkb-entry-header --><?php

					do_action('minerva_single_title_after');

					?><div class="mkb-single-content"><?php

						do_action('minerva_single_content_inside_before');

						?><div class="mkb-single-content__featured"><?php

							do_action('minerva_single_featured_before');

							the_post_thumbnail();

							do_action('minerva_single_featured_after');

						?></div><?php

						?><div class="mkb-single-content__text"><?php

							do_action('minerva_single_text_before');

							MKB_TemplateHelper::single_content();

							do_action('minerva_single_text_after');

						?></div><?php

						do_action('minerva_single_content_inside_after');

					?></div><!-- .mkb-single-content --><?php

					do_action('minerva_single_content_after');

					?></div><!-- #mkb-article-## --><?php

			endwhile;

			if (MKB_Options::option('enable_comments') && MKB_Options::option('comments_position') === 'after_content') {
				comments_template();
			}

			?></div><!--.mkb-content-main--><?php

		MKB_TemplateHelper::maybe_render_right_sidebar( 'article' );

		if (MKB_Options::option('enable_comments') && MKB_Options::option('comments_position') === 'inside_container') {
			comments_template();
		}

		?></div><!--.mkb-container--><?php

if (MKB_Options::option('enable_comments') && MKB_Options::option('comments_position') === 'after_container') {
	comments_template();
}

do_action('minerva_single_root_after');

if (!MKB_Options::option('no_article_footer')):
	get_footer();
endif;

?>