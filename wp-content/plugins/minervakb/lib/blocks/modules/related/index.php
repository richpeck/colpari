<?php

if (!defined('ABSPATH')) die;

class KST_Related_Block extends KST_Editor_Block {

    protected $ID = 'related';

    protected $attrs_map = array(
        'ids' => 'ids'
    );

    public function render($attrs) {
        $ids = explode(",", $attrs["ids"]);

        if ($ids && is_array($ids) && !empty($ids)):
            ?>
            <div class="mkb-related-content">
                <div class="mkb-related-content-title"><?php echo esc_html(MKB_Options::option('related_content_label')); ?></div>
                <ul class="mkb-related-content-list">
                    <?php foreach($ids as $id):
                        if ( empty($id) || !is_string( get_post_status( $id )) ) {
                            continue;
                        }
                        ?>
                        <li><a href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php
        endif;
    }

    public function custom_options() {
        return array(
            'ids' => array(
                'id' => 'ids',
                'type' => 'articles_list',
                'label' => __( 'Select related articles', 'minerva-kb' ),
                'default' => ''
            )
        );
    }
}

