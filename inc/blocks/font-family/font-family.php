<?php

/* Add Google Fonts */
require dirname( __FILE__ ) . '/google-fonts.php';

/* Add Default Font Family for Customizer Settings */
require dirname( __FILE__ ) . '/default-font-family.php';

include_once wp_normalize_path( dirname( __FILE__ ) . '/inc/helper-functions.php' );
include_once wp_normalize_path( dirname( __FILE__ ) . '/inc/class-webfonts-local.php' );
include_once wp_normalize_path( dirname( __FILE__ ) . '/inc/class-fonts-google-local.php' );



require dirname( __FILE__ ) . '/site-identity/site-identity-font-family.php';
require dirname( __FILE__ ) . '/main/main-font-family.php';
require dirname( __FILE__ ) . '/secondary/secondary-font-family.php';


add_action( 'wp_enqueue_scripts', 'zeitfresser_google_fonts_scripts', 5 );
function zeitfresser_google_fonts_scripts() {
    $local_css = zeitfresser_get_local_webfonts_css();

    wp_register_style( 'zeitfresser-webfonts', false, array(), ZEITFRESSER_VERSION );
    wp_enqueue_style( 'zeitfresser-webfonts' );

    if ( ! empty( $local_css ) ) {
        wp_add_inline_style( 'zeitfresser-webfonts', $local_css );
        return;
    }

    $args      = zeitfresser_used_google_fonts();
    $fonts_url = zeitfresser_fonts_url( $args );

    if ( empty( $fonts_url ) ) {
        return;
    }

    wp_enqueue_style( 'google-fonts', $fonts_url, array(), null );
}
