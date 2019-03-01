<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress_Controller_REST
{
    public function __construct()
    {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes()
    {
        register_rest_route( 'ninja-forms-save-progress/v1', '/save', array(
            'methods' => 'POST',
            'callback' => array( $this, 'save_form' )
        ) );

        register_rest_route( 'ninja-forms-save-progress/v1', '/saves/(?P<form_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_form_saves' )
        ) );
    }

    /*
    |--------------------------------------------------------------------------
    | Route Callbacks
    |--------------------------------------------------------------------------
    */

    public function save_form( WP_REST_Request $request )
    {
        $user_id = get_current_user_id();

        $data = array(
            'form_id' => $request->get_param( 'form_id' ),
            'fields'  => $request->get_param( 'fields' ),
            'updated' => time()
        );

        $save_id = $request->get_param( 'save_id' );

        if( $save_id ){
            global $wpdb;
            $query = "SELECT `meta_value` FROM $wpdb->usermeta WHERE `umeta_id` = %d";
            $previous = $wpdb->get_var( $wpdb->prepare( $query , $save_id ) );
        }

        if( $save_id && isset( $previous ) ){
            return update_user_meta( $user_id, 'form_save_' . $data[ 'form_id' ], json_encode( $data ), $previous );
        }

        return add_user_meta( $user_id, 'form_save_' . $data[ 'form_id' ], json_encode( $data ) );
    }

    public function get_form_saves( $data )
    {
        $user_id = get_current_user_id();
        $form_id = absint( $data[ 'form_id' ] );

        $saves = NF_SaveProgress()->saves()->get( apply_filters('nf_save_progress_get_form_saves_where', compact( 'user_id', 'form_id' ), $user_id, $form_id, $data ) );

        /**
         * @param array $saves {
         *     @param array $save
         * }
         */
        $saves = apply_filters( 'nf_save_progress_get_form_saves', $saves, $user_id, $form_id, $data );

        return array( 'saves' => array_values( $saves ) );
    }
}
