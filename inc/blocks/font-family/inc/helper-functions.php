<?php


/**
 * Function to check if it's a google font
*/
function zeitfresser_is_google_font( $font ){
    $return = false;
    $websafe_fonts = zeitfresser_get_websafe_font();
    if( $font ){
        if( array_key_exists( $font, $websafe_fonts ) ){
            //Web Safe Font
            $return = false;
        }else{
            //Google Font
            $return = true;
        }
    }
    return $return; 
}



if( ! function_exists( 'zeitfresser_get_websafe_font' ) ) {
    
    /**
     * Function listing WebSafe Fonts and its attributes
    */
    function zeitfresser_get_websafe_font(){
        $standard_fonts = array(
            'georgia-serif' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => 'Georgia, serif',
            ),
            'palatino-serif' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
            ),
            'times-serif' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Times New Roman", Times, serif',
            ),
            'arial-helvetica' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => 'Arial, Helvetica, sans-serif',
            ),
            'arial-gadget' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Arial Black", Gadget, sans-serif',
            ),
            'comic-cursive' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Comic Sans MS", cursive, sans-serif',
            ),
            'impact-charcoal'  => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => 'Impact, Charcoal, sans-serif',
            ),
            'lucida' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
            ),
            'tahoma-geneva' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => 'Tahoma, Geneva, sans-serif',
            ),
            'trebuchet-helvetica' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Trebuchet MS", Helvetica, sans-serif',
            ),
            'verdana-geneva'  => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => 'Verdana, Geneva, sans-serif',
            ),
            'courier' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Courier New", Courier, monospace',
            ),
            'lucida-monaco' => array(
                'variants' => array( 'regular', 'italic', '700', '700italic' ),
                'fonts' => '"Lucida Console", Monaco, monospace',
            )
        );
        
        return apply_filters( 'daisy_blog_standard_fonts', $standard_fonts );
    }

}


function zeitfresser_used_google_fonts() {
    $main_font_family = zeitfresser_get_mod( 'main_font_family', zeitfresser_get_default_main_font_family() );
    $secondary_font_family = zeitfresser_get_mod( 'secondary_font_family', zeitfresser_get_default_secondary_font_family() );
    $site_identity_font_family = esc_attr( zeitfresser_get_mod( 'site_identity_font_family', zeitfresser_get_default_site_identity_font_family() ) );

    $args['main_font_family'] = $main_font_family;
    $args['secondary_font_family'] = $secondary_font_family;
    $args['site_identity_font_family'] = $site_identity_font_family;

    return $args;
}



add_action( 'wp_loaded', 'zeitfresser_google_font_local' );
if( ! function_exists( 'zeitfresser_google_font_local' ) ) {
    /**
     * Function that load Google Fonts used in our theme from customer locally.
     * Solves privacy concerns with Google's CDN and their sometimes less-than-transparent policies.
    */
    function zeitfresser_google_font_local() {

        $args = array();
        $fonts = zeitfresser_used_google_fonts();

        foreach( $fonts as $font ) {

            $is_google_font = zeitfresser_is_google_font( $font );

            if( $is_google_font ) {
                array_push( $args, $font );
            }

        }

        new Daisy_Blog_Webfonts_Local( $args );
        
    }
}




if ( ! function_exists( 'zeitfresser_font_weight_variants' ) ) {
    /**
     * Return the font variants used by the theme as an array.
     *
     * @return array
     */
    function zeitfresser_font_weight_variants() {
        $weights = explode( ';', zeitfresser_font_weight_query() );
        $weights = array_map( 'trim', $weights );
        $weights = array_filter( $weights );

        if ( ! in_array( '400', $weights, true ) ) {
            $weights[] = '400';
        }

        $weights = array_values( array_unique( $weights ) );
        sort( $weights );

        return $weights;
    }
}

if ( ! function_exists( 'zeitfresser_get_local_webfonts_css' ) ) {
    /**
     * Build local @font-face CSS for currently selected Google fonts.
     *
     * Falls back to remote font file URLs per variant if a local file is not
     * available yet, so typography does not break during warmup.
     *
     * @return string
     */
    function zeitfresser_get_local_webfonts_css() {
        $fonts    = array_values( array_unique( array_filter( zeitfresser_used_google_fonts() ) ) );
        $variants = zeitfresser_font_weight_variants();
        $css      = '';

        foreach ( $fonts as $font ) {
            if ( ! zeitfresser_is_google_font( $font ) ) {
                continue;
            }

            $css .= Daisy_Blog_Google_Local::init( $font )->get_css( $variants );
        }

        return $css;
    }
}

if ( ! function_exists( 'zeitfresser_get_local_webfont_urls' ) ) {
    /**
     * Extract local font asset URLs from a generated @font-face stylesheet.
     *
     * @param string $css Local webfont CSS.
     * @return array
     */
    function zeitfresser_get_local_webfont_urls( $css ) {
        if ( empty( $css ) ) {
            return array();
        }

        preg_match_all( '#url\(([^)]+)\)#', $css, $matches );

        if ( empty( $matches[1] ) ) {
            return array();
        }

        $urls = array();

        foreach ( $matches[1] as $url ) {
            $url = trim( $url, "\"'" );

            if ( false !== strpos( $url, content_url() ) ) {
                $urls[] = esc_url_raw( $url );
            }
        }

        return array_values( array_unique( array_filter( $urls ) ) );
    }
}


if( ! function_exists( 'zeitfresser_font_weight_query' ) ) {
    /**
     * Return the compact weight list used by the Zeitfresser theme.
    */
    function zeitfresser_font_weight_query() {
        $weights = array( '400', '500', '700' );
        $body_weight = (string) zeitfresser_get_mod( 'font_weight', zeitfresser_get_default_font_weight() );

        if ( preg_match( '/^\d{3}$/', $body_weight ) ) {
            $weights[] = $body_weight;
        }

        $weights = array_values( array_unique( array_filter( $weights ) ) );
        sort( $weights );

        return implode( ';', $weights );
    }
}

if( ! function_exists( 'zeitfresser_fonts_url' ) ) {
    /**
     * Returns a Google Fonts CSS2 URL for the selected theme fonts.
    */ 
    function zeitfresser_fonts_url( $fonts = array() ) {
        $font_families = array();
        $weights       = zeitfresser_font_weight_query();

        foreach ( $fonts as $font ) {
            if ( ! zeitfresser_is_google_font( $font ) ) {
                continue;
            }

            $family_name = trim( (string) $font );

            if ( '' === $family_name ) {
                continue;
            }

            $font_families[] = 'family=' . str_replace( ' ', '+', $family_name ) . ':wght@' . $weights;
        }

        $font_families = array_values( array_unique( $font_families ) );

        if ( empty( $font_families ) ) {
            return '';
        }

        return esc_url( 'https://fonts.googleapis.com/css2?' . implode( '&', $font_families ) . '&display=swap' );
    }
}



if( ! function_exists( 'zeitfresser_check_varient' ) ) {
    /**
     * Checks for matched varients in google fonts for typography fields
    */
    function zeitfresser_check_varient( $font_family = 'serif', $font_variants = 'regular', $body = false ){
        $variant = '';
        $var     = array();
        $google_fonts  = zeitfresser_get_google_fonts(); //Google Fonts
        $websafe_fonts = zeitfresser_get_websafe_font(); //Standard Web Safe Fonts
        
        if( array_key_exists( $font_family, $google_fonts ) ){
            $variants = $google_fonts[ $font_family ][ 'variants' ];
            if( in_array( $font_variants, $variants ) ){
                if( $body ){ //LOAD ALL VARIANTS FOR BODY FONT
                    foreach( $variants as $v ){
                        $var[] = $v;
                    }
                    $variant = implode( ',', $var );
                }else{                
                    $variant = $font_variants;
                }
            }else{
                $variant = 'regular';
            }        
        }else{ //Standard Web Safe Fonts
            if( array_key_exists( $font_family, $websafe_fonts ) ){
                $variants = $websafe_fonts[ $font_family ][ 'variants' ];
                if( in_array( $font_variants, $variants ) ){
                    if( $body ){ //LOAD ALL VARIANTS FOR BODY FONT
                        foreach( $variants as $v ){
                            $var[] = $v;
                        }
                        $variant = implode( ',', $var );
                    }else{  
                        $variant = $font_variants;
                    }
                }else{
                    $variant = 'regular';
                }    
            }
        }
        return $variant;
    }
}



if( ! function_exists( 'zeitfresser_get_google_fonts' ) ) {
    /**
     * Get Google Fonts
    */
    function zeitfresser_get_google_fonts(){
        $webfonts_json = @file_get_contents( get_template_directory_uri() . '/inc/blocks/font-family/inc/google-webfonts.json', true );
        $fonts = json_decode( $webfonts_json, true );

        $google_fonts = array();
        
        if ( is_array( $fonts ) ) {
            foreach ( $fonts['items'] as $font ) {
                $google_fonts[ $font['family'] ] = array(
                    'variants' => $font['variants'],
                );
            }
        }    
        return $google_fonts;
    }
}