<?php

if (!defined('ABSPATH')) die;

// blocks base class
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/editor-block.php');

// blocks
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/tip/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/info/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/warning/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/faq/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/topics/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/topic/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/search/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/related/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/article-content/index.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/modules/guestpost/index.php');

class KST_Block_Editor_Integration {

    private $blocks = array();

    public function __construct() {
        $this->register_category();
        $this->register_blocks();
        $this->enqueue_assets();
    }

    public function register_category() {
        add_filter( 'block_categories', function( $categories, $post ) {
            return array_merge(
                $categories,
                array(
                    array(
                        'slug' => 'minervakb',
                        'title' => __( 'MinervaKB', 'minerva-kb' ),
                    ),
                )
            );
        }, 10, 2 );
    }

    public function register_blocks() {
        $this->blocks = array(
            new KST_Info_Block(),
            new KST_Tip_Block(),
            new KST_Warning_Block(),
            new KST_FAQ_Block(),
            new KST_Topics_Block(),
            new KST_Topic_Block(),
            new KST_Search_Block(),
            new KST_Related_Block(),
            new KST_ArticleContent_Block(),
            new KST_GuestPost_Block(),
        );
    }

    public function enqueue_assets() {
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
    }

    public function enqueue_block_editor_assets() {
        $block_path = '/assets/js/editor.blocks.js';
        $block_vendor_path = '/assets/js/editor.blocks.vendor.js';
        $style_client_path = '/assets/css/dist/minerva-kb.css';

        wp_enqueue_script(
            MINERVA_KB_PLUGIN_PREFIX . 'blocks-vendor-js',
            MINERVA_KB_PLUGIN_URL . $block_vendor_path,
            array(),
            filemtime( MINERVA_KB_PLUGIN_DIR . $block_vendor_path )
        );

        // Enqueue the bundled block JS file
        wp_enqueue_script(
            MINERVA_KB_PLUGIN_PREFIX . 'blocks-js',
            MINERVA_KB_PLUGIN_URL . $block_path,
            array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor', MINERVA_KB_PLUGIN_PREFIX . 'blocks-vendor-js' ),
            filemtime( MINERVA_KB_PLUGIN_DIR . $block_path )
        );

        wp_localize_script(MINERVA_KB_PLUGIN_PREFIX . 'blocks-js', 'MinervaKBBlocksInfo', $this->get_blocks_data());

        if (MKB_Options::option( 'typography_on' )) {
            $all_fonts = mkb_get_all_fonts();
            $google_fonts = $all_fonts['GOOGLE'];
            $google_fonts = $google_fonts["fonts"];
            $selected_family = MKB_Options::option( 'style_font' );
            $selected_weights = MKB_Options::option( 'style_font_gf_weights' );
            $selected_languages = MKB_Options::option( 'style_font_gf_languages' );

            if (isset($google_fonts[$selected_family])) {
                wp_enqueue_style( 'minerva-kb-font/css', mkb_get_google_font_url(
                    $selected_family, $selected_weights, $selected_languages
                ), false, null );
            }
        }

        wp_enqueue_style(
            MINERVA_KB_PLUGIN_PREFIX . 'blocks-editor-client-css',
            MINERVA_KB_PLUGIN_URL . $style_client_path,
            array( 'wp-edit-blocks' ),
            filemtime( MINERVA_KB_PLUGIN_DIR . $style_client_path )
        );

        global $minerva_kb;
        // dynamic styles
        wp_add_inline_style( MINERVA_KB_PLUGIN_PREFIX . 'blocks-editor-client-css', $minerva_kb->inline_styles->get_css());
    }

    /**
     * JS bootstrap data for blocks
     */
    public function get_blocks_data() {
        return array_reduce($this->blocks, function($acc, $block) {
            $acc[$block->get_ID()] = $block->get_block_options_info();
            return $acc;
        }, array());
    }
}

new KST_Block_Editor_Integration();
