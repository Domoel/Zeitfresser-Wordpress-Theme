<?php


add_action( 'after_setup_theme', function () {
    add_theme_support( 'custom-header', array( 'header-text' => false ) );
} );

add_action('customize_register', 'zeitfresser_color_section');
function zeitfresser_color_section($wp_customize)
{
    $wp_customize->get_section('colors')->title = esc_html__( "Color Options", 'zeitfresser' );
    $wp_customize->get_section('colors')->priority = 21;
}

/* Add Default Colors for Customizer Settings */
require dirname( __FILE__ ) . '/default-colors.php';

if( db_fs()->is__premium_only() ) {

    require dirname( __FILE__ ) . '/color-site-title/color-site-title.php';

    require dirname( __FILE__ ) . '/color-primary/color-primary.php';

    require dirname( __FILE__ ) . '/color-secondary/color-secondary.php';

    require dirname( __FILE__ ) . '/color-light/color-light.php';

    require dirname( __FILE__ ) . '/color-grey/color-grey.php';

    require dirname( __FILE__ ) . '/color-dark/color-dark.php';
}

require dirname( __FILE__ ) . '/color-background/color-background.php';

require dirname( __FILE__ ) . '/dynamic-colors.php';