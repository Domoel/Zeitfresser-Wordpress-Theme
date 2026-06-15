<?php
/**
 * TOC Customizer Options
 *
 * @package zeitfresser
 */

add_action( 'customize_register', 'zeitfresser_toc_options' );

function zeitfresser_toc_options( $wp_customize ) {

    /**
     * ------------------------
     * TOC HEADING
     * ------------------------
     */
    $wp_customize->add_setting(
        'ztfr_toc_heading',
        array(
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        new ZTFR_Customize_Heading_Control(
            $wp_customize,
            'ztfr_toc_heading',
            array(
                'label'    => esc_html__( 'TOC', 'zeitfresser' ),
                'section'  => 'ztfr_general',
                'priority' => 10,
            )
        )
    );

    /**
     * Show TOC
     */
    $wp_customize->add_setting(
        'show_article_toc',
        array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
        )
    );

    $wp_customize->add_control(
        'show_article_toc',
        array(
            'type'        => 'checkbox',
            'section'     => 'ztfr_general',
            'label'       => esc_html__( 'Show Article TOC', 'zeitfresser' ),
            'description' => esc_html__( 'Enable floating TOC on single posts.', 'zeitfresser' ),
            'priority'    => 11,
        )
    );

    /**
     * Minimum headlines threshold
     */
    $wp_customize->add_setting(
        'article_toc_min_headlines',
        array(
            'default'           => 3,
            'sanitize_callback' => 'absint',
        )
    );

    $wp_customize->add_control(
        'article_toc_min_headlines',
        array(
            'type'        => 'number',
            'section'     => 'ztfr_general',
            'label'       => esc_html__( 'Minimum Headlines', 'zeitfresser' ),
            'description' => esc_html__( 'TOC appears only if this number of headings is reached.', 'zeitfresser' ),
            'priority'    => 12,
            'input_attrs' => array(
                'min'  => 1,
                'max'  => 50,
                'step' => 1,
            ),
        )
    );
}
