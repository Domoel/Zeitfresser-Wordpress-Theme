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

if ( ! defined( 'ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION' ) ) {
    define( 'ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION', '1.0' );
}

require get_template_directory() . '/inc/zeitfresser-helpers.php';
require get_template_directory() . '/inc/performance-tools.php';
require get_template_directory() . '/inc/zeitfresser-toc.php';

/**
 * Upload Handler
 */
add_filter('wp_handle_upload', 'zeitfresser_capture_original_upload', 10, 2);

/**
 * Capture original upload path safely
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
 * Register optimized image sizes
 */
function zeitfresser_custom_image_sizes() {

    // Content images (main article)
    add_image_size( 'zeitfresser-content', 720, 0, false );

    // Archive / card layout
    add_image_size( 'zeitfresser-card', 480, 0, false );
}
add_action( 'after_setup_theme', 'zeitfresser_custom_image_sizes' );

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
 * Preload critical local fonts only
 */
function zeitfresser_preload_fonts() {
    ?>
    <!-- Critical Fonts Only -->
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/oswald-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/oswald-700.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/roboto-400.woff2" as="font" type="font/woff2" crossorigin>
    <?php
}
add_action('wp_head', 'zeitfresser_preload_fonts', 1);

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
 * Lower the threshold for WordPress scaled originals when auto optimization is enabled.
 *
 * When automatic optimization is disabled, original uploads should remain untouched.
 *
 * @return int|false
 */
function zeitfresser_big_image_size_threshold() {

    if ( ! get_theme_mod( 'ztfr_auto_optimize', true ) ) {
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

    // Remove oversized defaults
    unset(
        $sizes['1536x1536'],
        $sizes['2048x2048']
    );

    return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'zeitfresser_filter_intermediate_image_sizes' );

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

    // 🔒 only images
    if ( ! wp_attachment_is_image( $attachment_id ) ) {
        return $metadata;
    }

    // 🔒 feature toggle
    if ( ! get_theme_mod( 'ztfr_auto_optimize', true ) ) {
        return $metadata;
    }

    $file = get_attached_file( $attachment_id );

    if ( ! $file || ! file_exists( $file ) ) {
        return $metadata;
    }

    // 🔥 DO NOT overwrite upload-captured original
    if ( ! get_post_meta( $attachment_id, '_zeitfresser_original_file', true ) ) {

        // fallback only
        update_post_meta( $attachment_id, '_zeitfresser_original_file', $file );
    }

    // mark as optimized
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

    // 🔒 only images
    if ( ! wp_attachment_is_image( $attachment_id ) ) {
        return $metadata;
    }

    // 🔒 feature toggles
    if ( ! get_theme_mod( 'ztfr_auto_optimize', true ) ) {
        return $metadata;
    }

    if ( ! get_theme_mod( 'ztfr_auto_delete', false ) ) {
        return $metadata;
    }

    $original = get_post_meta(
        $attachment_id,
        '_zeitfresser_original_file',
        true
    );

    if ( ! $original || ! file_exists( $original ) ) {
        return $metadata;
    }

    // skip modern formats
    $ext = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );

    if ( in_array( $ext, [ 'webp', 'avif' ], true ) ) {
        update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
        return $metadata;
    }

    if ( ! is_writable( $original ) ) {
        return $metadata;
    }

    // 🔥 delete original
    if ( unlink( $original ) ) {
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

    switch ( $mime_type ) {
        case 'image/avif':
            return 50;

        case 'image/webp':
            return 75;

        case 'image/jpeg':
        default:
            return 82;
    }
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

        if ( ! $did_set_high_priority && is_singular() ) {
            $attr['fetchpriority'] = 'high';
            $did_set_high_priority = true;
        }
    }

    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zeitfresser_improve_attachment_dimensions', 11, 3 );

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

/**
 * Determine the most likely LCP image URL.
 *
 * @return string
 */
function zeitfresser_get_lcp_image_url() {

    // Single posts/pages → featured image
    if ( is_singular() ) {
        $post_id = get_queried_object_id();

        if ( $post_id && has_post_thumbnail( $post_id ) ) {
            return get_the_post_thumbnail_url( $post_id, 'large' );
        }
    }

    // Archives / homepage → first post with thumbnail
    if ( is_home() || is_front_page() || is_archive() || is_search() ) {

        global $wp_query;

        if ( ! empty( $wp_query->posts ) ) {

            foreach ( $wp_query->posts as $post ) {
                if ( has_post_thumbnail( $post->ID ) ) {
                    return get_the_post_thumbnail_url( $post->ID, 'medium_large' );
                }
            }
        }
    }

    return '';
}

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

    // 🔥 NEW: use smart detection
    $image_url = zeitfresser_get_lcp_image_url();

    if ( empty( $image_url ) ) {
        return $resources;
    }

    // Robust extension detection
    $extension = strtolower(
        pathinfo(
            wp_parse_url( $image_url, PHP_URL_PATH ),
            PATHINFO_EXTENSION
        )
    );

    // MIME fallback
    $image_type = 'image/jpeg';

    if ( 'png' === $extension ) {
        $image_type = 'image/png';
    } elseif ( 'webp' === $extension ) {
        $image_type = 'image/webp';
    } elseif ( 'avif' === $extension ) {
        $image_type = 'image/avif';
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
