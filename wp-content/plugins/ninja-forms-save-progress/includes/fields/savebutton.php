<?php

final class NF_SaveProgress_SaveButton extends NF_Fields_Submit
{
    protected $_name = 'save';

    protected $_type = 'save';

    protected $_icon = 'floppy-o';

    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __( 'Save', 'ninja-forms' );

        if( ! is_array( $this->_templates ) ){
            $this->_templates = array( $this->_templates );
        }
        $this->_templates[] = 'hidden';

        add_filter( 'ninja_forms_display_fields', array( $this, 'display_fields' ) );

        // hook into before_response action to check for errors
        add_action( 'ninja_forms_before_response', array( $this, 'check_errors' ) );
    }

    public function process( $field, $data )
    {
        // If the saveProgress data is not set, then this is a regular submission; delete the old save.
        if( ! isset( $data[ 'extra' ][ 'saveProgress' ] ) ){
//            NF_SaveProgress()->saves()->delete_by_id( $field[ 'save_id' ] );
            return $data;
        }

        // If the user is not authenticated, then do not save.
        $user_id = get_current_user_id();
        if( ! $user_id ) return $data;

        // If the save has already been created, then do not save again (Debounce).
        static $saved;
        if( isset( $saved ) ) return $data;

        // The Saved Progress Data is passed as Extra Data of the Form / Submission.
        $save_data = $data[ 'extra' ][ 'saveProgress' ];

        // Update or Create a Save.
        if( isset( $field[ 'save_id' ] ) && $field[ 'save_id' ] ) {
            $saved = NF_SaveProgress()->saves()->update_by_id( $field[ 'save_id' ], apply_filters('nf_save_progress_update_save', array(
                'fields' => json_encode( $save_data )
            ), $field, $data ) );
            do_action( 'ninja-forms-save-progress-save-updated', $field[ 'save_id' ], $data['form_id'], $save_data );
        } else {
            $saved = NF_SaveProgress()->saves()->insert( apply_filters('nf_save_progress_insert_save', array(
                'user_id' => $user_id,
                'form_id' => $data['form_id'],
                'fields' => json_encode( $save_data )
            ), $field, $data ) );
            do_action( 'ninja-forms-save-progress-save-created', $saved, $data['form_id'], $save_data );
        }

        // Always return the passed $data.
        return $data;
    }

    public function display_fields( $fields )
    {
        if( 0 == get_current_user_id() ){
            // Hide "Save Button" Fields if the user is not authenticated.
            foreach( $fields as $key => $settings ){
                if( 'save' != $settings[ 'type' ] ) continue;
                $fields[ $key ][ 'element_templates' ] = array( 'hidden' );
            }
        }
        return $fields;
    }

	/**
	 * Check for errors. If there are some do not delete the saved data when
	 * responding with the errors
	 *
	 * @param $data
	 */
    public function check_errors( $data ) {
        // If $data isn't an array...
        // Exit early.
        if ( ! is_array( $data ) ) return false;
        if( ! isset( $data[ 'extra' ][ 'saveProgress' ] ) ) {
            foreach( $data['fields'] as $field ) {
                if( 'save' === $field['settings'][ 'type' ] ) {
                    if( 0 === count( $data['errors' ] ) ) {
                        NF_SaveProgress()->saves()->delete_by_id( $field['save_id'] );
                    }
                    break;
                }
            }
        }
    }
}
