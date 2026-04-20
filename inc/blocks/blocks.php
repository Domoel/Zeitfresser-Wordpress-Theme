<?php
/**
 * Register customizer blocks used by the Zeitfresser theme.
 */

if ( ! defined( 'ZEITFRESSER_BLOCKS_DIR_PATH' ) ) {
    define( 'ZEITFRESSER_BLOCKS_DIR_PATH', dirname( __FILE__ ) );
}

if ( ! defined( 'DAISY_BLOG_BLOCKS_DIR_PATH' ) ) {
    define( 'DAISY_BLOG_BLOCKS_DIR_PATH', ZEITFRESSER_BLOCKS_DIR_PATH );
}

require dirname( __FILE__ ) . '/includes/sanitize.php';
require dirname( __FILE__ ) . '/includes/register-controls.php';

require dirname( __FILE__ ) . '/site-identity/site-identity.php';
require dirname( __FILE__ ) . '/colors/colors.php';
require dirname( __FILE__ ) . '/font-family/font-family.php';
require dirname( __FILE__ ) . '/font-customization/font-customization.php';
require dirname( __FILE__ ) . '/general/general.php';
require dirname( __FILE__ ) . '/post-detail/post-detail.php';
require dirname( __FILE__ ) . '/footer-copyright/footer-copyright.php';
