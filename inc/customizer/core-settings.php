<?php
/**
 * Theme Customizer Core
 *
 * @package zeitfresser
 */

/**
 * Shared heading control used across customizer sections.
 *
 * Defined here (the first customizer file loaded) so every section can rely on
 * it without redefining the class. The customize_register hook guarantees the
 * parent WP_Customize_Control class is already available.
 */
function zeitfresser_register_heading_control() {
	if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'ZTFR_Customize_Heading_Control' ) ) {
		class ZTFR_Customize_Heading_Control extends WP_Customize_Control {
			public $type = 'ztfr-heading';

			public function render_content() {
				?>
				<span style="display:block; font-weight:600; font-size:14px; margin:15px 0 5px;">
					<?php echo esc_html( $this->label ); ?>
				</span>
				<?php
			}
		}
	}
}
add_action( 'customize_register', 'zeitfresser_register_heading_control', 0 );

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
	$customizer = zeitfresser_asset_versioned( '/js/customizer.js' );

	wp_enqueue_script(
		'zeitfresser-customizer',
		$customizer['url'],
		array( 'customize-preview' ),
		$customizer['version'],
		true
	);
}
add_action( 'customize_preview_init', 'zeitfresser_customize_preview_js' );
