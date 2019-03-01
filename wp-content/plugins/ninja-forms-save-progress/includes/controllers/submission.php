<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SaveProgress_Controller_Submission
{
    public function __construct()
    {
        add_filter( 'ninja_forms_submit_data', array( $this, 'submit_data' ) );
        add_filter( 'ninja_forms_validate_fields', array( $this, 'maybe_validate_fields' ), 10, 2 );
    }

    public function submit_data( $form_data )
    {
        if( isset( $form_data[ 'extra' ][ 'saveProgress' ] ) && $form_data[ 'extra' ][ 'saveProgress' ] ){
            add_filter( 'ninja_forms_submission_actions', array( $this, 'parse_actions' ), 10, 2 );
            add_filter( 'ninja_forms_submission_actions_preview', array( $this, 'parse_actions' ), 10, 2 );
        }

        // Nothing to see here. Don't modify any data. Just listening.
        return $form_data;
    }

    public function parse_actions( $actions, $form_data )
    {
        foreach( $actions as $i => $action ){

            if( isset( $action[ 'settings' ][ 'active_save' ] ) ){
                $active = $action[ 'settings' ][ 'active_save' ];
            } else {
                $active = false;
            }

            // Copy 'active_save' setting over to 'active' for processing.
            $actions[ $i ][ 'settings' ][ 'active' ] = $active;
        }
        return $actions;
    }

    public function maybe_validate_fields( $maybe_validate_fields, $form_data )
    {
        if( isset( $form_data[ 'extra' ][ 'saveProgress' ] ) && $form_data[ 'extra' ][ 'saveProgress' ] ){
            $maybe_validate_fields = false;
        }
        return $maybe_validate_fields;
    }
}
