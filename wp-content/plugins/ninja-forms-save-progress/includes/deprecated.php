<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 |--------------------------------------------------------------------------
 | Table Row Class Filter
 |--------------------------------------------------------------------------
 */
function nf_sp_filter_tr( $classes, $class, $sub_id ) {
    global $pagenow, $typenow;
    if ( $pagenow == 'edit.php' && $typenow == 'nf_sub' ) {
        if ( Ninja_Forms()->form()->get_sub( $sub_id )->get_extra_value( '_action' ) == 'save' ) {
            $classes[] = 'nf-sub-saved';
        }
    }
    return $classes;
}
add_filter( 'post_class', 'nf_sp_filter_tr', 10, 3 );

/*
 |--------------------------------------------------------------------------
 | Table Styles
 |--------------------------------------------------------------------------
 */

function nf_sp_nf_sub_styles( $hook ) {
    if( $hook != 'edit.php' ) return;
    wp_enqueue_style( 'nf_sp_deprecated', NF_SaveProgress()->url( 'assets/styles/deprecated/admin.css' ) );
}
add_action( 'admin_enqueue_scripts', 'nf_sp_nf_sub_styles' );

/*
 |--------------------------------------------------------------------------
 | CPT Metabox
 |--------------------------------------------------------------------------
 */
function nf_sp_nf_sub_meta_boxes( $post_type, $post ) {
    // Check if this is a deprecated "save".
    if ( Ninja_Forms()->form()->get_sub( $post->ID )->get_extra_value( '_action' ) != 'save' ) return;

    add_meta_box(
        'nf-sp-sub',
        __( 'Convert to Published' ),
        'nf_sp_nf_sub_meta_box',
        'nf_sub', // only on submissions...
        'side', // in the sidebar...
        'high', // above the info box...
        compact( 'post' ) // pass the $post argument.
    );
}
add_action( 'add_meta_boxes', 'nf_sp_nf_sub_meta_boxes', 10, 2 );

function nf_sp_nf_sub_meta_box( $post ) {
    echo NF_SaveProgress()->template( 'deprecated/cpt-meta-box.html.php', compact( 'post' ) );
}

/*
 |--------------------------------------------------------------------------
 | Convert Save to Submission
 |--------------------------------------------------------------------------
 */
function nf_sp_convert_save_to_submission(){
    if( ! isset( $_POST[ 'nf_sp_convert_save_to_sub' ] ) ) return;
    if( 1 != $_POST[ 'nf_sp_convert_save_to_sub' ] ) return;

    $save_id = absint( $_POST[ 'post_ID'] );

    $sub = Ninja_Forms()->form()->get_sub( $save_id );
    $sub->update_extra_value( '_action', '' );
    $sub->save();
}
add_action( 'init', 'nf_sp_convert_save_to_submission' );

/*
 |--------------------------------------------------------------------------
 | Date Submitted
 |--------------------------------------------------------------------------
 */
function nf_sp_edit_sub_date_submitted( $column, $sub_id ) {
    if( 'nf_sub' != get_post_type() ) return;
    if( 'sub_date' != $column ) return;

    $sub = Ninja_Forms()->form()->get_sub( $sub_id );

    if ( 'save' != $sub->get_extra_value( '_action' ) ) return;
    echo '</br>Saved - Not Submitted';
}
add_action( 'manage_posts_custom_column', 'nf_sp_edit_sub_date_submitted', 11, 2 );
