<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress_Controller_Form
{
    public function __construct()
    {
        add_action( 'ninja_forms_enqueue_scripts', array( $this, 'register_scripts' ) );
        add_action( 'ninja_forms_output_templates',    array( $this, 'output_templates'     ) );
        add_filter( 'ninja_forms_display_before_form', array( $this, 'save_table_container' ), 10, 2 );
        add_filter( 'ninja_forms_display_form_settings', array( $this, 'localize_save_actions' ), 10, 2 );
    }

    public function register_scripts()
    {
        if( ! class_exists( 'Ninja_Forms' ) ) return;
        wp_register_script('nf-moment-with-locales', Ninja_Forms::$url . 'assets/js/lib/moment-with-locales.min.js', array('nf-front-end'), Ninja_Forms::VERSION, true);
        wp_register_script('nf-save-progress--front-end', NF_SaveProgress()->url('assets/js/min/front-end.min.js'), array('wp-api', 'nf-front-end', 'nf-moment-with-locales'), NF_SaveProgress()->version(), true);
        wp_localize_script('nf-save-progress--front-end', 'nfSaveProgress', array(
            'currentUserID' => get_current_user_id(),
            'restApiEndpoint' => rest_url('ninja-forms-save-progress/v1/'),
        ));
        wp_enqueue_script('nf-save-progress--front-end');
        wp_enqueue_style( 'nf-save-progress--front-end', NF_SaveProgress()->url( 'assets/styles/min/saves-table.css' ) );
    }

    public function save_table_container( $content, $form_id )
    {
        $form = Ninja_Forms()->form( $form_id )->get();
        $save_table_legend = $form->get_setting( 'save_progress_table_legend' );
        return NF_SaveProgress()->template( 'save-table-container.html.php', compact( 'form_id', 'save_table_legend' ) );
    }

    public function output_templates()
    {
        echo NF_SaveProgress()->template( 'save-table.html' );
        echo NF_SaveProgress()->template( 'save-item.html' );
        echo NF_SaveProgress()->template( 'save-empty.html' );
        echo NF_SaveProgress()->template( 'saves-loading.html' );
    }

    /**
     * Localize Save Actions
     *
     * Add any actions enabled on "save" to the form settings for reference.
     *
     * @param array $form_settings
     * @param int|string $form_id
     *
     * @return array
     */
    public function localize_save_actions( $form_settings, $form_id )
    {
        if( $preview = get_user_option( 'nf_form_preview_' . $form_id ) ){
            $actions = $preview[ 'actions' ];
        } else {
            $actions = Ninja_Forms()->form( $form_id )->get_actions();
        }

        $form_settings[ 'save_progress_actions' ] = array();
        foreach( $actions as $action ){

            if( $preview ) {
                if( ! isset( $action[ 'id' ] ) ) continue;
                $action_id = $action[ 'id' ];
                $active_save = ( isset( $action[ 'settings' ][ 'active_save' ] ) ) ? $action[ 'settings' ][ 'active_save' ] : 0;
                $form_settings['save_progress_actions'][$action_id]['active'] = $active_save;
            } else {
                $action_id = $action->get_id();
                $form_settings['save_progress_actions'][$action_id]['active'] = $action->get_setting('active_save', 0);
            }
        }

        return $form_settings;
    }
}
