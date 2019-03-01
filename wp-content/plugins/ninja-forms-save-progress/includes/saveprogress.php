<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress extends NF_SaveProgress_Plugin
{
    private $controllers;

    public function __construct( $version, $file )
    {
        parent::__construct( $version, $file );

        // Setup Controllers.
        $this->controllers[ 'builder' ]     = new NF_SaveProgress_Controller_Builder();
        $this->controllers[ 'form' ]        = new NF_SaveProgress_Controller_Form();
        $this->controllers[ 'menu' ]        = new NF_SaveProgress_Controller_Menu();
        $this->controllers[ 'rest' ]        = new NF_SaveProgress_Controller_REST();
        $this->controllers[ 'settings' ]    = new NF_SaveProgress_Controller_Settings();
        $this->controllers[ 'submissions' ] = new NF_SaveProgress_Controller_Submission();

        add_filter( 'ninja_forms_register_fields', array( $this, 'register_fields' ) );

        include_once $this->dir( 'includes/deprecated.php' );
    }

    /*
    |--------------------------------------------------------------------------
    | Action & Filter Hooks
    |--------------------------------------------------------------------------
    */

    public function register_fields( $fields )
    {
        require_once plugin_dir_path( __FILE__ ) . 'fields/savebutton.php';
        $fields[ 'save' ] = new NF_SaveProgress_SaveButton();

        return $fields;
    }

    /*
    |--------------------------------------------------------------------------
    | Internal API
    |--------------------------------------------------------------------------
    */

    public function saves()
    {
        static $repository;
        if( ! isset( $repository ) ){
            $repository = new NF_SaveProgress_Database_SaveRepository();
        }
        return $repository;
    }

}
