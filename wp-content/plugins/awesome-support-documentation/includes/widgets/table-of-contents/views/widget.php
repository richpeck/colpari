<?php
global $post;

/**
 * Get the documentation pages hierarchically sorted.
 * 
 * @var array
 */
$doc_pages = wpas_doc_get_hierarchy( $post->ID ); ?>

<div id="as-collapse">

	<?php foreach ( $doc_pages as $section_id => $section ): ?>

		<h3 class="as-collapse-heading <?php if ( in_array( $post->ID, $section['pages'] ) || $post->ID === $section_id ): ?>open<?php endif; ?>"><?php echo $section['name']; ?> <span class="as-collapse-badge"><?php echo count( $section['pages'] ); ?></span></h3>

		<?php if ( ! empty( $section['pages'] ) ): ?>
			<div>
				<div class="as-collapse-content">
					<?php foreach ( $section['pages'] as $child_page_id ): ?>
						<a href="<?php echo get_permalink( $child_page_id ); ?>" <?php if ( $post->ID === $child_page_id ): ?>class="as-collapse-active"<?php endif; ?>><?php echo get_the_title( $child_page_id ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	<?php endforeach; ?>

</div>