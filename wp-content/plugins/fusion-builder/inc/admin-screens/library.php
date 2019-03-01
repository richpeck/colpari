<?php
/**
 * Admin Screen markup (Ligrary page).
 *
 * @package fusion-builder
 */

?>
<div class="wrap about-wrap fusion-builder-wrap">

	<?php Fusion_Builder_Admin::header(); ?>

	<div class="fusion-builder-important-notice">
		<p class="about-description">
			<?php
			printf(
				/* translators: "Fusion Builder" link. */
				esc_html__( 'This is your collection of Fusion Builder Library Containers, Columns and Elements that you have saved. You can edit them individually below, delete them and sort them per category type. To find out more about the %1$s, view our Fusion Builder documentation.', 'fusion-builder' ),
				'<a href="https://theme-fusion.com/documentation/fusion-builder/fusion-builder-library/" target="_blank">' . esc_attr__( 'Fusion Builder', 'fusion-builder' ) . '</a>'
			);
			?>
		</p>
	</div>

	<div class="fusion-library-data-items">
		<?php
			$fusion_library_table = new Fusion_Builder_Library_Table();
			$fusion_library_table->get_status_links();
		?>
		<form id="fusion-library-data" method="get">
			<?php
			$fusion_library_table->prepare_items();
			$fusion_library_table->display();
			?>
		</form>
	</div>

	<?php Fusion_Builder_Admin::footer(); ?>
</div>
