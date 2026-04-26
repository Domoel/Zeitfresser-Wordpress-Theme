<?php
/**
 * Zeitfresser theme functions and definitions.
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ------------------------------------------------------------------------
 * Theme Constants
 * ------------------------------------------------------------------------
 */
if ( ! defined( 'ZEITFRESSER_VERSION' ) ) {
    define( 'ZEITFRESSER_VERSION', '2.3.6' );
}

if ( ! defined( 'ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION' ) ) {
    define( 'ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION', '1.0' );
}

/**
 * ------------------------------------------------------------------------
 * Core Modules
 * ------------------------------------------------------------------------
 */

// Helpers (foundation)
require get_template_directory() . '/inc/helpers/zeitfresser-helpers.php';

// Theme logic
require get_template_directory() . '/inc/zeitfresser-toc.php';

// Performance layer
require get_template_directory() . '/inc/performance/performance-tools.php';

/**
 * ------------------------------------------------------------------------
 * Customizer (modular)
 * ------------------------------------------------------------------------
 */
require get_template_directory() . '/inc/customizer/core.php';
require get_template_directory() . '/inc/customizer/general.php';
require get_template_directory() . '/inc/customizer/layout.php';
require get_template_directory() . '/inc/customizer/toc.php';
require get_template_directory() . '/inc/customizer/social.php';

/**
 * ------------------------------------------------------------------------
 * Theme Utilities
 * ------------------------------------------------------------------------
 */
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/pagination.php';

/**
 * ------------------------------------------------------------------------
 * Upload Handling (Original File Tracking)
 * ------------------------------------------------------------------------
 */
function zeitfresser_capture_original_upload( $upload, $context ) {

    if ( empty( $upload['file'] ) ) {
        return $upload;
    }

    // Store temporarily (request-scoped)
    $GLOBALS['zeitfresser_last_uploaded_file'] = $upload['file'];

    return $upload;
}
add_filter( 'wp_handle_upload', 'zeitfresser_capture_original_upload', 10, 2 );

/**
 * Persist original file path to attachment meta
 */
function zeitfresser_store_original_file( $attachment_id ) {

    if ( ! wp_attachment_is_image( $attachment_id ) ) {
        return;
    }

    if ( empty( $GLOBALS['zeitfresser_last_uploaded_file'] ) ) {
        return;
    }

    $file = $GLOBALS['zeitfresser_last_uploaded_file'];

    // Safety: ensure file still exists
    if ( ! file_exists( $file ) ) {
        return;
    }

    // Prevent overwrite if already set
    if ( get_post_meta( $attachment_id, '_zeitfresser_original_file', true ) ) {
        return;
    }

    update_post_meta(
        $attachment_id,
        '_zeitfresser_original_file',
        $file
    );
}
add_action( 'add_attachment', 'zeitfresser_store_original_file' );

/**
 * ------------------------------------------------------------------------
 * Theme Setup
 * ------------------------------------------------------------------------
 */
function zeitfresser_setup() {

    load_theme_textdomain( 'zeitfresser', get_template_directory() . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    add_theme_support( 'customize-selective-refresh-widgets' );

    add_theme_support( 'custom-logo', array(
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ));

    add_theme_support( 'align-wide' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );

    register_nav_menus( array(
        'menu-1' => esc_html__( 'Primary', 'zeitfresser' ),
    ));

    add_editor_style( 'editor-style.css' );
}
add_action( 'after_setup_theme', 'zeitfresser_setup' );

/**
 * ------------------------------------------------------------------------
 * Image Sizes
 * ------------------------------------------------------------------------
 */
function zeitfresser_custom_image_sizes() {
    add_image_size( 'zeitfresser-content', 720, 0, false );
    add_image_size( 'zeitfresser-card', 480, 0, false );
}
add_action( 'after_setup_theme', 'zeitfresser_custom_image_sizes' );

/**
 * ------------------------------------------------------------------------
 * Content Width
 * ------------------------------------------------------------------------
 */
function zeitfresser_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'zeitfresser_content_width', 640 );
}
add_action( 'after_setup_theme', 'zeitfresser_content_width', 0 );

/**
 * ------------------------------------------------------------------------
 * Widgets
 * ------------------------------------------------------------------------
 */
function zeitfresser_widgets_init() {
    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar', 'zeitfresser' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Add widgets here.', 'zeitfresser' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action( 'widgets_init', 'zeitfresser_widgets_init' );

/**
 * ------------------------------------------------------------------------
 * Assets
 * ------------------------------------------------------------------------
 */
function zeitfresser_scripts() {

    // Base stylesheet (theme root)
    wp_enqueue_style(
        'zeitfresser',
        get_template_directory_uri() . '/style.css',
        [],
        file_exists( get_template_directory() . '/style.css' )
            ? filemtime( get_template_directory() . '/style.css' )
            : ZEITFRESSER_VERSION
    );

    // Styles
    $fonts  = zeitfresser_asset_versioned('/css/fonts.css');
    $colors = zeitfresser_asset_versioned('/css/colors.css');

    wp_enqueue_style(
        'zeitfresser-fonts',
        $fonts['url'],
        [],
        $fonts['version']
    );

    wp_enqueue_style(
        'zeitfresser-colors',
        $colors['url'],
        ['zeitfresser'],
        $colors['version']
    );

    // Scripts
    $nav     = zeitfresser_asset_versioned('/js/navigation.js');
    $scripts = zeitfresser_asset_versioned('/js/scripts.js');

    wp_enqueue_script(
        'zeitfresser-navigation',
        $nav['url'],
        [],
        $nav['version'],
        true
    );

    wp_enqueue_script(
        'zeitfresser-scripts',
        $scripts['url'],
        [],
        $scripts['version'],
        true
    );

    // WordPress native threaded comments
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_scripts', 10 );

/**
 * ------------------------------------------------------------------------
 * Performance Tweaks
 * ------------------------------------------------------------------------
 */
 
 /**
 * ------------------------------------------------------------------------
 * Defer non-critical JavaScript
 * ------------------------------------------------------------------------
 *
 * Prevents JS from blocking page rendering.
 */
function zeitfresser_defer_scripts( $tag, $handle, $src ) {

    $defer_scripts = array(
        'zeitfresser-navigation',
        'zeitfresser-scripts',
    );

    if ( in_array( $handle, $defer_scripts, true ) ) {
        return str_replace( ' src=', ' defer src=', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'zeitfresser_defer_scripts', 10, 3 );
 
/**
 * ------------------------------------------------------------------------
 * Image Loading Optimization (LCP + Lazy Loading)
 * ------------------------------------------------------------------------
 *
 * Ensures the first visible image loads immediately (LCP),
 * while all other images are lazy-loaded for performance.
 */
function zeitfresser_optimize_image_attributes( $attr, $attachment, $size ) {

    static $is_first = true;

    if ( ! is_admin() ) {

        if ( $is_first ) {
            // First image (critical for LCP)
            $attr['loading'] = 'eager';
            $is_first = false;
        } else {
            // All other images
            $attr['loading'] = 'lazy';
        }

        // Improve decoding performance
        $attr['decoding'] = 'async';
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zeitfresser_optimize_image_attributes', 10, 3 );

/**
 * Lower the threshold for WordPress scaled originals when auto optimization is enabled.
 *
 * When automatic optimization is disabled, original uploads should remain untouched.
 *
 * @return int|false
 */
function zeitfresser_big_image_size_threshold() {

    $auto_enabled  = get_theme_mod( 'ztfr_auto_optimize', true );
    $force_enabled = ! empty( $GLOBALS['zeitfresser_force_image_optimization'] );

    if ( ! $auto_enabled && ! $force_enabled ) {
        return false;
    }

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

    $auto_enabled  = get_theme_mod( 'ztfr_auto_optimize', true );
    $force_enabled = ! empty( $GLOBALS['zeitfresser_force_image_optimization'] );

    if ( ! $auto_enabled && ! $force_enabled ) {
        return $sizes;
    }

    unset(
        $sizes['1536x1536'],
        $sizes['2048x2048']
    );

    return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'zeitfresser_filter_intermediate_image_sizes' );

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

        if ( ! $did_set_high_priority && is_singular() ) {
            $attr['fetchpriority'] = 'high';
            $did_set_high_priority = true;
        }
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zeitfresser_improve_attachment_dimensions', 11, 3 );

/**
 * ------------------------------------------------------------------------
 * Preload critical fonts
 * ------------------------------------------------------------------------
 *
 * Preloads only the fonts that are needed for initial rendering
 * (headings + body text). This improves LCP and avoids render delays.
 */
function zeitfresser_preload_fonts() {
    ?>
    <!-- Critical Fonts Only -->
    <link rel="preload" href="<?php echo zeitfresser_asset('/fonts/oswald-400.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo zeitfresser_asset('/fonts/oswald-700.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo zeitfresser_asset('/fonts/roboto-400.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo zeitfresser_asset('/fonts/roboto-500.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <?php
}
add_action('wp_head', 'zeitfresser_preload_fonts', 0);

/**
 * Optimize font loading with preconnect
 */
add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {

    if ( 'preconnect' === $relation_type ) {
        $urls[] = [
            'href'        => get_template_directory_uri(),
            'crossorigin' => 'anonymous',
        ];
    }

    return $urls;

}, 10, 2 );

/**
 * ------------------------------------------------------------------------
 * Critical CSS (inline for faster first render)
 * ------------------------------------------------------------------------
 *
 * We inline only the minimal CSS required for initial layout.
 * This ensures the page structure renders immediately without
 * waiting for the full stylesheet.
 */
function zeitfresser_inline_critical_css() {
    ?>
    <style>
        body {
            margin: 0;
            background: #1e1f29;
        }

        .container {
            max-width: var(--container-width, 1140px);
            margin: 0 auto;
            padding: 0 70px;
        }

        @media (max-width: 800px) {
            .container {
                padding: 0 20px;
            }
        }

        .custom-grid-view {
            display: grid;
        }

        header.site-header {
            background: var(--light-color);
        }
    </style>
    <?php
}
add_action('wp_head', 'zeitfresser_inline_critical_css', 1);

function zeitfresser_performance_setup() {
    if ( ! is_admin() ) {
        wp_deregister_script( 'wp-embed' );
    }
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_performance_setup', 100 );

function zeitfresser_cleanup_wp_head() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'zeitfresser_cleanup_wp_head' );

/**
 * Convert generated JPEG and PNG files to AVIF/WebP when enabled.
 *
 * Auto optimization can be disabled for uploads via Customizer.
 * Manual optimization may still force conversion through a request-scoped flag.
 *
 * @param array $formats Output format map.
 * @return array
 */
function zeitfresser_image_output_format( $formats ) {

    $auto_enabled  = get_theme_mod( 'ztfr_auto_optimize', true );
    $force_enabled = ! empty( $GLOBALS['zeitfresser_force_image_optimization'] );

    if ( ! $auto_enabled && ! $force_enabled ) {
        return $formats;
    }

    if ( function_exists( 'wp_image_editor_supports' ) ) {

        // Prefer AVIF if supported.
        if ( wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) ) ) {
            $formats['image/jpeg'] = 'image/avif';
            $formats['image/png']  = 'image/avif';

        // Fallback to WebP.
        } elseif ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
            $formats['image/jpeg'] = 'image/webp';
            $formats['image/png']  = 'image/webp';
        }
    }

    return $formats;
}
add_filter( 'image_editor_output_format', 'zeitfresser_image_output_format' );

/**
 * Mark images as optimized only when optimization is actually active.
 *
 * @param array $metadata Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function zeitfresser_mark_new_images_optimized( $metadata, $attachment_id ) {

    if ( ! wp_attachment_is_image( $attachment_id ) ) {
        return $metadata;
    }

    $auto_enabled  = get_theme_mod( 'ztfr_auto_optimize', true );
    $force_enabled = ! empty( $GLOBALS['zeitfresser_force_image_optimization'] );

    if ( ! $auto_enabled && ! $force_enabled ) {
        return $metadata;
    }

    update_post_meta(
        $attachment_id,
        '_zeitfresser_media_optimized_version',
        ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION
    );

    return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'zeitfresser_mark_new_images_optimized', 20, 2 );

/**
 * Auto Optimize Hook
 */
add_filter(
    'wp_generate_attachment_metadata',
    'zeitfresser_auto_optimize_on_upload',
    15,
    2
);

function zeitfresser_auto_optimize_on_upload( $metadata, $attachment_id ) {

    // Only images
    if ( ! wp_attachment_is_image( $attachment_id ) ) {
        return $metadata;
    }

    // Feature toggle
    if ( ! get_theme_mod( 'ztfr_auto_optimize', true ) ) {
        return $metadata;
    }

    $file = get_attached_file( $attachment_id );

    if ( ! $file || ! file_exists( $file ) ) {
        return $metadata;
    }

    // DO NOT overwrite captured original
    if ( ! get_post_meta( $attachment_id, '_zeitfresser_original_file', true ) ) {
        update_post_meta( $attachment_id, '_zeitfresser_original_file', $file );
    }

    // Mark optimized (IMPORTANT: no re-trigger here)
    update_post_meta(
        $attachment_id,
        '_zeitfresser_media_optimized_version',
        ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION
    );

    return $metadata;
}

/**
 * Auto Delete Hook
 */
add_filter(
    'wp_generate_attachment_metadata',
    'zeitfresser_auto_delete_original_after_upload',
    30,
    2
);

function zeitfresser_auto_delete_original_after_upload( $metadata, $attachment_id ) {

    if ( ! wp_attachment_is_image( $attachment_id ) ) {
        return $metadata;
    }

    $auto_enabled  = get_theme_mod( 'ztfr_auto_optimize', true );
    $delete_enabled = get_theme_mod( 'ztfr_auto_delete', false );

    if ( ! $auto_enabled || ! $delete_enabled ) {
        return $metadata;
    }

    $original = get_post_meta(
        $attachment_id,
        '_zeitfresser_original_file',
        true
    );

    if ( ! $original ) {
        return $metadata;
    }

    $ext = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );

    // Skip modern formats
    if ( in_array( $ext, [ 'webp', 'avif' ], true ) ) {
        update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
        return $metadata;
    }

    // 🔥 Wenn nichts mehr existiert → fertig
    if ( ! zeitfresser_original_family_exists( $attachment_id, $original ) ) {
        update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
        return $metadata;
    }

    // 🔥 HIER passiert der Fix
    zeitfresser_delete_original_family_files( $attachment_id, $original );

    // Final check
    if ( ! zeitfresser_original_family_exists( $attachment_id, $original ) ) {
        update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
    }

    return $metadata;
}

/**
 * Keep generated image quality balanced for file size and visual fidelity.
 *
 * @param int    $quality Proposed image quality.
 * @param string $mime_type Image mime type.
 * @return int
 */
function zeitfresser_image_quality( $quality, $mime_type = 'image/jpeg' ) {

    $auto_enabled  = get_theme_mod( 'ztfr_auto_optimize', true );
    $force_enabled = ! empty( $GLOBALS['zeitfresser_force_image_optimization'] );

    if ( ! $auto_enabled && ! $force_enabled ) {
        return $quality;
    }

    return match ($mime_type) {
        'image/avif' => 50,
        'image/webp' => 75,
        default => 82,
    };
}
add_filter( 'wp_editor_set_quality', 'zeitfresser_image_quality', 10, 2 );

/**
 * Improve responsive image sizes attribute.
 *
 * @param string $sizes Existing sizes attribute.
 * @param array  $size  Requested image size.
 * @return string
 */
function zeitfresser_responsive_image_sizes( $sizes, $size ) {

    // Single post content
    if ( is_singular() ) {
        return '(max-width: 768px) 100vw, (max-width: 1200px) 720px, 720px';
    }

    // Archive / blog overview
    if ( is_home() || is_front_page() || is_archive() || is_search() ) {
        return '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 400px';
    }

    return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'zeitfresser_responsive_image_sizes', 10, 2 );
