<?php

add_action( 'customize_register', 'zeitfresser_post_snippet_excerpt_size' );
function zeitfresser_post_snippet_excerpt_size( $wp_customize ) {

	$wp_customize->add_setting( 'post_snippet_excerpt_size', array(
        'sanitize_callback'     =>  'absint',
        'default'               =>  zeitfresser_get_default_post_snippet_excerpt_size()
    ) );

    $wp_customize->add_control( 'post_snippet_excerpt_size', array(
        'settings' => 'post_snippet_excerpt_size',
        'type' => 'number',
        'section' => 'daisy_blog_general_customization_section',
        'label' => esc_html__( 'Excerpt Size', 'zeitfresser' ),
        'priority' => 11,
        'input_attrs' => array(
            'min' => 5,
            'max' => 80,
        ),
    ) );

}