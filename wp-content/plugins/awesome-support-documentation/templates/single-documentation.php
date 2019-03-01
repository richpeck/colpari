<?php
global $post;
setup_postdata( $post );
$customization = wpas_doc_get_customization_options();
$products = wpas_doc_get_products();
$topic_ids = array ();
$tags = wpas_doc_get_tags_by_post( get_the_ID() );
$categories = wpas_doc_get_categories_by_post( get_the_ID() );
$menu_query = array (
    'theme_location'    => 'wpas-docs-top-menu',
    'container_class'   => 'DocSite-globalNav ansibleNav',
    'menu_id'           => 'wpas-docs-top-wp-menu',
);
$location = array ();
?>
<!DOCTYPE html>
<!--[if IE 8]><html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation &mdash; Awesome Support</title>
    <?php wp_head(); ?>
    <style>
    .ansibleNav, .DocSite-nav {
        background: <?php echo $customization['topbar-color']; ?>;
    }
    .wy-body-for-nav, .wy-nav-side {
        background: <?php echo $customization['sidebar-color'] ?> !important;
    }
    .DocSiteProduct-logoText {
        color: <?php echo $customization['product-text-color']; ?>;
        font-family: <?php echo $customization['product-font']['font-family']; ?>;
        font-size: <?php echo $customization['product-font']['font-size']; ?>;
    }
    .dx-wpas-docs .DocSiteProduct-header {
        background-color: <?php echo $customization['product-bg-color']; ?>;
        border-color: <?php echo $customization['product-bg-color']; ?>;
    }
    .dx-wpas-docs .DocSiteProduct-header:hover {
        background-color: <?php echo $customization['product-bg-color']; ?>;
        border-color: <?php echo $customization['product-bg-color']; ?>;
    }
    .dx-wpas-docs .toctree-l1-chapter {
        background-color: <?php echo $customization['chapter-bg-color']; ?>;
        color: <?php echo $customization['chapter-text-color']; ?>;
        font-family: <?php echo $customization['chapter-font']['font-family']; ?>;
        font-size: <?php echo $customization['chapter-font']['font-size']; ?>;
    }
    .dx-wpas-docs .toctree-l1-version {
        font-family: <?php echo $customization['version-font']['font-family']; ?>;
        font-size: <?php echo $customization['version-font']['font-size']; ?>;
    }
    .dx-wpas-docs .toctree-toggled {
        background-color: <?php echo $customization['menu-active-color']; ?>;
        color: #404040;
    }
    .dx-wpas-docs .toctree-l2 a {
        font-family: <?php echo $customization['version-font']['font-family']; ?>;
        font-size: <?php echo $customization['version-font']['font-size']; ?>;
    }
    .dx-wpas-docs #wpas-docs-top-wp-menu li a {
        font-family: <?php echo $customization['top-menu-font']['font-family']; ?>;
        font-size: <?php echo $customization['top-menu-font']['font-size']; ?>;
    }
    ul, ol {
        margin: 0;
    }
    li > ul, li > ol {
        margin-left: 0;
    }
    </style>
</head>

<body <?php body_class(); ?>>
    <?php if ( has_nav_menu( $menu_query['theme_location'] ) ):
        wp_nav_menu( $menu_query );
    endif; ?>

    <a class="DocSite-nav" href="<?php echo $customization['title-link']; ?>">
        <?php if ( isset($customization['logo'][0] ) ) : ?>
            <img class="DocSiteNav-logo" src="<?php echo $customization['logo'][0]; ?>" alt="AS Docs Logo">
        <?php endif; ?>
        <div class="DocSiteNav-title">
            <?php echo $customization['name']; ?>
        </div>
    </a>
    <div class="mobile-nav">
      <i class="mobile-nav-toggle fa fa-bars" aria-hidden="true"></i>
    </div>
    <div class="wy-grid-for-nav">
        <nav data-toggle="wy-nav-shift" class="wy-nav-side">

            <!-- DISPLAY PRODUCTS IN SIDEBAR -->
            <?php if ( !is_null( $products ) ) : ?>
                <?php foreach( $products as $product ):
                    if ( !is_null( wpas_doc_get_chapter_or_version_by_product( 'chapter', $product->term_id ) ) ): ?>
                        <div style="height=80px;margin:'auto auto auto auto';background-color: <?php echo $customization['product-bg-color'];?>;">
                            <div class="DocSiteProduct-header DocSiteProduct-header--core">
                                <div class="DocSiteProduct-productName">
                                    <div class="DocSiteProduct-logoText">
                                        <?php echo $product->name; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="menu-id-<?php echo $product->term_id; ?>" class="wy-menu wy-menu-vertical" data-spy="affix">
                            <ul>
                                <!-- DISPLAY CURRENT VERSION IN SIDEBAR -->
                                <?php if ( $customization['versions'] ): ?>
                                    <?php $topics = wpas_doc_get_topics_with_no_chapters_or_versions( 'version', $product->term_id ); ?>
                                    <?php if ( !is_null( $topics ) ): ?>
                                        <li class="toctree-l1" style="background-color: <?php echo $customization['version-bg-color']; ?>;">
                                            <a class="reference internal toctree-l1-version" href="javascript:;" style="color: <?php echo $customization['version-text-color']; ?>;">
                                                <?php echo __( 'Current Version', 'wpas-documentation' ); ?>
                                            </a>
                                            <ul class="toctree-l1-toggle">
                                                <?php foreach( $topics as $topic ): ?>
                                                    <li class="toctree-l2" style="background: <?php echo $customization['topic-bg-color']; ?>;">
                                                        <a class="reference internal" href="<?php echo get_permalink($topic) ?>" style="color: <?php echo $customization['topic-text-color']; ?>;">
                                                            <?php echo $topic->post_title; ?>
                                                        </a>
                                                    </li>
                                                    <?php $topic_ids[] = $topic->ID; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                <!-- END OF DISPLAY CURRENT VERSION IN SIDEBAR -->

                                <!-- DISPLAY VERSIONS IN SIDEBAR -->
                                    <?php $versions = ( isset( $product->term_id ) ) ? wpas_doc_get_chapter_or_version_by_product( 'version', $product->term_id ) : null; ?>
                                    <?php if ( !is_null( $versions ) ): ?>
                                        <?php foreach( $versions as $version ): ?>
                                            <li class="toctree-l1" style="background-color: <?php echo $customization['version-bg-color']; ?>;">
                                                <a class="reference internal toctree-l1-version" href="javascript:;" style="color: <?php echo $customization['version-text-color']; ?>;">
                                                    <?php echo $version->name; ?>
                                                </a>
                                                <ul class="toctree-l1-toggle">
                                                    <?php $topics = wpas_doc_get_topics_by_taxonomy( 'version', $version->term_id, $product->term_id ); ?>
                                                    <?php if ( !is_null($topics) ): ?>
                                                        <?php foreach( $topics as $topic ): ?>
                                                            <li class="toctree-l2" style="background: <?php echo $customization['topic-bg-color']; ?>;">
                                                                <a class="reference internal" href="<?php echo get_permalink($topic) ?>" style="color: <?php echo $customization['topic-text-color']; ?>;">
                                                                    <?php echo $topic->post_title; ?>
                                                                </a>
                                                            </li>
                                                            <?php $topic_ids[] = $topic->ID; ?>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <!-- END OF DISPLAY VERSIONS IN SIDEBAR -->

                                <!-- DISPLAY CHAPTERS IN SIDEBAR -->
                                <?php $chapters = ( isset( $product->term_id ) ) ? wpas_doc_get_chapter_or_version_by_product( 'chapter', $product->term_id ) : null; ?>
                                <?php if ( !is_null($chapters) ): ?>
                                    <?php foreach( $chapters as $chapter ): ?>
                                    <li class="toctree-l1">
                                        <a class="reference internal toctree-l1-chapter" href="javascript:;">
                                            <?php echo $chapter->name; ?>
                                        </a>
                                        <ul class="toctree-l1-toggle">
                                            <?php $topics = wpas_doc_get_topics_by_taxonomy( 'chapter', $chapter->term_id, $product->term_id ); ?>
                                            <?php if ( !is_null($topics) ): ?>
                                                <?php foreach( $topics as $topic ): ?>
                                                    <li class="toctree-l2" style="background: <?php echo $customization['topic-bg-color']; ?>;">
                                                        <a class="reference internal" href="<?php echo get_permalink($topic) ?>" style="color: <?php echo $customization['topic-text-color']; ?>;">
                                                            <?php echo $topic->post_title; ?>
                                                        </a>
                                                    </li>
                                                    <?php $topic_ids[] = $topic->ID; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <!-- END OF DISPLAY CHAPTERS IN SIDEBAR-->

                                <!-- DISPLAY CURRENT CHAPTERS IN SIDEBAR -->
                                <?php $topics = wpas_doc_get_topics_with_no_chapters_or_versions( 'chapter', $product->term_id ); ?>
                                <?php if ( !is_null( $topics ) ): ?>
                                    <li class="toctree-l1">
                                        <a class="reference internal toctree-l1-chapter" href="javascript:;">
                                            <?php echo __( 'Current Chapter', 'wpas-documentation' ); ?>
                                        </a>
                                        <ul class="toctree-l1-toggle">
                                            <?php foreach( $topics as $topic ): ?>
                                                <li class="toctree-l2" style="background: <?php echo $customization['topic-bg-color']; ?>;">
                                                    <a class="reference internal" href="<?php echo get_permalink($topic) ?>" style="color: <?php echo $customization['topic-text-color']; ?>;">
                                                        <?php echo $topic->post_title; ?>
                                                    </a>
                                                </li>
                                                <?php $topic_ids[] = $topic->ID; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                                <!-- END OF DISPLAY CURRENT CHAPTERS IN SIDEBAR -->
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- END OF DISPLAY PRODUCTS IN SIDEBAR -->
            &nbsp;
        </nav>

        <section data-toggle="wy-nav-shift" class="wy-nav-content-wrap">
            <div class="wy-nav-content">
                <div class="rst-content">
                    <ul class="wy-breadcrumbs">
                        <li><a href="#" class="wpas-docs-breadcrumbs-product"></a>&raquo;</li>
                        <li><a href="#" class="wpas-docs-breadcrumbs-menu"></a>&raquo;</li>
                        <li><a href="#"><?php the_title(); ?></a></li>
                    </ul>
                    <hr/>

                    <div id="page-content">
                        <div class="section" id="ansible-documentation">
                            <h1><?php the_title(); ?></h1>
                            <div class="section" id="about-ansible">
                                <p> <?php the_content(); ?></p>
                                <?php if ( has_term( '','as-doc-section', $post ) ) : ?>
                                    <?php $post_section = get_the_terms( $post, 'as-doc-section' ); ?>
                                    <?php $related_section = wpas_doc_get_section( $post_section[0]->term_id ); ?>
                                    <?php if ( count( $related_section ) > 1 ): ?>
                                    <div class="toctree-wrapper compound" id="an-introduction">
                                        <h3><?php echo __( 'More Topics In ', 'wpas-documentation' ); echo $post_section[0]->name; ?></h3>
                                        <ul>
                                            <?php foreach ( $related_section as $section ) : ?>
                                                <?php if ( get_the_ID() != $section->ID ): ?>
                                                <li class="toctree-l1">
                                                    <a class="reference internal" href="<?php the_permalink( $section->ID ); ?>">
                                                        <?php echo $section->post_title; ?>
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <?php if( ( !empty( $tags ) ) || ( !empty( $categories ) ) ): ?>
                        <!-- BEGIN TAGS AND CATEGORIES -->
                        <div class="admonition seealso">
                            <p class="first admonition-title"> <?php echo __( 'See Also', 'wpas-documentation' ) ?> </p>
                            <dl class="last docutils">
                            <?php if( !empty( $tags ) ):
                                foreach( $tags as $tag ):
                                    if ( $tag->ID != get_the_ID() ): ?>
                                        <dt><a class="reference internal" href="<?php echo get_permalink( $tag ); ?>"><span class="doc"> <?php echo $tag->post_title; ?></span></a></dt>
                                    <?php endif;
                                endforeach;
                            endif;?>
                            <?php if( !empty( $categories ) ):
                                foreach( $categories as $category ):
                                    if ( $category->ID != get_the_ID() ): ?>
                                        <dt><a class="reference internal" href="<?php echo get_permalink( $category ); ?>"><span class="doc"> <?php echo $category->post_title; ?></span></a></dt>
                                    <?php endif;
                                endforeach;
                            endif;?>
                        </div>
                        <!-- END OF TAGS AND CATEGORIES -->
                    <?php endif; ?>

                    <footer>
                        <!-- BEGIN DISPLAY NAVIGATION -->
                        <div class="rst-footer-buttons">
                            <?php $this_index = array_search( get_the_ID(), $topic_ids );
                            $previd = isset($topic_ids[$this_index - 1]) ? $topic_ids[$this_index - 1] : '';
                            $nextid = isset($topic_ids[$this_index + 1]) ? $topic_ids[$this_index + 1] : '';
                            if ( ! empty ( $nextid ) ): ?>
                                <a href="<?php echo get_post_permalink($nextid); ?>" class="btn btn-neutral float-right" title="Next Page" />Next <span class="icon icon-circle-arrow-right"></span></a>
                            <?php endif;
                            if ( ! empty ( $previd ) ): ?>
                                <a href="<?php echo get_post_permalink($previd); ?>" class="btn btn-neutral" title="Previous Page"><span class="icon icon-circle-arrow-left"></span> Previous</a>
                            <?php endif; ?>
                        </div>
                        <!-- END OF NAVIGATION -->
                        <hr/>
                        <p>
                            <?php echo $customization['copyright']; ?>
                        </p>
                    </footer>
                    <?php wp_reset_postdata( $post ); ?>
                    <?php wp_footer(); ?>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
