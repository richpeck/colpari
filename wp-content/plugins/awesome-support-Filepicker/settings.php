<?php
add_filter( 'wpas_plugin_settings', 'wpas_addon_settings_filepicker', 11, 1 );
/**
 * Add plugin file upload settings.
 *
 * @param  array $settings Array of existing settings
 *
 * @return array      Updated settings
 */
function wpas_addon_settings_filepicker( $settings ) {

	$settings['file_upload']['options'][] = array(
		'type' => 'heading',
		'name' => __( 'Filepicker', 'as-filepicker' )
	);

	$settings['file_upload']['options'][] = array(
		'name'    => __( 'API Key', 'as-filepicker' ),
		'id'      => 'filepicker_api_key',
		'type'    => 'text',
		'default' => '',
		'desc'    => sprintf( __( 'In order to get your API key, you need to <a href="%s" target="_blank">create an application in Filepicker</a>.', 'as-filepicker' ), esc_url( 'https://developers.filepicker.io/new_application' ) )
	);

	$settings['file_upload']['options'][] = array(
		'name'    => __( 'API Secret', 'as-filepicker' ),
		'id'      => 'filepicker_api_secret',
		'type'    => 'text',
		'default' => '',
		'desc'    => __( 'The API secret is optional but recommended. It will enforce security of your files by limiting how users can interact with them.', 'as-filepicker' )
	);

	$settings['file_upload']['options'][] = array(
		'name'    => __( 'Enabled Services', 'as-filepicker' ),
		'id'      => 'filepicker_services',
		'type'    => 'multicheck',
		'default' => array( 'COMPUTER', 'GOOGLE_DRIVE', 'SKYDRIVE' ),
		'desc'    => __( 'Which services do you want to allow users to pick files from when using Filepicker? Some of these services may require a paid plan.', 'as-filepicker' ),
		'options' => array(
			'BOX'          => 'Box',
			'COMPUTER'     => 'Computer',
			'DROPBOX'      => sprintf( 'Dropbox (requires <a href="%s" target="_blank">additional configuration in Filepicker</a>)', esc_url( 'http://blog.filepicker.io/post/91790833702/install-your-own-filepicker-developer-app-on-dropbox' ) ),
			'EVERNOTE'     => 'Evernote',
			'FACEBOOK'     => 'Facebook',
			'GMAIL'        => 'Gmail',
			'IMAGE_SEARCH' => 'Image Search',
			'FLICKR'       => 'Flickr',
			'FTP'          => 'FTP',
			'GITHUB'       => 'Github',
			'GOOGLE_DRIVE' => 'Google Drive',
			'SKYDRIVE'     => 'Skydrive',
			'PICASA'       => 'Picasa',
			'URL'          => 'Url',
			'WEBCAM'       => 'Webcam',
			'SEND_EMAIL'   => 'Send Email',
			'INSTAGRAM'    => 'Instagram',
			'FLICKR'       => 'Flickr',
			'VIDEO'        => 'Video',
			'ALFRESCO'     => 'Alfresco',
			'CUSTOMSOURCE' => 'Customsource',
			'CLOUDDRIVE'   => 'Cloud Drive',
		),
	);

	return $settings;

}