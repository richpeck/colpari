<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Autocomplete Posts, Pages, Post types
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSFramework_Option_autocomplete extends CSFramework_Options {

  public function __construct( $field, $value = '', $unique = '' ) {
    parent::__construct( $field, $value, $unique );
  }

  public function output() {

    $value = $this->element_value();
    $query = $values = '';

    if ( isset( $this->field['query_args'] ) ) {
        $query = json_encode(array(
            'action'        => 'codevz_autocomplete', 
            'query_args'    => $this->field['query_args'],
            'elm_name'      => $this->element_name()
        ));
    }

    if ( ! empty( $value ) ) {
        if ( is_array( $value ) ) {
          foreach ( $value as $id ) {
            $values .= '<div id="' . $id . '"><input name="' . $this->element_name() . '[]" value="' . $id . '" /><span> ' . get_the_title( $id ) . '<i class="fa fa-remove"></i></span></div>';
          }
        } else {
            $values .= '<div id="' . $value . '"><input name="' . $this->element_name() . '" value="' . $value . '" /><span> ' . get_the_title( $value ) . '<i class="fa fa-remove"></i></span></div>';
      }
    }

    echo $this->element_before();
    echo '<div class="cs-autocomplete" data-query=\'' . $query . '\'>';
    echo '<input type="text"'. $this->element_class() . $this->element_attributes() .' />';
    echo '<i class="fa fa-codevz"></i>';
    echo '<div class="ajax_items"></div>';
    echo '<div class="selected_items">' . $values . '</div>';
    echo '</div>';
    echo $this->element_after();

  }

}

add_action( 'wp_ajax_codevz_autocomplete', 'codevz_autocomplete' );
function codevz_autocomplete() {

    if ( empty( $_GET['query_args'] ) || empty( $_GET['s'] ) ) {
        echo '<b>Query is empty ...</b>';
        die();
    }

    $out = array();
    ob_start();

    $query = new WP_Query( wp_parse_args( $_GET['query_args'], array( 's' => $_GET['s'] ) ) );
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<div data-id="' . get_the_ID() . '">' . get_the_title() . '</div>';
        }
    } else {
        echo '<b>Not found</b>';
    }

    echo ob_get_clean();
    wp_reset_postdata();
    die();
}