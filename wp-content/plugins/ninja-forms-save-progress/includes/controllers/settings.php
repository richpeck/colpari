<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_SaveUserProgress_Settings
 */
final class NF_SaveProgress_Controller_Settings
{
    public function __construct()
    {
        add_action( 'ninja_forms_action_settings', array( $this, 'action_settings' ), 10, 1 );
        add_action( 'ninja_forms_builder_templates', array( $this, 'builder_templates' ), 10, 1 );
        add_filter( 'ninja_forms_actions_settings_all', array( $this, 'action_settings_all' ), 10, 1 );
        add_filter( 'ninja_forms_from_settings_types', array( $this, 'form_settings_types' ), 10, 1 );
        add_filter( 'ninja_forms_localize_forms_settings', array( $this, 'form_settings' ), 10, 1 );
    }

    public function action_settings( $settings )
    {
        $settings[ 'active_save' ] =  array(
            'name' => 'active_save',
            'type' => 'toggle',
            // 'group' => 'advanced',
            'label' => __( 'Active on Save', 'ninja-forms-save-progress' ),
            'width' => 'full',
        );
        return $settings;
    }

    public function action_settings_all( $settings )
    {
        $settings[] = 'active_save';
        return $settings;
    }

    public function form_settings_types( $types )
    {
        $types[ 'save_progress' ] = array(
            'id'       => 'save_progress',
            'nicename' => __( 'Save Progress', 'ninja-forms-save-progress' ),
        );
        return $types;
    }

    public function form_settings( $settings )
    {
        $settings[ 'save_progress' ] = array(

            'save_progress_passive_mode' => array(
                'name'          => 'save_progress_passive_mode',
                'type'          => 'toggle',
                'label'         => __( 'Enable Local Browser Storage', 'ninja-forms-save-progress' ),
                'width'         => 'full',
                'group'        => 'primary',
                'help'          => __( 'Saves field data to the visitor\'s browser using local storage (using a cookie), without needing to log-in.', 'ninja-forms-save-progress'),
            ),

            'save_progress_allow_multiple' => array(
                'name' => 'save_progress_allow_multiple',
                'type' => 'toggle',
                'label' => __( 'Allow multiple saves (Logged-In Only)', 'ninja-forms-save-progress' ),
                'width' => 'full',
                'group' => 'primary',
                'help' => __( 'Allow visitors to save multiple versions of their progress, if they are logged in.', 'ninja-forms-save-progress' ),
                'value' => false
            ),

            'save_progress_table_legend' => array(
                'name' => 'save_progress_table_legend',
                'type' => 'textbox',
                'label' => __( 'Saves Table Title', 'ninja-forms-save-progress' ),
                'width' => 'full',
                'group' => 'primary',
                'value' => __( 'Load saved progress', 'ninja-forms-save-progress' ),
                'deps' => array(
                    'save_progress_allow_multiple' => true
                )
            ),

            'save_progress_table_columns' => array(
                'name' => 'save_progress_table_columns',
                'type' => 'option-repeater',
                'label' => __( 'Save Table Columns' ) . ' <a href="#" class="nf-add-new">' . __( 'Add New' ) . '</a>',
                'width' => 'full',
                'group' => 'primary',
                'columns'           => array(
                    'field'          => array(
                        'header'    => __( 'Field Key', 'ninja-forms-save-progress' ),
                        'default'   => '',
                    ),
                ),
                // TODO: Deps not working with option-repeater settings.
//                'deps' => array(
//                    'save_progress_allow_multiple' => true
//                ),

                /* Optional */
                'value' => array(
                    array( 'label'  => 'Column Title', 'field' => '{field}', 'order' => 0 ),
                ),

                /* Optional */
                'tmpl_row' => 'tmpl-nf-save-progress-table-columns-repeater-row'
            )

        );
        return $settings;
    }

    public function builder_templates()
    {
        echo NF_SaveProgress()->template( 'admin/table-columns-repeater-row.html.php' );
    }
}
