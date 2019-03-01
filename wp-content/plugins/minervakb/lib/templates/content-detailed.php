<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

$article_icon = MKB_Options::option('article_icon');

?><div id="mkb-article-<?php the_ID(); ?>" class="mkb-article-item mkb-article-item--detailed"><?php

	do_action('minerva_loop_entry_before');

	?><div class="mkb-entry-header"><?php

		do_action('minerva_loop_entry_inside_before');

		$is_search = isset($_REQUEST['s']) && trim($_REQUEST['s']);

		$terms = wp_get_post_terms( get_the_ID(), MKB_Options::option( 'article_cpt_category' ));
		$first_term = sizeof($terms) ? $terms[0] : null;
		$title = get_the_title();

		if ($is_search) {
			$term = strtolower($_REQUEST['s']);

			$title = preg_replace_callback(
				"/$term+/i",
				function ( $matches ) {
					return '<strong>' . $matches[0] . '</strong>';
				},
				$title
			);
		}

		?><div class="mkb-entry-title-wrap">
			<h2 class="mkb-entry-title">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php echo $title; ?></a>
			</h2><?php

			if (MKB_Options::option('show_search_page_topic') && $first_term && $is_search):
				?><span class="mkb-article-item__topic">
					<a href="<?php echo esc_attr(get_term_link($first_term)); ?>"><?php
						echo esc_html($first_term->name); ?></a></span><?php
			endif;

		?></div><?php

		$excerpt_length = (int) MKB_Options::option('search_results_excerpt_length');
		$plain_content = strip_tags(get_the_content());
		$plain_content = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $plain_content);

		$start_pos = 0;

		if ($is_search) {
			$term = strtolower($_REQUEST['s']);
			$first_entry_pos = strpos(strtolower($plain_content), $term);
			$start_pos = max(0, $first_entry_pos - $excerpt_length / 2);
		}

		$end_pos = $start_pos + $excerpt_length;

		$prefix = $start_pos > 0 ? '...' : '';
		$postfix = $end_pos < strlen($plain_content) ? '...' : '';

		// trim excerpt to desired length
		$content = $prefix . mb_substr($plain_content, $start_pos, $excerpt_length) . $postfix;

		if ($is_search) {
			// wrap matches in highlight spans
			$content = preg_replace_callback(
				"/$term+/i",
				function ( $matches ) {
					return '<span class="mkb-search-match">' . $matches[0] . '</span>';
				},
				$content
			);
		}

		?><div class="mkb-article-item__meta"><?php

			// results meta
			$views = get_post_meta( $id, '_mkb_views', true );
			$likes = get_post_meta( $id, '_mkb_likes', true );
			$dislikes = get_post_meta( $id, '_mkb_dislikes', true );

			?><?php if (MKB_Options::option('show_search_page_last_edit')):
				?><span class="mkb-article-item__meta-item">
				<span class="mkb-article-item__modified">
					<i class="fa fa-pencil mkb-article-item__meta-icon"></i><?php the_modified_date('M, n'); ?></span>
			</span><?php
			endif;
			if (MKB_Options::option('show_search_page_views') && $views):
				?><span class="mkb-article-item__meta-item" title="Views">
				<span class="mkb-article-item__views">
					<i class="fa fa-eye mkb-article-item__meta-icon"></i><?php echo esc_html($views); ?></span>
			</span><?php
			endif;
			if (MKB_Options::option('show_search_page_likes') && $likes):
				?><span class="mkb-article-item__meta-item" title="Likes">
				<span class="mkb-article-item__likes">
					<i class="fa fa-smile-o mkb-article-item__meta-icon"></i><?php echo esc_html($likes); ?></span>
			</span><?php
			endif;
			if (MKB_Options::option('show_search_page_dislikes') && $dislikes):
				?><span class="mkb-article-item__meta-item" title="Dislikes">
				<span class="mkb-article-item__dislikes">
					<i class="fa fa-frown-o mkb-article-item__meta-icon"></i><?php echo esc_html($dislikes); ?></span>
			</span><?php
			endif;
			?></div>

		<div class="mkb-article-item__excerpt">
			<?php echo wp_kses_post($content); ?>
		</div><?php

		do_action('minerva_loop_entry_inside_after');

		?></div><!-- .mkb-entry-header --><?php

	do_action('minerva_loop_entry_after');

?></div><!-- #mkb-article-## -->