<?php
/**
 * Simplified Code Block Feature
 *
 * Handles:
 * - Frontend assets
 * - Editor assets
 * - Classic Editor button
 * - Gutenberg block
 * - Server-side wrapping for code blocks
 *
 * @package Zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if current post content contains code blocks.
 *
 * @return bool
 */
function ztfr_has_code_block() {

	if ( is_admin() ) {
		return false;
	}

	if ( ! is_singular() ) {
		return false;
	}

	global $post;

	if ( ! isset( $post ) || empty( $post->post_content ) ) {
		return false;
	}

	if ( has_block( 'ztfr/code-block', $post ) ) {
		return true;
	}

	return false !== strpos( $post->post_content, '<pre' );
}

/**
 * Get asset version from file modification time.
 *
 * @param string $relative_path Relative path inside the theme directory.
 * @return string
 */
function ztfr_code_asset_version( $relative_path ) {
	$file_path = get_template_directory() . $relative_path;

	if ( file_exists( $file_path ) ) {
		return (string) filemtime( $file_path );
	}

	return (string) ZEITFRESSER_VERSION;
}

/**
 * Enqueue frontend assets.
 */
function ztfr_enqueue_code_assets() {

	if ( is_admin() ) {
		return;
	}
	
<<<<<<< HEAD
=======
	// 🔥 Nur laden wenn Codeblock vorhanden
>>>>>>> 1ee3574d03f75ed3016267f2fdfbc78f468fcaa0
	if ( ! ztfr_has_code_block() ) {
		return;
	}

	$code_css_version = ztfr_code_asset_version( '/assets/css/code.css' );
	$prism_version    = ztfr_code_asset_version( '/assets/js/prism.js' );
	$code_js_version  = ztfr_code_asset_version( '/assets/js/code-block.js' );

	wp_enqueue_style(
		'ztfr-code',
		get_template_directory_uri() . '/assets/css/code.css',
		[],
		$code_css_version
	);

	wp_enqueue_script(
		'ztfr-prism',
		get_template_directory_uri() . '/assets/js/prism.js',
		[],
		$prism_version,
		true
	);

	/**
	 * Disable Prism auto-highlighting.
	 *
	 * Prism auto-runs on DOM ready unless Prism.manual is set before the core
	 * script executes. We disable auto-run so highlighting happens only once
	 * in our custom script after the DOM is ready.
	 */
	wp_add_inline_script(
		'ztfr-prism',
		'window.Prism = window.Prism || {}; window.Prism.manual = true;',
		'before'
	);

	wp_enqueue_script(
		'ztfr-code-block',
		get_template_directory_uri() . '/assets/js/code-block.js',
		[ 'ztfr-prism' ],
		$code_js_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ztfr_enqueue_code_assets' );

/**
 * Enqueue block editor assets.
 */
function ztfr_enqueue_code_block_editor_assets() {
	$editor_js_version  = ztfr_code_asset_version( '/assets/js/editor.js' );
	$code_css_version   = ztfr_code_asset_version( '/assets/css/code.css' );
	$editor_css_version = ztfr_code_asset_version( '/assets/css/editor.css' );

	wp_enqueue_script(
		'ztfr-code-editor',
		get_template_directory_uri() . '/assets/js/editor.js',
		[
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-components',
			'wp-block-editor',
		],
		$editor_js_version,
		true
	);

	wp_enqueue_style(
		'ztfr-code-editor-preview',
		get_template_directory_uri() . '/assets/css/code.css',
		[],
		$code_css_version
	);

	wp_enqueue_style(
		'ztfr-editor',
		get_template_directory_uri() . '/assets/css/editor.css',
		[],
		$editor_css_version
	);
}
add_action( 'enqueue_block_editor_assets', 'ztfr_enqueue_code_block_editor_assets' );

/**
 * Register Classic Editor button only for users who can edit and use rich text.
 */
function ztfr_register_classic_code_button() {

	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	if ( 'true' !== get_user_option( 'rich_editing' ) ) {
		return;
	}

	add_filter( 'mce_external_plugins', 'ztfr_add_classic_code_plugin' );
	add_filter( 'mce_buttons', 'ztfr_add_classic_code_button' );
}
add_action( 'admin_init', 'ztfr_register_classic_code_button' );

/**
 * Add TinyMCE plugin script.
 *
 * @param array $plugins TinyMCE plugins.
 * @return array
 */
function ztfr_add_classic_code_plugin( $plugins ) {
	$plugins['ztfr_code_block'] = get_template_directory_uri() . '/assets/js/editor.js';
	return $plugins;
}

/**
 * Add TinyMCE toolbar button.
 *
 * @param array $buttons TinyMCE buttons.
 * @return array
 */
function ztfr_add_classic_code_button( $buttons ) {
	$buttons[] = 'ztfr_code_block';
	return $buttons;
}

/**
 * Wrap code blocks server-side.
 *
 * This keeps Classic Editor, Gutenberg and pasted raw code blocks
 * compatible with the frontend styling without runtime DOM manipulation.
 *
 * @param string $content Post content.
 * @return string
 */
function ztfr_wrap_code_blocks( $content ) {

	if ( is_admin() ) {
		return $content;
	}

	if ( false === strpos( $content, '<pre' ) ) {
		return $content;
	}

	$content = preg_replace_callback(
		'/<pre([^>]*)><code([^>]*)>(.*?)<\/code><\/pre>/s',
		function ( $matches ) {

			$pre_attrs  = $matches[1];
			$code_attrs = $matches[2];
			$code       = $matches[3];

			if ( false === strpos( $pre_attrs, 'language-' ) ) {
				$pre_attrs .= ' class="language-yaml"';
			}

			if ( false === strpos( $code_attrs, 'language-' ) ) {
				$code_attrs .= ' class="language-yaml"';
			}

			return sprintf(
				'<div class="ztfr-code"><pre%s><code%s>%s</code></pre></div>',
				$pre_attrs,
				$code_attrs,
				$code
			);
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'ztfr_wrap_code_blocks', 20 );
