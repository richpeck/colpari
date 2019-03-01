<section class="conditions-wrapper">
<?php 
if( ! empty ( $conditions ) ) {
	
	foreach( $conditions as $condition ) {
		$condition->render_field();
	}
} 
?>	
</section>