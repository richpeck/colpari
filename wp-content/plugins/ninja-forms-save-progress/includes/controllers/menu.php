<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress_Controller_Menu
{
    public function __construct()
    {
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_menu', array( $this, 'register_menu' ), 11 ); // After Ninja Forms Menu item.
        add_action( 'manage_posts_extra_tablenav', array( $this, 'nf_sub_hyperlink' ) );
    }

    public function admin_init()
    {
        $this->table = new NF_SaveProgress_Admin_SavesTable();
        $this->bulk_actions();
    }

    public function display(){
        if( ! isset( $_GET[ 'form_id' ] ) ) return;
        $form_id = absint( $_GET[ 'form_id' ] );

        $this->table->prepare_items();

        echo NF_SaveProgress()->template( 'admin/menu-page.html.php', array( 'table' => $this->table, 'form_id' => $form_id ) );
    }

    public function register_menu()
    {
        $parent = ''; // Hide from Admin Menu is not the current page.
        if( isset( $_GET[ 'page' ] ) && 'ninja-forms-saves' == $_GET[ 'page' ] ){
            $parent = 'ninja-forms';
        }
        add_submenu_page(
            $parent,
            'Form Saves',
            'Form Saves',
            apply_filters( 'ninja_forms_submenu_saves_capability', 'manage_options' ),
            'ninja-forms-saves',
            array( $this, 'display' )
        );
    }

    public function nf_sub_hyperlink()
    {
        global $typenow;
        // Bail if we aren't in our submission custom post type.
        if ( $typenow != 'nf_sub' ) return false;

        // Bail if we have not selected a form.
        if( ! isset( $_GET[ 'form_id' ] ) ) return;
        $form_id = absint( $_GET[ 'form_id' ] );
        $url = add_query_arg( array(
            'page' => 'ninja-forms-saves',
            'form_id' => $form_id,
        ), admin_url( 'admin.php' ) );
        echo "<a href='$url' class='button' style='display:inline-block;'>View Saves</a>";
//            echo '<input type="button" name="" class="button" value="View Saves" style="margin-top:3px;">';
    }

    /*
     |--------------------------------------------------------------------------
     | Bulk Actions
     |--------------------------------------------------------------------------
     */

    public function bulk_actions()
    {
        // Check if we are on the Form Saves page.
        if( ! isset( $_GET[ 'page' ] ) || 'ninja-forms-saves' != $_GET[ 'page' ] ) return;

        // Check if a bulk action was selected.
        if( ! isset( $_REQUEST[ 'action' ] ) && ! isset( $_REQUEST[ 'action2' ] ) ) return;

        // Check that saves were selected to be processed.
        if( ! isset( $_REQUEST[ 'bulk' ] ) ) return;

        // Check that a form is specified.
        if( ! isset( $_GET[ 'form_id' ] ) ) return;

        // Request and sanitize the specified action.
        $action = ( isset( $_REQUEST[ 'action' ] ) ) ? $_REQUEST[ 'action' ] : $_REQUEST[ 'action2' ];
        $action = sanitize_title_for_query( $action );

        // Request and sanitize the save IDs. Also, for to an array.
        $saves = ( is_array( $_REQUEST[ 'bulk' ] ) ) ? $_REQUEST[ 'bulk' ] : array( $_REQUEST[ 'bulk' ] );
        $saves = array_map( 'absint', $saves );

        // Get and sanitize the form ID.
        $form_id = absint( $_GET[ 'form_id' ] );

        $api = NF_SaveProgress()->saves();
        switch( $action ){
            case 'delete':
            case 'bulk-delete':
                array_walk( $saves, array( $api, 'delete_by_id' ) );
                break;
            case 'convert':
            case 'bulk-convert':
                array_walk( $saves, array( $api, 'convert_by_id' ), $form_id ); // Pass the form ID to create the sub.
                break;
        }

        $redirect = remove_query_arg( array( 'action', 'bulk' ) );
        wp_redirect( $redirect );
        exit();
    }

}
