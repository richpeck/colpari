<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

if (!MKB_Options::option('no_archive_header')):
	get_header();
endif;

do_action('minerva_category_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('topic')); ?>"><?php

	MKB_TemplateHelper::maybe_render_left_sidebar( 'topic' );

	?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('topic')); ?>"><?php

		if (have_posts()): // archive has articles

			do_action('minerva_category_title_before');

			?><div class="mkb-page-header"><?php

				do_action('minerva_category_title_inside_before');

				the_archive_title( '<h1 class="mkb-page-title">', '</h1>' );
				the_archive_description( '<div class="mkb-taxonomy-description">', '</div>' );

				do_action('minerva_category_title_inside_after');

			?></div><?php

			do_action('minerva_category_title_after');

			do_action('minerva_category_loop_before');

			while ( have_posts() ) : the_post();
				include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/content.php' );
			endwhile;

			do_action('minerva_category_loop_after');

			else: // archive has no articles

				include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/no-content.php' );

			endif;
			?></div><!--.mkb-content-main--><?php

	MKB_TemplateHelper::maybe_render_right_sidebar( 'topic' );

	?></div><!--.mkb-container--><?php

do_action('minerva_category_root_after');

if (!MKB_Options::option('no_archive_footer')):
	get_footer();
endif;

?>