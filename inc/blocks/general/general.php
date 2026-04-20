<?php
/**
 * General customizer section.
 */

add_action( 'customize_register', 'zeitfresser_register_general_customization_section' );

/**
 * Register the general options section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function zeitfresser_register_general_customization_section( $wp_customize ) {
    $wp_customize->add_section(
        'daisy_blog_general_customization_section',
        array(
            'title'    => esc_html__( 'General Options', 'zeitfresser' ),
            'priority' => 21,
        )
    );
}

require dirname( __FILE__ ) . '/default-general.php';
require dirname( __FILE__ ) . '/container-width/container-width.php';
require dirname( __FILE__ ) . '/social-links/social-links.php';

require dirname( __FILE__ ) . '/../post-snippet/default-post-snippet.php';
require dirname( __FILE__ ) . '/../post-snippet/excerpt/excerpt.php';
