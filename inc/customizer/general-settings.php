<?php
/**
 * General Theme Options
 *
 * @package zeitfresser
 */

add_action( 'customize_register', 'zeitfresser_general_options' );

function zeitfresser_general_options( $wp_customize ) {

    /**
     * 🔥 Custom Heading Control
     */
    if ( class_exists( 'WP_Customize_Control' ) ) {
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
     * General Section
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
     * ------------------------
     * HEADER
     * ------------------------
     */
    $wp_customize->add_setting( 'ztfr_header_heading', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control(
        new ZTFR_Customize_Heading_Control(
            $wp_customize,
            'ztfr_header_heading',
            array(
                'label'    => 'Header',
                'section'  => 'ztfr_general',
                'priority' => 1,
            )
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
            'type'     => 'checkbox',
            'section'  => 'ztfr_general',
            'label'    => 'Show Site Title',
            'priority' => 2,
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
            'type'     => 'checkbox',
            'section'  => 'ztfr_general',
            'label'    => 'Show Tagline',
            'priority' => 3,
        )
    );

    /**
     * ------------------------
     * GRID
     * ------------------------
     */
    $wp_customize->add_setting( 'ztfr_grid_heading', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control(
        new ZTFR_Customize_Heading_Control(
            $wp_customize,
            'ztfr_grid_heading',
            array(
                'label'    => 'Grid',
                'section'  => 'ztfr_general',
                'priority' => 20,
            )
        )
    );

    /**
     * Excerpt Length
     */
    $wp_customize->add_setting(
        'post_snippet_excerpt_size',
        array(
            'default'           => 25,
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
            'priority'    => 21,
            'input_attrs' => array(
                'min'  => 5,
                'max'  => 100,
                'step' => 1,
            ),
        )
    );
}
