<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$custom_descp = '';
$full_dir     = untrailingslashit( NF_File_Uploads()->controllers->uploads->get_path( '[form_id]/' ) );

if ( ! isset( $raw ) ) {
	$custom_descp = __( 'If you want to create dynamic directories, you can use various shortcodes:', 'ninja-forms-uploads' );
	$custom_descp .= '<ul>';
	$shortcodes = NF_File_Uploads()->controllers->custom_paths->get_shortcodes();
	foreach ( $shortcodes as $shortcode => $descp ) {
		$custom_descp .= '<li>%' . $shortcode . '% - ' . $descp . '</li>';
	}
	$custom_descp .= '</ul>';
	$custom_descp .= '<p>' . __( 'For Example: <code>/%formtitle%/%month%/%year%/</code> would be <code>/MyFormTitle/04/2012/</code>', 'ninja-forms-uploads' ) . '</p>';

	$full_dir .= NF_File_Uploads()->controllers->settings->custom_upload_dir();
}

$max_filesize = NF_FU_Helper::format_mb( ini_get( 'upload_max_filesize' ) );
$max_mb_int = substr( $max_filesize, 0, -1 );

if ( isset( $default ) ) {
	$default = ( $default < $max_mb_int ) ? $default : $max_mb_int;
} else {
	$default = $max_mb_int;
}

return apply_filters( 'ninja_forms_uploads_settings_upload', array(
	'max_filesize' => array(
		'id'      => 'max_filesize',
		'type'    => 'number',
		'max'     => $max_mb_int,
		'default' => $default,
		'label'   => __( 'Max File Size (in MB)', 'ninja-forms-uploads' ),
		'desc'    => sprintf( __( 'Your server\'s maximum file size is set to %s. This setting cannot be increased beyond this value. To increase your server file size limit, please contact your host.', 'ninja-forms-uploads' ), $max_filesize ),
	),

	'upload_error' => array(
		'id'      => 'upload_error',
		'type'    => 'textbox',
		'default' => 'There was an error uploading your file.',
		'label'   => __( 'File upload error message', 'ninja-forms-uploads' ),
		'desc'    => '',
	),

	'custom_upload_dir' => array(
		'id'    => 'custom_upload_dir',
		'type'  => 'textbox',
		'label' => __( 'Custom Directory', 'ninja-forms-uploads' ),
		'desc'  => $custom_descp,
	),

	'full_dir' => array(
		'id'    => 'full_dir',
		'type'  => 'desc',
		'label' => __( 'Full Directory', 'ninja-forms-uploads' ),
		'default'  => '<code>' . $full_dir . '</code>',
	),
) );
