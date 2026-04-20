<?php

add_action( 'customize_register', 'zeitfresser_container_width' );
function zeitfresser_container_width( $wp_customize ) {

    $general_title = '<hr/><h2>' . esc_html__( 'General:', 'zeitfresser' ) . '</h2>';

    $wp_customize->add_setting(
        'zeitfresser_general_title',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );

    $wp_customize->add_control(
        new Daisy_Blog_Custom_Text(
            $wp_customize,
            'zeitfresser_general_title',
            array(
                'section'  => 'daisy_blog_general_customization_section',
                'label'    => $general_title,
                'priority' => 5,
            )
        )
    );

	$wp_customize->add_setting('container_width', array(
        'default'           => zeitfresser_get_default_container_width(),
        'sanitize_callback' => 'absint',
        'transport' => 'postMessage',
    ));

    $wp_customize->add_control( 'container_width', array(
        'label'       => esc_html__('Container Width', 'zeitfresser'),
        'section'     => 'daisy_blog_general_customization_section',
        'type'        => 'number',
        'priority'    => 10,
        'input_attrs' => array(
            'min' => 1000,
            'max' => 1600
        ) )
    );

}



add_action( 'customize_preview_init', 'zeitfresser_container_width_enqueue_scripts' );
function zeitfresser_container_width_enqueue_scripts() {
    wp_enqueue_script( 'graphthemes-container-width-customizer', get_template_directory_uri() . '/inc/blocks/general/container-width/customizer-container-width.js', array('jquery'), '', true );
}


add_action( 'wp_enqueue_scripts', 'zeitfresser_container_width_dynamic_css' );
function zeitfresser_container_width_dynamic_css() {

    $container_width = esc_attr( get_theme_mod( 'container_width', zeitfresser_get_default_container_width() ) );
    $container_width .= 'px';

    $dynamic_css = ":root { --container-width: $container_width; }";

    wp_add_inline_style( 'zeitfresser', $dynamic_css );
}