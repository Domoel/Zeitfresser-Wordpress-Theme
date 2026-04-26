<?php
/**
 * Social Links Customizer Options
 *
 * @package zeitfresser
 */

if ( ! function_exists( 'zeitfresser_get_social_links' ) ) {
    /**
     * Return supported social networks.
     *
     * @return array<string,string>
     */
    function zeitfresser_get_social_links() {
        return array(
            'facebook'  => esc_html__( 'Facebook', 'zeitfresser' ),
            'instagram' => esc_html__( 'Instagram', 'zeitfresser' ),
            'youtube'   => esc_html__( 'YouTube', 'zeitfresser' ),
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

function zeitfresser_social_links( $wp_customize ) {

    $social_links = zeitfresser_get_social_links();

    /**
     * Section Divider
     */
    $wp_customize->add_setting(
        'ztfr_social_heading',
        array(
            'sanitize_callback' => 'wp_kses_post',
        )
    );

    $wp_customize->add_control(
        'ztfr_social_heading',
        array(
            'section'     => 'ztfr_general',
            'type'        => 'hidden',
            'description' => '<hr><strong>' . esc_html__( 'Social Links', 'zeitfresser' ) . '</strong>',
            'priority'    => 30,
        )
    );

    /**
     * Social URLs
     */
    $priority = 31;

    foreach ( $social_links as $key => $label ) {

        $wp_customize->add_setting(
            'social_links_' . $key,
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
            )
        );

        $wp_customize->add_control(
            'social_links_' . $key,
            array(
                'type'     => 'url',
                'section'  => 'ztfr_general',
                'label'    => $label,
                'priority' => $priority++,
            )
        );
    }
}
