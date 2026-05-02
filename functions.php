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
 * Customizer
 * ------------------------------------------------------------------------
 */
require_once get_template_directory() . '/inc/customizer/core-settings.php';
require_once get_template_directory() . '/inc/customizer/general-settings.php';
require_once get_template_directory() . '/inc/customizer/layout-settings.php';
require_once get_template_directory() . '/inc/customizer/toc-settings.php';
require_once get_template_directory() . '/inc/customizer/social-settings.php';
require_once get_template_directory() . '/inc/customizer/image-optimizer-settings.php';

/**
 * ------------------------------------------------------------------------
 * Utilities
 * ------------------------------------------------------------------------
 */
require_once get_template_directory() . '/inc/utilities/helpers.php';
require_once get_template_directory() . '/inc/utilities/template-tags.php';
require_once get_template_directory() . '/inc/utilities/template-functions.php';
require_once get_template_directory() . '/inc/utilities/pagination.php';
require_once get_template_directory() . '/inc/utilities/toc.php';

/**
 * ------------------------------------------------------------------------
 * Tools
 * ------------------------------------------------------------------------
 */
require_once get_template_directory() . '/inc/tools/image-optimizer.php';
require_once get_template_directory() . '/inc/tools/code-block.php';

/**
 * ------------------------------------------------------------------------
 * Performance
 * ------------------------------------------------------------------------
 */
require_once get_template_directory() . '/inc/performance/performance.php';

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

	// Editor Styles
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

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

	/**
	 * Base stylesheet
	 */
	wp_enqueue_style(
		'zeitfresser',
		get_template_directory_uri() . '/style.css',
		[],
		file_exists( get_template_directory() . '/style.css' )
			? filemtime( get_template_directory() . '/style.css' )
			: ZEITFRESSER_VERSION
	);

	/**
	 * Additional styles (versioned helper)
	 */
	$fonts  = zeitfresser_asset_versioned('/css/fonts.css');
	$colors = zeitfresser_asset_versioned('/css/colors.css');

	wp_enqueue_style(
		'zeitfresser-fonts',
		$fonts['url'],
		['zeitfresser'],
		$fonts['version']
	);

	wp_enqueue_style(
		'zeitfresser-colors',
		$colors['url'],
		['zeitfresser'],
		$colors['version']
	);

	/**
	 * Scripts
	 */
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
		['zeitfresser-navigation'],
		$scripts['version'],
		true
	);

	/**
	 * WordPress native threaded comments
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'zeitfresser_scripts', 10 );
