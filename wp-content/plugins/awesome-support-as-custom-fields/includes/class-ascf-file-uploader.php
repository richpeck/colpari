<?php

//load main class if not exists
if( !class_exists( 'WPAS_File_Upload' ) ){
	if( defined( WPAS_PATH ) && file_exists(  WPAS_PATH . 'includes/file-uploader/class-file-uploader.php' ) ){
		require( WPAS_PATH . 'includes/file-uploader/class-file-uploader.php' );
	} else {
		exit();
	}	
}

if( !class_exists( 'ASCF_File_Upload' ) ):	
	
class ASCF_File_Upload extends WPAS_File_Upload {
	protected $index;
	
	public function __construct( $field_name ) {
		$this->setup_index( $field_name );
		if( is_admin() ){
			add_action( 'wpas_add_reply_admin_after', array( $this, 'new_reply_backend_attachment' ), 10, 2 );			
		} else {
			add_action( 'wpas_open_ticket_after', array( $this, 'new_ticket_attachment' ), 10, 2 );
		}
	}
	
	protected function setup_index( $field_name ){
		$this->index = $field_name;
	}	
}
	
endif;

