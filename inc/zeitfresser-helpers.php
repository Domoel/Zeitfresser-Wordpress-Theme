<?php
/**
 * Zeitfresser helper functions.
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'zeitfresser_fs' ) ) {
    /**
     * Lightweight Freemius compatibility stub.
     *
     * The original premium theme used Freemius checks to gate premium-only controls.
     * Zeitfresser ships as a self-contained theme, so we keep a tiny compatibility
     * object instead of loading the full SDK.
     *
     * @return object
     */
    function zeitfresser_fs() {
        static $stub = null;

        if ( null === $stub ) {
            $stub = new class() {
                public function is__premium_only() {
                    return true;
                }
            };
        }

        return $stub;
    }
}

if ( ! function_exists( 'zeitfresser_get_social_defaults' ) ) {
    /**
     * Return default social profile URLs.
     *
     * @return array<string,string>
     */
    function zeitfresser_get_social_defaults() {
        return array(
            'mastodon' => 'https://social.ztfr.eu/@dome',
            'github'   => 'https://github.com/Domoel',
            'matrix'   => 'https://look.ztfr.eu/#/@dome:ztfr.eu',
        );
    }
}

if ( ! function_exists( 'zeitfresser_get_social_link_default' ) ) {
    /**
     * Return default social URL for a given service.
     *
     * @param string $social_key Social service key.
     * @return string
     */
    function zeitfresser_get_social_link_default( $social_key ) {
        $defaults = zeitfresser_get_social_defaults();
        return isset( $defaults[ $social_key ] ) ? $defaults[ $social_key ] : '';
    }
}



if ( ! function_exists( 'zeitfresser_get_mod' ) ) {
    /**
     * Return a cached theme modification value.
     *
     * @param string $key     Theme mod key.
     * @param mixed  $default Optional default value.
     * @return mixed
     */
    function zeitfresser_get_mod( $key, $default = null ) {
        static $cache = array();

        if ( array_key_exists( $key, $cache ) ) {
            return $cache[ $key ];
        }

        $cache[ $key ] = get_theme_mod( $key, $default );

        return $cache[ $key ];
    }
}

if ( ! function_exists( 'zeitfresser_get_social_links_settings' ) ) {
    /**
     * Return configured social links with defaults applied.
     *
     * @return array<string,string>
     */
    function zeitfresser_get_social_links_settings() {
        $settings = array();

        foreach ( zeitfresser_get_social_links() as $social_key => $social_label ) {
            $settings[ $social_key ] = zeitfresser_get_mod(
                'social_links_' . $social_key,
                zeitfresser_get_social_link_default( $social_key )
            );
        }

        return $settings;
    }
}

if ( ! function_exists( 'zeitfresser_social_icon_svg' ) ) {
    /**
     * Return inline SVG markup for social icons.
     *
     * @param string $key Social service key.
     * @return string
     */
    function zeitfresser_social_icon_svg( $key ) {
        $icons = array(
            'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" aria-hidden="true" focusable="false"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/></svg>',
            'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/></svg>',
            'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8A53.79 53.79 0 0 1 53.79 0A53.79 53.79 0 0 1 107.6 53.8c0 29.7-24.1 54.3-53.81 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2c-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.29 87.88-48.29c94 0 111.28 61.9 111.28 142.3V448z"/></svg>',
            'twitter'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><path d="M459.37 151.72c.32 4.54.32 9.1.32 13.63c0 138.72-105.58 298.56-298.56 298.56c-59.45 0-114.68-17.22-161.14-47.11c8.45 1 16.57 1.32 25.34 1.32c49.06 0 94.21-16.57 130.27-44.84c-46.13-1-84.79-31.24-98.11-72.77c6.5 1 12.99 1.63 19.81 1.63c9.42 0 18.84-1.32 27.61-3.57c-48.08-9.74-84.14-51.98-84.14-102.98v-1.32c13.97 7.8 30.21 12.67 47.43 13.3c-28.3-18.84-46.78-51.02-46.78-87.39c0-19.49 5.2-37.36 14.29-52.95c51.98 63.67 129.3 105.26 216.36 109.75c-1.62-7.8-2.6-15.92-2.6-24.04c0-57.83 46.78-104.93 104.93-104.93c30.21 0 57.5 12.67 76.67 33.14c23.72-4.55 46.46-13.3 66.59-25.34c-7.8 24.37-24.37 44.84-46.13 57.83c21.12-2.27 41.58-8.12 60.42-16.24c-14.29 20.79-32.16 39.31-52.63 54.25z"/></svg>',
            'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" aria-hidden="true" focusable="false"><path d="M204 6.7C93.4 6.7 0 100.1 0 210.7c0 69.2 40.5 128.9 101.6 161.4c-1.4-13.7-2.6-34.8.5-49.8c2.8-15.9 18.1-101.2 18.1-101.2s-4.6-9.3-4.6-23c0-21.6 12.5-37.8 28.1-37.8c13.2 0 19.6 9.9 19.6 21.8c0 13.2-8.4 33-12.7 51.3c-3.6 15.6 7.7 28.4 23 28.4c27.6 0 48.9-29.1 48.9-71.1c0-37.2-26.8-63.2-65-63.2c-44.3 0-70.3 33.1-70.3 67.5c0 13.3 5.1 27.5 11.6 35.2c1.3 1.6 1.5 3 .9 4.7c-1 5.1-3.2 16-3.6 18.2c-.6 3-2.1 3.6-4.8 2.2c-17.8-8.3-28.9-34.1-28.9-54.8c0-44.6 32.4-85.6 93.4-85.6c49 0 87.1 34.9 87.1 81.6c0 48.7-30.7 87.9-73.2 87.9c-14.3 0-27.7-7.4-32.3-16.2l-8.8 33.5c-3.2 12.2-11.8 27.5-17.6 36.8c13.2 4.1 27.1 6.2 41.5 6.2c110.6 0 204-93.4 204-204S314.6 6.7 204 6.7z"/></svg>',
            'tiktok'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path d="M448,209.9a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0h88a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,448,142.3Z"/></svg>',
            'mastodon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path d="M433 179.11c0-97.2-63.91-125.7-63.91-125.7C336.42 38.4 279.2 32 224.14 32h-.27c-55.06 0-112.28 6.4-144.95 21.41c0 0-63.92 28.5-63.92 125.7c0 22.34-.43 49.13.27 81.78c2.31 109.32 20.05 217.01 121.08 241.82c46.64 11.45 86.68 13.84 119.46 12.06c59.08-3.2 92.27-20.62 92.27-20.62l-1.96-43.47s-42.3 13.33-89.78 11.72c-47.03-1.6-96.73-5.09-104.41-63.16a116.85 116.85 0 0 1-1.06-15.65s46.27 11.3 104.92 13.99c35.27 1.62 68.32-2.06 101.96-6.15c64.49-7.83 120.53-48.23 127.62-85.36c11.18-58.65 10.26-143.5 10.26-143.5zm-80.32 145.27h-50.54V200.59c0-26.15-11.06-39.43-33.17-39.43c-24.46 0-36.72 15.84-36.72 47.1v67.82H181.8V208.26c0-31.26-12.26-47.1-36.73-47.1c-22.1 0-33.16 13.28-33.16 39.43v123.79H61.37V196.83c0-26.15 6.68-46.92 20.03-62.31c13.75-15.39 31.75-23.14 54.09-23.14c25.82 0 45.34 9.92 58.74 29.76l12.69 21.11l12.69-21.11c13.4-19.84 32.92-29.76 58.75-29.76c22.33 0 40.34 7.75 54.08 23.14c13.36 15.39 20.04 36.16 20.04 62.31z"/></svg>',
            'github'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" aria-hidden="true" focusable="false"><path d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6c-3.3.3-5.6-1.3-5.6-3.6c0-2 2.3-3.6 5.2-3.6c3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9c2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9c.3 2 2.6 3.3 5.6 2.6c2.9-.7 4.9-2.6 4.6-4.6c-.3-2.3-2.6-3.6-5.6-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 71.8 205 171.6 238.2c12.8 2.3 17.5-5.6 17.5-12.1c0-6.2-.3-40.4-.3-61.4c0 0-70 15-84.7-29.8c0 0-11.4-29.1-27.8-36.9c0 0-22.9-15.7 1.6-15.4c0 0 24.9 2 38.6 25.8c21.9 38.6 58.6 27.5 72.9 20.9c2.3-16 8.8-27.5 16-33.7c-55.9-6.2-112.3-13.9-112.3-110.5c0-27.5 7.8-41.4 20.6-55.2c-2-5-8.8-25.7 2-53.6c16.1-5 52.6 20.6 52.6 20.6c15.2-4.3 31.4-6.5 47.6-6.5c16.3 0 32.8 2.3 47.7 6.5c0 0 36.6-25.8 52.6-20.6c10.8 27.8 4 48.6 2 53.6c12.8 13.8 20.6 27.8 20.6 55.2c0 96.9-58.8 104.2-114.9 110.5c9.2 7.8 17.2 22.9 17.2 46.4c0 33.7-.3 75.4-.3 83.1c0 6.5 4.6 14.4 17.5 12.1C424.2 457 496 362.9 496 252C496 113.3 383.5 8 244.8 8z"/></svg>',
            'matrix'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" aria-hidden="true" focusable="false"><path d="M0 32v448h60V0H0v32zm580 0v448h60V0h-60v32zM120 96v320h70V206.8l77.7 117.8h4.6L350 206.8V416h70V96h-68l-81.4 122.6L189 96h-69z"/></svg>',
        );

        return isset( $icons[ $key ] ) ? $icons[ $key ] : '';
    }
}


/**
 * Backward-compatible Freemius helper alias.
 *
 * @return object
 */
function db_fs() {
    return zeitfresser_fs();
}


/**
 * Backward-compatible social default alias.
 *
 * @param string $social_key Social service key.
 * @return string
 */
function graphthemes_get_social_link_default( $social_key ) {
    return zeitfresser_get_social_link_default( $social_key );
}
