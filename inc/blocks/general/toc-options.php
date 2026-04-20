<?php
/**
 * TOC related general settings.
 */

add_action( 'customize_register', 'zeitfresser_register_article_toc_options' );

/**
 * Sanitize the minimum heading threshold for the article TOC.
 *
 * @param mixed $value Submitted control value.
 * @return int
 */
function zeitfresser_sanitize_article_toc_min_headlines( $value ) {
    $value = absint( $value );

    if ( $value < 1 ) {
        $value = zeitfresser_get_default_article_toc_min_headlines();
    }

    return min( 50, $value );
}

/**
 * Register article TOC options in General Options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function zeitfresser_register_article_toc_options( $wp_customize ) {
    $article_toc_title = '<hr/><h2>' . esc_html__( 'Article TOC:', 'zeitfresser' ) . '</h2>';

    $wp_customize->add_setting(
        'article_toc_options_heading',
        array(
            'sanitize_callback' => 'wp_kses_post',
        )
    );

    $wp_customize->add_control(
        new Daisy_Blog_Custom_Text(
            $wp_customize,
            'article_toc_options_heading',
            array(
                'section'  => 'daisy_blog_general_customization_section',
                'label'    => $article_toc_title,
                'priority' => 12,
            )
        )
    );

    $wp_customize->add_setting(
        'show_article_toc',
        array(
            'sanitize_callback' => 'zeitfresser_sanitize_checkbox',
            'default'           => zeitfresser_get_default_show_article_toc(),
        )
    );

    $wp_customize->add_control(
        new Graphthemes_Toggle_Control(
            $wp_customize,
            'show_article_toc',
            array(
                'settings'    => 'show_article_toc',
                'section'     => 'daisy_blog_general_customization_section',
                'label'       => esc_html__( 'Show Article TOC', 'zeitfresser' ),
                'description' => esc_html__( 'Enable the floating article TOC on single posts. Default: enabled.', 'zeitfresser' ),
                'type'        => 'toggle',
                'priority'    => 13,
            )
        )
    );

    $wp_customize->add_setting(
        'article_toc_min_headlines',
        array(
            'sanitize_callback' => 'zeitfresser_sanitize_article_toc_min_headlines',
            'default'           => zeitfresser_get_default_article_toc_min_headlines(),
        )
    );

    $wp_customize->add_control(
        'article_toc_min_headlines',
        array(
            'settings'    => 'article_toc_min_headlines',
            'type'        => 'number',
            'section'     => 'daisy_blog_general_customization_section',
            'label'       => esc_html__( 'Number of Headlines to Start TOC', 'zeitfresser' ),
            'description' => esc_html__( 'The article TOC is shown only when this number of headings or more is found. Default: 3.', 'zeitfresser' ),
            'priority'    => 14,
            'input_attrs' => array(
                'min'  => 1,
                'max'  => 50,
                'step' => 1,
            ),
        )
    );
}
