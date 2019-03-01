<?php if ( ! defined( 'ABSPATH' ) ) exit;

abstract class NF_SaveProgress_Plugin
{
    private $version;
    private $url;
    private $dir;

    public function __construct( $version, $file )
    {
        $this->version = $version;
        $this->url = plugin_dir_url( $file );
        $this->dir = plugin_dir_path( $file );

        add_action( 'admin_init',     array( $this, 'setup_license'   ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }

    public function setup_license()
    {
        if ( ! class_exists( 'NF_Extension_Updater' ) ) return;
        new NF_Extension_Updater( 'Save Progress', $this->version(), 'The WP Ninjas', $this->dir(), 'save-progress' );
    }

    public function load_textdomain()
    {
        load_plugin_textdomain( 'ninja-forms-save-progress', false, basename( dirname( __FILE__ ) ) . '/lang' );
    }

    /*
    |--------------------------------------------------------------------------
    | Plugin Methods
    |--------------------------------------------------------------------------
    */

    public function version()
    {
        return $this->version;
    }

    public function url( $url = '' )
    {
        return trailingslashit( $this->url ) . $url;
    }

    public function dir( $path = '' )
    {
        return trailingslashit( $this->dir ) . $path;
    }

    public function template( $file, $args = array() )
    {
        $path = $this->dir( 'templates/' . $file );
        if( ! file_exists(  $path ) ) return '';
        extract( $args );

        ob_start();
        include $path;
        return ob_get_clean();
    }

}
