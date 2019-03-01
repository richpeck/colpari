<?php 

if ( ! defined( 'WPINC' ) ) {
	die;
}

$credentials = $this->get_credentials( $id, true );

if ( $credentials ) {

    $this->loadTemplate( 'credentials', array(
        'id'          => $id,  
        'credentials' => $credentials
    ));

} else {

    $this->loadTemplate( 'add_credentials', array(
        'id' => $id
    ));

}