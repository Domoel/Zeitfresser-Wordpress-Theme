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
 * Image attribute optimization (LCP, lazy loading, CLS, fetch priority)
 * ------------------------------------------------------------------------
 *
 * Single pass over wp_get_attachment_image_attributes:
 * - backfills width/height for layout stability (runs everywhere, incl. admin)
 * - first visible image loads eagerly (LCP), the rest lazily
 * - first image on singular views gets fetchpriority="high"
 *
 * @param array        $attr       Image markup attributes.
 * @param WP_Post      $attachment Attachment post object.
 * @param string|array $size       Requested image size.
 * @return array
 */
function zeitfresser_image_attributes( $attr, $attachment, $size ) {

    // Backfill missing dimensions for layout stability (CLS).
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

    if ( is_admin() ) {
        return $attr;
    }

    // Loading strategy: first image eager (LCP), all others lazy.
    static $is_first = true;

    if ( $is_first ) {
        $attr['loading'] = 'eager';
        $is_first        = false;
    } else {
        $attr['loading'] = 'lazy';
    }

    $attr['decoding'] = 'async';

    // First image on singular views is the likely LCP element.
    if ( empty( $attr['fetchpriority'] ) && ! is_feed() ) {
        static $did_set_high_priority = false;

        if ( ! $did_set_high_priority && is_singular() ) {
            $attr['fetchpriority']  = 'high';
            $did_set_high_priority = true;
        }
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zeitfresser_image_attributes', 10, 3 );

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
 * ------------------------------------------------------------------------
 * Preload critical fonts
 * ------------------------------------------------------------------------
 *
 * Preloads only the fonts that are needed for initial rendering
 * (headings + body text). This improves LCP and avoids render delays.
 */
function zeitfresser_preload_fonts() {
    ?>
    <!-- Above-the-fold fonts only: body text (Roboto 400) + headings/post title (Oswald 500). -->
    <!-- All other weights load on demand via font-display: swap. -->
    <link rel="preload" href="<?php echo zeitfresser_asset('/fonts/roboto-400.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo zeitfresser_asset('/fonts/oswald-500.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <?php
}
add_action('wp_head', 'zeitfresser_preload_fonts', 0);

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
    </style>
    <?php
}
add_action('wp_head', 'zeitfresser_inline_critical_css', 1);

/**
 * ------------------------------------------------------------------------
 * Speculation Rules: prerender same-origin links on hover
 * ------------------------------------------------------------------------
 *
 * Enables near-instant navigation via the Speculation Rules API. Links are
 * prerendered on hover/pointerdown ("moderate" eagerness). Dynamic URLs
 * (query strings), admin/login URLs, nofollow and new-tab links are excluded;
 * add the .no-prerender class to opt a link out.
 *
 * WordPress 6.8+ ships native Speculative Loading, so we only output our own
 * rules on older versions to avoid duplicate hints.
 */
function zeitfresser_speculation_rules() {

    if ( is_admin() || is_feed() ) {
        return;
    }

    if ( version_compare( get_bloginfo( 'version' ), '6.8', '>=' ) ) {
        return;
    }

    $rules = array(
        'prerender' => array(
            array(
                'source'    => 'document',
                'where'     => array(
                    'and' => array(
                        array( 'href_matches' => '/*' ),
                        array( 'not' => array( 'href_matches' => array( '/wp-login.php*', '/wp-admin/*' ) ) ),
                        array( 'not' => array( 'selector_matches' => "[rel~=nofollow], [href*='?'], [target=_blank], .no-prerender" ) ),
                    ),
                ),
                'eagerness' => 'moderate',
            ),
        ),
    );

    echo '<script type="speculationrules">' . wp_json_encode( $rules ) . '</script>' . "\n";
}
add_action( 'wp_head', 'zeitfresser_speculation_rules' );

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
