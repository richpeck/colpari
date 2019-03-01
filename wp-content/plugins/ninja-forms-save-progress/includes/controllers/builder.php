<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress_Controller_Builder
{
    public function __construct()
    {
        add_action( 'nf_admin_enqueue_scripts', array( $this, 'register_scripts' ) );
        add_action( 'ninja_forms_builder_templates', array( $this, 'builder_templates' ) );
    }

    public function register_scripts()
    {
        wp_register_script( 'nf-save-progress--builder', NF_SaveProgress()->url( 'assets/js/min/builder.min.js' ), array( 'nf-builder' ), NF_SaveProgress()->version(), true );
        wp_enqueue_script( 'nf-save-progress--builder' );
    }

    public function builder_templates()
    {
        echo NF_SaveProgress()->template( 'action-table.html.php' );
        echo NF_SaveProgress()->template( 'action-item.html.php' );
    }
}