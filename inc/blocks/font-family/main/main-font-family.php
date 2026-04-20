<?php

add_action( 'customize_register', 'zeitfresser_main_font_family' );
function zeitfresser_main_font_family( $wp_customize ) {

    $wp_customize->add_setting( 'main_font_family', array(
        'default'     => zeitfresser_get_default_main_font_family(),
        'transport'   => 'postMessage',
        'sanitize_callback' => 'zeitfresser_sanitize_google_fonts'
    ) );

    $wp_customize->add_control( 'main_font_family', array(
        'settings'    => 'main_font_family',
        'label'       =>  esc_html__( 'Primary Font', 'zeitfresser' ),
        'section'     => 'daisy_blog_font_customization_section',
        'type'        => 'select',
        'choices'     => zeitfresser_google_fonts( zeitfresser_free_pro() ),
    ) );

}


add_action( 'customize_preview_init', 'zeitfresser_main_font_family_enqueue_scripts' );
function zeitfresser_main_font_family_enqueue_scripts() {

    $main_font_family = esc_attr( get_theme_mod( 'main_font_family', zeitfresser_get_default_main_font_family() ) );

    wp_enqueue_script( 'graphthemes-main-font-family-customizer', get_template_directory_uri() . '/inc/blocks/font-family/main/customizer-main-font-family.js', array('jquery'), '', true );
}


add_action( 'wp_enqueue_scripts', 'zeitfresser_main_font_family_dynamic_css' );
function zeitfresser_main_font_family_dynamic_css() {

    $main_font_family = esc_attr( get_theme_mod( 'main_font_family', zeitfresser_get_default_main_font_family() ) );

    $dynamic_css = ":root { --primary-font: $main_font_family; }";

    wp_add_inline_style( 'zeitfresser', $dynamic_css );
}