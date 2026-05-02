<?php
/**
 * Performance tweaks.
 *
 * @package Zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
