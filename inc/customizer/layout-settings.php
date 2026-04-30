<?php
/**
 * Layout / Container Settings
 *
 * @package zeitfresser
 */

add_action( 'customize_register', 'zeitfresser_layout_options' );

function zeitfresser_layout_options( $wp_customize ) {

    /**
     * Container Width
     */
    $wp_customize->add_setting(
        'container_width',
        array(
            'default'           => 1400,
            'sanitize_callback' => 'absint',
        )
    );

    $wp_customize->add_control(
        'container_width',
        array(
            'type'        => 'number',
            'section'     => 'ztfr_general',
            'label'       => esc_html__( 'Container Width', 'zeitfresser' ),
            'description' => esc_html__( 'Maximum width of the content container in pixels.', 'zeitfresser' ),
            'priority'    => 22,
            'input_attrs' => array(
                'min'  => 800,
                'max'  => 2000,
                'step' => 10,
            ),
        )
    );
}

/**
 * Apply container width via CSS variable
 */
add_action( 'wp_head', 'zeitfresser_container_width_dynamic_css' );

function zeitfresser_container_width_dynamic_css() {

    $container_width = (int) get_theme_mod( 'container_width' );

    if ( $container_width <= 0 ) {
        $container_width = 1400;
    }

    echo '<style>:root{--container-width:' . esc_attr( $container_width ) . 'px;}</style>';
}
