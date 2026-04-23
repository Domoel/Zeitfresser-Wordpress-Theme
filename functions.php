<?php
/**
 * Zeitfresser theme functions and definitions.
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'ZEITFRESSER_VERSION' ) ) {
    define( 'ZEITFRESSER_VERSION', '2.3.6' );
}

if ( ! defined( 'DAISY_BLOG_VERSION' ) ) {
    define( 'DAISY_BLOG_VERSION', ZEITFRESSER_VERSION );
}

require get_template_directory() . '/inc/zeitfresser-helpers.php';
require get_template_directory() . '/inc/performance-tools.php';
require get_template_directory() . '/inc/zeitfresser-toc.php';

/**
 * Theme setup.
 *
 * @return void
 */
function zeitfresser_setup() {
    load_theme_textdomain( 'zeitfresser', get_template_directory() . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        )
    );
    add_theme_support( 'align-wide' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );

    register_nav_menus(
        array(
            'menu-1' => esc_html__( 'Primary', 'zeitfresser' ),
        )
    );

    add_editor_style( 'editor-style.css' );
}
add_action( 'after_setup_theme', 'zeitfresser_setup' );

/**
 * Set the content width in pixels.
 *
 * @return void
 */
function zeitfresser_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'zeitfresser_content_width', 640 );
}
add_action( 'after_setup_theme', 'zeitfresser_content_width', 0 );

/**
 * Register widget area.
 *
 * @return void
 */
function zeitfresser_widgets_init() {
    register_sidebar(
        array(
            'name'          => esc_html__( 'Sidebar', 'zeitfresser' ),
            'id'            => 'sidebar-1',
            'description'   => esc_html__( 'Add widgets here.', 'zeitfresser' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        )
    );
}
add_action( 'widgets_init', 'zeitfresser_widgets_init' );

/**
 * Enqueue floating TOC assets on single posts.
 *
 * @return void
 */
function zeitfresser_enqueue_toc_assets() {
	if ( is_singular( 'post' ) && zeitfresser_has_floating_toc() ) {
		wp_enqueue_script(
			'zeitfresser-toc',
			get_template_directory_uri() . '/js/toc.js',
			array(),
			ZEITFRESSER_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_enqueue_toc_assets', 20 );


/**
 * Return file version using filemtime in production-safe form.
 *
 * @param string $relative_path Relative file path inside the theme.
 * @return string
 */
function zeitfresser_asset_version( $relative_path ) {
    $path = get_template_directory() . $relative_path;
    return file_exists( $path ) ? (string) filemtime( $path ) : ZEITFRESSER_VERSION;
}

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function zeitfresser_scripts() {
    wp_enqueue_style(
        'zeitfresser',
        get_template_directory_uri() . '/style.css',
        array(),
        zeitfresser_asset_version( '/style.css' )
    );

    wp_enqueue_script(
        'zeitfresser-navigation',
        get_template_directory_uri() . '/js/navigation.js',
        array(),
        zeitfresser_asset_version( '/js/navigation.js' ),
        true
    );

    if ( is_home() || is_front_page() || is_archive() || is_search() ) {
        wp_enqueue_script(
            'zeitfresser-masonry',
            get_template_directory_uri() . '/js/masonry.pkgd.min.js',
            array(),
            zeitfresser_asset_version( '/js/masonry.pkgd.min.js' ),
            true
        );
    }

    wp_enqueue_script(
        'zeitfresser-scripts',
        get_template_directory_uri() . '/js/scripts.js',
        array(),
        zeitfresser_asset_version( '/js/scripts.js' ),
        true
    );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_scripts', 10 );


/**
 * Load RTL stylesheet from /css folder
 */
function zeitfresser_rtl_styles() {
    if ( is_rtl() ) {
        wp_enqueue_style(
            'zeitfresser-rtl',
            get_template_directory_uri() . '/css/style-rtl.css',
            array('zeitfresser'),
            filemtime(get_template_directory() . '/css/style-rtl.css')
        );
    }
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_rtl_styles', 11 );

function zeitfresser_enqueue_static_colors() {
    wp_enqueue_style(
        'zeitfresser-colors',
        get_template_directory_uri() . '/css/colors.css',
        array('zeitfresser'),
        file_exists(get_template_directory() . '/css/colors.css')
            ? filemtime(get_template_directory() . '/css/colors.css')
            : ZEITFRESSER_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_enqueue_static_colors', 20 );

function zeitfresser_enqueue_static_fonts() {
    wp_enqueue_style(
        'zeitfresser-fonts',
        get_template_directory_uri() . '/css/fonts.css',
        array(),
        file_exists(get_template_directory() . '/css/fonts.css')
            ? filemtime(get_template_directory() . '/css/fonts.css')
            : ZEITFRESSER_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_enqueue_static_fonts', 15 );

/**
 * Theme package marker kept for compatibility with the original premium controls.
 *
 * @return string
 */
function zeitfresser_free_pro() {
    return 'pro';
}

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/customizer.php';

if ( defined( 'JETPACK__VERSION' ) ) {
    require get_template_directory() . '/inc/jetpack.php';
}

require get_template_directory() . '/inc/blocks/blocks.php';
require get_template_directory() . '/inc/pagination.php';

/**
 * Add safe front-end performance optimizations.
 *
 * @return void
 */
function zeitfresser_performance_setup() {
    // Disable the legacy embeds script on the front end. Native iframes still work.
    if ( ! is_admin() ) {
        wp_deregister_script( 'wp-embed' );
    }
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_performance_setup', 100 );

/**
 * Remove unnecessary head output for a leaner front end.
 *
 * @return void
 */
function zeitfresser_cleanup_wp_head() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
}
add_action( 'init', 'zeitfresser_cleanup_wp_head' );

/**
 * Preload local fonts for better performance
 */
function zeitfresser_preload_fonts() {
    ?>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/oswald-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/oswald-500.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/oswald-700.woff2" as="font" type="font/woff2" crossorigin>

    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/roboto-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/roboto-500.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/roboto-700.woff2" as="font" type="font/woff2" crossorigin>
    <?php
}
add_action('wp_head', 'zeitfresser_preload_fonts', 1);

/**
 * Remove front-end dashicons for visitors.
 *
 * @return void
 */
function zeitfresser_maybe_dequeue_dashicons() {
    if ( ! is_user_logged_in() ) {
        wp_deregister_style( 'dashicons' );
    }
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_maybe_dequeue_dashicons', 100 );

/**
 * Improve image decoding defaults without changing visual output.
 *
 * @param array        $attr       Image markup attributes.
 * @param WP_Post      $attachment Attachment post object.
 * @param string|array $size       Requested image size.
 * @return array
 */
function zeitfresser_optimize_image_attributes( $attr, $attachment, $size ) {
    if ( empty( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }

    if ( empty( $attr['loading'] ) && ! is_admin() ) {
        $attr['loading'] = 'lazy';
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zeitfresser_optimize_image_attributes', 10, 3 );

/**
 * Lower the threshold for WordPress scaled originals.
 *
 * This prevents very large uploads from shipping oversized source images.
 *
 * @return int
 */
function zeitfresser_big_image_size_threshold() {
    return 1800;
}
add_filter( 'big_image_size_threshold', 'zeitfresser_big_image_size_threshold' );

/**
 * Skip generating oversized core intermediate sizes we do not use.
 *
 * @param array $sizes Registered intermediate sizes.
 * @return array
 */
function zeitfresser_filter_intermediate_image_sizes( $sizes ) {
    unset( $sizes['1536x1536'], $sizes['2048x2048'] );

    return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'zeitfresser_filter_intermediate_image_sizes' );

/**
 * Convert generated JPEG and PNG sub-sizes to WebP when supported by the server.
 *
 * @param array $formats Output format map.
 * @return array
 */
function zeitfresser_image_output_format( $formats ) {
    if ( function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
        $formats['image/jpeg'] = 'image/webp';
        $formats['image/png']  = 'image/webp';
    }

    return $formats;
}
add_filter( 'image_editor_output_format', 'zeitfresser_image_output_format' );

/**
 * Keep generated image quality balanced for file size and visual fidelity.
 *
 * @param int    $quality Proposed image quality.
 * @param string $mime_type Image mime type.
 * @return int
 */
function zeitfresser_image_quality( $quality, $mime_type = 'image/jpeg' ) {
    if ( 'image/png' === $mime_type ) {
        return $quality;
    }

    return 82;
}
add_filter( 'wp_editor_set_quality', 'zeitfresser_image_quality', 10, 2 );

/**
 * Improve attachment image attributes for layout stability and fetch priority.
 *
 * @param array        $attr       Image markup attributes.
 * @param WP_Post      $attachment Attachment post object.
 * @param string|array $size       Requested image size.
 * @return array
 */
function zeitfresser_improve_attachment_dimensions( $attr, $attachment, $size ) {
    if ( empty( $attr['width'] ) || empty( $attr['height'] ) ) {
        $metadata = wp_get_attachment_metadata( $attachment->ID );

        if ( is_array( $metadata ) && ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
            if ( empty( $attr['width'] ) ) {
                $attr['width'] = (int) $metadata['width'];
            }

            if ( empty( $attr['height'] ) ) {
                $attr['height'] = (int) $metadata['height'];
            }
        }
    }

    if ( empty( $attr['fetchpriority'] ) && ! is_admin() && ! is_feed() ) {
        static $did_set_high_priority = false;

        if ( ! $did_set_high_priority && ( is_home() || is_front_page() || is_archive() || is_search() || is_singular() ) ) {
            $attr['fetchpriority'] = 'high';
            $did_set_high_priority = true;
        }
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zeitfresser_improve_attachment_dimensions', 11, 3 );


/**
 * Preload the most likely LCP image for archive and singular views.
 *
 * @param array $resources Existing preload resources.
 * @return array
 */
function zeitfresser_preload_resources( $resources ) {
    if ( is_admin() || is_feed() || is_embed() ) {
        return $resources;
    }

    $image_url  = '';
    $image_type = '';

    if ( is_singular() ) {
        $object_id = get_queried_object_id();

        if ( $object_id && has_post_thumbnail( $object_id ) ) {
            $image_url = get_the_post_thumbnail_url( $object_id, 'large' );
        }
    } elseif ( is_home() || is_front_page() || is_archive() || is_search() ) {
        global $wp_query;

        if ( isset( $wp_query->posts[0]->ID ) && has_post_thumbnail( $wp_query->posts[0]->ID ) ) {
            $image_url = get_the_post_thumbnail_url( $wp_query->posts[0]->ID, 'thumbnail' );
        }
    }

    if ( empty( $image_url ) ) {
        return $resources;
    }

    $extension = strtolower( pathinfo( wp_parse_url( $image_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

    if ( 'jpg' === $extension || 'jpeg' === $extension ) {
        $image_type = 'image/jpeg';
    } elseif ( 'png' === $extension ) {
        $image_type = 'image/png';
    } elseif ( 'webp' === $extension ) {
        $image_type = 'image/webp';
    }

    $resources[] = array_filter(
        array(
            'href'          => esc_url( $image_url ),
            'as'            => 'image',
            'type'          => $image_type,
            'fetchpriority' => 'high',
        )
    );

    return $resources;
}
add_filter( 'wp_preload_resources', 'zeitfresser_preload_resources' );

/**
 * Improve script loading strategy for non-critical assets.
 *
 * @param array  $tag    Script tag markup.
 * @param string $handle Script handle.
 * @param string $src    Script source URL.
 * @return string
 */
function zeitfresser_defer_non_critical_scripts( $tag, $handle, $src ) {
    $deferred_handles = array(
        'zeitfresser-navigation',
        'zeitfresser-masonry',
        'zeitfresser-scripts',
    );

    if ( in_array( $handle, $deferred_handles, true ) && false === strpos( $tag, ' defer' ) ) {
        return str_replace( ' src=', ' defer src=', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'zeitfresser_defer_non_critical_scripts', 10, 3 );
