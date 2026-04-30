<?php
/**
 * Theme Customizer Core
 *
 * @package zeitfresser
 */

function zeitfresser_customize_register( $wp_customize ) {

	// Live Preview support
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'zeitfresser_customize_partial_blogname',
			)
		);

		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'zeitfresser_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'zeitfresser_customize_register' );

/**
 * Partial refresh helpers
 */
function zeitfresser_customize_partial_blogname() {
	bloginfo( 'name' );
}

function zeitfresser_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Live preview JS
 */
function zeitfresser_customize_preview_js() {
	wp_enqueue_script(
		'zeitfresser-customizer',
		get_template_directory_uri() . '/js/customizer.js',
		array( 'customize-preview' ),
		ZEITFRESSER_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'zeitfresser_customize_preview_js' );
