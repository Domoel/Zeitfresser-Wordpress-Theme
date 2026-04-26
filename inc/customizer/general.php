<?php
/**
 * General Theme Options
 *
 * @package zeitfresser
 */

add_action( 'customize_register', 'zeitfresser_general_options' );

function zeitfresser_general_options( $wp_customize ) {

    /**
     * General Section (falls nicht schon vorhanden)
     */
    if ( ! $wp_customize->get_section( 'ztfr_general' ) ) {
        $wp_customize->add_section(
            'ztfr_general',
            array(
                'title'    => 'General Options',
                'priority' => 30,
            )
        );
    }

    /**
     * Excerpt Length
     */
    $wp_customize->add_setting(
        'post_snippet_excerpt_size',
        array(
            'default'           => 20,
            'sanitize_callback' => 'absint',
        )
    );

    $wp_customize->add_control(
        'post_snippet_excerpt_size',
        array(
            'type'        => 'number',
            'section'     => 'ztfr_general',
            'label'       => 'Excerpt Length (Post Cards)',
            'description' => 'Number of words shown in post previews.',
            'input_attrs' => array(
                'min'  => 5,
                'max'  => 100,
                'step' => 1,
            ),
        )
    );

    /**
     * Show Site Title
     */
    $wp_customize->add_setting(
        'show_hide_site_title',
        array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
        )
    );

    $wp_customize->add_control(
        'show_hide_site_title',
        array(
            'type'    => 'checkbox',
            'section' => 'ztfr_general',
            'label'   => 'Show Site Title',
        )
    );

    /**
     * Show Tagline
     */
    $wp_customize->add_setting(
        'show_hide_site_tagline',
        array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
        )
    );

    $wp_customize->add_control(
        'show_hide_site_tagline',
        array(
            'type'    => 'checkbox',
            'section' => 'ztfr_general',
            'label'   => 'Show Tagline',
        )
    );
}
