<?php
/**
 * @package   AS Email Support/Metaboxes/Attachments
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://julienliabeuf.com
 * @copyright 2017 Julien Liabeuf
 */
?>
<div id="wpas-unknown-message-attachments">
	<?php WPAS_File_Upload::get_instance()->show_attachments( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) ); ?>
</div>
