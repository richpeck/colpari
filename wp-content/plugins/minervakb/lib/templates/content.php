<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

$article_icon = MKB_Options::option('article_icon');

?><div id="mkb-article-<?php the_ID(); ?>" class="mkb-article-item mkb-article-item--simple"><?php

	do_action('minerva_loop_entry_before');

	?><div class="mkb-entry-header"><?php

		do_action('minerva_loop_entry_inside_before');

		$article_title = get_the_title();

		?>

		<h2 class="mkb-entry-title">

			<?php do_action('minerva_loop_entry_icon_before'); ?>

			<i class="mkb-article-icon fa fa-lg <?php esc_attr_e($article_icon); ?>"></i>

			<?php do_action('minerva_loop_entry_icon_after'); ?>

			<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">

				<?php do_action('minerva_loop_entry_title_inside_before'); ?>

				<?php esc_html_e($article_title); ?>

				<?php do_action('minerva_loop_entry_title_inside_after'); ?>

			</a>

			<?php do_action('minerva_loop_entry_title_after'); ?>

		</h2>

		<?php

		do_action('minerva_loop_entry_inside_after');

	?></div><!-- .mkb-entry-header --><?php

	do_action('minerva_loop_entry_after');

?></div><!-- #mkb-article-## -->