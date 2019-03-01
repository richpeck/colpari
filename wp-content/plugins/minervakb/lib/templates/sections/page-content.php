<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2016 @KonstruktStudio
 */

?>
<div class="mkb-container">
	<div class="mkb-page-content mkb-clearfix">
		<?php if(MKB_Options::option('page_template') == 'theme'):
			global $post;

			// we cannot use the_content(), since we add content with the_content filter
			echo do_shortcode( $post->post_content );
		else:
			the_content();
		endif;
		?>
	</div>
</div>