<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2016 @KonstruktStudio
 */

global $minerva_kb_page_render;
$minerva_kb_page_render = true;

$sections = MKB_PageOptions::get_builder_sections();

if ( isset( $sections ) && ! empty( $sections ) ):
	foreach ( $sections as $section ):
		?>
		<div class="mkb-builder-section">
			<?php
			include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/sections/' . $section['type'] . '.php' );
			?>
		</div>
	<?php
	endforeach;
endif;

$minerva_kb_page_render = false;
