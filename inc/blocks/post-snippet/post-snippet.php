<?php
/**
 * Post snippet customizer section.
 */

add_action( 'customize_register', 'zeitfresser_register_post_snippet_customization_section' );

/**
 * Register the post snippet section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function zeitfresser_register_post_snippet_customization_section( $wp_customize ) {
    $wp_customize->add_section(
        'daisy_blog_post_snippet_customization_section',
        array(
            'title'    => esc_html__( 'Post Snippet', 'zeitfresser' ),
            'priority' => 22,
        )
    );
}

require dirname( __FILE__ ) . '/default-post-snippet.php';
require dirname( __FILE__ ) . '/excerpt/excerpt.php';
