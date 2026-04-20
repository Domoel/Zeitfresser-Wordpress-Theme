<?php

add_action( 'customize_register', 'zeitfresser_show_hide_site_tagline' );
function zeitfresser_show_hide_site_tagline( $wp_customize ) {

    $wp_customize->add_setting( 'show_hide_site_tagline', array(
        'sanitize_callback'     =>  'zeitfresser_sanitize_checkbox',
        'default'               =>  zeitfresser_get_default_site_tagline_show_hide()
    ) );

    $wp_customize->add_control( new Graphthemes_Toggle_Control( $wp_customize, 'show_hide_site_tagline', array(
        'label' => esc_html__( 'Show/Hide Site Tagline','zeitfresser' ),
        'section' => 'title_tagline',
        'settings' => 'show_hide_site_tagline',
        'type'=> 'toggle',
    ) ) );

}