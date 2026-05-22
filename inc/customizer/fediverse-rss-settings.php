<?php
/**
 * Fediverse RSS Widget Customizer Options
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function zeitfresser_customize_fediverse_settings( $wp_customize ) {

    /**
     * 1. Neue Sektion (Eigenes Menü im Customizer) erstellen
     */
    $wp_customize->add_section(
        'ztfr_fediverse_rss_section',
        array(
            'title'       => __( 'Fediverse Social Feed', 'zeitfresser' ),
            'description' => __( 'Configuration settings for the Fediverse/Mastodon RSS Widget.', 'zeitfresser' ),
            'priority'    => 150,
        )
    );

    /**
     * 2. Feed URL
     */
    $wp_customize->add_setting(
        'ztfr_fediverse_feed_url',
        array(
            'default'           => 'https://social.ztfr.eu/@dome/feed.rss',
            'sanitize_callback' => 'esc_url_raw',
        )
    );

    $wp_customize->add_control(
        'ztfr_fediverse_feed_url',
        array(
            'type'        => 'url',
            'section'     => 'ztfr_fediverse_rss_section',
            'label'       => __( 'RSS Feed URL', 'zeitfresser' ),
            'description' => __( 'Please enter the exact .rss URL of your profile (Mastodon/GoToSocial).', 'zeitfresser' ),
        )
    );

    /**
     * 3. Custom Display Name (Optional)
     */
    $wp_customize->add_setting(
        'ztfr_fediverse_display_name',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'ztfr_fediverse_display_name',
        array(
            'type'        => 'text',
            'section'     => 'ztfr_fediverse_rss_section',
            'label'       => __( 'Custom Display Name (Optional)', 'zeitfresser' ),
            'description' => __( 'Overwrites the auto detected displayname. Leave blank to use auto detection.', 'zeitfresser' ),
        )
    );

    /**
     * 4. Maximale Anzahl an Posts
     */
    $wp_customize->add_setting(
        'ztfr_fediverse_max_posts',
        array(
            'default'           => 3,
            'sanitize_callback' => 'absint',
        )
    );

    $wp_customize->add_control(
        'ztfr_fediverse_max_posts',
        array(
            'type'        => 'number',
            'section'     => 'ztfr_fediverse_rss_section',
            'label'       => __( 'Number of Posts', 'zeitfresser' ),
            'input_attrs' => array(
                'min'  => 1,
                'max'  => 10,
                'step' => 1,
            ),
        )
    );

    /**
     * 5. Word Limit (Textkürzung)
     */
    $wp_customize->add_setting(
        'ztfr_fediverse_word_limit',
        array(
            'default'           => 30,
            'sanitize_callback' => 'absint',
        )
    );

    $wp_customize->add_control(
        'ztfr_fediverse_word_limit',
        array(
            'type'        => 'number',
            'section'     => 'ztfr_fediverse_rss_section',
            'label'       => __( 'Word Limit (Text Length)', 'zeitfresser' ),
            'description' => __( 'After how many words should the post be cut off by “Read More”?', 'zeitfresser' ),
            'input_attrs' => array(
                'min'  => 10,
                'max'  => 100,
                'step' => 5,
            ),
        )
    );

    /**
     * 6. Cache Time
     */
    $wp_customize->add_setting(
        'ztfr_fediverse_cache_time',
        array(
            'default'           => 1800,
            'sanitize_callback' => 'absint',
        )
    );

    $wp_customize->add_control(
        'ztfr_fediverse_cache_time',
        array(
            'type'        => 'select',
            'section'     => 'ztfr_fediverse_rss_section',
            'label'       => __( 'Cache Duration', 'zeitfresser' ),
            'description' => __( 'How often should the feed be updated from the server?', 'zeitfresser' ),
            'choices'     => array(
                900    => __( '15 Minuten', 'zeitfresser' ),
                1800   => __( '30 Minuten', 'zeitfresser' ),
                3600   => __( '1 Stunde', 'zeitfresser' ),
                21600  => __( '6 Stunden', 'zeitfresser' ),
                43200  => __( '12 Stunden', 'zeitfresser' ),
            ),
        )
    );

}
add_action( 'customize_register', 'zeitfresser_customize_fediverse_settings' );
