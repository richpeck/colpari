<?php if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'WP_List_Table' ) ){
    if( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    } else {
        //TODO: Load local wp-list-table-class.php
    }
}

final class NF_SaveProgress_Admin_SavesTable extends WP_List_Table
{
    private $progress_data = array();

    /**
     * Class constructor
     */
    public function __construct() {

        parent::__construct( array(
            'singular' => __( 'Save', 'ninja-forms-progress' ), //singular name of the listed records
            'plural'   => __( 'Saves', 'ninja-forms-progress' ), //plural name of the listed records
            'ajax'     => false //should this table support ajax?
        ) );

        $this->_column_headers = array(
            $this->get_columns(),
            $this->get_hidden_columns(),
            $this->get_sortable_columns()
        );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    public function admin_enqueue_scripts()
    {
        if( ! isset( $_GET[ 'page' ] ) || 'ninja-forms-saves' != $_GET[ 'page' ] ) return;
        wp_register_script('nf-save-progress--admin', NF_SaveProgress()->url('client/admin/main.js'), array( 'jquery', 'underscore' ), NF_SaveProgress()->version() );
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $form_id = absint( $_GET[ 'form_id' ] );
        $saves = NF_SaveProgress()->saves()->get( compact( 'form_id' ) );
        $data = array();
        $this->progress_data = array();
        foreach( $saves as $save ){
            $data[] = array(
                'id' => $save[ 'save_id' ],
                'user_id' => $save[ 'user_id' ],
                'updated' => $save[ 'updated' ]
            );
            $this->progress_data[ $save['save_id'] ] = $save[ 'fields' ];
        }

        usort( $data, array( $this, 'sort_data' ) );
        $this->items = $this->paginate( $data );

        $field_data = array();
        foreach( Ninja_Forms()->form( $form_id )->get_fields() as $field ){
            $field_data[ $field->get_id() ] = $field->get_settings();
        }

        wp_localize_script('nf-save-progress--admin', 'nfSaveProgress', array(
            'progress' => $this->get_progress_data(),
            'fields'   => $field_data,
            'url'   => admin_url( 'admin.php?page=ninja-forms-saves' )
        ));
        wp_enqueue_script('nf-save-progress--admin');
    }

    /**
     * The message to be displayed when there are no items.
     */
    public function no_items() {
        _e( 'No saves found.', 'ninja-forms-save-progress' );
    }

    /**
     * @param string $which Location of 'top' or 'bottom'
     */
    protected function extra_tablenav( $which )
    {
        // Forms Data
        $forms = Ninja_Forms()->form()->get_forms();
        $form_id = absint( $_GET[ 'form_id' ] );

        // Users Data
        $users = get_users();
        $user_id = ( isset( $_GET[ 'user_id' ] ) ) ? absint( $_GET[ 'user_id' ] ) : false;

        // Display the Extra Table Nav Template
        echo NF_SaveProgress()->template( 'admin/table/extra-tablenav.html.php', compact( 'forms', 'form_id', 'users', 'user_id' ) );
    }

    public function get_progress_data()
    {
        return $this->progress_data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'user_id'   => __( 'User', 'ninja-forms-save-progress' ),
            'updated'   => __( 'Updated', 'ninja-forms-save-progress' )
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array(
            'user_id' => array( __( 'user_id', 'ninja-forms-save-progress' ), true ),
            'updated' => array( __( 'updated', 'ninja-forms-save-progress' ), true )
        );
    }

    /**
     |--------------------------------------------------------------------------
     | Column Display
     |--------------------------------------------------------------------------
     | WP_List_Table follows a convention based method calling.
     |      1. columns_{$name}
     |      2. columns_default
     */

    /**
     * Display the Checkbox (cb) Column for each item.
     * @param $item
     * @return string
     */
    public function column_cb( $item )
    {
        return sprintf(
            '<input type="checkbox" name="bulk[]" value="%s" />', $item['id']
        );
    }

    public function column_user_id( $item )
    {
        $save_id = $item[ 'id' ];
        $updated = $item[ 'updated' ];
        $base_url = add_query_arg( array(
            'page' => 'ninja-forms-saves',
            'form_id' => absint( $_GET[ 'form_id' ] ),
            'bulk'    => $save_id
        ), admin_url( 'admin.php' ) );

        // If the User ID is set, then pass it along for redirects.
        if( isset( $_GET[ 'user_id' ] ) ){
            $base_url = add_query_arg( 'user_id', absint( $_GET[ 'user_id' ] ), $base_url );
        }

        $convert_url = add_query_arg( 'action', 'convert', $base_url );;
        $delete_url = add_query_arg( 'action', 'delete', $base_url );
        $row_actions = NF_SaveProgress()->template( 'admin/table/row-actions.html.php', compact( 'save_id', 'updated', 'convert_url', 'delete_url' ) );

        $avatar_url = get_avatar_url( $item[ 'user_id' ], array( 'size' => 45 ) );
        $user_link = $this->get_user_link( $item[ 'user_id' ] );
        echo NF_SaveProgress()->template( 'admin/table/column-user-id.html.php', compact( 'avatar_url', 'user_link', 'row_actions' ) );
    }

    public function column_updated( $item )
    {
        echo $item[ 'updated' ];
    }

    /**
     * Default column display.
     *
     * @param  Array $item
     * @param  String $column_name - Current column name
     */
    public function column_default( $item, $column_name )
    {
        echo $item[ $column_name ];
    }

    /*
     |--------------------------------------------------------------------------
     | Processing
     |--------------------------------------------------------------------------
     */

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        return array(
            'bulk-delete' => __( 'Delete', 'ninja-forms-save-progress' ),
            'bulk-convert' => __( 'Convert to Submission', 'ninja-forms-save-progress' )
        );
    }

    public static function process_bulk_action()
    {
        // Check that we are on the right page.
        if( ! isset( $_GET[ 'page' ] ) || 'ninja-forms-saves' != $_GET[ 'page' ] ) return;

        // DELETE
        if ( isset( $_REQUEST[ 'action' ] ) && 'delete' === $_REQUEST[ 'action' ] ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'nf_delete_form' ) ) {
                die( __( 'Go get a life, script kiddies', 'ninja-forms' ) );
            }
            else {
                self::delete_item( absint( $_GET['id'] ) );
            }

            wp_redirect( admin_url( 'admin.php?page=ninja-forms' ) );
            exit;
        }

        // BULK DELETE
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
            || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'bulk-forms' ) ) {
                die( __( 'Go get a life, script kiddies', 'ninja-forms' ) );
            }

            if( isset( $_POST[ 'bulk-delete' ] ) ) {
                $delete_ids = esc_sql($_POST['bulk-delete']);

                // loop over the array of record IDs and delete them
                foreach ($delete_ids as $id) {

                    self::delete_item(absint($id));
                }
            }

            wp_redirect( admin_url( 'admin.php?page=ninja-forms' ) );
            exit;
        }
    }


    /**
     * Delete a single item.
     * @param $id
     */
    public static function delete_item( $id )
    {
        NF_SaveProgress()->saves()->delete_by_id( $id );
    }

    /*
     |--------------------------------------------------------------------------
     | Helper Methods
     |--------------------------------------------------------------------------
     */

    /**
     * Format an edit link for a user by ID.
     * @param $user_id
     * @return string
     */
    private function get_user_link( $user_id )
    {
        $user_info  = get_userdata( $user_id );
        $user_login = $user_info->display_name;
        $user_link  = get_edit_user_link( $user_id );
        return sprintf( '<a href="%s">%s</a>', $user_link, $user_login );
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'id';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }

    /**
     * Paginate data for the table.
     *
     * @param $data
     * @param int $per_page
     * @return array
     */
    private function paginate( $data, $per_page = 20 )
    {
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        return array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
    }

} // END CLASS NF_SaveProgress_Admin_SavesTable
