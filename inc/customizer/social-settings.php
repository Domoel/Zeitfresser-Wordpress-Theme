<?php
/**
 * Social Links Customizer Options
 *
 * @package zeitfresser
 */

if ( ! function_exists( 'zeitfresser_get_social_links' ) ) {
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

    /**
     * 🔥 Heading Control (falls noch nicht vorhanden)
     */
    if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'ZTFR_Customize_Heading_Control' ) ) {
        class ZTFR_Customize_Heading_Control extends WP_Customize_Control {
            public $type = 'ztfr-heading';

            public function render_content() {
                ?>
                <span style="display:block; font-weight:600; font-size:14px; margin:15px 0 5px;">
                    <?php echo esc_html( $this->label ); ?>
                </span>
                <?php
            }
        }
    }

    /**
     * ------------------------
     * SOCIAL HEADING
     * ------------------------
     */
    $wp_customize->add_setting(
        'ztfr_social_heading',
        array(
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        new ZTFR_Customize_Heading_Control(
            $wp_customize,
            'ztfr_social_heading',
            array(
                'label'    => esc_html__( 'Social Links', 'zeitfresser' ),
                'section'  => 'ztfr_general',
                'priority' => 30,
            )
        )
    );

    /**
     * Social URLs
     */
    $social_links = zeitfresser_get_social_links();

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
