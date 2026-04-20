<?php
/**
 * Social links customizer settings.
 *
 * @package zeitfresser
 */

if ( ! function_exists( 'zeitfresser_get_social_links' ) ) {
    /**
     * Return supported social network labels.
     *
     * @return array<string,string>
     */
    function zeitfresser_get_social_links() {
        return array(
            'facebook'  => esc_html__( 'Facebook', 'zeitfresser' ),
            'instagram' => esc_html__( 'Instagram', 'zeitfresser' ),
            'youtube'   => esc_html__( 'Youtube', 'zeitfresser' ),
            'linkedin'  => esc_html__( 'LinkedIn', 'zeitfresser' ),
            'twitter'   => esc_html__( 'Twitter', 'zeitfresser' ),
            'pinterest' => esc_html__( 'Pinterest', 'zeitfresser' ),
            'tiktok'    => esc_html__( 'TikTok', 'zeitfresser' ),
            'mastodon'  => esc_html__( 'Mastodon', 'zeitfresser' ),
            'github'    => esc_html__( 'GitHub', 'zeitfresser' ),
            'matrix'    => esc_html__( 'Matrix.org', 'zeitfresser' ),
        );
    }
}

add_action( 'customize_register', 'zeitfresser_social_links' );

/**
 * Register social link customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function zeitfresser_social_links( $wp_customize ) {
    $social_links       = zeitfresser_get_social_links();
    $social_links_title = '<hr/><h2>' . esc_html__( 'Social Links:', 'zeitfresser' ) . '</h2>';

    $wp_customize->add_setting(
        'social_links_title',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );

    $wp_customize->add_control(
        new Daisy_Blog_Custom_Text(
            $wp_customize,
            'social_links_title',
            array(
                'section'  => 'daisy_blog_general_customization_section',
                'label'    => $social_links_title,
                'priority' => 20,
            )
        )
    );

    $social_priority = 21;

    foreach ( $social_links as $social_key => $social_label ) {
        $wp_customize->add_setting(
            'social_links_' . $social_key,
            array(
                'default'           => zeitfresser_get_social_link_default( $social_key ),
                'sanitize_callback' => 'esc_url_raw',
            )
        );

        $wp_customize->add_control(
            'social_links_' . $social_key,
            array(
                'label'    => $social_label,
                'section'  => 'daisy_blog_general_customization_section',
                'type'     => 'url',
                'priority' => $social_priority++,
            )
        );
    }
}
