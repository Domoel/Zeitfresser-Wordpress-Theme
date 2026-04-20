<?php

add_action( 'customize_register', 'zeitfresser_primary_color' );
function zeitfresser_primary_color( $wp_customize ) {

    $wp_customize->add_setting( 'primary_color', array(
        'default'     => zeitfresser_get_default_primary_color(),
        'transport'   => 'postMessage',
        'sanitize_callback' => 'sanitize_hex_color'
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary_color', array(
        'label'      => esc_html__( 'Menu / Title / Button Color', 'zeitfresser' ),
        'section'    => 'colors',
        'settings'   => 'primary_color',
        
    ) ) );

}


add_action( 'customize_preview_init', 'zeitfresser_primary_color_enqueue_scripts' );
function zeitfresser_primary_color_enqueue_scripts() {
    wp_enqueue_script( 'graphthemes-primary-color-customizer', get_template_directory_uri() . '/inc/blocks/colors/color-primary/customizer-color-primary.js', array('jquery'), '', true );
}